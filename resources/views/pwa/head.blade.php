@if(config('lara-base.pwa.enabled', false))
{{-- PWA Manifest --}}
<link rel="manifest" href="{{ route('pwa.manifest') }}">

{{-- Theme Color (Browser-Toolbar) --}}
<meta name="theme-color" content="{{ config('lara-base.pwa.manifest.theme_color', '#000000') }}">

{{-- Apple Touch Icon --}}
<link rel="apple-touch-icon" href="{{ asset('icons-pwa/icon-192x192.png') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('lara-base.pwa.manifest.short_name', config('app.name')) }}">

{{-- Service Worker Registration --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(function (reg) {
                    // SW registriert: reg.scope
                })
                .catch(function (err) {
                    console.warn('[PWA] SW registration failed:', err);
                });
        });
    }
</script>
@endif