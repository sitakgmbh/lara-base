@php
    $positionClass = match($position) {
        'top'      => 'modal-top',
        'bottom'   => 'modal-bottom',
        'right'    => 'modal-right',
        'centered' => 'modal-dialog-centered',
        default    => '',
    };
    $scrollClass = $scrollable ? 'modal-dialog-scrollable' : '';
@endphp

<div wire:ignore.self
     class="modal fade"
     id="{{ $this->getModalId() }}"
     tabindex="-1"
     aria-labelledby="{{ $this->getModalId() }}Label"
     aria-hidden="true">

    <div class="modal-dialog {{ $scrollClass }} modal-{{ $size }} {{ $positionClass }}">
        <div class="modal-content">
            <div class="modal-header {{ $headerBg }}">
                <h5 class="modal-title {{ $headerText }}" id="{{ $this->getModalId() }}Label">{{ $title }}</h5>
                <button type="button"
                        class="btn-close {{ $headerText === 'text-white' ? 'btn-close-white' : '' }}"
                        wire:click="closeModal"></button>
            </div>

            <div class="modal-body">
                @yield('body')
            </div>

            @hasSection('footer')
                <div class="modal-footer">
                    @yield('footer')
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById(@js($this->getModalId()));
    if (!modalEl) return;

    modalEl.addEventListener('shown.bs.modal', () => {
        modalEl.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            bootstrap.Tooltip.getOrCreateInstance(el, { container: 'body' });
        });
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        modalEl.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            bootstrap.Tooltip.getInstance(el)?.dispose();
        });
    });
});
</script>