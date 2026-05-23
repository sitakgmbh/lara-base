<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UsersTable extends BaseTable
{
    protected $listeners = ['user-deleted' => '$refresh'];

    protected function model(): string
    {
        return config('auth.providers.users.model', \App\Models\User::class);
    }

    protected function getColumns(): array
    {
        return [
            'auth_type'  => ['label' => 'Typ',          'sortable' => true],
            'username'   => ['label' => 'Benutzername',  'sortable' => true, 'searchable' => true],
            'firstname'  => ['label' => 'Vorname',       'sortable' => true, 'searchable' => true],
            'lastname'   => ['label' => 'Nachname',      'sortable' => true, 'searchable' => true],
            'email'      => ['label' => 'E-Mail',        'sortable' => true, 'searchable' => true],
            'role'       => ['label' => 'Rolle',         'sortable' => true],
            'is_enabled' => ['label' => 'Status',        'sortable' => true],
            'created_at' => ['label' => 'Erstellt',      'sortable' => true],
            'actions'    => ['label' => 'Aktionen',      'sortable' => false, 'class' => 'shrink'],
        ];
    }

    protected function defaultSortField(): string
    {
        return 'username';
    }

    protected function applyFilters(Builder $query): void
    {
        $query->with('roles');
        $query->select('users.*');

        $query->addSelect([
            'role' => DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereColumn('model_has_roles.model_id', 'users.id')
                ->limit(1)
                ->select('roles.name'),
        ]);

        if ($this->search) {
            $search = strtolower($this->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(username) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"]);
            });
        }
    }

    protected function getColumnBadges(): array
    {
        return [
            'auth_type' => [
                'local' => ['label' => 'Local', 'class' => 'secondary'],
                'ldap'  => ['label' => 'LDAP',  'class' => 'info'],
            ],
            'is_enabled' => [
                '1'  => ['label' => 'Aktiviert',   'class' => 'success'],
                '0'  => ['label' => 'Deaktiviert', 'class' => 'secondary'],
                ''   => ['label' => 'n/a',          'class' => 'light text-dark'],
            ],
            'role' => [
                'admin' => ['label' => 'Admin', 'class' => 'dark'],
                'user'  => ['label' => 'User',  'class' => 'secondary'],
            ],
        ];
    }

	protected function getColumnButtons(): array
	{
		return [
			'actions' => [
				[
					'url'   => fn($row) => route('admin.users.edit', $row->id),
					'icon'  => 'mdi mdi-square-edit-outline',
				],
				[
					'method'  => 'openDeleteModal',
					'idParam' => 'id',
					'icon'    => 'mdi mdi-delete',
					'title'   => 'Löschen',
				],
			],
		];
	}

	public function openDeleteModal(int $id): void
	{
		$this->dispatch('open-modal',
			modal: 'components.modals.confirm-modal',
			payload: [
				'title'          => 'Benutzer löschen',
				'message'        => 'Soll dieser Benutzer wirklich gelöscht werden?',
				'hint'           => 'Dieser Vorgang kann nicht rückgängig gemacht werden.',
				'confirmLabel'   => 'Löschen',
				'confirmClass'   => 'btn-danger',
				'headerBg'       => 'bg-danger',
				'confirmEvent'   => 'user-delete-confirmed',
				'confirmPayload' => ['id' => $id],
			]
		);
	}

	#[\Livewire\Attributes\On('user-delete-confirmed')]
	public function deleteConfirmed(int $id): void
	{
		$model = config('auth.providers.users.model', \App\Models\User::class);
		$model::findOrFail($id)->delete();
		\Sitakgmbh\LaraBase\Support\LaraToast::success('Benutzer gelöscht', '', $this);
		$this->dispatch('user-deleted');
	}

    protected function getTableActions(): array
    {
        return [
            [
                'method'    => 'exportCsv',
                'icon'      => 'mdi mdi-tray-arrow-down',
                'iconClass' => 'text-secondary',
                'class'     => 'btn-outline-light',
                'title'     => 'Tabelle als CSV-Datei exportieren',
            ],
        ];
    }

    public function render()
    {
        return view('lara-base::livewire.components.tables.users-table', [
            'columns' => $this->getColumns(),
            'records' => $this->records,
        ]);
    }
}