<div wire:ignore.self
     class="modal fade"
     id="{{ $this->getModalId() }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content modal-filled {{ $color }}">
            <div class="modal-body p-4">
                <div class="text-center text-white">
                    <i class="{{ $icon }} h1"></i>
                    <h4 class="mt-2">{{ $headline }}</h4>
                    <p class="mt-3">{{ $message }}</p>
                    <button type="button" class="btn btn-light my-2" wire:click="closeModal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>