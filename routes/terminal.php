<?php

use Illuminate\Support\Facades\Route;
use Abdurrahman\LaravelTerminal\Http\Controllers\TerminalController;

Route::middleware(config('terminal.middleware', ['web', 'auth']))
    ->prefix(config('terminal.prefix', 'terminal'))
    ->name('terminal.')
    ->group(function () {
        Route::get('/', [TerminalController::class, 'index'])->name('index');
        Route::post('/execute', [TerminalController::class, 'executeCommand'])->name('execute');
        Route::post('/execute-interactive', [TerminalController::class, 'executeInteractive'])->name('execute-interactive');
        Route::post('/send-input', [TerminalController::class, 'sendInput'])->name('send-input');
        Route::get('/diagnose-composer', [TerminalController::class, 'diagnoseComposer'])->name('diagnose-composer');
        Route::post('/clear-all-caches', [TerminalController::class, 'clearAllCaches'])->name('clear-all-caches');
        Route::post('/migrate', [TerminalController::class, 'runMigrations'])->name('migrate');
        Route::get('/migrate-status', [TerminalController::class, 'migrationStatus'])->name('migrate-status');
        Route::post('/optimize', [TerminalController::class, 'optimize'])->name('optimize');
        Route::post('/storage-link', [TerminalController::class, 'storageLink'])->name('storage-link');
        Route::get('/error-logs', [TerminalController::class, 'errorLogs'])->name('error-logs');
        Route::get('/get-error-logs', [TerminalController::class, 'getErrorLogs'])->name('get-error-logs');
        Route::post('/clear-logs', [TerminalController::class, 'clearLogs'])->name('clear-logs');
        Route::get('/download-logs', [TerminalController::class, 'downloadLogs'])->name('download-logs');
        Route::get('/system-info', [TerminalController::class, 'systemInfo'])->name('system-info');
        Route::get('/diagnose-npm', [TerminalController::class, 'diagnoseNpm'])->name('diagnose-npm');
        Route::get('/node-modules-list', [TerminalController::class, 'nodeModulesList'])->name('node-modules-list');
    });
