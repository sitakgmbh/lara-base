<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-lg-5">
                <div class="card">
                    <div class="card-header py-4 text-center bg-primary">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="logo" height="22">
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center w-75 m-auto">
                            <h4 class="text-dark-50 fw-bold">Anmeldung</h4>
                            <p class="text-muted mb-4">Bitte Zugangsdaten eingeben, um fortzufahren.</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success text-center">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="login" autocomplete="off">
                            @csrf
                            <div class="mb-3">
                                <label for="username" class="form-label">Benutzername oder E-Mail</label>
                                <input wire:model="form.username" type="text" id="username" class="form-control" required autofocus>
                                @error('form.username') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <label for="password" class="form-label">Passwort</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-muted" tabindex="-1"><small>Passwort vergessen?</small></a>
                                    @endif
                                </div>
                                <div class="input-group input-group-merge">
                                    <input wire:model="form.password" type="password" id="password" class="form-control" required>
                                    <div class="input-group-text" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                                @error('form.password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input wire:model="form.remember" type="checkbox" class="form-check-input" id="remember">
                                    <label class="form-check-label" for="remember">Angemeldet bleiben</label>
                                </div>
                            </div>

                            <div class="mb-3 d-grid text-center">
                                <button class="btn btn-primary" type="submit">Anmelden</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>