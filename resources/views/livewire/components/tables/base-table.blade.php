<div class="card w-100">
    <div class="card-body">
        <div class="row mb-3 align-items-center">
            <div class="col-md-2 mb-2 mb-md-0">
                <select wire:model.live="perPage" class="form-select form-select-sm">
                    <option value="10">10 pro Seite</option>
                    <option value="25">25 pro Seite</option>
                    <option value="50">50 pro Seite</option>
                    <option value="100">100 pro Seite</option>
                </select>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <input type="text"
                       wire:model.live.debounce.500ms="search"
                       class="form-control form-control-sm"
                       placeholder="Suchen...">
            </div>
            <div class="col-md-6 text-md-end d-flex justify-content-end">
                <div class="btn-group" role="group">
                    @if(method_exists($this, 'getTableActions'))
                        @foreach($this->getTableActions() as $action)
                            <button
                                @if(isset($action['method'])) wire:click="{{ $action['method'] }}" @endif
                                class="btn btn-sm {{ $action['class'] ?? 'btn-outline-secondary' }}"
                                title="{{ $action['title'] ?? $action['label'] ?? '' }}">
                                @if(!empty($action['icon']))
                                    <i class="{{ $action['icon'] }} {{ $action['iconClass'] ?? '' }}"></i>
                                @endif
                                {{ $action['label'] ?? '' }}
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-centered table-striped table-nowrap mb-0">
                <thead>
                <tr>
                    @foreach ($columns as $field => $col)
                        @php $hidden = is_array($col) ? ($col['hidden'] ?? false) : false; @endphp
                        @if(!$hidden)
                            @php
                                $label    = is_array($col) ? $col['label'] : $col;
                                $sortable = is_array($col) ? ($col['sortable'] ?? true) : true;
                                $class    = is_array($col) ? ($col['class'] ?? '') : '';
                                $style    = in_array($field, ['actions', 'id', 'status_badges']) ? 'width:1%; white-space:nowrap;' : '';
                            @endphp
                            <th @if($sortable) wire:click="sortBy('{{ $field }}')" class="cursor-pointer {{ $class }}" @else class="{{ $class }}" @endif style="{{ $style }}">
                                {{ $label }}
                                @if($sortable && $sortField === $field)
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                        @endif
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @forelse ($records as $row)
                    <tr>
                        @foreach ($columns as $field => $col)
                            @php $hidden = is_array($col) ? ($col['hidden'] ?? false) : false; @endphp
                            @if(!$hidden)
                                @php
                                    $class = is_array($col) ? ($col['class'] ?? '') : '';
                                    $style = $field === 'actions' ? 'width:1%; white-space:nowrap;' : '';
                                @endphp
                                <td class="{{ $class }}" style="{{ $style }}">{!! $this->renderCell($field, $row) !!}</td>
                            @endif
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ collect($columns)->reject(fn($c) => is_array($c) && ($c['hidden'] ?? false))->count() }}">
                            Keine Daten gefunden.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $records->onEachSide(1)->links('livewire::bootstrap') }}
        </div>
    </div>
</div>