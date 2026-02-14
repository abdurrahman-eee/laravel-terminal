# Laravel Terminal

A powerful web-based terminal for Laravel applications. Execute artisan commands, manage packages, run shell commands, and more — all from your browser.

![Laravel Terminal](https://img.shields.io/badge/Laravel-10%20|%2011%20|%2012-red) ![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue) ![License](https://img.shields.io/badge/License-MIT-green)

## Screenshots

![Terminal Main View](asset/pic1.jpg)

![Terminal Features](asset/pic2.jpg)

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
- Composer

## Download

📦 **GitHub Repository**: [https://github.com/abdurrahman-eee/laravel-terminal](https://github.com/abdurrahman-eee/laravel-terminal)

You can clone or download the repository from GitHub, or install it directly via Composer (recommended).

## Installation

### Quick Install (Recommended)

For experienced developers, here's the quick version:

```bash
# Add the repository to your composer.json
composer config repositories.laravel-terminal vcs https://github.com/abdurrahman-eee/laravel-terminal

# Install the package
composer require abdurrahman/laravel-terminal

# Publish config (optional)
php artisan vendor:publish --tag=terminal-config

# Visit /terminal in your browser
```

### Detailed Installation Guide

Follow these steps for a complete installation:

#### Step 1: Add the GitHub Repository

Open your terminal and navigate to your Laravel project root, then run:

```bash
composer config repositories.laravel-terminal vcs https://github.com/abdurrahman-eee/laravel-terminal
```

**Or manually edit** your `composer.json` file and add this in the root object:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/abdurrahman-eee/laravel-terminal"
        }
    ]
}
```

> 💡 **Why?** This tells Composer where to download the package from GitHub.

#### Step 2: Install the Package

Run the following command in your terminal:

```bash
composer require abdurrahman/laravel-terminal
```

This will:
- Download the package from GitHub
- Install all dependencies
- Auto-register the service provider (Laravel auto-discovery)

> ✅ **No manual provider registration needed!** Laravel will automatically discover the package.

#### Step 3: Publish Configuration (Optional)

If you want to customize settings like middleware, route prefix, blocked commands, etc:

```bash
php artisan vendor:publish --tag=terminal-config
```

This creates `config/terminal.php` where you can modify:
- Route prefix (default: `/terminal`)
- Middleware (default: `web`, `auth`)
- Blocked/allowed commands
- Command timeout

#### Step 4: Publish Views (Optional)

If you want to customize the terminal UI:

```bash
php artisan vendor:publish --tag=terminal-views
```

This copies the Blade templates to `resources/views/vendor/terminal/` for customization.

#### Step 5: Access the Terminal

Open your browser and visit:

```
https://yoursite.com/terminal
```

> 🔒 **Default Protection**: Routes are protected by `auth` middleware. Make sure you're logged in!

**That's it!** 🎉 You should now see the terminal dashboard.

---

### Alternative: Local Path Installation

If you prefer to include the package directly in your project (without GitHub):

#### Option A: Clone the Repository

```bash
# Navigate to your Laravel project
cd your-laravel-project

# Create packages directory
mkdir -p packages/abdurrahman

# Clone the repository
cd packages/abdurrahman
git clone https://github.com/abdurrahman-eee/laravel-terminal.git
cd ../../..

# Add to composer.json
composer config repositories.laravel-terminal path packages/abdurrahman/laravel-terminal

# Install
composer require abdurrahman/laravel-terminal
```

#### Option B: Download ZIP

1. Download ZIP from [GitHub](https://github.com/abdurrahman-eee/laravel-terminal/archive/refs/heads/main.zip)
2. Extract to `packages/abdurrahman/laravel-terminal/` in your Laravel project
3. Add this to your `composer.json`:

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

4. Run:

```bash
composer require abdurrahman/laravel-terminal
```

---

### Troubleshooting

**Issue**: Package not found or version conflicts

**Solution**: Make sure you've added the repository correctly. Run:
```bash
composer clear-cache
composer update abdurrahman/laravel-terminal
```

**Issue**: "Target class does not exist" error

**Solution**: Clear your application cache:
```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

**Issue**: Can't access `/terminal` route

**Solution**: Make sure you're logged in (default middleware is `auth`). Or publish and edit the config to change middleware:
```bash
php artisan vendor:publish --tag=terminal-config
# Edit config/terminal.php and change 'middleware' => ['web']
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

> ⚠️ **Warning**: This package gives browser-based shell access. Always protect it with authentication middleware and never expose it publicly without auth.

## Uninstall

```bash
composer remove abdurrahman/laravel-terminal
```

Optionally remove published files:
```bash
rm config/terminal.php
rm -rf resources/views/vendor/terminal
```

## Author

**Abdur Rahman**
- GitHub: [@abdurrahman-eee](https://github.com/abdurrahman-eee)
- Website: [abdurrahmanbd.com](https://abdurrahmanbd.com)

## License

MIT — see [LICENSE](LICENSE) for details.
