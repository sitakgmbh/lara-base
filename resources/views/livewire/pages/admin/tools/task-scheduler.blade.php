<div>
    <div wire:loading.flex wire:target="run"
         class="position-fixed top-0 start-0 w-100 h-100 bg-primary text-white bg-opacity-75 align-items-center justify-content-center"
         style="z-index:1050;">
        <div class="text-center">
            <i class="mdi mdi-loading mdi-spin fs-2 mb-2"></i>
            <div>Bitte warten …</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:36px;"></th>
                    <th wire:click="sort('task')" style="cursor:pointer;">Task</th>
                    <th class="d-none d-md-table-cell" wire:click="sort('description')" style="cursor:pointer;">Beschreibung</th>
                    <th class="d-none d-md-table-cell" wire:click="sort('interval')" style="cursor:pointer;">Intervall</th>
                    <th wire:click="sort('nextRun')" style="cursor:pointer;">Nächster Lauf</th>
                    <th style="width:34px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php $open = $openDetails[$row['key']] ?? false; @endphp
                    <tr>
                        <td class="text-center p-0">
                            <button class="btn btn-link p-0 d-flex align-items-center justify-content-center"
                                    wire:click="toggleDetails('{{ $row['key'] }}')"
                                    style="width:36px;height:36px;">
                                <i class="mdi mdi-chevron-{{ $open ? 'down' : 'right' }} fs-5"></i>
                            </button>
                        </td>
                        <td class="fw-semibold py-1 text-nowrap">{{ $row['task'] }}</td>
                        <td class="d-none d-md-table-cell py-1 text-muted small text-nowrap">
                            {{ \Illuminate\Support\Str::limit($row['description'], 150) }}
                        </td>
                        <td class="d-none d-md-table-cell py-1 text-nowrap">{{ $row['interval'] }}</td>
                        <td class="py-1 text-nowrap">{{ $row['nextRun'] }}</td>
                        <td class="text-end p-0">
                            <button class="btn btn-link btn-sm p-0"
                                    wire:click="run('{{ $row['task'] }}')"
                                    @disabled($running)>
                                <i class="mdi mdi-play-circle-outline fs-4"></i>
                            </button>
                        </td>
                    </tr>

                    @if($open)
                        <tr>
                            <td></td>
                            <td colspan="5" class="bg-light small py-2">
                                <div class="fw-semibold mb-1">Beschreibung</div>
                                <div>{{ $row['description'] }}</div>
                                <div class="mt-2 text-muted">
                                    <strong>Intervall:</strong> {{ $row['interval'] }}
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">Keine Tasks gefunden</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <livewire:components.modals.artisan-output />
    <livewire:components.modals.alert-modal />
</div>