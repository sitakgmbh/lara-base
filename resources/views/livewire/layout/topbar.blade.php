<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-lg-2 gap-1">
            <div class="logo-topbar">
                <a href="{{ url('/') }}" class="logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo-sm.png') }}" alt="small logo">
                    </span>
                </a>
                <a href="{{ url('/') }}" class="logo-dark">
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/logo-dark.png') }}" alt="dark logo">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo-dark-sm.png') }}" alt="small logo">
                    </span>
                </a>
            </div>
            <button class="button-toggle-menu">
                <i class="mdi mdi-menu"></i>
            </button>
            <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <div class="lines">
                    <span></span><span></span><span></span>
                </div>
            </button>
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-3">

            {{-- Test-Modus Badge --}}
            @if(config('lara-base.test_mode', false))
                <li class="d-inline-block">
                    <div class="badge bg-warning text-dark">Test-Modus aktiv</div>
                </li>
            @endif

            {{-- Incidents Notification (nur Admin) --}}
            @role('admin')
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                    @php
                        if ($openIncidents->contains(fn($i) => $i->priority === 'high')) {
                            $iconClass = 'text-danger';
                            $iconName  = 'ri-alert-line';
                        } elseif ($openIncidents->contains(fn($i) => $i->priority === 'medium')) {
                            $iconClass = 'text-warning';
                            $iconName  = 'ri-error-warning-line';
                        } elseif ($openIncidents->isNotEmpty()) {
                            $iconClass = 'text-info';
                            $iconName  = 'ri-information-line';
                        } else {
                            $iconClass = 'text-success';
                            $iconName  = 'ri-checkbox-circle-line';
                        }
                    @endphp
                    <i class="{{ $iconName }} font-22 {{ $iconClass }}"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg py-0">
                    <div class="p-2 border-top-0 border-start-0 border-end-0 border">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0 font-14 fw-semibold">Offene Incidents: {{ $openIncidents->count() }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="px-2" style="max-height: 300px;" data-simplebar>
                        @forelse($openIncidents as $incident)
                            <a href="{{ route('admin.incidents.show', $incident->id) }}"
                               class="dropdown-item p-0 notify-item card unread-noti shadow-none mb-2 {{ $loop->first ? 'mt-2' : '' }}">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="notify-icon bg-{{ $incident->priority === 'high' ? 'danger' : ($incident->priority === 'medium' ? 'warning' : 'info') }}">
                                                <i class="mdi mdi-alert-circle-outline"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h5 class="noti-item-title fw-semibold font-14"
                                                style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:250px;">
                                                {{ \Illuminate\Support\Str::limit($incident->title, 28) }}
                                            </h5>
                                            <small class="fw-normal text-muted">{{ $incident->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-3 text-center text-muted">
                                <i class="mdi mdi-check-circle-outline font-36"></i>
                                <p class="mb-0">Keine offenen Incidents</p>
                            </div>
                        @endforelse
                    </div>
                    <a href="{{ route('admin.incidents.index') }}" class="dropdown-item text-center text-primary notify-item border-top py-2">
                        Alle anzeigen
                    </a>
                </div>
            </li>
            @endrole

			<!-- Theme Mode -->
			<li class="d-inline-block">
				<div class="nav-link cursor-pointer" wire:click="toggleDarkMode">
					<i class="ri-moon-line font-22"></i>
				</div>
			</li>

            <!-- Fullscreen -->
            <li class="d-none d-md-inline-block">
                <a class="nav-link" href="#" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line font-22"></i>
                </a>
            </li>

            <!-- User -->
			<li class="dropdown">
				<a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button">
					<span class="account-user-avatar">
						<img src="{{ asset('assets/images/users/avatar-1.jpg') }}"
							 alt="Profilbild" width="32" height="32"
							 class="rounded-circle" style="object-fit: cover;">
					</span>
					<span class="d-lg-flex flex-column gap-1 d-none">
						<h5 class="my-0">{{ Auth::user()->firstname }}</h5>
						<h6 class="my-0 fw-normal">{{ Auth::user()->username }}</h6>
					</span>
				</a>
				<div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
					@if(\Illuminate\Support\Facades\Route::has('profile.edit'))
						<a href="{{ route('profile.edit') }}" class="dropdown-item">
							<i class="mdi mdi-account-circle me-1"></i>
							<span>Profil</span>
						</a>
					@endif

					@if(\Illuminate\Support\Facades\Route::has('profile.settings'))
						<a href="{{ route('profile.settings') }}" class="dropdown-item">
							<i class="mdi mdi-cog me-1"></i>
							<span>Einstellungen</span>
						</a>
					@endif

					@if(config('lara-base.auth.mode') === 'local')
						<a href="#" class="dropdown-item" wire:click.prevent="logout">
							<i class="mdi mdi-logout me-1"></i>
							<span>Abmelden</span>
						</a>
					@endif
				</div>
			</li>
        </ul>
    </div>
	
	@include('lara-base::livewire.layout.partials.contact-offcanvas')
	
</div>