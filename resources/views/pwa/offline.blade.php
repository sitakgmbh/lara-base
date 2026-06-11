<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline – {{ config('app.name') }}</title>
    <meta name="theme-color" content="{{ config('lara-base.pwa.manifest.theme_color', '#000000') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: {{ config('lara-base.pwa.manifest.background_color', '#f8f9fa') }};
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 420px;
            width: 100%;
        }

        .icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: block;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.65rem 1.75rem;
            background: {{ config('lara-base.pwa.manifest.theme_color', '#0d6efd') }};
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="card">
        <span class="icon">📡</span>
        <h1>Keine Verbindung</h1>
        <p>
            Du bist offline. Bitte überprüfe deine Internetverbindung
            und versuche es erneut.
        </p>
        <button class="btn" onclick="window.location.reload()">
            Erneut versuchen
        </button>
    </div>
</body>
</html>