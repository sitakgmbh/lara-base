<div>
    {{-- Incident Details --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-2">Titel:</dt>
                        <dd class="col-sm-10">{{ $incident->title }}</dd>
                        <dt class="col-sm-2">Priorität:</dt>
                        <dd class="col-sm-10">
                            <span class="badge 
                                {{ $incident->priority === 'high' ? 'bg-danger' : '' }}
                                {{ $incident->priority === 'medium' ? 'bg-warning' : '' }}
                                {{ $incident->priority === 'low' ? 'bg-info' : '' }}">
                                {{ ucfirst($incident->priority) === 'High' ? 'Hoch' : '' }}
                                {{ ucfirst($incident->priority) === 'Medium' ? 'Mittel' : '' }}
                                {{ ucfirst($incident->priority) === 'Low' ? 'Tief' : '' }}
                            </span>
                        </dd>
                        <dt class="col-sm-2">Erstellt von:</dt>
                        <dd class="col-sm-10">{{ $incident->creator?->firstname ?? '-' }} {{ $incident->creator?->lastname ?? '' }}</dd>
                        <dt class="col-sm-2">Erstellt am:</dt>
                        <dd class="col-sm-10">{{ $incident->created_at->format('d.m.Y H:i') }}</dd>
                        <dt class="col-sm-2">Gelöst von:</dt>
                        <dd class="col-sm-10">{{ $incident->resolver?->firstname ?? '-' }} {{ $incident->resolver?->lastname ?? '' }}</dd>
                        <dt class="col-sm-2">Gelöst am:</dt>
                        <dd class="col-sm-10">{{ $incident->resolved_at?->format('d.m.Y H:i') ?? '-' }}</dd>
                        <dt class="col-sm-2">Beschreibung:</dt>
                        <dd class="col-sm-10 mb-0">{!! nl2br(e($incident->description)) !!}</dd>
                    </dl>
                    @if($incident->metadata)
                        <div class="mt-2">
                            <a class="btn btn-link text-decoration-none p-0" data-bs-toggle="collapse" href="#collapseMetadata" role="button" aria-expanded="false" aria-controls="collapseMetadata">
                                <i class="mdi mdi-chevron-right me-0"></i>
                                <span class="metadata-toggle">Metadaten einblenden</span>
                            </a>
                            <div class="collapse" id="collapseMetadata">
                                <pre class="bg-light p-3 rounded mb-0" style="font-size:0.85rem; overflow-x:auto;">
{{ rtrim(json_encode($incident->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}
</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-start mb-3">
                <a href="{{ route('admin.incidents.index') }}" class="btn btn-secondary me-2">
                    <i class="mdi mdi-arrow-left"></i> Zurück
                </a>
                @if(!$incident->resolved_at)
                    <button type="button"
                            class="btn btn-success"
                            wire:click="resolveIncident"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="resolveIncident">
                            <i class="mdi mdi-check"></i> Abschliessen
                        </span>
                        <span wire:loading wire:target="resolveIncident">
                            <i class="mdi mdi-loading mdi-spin"></i> Bitte warten
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var collapseMetadata = document.getElementById('collapseMetadata');
        var metadataToggle = document.querySelector('.metadata-toggle');
        var metadataIcon = document.querySelector('.mdi-chevron-right');

        collapseMetadata.addEventListener('show.bs.collapse', function() {
            metadataToggle.textContent = 'Metadaten ausblenden';
            metadataIcon.classList.replace('mdi-chevron-right', 'mdi-chevron-down');
        });

        collapseMetadata.addEventListener('hide.bs.collapse', function() {
            metadataToggle.textContent = 'Metadaten einblenden';
            metadataIcon.classList.replace('mdi-chevron-down', 'mdi-chevron-right');
        });
    });
</script>