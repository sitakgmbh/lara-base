<div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.addEventListener('show-bs-modal', function (event) {
                const id = event.detail.id;
                const el = document.getElementById(id);

                if (!el) {
                    console.error("Modal nicht gefunden:", id);
                    return;
                }

                const modal = bootstrap.Modal.getOrCreateInstance(el, {
                    backdrop: event.detail.backdrop ?? true,
                    keyboard: event.detail.keyboard ?? true,
                });

                modal.show();
            });

            window.addEventListener('hide-bs-modal', function (event) {
                const id = event.detail.id;
                const el = document.getElementById(id);
                if (!el) return;

                const modal = bootstrap.Modal.getInstance(el);

                if (modal) {
                    modal.hide();
                    Livewire.dispatch('modal-closed', { id: id });
                }
            });
        });
    </script>
</div>