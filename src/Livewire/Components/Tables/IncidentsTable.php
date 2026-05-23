<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Tables;

use Illuminate\Database\Eloquent\Builder;
use Sitakgmbh\LaraBase\Models\Incident;

class IncidentsTable extends BaseTable
{
    public bool $showResolved = false;

    protected $queryString = [
        'showResolved'  => ['except' => false],
        'search'        => ['except' => ''],
        'perPage'       => ['except' => 10],
        'sortField'     => ['except' => null],
        'sortDirection' => ['except' => null],
    ];

    public function toggleResolved(): void
    {
        $this->showResolved = !$this->showResolved;
        $this->resetPage();
    }

    protected function model(): string
    {
        return Incident::class;
    }

    protected function getColumns(): array
    {
        return [
            'id'          => ['label' => 'ID',         'sortable' => true,  'searchable' => true],
            'priority'    => ['label' => 'Priorität',  'sortable' => true,  'searchable' => true],
            'title'       => ['label' => 'Titel',      'sortable' => true,  'searchable' => true],
            'created_at'  => ['label' => 'Erstellt am','sortable' => true,  'searchable' => true],
            'resolved_at' => ['label' => 'Gelöst am',  'sortable' => true,  'searchable' => true],
            'actions'     => ['label' => 'Aktionen',   'sortable' => false, 'searchable' => false, 'class' => 'shrink'],
        ];
    }

    protected function defaultSortField(): string
    {
        return 'created_at';
    }

    protected function defaultSortDirection(): string
    {
        return 'desc';
    }

    protected function applyFilters(Builder $query): void
    {
        if (!$this->showResolved) {
            $query->open();
        }

        if ($this->search) {
            $search = strtolower($this->search);
            $query->where(function ($q) use ($search) {
                $q->orWhereRaw('LOWER(id) LIKE ?',    ["%{$search}%"])
                  ->orWhereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(priority) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(resolved_at, '%d.%m.%Y %H:%i') LIKE ?", ["%{$search}%"]);
            });
        }
    }

    protected function getColumnBadges(): array
    {
        return [
            'priority' => [
                'high'   => ['label' => 'Hoch',  'class' => 'danger'],
                'medium' => ['label' => 'Mittel','class' => 'warning'],
                'low'    => ['label' => 'Tief',  'class' => 'info'],
            ],
        ];
    }

	protected function getColumnButtons(): array
	{
		return [
			'actions' => [
				[
					'url'   => fn($row) => route('admin.incidents.show', $row->id),
					'icon'  => 'mdi mdi-eye',
					'title' => 'Details',
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
				'title'          => 'Incident löschen',
				'message'        => 'Soll dieser Incident wirklich gelöscht werden?',
				'hint'           => 'Dieser Vorgang kann nicht rückgängig gemacht werden.',
				'confirmLabel'   => 'Löschen',
				'confirmClass'   => 'btn-danger',
				'headerBg'       => 'bg-danger',
				'confirmEvent'   => 'incident-delete-confirmed',
				'confirmPayload' => ['id' => $id],
			]
		);
	}

	#[\Livewire\Attributes\On('incident-delete-confirmed')]
	public function deleteConfirmed(int $id): void
	{
		Incident::findOrFail($id)->delete();
		\Sitakgmbh\LaraBase\Support\LaraToast::success('Incident gelöscht', '', $this);
		$this->dispatch('$refresh');
	}

    protected function getTableActions(): array
    {
        return [
            [
                'method'    => 'toggleResolved',
                'icon'      => $this->showResolved ? 'mdi mdi-checkbox-marked-outline' : 'mdi mdi-checkbox-blank-outline',
                'iconClass' => 'text-secondary',
                'class'     => $this->showResolved ? 'btn-light' : 'btn-outline-light',
                'title'     => $this->showResolved ? 'Nur offene anzeigen' : 'Alle anzeigen',
            ],
        ];
    }

    public function render()
    {
        return view('lara-base::livewire.components.tables.incidents-table', [
            'columns' => $this->getColumns(),
            'records' => $this->records,
        ]);
    }
}