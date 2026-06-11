<div wire:ignore.self
     class="modal fade"
     id="{{ $this->getModalId() }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-{{ $size }} modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header {{ $headerBg }}">
                <h5 class="modal-title text-white">{{ $title }}</h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        wire:click="closeModal"></button>
            </div>

			<div class="modal-body">
				{!! $message !!}
				@if($hint)
					<div class="mt-2 text-muted small">
						<i class="mdi mdi-information-outline"></i>
						{{ $hint }}
					</div>
				@endif
			</div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeModal">
                    {{ $cancelLabel }}
                </button>
                <button type="button" class="btn {{ $confirmClass }}" wire:click="confirm">
                    {{ $confirmLabel }}
                </button>
            </div>
        </div>
    </div>
</div>