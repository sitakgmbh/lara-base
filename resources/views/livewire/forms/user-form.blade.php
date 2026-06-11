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
                        <label for="username" class="form-label">Benutzername</label>
                        <input type="text" id="username" wire:model.defer="form.username"
                               class="form-control @error('form.username') is-invalid @enderror"
                               @if(!$form->isCreate || $isLdap) readonly disabled @endif>
                        @error('form.username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label">Rolle</label>
                        <select id="role" wire:model.defer="form.role"
                                class="form-select @error('form.role') is-invalid @enderror"
                                @if($isLdap) disabled @endif>
                            @foreach($roles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                        @error('form.role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

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
                    <p class="mb-0"><strong>Passwort</strong></p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">{{ $form->isCreate ? 'Passwort' : 'Neues Passwort' }}</label>
                            <input type="password" id="password" wire:model.defer="form.password"
                                   class="form-control @error('form.password') is-invalid @enderror"
                                   @if(!$form->isCreate) placeholder="Leer lassen, um Passwort nicht zu ändern" @endif>
                            @error('form.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">{{ $form->isCreate ? 'Passwort wiederholen' : 'Neues Passwort wiederholen' }}</label>
                            <input type="password" id="password_confirmation" wire:model.defer="form.password_confirmation"
                                   class="form-control @error('form.password_confirmation') is-invalid @enderror">
                            @error('form.password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header text-white bg-primary py-1">
                    <p class="mb-0"><strong>Status</strong></p>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch form-switch-lg">
                        <input type="checkbox" class="form-check-input @error('form.is_enabled') is-invalid @enderror"
                               id="is_enabled" wire:model="form.is_enabled">
                        <label for="is_enabled" class="form-check-label">Account aktivieren</label>
                        @error('form.is_enabled') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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