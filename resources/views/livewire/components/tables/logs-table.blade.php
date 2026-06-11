<div>
    {{-- Filter --}}
    <div class="row mb-3 g-2">
        <div class="col-md-2">
            <select wire:model.live="filterLevel" class="form-select form-select-sm">
                <option value="">Alle Level</option>
                @foreach(\Sitakgmbh\LaraBase\Enums\LogLevel::cases() as $level)
                    <option value="{{ $level->value }}">{{ $level->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select wire:model.live="filterCategory" class="form-select form-select-sm">
                <option value="">Alle Kategorien</option>
                @foreach(\Sitakgmbh\LaraBase\Enums\LogCategory::labels() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="datetime-local" wire:model.live="dateFrom" class="form-control form-control-sm" placeholder="Von">
        </div>
        <div class="col-md-2">
            <input type="datetime-local" wire:model.live="dateTo" class="form-control form-control-sm" placeholder="Bis">
        </div>
        <div class="col-md-4 d-flex gap-1">
            <button wire:click="setToday"    class="btn btn-sm btn-outline-light">Heute</button>
            <button wire:click="setLastWeek" class="btn btn-sm btn-outline-light">Letzte Woche</button>
            <button wire:click="resetFilters" class="btn btn-sm btn-outline-light"><i class="mdi mdi-filter-remove-outline"></i></button>
        </div>
    </div>

    @include('lara-base::livewire.components.tables.base-table', ['columns' => $columns, 'records' => $records])
    <livewire:components.modals.confirm-modal />
    <livewire:components.modals.log-context />
</div>