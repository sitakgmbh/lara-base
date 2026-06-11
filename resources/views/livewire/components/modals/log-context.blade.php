<div wire:ignore.self
     class="modal fade"
     id="{{ $this->getModalId() }}"
     tabindex="-1"
     aria-hidden="true">

    @php
        $positionClass = 'modal-dialog-centered';
        $scrollClass   = 'modal-dialog-scrollable';
    @endphp

    <div class="modal-dialog {{ $scrollClass }} modal-{{ $size }} {{ $positionClass }}">
        <div class="modal-content">
            <div class="modal-header {{ $headerBg }}">
                <h5 class="modal-title {{ $headerText }}">{{ $title }}</h5>
                <button type="button"
                        class="btn-close {{ $headerText === 'text-white' ? 'btn-close-white' : '' }}"
                        wire:click="closeModal"></button>
            </div>

            <div class="modal-body">
                @if($log)
                    <p><strong>Nachricht:</strong> {{ $log->message }}</p>

                    <h6 class="mt-3">Kontext:</h6>
                    <pre class="bg-light p-3 rounded"
                         style="max-height: 400px; overflow-y: auto; font-size: 0.85rem; white-space: pre;">
{!! json_encode(json_decode($log->context, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
                    </pre>

                    <ul class="list-unstyled mb-0">
                        <li><strong>Level:</strong> {{ $log->level->label() }}</li>
                        <li><strong>Kategorie:</strong> {{ $log->category->label() }}</li>
                        <li><strong>Erstellt am:</strong> {{ $log->created_at->format('d.m.Y H:i:s') }}</li>
                    </ul>

                    @if($permalink)
                        <div class="mt-1">
                            <a href="{{ $permalink }}" target="_blank" class="text-decoration-none">
                                <i class="mdi mdi-link-variant"></i> Permalink
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeModal">Schliessen</button>
            </div>
        </div>
    </div>
</div>