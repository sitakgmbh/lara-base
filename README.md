# Laravel Base Package

Erste Schritte

---

## Installation

### 1. Laravel installieren

```bash
composer create-project laravel/laravel a-cool-project
cd a-cool-project
```

### 2. Package installieren

```bash
composer require sitakgmbh/lara-base
```

### 3. `.env` anpassen

`.env.example` kann als Vorlage genutzt werden.

### 4. Installer ausführen

```bash
php artisan lara-base:install
```

Der Installer erledigt automatisch:
- User Model anpassen
- Standard User-Migration löschen
- Logging-Channels hinzufügen
- LDAP-Config erstellen
- routes/web.php anpassen
- Public Assets entpacken
- Spatie Permission publizieren
- lara-base Config publizieren
- Migrationen ausführen
- Rollen und Admin-User anlegen

Bei bestehenden Dateien `--force` verwenden:

```bash
php artisan lara-base:install --force
```

---

## Menü konfigurieren

In `config/lara-base.php`:

```php
'menu' => [
    [
        'title' => 'Navigation',
        'items' => [
            [
                'label' => 'Dashboard',
                'icon'  => 'mdi mdi-view-dashboard',
                'url'   => '/dashboard',
            ],
            [
                'label' => 'Berichte',
                'icon'  => 'mdi mdi-chart-bar',
                'children' => [
                    [
                        'label' => 'Übersicht',
                        'url'   => '/reports',
                    ],
                    [
                        'label' => 'Monatsbericht',
                        'url'   => '/reports/monthly',
                    ],
                ],
            ],
        ],
    ],
],
```

---

## PWA

In `.env`:

```
PWA_ENABLED=true
PWA_NAME="${APP_NAME}"
PWA_THEME_COLOR="#1D4E8F"
```

Icons sind in `public/icons-pwa/` abgelegt.

### Push-Benachrichtigungen

```bash
php artisan webpush:vapid
```

In `.env`:

```
PWA_PUSH_ENABLED=true
PWA_PUSH_QUEUE=false
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
```

Kategorien in `config/lara-base.php`:

```php
'push' => [
    'categories' => [
        [
            'key'   => 'system',
            'label' => 'System-Meldungen',
            'roles' => ['admin'],
        ],
        [
            'key'   => 'incidents',
            'label' => 'Incidents',
            'roles' => ['admin'],
        ],
    ],
],
```

Notification versenden:

```php
LaraNotify::send('incidents', 'Titel', 'Nachricht', '/url');
LaraNotify::sendTo($user, 'system', 'Titel', 'Nachricht');
```

Test:

```bash
php artisan pwa:test-push --sync
php artisan pwa:test-push --category=incidents --sync
```

---

## Logging

```php
\LaraLog::info('Nachricht');
\LaraLog::warning('Warnung', ['key' => 'value']);
\LaraLog::error('Fehler', ['exception' => $e->getMessage()]);
\LaraLog::debug('Debug');

// DB-Log
\LaraLog::db('auth', 'info', 'Login erfolgreich', ['user' => 'peter.lustig']);
\LaraLog::db('system', 'error', 'Job fehlgeschlagen');
```

Log-Kategorien erweitern in `config/lara-base.php`:

```php
'log_categories' => [
    'sap'  => 'SAP',
    'ldap' => 'LDAP',
],
```

---

## Auth-Modi

| `APP_AUTH_MODE` | Beschreibung |
|---|---|
| `local` | Username/Passwort gegen lokale DB |
| `ldap` | LDAP – SSO via `REMOTE_USER` oder Formular als Fallback |

---

## Views überschreiben

```bash
php artisan vendor:publish --tag=lara-base-views --force
```

Views landen in `resources/views/vendor/lara-base/` und können angepasst werden.
