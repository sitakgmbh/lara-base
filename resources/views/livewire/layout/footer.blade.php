<div>
    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
				<div class="col-md-6">
					{{ config('app.name') }} {{ \Sitakgmbh\LaraBase\Models\Setting::getValue('app_version', '0.0.0') }}
				</div>
                <div class="col-md-6">
                    <div class="text-md-end footer-links">
                        @php
                            $mode      = \Sitakgmbh\LaraBase\Models\Setting::getValue('show_help', 'off');
                            $routeName = request()->route()?->getName();
                            $user      = auth()->user();
                            $showHelp  = match ($mode) {
                                'all'   => true,
                                'admin' => $user && (
                                    (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
                                    (method_exists($user, 'hasRole') && $user->hasRole('admin'))
                                ),
                                default => false,
                            };
                        @endphp

                        @if($showHelp && $routeName && \Illuminate\Support\Facades\Route::has('help.viewer'))
                            <a href="#" onclick="
                                const width  = 900;
                                const height = 700;
                                const left   = Math.round((screen.width  - width)  / 2);
                                const top    = Math.round((screen.height - height) / 2);
                                window.open(
                                    '{{ route('help.viewer', ['key' => $routeName ?? 'dashboard']) }}',
                                    'help',
                                    `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
                                );
                                return false;">
                                Hilfe
                            </a>
                        @endif

						@if(\Sitakgmbh\LaraBase\Models\Setting::getValue('show_contact', true))
							<a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasHelp">
								{{ config('lara-base.contact.title', 'Kontakt') }}
							</a>
						@endif
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>