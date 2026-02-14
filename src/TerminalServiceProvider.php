<?php

namespace Abdurrahman\LaravelTerminal;

use Illuminate\Support\ServiceProvider;

class TerminalServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/terminal.php', 'terminal');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/terminal.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'terminal');

        // Publishable assets
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/terminal.php' => config_path('terminal.php'),
            ], 'terminal-config');

            // Publish views (so users can customize them)
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/terminal'),
            ], 'terminal-views');
        }
    }
}
