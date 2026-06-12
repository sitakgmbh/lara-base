<div wire:ignore.self
     class="modal fade"
     id="{{ $this->getModalId() }}"
     tabindex="-1"
     aria-labelledby="{{ $this->getModalId() }}Label"
     aria-hidden="true">

    <div class="modal-dialog modal-{{ $size }} modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header {{ $headerBg }}">
                <h5 class="modal-title {{ $headerText }}" id="{{ $this->getModalId() }}Label">
                    <i class="mdi mdi-bell-outline me-1"></i>
                    {{ $title }}
                </h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        wire:click="closeModal"></button>
            </div>

            <div class="modal-body">

                {{-- Nicht unterstützt --}}
                <div id="push-not-supported" class="d-none">
                    <div class="alert alert-warning mb-0">
                        <i class="mdi mdi-alert-outline me-1"></i>
                        Dein Browser unterstützt keine Push-Benachrichtigungen.
                    </div>
                </div>

                {{-- Blockiert --}}
                <div id="push-blocked" class="d-none">
                    <div class="alert alert-danger mb-0">
                        <i class="mdi mdi-bell-cancel-outline me-1"></i>
                        Benachrichtigungen wurden blockiert. Bitte erlaube sie in den Browser-Einstellungen und lade die Seite neu.
                    </div>
                </div>

                {{-- Laden --}}
                <div id="push-loading">
                    <span class="text-muted">
                        <i class="mdi mdi-loading mdi-spin me-1"></i> Lade...
                    </span>
                </div>

                {{-- Nicht registriert --}}
                <div id="push-unregistered" class="d-none">
                    <p class="text-muted mb-3">Dieses Gerät ist noch nicht registriert.</p>
                    <button id="btn-register" type="button" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-bell-plus-outline me-1"></i>
                        Gerät registrieren
                    </button>
                </div>

                {{-- Registriert --}}
                <div id="push-registered" class="d-none">

                    {{-- Gerätename --}}
                    <div id="current-device-name" class="d-flex align-items-center gap-2 mb-2">
                        <small class="text-muted" id="device-name-display"></small>
                        <button type="button" id="btn-edit-name" class="btn btn-link btn-sm p-0 text-muted">
                            <i class="mdi mdi-pencil-outline"></i>
                        </button>
                    </div>
                    <div id="device-name-edit" class="d-none mb-2">
                        <div class="input-group input-group-sm">
                            <input type="text" id="device-name-input" class="form-control" maxlength="100">
                            <button type="button" id="btn-save-name" class="btn btn-primary">
                                <i class="mdi mdi-check"></i>
                            </button>
                            <button type="button" id="btn-cancel-name" class="btn btn-secondary">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                    </div>

                    <p class="text-muted mb-2 small">Benachrichtigungen für dieses Gerät:</p>

                    <div class="d-flex flex-column gap-2 mb-3">
                        @foreach($pushCategories as $cat)
                            <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2">
                                <span>
                                    <i class="mdi mdi-bell-outline me-1 text-muted"></i>
                                    {{ $cat['label'] }}
                                </span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input push-toggle"
                                           type="checkbox"
                                           role="switch"
                                           id="push-toggle-{{ $cat['key'] }}"
                                           data-category="{{ $cat['key'] }}"
                                           disabled>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button id="btn-unregister" type="button" class="btn btn-outline-danger btn-sm">
                        <i class="mdi mdi-bell-off-outline me-1"></i>
                        Gerät löschen
                    </button>
                </div>

                {{-- Andere Geräte --}}
                <div id="push-other-devices" class="d-none">
                    <hr class="my-3">
                    <p class="text-muted mb-2 small"><strong>Andere Geräte</strong></p>
                    <div id="push-other-devices-list" class="d-flex flex-column gap-2"></div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" wire:click="closeModal">
                    Schliessen
                </button>
            </div>

        </div>
    </div>
</div>

@if(config('lara-base.pwa.push.enabled'))
<script>
(function () {
    const modalEl = document.getElementById(@js($this->getModalId()));
    if (! modalEl) return;

    const VAPID_PUBLIC_KEY  = @json(config('lara-base.pwa.push.vapid.public_key'));
    const CSRF_TOKEN        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const URL_STATUS        = '{{ route("pwa.subscriptions") }}';
    const URL_DEVICES       = '{{ route("pwa.devices") }}';
    const URL_SUBSCRIBE     = '{{ route("pwa.subscribe") }}';
    const URL_UNSUBSCRIBE   = '{{ route("pwa.unsubscribe") }}';
    const URL_DEVICE_DEL    = '{{ route("pwa.device.destroy") }}';
    const URL_DEVICE_UPDATE = '{{ route("pwa.device.update") }}';
    const CATEGORY_LABELS   = @json(array_column($pushCategories, 'label', 'key'));

    let currentEndpoint = null;
    let initialized     = false;

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw     = window.atob(base64);
        return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
    }

    function showSection(id) {
        ['push-not-supported', 'push-blocked', 'push-loading', 'push-unregistered', 'push-registered']
            .forEach(s => document.getElementById(s)?.classList.add('d-none'));
        document.getElementById(id)?.classList.remove('d-none');
    }

    async function apiCall(url, method = 'GET', body = null) {
        const opts = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept':       'application/json',
            },
        };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(url, opts);
        if (! res.ok) throw new Error(await res.text());
        return res.json();
    }

    async function loadCategoryStatus(endpoint) {
        const url    = `${URL_STATUS}?endpoint=${encodeURIComponent(endpoint)}`;
        const status = await apiCall(url);

        document.querySelectorAll('.push-toggle').forEach(toggle => {
            const category  = toggle.dataset.category;
            toggle.disabled = false;
            toggle.checked  = status[category] === true;
        });
    }

    async function loadDeviceName(endpoint) {
        const devices = await apiCall(URL_DEVICES);
        const device  = devices.find(d => d.endpoint === endpoint);
        if (device) {
            document.getElementById('device-name-display').textContent = device.device_name;
        }
    }

    async function loadOtherDevices(myEndpoint) {
        const devices   = await apiCall(URL_DEVICES);
        const others    = devices.filter(d => d.endpoint !== myEndpoint);
        const container = document.getElementById('push-other-devices');
        const list      = document.getElementById('push-other-devices-list');

        if (others.length === 0) {
            container.classList.add('d-none');
            return;
        }

        list.innerHTML = '';

        others.forEach(device => {
            const row  = document.createElement('div');
            row.className = 'd-flex align-items-center justify-content-between border rounded px-3 py-2';
            row.innerHTML = `
                <span>
                    <i class="mdi mdi-devices me-1 text-muted"></i>
                    <strong>${device.device_name}</strong>
                </span>
                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-device"
                        data-endpoint="${device.endpoint}">
                    <i class="mdi mdi-delete-outline"></i>
                </button>
            `;
            list.appendChild(row);
        });

        list.querySelectorAll('.btn-remove-device').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                try {
                    await apiCall(URL_DEVICE_DEL, 'DELETE', { endpoint: btn.dataset.endpoint });
                    btn.closest('div.border').remove();
                    if (list.querySelectorAll('.border').length === 0) {
                        container.classList.add('d-none');
                    }
                } catch (err) {
                    console.error('[PWA] Device remove failed:', err);
                    btn.disabled = false;
                }
            });
        });

        container.classList.remove('d-none');
    }

    async function getOrCreateSubscription() {
        const reg = await navigator.serviceWorker.ready;
        let sub   = await reg.pushManager.getSubscription();

        if (! sub) {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                showSection('push-blocked');
                return null;
            }
            sub = await reg.pushManager.subscribe({
                userVisibleOnly:      true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
            });
        }

        return sub;
    }

    function bindToggles(endpoint, subJson) {
        document.querySelectorAll('.push-toggle').forEach(toggle => {
            const fresh = toggle.cloneNode(true);
            toggle.replaceWith(fresh);
        });

        document.querySelectorAll('.push-toggle').forEach(toggle => {
            toggle.addEventListener('change', async () => {
                toggle.disabled = true;
                const category  = toggle.dataset.category;
                try {
                    if (toggle.checked) {
                        await apiCall(URL_SUBSCRIBE, 'POST', { ...subJson, category });
                    } else {
                        await apiCall(URL_UNSUBSCRIBE, 'DELETE', { endpoint, category });
                    }
                } catch (err) {
                    console.error('[PWA] Toggle failed:', err);
                    toggle.checked = ! toggle.checked;
                }
                toggle.disabled = false;
            });
        });
    }

	function detectDeviceName() {
		const ua      = navigator.userAgent;
		const browser = ua.includes('Firefox') ? 'Firefox' : ua.includes('Edg') ? 'Edge' : ua.includes('OPR') ? 'Opera' : ua.includes('Chrome') ? 'Chrome' : 'Safari';
		const os      = ua.includes('iPhone') ? 'iPhone' : ua.includes('iPad') ? 'iPad' : ua.includes('Android') ? 'Android' : ua.includes('Windows') ? 'Windows' : ua.includes('Macintosh') ? 'macOS' : 'Gerät';
		return `${browser} auf ${os}`;
	}

	async function loadDeviceName(endpoint) {
		const devices = await apiCall(URL_DEVICES);
		const device  = devices.find(d => d.endpoint === endpoint);
		// DB-Eintrag vorhanden → DB-Name, sonst clientseitig ermitteln
		document.getElementById('device-name-display').textContent = device?.device_name ?? detectDeviceName();
	}

	async function initPush() {
		if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
			showSection('push-not-supported');
			return;
		}
		if (Notification.permission === 'denied') {
			showSection('push-blocked');
			return;
		}
		const reg = await navigator.serviceWorker.ready;
		const sub = await reg.pushManager.getSubscription();
		if (sub) {
			currentEndpoint = sub.endpoint;
			showSection('push-registered');
			await loadCategoryStatus(currentEndpoint);
			bindToggles(currentEndpoint, sub.toJSON());
			await loadDeviceName(currentEndpoint);
			await loadOtherDevices(currentEndpoint);
		} else {
			showSection('push-unregistered');
			await loadOtherDevices(null);
		}
	}

	// Registrieren
	document.getElementById('btn-register')?.addEventListener('click', async () => {
		const sub = await getOrCreateSubscription();
		if (! sub) return;
		currentEndpoint = sub.endpoint;
		showSection('push-registered');
		await loadCategoryStatus(currentEndpoint);
		bindToggles(currentEndpoint, sub.toJSON());
		// Neu: clientseitig anzeigen ohne DB-Call
		document.getElementById('device-name-display').textContent = detectDeviceName();
		await loadOtherDevices(currentEndpoint);
	});

    // Gerät löschen
    document.getElementById('btn-unregister')?.addEventListener('click', async () => {
        if (! currentEndpoint) return;
        try {
            await apiCall(URL_DEVICE_DEL, 'DELETE', { endpoint: currentEndpoint });
            const reg = await navigator.serviceWorker.ready;
            const sub = await reg.pushManager.getSubscription();
            if (sub) await sub.unsubscribe();
            currentEndpoint = null;
            showSection('push-unregistered');
            await loadOtherDevices(null);
        } catch (err) {
            console.error('[PWA] Unregister failed:', err);
        }
    });

    // Stift-Icon — Name bearbeiten
    document.getElementById('btn-edit-name')?.addEventListener('click', () => {
        const name = document.getElementById('device-name-display').textContent;
        document.getElementById('device-name-input').value = name;
        document.getElementById('current-device-name').classList.add('d-none');
        document.getElementById('device-name-edit').classList.remove('d-none');
        document.getElementById('device-name-input').focus();
    });

    // Name speichern
    document.getElementById('btn-save-name')?.addEventListener('click', async () => {
        const name = document.getElementById('device-name-input').value.trim();
        if (! name) return;
        try {
            await apiCall(URL_DEVICE_UPDATE, 'PATCH', {
                endpoint:    currentEndpoint,
                device_name: name,
            });
            document.getElementById('device-name-display').textContent = name;
            document.getElementById('current-device-name').classList.remove('d-none');
            document.getElementById('device-name-edit').classList.add('d-none');
        } catch (err) {
            console.error('[PWA] Name update failed:', err);
        }
    });

    // Abbrechen
    document.getElementById('btn-cancel-name')?.addEventListener('click', () => {
        document.getElementById('current-device-name').classList.remove('d-none');
        document.getElementById('device-name-edit').classList.add('d-none');
    });

    // Modal beim Öffnen initialisieren
    modalEl.addEventListener('shown.bs.modal', () => {
        if (! initialized) {
            initialized = true;
            initPush();
        }
    });

	// Reset beim Schliessen
	modalEl.addEventListener('hidden.bs.modal', () => {
		initialized = false;
	});

})();
</script>
@endif