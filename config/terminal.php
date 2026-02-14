<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Terminal Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for the terminal. By default, the terminal is accessible
    | at /terminal. You can change this to any prefix you want.
    |
    */
    'prefix' => 'terminal',

    /*
    |--------------------------------------------------------------------------
    | Terminal Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware applied to all terminal routes. By default, only
    | authenticated users with the 'auth' middleware can access the terminal.
    | You can add additional middleware like 'admin', 'can:access-terminal', etc.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Terminal Layout
    |--------------------------------------------------------------------------
    |
    | The Blade layout that the terminal views will extend. Change this to
    | match your application's admin layout.
    |
    */
    'layout' => 'layouts.admin',

    /*
    |--------------------------------------------------------------------------
    | Blocked Commands
    |--------------------------------------------------------------------------
    |
    | Commands that are completely blocked for security reasons.
    | These commands can never be executed through the terminal.
    |
    */
    'blocked_commands' => [
        'rm', 'rmdir', 'unlink', 'del',
        'dd', 'mkfs', 'fdisk',
        'kill', 'killall', 'pkill',
        'sudo', 'su',
        'reboot', 'shutdown', 'halt',
        'passwd', 'useradd', 'userdel',
        'iptables', 'firewall-cmd',
        'systemctl', 'service',
        'crontab',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Artisan Commands
    |--------------------------------------------------------------------------
    |
    | Artisan commands that can be executed directly (without interactive mode).
    | Add or remove commands as needed for your security requirements.
    |
    */
    'allowed_artisan_commands' => [
        'cache:clear',
        'config:clear',
        'config:cache',
        'route:clear',
        'route:cache',
        'view:clear',
        'view:cache',
        'optimize:clear',
        'migrate:status',
        'storage:link',
        'optimize',
        'about',
        'env',
        'list',
        'help',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed NPM Commands
    |--------------------------------------------------------------------------
    */
    'allowed_npm_commands' => [
        'install', 'i', 'run', 'build', 'dev', 'list', 'ls',
        'outdated', 'update', 'uninstall', 'remove',
        '--version', '-v', 'version', 'search', 'info', 'view',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Composer Commands
    |--------------------------------------------------------------------------
    */
    'allowed_composer_commands' => [
        'require', 'install', 'update', 'remove', 'show', 'list',
        'outdated', 'dump-autoload', 'dumpautoload',
        '--version', '-V', 'about', 'search', 'info',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Shell Commands
    |--------------------------------------------------------------------------
    */
    'allowed_shell_commands' => [
        'ls', 'dir', 'pwd', 'cd', 'find', 'locate', 'which', 'whereis',
        'cat', 'head', 'tail', 'less', 'more',
        'grep', 'awk', 'sed', 'sort', 'uniq', 'wc', 'cut',
        'file', 'stat', 'du', 'df',
        'curl', 'wget', 'ping',
        'php', 'node', 'python', 'python3', 'ruby', 'perl',
        'git',
        'tar', 'zip', 'unzip', 'gzip', 'gunzip',
        'echo', 'printf', 'date', 'whoami', 'hostname',
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Timeout (seconds)
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds a command can run before being terminated.
    |
    */
    'timeout' => 300,

];
