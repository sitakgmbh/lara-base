@extends('lara-base::livewire.components.modals.base-modal')

@section('body')
    <div class="mb-2">
        <strong>Befehl:</strong> {{ $command }}
    </div>

    <div class="mb-2 small text-muted">
        Gestartet: {{ $started }}<br>
        Dauer: {{ $duration }}
    </div>

    <div class="mb-2">
        <strong>Antwort:</strong>
    </div>
    <pre class="bg-light p-2 rounded mb-0" style="max-height: 400px; overflow-y: auto; font-size: 0.75rem;">{{ $output }}</pre>
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Schliessen</button>
@endsection