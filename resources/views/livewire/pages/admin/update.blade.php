<div>

	@php
		$alertClass = match($status) {
			'current', 'done'  => 'alert-success',
			'available'        => 'alert-info',
			'error'            => 'alert-danger',
			default            => 'alert-dark',
		};
	@endphp
	<div class="alert {{ $alertClass }} d-flex align-items-center gap-2 mb-4">
		@if(in_array($status, ['checking', 'updating']))
			<div class="spinner-border spinner-border-sm" role="status"></div>
		@elseif(in_array($status, ['current', 'done']))
			<i class="mdi mdi-check-circle"></i>
		@elseif($status === 'available')
			<i class="mdi mdi-update"></i>
		@elseif($status === 'error')
			<i class="mdi mdi-alert-circle"></i>
		@endif
		<span>{{ $statusMessage }}</span>
	</div>

    @if(in_array($status, ['current', 'available', 'done']))
		<div class="table-responsive mb-4">
			<table class="table table-sm mb-0 align-middle">
				<tbody>
					<tr>
						<td class="text-muted text-nowrap" style="width: 180px;">Installierte Version</td>
						<td class="text-nowrap"><span class="badge bg-secondary">{{ $currentVersion }}</span></td>
					</tr>
					<tr>
						<td class="text-muted text-nowrap">Verfügbare Version</td>
						<td class="text-nowrap">
							<span class="badge {{ $status === 'available' ? 'bg-info' : 'bg-success' }}">
								{{ $latestVersion }}
							</span>
							@if($latestDate)
								<span class="text-muted ms-2 small">{{ $latestDate }}</span>
							@endif
						</td>
					</tr>
					@if($checkedAt)
						<tr>
							<td class="text-muted text-nowrap">Zuletzt geprüft</td>
							<td class="text-muted text-nowrap small">{{ $checkedAt }}</td>
						</tr>
					@endif
				</tbody>
			</table>
		</div>
    @endif

    @if($status === 'error' && $errorMessage)
        <div class="alert alert-danger small font-monospace">
            {{ $errorMessage }}
        </div>
    @endif

	@if($status === 'done')
		@if($updatedCount > 0)
			<div class="alert alert-secondary small">
				{{ $updatedCount }} {{ $updatedCount === 1 ? 'Datei' : 'Dateien' }} aktualisiert.
			</div>
		@else
			<div class="alert alert-secondary small">Keine Dateien geändert.</div>
		@endif
	@endif

    @if($status === 'done' && $updatedCount === 0)
        <div class="alert alert-secondary small">Keine Dateien geändert.</div>
    @endif

    <div class="d-flex gap-2">
        @if($status === 'available')
            <button wire:click="installUpdate" wire:loading.attr="disabled" class="btn btn-primary">
                <span wire:loading.remove wire:target="installUpdate">
                    Update installieren
                </span>
                <span wire:loading wire:target="installUpdate">
                    <span class="spinner-border spinner-border-sm me-1"></span> Installiere...
                </span>
            </button>
        @endif

        @if(in_array($status, ['current', 'done', 'error']))
            <button wire:click="checkForUpdate" wire:loading.attr="disabled" class="btn btn-light btn-sm">
                <span wire:loading.remove wire:target="checkForUpdate">
                    <i class="mdi mdi-refresh me-1"></i> Erneut prüfen
                </span>
                <span wire:loading wire:target="checkForUpdate">
                    <span class="spinner-border spinner-border-sm me-1"></span> Prüfe...
                </span>
            </button>
        @endif
    </div>

</div>