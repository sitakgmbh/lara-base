<div>
    <form wire:submit.prevent="save">
        <div class="card">
            <div class="card-body">
                <div class="border rounded p-3 bg-body-tertiary">
                    <div class="form-check form-switch mb-1">
                        <input type="checkbox" class="form-check-input" id="darkmodeSwitch"
                               wire:model="darkmode_enabled">
                        <label for="darkmodeSwitch" class="form-check-label fw-semibold">
                            Dark Mode
                        </label>
                    </div>
                    <small class="text-muted d-block">
                        Aktiviere ein dunkles Farbschema. Wird beim nächsten Login angewendet.
                    </small>
                </div>
            </div>
        </div>

        @if(session()->has('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Speichern</button>
        </div>
    </form>
</div>