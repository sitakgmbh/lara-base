<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Tables;

use Illuminate\Database\Eloquent\Builder;
use Sitakgmbh\LaraBase\Enums\LogLevel;
use Sitakgmbh\LaraBase\Enums\LogCategory;
use Sitakgmbh\LaraBase\Models\Log;

class LogsTable extends BaseTable
{
    public string $filterLevel    = '';
    public string $filterCategory = '';
    public string $dateFrom       = '';
    public string $dateTo         = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'perPage'        => ['except' => 10],
        'sortField'      => ['except' => null],
        'sortDirection'  => ['except' => null],
        'filterLevel'    => ['except' => ''],
        'filterCategory' => ['except' => ''],
        'dateFrom'       => ['except' => ''],
        'dateTo'         => ['except' => ''],
    ];

    protected function model(): string
    {
        return Log::class;
    }

    protected function defaultSortField(): string
    {
        return 'created_at';
    }

    protected function defaultSortDirection(): string
    {
        return 'desc';
    }

    protected function getColumns(): array
    {
        return [
            'id' => [
                'label'    => 'ID',
                'sortable' => true,
                'hidden'   => true,
            ],
            'created_at' => [
                'label'    => 'Datum',
                'sortable' => true,
            ],
            'level' => [
                'label'    => 'Level',
                'sortable' => true,
            ],
            'category' => [
                'label'    => 'Kategorie',
                'sortable' => true,
            ],
            'message' => [
                'label'      => 'Nachricht',
                'sortable'   => true,
                'searchable' => true,
            ],
            'actions' => [
                'label'    => 'Aktionen',
                'sortable' => false,
                'class'    => 'shrink',
            ],
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterLevel !== '') {
            try {
                $level = LogLevel::from($this->filterLevel);
                $query->where('level', $level->value);
            } catch (\ValueError) {
                $this->filterLevel = '';
            }
        }

        if ($this->filterCategory !== '') {
            try {
                $category = LogCategory::from($this->filterCategory);
                $query->where('category', $category->value);
            } catch (\ValueError) {
                $this->filterCategory = '';
            }
        }

        if ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo);
        }

        if (!empty($this->search)) {
            $search = mb_strtolower($this->search, 'UTF-8');
            $query->whereRaw('LOWER(message) LIKE ?', ["%{$search}%"]);
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['filterLevel', 'filterCategory', 'dateFrom', 'dateTo']);
    }

    protected function getColumnBadges(): array
    {
        return [
            'level' => [
                'error'   => ['label' => LogLevel::Error->label(),   'class' => 'danger'],
                'warning' => ['label' => LogLevel::Warning->label(), 'class' => 'warning'],
                'info'    => ['label' => LogLevel::Info->label(),    'class' => 'info'],
            ],
        ];
    }

	protected function getColumnButtons(): array
	{
		return [
			'actions' => [
				[
					'method'  => 'openContextModal',
					'idParam' => 'id',
					'icon'    => 'mdi mdi-eye',
					'title'   => 'Details',
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
				'title'          => 'Log löschen',
				'message'        => "Soll dieser Log-Eintrag wirklich gelöscht werden?",
				'hint'           => 'Dieser Vorgang kann nicht rückgängig gemacht werden.',
				'confirmLabel'   => 'Löschen',
				'confirmClass'   => 'btn-danger',
				'headerBg'       => 'bg-danger',
				'confirmEvent'   => 'log-delete-confirmed',
				'confirmPayload' => ['id' => $id],
			]
		);
	}

	#[\Livewire\Attributes\On('log-delete-confirmed')]
	public function deleteConfirmed(int $id): void
	{
		Log::findOrFail($id)->delete();
		\Sitakgmbh\LaraBase\Support\LaraToast::success('Log gelöscht', '', $this);
		$this->dispatch('$refresh');
	}

	protected function getColumnFormatters(): array
	{
		return [
			'level'    => fn($row) => $row->level->label(),
			'category' => fn($row) => $row->category->label(),
		];
	}

    public function renderCell(string $field, $row)
    {
        if ($field === 'level') {
            $value  = data_get($row, $field);
            $key    = strtolower(is_string($value) ? $value : ($value->value ?? ''));
            $badges = $this->getColumnBadges()['level'] ?? [];

            if (isset($badges[$key])) {
                return view('lara-base::livewire.components.tables.base-table-badge', [
                    'label' => $badges[$key]['label'],
                    'class' => $badges[$key]['class'],
                    'icon'  => null,
                ]);
            }
        }

        return parent::renderCell($field, $row);
    }

    public function openContextModal(int $id): void
    {
        $this->dispatch('open-modal', 'components.modals.log-context', ['id' => $id]);
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['filterLevel', 'filterCategory', 'dateFrom', 'dateTo'])) {
            $this->resetPage();
        }
    }

    public function setToday(): void
    {
        $this->dateFrom = now()->startOfDay()->format('Y-m-d\TH:i');
        $this->dateTo   = now()->format('Y-m-d\TH:i');
    }

    public function setLastWeek(): void
    {
        $this->dateFrom = now()->subWeek()->format('Y-m-d\TH:i');
        $this->dateTo   = now()->format('Y-m-d\TH:i');
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
        return view('lara-base::livewire.components.tables.logs-table', [
            'columns' => $this->getColumns(),
            'records' => $this->records,
        ]);
    }
}