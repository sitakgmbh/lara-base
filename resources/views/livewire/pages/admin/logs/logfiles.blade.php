<div>
    @if($status)
        <div class="alert alert-success">{{ $status }}</div>
    @endif

    @forelse($files as $file)
        <div class="card mb-3 shadow-sm">

            <div class="card-header bg-primary text-white py-1 d-flex justify-content-between align-items-center">
                <div><strong>{{ $file['name'] }}</strong></div>

                <div class="small text-white">
                    Letzte Änderung: {{ $file['updated'] }}
                </div>
            </div>

            <div class="card-body p-0">
                <pre class="mb-0 p-3 bg-light"
                     style="max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 0.875rem;">{{ $file['content'] }}</pre>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div class="text-muted small">
                    Grösse: {{ $file['size'] }}

                    @if($file['truncated'])
                        · Letzte {{ $offsets[$file['name']] ?? $linesPerPage }} Zeilen geladen
                    @else
                        · Alle {{ $offsets[$file['name']] ?? $linesPerPage }} Zeilen geladen
                    @endif

                    <i class="mdi mdi-information-outline text-muted"
                       data-bs-toggle="tooltip"
                       title="Es werden maximal 2000 Zeilen gleichzeitig angezeigt (Sliding Window). Ältere Zeilen können über 'Mehr' geladen werden. Für den vollständigen Inhalt bitte herunterladen."></i>
                </div>

                <div class="d-flex flex-wrap gap-2">

                    @if($file['truncated'])
                        <button type="button"
                                wire:click="loadMore('{{ $file['name'] }}')"
                                class="btn btn-sm btn-light-secondary">
                            <i class="mdi mdi-chevron-up"></i> Mehr
                        </button>
                    @endif

                    <button type="button"
                            wire:click="download('{{ $file['name'] }}')"
                            class="btn btn-sm btn-primary">
                        <i class="mdi mdi-download"></i> Download
                    </button>

                    <button type="button"
                            wire:click="confirmDelete('{{ $file['name'] }}')"
                            class="btn btn-sm btn-danger">
                        <i class="mdi mdi-delete"></i> Löschen
                    </button>

                </div>
            </div>
        </div>

    @empty
        <div class="alert alert-info">Keine Logfiles gefunden.</div>
    @endforelse

    <livewire:components.modals.alert-modal />
    <livewire:components.modals.confirm-modal />
</div>

@push('scripts')
<script>
    document.querySelectorAll('pre').forEach(pre => {
        pre.scrollTop = pre.scrollHeight;
    });
</script>
@endpush