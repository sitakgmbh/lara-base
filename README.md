# sitakgmbh/laravel-base

Sitak GmbH – Laravel Base Package

---

## Neue App erstellen

### 1. Laravel installieren

```bash
composer create-project laravel/laravel meine-app
cd mein-projekt
```

### 2. `composer.json` anpassen

```json
"repositories": [
    {
        "type": "path",
        "url": "C:/Dev/lara-base",
        "options": { "symlink": true }
    }
],
"minimum-stability": "dev",
"prefer-stable": true
```

Für GitHub:
```json
{ "type": "vcs", "url": "git@github.com:sitakgmbh/lara-base.git" }
```

### 3. Package installieren

```bash
composer require sitakgmbh/lara-base
```

### 4. `.env` anpassen

`.env.example` kann als Vorlage genutzt werden.

### 5. Installer ausführen

```bash
php artisan laravel-base:install --force
```

Der Installer erledigt automatisch:
- User Model anpassen
- Standard User-Migration löschen
- Logging-Channels hinzufügen
- LDAP-Config erstellen
- routes/web.php anpassen
- Public Assets entpacken
- Spatie Permission publizieren
- laravel-base Config publizieren
- Migrationen ausführen
- Rollen und Admin-User anlegen

### Menü konfigurieren

In `config/laravel-base.php`:

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
        ],
    ],
],
```

---

## Logging

```php
\LaraLog::info('Nachricht');
\LaraLog::warning('Warnung', ['key' => 'value']);
\LaraLog::error('Fehler', ['exception' => $e->getMessage()]);
\LaraLog::debug('Debug');

// DB-Log
\LaraLog::db('auth', 'info', 'Login erfolgreich', ['user' => 'pase']);
\LaraLog::db('system', 'error', 'Job fehlgeschlagen');
```

Log-Kategorien erweitern in `config/laravel-base.php`:

```php
'log_categories' => [
    'sap'  => 'SAP',
    'ldap' => 'LDAP',
],
```

---

## Auth-Modi

| `AUTH_MODE` | Beschreibung |
|---|---|
| `local` | Username/Passwort gegen lokale DB |
| `ldap` | LDAP – SSO via `REMOTE_USER` oder Formular als Fallback |

---

## Views überschreiben

```bash
php artisan vendor:publish --tag=laravel-base-views --force
```

Views landen in `resources/views/vendor/laravel-base/` und können angepasst werden.
