{{-- Laravel Terminal Package - Main View --}}
@extends(config('terminal.layout', 'layouts.admin'))

@section('content')
<style>
    :root {
        --terminal-bg: #0d1117;
        --terminal-text: #c9d1d9;
        --terminal-green: #3fb950;
        --terminal-red: #f85149;
        --terminal-yellow: #d29922;
        --terminal-blue: #58a6ff;
        --terminal-cyan: #39c5cf;
        --terminal-purple: #bc8cff;
        --terminal-orange: #f0883e;
    }

    .terminal-dashboard {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        min-height: 100vh;
        padding: 1.5rem 0;
    }

    /* Header */
    .terminal-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .terminal-header h1 { margin: 0; font-size: 1.5rem; font-weight: 700; }
    .terminal-header p { margin: 0.3rem 0 0; opacity: 0.7; font-size: 0.85rem; }

    /* Tabs */
    .cmd-tabs {
        display: flex;
        gap: 0.25rem;
        background: #161b22;
        padding: 0.5rem;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        overflow-x: auto;
        border: 1px solid #30363d;
    }
    .cmd-tab {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: none;
        background: transparent;
        color: #8b949e;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .cmd-tab:hover { background: #21262d; color: #c9d1d9; }
    .cmd-tab.active { background: #238636; color: white; }
    .cmd-tab i { font-size: 0.9rem; }

    /* Command Panels */
    .cmd-panel { display: none; }
    .cmd-panel.active { display: block; }

    .cmd-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.6rem;
    }
    .cmd-btn {
        padding: 0.6rem 0.8rem;
        border-radius: 0.5rem;
        border: 1px solid #30363d;
        background: #161b22;
        color: #c9d1d9;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .cmd-btn:hover {
        border-color: #58a6ff;
        background: #1c2333;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(88, 166, 255, 0.15);
    }
    .cmd-btn i { color: #58a6ff; font-size: 0.85rem; min-width: 16px; }
    .cmd-btn code {
        font-family: 'Consolas', monospace;
        font-size: 0.72rem;
        color: #8b949e;
        display: block;
        margin-top: 2px;
    }
    .cmd-btn .cmd-info {
        display: flex;
        flex-direction: column;
    }
    .cmd-btn .cmd-name { font-weight: 600; font-size: 0.8rem; }

    /* Section Labels */
    .section-label {
        color: #8b949e;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 1rem 0 0.5rem;
        padding-left: 0.3rem;
    }

    /* Terminal */
    .terminal-card {
        background: #161b22;
        border-radius: 0.75rem;
        border: 1px solid #30363d;
        overflow: hidden;
    }
    .terminal-titlebar {
        background: #21262d;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #30363d;
    }
    .terminal-titlebar .dots {
        display: flex;
        gap: 6px;
    }
    .terminal-titlebar .dot {
        width: 12px; height: 12px;
        border-radius: 50%;
    }
    .dot-red { background: #f85149; }
    .dot-yellow { background: #d29922; }
    .dot-green { background: #3fb950; }
    .terminal-titlebar .title {
        color: #8b949e;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .terminal-titlebar .actions {
        display: flex; gap: 0.5rem;
    }
    .terminal-titlebar .actions button {
        background: transparent;
        border: 1px solid #30363d;
        color: #8b949e;
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
        border-radius: 0.3rem;
        cursor: pointer;
    }
    .terminal-titlebar .actions button:hover { color: #f85149; border-color: #f85149; }

    .terminal-output {
        background: var(--terminal-bg);
        color: var(--terminal-text);
        padding: 1rem 1.25rem;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.82rem;
        line-height: 1.7;
        min-height: 280px;
        max-height: 500px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
        cursor: text;
    }
    .terminal-output::-webkit-scrollbar { width: 6px; }
    .terminal-output::-webkit-scrollbar-track { background: #161b22; }
    .terminal-output::-webkit-scrollbar-thumb { background: #30363d; border-radius: 3px; }

    .terminal-output .success { color: var(--terminal-green); }
    .terminal-output .error { color: var(--terminal-red); }
    .terminal-output .warning { color: var(--terminal-yellow); }
    .terminal-output .info { color: var(--terminal-blue); }
    .terminal-prompt { color: var(--terminal-green); margin-right: 0.5rem; }

    .terminal-input-line {
        background: #0d1117;
        padding: 0.6rem 1.25rem;
        border-top: 1px solid #21262d;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
    }
    .terminal-input {
        flex: 1;
        background: transparent;
        border: none;
        color: var(--terminal-text);
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.85rem;
        outline: none;
        padding: 0;
    }
    .terminal-input::placeholder { color: #484f58; }

    /* Autocomplete dropdown */
    .autocomplete-dropdown {
        display: none;
        position: absolute;
        bottom: 100%;
        left: 0;
        right: 0;
        background: #1c2128;
        border: 1px solid #30363d;
        border-radius: 0.5rem 0.5rem 0 0;
        max-height: 220px;
        overflow-y: auto;
        z-index: 100;
        box-shadow: 0 -4px 16px rgba(0,0,0,0.4);
    }
    .autocomplete-dropdown.show { display: block; }
    .autocomplete-item {
        padding: 0.4rem 1rem;
        cursor: pointer;
        font-family: 'Consolas', monospace;
        font-size: 0.8rem;
        color: #c9d1d9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #21262d;
    }
    .autocomplete-item:hover, .autocomplete-item.selected {
        background: #238636;
        color: white;
    }
    .autocomplete-item .hint {
        color: #8b949e;
        font-size: 0.7rem;
    }
    .autocomplete-item:hover .hint, .autocomplete-item.selected .hint { color: rgba(255,255,255,0.7); }

    /* Command history */
    .history-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: #8b949e;
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        background: #21262d;
        border-radius: 0.25rem;
    }

    /* Scrollbar for tabs */
    .cmd-tabs::-webkit-scrollbar { height: 4px; }
    .cmd-tabs::-webkit-scrollbar-track { background: transparent; }
    .cmd-tabs::-webkit-scrollbar-thumb { background: #30363d; border-radius: 2px; }

    @@media (max-width: 768px) {
        .cmd-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        .cmd-tabs { flex-wrap: nowrap; }
        .terminal-header { padding: 1rem; }
        .terminal-header h1 { font-size: 1.2rem; }
    }
</style>

<div class="terminal-dashboard">
    <div class="container-fluid px-4">
        {{-- Header --}}
        <div class="terminal-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1><i class="fas fa-terminal me-2"></i>Laravel Terminal</h1>
                    <p><i class="fas fa-shield-alt text-success me-1"></i> Safe Mode &mdash; Dangerous commands blocked &middot; Auto-suggestions enabled</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('terminal.error-logs') }}" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-bug me-1"></i>Error Logs
                    </a>
                    <button class="btn btn-sm btn-outline-light" onclick="loadSystemInfo()">
                        <i class="fas fa-server me-1"></i>System Info
                    </button>
                </div>
            </div>
        </div>

        {{-- Command Tabs --}}
        <div class="cmd-tabs" id="cmdTabs">
            <button class="cmd-tab active" data-tab="laravel" onclick="switchTab('laravel', this)"><i class="fab fa-laravel"></i> Laravel</button>
            <button class="cmd-tab" data-tab="artisan" onclick="switchTab('artisan', this)"><i class="fas fa-magic"></i> Artisan</button>
            <button class="cmd-tab" data-tab="composer" onclick="switchTab('composer', this)"><i class="fas fa-box"></i> Composer</button>
            <button class="cmd-tab" data-tab="database" onclick="switchTab('database', this)"><i class="fas fa-database"></i> Database</button>
            <button class="cmd-tab" data-tab="cache" onclick="switchTab('cache', this)"><i class="fas fa-broom"></i> Cache</button>
            <button class="cmd-tab" data-tab="make" onclick="switchTab('make', this)"><i class="fas fa-plus-circle"></i> Make</button>
            <button class="cmd-tab" data-tab="npm" onclick="switchTab('npm', this)"><i class="fab fa-npm"></i> NPM</button>
            <button class="cmd-tab" data-tab="git" onclick="switchTab('git', this)"><i class="fab fa-git-alt"></i> Git</button>
            <button class="cmd-tab" data-tab="shell" onclick="switchTab('shell', this)"><i class="fas fa-terminal"></i> Shell</button>
            <button class="cmd-tab" data-tab="cdn" onclick="switchTab('cdn', this)"><i class="fas fa-cloud"></i> CDN</button>
        </div>

        {{-- ═══════════════ LARAVEL Panel ═══════════════ --}}
        <div class="cmd-panel active" id="panel-laravel">
            <div class="section-label">&#x1f527; Quick Actions</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan about')"><i class="fas fa-info-circle"></i><div class="cmd-info"><span class="cmd-name">Laravel Info</span><code>artisan about</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan optimize')"><i class="fas fa-rocket"></i><div class="cmd-info"><span class="cmd-name">Optimize App</span><code>artisan optimize</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan optimize:clear')"><i class="fas fa-eraser"></i><div class="cmd-info"><span class="cmd-name">Clear Optimize</span><code>optimize:clear</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan storage:link')"><i class="fas fa-link"></i><div class="cmd-info"><span class="cmd-name">Storage Link</span><code>storage:link</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan route:list')"><i class="fas fa-sitemap"></i><div class="cmd-info"><span class="cmd-name">Route List</span><code>route:list</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan env')"><i class="fas fa-cog"></i><div class="cmd-info"><span class="cmd-name">Environment</span><code>artisan env</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan down')"><i class="fas fa-pause-circle"></i><div class="cmd-info"><span class="cmd-name">Maintenance On</span><code>artisan down</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan up')"><i class="fas fa-play-circle"></i><div class="cmd-info"><span class="cmd-name">Maintenance Off</span><code>artisan up</code></div></button>
            </div>
            <div class="section-label">&#x1f4e6; Popular Laravel Packages</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('composer require livewire/livewire')"><i class="fas fa-bolt" style="color:#fb70a9"></i><div class="cmd-info"><span class="cmd-name">Livewire</span><code>composer require</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require spatie/laravel-permission')"><i class="fas fa-user-shield" style="color:#f0883e"></i><div class="cmd-info"><span class="cmd-name">Spatie Permission</span><code>roles &amp; perms</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require spatie/laravel-medialibrary')"><i class="fas fa-images" style="color:#39c5cf"></i><div class="cmd-info"><span class="cmd-name">Media Library</span><code>spatie/media</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require barryvdh/laravel-dompdf')"><i class="fas fa-file-pdf" style="color:#f85149"></i><div class="cmd-info"><span class="cmd-name">PDF Generator</span><code>laravel-dompdf</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require maatwebsite/excel')"><i class="fas fa-file-excel" style="color:#3fb950"></i><div class="cmd-info"><span class="cmd-name">Excel Export</span><code>maatwebsite/excel</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require intervention/image')"><i class="fas fa-image" style="color:#bc8cff"></i><div class="cmd-info"><span class="cmd-name">Image Processing</span><code>intervention/image</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require artesaos/seotools')"><i class="fas fa-search" style="color:#d29922"></i><div class="cmd-info"><span class="cmd-name">SEO Tools</span><code>artesaos/seotools</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require barryvdh/laravel-debugbar --dev')"><i class="fas fa-bug" style="color:#f0883e"></i><div class="cmd-info"><span class="cmd-name">Debug Bar</span><code>debugbar (dev)</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require laravel/sanctum')"><i class="fas fa-lock" style="color:#58a6ff"></i><div class="cmd-info"><span class="cmd-name">Sanctum API</span><code>laravel/sanctum</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require laravel/socialite')"><i class="fas fa-users" style="color:#3fb950"></i><div class="cmd-info"><span class="cmd-name">Socialite OAuth</span><code>laravel/socialite</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require laravel/horizon')"><i class="fas fa-chart-line" style="color:#bc8cff"></i><div class="cmd-info"><span class="cmd-name">Horizon</span><code>laravel/horizon</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer require laravel/telescope --dev')"><i class="fas fa-satellite" style="color:#d29922"></i><div class="cmd-info"><span class="cmd-name">Telescope</span><code>telescope (dev)</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ ARTISAN Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-artisan">
            <div class="section-label">&#x1f4cb; List &amp; Info</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan list')"><i class="fas fa-list"></i><div class="cmd-info"><span class="cmd-name">All Commands</span><code>artisan list</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan route:list')"><i class="fas fa-sitemap"></i><div class="cmd-info"><span class="cmd-name">Route List</span><code>route:list</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan about')"><i class="fas fa-info-circle"></i><div class="cmd-info"><span class="cmd-name">About</span><code>artisan about</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan schedule:list')"><i class="fas fa-clock"></i><div class="cmd-info"><span class="cmd-name">Schedule List</span><code>schedule:list</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan event:list')"><i class="fas fa-bell"></i><div class="cmd-info"><span class="cmd-name">Event List</span><code>event:list</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan route:list --columns=method,uri,name')"><i class="fas fa-filter"></i><div class="cmd-info"><span class="cmd-name">Routes Compact</span><code>route:list --columns</code></div></button>
            </div>
            <div class="section-label">&#x1f511; Key &amp; Config</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan key:generate')"><i class="fas fa-key"></i><div class="cmd-info"><span class="cmd-name">Generate Key</span><code>key:generate</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan config:show app')"><i class="fas fa-cog"></i><div class="cmd-info"><span class="cmd-name">Show App Config</span><code>config:show app</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan config:show database')"><i class="fas fa-database"></i><div class="cmd-info"><span class="cmd-name">Show DB Config</span><code>config:show database</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan vendor:publish --all')"><i class="fas fa-upload"></i><div class="cmd-info"><span class="cmd-name">Publish All</span><code>vendor:publish --all</code></div></button>
            </div>
            <div class="section-label">&#x1f9ea; Queue &amp; Scheduling</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan queue:work --stop-when-empty')"><i class="fas fa-tasks"></i><div class="cmd-info"><span class="cmd-name">Queue Work</span><code>queue:work</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan queue:retry all')"><i class="fas fa-redo"></i><div class="cmd-info"><span class="cmd-name">Retry Failed</span><code>queue:retry all</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan queue:flush')"><i class="fas fa-trash"></i><div class="cmd-info"><span class="cmd-name">Flush Failed</span><code>queue:flush</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan queue:listen --tries=3')"><i class="fas fa-headphones"></i><div class="cmd-info"><span class="cmd-name">Queue Listen</span><code>queue:listen</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan schedule:run')"><i class="fas fa-clock"></i><div class="cmd-info"><span class="cmd-name">Schedule Run</span><code>schedule:run</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ COMPOSER Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-composer">
            <div class="section-label">&#x1f4e6; Package Management</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="fillCommand('composer require ')"><i class="fas fa-download"></i><div class="cmd-info"><span class="cmd-name">Require Package</span><code>composer require ...</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('composer remove ')"><i class="fas fa-trash-alt"></i><div class="cmd-info"><span class="cmd-name">Remove Package</span><code>composer remove ...</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer install')"><i class="fas fa-download"></i><div class="cmd-info"><span class="cmd-name">Install All</span><code>composer install</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer update')"><i class="fas fa-sync"></i><div class="cmd-info"><span class="cmd-name">Update All</span><code>composer update</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer show')"><i class="fas fa-list"></i><div class="cmd-info"><span class="cmd-name">Show Packages</span><code>composer show</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer outdated')"><i class="fas fa-clock"></i><div class="cmd-info"><span class="cmd-name">Outdated</span><code>composer outdated</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer dump-autoload')"><i class="fas fa-recycle"></i><div class="cmd-info"><span class="cmd-name">Dump Autoload</span><code>dump-autoload</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('composer --version')"><i class="fas fa-tag"></i><div class="cmd-info"><span class="cmd-name">Version</span><code>composer --version</code></div></button>
            </div>
            <div class="section-label">&#x1f50d; Diagnostics</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="diagnoseComposerInstallation()"><i class="fas fa-stethoscope"></i><div class="cmd-info"><span class="cmd-name">Diagnose Composer</span><code>full diagnostic</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php composer.phar --version')"><i class="fas fa-check-circle"></i><div class="cmd-info"><span class="cmd-name">Test composer.phar</span><code>php composer.phar</code></div></button>
                <button class="cmd-btn" onclick="installComposerStepByStep()"><i class="fas fa-magic" style="color:#3fb950"></i><div class="cmd-info"><span class="cmd-name">Auto-Install</span><code>install composer.phar</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ DATABASE Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-database">
            <div class="section-label">&#x1f5c4;&#xfe0f; Migrations</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan migrate:status')"><i class="fas fa-list-check"></i><div class="cmd-info"><span class="cmd-name">Migration Status</span><code>migrate:status</code></div></button>
                <button class="cmd-btn" onclick="confirmAndRun('php artisan migrate', 'Run all pending migrations?')"><i class="fas fa-play"></i><div class="cmd-info"><span class="cmd-name">Run Migrations</span><code>artisan migrate</code></div></button>
                <button class="cmd-btn" onclick="confirmAndRun('php artisan migrate:rollback', 'Rollback last migration batch?')"><i class="fas fa-undo"></i><div class="cmd-info"><span class="cmd-name">Rollback</span><code>migrate:rollback</code></div></button>
                <button class="cmd-btn" onclick="confirmAndRun('php artisan migrate:reset', 'Reset ALL migrations? Data may be lost!')"><i class="fas fa-backward" style="color:#d29922"></i><div class="cmd-info"><span class="cmd-name">Reset All</span><code>migrate:reset &#x26a0;</code></div></button>
                <button class="cmd-btn" onclick="confirmAndRun('php artisan migrate:fresh', 'Drop ALL tables and re-migrate? This DELETES all data!')"><i class="fas fa-exclamation-triangle" style="color:#f85149"></i><div class="cmd-info"><span class="cmd-name">Fresh Migration</span><code>migrate:fresh &#x26a0;</code></div></button>
                <button class="cmd-btn" onclick="confirmAndRun('php artisan migrate:fresh --seed', 'Drop ALL and re-seed? ALL DATA LOST!')"><i class="fas fa-exclamation-triangle" style="color:#f85149"></i><div class="cmd-info"><span class="cmd-name">Fresh + Seed</span><code>fresh --seed &#x26a0;</code></div></button>
            </div>
            <div class="section-label">&#x1f331; Seeders &amp; DB Info</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="confirmAndRun('php artisan db:seed', 'Run database seeders?')"><i class="fas fa-seedling"></i><div class="cmd-info"><span class="cmd-name">Run Seeder</span><code>db:seed</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan db:seed --class=')"><i class="fas fa-seedling" style="color:#d29922"></i><div class="cmd-info"><span class="cmd-name">Seed Specific</span><code>db:seed --class=</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan db:show')"><i class="fas fa-eye"></i><div class="cmd-info"><span class="cmd-name">DB Info</span><code>db:show</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan db:table')"><i class="fas fa-table"></i><div class="cmd-info"><span class="cmd-name">Table List</span><code>db:table</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ CACHE Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-cache">
            <div class="section-label">&#x1f9f9; Clear Caches</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan cache:clear')"><i class="fas fa-broom" style="color:#f0883e"></i><div class="cmd-info"><span class="cmd-name">App Cache</span><code>cache:clear</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan config:clear')"><i class="fas fa-cog" style="color:#d29922"></i><div class="cmd-info"><span class="cmd-name">Config Cache</span><code>config:clear</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan route:clear')"><i class="fas fa-route" style="color:#39c5cf"></i><div class="cmd-info"><span class="cmd-name">Route Cache</span><code>route:clear</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan view:clear')"><i class="fas fa-eye" style="color:#bc8cff"></i><div class="cmd-info"><span class="cmd-name">View Cache</span><code>view:clear</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan event:clear')"><i class="fas fa-bell" style="color:#58a6ff"></i><div class="cmd-info"><span class="cmd-name">Event Cache</span><code>event:clear</code></div></button>
                <button class="cmd-btn" onclick="clearAllCachesSequential()"><i class="fas fa-fire" style="color:#f85149"></i><div class="cmd-info"><span class="cmd-name">Clear ALL</span><code>all caches at once</code></div></button>
            </div>
            <div class="section-label">&#x26a1; Build Caches</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan config:cache')"><i class="fas fa-cog"></i><div class="cmd-info"><span class="cmd-name">Cache Config</span><code>config:cache</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan route:cache')"><i class="fas fa-route"></i><div class="cmd-info"><span class="cmd-name">Cache Routes</span><code>route:cache</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan view:cache')"><i class="fas fa-eye"></i><div class="cmd-info"><span class="cmd-name">Cache Views</span><code>view:cache</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan event:cache')"><i class="fas fa-bell"></i><div class="cmd-info"><span class="cmd-name">Cache Events</span><code>event:cache</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php artisan optimize')"><i class="fas fa-rocket"></i><div class="cmd-info"><span class="cmd-name">Optimize All</span><code>artisan optimize</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ MAKE Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-make">
            <div class="section-label">&#x1f3d7;&#xfe0f; Generate Files</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="fillCommand('php artisan make:controller ')"><i class="fas fa-gamepad"></i><div class="cmd-info"><span class="cmd-name">Controller</span><code>make:controller</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:model ')"><i class="fas fa-cubes"></i><div class="cmd-info"><span class="cmd-name">Model</span><code>make:model</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:migration ')"><i class="fas fa-database"></i><div class="cmd-info"><span class="cmd-name">Migration</span><code>make:migration</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:middleware ')"><i class="fas fa-shield-alt"></i><div class="cmd-info"><span class="cmd-name">Middleware</span><code>make:middleware</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:request ')"><i class="fas fa-paper-plane"></i><div class="cmd-info"><span class="cmd-name">Form Request</span><code>make:request</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:seeder ')"><i class="fas fa-seedling"></i><div class="cmd-info"><span class="cmd-name">Seeder</span><code>make:seeder</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:factory ')"><i class="fas fa-industry"></i><div class="cmd-info"><span class="cmd-name">Factory</span><code>make:factory</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:command ')"><i class="fas fa-terminal"></i><div class="cmd-info"><span class="cmd-name">Command</span><code>make:command</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:mail ')"><i class="fas fa-envelope"></i><div class="cmd-info"><span class="cmd-name">Mailable</span><code>make:mail</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:notification ')"><i class="fas fa-bell"></i><div class="cmd-info"><span class="cmd-name">Notification</span><code>make:notification</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:event ')"><i class="fas fa-bolt"></i><div class="cmd-info"><span class="cmd-name">Event</span><code>make:event</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:listener ')"><i class="fas fa-headphones"></i><div class="cmd-info"><span class="cmd-name">Listener</span><code>make:listener</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:job ')"><i class="fas fa-tasks"></i><div class="cmd-info"><span class="cmd-name">Job</span><code>make:job</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:policy ')"><i class="fas fa-gavel"></i><div class="cmd-info"><span class="cmd-name">Policy</span><code>make:policy</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:resource ')"><i class="fas fa-code"></i><div class="cmd-info"><span class="cmd-name">Resource</span><code>make:resource</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:component ')"><i class="fas fa-puzzle-piece"></i><div class="cmd-info"><span class="cmd-name">Component</span><code>make:component</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:livewire ')"><i class="fas fa-bolt" style="color:#fb70a9"></i><div class="cmd-info"><span class="cmd-name">Livewire</span><code>make:livewire</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:test ')"><i class="fas fa-vial"></i><div class="cmd-info"><span class="cmd-name">Test</span><code>make:test</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:rule ')"><i class="fas fa-check-double"></i><div class="cmd-info"><span class="cmd-name">Validation Rule</span><code>make:rule</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:observer ')"><i class="fas fa-eye"></i><div class="cmd-info"><span class="cmd-name">Observer</span><code>make:observer</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:scope ')"><i class="fas fa-filter"></i><div class="cmd-info"><span class="cmd-name">Scope</span><code>make:scope</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:cast ')"><i class="fas fa-exchange-alt"></i><div class="cmd-info"><span class="cmd-name">Cast</span><code>make:cast</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:enum ')"><i class="fas fa-list-ol"></i><div class="cmd-info"><span class="cmd-name">Enum</span><code>make:enum</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:exception ')"><i class="fas fa-exclamation"></i><div class="cmd-info"><span class="cmd-name">Exception</span><code>make:exception</code></div></button>
            </div>
            <div class="section-label">&#x1f9e9; Model Shortcuts</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="fillCommand('php artisan make:model -m ')"><i class="fas fa-cube" style="color:#58a6ff"></i><div class="cmd-info"><span class="cmd-name">Model + Migration</span><code>-m (migration)</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:model -mc ')"><i class="fas fa-cube" style="color:#39c5cf"></i><div class="cmd-info"><span class="cmd-name">Model + M + C</span><code>-mc (migration+controller)</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:model -mcr ')"><i class="fas fa-cube" style="color:#3fb950"></i><div class="cmd-info"><span class="cmd-name">Model + MCR</span><code>-mcr (resource controller)</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:model -mcs ')"><i class="fas fa-cube" style="color:#d29922"></i><div class="cmd-info"><span class="cmd-name">Model + MCS</span><code>-mcs (controller+seeder)</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('php artisan make:model -a ')"><i class="fas fa-cube" style="color:#bc8cff"></i><div class="cmd-info"><span class="cmd-name">Model + All</span><code>-a (everything)</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ NPM Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-npm">
            <div class="section-label">&#x1f4e6; NPM Commands</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="fillCommand('npm install ')"><i class="fas fa-download"></i><div class="cmd-info"><span class="cmd-name">Install Package</span><code>npm install ...</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('npm install')"><i class="fas fa-download"></i><div class="cmd-info"><span class="cmd-name">Install All</span><code>npm install</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('npm run build')"><i class="fas fa-hammer"></i><div class="cmd-info"><span class="cmd-name">Build</span><code>npm run build</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('npm run dev')"><i class="fas fa-play"></i><div class="cmd-info"><span class="cmd-name">Dev Server</span><code>npm run dev</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('npm list --depth=0')"><i class="fas fa-list"></i><div class="cmd-info"><span class="cmd-name">List Packages</span><code>npm list</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('npm outdated')"><i class="fas fa-clock"></i><div class="cmd-info"><span class="cmd-name">Outdated</span><code>npm outdated</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('npm audit')"><i class="fas fa-shield-alt"></i><div class="cmd-info"><span class="cmd-name">Security Audit</span><code>npm audit</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('npm --version')"><i class="fas fa-tag"></i><div class="cmd-info"><span class="cmd-name">Version</span><code>npm --version</code></div></button>
                <button class="cmd-btn" onclick="diagnoseNpmInstallation()"><i class="fas fa-stethoscope"></i><div class="cmd-info"><span class="cmd-name">Diagnose npm</span><code>full diagnostic</code></div></button>
                <button class="cmd-btn" onclick="showNodeModulesPackages()"><i class="fas fa-box-open"></i><div class="cmd-info"><span class="cmd-name">Show node_modules</span><code>list installed</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ GIT Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-git">
            <div class="section-label">&#x1f4c2; Git Info</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('git status')"><i class="fas fa-info-circle"></i><div class="cmd-info"><span class="cmd-name">Status</span><code>git status</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git log --oneline -10')"><i class="fas fa-history"></i><div class="cmd-info"><span class="cmd-name">Log (last 10)</span><code>git log --oneline</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git branch -a')"><i class="fas fa-code-branch"></i><div class="cmd-info"><span class="cmd-name">Branches</span><code>git branch -a</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git remote -v')"><i class="fas fa-globe"></i><div class="cmd-info"><span class="cmd-name">Remotes</span><code>git remote -v</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git diff --stat')"><i class="fas fa-exchange-alt"></i><div class="cmd-info"><span class="cmd-name">Diff Stats</span><code>git diff --stat</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git stash list')"><i class="fas fa-archive"></i><div class="cmd-info"><span class="cmd-name">Stash List</span><code>git stash list</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git log --oneline --graph -15')"><i class="fas fa-project-diagram"></i><div class="cmd-info"><span class="cmd-name">Graph Log</span><code>--graph --oneline</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git tag')"><i class="fas fa-tags"></i><div class="cmd-info"><span class="cmd-name">Tags</span><code>git tag</code></div></button>
            </div>
            <div class="section-label">&#x1f4e4; Git Actions</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('git add .')"><i class="fas fa-plus-circle" style="color:#3fb950"></i><div class="cmd-info"><span class="cmd-name">Stage All</span><code>git add .</code></div></button>
                <button class="cmd-btn" onclick="fillCommand('git commit -m &quot;')"><i class="fas fa-check" style="color:#58a6ff"></i><div class="cmd-info"><span class="cmd-name">Commit</span><code>git commit -m &quot;...&quot;</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git pull')"><i class="fas fa-download" style="color:#bc8cff"></i><div class="cmd-info"><span class="cmd-name">Pull</span><code>git pull</code></div></button>
                <button class="cmd-btn" onclick="confirmAndRun('git push', 'Push to remote?')"><i class="fas fa-upload" style="color:#f0883e"></i><div class="cmd-info"><span class="cmd-name">Push</span><code>git push</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ SHELL Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-shell">
            <div class="section-label">&#x1f4bb; System Commands</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('ls -la')"><i class="fas fa-folder-open"></i><div class="cmd-info"><span class="cmd-name">List Files</span><code>ls -la</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('pwd')"><i class="fas fa-map-marker-alt"></i><div class="cmd-info"><span class="cmd-name">Current Dir</span><code>pwd</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('df -h')"><i class="fas fa-hdd"></i><div class="cmd-info"><span class="cmd-name">Disk Space</span><code>df -h</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('du -sh * | sort -rh | head -10')"><i class="fas fa-sort-amount-down"></i><div class="cmd-info"><span class="cmd-name">Largest Dirs</span><code>du -sh * | sort</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('whoami')"><i class="fas fa-user"></i><div class="cmd-info"><span class="cmd-name">Current User</span><code>whoami</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('uptime')"><i class="fas fa-clock"></i><div class="cmd-info"><span class="cmd-name">Uptime</span><code>uptime</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('free -h')"><i class="fas fa-memory"></i><div class="cmd-info"><span class="cmd-name">Memory</span><code>free -h</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('uname -a')"><i class="fas fa-server"></i><div class="cmd-info"><span class="cmd-name">Kernel Info</span><code>uname -a</code></div></button>
            </div>
            <div class="section-label">&#x1f40d; PHP &amp; Versions</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('php -v')"><i class="fab fa-php"></i><div class="cmd-info"><span class="cmd-name">PHP Version</span><code>php -v</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php -m')"><i class="fas fa-puzzle-piece"></i><div class="cmd-info"><span class="cmd-name">PHP Modules</span><code>php -m</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('php -i | head -60')"><i class="fas fa-info"></i><div class="cmd-info"><span class="cmd-name">PHP Info</span><code>php -i</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('git --version')"><i class="fab fa-git"></i><div class="cmd-info"><span class="cmd-name">Git Version</span><code>git --version</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('curl --version | head -1')"><i class="fas fa-download"></i><div class="cmd-info"><span class="cmd-name">cURL Version</span><code>curl --version</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('cat .env | head -25')"><i class="fas fa-file-alt"></i><div class="cmd-info"><span class="cmd-name">.env (first 25)</span><code>cat .env | head</code></div></button>
            </div>
            <div class="section-label">&#x1f4c1; File Operations</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="executeQuickCommand('find . -name \"*.php\" -newer composer.json | head -20')"><i class="fas fa-search"></i><div class="cmd-info"><span class="cmd-name">Recent PHP Files</span><code>modified recently</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('wc -l app/Http/Controllers/*.php')"><i class="fas fa-sort-numeric-up"></i><div class="cmd-info"><span class="cmd-name">Controller Lines</span><code>wc -l controllers</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('ls -la storage/logs/')"><i class="fas fa-file-alt" style="color:#f85149"></i><div class="cmd-info"><span class="cmd-name">Log Files</span><code>ls storage/logs/</code></div></button>
                <button class="cmd-btn" onclick="executeQuickCommand('tail -30 storage/logs/laravel.log')"><i class="fas fa-scroll" style="color:#d29922"></i><div class="cmd-info"><span class="cmd-name">Last 30 Log Lines</span><code>tail laravel.log</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ CDN Panel ═══════════════ --}}
        <div class="cmd-panel" id="panel-cdn">
            <div class="section-label">&#x2601;&#xfe0f; CDN Packages &mdash; No Installation Needed</div>
            <div class="cmd-grid">
                <button class="cmd-btn" onclick="copyCDN('alpine')"><i class="fas fa-mountain" style="color:#77c1d2"></i><div class="cmd-info"><span class="cmd-name">Alpine.js 3</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('htmx')"><i class="fas fa-code" style="color:#3d72d7"></i><div class="cmd-info"><span class="cmd-name">HTMX</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('vue')"><i class="fab fa-vuejs" style="color:#42b883"></i><div class="cmd-info"><span class="cmd-name">Vue.js 3</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('jquery')"><i class="fas fa-dollar-sign" style="color:#0769ad"></i><div class="cmd-info"><span class="cmd-name">jQuery</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('bootstrap')"><i class="fab fa-bootstrap" style="color:#7952b3"></i><div class="cmd-info"><span class="cmd-name">Bootstrap 5</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('tailwind')"><i class="fas fa-wind" style="color:#06b6d4"></i><div class="cmd-info"><span class="cmd-name">Tailwind CSS</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('chartjs')"><i class="fas fa-chart-bar" style="color:#ff6384"></i><div class="cmd-info"><span class="cmd-name">Chart.js</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('fontawesome')"><i class="fas fa-flag" style="color:#339af0"></i><div class="cmd-info"><span class="cmd-name">Font Awesome 6</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('axios')"><i class="fas fa-exchange-alt" style="color:#5a29e4"></i><div class="cmd-info"><span class="cmd-name">Axios</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('sweetalert')"><i class="fas fa-bell" style="color:#f27474"></i><div class="cmd-info"><span class="cmd-name">SweetAlert2</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('aos')"><i class="fas fa-eye" style="color:#4fc08d"></i><div class="cmd-info"><span class="cmd-name">AOS Animate</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('gsap')"><i class="fas fa-film" style="color:#88CE02"></i><div class="cmd-info"><span class="cmd-name">GSAP Animation</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('livewire')"><i class="fas fa-bolt" style="color:#fb70a9"></i><div class="cmd-info"><span class="cmd-name">Livewire CDN</span><code>@livewireStyles</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('moment')"><i class="fas fa-calendar" style="color:#58a6ff"></i><div class="cmd-info"><span class="cmd-name">Moment.js</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('lodash')"><i class="fas fa-toolbox" style="color:#3492ff"></i><div class="cmd-info"><span class="cmd-name">Lodash</span><code>click to copy CDN</code></div></button>
                <button class="cmd-btn" onclick="copyCDN('toastr')"><i class="fas fa-comment" style="color:#d29922"></i><div class="cmd-info"><span class="cmd-name">Toastr Notify</span><code>click to copy CDN</code></div></button>
            </div>
        </div>

        {{-- ═══════════════ TERMINAL ═══════════════ --}}
        <div class="terminal-card mt-3">
            <div class="terminal-titlebar">
                <div class="dots">
                    <span class="dot dot-red"></span>
                    <span class="dot dot-yellow"></span>
                    <span class="dot dot-green"></span>
                </div>
                <div class="title">terminal &mdash; laravel@server</div>
                <div class="actions">
                    <button onclick="clearTerminalOutput()" title="Clear terminal"><i class="fas fa-times me-1"></i>Clear</button>
                </div>
            </div>
            <div id="terminalOutput" class="terminal-output" onclick="document.getElementById('terminalInput').focus()"><span class="terminal-prompt">$</span><span class="info"> Welcome to Laravel Terminal</span>
<span class="terminal-prompt">$</span><span class="success"> Ready to execute commands...</span>
<span class="terminal-prompt">$</span><span class="warning"> Type a command below or use the buttons above</span>
<span class="terminal-prompt">$</span><span class="info"> Auto-suggestions appear as you type! Use Tab to accept, Arrow keys to navigate</span></div>
            <div class="terminal-input-line">
                <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
                <span class="terminal-prompt" style="color:var(--terminal-green)">$</span>
                <input type="text" id="terminalInput" class="terminal-input" placeholder="Type command and press Enter... (auto-suggest enabled)" autocomplete="off" spellcheck="false" autofocus>
                <span class="history-badge" id="historyBadge" title="Command history (Up/Down arrows)"><i class="fas fa-history"></i> <span id="historyCount">0</span></span>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const commandHistory = [];
let historyIndex = -1;
let selectedAutocompleteIndex = -1;

// ═══════════════════════════════════════════
// ALL KNOWN COMMANDS FOR AUTOCOMPLETE
// ═══════════════════════════════════════════
const allCommands = [
    // ── Laravel Artisan ──
    { cmd: 'php artisan about', hint: 'Laravel info' },
    { cmd: 'php artisan list', hint: 'All commands list' },
    { cmd: 'php artisan serve', hint: 'Start dev server' },
    { cmd: 'php artisan up', hint: 'Maintenance off' },
    { cmd: 'php artisan down', hint: 'Maintenance on' },
    { cmd: 'php artisan env', hint: 'Environment info' },
    { cmd: 'php artisan optimize', hint: 'Optimize all caches' },
    { cmd: 'php artisan optimize:clear', hint: 'Clear all optimizations' },
    { cmd: 'php artisan key:generate', hint: 'Generate app key' },
    { cmd: 'php artisan storage:link', hint: 'Create storage symlink' },
    { cmd: 'php artisan vendor:publish --all', hint: 'Publish all vendor' },
    // ── Cache ──
    { cmd: 'php artisan cache:clear', hint: 'Clear app cache' },
    { cmd: 'php artisan config:clear', hint: 'Clear config cache' },
    { cmd: 'php artisan config:cache', hint: 'Cache config' },
    { cmd: 'php artisan route:clear', hint: 'Clear route cache' },
    { cmd: 'php artisan route:cache', hint: 'Cache routes' },
    { cmd: 'php artisan route:list', hint: 'List all routes' },
    { cmd: 'php artisan route:list --columns=method,uri,name', hint: 'Routes compact' },
    { cmd: 'php artisan view:clear', hint: 'Clear view cache' },
    { cmd: 'php artisan view:cache', hint: 'Cache compiled views' },
    { cmd: 'php artisan event:clear', hint: 'Clear event cache' },
    { cmd: 'php artisan event:cache', hint: 'Cache events' },
    { cmd: 'php artisan event:list', hint: 'List events' },
    // ── Database ──
    { cmd: 'php artisan migrate', hint: 'Run pending migrations' },
    { cmd: 'php artisan migrate:status', hint: 'Migration status' },
    { cmd: 'php artisan migrate:rollback', hint: 'Rollback last batch' },
    { cmd: 'php artisan migrate:reset', hint: 'Reset all migrations' },
    { cmd: 'php artisan migrate:fresh', hint: 'Drop all & re-migrate' },
    { cmd: 'php artisan migrate:fresh --seed', hint: 'Fresh + seed data' },
    { cmd: 'php artisan db:seed', hint: 'Run seeders' },
    { cmd: 'php artisan db:seed --class=', hint: 'Run specific seeder' },
    { cmd: 'php artisan db:show', hint: 'Database info' },
    { cmd: 'php artisan db:table', hint: 'List all tables' },
    // ── Make ──
    { cmd: 'php artisan make:controller ', hint: 'New controller' },
    { cmd: 'php artisan make:model ', hint: 'New model' },
    { cmd: 'php artisan make:model -m ', hint: 'Model + migration' },
    { cmd: 'php artisan make:model -mc ', hint: 'Model + migration + controller' },
    { cmd: 'php artisan make:model -mcr ', hint: 'Model + migration + resource controller' },
    { cmd: 'php artisan make:model -mcs ', hint: 'Model + migration + controller + seeder' },
    { cmd: 'php artisan make:model -a ', hint: 'Model + all files' },
    { cmd: 'php artisan make:migration ', hint: 'New migration' },
    { cmd: 'php artisan make:middleware ', hint: 'New middleware' },
    { cmd: 'php artisan make:request ', hint: 'New form request' },
    { cmd: 'php artisan make:seeder ', hint: 'New seeder' },
    { cmd: 'php artisan make:factory ', hint: 'New factory' },
    { cmd: 'php artisan make:command ', hint: 'New artisan command' },
    { cmd: 'php artisan make:mail ', hint: 'New mailable' },
    { cmd: 'php artisan make:notification ', hint: 'New notification' },
    { cmd: 'php artisan make:event ', hint: 'New event' },
    { cmd: 'php artisan make:listener ', hint: 'New listener' },
    { cmd: 'php artisan make:job ', hint: 'New job' },
    { cmd: 'php artisan make:policy ', hint: 'New policy' },
    { cmd: 'php artisan make:resource ', hint: 'New API resource' },
    { cmd: 'php artisan make:component ', hint: 'New Blade component' },
    { cmd: 'php artisan make:livewire ', hint: 'New Livewire component' },
    { cmd: 'php artisan make:test ', hint: 'New test' },
    { cmd: 'php artisan make:rule ', hint: 'New validation rule' },
    { cmd: 'php artisan make:observer ', hint: 'New model observer' },
    { cmd: 'php artisan make:scope ', hint: 'New model scope' },
    { cmd: 'php artisan make:cast ', hint: 'New custom cast' },
    { cmd: 'php artisan make:enum ', hint: 'New enum' },
    { cmd: 'php artisan make:exception ', hint: 'New exception class' },
    // ── Queue ──
    { cmd: 'php artisan queue:work --stop-when-empty', hint: 'Process queue' },
    { cmd: 'php artisan queue:retry all', hint: 'Retry all failed jobs' },
    { cmd: 'php artisan queue:flush', hint: 'Flush failed jobs' },
    { cmd: 'php artisan queue:listen --tries=3', hint: 'Listen to queue' },
    { cmd: 'php artisan schedule:run', hint: 'Run scheduled tasks' },
    { cmd: 'php artisan schedule:list', hint: 'List schedules' },
    { cmd: 'php artisan config:show app', hint: 'Show app config' },
    { cmd: 'php artisan config:show database', hint: 'Show DB config' },
    // ── Composer ──
    { cmd: 'composer require ', hint: 'Add package' },
    { cmd: 'composer remove ', hint: 'Remove package' },
    { cmd: 'composer install', hint: 'Install all deps' },
    { cmd: 'composer update', hint: 'Update all deps' },
    { cmd: 'composer show', hint: 'List installed packages' },
    { cmd: 'composer outdated', hint: 'Check outdated packages' },
    { cmd: 'composer dump-autoload', hint: 'Rebuild autoloader' },
    { cmd: 'composer --version', hint: 'Composer version' },
    { cmd: 'composer require livewire/livewire', hint: 'Livewire' },
    { cmd: 'composer require spatie/laravel-permission', hint: 'Roles & Permissions' },
    { cmd: 'composer require barryvdh/laravel-dompdf', hint: 'PDF Generation' },
    { cmd: 'composer require maatwebsite/excel', hint: 'Excel Export' },
    { cmd: 'composer require intervention/image', hint: 'Image Processing' },
    { cmd: 'composer require spatie/laravel-medialibrary', hint: 'Media Library' },
    { cmd: 'composer require artesaos/seotools', hint: 'SEO Tools' },
    { cmd: 'composer require barryvdh/laravel-debugbar --dev', hint: 'Debug Bar' },
    { cmd: 'composer require laravel/sanctum', hint: 'API Auth' },
    { cmd: 'composer require laravel/socialite', hint: 'Social OAuth' },
    { cmd: 'composer require laravel/horizon', hint: 'Queue Dashboard' },
    { cmd: 'composer require laravel/telescope --dev', hint: 'Debug Dashboard' },
    { cmd: 'php composer.phar --version', hint: 'Test local composer' },
    // ── NPM ──
    { cmd: 'npm install', hint: 'Install all npm deps' },
    { cmd: 'npm install ', hint: 'Install specific package' },
    { cmd: 'npm run build', hint: 'Production build' },
    { cmd: 'npm run dev', hint: 'Dev mode server' },
    { cmd: 'npm list --depth=0', hint: 'List npm packages' },
    { cmd: 'npm outdated', hint: 'Check outdated npm' },
    { cmd: 'npm audit', hint: 'Security audit' },
    { cmd: 'npm --version', hint: 'npm version' },
    // ── Git ──
    { cmd: 'git status', hint: 'Check git status' },
    { cmd: 'git log --oneline -10', hint: 'Last 10 commits' },
    { cmd: 'git log --oneline --graph -15', hint: 'Graph log' },
    { cmd: 'git branch -a', hint: 'All branches' },
    { cmd: 'git remote -v', hint: 'Remote URLs' },
    { cmd: 'git diff --stat', hint: 'Diff summary' },
    { cmd: 'git stash list', hint: 'Stash list' },
    { cmd: 'git tag', hint: 'List tags' },
    { cmd: 'git add .', hint: 'Stage all changes' },
    { cmd: 'git commit -m "', hint: 'Commit with message' },
    { cmd: 'git pull', hint: 'Pull from remote' },
    { cmd: 'git push', hint: 'Push to remote' },
    // ── Shell ──
    { cmd: 'ls -la', hint: 'List files detailed' },
    { cmd: 'pwd', hint: 'Current directory' },
    { cmd: 'whoami', hint: 'Current user' },
    { cmd: 'df -h', hint: 'Disk space' },
    { cmd: 'du -sh * | sort -rh | head -10', hint: 'Largest directories' },
    { cmd: 'free -h', hint: 'Memory usage' },
    { cmd: 'uptime', hint: 'Server uptime' },
    { cmd: 'uname -a', hint: 'Kernel info' },
    { cmd: 'php -v', hint: 'PHP version' },
    { cmd: 'php -m', hint: 'PHP modules' },
    { cmd: 'php -i | head -60', hint: 'PHP info' },
    { cmd: 'git --version', hint: 'Git version' },
    { cmd: 'curl --version | head -1', hint: 'cURL version' },
    { cmd: 'cat .env | head -25', hint: 'View .env' },
    { cmd: 'tail -30 storage/logs/laravel.log', hint: 'Last 30 log lines' },
    { cmd: 'ls -la storage/logs/', hint: 'Log file list' },
    { cmd: 'find . -name "*.php" -newer composer.json | head -20', hint: 'Recent PHP files' },
    { cmd: 'wc -l app/Http/Controllers/*.php', hint: 'Controller line counts' },
    { cmd: 'curl -sS https://getcomposer.org/installer | php', hint: 'Install composer.phar' },
];

// ═══════════════ Tab Switching ═══════════════
function switchTab(tab, btn) {
    document.querySelectorAll('.cmd-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.cmd-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    btn.classList.add('active');
}

// ═══════════════ Terminal Output ═══════════════
function appendOutput(text, type) {
    type = type || 'info';
    const output = document.getElementById('terminalOutput');
    const timestamp = new Date().toLocaleTimeString();
    const escapedText = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    output.innerHTML += '\n<span class="terminal-prompt">[' + timestamp + ']$</span><span class="' + type + '"> ' + escapedText + '</span>';
    output.scrollTop = output.scrollHeight;
}

function appendRawOutput(html) {
    const output = document.getElementById('terminalOutput');
    output.innerHTML += '\n' + html;
    output.scrollTop = output.scrollHeight;
}

function clearTerminalOutput() {
    const output = document.getElementById('terminalOutput');
    output.innerHTML = '<span class="terminal-prompt">$</span><span class="info"> Terminal cleared</span>';
    document.getElementById('terminalInput').value = '';
    document.getElementById('terminalInput').focus();
}

// ═══════════════ Fill Command ═══════════════
function fillCommand(cmd) {
    const input = document.getElementById('terminalInput');
    input.value = cmd;
    input.focus();
    input.setSelectionRange(cmd.length, cmd.length);
    showAutocomplete(cmd);
}

// ═══════════════ Confirm Dangerous ═══════════════
function confirmAndRun(cmd, message) {
    if (confirm(message)) {
        executeQuickCommand(cmd);
    }
}

// ═══════════════ Execute Command ═══════════════
function executeQuickCommand(command) {
    appendOutput('$ ' + command, 'info');
    appendOutput('Executing...', 'warning');

    fetch('{{ route("terminal.execute-interactive") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ command: command })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.output) {
            appendOutput(data.output, data.status === 'success' ? 'success' : 'error');
        } else {
            appendOutput('Done (no output)', 'success');
        }
    })
    .catch(function(error) {
        appendOutput('Error: ' + error.message, 'error');
    });
}

// ═══════════════ Clear All Caches ═══════════════
function clearAllCachesSequential() {
    var caches = ['cache:clear', 'config:clear', 'route:clear', 'view:clear', 'event:clear'];
    appendOutput('Clearing ALL caches...', 'info');
    var i = 0;
    function next() {
        if (i >= caches.length) {
            appendOutput('All caches cleared!', 'success');
            return;
        }
        executeQuickCommand('php artisan ' + caches[i]);
        i++;
        setTimeout(next, 2000);
    }
    next();
}

// ═══════════════ CDN Copy ═══════════════
var cdnMap = {
    alpine: '<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"><\/script>',
    htmx: '<script src="https://unpkg.com/htmx.org@2.0.0"><\/script>',
    vue: '<script src="https://unpkg.com/vue@3/dist/vue.global.js"><\/script>',
    jquery: '<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>',
    bootstrap: '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">\n<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"><\/script>',
    tailwind: '<script src="https://cdn.tailwindcss.com"><\/script>',
    chartjs: '<script src="https://cdn.jsdelivr.net/npm/chart.js"><\/script>',
    fontawesome: '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">',
    axios: '<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"><\/script>',
    sweetalert: '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>',
    aos: '<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">\n<script src="https://unpkg.com/aos@next/dist/aos.js"><\/script>\n<script>AOS.init();<\/script>',
    gsap: '<script src="https://cdn.jsdelivr.net/npm/gsap@3/dist/gsap.min.js"><\/script>',
    livewire: '@livewireStyles\n{{-- Place before </body>: --}}\n@livewireScripts',
    moment: '<script src="https://cdn.jsdelivr.net/npm/moment@2/moment.min.js"><\/script>',
    lodash: '<script src="https://cdn.jsdelivr.net/npm/lodash@4/lodash.min.js"><\/script>',
    toastr: '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">\n<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"><\/script>'
};

function copyCDN(name) {
    var code = cdnMap[name];
    if (!code) return;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(function() {
            appendOutput(name.toUpperCase() + ' CDN code copied to clipboard!', 'success');
            appendOutput('Paste it in your Blade template <head> or before </body>', 'info');
        }).catch(function() {
            appendOutput('CDN code for ' + name + ':', 'info');
            appendOutput(code, 'warning');
        });
    } else {
        appendOutput('CDN code for ' + name + ':', 'info');
        appendOutput(code, 'warning');
    }
}

// ═══════════════ AUTOCOMPLETE SYSTEM ═══════════════
function showAutocomplete(query) {
    var dropdown = document.getElementById('autocompleteDropdown');
    if (!query || query.length < 1) {
        dropdown.classList.remove('show');
        selectedAutocompleteIndex = -1;
        return;
    }
    var q = query.toLowerCase();
    var matches = allCommands.filter(function(c) {
        return c.cmd.toLowerCase().indexOf(q) !== -1 || c.hint.toLowerCase().indexOf(q) !== -1;
    }).slice(0, 12);

    if (matches.length === 0) {
        dropdown.classList.remove('show');
        selectedAutocompleteIndex = -1;
        return;
    }
    selectedAutocompleteIndex = -1;
    var html = '';
    for (var i = 0; i < matches.length; i++) {
        html += '<div class="autocomplete-item" data-index="' + i + '" data-cmd="' + matches[i].cmd.replace(/"/g, '&quot;') + '">';
        html += '<span>' + highlightMatch(matches[i].cmd, q) + '</span>';
        html += '<span class="hint">' + matches[i].hint + '</span>';
        html += '</div>';
    }
    dropdown.innerHTML = html;
    dropdown.classList.add('show');
    
    var items = dropdown.querySelectorAll('.autocomplete-item');
    for (var j = 0; j < items.length; j++) {
        items[j].addEventListener('click', function() {
            selectAutocomplete(this.getAttribute('data-cmd'));
        });
    }
}

function highlightMatch(text, query) {
    var idx = text.toLowerCase().indexOf(query);
    if (idx === -1) return text;
    return text.slice(0, idx) + '<strong style="color:#58a6ff">' + text.slice(idx, idx + query.length) + '</strong>' + text.slice(idx + query.length);
}

function selectAutocomplete(cmd) {
    var input = document.getElementById('terminalInput');
    input.value = cmd;
    document.getElementById('autocompleteDropdown').classList.remove('show');
    selectedAutocompleteIndex = -1;
    input.focus();
}

function navigateAutocomplete(direction) {
    var dropdown = document.getElementById('autocompleteDropdown');
    var items = dropdown.querySelectorAll('.autocomplete-item');
    if (items.length === 0) return false;
    
    for (var k = 0; k < items.length; k++) { items[k].classList.remove('selected'); }
    
    selectedAutocompleteIndex += direction;
    if (selectedAutocompleteIndex < 0) selectedAutocompleteIndex = items.length - 1;
    if (selectedAutocompleteIndex >= items.length) selectedAutocompleteIndex = 0;
    
    items[selectedAutocompleteIndex].classList.add('selected');
    items[selectedAutocompleteIndex].scrollIntoView({ block: 'nearest' });
    return true;
}

// ═══════════════ Terminal Input Handler ═══════════════
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('terminalInput');
    
    input.addEventListener('input', function() {
        showAutocomplete(this.value);
    });

    input.addEventListener('keydown', function(e) {
        var dropdown = document.getElementById('autocompleteDropdown');
        var isDropdownOpen = dropdown.classList.contains('show');
        
        if (e.key === 'Tab') {
            e.preventDefault();
            if (isDropdownOpen) {
                var items = dropdown.querySelectorAll('.autocomplete-item');
                if (items.length > 0) {
                    var idx = selectedAutocompleteIndex >= 0 ? selectedAutocompleteIndex : 0;
                    selectAutocomplete(items[idx].getAttribute('data-cmd'));
                }
            }
            return;
        }
        
        if (e.key === 'ArrowDown' && isDropdownOpen) {
            e.preventDefault();
            navigateAutocomplete(1);
            return;
        }
        if (e.key === 'ArrowUp' && isDropdownOpen) {
            e.preventDefault();
            navigateAutocomplete(-1);
            return;
        }
        
        if (e.key === 'ArrowUp' && !isDropdownOpen) {
            e.preventDefault();
            if (commandHistory.length > 0) {
                if (historyIndex < commandHistory.length - 1) historyIndex++;
                this.value = commandHistory[commandHistory.length - 1 - historyIndex];
            }
            return;
        }
        if (e.key === 'ArrowDown' && !isDropdownOpen) {
            e.preventDefault();
            if (historyIndex > 0) {
                historyIndex--;
                this.value = commandHistory[commandHistory.length - 1 - historyIndex];
            } else {
                historyIndex = -1;
                this.value = '';
            }
            return;
        }
        
        if (e.key === 'Escape') {
            dropdown.classList.remove('show');
            selectedAutocompleteIndex = -1;
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            
            if (isDropdownOpen && selectedAutocompleteIndex >= 0) {
                var selItems = dropdown.querySelectorAll('.autocomplete-item');
                if (selItems[selectedAutocompleteIndex]) {
                    selectAutocomplete(selItems[selectedAutocompleteIndex].getAttribute('data-cmd'));
                    return;
                }
            }
            
            dropdown.classList.remove('show');
            var command = this.value.trim();
            if (!command) return;
            
            commandHistory.push(command);
            historyIndex = -1;
            document.getElementById('historyCount').textContent = commandHistory.length;
            
            this.value = '';
            executeQuickCommand(command);
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.terminal-input-line')) {
            document.getElementById('autocompleteDropdown').classList.remove('show');
        }
    });
});

// ═══════════════ System Info ═══════════════
function loadSystemInfo() {
    appendOutput('Loading system information...', 'info');
    fetch('{{ route("terminal.system-info") }}')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            appendOutput('', 'info');
            appendOutput('━━━ SYSTEM INFORMATION ━━━', 'info');
            var info = data.info;
            for (var key in info) {
                if (info.hasOwnProperty(key)) {
                    appendOutput('  ' + key.replace(/_/g, ' ').toUpperCase() + ': ' + info[key], 'success');
                }
            }
            appendOutput('━━━━━━━━━━━━━━━━━━━━━━━━━━', 'info');
        }
    })
    .catch(function(error) { appendOutput('Error: ' + error.message, 'error'); });
}

// ═══════════════ Diagnostics ═══════════════
function diagnoseNpmInstallation() {
    appendOutput('Diagnosing npm installation...', 'info');
    fetch('{{ route("terminal.diagnose-npm") }}', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            var d = data.diagnostics;
            appendOutput('', 'info');
            appendOutput('━━━ NPM DIAGNOSTICS ━━━', 'info');
            if (d.npm_found) {
                appendOutput('  npm: ' + d.npm_path + ' (' + d.npm_version + ')', 'success');
            } else {
                appendOutput('  npm NOT FOUND', 'error');
            }
            if (d.node_found) {
                appendOutput('  node: ' + d.node_path + ' (' + d.node_version + ')', 'success');
            } else {
                appendOutput('  Node.js NOT FOUND', 'error');
            }
            appendOutput('  Home: ' + d.home_directory + ' | User: ' + d.current_user, 'info');
            if (!d.npm_found) {
                appendOutput('  Tip: Go to cPanel > Setup Node.js App to install', 'warning');
            }
            appendOutput('━━━━━━━━━━━━━━━━━━━━━━━━━━', 'info');
        }
    })
    .catch(function(error) { appendOutput('Error: ' + error.message, 'error'); });
}

function diagnoseComposerInstallation() {
    appendOutput('Diagnosing Composer installation...', 'info');
    fetch('{{ route("terminal.diagnose-composer") }}', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            var d = data.diagnostics;
            appendOutput('', 'info');
            appendOutput('━━━ COMPOSER DIAGNOSTICS ━━━', 'info');
            if (d.composer_found) {
                appendOutput('  Composer: ' + d.composer_path, 'success');
            } else {
                appendOutput('  Composer NOT FOUND', 'error');
            }
            if (d.composer_version) {
                appendOutput('  ' + d.composer_version, 'success');
            }
            appendOutput('  PHP: ' + d.php_version, 'success');
            appendOutput('  vendor/: ' + (d.vendor_exists ? 'YES' : 'NO') +
                ' | composer.json: ' + (d.composer_json_exists ? 'YES' : 'NO') +
                ' | composer.lock: ' + (d.composer_lock_exists ? 'YES' : 'NO'), 'info');
            if (!d.composer_found) {
                appendOutput('  Tip: Run Auto-Install or: curl -sS https://getcomposer.org/installer | php', 'warning');
            }
            appendOutput('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'info');
        }
    })
    .catch(function(error) { appendOutput('Error: ' + error.message, 'error'); });
}

function showNodeModulesPackages() {
    appendOutput('Loading installed npm packages...', 'info');
    fetch('{{ route("terminal.node-modules-list") }}', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            appendOutput(data.total + ' packages in ' + data.path, 'info');
            for (var i = 0; i < data.packages.length; i++) {
                appendOutput('  ' + data.packages[i].name + ' @ ' + data.packages[i].version, 'success');
            }
        } else {
            appendOutput(data.message || 'No packages found', 'warning');
        }
    })
    .catch(function(error) { appendOutput('Error: ' + error.message, 'error'); });
}

// ═══════════════ Composer Auto Install ═══════════════
function installComposerStepByStep() {
    appendOutput('', 'info');
    appendOutput('Installing Composer in project directory...', 'info');
    
    var steps = [
        { cmd: 'curl -sS https://getcomposer.org/installer | php', desc: 'Downloading composer.phar...', wait: 10000 },
        { cmd: 'php composer.phar --version', desc: 'Testing installation...', wait: 3000 }
    ];
    var i = 0;
    function executeStep() {
        if (i >= steps.length) {
            appendOutput('Composer installed! Now use: composer require [package]', 'success');
            appendOutput('The terminal auto-translates "composer" to "php composer.phar"', 'info');
            return;
        }
        appendOutput(steps[i].desc, 'info');
        executeQuickCommand(steps[i].cmd);
        var waitTime = steps[i].wait;
        i++;
        setTimeout(executeStep, waitTime);
    }
    executeStep();
}
</script>
@endsection
