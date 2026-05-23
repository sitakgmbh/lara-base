<div>
    <form wire:submit.prevent="save">
        @php $isLdap = $form->auth_type === 'ldap'; @endphp

        <div class="card mb-3">
            <div class="card-header bg-primary text-white py-1">
                <p class="mb-0"><strong>Stammdaten</strong></p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="firstname" class="form-label">Vorname</label>
                        <input type="text" id="firstname" wire:model.defer="form.firstname"
                               class="form-control @error('form.firstname') is-invalid @enderror"
                               @if($isLdap) disabled @endif>
                        @error('form.firstname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="lastname" class="form-label">Nachname</label>
                        <input type="text" id="lastname" wire:model.defer="form.lastname"
                               class="form-control @error('form.lastname') is-invalid @enderror"
                               @if($isLdap) disabled @endif>
                        @error('form.lastname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-Mail</label>
                        <input type="email" id="email" wire:model.defer="form.email"
                               class="form-control @error('form.email') is-invalid @enderror"
                               @if($isLdap) disabled @endif>
                        @error('form.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        @if(!$isLdap)
            <div class="card mb-3">
                <div class="card-header text-white bg-primary py-1">
                    <p class="mb-0"><strong>Passwort ändern</strong></p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="current_password" class="form-label">Aktuelles Passwort</label>
                            <input type="password" id="current_password" wire:model.defer="form.current_password"
                                   class="form-control @error('form.current_password') is-invalid @enderror">
                            @error('form.current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Neues Passwort</label>
                            <input type="password" id="password" wire:model.defer="form.password"
                                   class="form-control @error('form.password') is-invalid @enderror"
                                   placeholder="Leer lassen, um Passwort nicht zu ändern">
                            @error('form.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Neues Passwort wiederholen</label>
                            <input type="password" id="password_confirmation" wire:model.defer="form.password_confirmation"
                                   class="form-control @error('form.password_confirmation') is-invalid @enderror">
                            @error('form.password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(session()->has('success'))
            <div class="mb-3">
                <div class="alert alert-success mb-0">
                    <strong>Erfolg – </strong> {{ session('success') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-3">
                <div class="alert alert-danger mb-0">
                    <strong>Fehler – </strong> Bitte Eingaben überprüfen.
                </div>
            </div>
        @endif

        <div class="mt-3">
            <button type="submit" class="btn btn-primary"
                    @if($isLdap) disabled @endif
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Speichern</span>
                <span wire:loading wire:target="save">
                    <i class="mdi mdi-loading mdi-spin me-1"></i>Bitte warten...
                </span>
            </button>
        </div>
    </form>
</div>