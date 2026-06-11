<div>
    {{-- Model + Toolbar --}}
    <div class="card mb-3">
        <div class="card-body d-flex gap-2 align-items-end flex-wrap py-2 mb-2">
            <div>
                <label class="form-label small mt-1 mb-1">Model</label>
                <select wire:model.live="model"
                        wire:loading.attr="disabled"
                        class="form-select form-select-sm">
                    <option value="">Bitte auswählen</option>
                    @foreach($this->models as $label)
                        <option value="{{ $label }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($model)
                <div>
                    <label class="form-label small mb-1">Sortierung</label>
                    <select wire:model.live="orderBy"
                            wire:loading.attr="disabled"
                            class="form-select form-select-sm">
                        @foreach($this->columns as $col)
                            <option value="{{ $col }}">{{ $col }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Richtung</label>
                    <select wire:model.live="orderDir"
                            wire:loading.attr="disabled"
                            class="form-select form-select-sm">
                        <option value="asc">ASC</option>
                        <option value="desc">DESC</option>
                    </select>
                </div>
                <button wire:click="addFilter" wire:loading.attr="disabled" class="btn btn-sm btn-primary">
                    <i class="mdi mdi-plus"></i> Filter
                </button>
                <button wire:click="resetFilters" wire:loading.attr="disabled" class="btn btn-sm btn-secondary">
                    <i class="mdi mdi-refresh"></i> Reset
                </button>
            @endif
        </div>
    </div>

    @if($model)
        @foreach($filters as $i => $filter)
            <div class="card mb-2">
                <div class="card-body d-flex gap-2 align-items-end flex-wrap py-2">
                    <div>
                        <label class="form-label small mb-1">Spalte</label>
                        <select wire:model.live="filters.{{ $i }}.column"
                                wire:loading.attr="disabled"
                                class="form-select form-select-sm">
                            <option value="">Bitte auswählen</option>
                            @foreach($this->columns as $col)
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-1">Operator</label>
                        <select wire:model.live="filters.{{ $i }}.operator"
                                wire:loading.attr="disabled"
                                class="form-select form-select-sm">
                            <option value="=">=</option>
                            <option value="!=">!=</option>
                            <option value="like">LIKE</option>
                            <option value=">">&gt;</option>
                            <option value="<">&lt;</option>
                            <option value="is_null">IS NULL</option>
                            <option value="is_not_null">IS NOT NULL</option>
                        </select>
                    </div>
                    @if(!in_array($filter['operator'], ['is_null', 'is_not_null']))
                        <div>
                            <label class="form-label small mb-1">Wert</label>
                            <input wire:model.live.debounce.500ms="filters.{{ $i }}.value"
                                   type="text"
                                   class="form-control form-control-sm"
                                   placeholder="Wert…">
                        </div>
                    @endif
                    <button wire:click="removeFilter({{ $i }})"
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-danger">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>
            </div>
        @endforeach

        @if($this->results)
            <div class="card" style="overflow: hidden;">
                <div class="card-header py-1 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 mt-2 mb-1">
                        <select wire:model.live="perPage"
                                wire:loading.attr="disabled"
                                class="form-select form-select-sm" style="width: auto;">
                            <option value="10">10 pro Seite</option>
                            <option value="25">25 pro Seite</option>
                            <option value="50">50 pro Seite</option>
                            <option value="100">100 pro Seite</option>
                            <option value="500">500 pro Seite</option>
                        </select>
                    </div>
                    <button wire:click="export" wire:loading.attr="disabled" class="btn btn-sm btn-light">
                        <i class="mdi mdi-download"></i> CSV
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                @foreach($this->columns as $col)
                                    <th class="small ps-3">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->results as $row)
                                <tr>
                                    @foreach($this->columns as $col)
                                        <td class="small text-truncate ps-3" style="max-width: 200px;">
                                            {{ is_array($row->$col) ? json_encode($row->$col) : $row->$col }}
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($this->columns) }}" class="text-center text-muted">
                                        Keine Treffer
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-1 mt-2">
                    {{ $this->results->links('livewire::bootstrap') }}
                </div>
            </div>
        @endif
    @endif
</div>