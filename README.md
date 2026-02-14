# Laravel Terminal

A powerful web-based terminal for Laravel applications. Execute artisan commands, manage packages, run shell commands, and more — all from your browser.

## Features

- **Laravel Artisan** — Run any artisan command with one click
- **Composer & NPM** — Install/remove packages, run builds
- **Database** — Migrations, seeders, DB info
- **Git** — Status, log, branches, push/pull
- **Cache Management** — Clear/build all caches
- **Make Commands** — Generate controllers, models, migrations, etc.
- **Shell Commands** — ls, pwd, df, free, uname, and more
- **CDN Library** — Copy CDN links for Alpine, Vue, Tailwind, Bootstrap, etc.
- **Error Logs Viewer** — Browse, search, and clear Laravel error logs
- **Auto-suggestions** — Type and get instant command suggestions
- **Command History** — Navigate with arrow keys
- **Safe Mode** — Dangerous commands are blocked by default

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

## Installation

### 1. Add the package path to your project's `composer.json`

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/abdurrahman/laravel-terminal"
        }
    ]
}
```

### 2. Require the package

```bash
composer require abdurrahman/laravel-terminal
```

### 3. Publish the config (optional)

```bash
php artisan vendor:publish --tag=terminal-config
```

### 4. Publish the views (optional, for customization)

```bash
php artisan vendor:publish --tag=terminal-views
```

## Configuration

After publishing, edit `config/terminal.php`:

```php
return [
    // URL prefix (e.g., /terminal, /admin/terminal)
    'prefix' => 'terminal',

    // Middleware applied to all terminal routes
    'middleware' => ['web', 'auth'],

    // Blade layout to extend
    'layout' => 'layouts.admin',

    // Commands that are blocked for security
    'blocked_commands' => [
        'rm -rf', 'mkfs', 'dd if=', ':(){', 'chmod -R 777',
        'wget', '> /dev/sda', 'shutdown', 'reboot', 'init 0',
        'init 6', 'kill -9', 'killall', 'format', 'fdisk',
        'passwd', 'useradd', 'userdel', 'visudo', 'crontab -r',
    ],

    // Allowed artisan commands
    'allowed_artisan_commands' => [
        'list', 'about', 'env', 'up', 'down', 'optimize',
        'optimize:clear', 'key:generate', 'storage:link',
        'cache:clear', 'config:clear', 'config:cache',
        'route:clear', 'route:cache', 'route:list',
        'view:clear', 'view:cache', 'event:clear', 'event:cache',
        'event:list', 'migrate', 'migrate:status', 'migrate:rollback',
        'migrate:reset', 'migrate:fresh', 'db:seed', 'db:show', 'db:table',
        'schedule:list', 'schedule:run', 'queue:work', 'queue:retry',
        'queue:flush', 'queue:listen', 'vendor:publish',
        'config:show', 'make:controller', 'make:model', 'make:migration',
        'make:middleware', 'make:request', 'make:seeder', 'make:factory',
        'make:command', 'make:mail', 'make:notification', 'make:event',
        'make:listener', 'make:job', 'make:policy', 'make:resource',
        'make:component', 'make:livewire', 'make:test', 'make:rule',
        'make:observer', 'make:scope', 'make:cast', 'make:enum',
        'make:exception',
    ],

    // Command execution timeout in seconds
    'timeout' => 300,
];
```

## Usage

Visit `https://yoursite.com/terminal` (or your configured prefix) in your browser.

## Routes

| Method | URI | Name |
|--------|-----|------|
| GET | /terminal | terminal.index |
| POST | /terminal/execute | terminal.execute |
| POST | /terminal/execute-interactive | terminal.execute-interactive |
| GET | /terminal/system-info | terminal.system-info |
| GET | /terminal/error-logs | terminal.error-logs |
| GET | /terminal/get-error-logs | terminal.get-error-logs |
| POST | /terminal/clear-logs | terminal.clear-logs |
| GET | /terminal/download-logs | terminal.download-logs |
| GET | /terminal/diagnose-npm | terminal.diagnose-npm |
| GET | /terminal/diagnose-composer | terminal.diagnose-composer |
| GET | /terminal/node-modules-list | terminal.node-modules-list |

## Security

- All routes are protected by configurable middleware (default: `web`, `auth`)
- Dangerous commands (rm -rf, shutdown, etc.) are blocked
- Only whitelisted artisan commands can be executed
- Configure `blocked_commands` and `allowed_*_commands` in config

## License

MIT
