<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Tools;

use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('lara-base::layouts.app')]
class ModelQuery extends Component
{
    use WithPagination;

    public string $model    = '';
    public array  $filters  = [];
    public string $orderBy  = 'id';
    public string $orderDir = 'asc';
    public int    $perPage  = 10;

    protected $paginationTheme = 'bootstrap';

    private array $excludeFromView = [
        'password',
        'remember_token',
    ];

    private array $excludeFromExport = [
        'password',
        'remember_token',
        'profile_photo_base64',
    ];

	protected function allowedModels(): array
	{
		return config('lara-base.model_query.models', []);
	}
	
    public function getModelsProperty(): array
    {
        return array_keys($this->allowedModels());
    }

    public function updatedModel(): void
    {
        $this->filters  = [];
        $this->orderBy  = 'id';
        $this->orderDir = 'asc';
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array($this->perPage, [10, 25, 50, 100, 500]) ? $this->perPage : 25;
        $this->resetPage();
    }

    public function addFilter(): void
    {
        $this->filters[] = ['column' => '', 'operator' => '=', 'value' => ''];
        $this->resetPage();
    }

    public function removeFilter(int $index): void
    {
        array_splice($this->filters, $index, 1);
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filters  = [];
        $this->orderBy  = 'id';
        $this->orderDir = 'asc';
        $this->resetPage();
    }

    public function getColumnsProperty(): array
    {
        $class = $this->allowedModels()[$this->model] ?? null;
        if (!$class) return [];

        return array_values(
            array_diff(
                Schema::getColumnListing((new $class)->getTable()),
                $this->excludeFromView
            )
        );
    }

    private function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $class   = $this->allowedModels()[$this->model];
        $allowed = $this->columns;
        $query   = $class::query();

        foreach ($this->filters as $filter) {
            $column   = $filter['column'];
            $operator = $filter['operator'];
            $value    = $filter['value'];

            if (empty($column) || !in_array($column, $allowed)) continue;

            match($operator) {
                'is_null'     => $query->whereNull($column),
                'is_not_null' => $query->whereNotNull($column),
                'like'        => $query->where($column, 'like', '%' . $value . '%'),
                '='           => $query->where($column, '=', $value),
                '!='          => $query->where($column, '!=', $value),
                '>'           => $query->where($column, '>', $value),
                '<'           => $query->where($column, '<', $value),
                default       => null,
            };
        }

        $orderBy = in_array($this->orderBy, $allowed) ? $this->orderBy : 'id';

        return $query->orderBy($orderBy, $this->orderDir === 'desc' ? 'desc' : 'asc');
    }

    public function getResultsProperty()
    {
        $class = $this->allowedModels()[$this->model] ?? null;
        if (!$class) return null;

        return $this->buildQuery()->paginate($this->perPage);
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows     = $this->buildQuery()->get();
        $columns  = array_values(array_diff($this->columns, $this->excludeFromExport));
        $filename = strtolower(str_replace(' ', '_', $this->model)) . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows, $columns) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $columns, ';');

            foreach ($rows as $row) {
                $line = array_map(function ($col) use ($row) {
                    $val = $row->$col;

                    if ($val instanceof \BackedEnum)    return $val->value;
                    if ($val instanceof \UnitEnum)      return $val->name;
                    if ($val instanceof \Carbon\Carbon) return $val->format('Y-m-d H:i:s');
                    if (is_array($val))                 return json_encode($val);

                    return $val;
                }, $columns);

                fputcsv($handle, array_values($line), ';');
            }

            fclose($handle);
        }, $filename);
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.tools.model-query')
            ->layoutData(['pageTitle' => 'Model Query']);
    }
}