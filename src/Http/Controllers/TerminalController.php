<?php

namespace Abdurrahman\LaravelTerminal\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Laravel Terminal Controller - handles all terminal operations
 */
class TerminalController extends Controller
{
    /**
     * Display the terminal dashboard
     */
    public function index()
    {
        return view('terminal::index');
    }

    /**
     * Execute artisan command or system command
     */
    public function executeCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        $command = trim($request->input('command'));
        $output = '';
        $status = 'success';

        try {
            // Parse command to get base command
            $commandParts = explode(' ', $command);
            $baseCommand = $commandParts[0];

            // Check for blocked dangerous commands first
            $blockedCommands = config('terminal.blocked_commands', [
                'rm', 'rmdir', 'unlink', 'del',
                'dd', 'mkfs', 'fdisk',
                'kill', 'killall', 'pkill',
                'sudo', 'su',
                'reboot', 'shutdown', 'halt',
                'passwd', 'useradd', 'userdel',
                'iptables', 'firewall-cmd',
                'systemctl', 'service',
                'crontab',
            ]);

            if (in_array($baseCommand, $blockedCommands)) {
                return response()->json([
                    'status' => 'error',
                    'output' => "⛔ Command '{$baseCommand}' is blocked for security reasons.\n\nThis command could harm your server or application."
                ]);
            }

            // Check if it's an npm command
            if (stripos($command, 'npm ') === 0) {
                return $this->executeNpmCommand($command);
            }

            // Check if it's a composer command
            if (stripos($command, 'composer ') === 0) {
                return $this->executeComposerCommand($command);
            }

            // Check if it's a node command
            if (stripos($command, 'node ') === 0) {
                return $this->executeNodeCommand($command);
            }

            // Check if it's a Python pip command
            if (preg_match('/^(pip|pip3|python -m pip|python3 -m pip)\s+/', $command)) {
                return $this->executePipCommand($command);
            }

            // Check if it's a PHP extension command
            if (stripos($command, 'pecl ') === 0 || stripos($command, 'pear ') === 0) {
                return $this->executePhpExtensionCommand($command);
            }

            // Check if it's a Ruby gem command
            if (stripos($command, 'gem ') === 0) {
                return $this->executeGemCommand($command);
            }

            // Check if it's a shell utility command
            $shellCommands = config('terminal.allowed_shell_commands', [
                'ls', 'dir', 'pwd', 'cd', 'find', 'locate', 'which', 'whereis',
                'cat', 'head', 'tail', 'less', 'more',
                'grep', 'awk', 'sed', 'sort', 'uniq', 'wc', 'cut',
                'file', 'stat', 'du', 'df',
                'curl', 'wget', 'ping',
                'php', 'node', 'python', 'python3', 'ruby', 'perl',
                'git',
                'tar', 'zip', 'unzip', 'gzip', 'gunzip',
                'echo', 'printf', 'date', 'whoami', 'hostname',
            ]);

            if (in_array($baseCommand, $shellCommands)) {
                return $this->executeShellCommand($command);
            }

            // Whitelist of allowed artisan commands for security
            $allowedCommands = config('terminal.allowed_artisan_commands', [
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
            ]);

            // Check if command is allowed
            if (!in_array($baseCommand, $allowedCommands)) {
                return response()->json([
                    'status' => 'error',
                    'output' => "❌ Command '{$baseCommand}' is not recognized.\n\n📦 Package Managers:\n  • npm install <package>\n  • composer require <package>\n  • pip install <package>\n  • pecl install <extension>\n  • gem install <gem>\n\n🔧 Shell Commands:\n  • ls, pwd, cat, grep, find, curl, wget\n  • php -v, node -v, git --version\n\n⚡ Laravel Commands:\n  " . implode(', ', $allowedCommands) . "\n\nType 'help' for more information."
                ]);
            }

            // Execute the artisan command
            Artisan::call($command);
            $output = Artisan::output();

            // Log the command
            Log::info("Terminal command executed: {$command}", [
                'user' => auth()->user()->email ?? 'unknown',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            $status = 'error';
            $output = "Error: " . $e->getMessage();
            
            Log::error("Terminal command failed: {$command}", [
                'error' => $e->getMessage(),
                'user' => auth()->user()->email ?? 'unknown'
            ]);
        }

        return response()->json([
            'status' => $status,
            'output' => $output ?: 'Command executed successfully with no output.'
        ]);
    }

    /**
     * Execute command with real-time output streaming (interactive mode)
     */
    public function executeInteractive(Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        // Extend PHP execution time to match our configured timeout
        $maxTime = config('terminal.timeout', 300);
        set_time_limit($maxTime + 30);

        $command = trim($request->input('command'));
        
        // Security check - reuse from executeCommand
        $commandParts = explode(' ', $command);
        $baseCommand = $commandParts[0];

        $blockedCommands = config('terminal.blocked_commands', [
            'rm', 'rmdir', 'unlink', 'del', 'dd', 'mkfs', 'fdisk',
            'kill', 'killall', 'pkill',  'sudo', 'su',
            'reboot', 'shutdown', 'halt', 'passwd', 'useradd', 'userdel',
            'iptables', 'firewall-cmd', 'systemctl', 'service', 'crontab',
        ]);

        if (in_array($baseCommand, $blockedCommands)) {
            return response()->json([
                'status' => 'error',
                'output' => "⛔ Command '{$baseCommand}' is blocked for security reasons."
            ]);
        }

        $projectPath = base_path();
        
        // Get actual user home directory
        $userHome = $this->getUserHome();
        
        // Auto-translate commands to use correct paths
        $command = $this->translateCommand($command, $projectPath, $userHome);

        // Force non-interactive mode for artisan commands to prevent hanging
        if (preg_match('/php\s+artisan\s+/', $command) && strpos($command, '--no-interaction') === false && strpos($command, '-n') === false) {
            $command .= ' --no-interaction';
        }

        // Set proper environment variables
        $composerHome = $projectPath . '/storage/composer';
        if (!is_dir($composerHome)) {
            @mkdir($composerHome, 0755, true);
        }

        $envVars = [
            'HOME' => $userHome,
            'COMPOSER_HOME' => $composerHome,
            'PATH' => $userHome . '/bin:/usr/local/bin:/usr/bin:/bin:/opt/cpanel/composer/bin:' . (getenv('PATH') ?: ''),
        ];

        // Prepare command with cd into project
        $fullCommand = "cd '{$projectPath}' && {$command} 2>&1";

        // Execute with real-time output
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];

        try {
            $process = proc_open($fullCommand, $descriptors, $pipes, $projectPath, $envVars);
            
            if (!is_resource($process)) {
                return response()->json([
                    'status' => 'error',
                    'output' => 'Failed to start command execution.'
                ]);
            }

            // Close stdin immediately to prevent commands from waiting for input
            fclose($pipes[0]);

            // Set streams to non-blocking
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            $output = '';
            $startTime = time();

            // Read output in chunks for real-time display
            while (!feof($pipes[1]) || !feof($pipes[2])) {
                $stdout = fgets($pipes[1], 4096);
                $stderr = fgets($pipes[2], 4096);
                
                if ($stdout !== false) {
                    $output .= $stdout;
                }
                if ($stderr !== false) {
                    $output .= $stderr;
                }

                // Check timeout
                if ((time() - $startTime) > $maxTime) {
                    proc_terminate($process);
                    $output .= "\n\n⚠️  Command timed out after " . ($maxTime / 60) . " minutes.";
                    break;
                }

                // Small delay to prevent CPU spinning
                if ($stdout === false && $stderr === false) {
                    usleep(100000); // 0.1 seconds
                }
            }

            fclose($pipes[1]);
            fclose($pipes[2]);
            $returnCode = proc_close($process);

            Log::info("Interactive command executed: {$command}", [
                'user' => auth()->user()->email ?? 'unknown',
                'return_code' => $returnCode
            ]);

            return response()->json([
                'status' => $returnCode === 0 ? 'success' : 'error',
                'output' => $output ?: '✅ Command completed successfully.',
                'return_code' => $returnCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send input to running process (for interactive prompts)
     */
    public function sendInput(Request $request)
    {
        $request->validate([
            'input' => 'required|string',
            'process_id' => 'required|string'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Input sent to process'
        ]);
    }

    /**
     * Execute npm command
     */
    private function executeNpmCommand($command)
    {
        try {
            $allowedNpmCommands = config('terminal.allowed_npm_commands', [
                'install', 'i', 'run', 'build', 'dev',
                'list', 'ls', 'outdated', 'update',
                'uninstall', 'remove',
                '--version', '-v', 'version',
                'search', 'info', 'view',
            ]);

            // Parse npm command
            preg_match('/^npm\s+(\S+)(.*)/', $command, $matches);
            if (!$matches) {
                return response()->json([
                    'status' => 'error',
                    'output' => 'Invalid npm command format.'
                ]);
            }

            $npmAction = $matches[1];
            $npmArgs = trim($matches[2] ?? '');

            if (!in_array($npmAction, $allowedNpmCommands)) {
                return response()->json([
                    'status' => 'error',
                    'output' => "npm command '{$npmAction}' is not allowed for security reasons.\n\nAllowed npm commands:\n" . implode(", ", $allowedNpmCommands)
                ]);
            }

            $projectPath = base_path();
            
            $result = $this->findExecutableWithDiagnostics('npm');
            
            if (!$result['found']) {
                return response()->json([
                    'status' => 'error',
                    'output' => "npm not found. Please ensure Node.js and npm are installed on the server.\n\n" . 
                               "Searched in the following locations:\n" . implode("\n", $result['searched']) . 
                               "\n\nFor cPanel:\n" .
                               "1. Go to cPanel → Setup Node.js App\n" .
                               "2. Create a Node.js application\n" .
                               "3. Or install via SSH: curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash"
                ]);
            }

            $npmPath = $result['path'];
            $fullCommand = "{$npmAction} {$npmArgs}";
            $output = '';
            $returnCode = 0;
            
            $nodeModulesPath = $projectPath . '/node_modules';
            if (!is_dir($nodeModulesPath)) {
                @mkdir($nodeModulesPath, 0755, true);
            }
            
            set_time_limit(config('terminal.timeout', 300));
            
            $bashCommand = "export HOME='" . getenv('HOME') . "'; cd '{$projectPath}' && ";
            $bashCommand .= "source ~/.bashrc 2>/dev/null; source ~/.bash_profile 2>/dev/null; ";
            $bashCommand .= "{$npmPath} {$fullCommand} 2>&1";
            
            $descriptorspec = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];
            
            $process = proc_open($bashCommand, $descriptorspec, $pipes);
            
            if (is_resource($process)) {
                fclose($pipes[0]);
                
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);
                
                fclose($pipes[1]);
                fclose($pipes[2]);
                
                $returnCode = proc_close($process);
                $output = trim($stdout . "\n" . $stderr);
            } else {
                $output = "Failed to execute npm command";
                $returnCode = 1;
            }
            
            if ($returnCode === 0 && $npmAction === 'install') {
                $nodeModulesCount = 0;
                if (is_dir($nodeModulesPath)) {
                    $iterator = new \FilesystemIterator($nodeModulesPath, \FilesystemIterator::SKIP_DOTS);
                    $nodeModulesCount = iterator_count($iterator);
                }
                $output .= "\n\n✅ Installation completed successfully!";
                $output .= "\n📦 Total packages in node_modules: {$nodeModulesCount}";
                $output .= "\n📁 Location: {$nodeModulesPath}";
            }

            Log::info("NPM command executed: {$fullCommand}", [
                'user' => auth()->user()->email ?? 'unknown',
                'return_code' => $returnCode,
                'output_length' => strlen($output)
            ]);

            return response()->json([
                'status' => $returnCode === 0 ? 'success' : 'error',
                'output' => $output ?: "Command executed but produced no output"
            ]);

        } catch (\Exception $e) {
            Log::error("NPM command failed: {$command}", [
                'error' => $e->getMessage(),
                'user' => auth()->user()->email ?? 'unknown'
            ]);

            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute composer command
     */
    private function executeComposerCommand($command)
    {
        try {
            $allowedComposerCommands = config('terminal.allowed_composer_commands', [
                'require', 'install', 'update', 'remove',
                'show', 'list', 'outdated',
                'dump-autoload', 'dumpautoload',
                '--version', '-V', 'about',
                'search', 'info',
            ]);

            // Parse composer command
            preg_match('/^composer\s+(\S+)(.*)/', $command, $matches);
            if (!$matches) {
                return response()->json([
                    'status' => 'error',
                    'output' => 'Invalid composer command format.'
                ]);
            }

            $composerAction = $matches[1];
            $composerArgs = trim($matches[2] ?? '');

            if (!in_array($composerAction, $allowedComposerCommands)) {
                return response()->json([
                    'status' => 'error',
                    'output' => "composer command '{$composerAction}' is not allowed for security reasons.\n\nAllowed composer commands:\n" . implode(", ", $allowedComposerCommands)
                ]);
            }

            $projectPath = base_path();
            
            $composerPath = $this->findComposerExecutable();
            
            if (!$composerPath) {
                return response()->json([
                    'status' => 'error',
                    'output' => "❌ Composer not found in project.\n\n" .
                               "💡 QUICK FIX - Install Composer in your project:\n" .
                               "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                               "Run this command in your terminal:\n\n" .
                               "1️⃣  Download Composer to project:\n" .
                               "   curl -sS https://getcomposer.org/installer | php\n\n" .
                               "2️⃣  Test it:\n" .
                               "   php composer.phar --version\n\n" .
                               "3️⃣  Use it:\n" .
                               "   php composer.phar require [package]\n\n" .
                               "📋 Click 'Auto-Install Composer' button above to install automatically."
                ]);
            }

            $composerHome = $projectPath . '/storage/composer';
            if (!is_dir($composerHome)) {
                @mkdir($composerHome, 0755, true);
            }

            $fullCommand = "{$composerPath} {$composerAction} {$composerArgs}";
            $output = '';
            $returnCode = 0;
            
            $userHome = $this->getUserHome();
            
            $bashCommand = "cd '{$projectPath}' && " .
                          "export HOME='{$userHome}' && " .
                          "export COMPOSER_HOME='{$composerHome}' && " .
                          "export PATH=\"{$userHome}/bin:/usr/local/bin:/usr/bin:/bin:/opt/cpanel/composer/bin:\$PATH\" && " .
                          "{$fullCommand} 2>&1";
            exec($bashCommand, $outputLines, $returnCode);
            $output = implode("\n", $outputLines);

            Log::info("Composer command executed: {$fullCommand}", [
                'user' => auth()->user()->email ?? 'unknown',
                'return_code' => $returnCode,
                'output' => $output
            ]);

            return response()->json([
                'status' => $returnCode === 0 ? 'success' : 'error',
                'output' => $output ?: "✅ composer {$composerAction} completed successfully!"
            ]);

        } catch (\Exception $e) {
            Log::error("Composer command failed: {$command}", [
                'error' => $e->getMessage(),
                'user' => auth()->user()->email ?? 'unknown'
            ]);

            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute node command
     */
    private function executeNodeCommand($command)
    {
        try {
            $allowedNodeCommands = [
                '--version',
                '-v',
            ];

            preg_match('/^node\s+(\S+)(.*)/', $command, $matches);
            if (!$matches) {
                return response()->json([
                    'status' => 'error',
                    'output' => 'Invalid node command format.'
                ]);
            }

            $nodeAction = $matches[1];
            $nodeArgs = trim($matches[2] ?? '');

            if (!in_array($nodeAction, $allowedNodeCommands)) {
                return response()->json([
                    'status' => 'error',
                    'output' => "node command '{$nodeAction}' is not allowed for security reasons.\n\nAllowed node commands:\n" . implode(", ", $allowedNodeCommands)
                ]);
            }

            $nodePath = $this->findExecutable('node');
            
            if (!$nodePath) {
                return response()->json([
                    'status' => 'error',
                    'output' => "node not found. Please ensure Node.js is installed on the server."
                ]);
            }

            $fullCommand = "{$nodePath} {$nodeAction} {$nodeArgs}";
            $output = '';
            $returnCode = 0;
            
            exec("{$fullCommand} 2>&1", $outputLines, $returnCode);
            $output = implode("\n", $outputLines);

            Log::info("Node command executed: {$fullCommand}", [
                'user' => auth()->user()->email ?? 'unknown',
                'return_code' => $returnCode,
                'output' => $output
            ]);

            return response()->json([
                'status' => $returnCode === 0 ? 'success' : 'error',
                'output' => $output ?: "✅ node {$nodeAction} completed successfully!"
            ]);

        } catch (\Exception $e) {
            Log::error("Node command failed: {$command}", [
                'error' => $e->getMessage(),
                'user' => auth()->user()->email ?? 'unknown'
            ]);

            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute Python pip command
     */
    private function executePipCommand($command)
    {
        try {
            set_time_limit(config('terminal.timeout', 300));

            preg_match('/^(pip3?|python3?\s+-m\s+pip)\s+(\w+)(.*)/', $command, $matches);
            if (!$matches) {
                return response()->json([
                    'status' => 'error',
                    'output' => 'Invalid pip command format.'
                ]);
            }

            $pipExecutor = $matches[1];
            $pipAction = $matches[2];
            $pipArgs = trim($matches[3] ?? '');

            $allowedPipCommands = [
                'install', 'list', 'show', 'search', 'freeze', '--version'
            ];

            if (!in_array($pipAction, $allowedPipCommands)) {
                return response()->json([
                    'status' => 'error',
                    'output' => "pip command '{$pipAction}' is not allowed.\n\nAllowed: " . implode(", ", $allowedPipCommands)
                ]);
            }

            $pipPath = null;
            if (strpos($pipExecutor, 'python') !== false) {
                $pythonPath = $this->findExecutable('python3') ?: $this->findExecutable('python');
                if ($pythonPath) {
                    $pipPath = "{$pythonPath} -m pip";
                }
            } else {
                $pipPath = $this->findExecutable('pip3') ?: $this->findExecutable('pip');
            }
            
            if (!$pipPath) {
                return response()->json([
                    'status' => 'error',
                    'output' => "❌ Python/pip not found on server.\n\n💡 Install Python via cPanel → Setup Python App"
                ]);
            }

            $fullCommand = "{$pipPath} {$pipAction} {$pipArgs}";
            
            if ($pipAction === 'install') {
                $descriptors = [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ];

                $process = proc_open($fullCommand . ' 2>&1', $descriptors, $pipes);
                
                if (is_resource($process)) {
                    fclose($pipes[0]);
                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    $error = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    $returnCode = proc_close($process);
                    
                    if ($error) {
                        $output .= "\n" . $error;
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'output' => 'Failed to execute pip command.'
                    ]);
                }
            } else {
                exec("{$fullCommand} 2>&1", $outputLines, $returnCode);
                $output = implode("\n", $outputLines);
            }

            Log::info("Pip command executed: {$fullCommand}", [
                'user' => auth()->user()->email ?? 'unknown',
                'return_code' => $returnCode ?? 0
            ]);

            return response()->json([
                'status' => ($returnCode ?? 0) === 0 ? 'success' : 'error',
                'output' => $output ?: "✅ pip {$pipAction} completed successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute PHP extension command (pecl/pear)
     */
    private function executePhpExtensionCommand($command)
    {
        try {
            set_time_limit(config('terminal.timeout', 300));

            preg_match('/^(pecl|pear)\s+(\w+)(.*)/', $command, $matches);
            if (!$matches) {
                return response()->json([
                    'status' => 'error',
                    'output' => 'Invalid pecl/pear command format.'
                ]);
            }

            $tool = $matches[1];
            $action = $matches[2];
            $args = trim($matches[3] ?? '');

            $allowedCommands = [
                'list', 'search', 'info', 'version', 'list-all', 'list-channels'
            ];

            if ($action === 'install') {
                $allowedCommands[] = 'install';
            }

            if (!in_array($action, $allowedCommands)) {
                return response()->json([
                    'status' => 'error',
                    'output' => "{$tool} command '{$action}' is not allowed.\n\nAllowed: " . implode(", ", $allowedCommands)
                ]);
            }

            $toolPath = $this->findExecutable($tool);
            
            if (!$toolPath) {
                return response()->json([
                    'status' => 'error',
                    'output' => "❌ {$tool} not found on server.\n\n💡 {$tool} is usually available in cPanel servers with PHP installed."
                ]);
            }

            $fullCommand = "{$toolPath} {$action} {$args}";
            exec("{$fullCommand} 2>&1", $outputLines, $returnCode);
            $output = implode("\n", $outputLines);

            if (strpos($output, 'Permission denied') !== false || strpos($output, 'must run as root') !== false) {
                $output .= "\n\n⚠️  This operation requires server admin permissions.\n💡 Contact your hosting provider or use cPanel's PHP Extensions interface.";
            }

            return response()->json([
                'status' => $returnCode === 0 ? 'success' : 'error',
                'output' => $output ?: "✅ {$tool} {$action} completed!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute Ruby gem command
     */
    private function executeGemCommand($command)
    {
        try {
            set_time_limit(config('terminal.timeout', 300));

            preg_match('/^gem\s+(\w+)(.*)/', $command, $matches);
            if (!$matches) {
                return response()->json([
                    'status' => 'error',
                    'output' => 'Invalid gem command format.'
                ]);
            }

            $action = $matches[1];
            $args = trim($matches[2] ?? '');

            $allowedCommands = [
                'install', 'list', 'search', 'info', 'outdated', 'version', 'env'
            ];

            if (!in_array($action, $allowedCommands)) {
                return response()->json([
                    'status' => 'error',
                    'output' => "gem command '{$action}' is not allowed.\n\nAllowed: " . implode(", ", $allowedCommands)
                ]);
            }

            $gemPath = $this->findExecutable('gem');
            
            if (!$gemPath) {
                return response()->json([
                    'status' => 'error',
                    'output' => "❌ Ruby/gem not found on server.\n\n💡 Install Ruby via cPanel → Setup Ruby App"
                ]);
            }

            $fullCommand = "{$gemPath} {$action} {$args}";
            
            if ($action === 'install') {
                $descriptors = [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ];

                $process = proc_open($fullCommand . ' 2>&1', $descriptors, $pipes);
                
                if (is_resource($process)) {
                    fclose($pipes[0]);
                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    $error = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    $returnCode = proc_close($process);
                    
                    if ($error) {
                        $output .= "\n" . $error;
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'output' => 'Failed to execute gem command.'
                    ]);
                }
            } else {
                exec("{$fullCommand} 2>&1", $outputLines, $returnCode);
                $output = implode("\n", $outputLines);
            }

            return response()->json([
                'status' => ($returnCode ?? 0) === 0 ? 'success' : 'error',
                'output' => $output ?: "✅ gem {$action} completed!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute shell utility command
     */
    private function executeShellCommand($command)
    {
        try {
            $dangerous = ['&&', '||', ';', '|', '>', '<', '`', '$', '(', ')'];
            foreach ($dangerous as $char) {
                if (strpos($command, $char) !== false && !in_array(explode(' ', $command)[0], ['grep', 'awk', 'sed', 'find'])) {
                    return response()->json([
                        'status' => 'error',
                        'output' => "⛔ Command contains potentially dangerous characters.\n\nFor security, command chaining is not allowed."
                    ]);
                }
            }

            if (preg_match('/^cd\s+(.*)/', $command, $matches)) {
                $targetDir = trim($matches[1]);
                if (empty($targetDir) || $targetDir === '~') {
                    $targetDir = base_path();
                } elseif ($targetDir === '..') {
                    $targetDir = dirname(base_path());
                } elseif (!str_starts_with($targetDir, '/')) {
                    $targetDir = base_path() . '/' . $targetDir;
                }

                if (is_dir($targetDir)) {
                    return response()->json([
                        'status' => 'success',
                        'output' => "📁 Directory: {$targetDir}\n\n💡 Note: Can't change directory in web terminal. Use 'ls {$targetDir}' to view contents."
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'output' => "Directory does not exist: {$targetDir}"
                    ]);
                }
            }

            $projectPath = base_path();
            
            $fullCommand = "cd '{$projectPath}' && {$command} 2>&1";
            exec($fullCommand, $outputLines, $returnCode);
            $output = implode("\n", $outputLines);

            if (empty($output)) {
                $output = "✅ Command executed successfully (no output).";
            }

            Log::info("Shell command executed: {$command}", [
                'user' => auth()->user()->email ?? 'unknown',
                'return_code' => $returnCode
            ]);

            return response()->json([
                'status' => $returnCode === 0 ? 'success' : 'warning',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Clear all caches
     */
    public function clearAllCaches()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('optimize:clear');

            $output = "✅ All caches cleared successfully!\n\n";
            $output .= "Cleared:\n";
            $output .= "- Application Cache\n";
            $output .= "- Configuration Cache\n";
            $output .= "- Route Cache\n";
            $output .= "- View Cache\n";
            $output .= "- Compiled Services\n";

            Log::info('All caches cleared via Terminal', [
                'user' => auth()->user()->email ?? 'unknown'
            ]);

            return response()->json([
                'status' => 'success',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Run migrations
     */
    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            Log::info('Migrations executed via Terminal', [
                'user' => auth()->user()->email ?? 'unknown'
            ]);

            return response()->json([
                'status' => 'success',
                'output' => $output ?: '✅ Migrations completed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check migration status
     */
    public function migrationStatus()
    {
        try {
            Artisan::call('migrate:status');
            $output = Artisan::output();

            return response()->json([
                'status' => 'success',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Optimize application
     */
    public function optimize()
    {
        try {
            Artisan::call('optimize');
            $output = "✅ Application optimized successfully!\n\n";
            $output .= Artisan::output();

            Log::info('Application optimized via Terminal', [
                'user' => auth()->user()->email ?? 'unknown'
            ]);

            return response()->json([
                'status' => 'success',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create storage link
     */
    public function storageLink()
    {
        try {
            Artisan::call('storage:link');
            $output = Artisan::output();

            return response()->json([
                'status' => 'success',
                'output' => $output ?: '✅ Storage link created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display error logs
     */
    public function errorLogs()
    {
        return view('terminal::error-logs');
    }

    /**
     * Fetch error logs data
     */
    public function getErrorLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            $errors = [];

            if (File::exists($logFile)) {
                $logContent = File::get($logFile);
                $lines = explode("\n", $logContent);
                
                $currentError = null;
                $errorCount = 0;
                $maxErrors = 20;

                for ($i = count($lines) - 1; $i >= 0 && $errorCount < $maxErrors; $i--) {
                    $line = $lines[$i];
                    
                    if (preg_match('/\[(.*?)\]\s+(local|production|testing)\.ERROR:(.*)/', $line, $matches)) {
                        if ($currentError) {
                            $errors[] = $currentError;
                            $errorCount++;
                        }
                        
                        $currentError = [
                            'timestamp' => $matches[1] ?? 'Unknown',
                            'environment' => $matches[2] ?? 'unknown',
                            'message' => trim($matches[3] ?? ''),
                            'stack_trace' => '',
                            'file' => 'Unknown',
                            'line' => 'Unknown',
                            'type' => 'ERROR'
                        ];
                    } elseif (preg_match('/\[(.*?)\]\s+(local|production|testing)\.(WARNING|CRITICAL|ALERT|EMERGENCY):(.*)/', $line, $matches)) {
                        if ($currentError) {
                            $errors[] = $currentError;
                            $errorCount++;
                        }
                        
                        $currentError = [
                            'timestamp' => $matches[1] ?? 'Unknown',
                            'environment' => $matches[2] ?? 'unknown',
                            'message' => trim($matches[4] ?? ''),
                            'stack_trace' => '',
                            'file' => 'Unknown',
                            'line' => 'Unknown',
                            'type' => $matches[3] ?? 'ERROR'
                        ];
                    } elseif ($currentError && !empty(trim($line))) {
                        $currentError['stack_trace'] = trim($line) . "\n" . $currentError['stack_trace'];
                        
                        if (preg_match('/([\/\w\.]+\.php):(\d+)/', $line, $fileMatches)) {
                            $currentError['file'] = basename($fileMatches[1]);
                            $currentError['line'] = $fileMatches[2];
                        }
                    }
                }
                
                if ($currentError && $errorCount < $maxErrors) {
                    $errors[] = $currentError;
                }
            }

            return response()->json([
                'status' => 'success',
                'errors' => $errors,
                'total' => count($errors),
                'log_file' => $logFile,
                'file_exists' => File::exists($logFile),
                'file_size' => File::exists($logFile) ? File::size($logFile) : 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => []
            ]);
        }
    }

    /**
     * Clear log files
     */
    public function clearLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (File::exists($logFile)) {
                File::put($logFile, '');
                
                Log::info('Log files cleared via Terminal', [
                    'user' => auth()->user()->email ?? 'unknown'
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => '✅ Log file cleared successfully!'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Log file does not exist.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download log file
     */
    public function downloadLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (File::exists($logFile)) {
                return response()->download($logFile, 'laravel-' . date('Y-m-d-His') . '.log');
            }

            return redirect()->back()->with('error', 'Log file does not exist.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Get system information
     */
    public function systemInfo()
    {
        try {
            $info = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'database_driver' => config('database.default'),
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
                'session_driver' => config('session.driver'),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'disk_free_space' => $this->formatBytes(disk_free_space('/')),
                'disk_total_space' => $this->formatBytes(disk_total_space('/')),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time') . 's',
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ];

            return response()->json([
                'status' => 'success',
                'info' => $info
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Diagnose npm installation
     */
    public function diagnoseNpm()
    {
        try {
            $result = $this->findExecutableWithDiagnostics('npm');
            $nodeResult = $this->findExecutableWithDiagnostics('node');
            
            $diagnostics = [
                'npm_found' => $result['found'],
                'npm_path' => $result['path'] ?? 'Not found',
                'npm_searched_paths' => $result['searched'],
                'node_found' => $nodeResult['found'],
                'node_path' => $nodeResult['path'] ?? 'Not found',
                'home_directory' => getenv('HOME') ?: '/home/' . get_current_user(),
                'current_user' => get_current_user(),
                'path_env' => getenv('PATH') ?: 'Not set',
                'project_path' => base_path(),
            ];

            if ($result['found']) {
                $output = [];
                exec("{$result['path']} --version 2>&1", $output, $returnCode);
                $diagnostics['npm_version'] = $returnCode === 0 ? trim(implode("\n", $output)) : 'Unable to determine';
            }

            if ($nodeResult['found']) {
                $output = [];
                exec("{$nodeResult['path']} --version 2>&1", $output, $returnCode);
                $diagnostics['node_version'] = $returnCode === 0 ? trim(implode("\n", $output)) : 'Unable to determine';
            }

            $diagnostics['node_modules_exists'] = is_dir(base_path('node_modules'));
            $diagnostics['node_modules_writable'] = is_dir(base_path('node_modules')) ? is_writable(base_path('node_modules')) : 'N/A';
            $diagnostics['package_json_exists'] = file_exists(base_path('package.json'));

            return response()->json([
                'status' => 'success',
                'diagnostics' => $diagnostics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * List installed node_modules packages
     */
    public function nodeModulesList()
    {
        try {
            $nodeModulesPath = base_path('node_modules');
            $packages = [];
            
            if (!is_dir($nodeModulesPath)) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'node_modules folder does not exist yet. Install some packages first!',
                    'packages' => [],
                    'total' => 0
                ]);
            }
            
            $iterator = new \DirectoryIterator($nodeModulesPath);
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isDot() || $fileInfo->getFilename() === '.bin') {
                    continue;
                }
                
                if ($fileInfo->isDir()) {
                    $packageName = $fileInfo->getFilename();
                    $packageJsonPath = $nodeModulesPath . '/' . $packageName . '/package.json';
                    
                    $version = 'unknown';
                    $description = '';
                    
                    if (file_exists($packageJsonPath)) {
                        $packageData = json_decode(file_get_contents($packageJsonPath), true);
                        $version = $packageData['version'] ?? 'unknown';
                        $description = $packageData['description'] ?? '';
                    }
                    
                    if (strpos($packageName, '@') === 0) {
                        $scopedIterator = new \DirectoryIterator($nodeModulesPath . '/' . $packageName);
                        foreach ($scopedIterator as $scopedFile) {
                            if ($scopedFile->isDot() || !$scopedFile->isDir()) {
                                continue;
                            }
                            
                            $fullPackageName = $packageName . '/' . $scopedFile->getFilename();
                            $scopedPackageJsonPath = $nodeModulesPath . '/' . $fullPackageName . '/package.json';
                            
                            $scopedVersion = 'unknown';
                            $scopedDescription = '';
                            
                            if (file_exists($scopedPackageJsonPath)) {
                                $scopedPackageData = json_decode(file_get_contents($scopedPackageJsonPath), true);
                                $scopedVersion = $scopedPackageData['version'] ?? 'unknown';
                                $scopedDescription = $scopedPackageData['description'] ?? '';
                            }
                            
                            $packages[] = [
                                'name' => $fullPackageName,
                                'version' => $scopedVersion,
                                'description' => $scopedDescription
                            ];
                        }
                    } else {
                        $packages[] = [
                            'name' => $packageName,
                            'version' => $version,
                            'description' => $description
                        ];
                    }
                }
            }
            
            usort($packages, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            
            return response()->json([
                'status' => 'success',
                'packages' => $packages,
                'total' => count($packages),
                'path' => $nodeModulesPath
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Diagnose Composer installation
     */
    public function diagnoseComposer()
    {
        try {
            $projectPath = base_path();
            $composerResult = $this->findExecutableWithDiagnostics('composer');
            
            $diagnostics = [
                'composer_found' => $composerResult['found'],
                'composer_path' => $composerResult['path'],
                'searched_paths' => $composerResult['searched'],
                'composer_version' => null,
                'php_version' => PHP_VERSION,
                'vendor_exists' => is_dir($projectPath . '/vendor'),
                'composer_json_exists' => file_exists($projectPath . '/composer.json'),
                'composer_lock_exists' => file_exists($projectPath . '/composer.lock'),
            ];

            if ($composerResult['found']) {
                $versionOutput = [];
                exec($composerResult['path'] . ' --version 2>&1', $versionOutput, $returnCode);
                if ($returnCode === 0 && !empty($versionOutput)) {
                    $diagnostics['composer_version'] = implode(' ', $versionOutput);
                }
            }

            return response()->json([
                'status' => 'success',
                'diagnostics' => $diagnostics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Find executable path
     */
    private function findExecutable($name)
    {
        $result = $this->findExecutableWithDiagnostics($name);
        return $result['found'] ? $result['path'] : null;
    }

    /**
     * Find executable with detailed diagnostics
     */
    private function findExecutableWithDiagnostics($name)
    {
        $searchedPaths = [];
        $home = getenv('HOME') ?: '/home/' . get_current_user();
        
        $commonPaths = [
            "/usr/bin/{$name}",
            "/usr/local/bin/{$name}",
            "/opt/cpanel/composer/bin/{$name}",
            "/usr/local/cpanel/3rdparty/bin/{$name}",
            "{$home}/bin/{$name}",
            "{$home}/.local/bin/{$name}",
        ];

        if ($name === 'npm' || $name === 'node') {
            $nodePaths = [
                "{$home}/.nvm/versions/node/*/bin/{$name}",
                "{$home}/.nvm/current/bin/{$name}",
                "{$home}/.nvm/alias/default/bin/{$name}",
                "{$home}/nodevenv/*/bin/{$name}",
                "{$home}/.nodejs/bin/{$name}",
                "/opt/alt/alt-nodejs*/root/usr/bin/{$name}",
                "/opt/remi/*/root/usr/bin/{$name}",
                "/usr/local/nodejs/bin/{$name}",
                "/usr/local/node/bin/{$name}",
            ];
            $commonPaths = array_merge($commonPaths, $nodePaths);
        }

        foreach ($commonPaths as $path) {
            $searchedPaths[] = $path;
            
            if (strpos($path, '*') !== false) {
                $matches = glob($path);
                if (!empty($matches)) {
                    foreach ($matches as $match) {
                        if (is_executable($match)) {
                            return [
                                'found' => true,
                                'path' => $match,
                                'searched' => $searchedPaths
                            ];
                        }
                    }
                }
            } elseif (file_exists($path) && is_executable($path)) {
                return [
                    'found' => true,
                    'path' => $path,
                    'searched' => $searchedPaths
                ];
            }
        }

        $searchedPaths[] = "which {$name}";
        $output = [];
        exec("which {$name} 2>/dev/null", $output, $returnCode);
        if ($returnCode === 0 && !empty($output[0])) {
            $foundPath = trim($output[0]);
            if (is_executable($foundPath)) {
                return [
                    'found' => true,
                    'path' => $foundPath,
                    'searched' => $searchedPaths
                ];
            }
        }

        $searchedPaths[] = "bash -lc 'which {$name}'";
        $output = [];
        $bashCommand = "export HOME='{$home}'; source ~/.bashrc 2>/dev/null; source ~/.bash_profile 2>/dev/null; which {$name} 2>/dev/null";
        exec($bashCommand, $output, $returnCode);
        if ($returnCode === 0 && !empty($output[0])) {
            $foundPath = trim($output[0]);
            if (is_executable($foundPath)) {
                return [
                    'found' => true,
                    'path' => $foundPath,
                    'searched' => $searchedPaths
                ];
            }
        }

        $searchedPaths[] = "PATH environment variable";
        $pathEnv = getenv('PATH');
        if ($pathEnv) {
            $pathDirs = explode(':', $pathEnv);
            foreach ($pathDirs as $dir) {
                $fullPath = rtrim($dir, '/') . '/' . $name;
                if (file_exists($fullPath) && is_executable($fullPath)) {
                    return [
                        'found' => true,
                        'path' => $fullPath,
                        'searched' => $searchedPaths
                    ];
                }
            }
        }

        return [
            'found' => false,
            'path' => null,
            'searched' => $searchedPaths
        ];
    }

    /**
     * Find composer executable with multiple fallback options
     */
    private function findComposerExecutable()
    {
        $projectPath = base_path();
        
        $home = getenv('HOME');
        if (!$home && function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
            $userInfo = posix_getpwuid(posix_getuid());
            $home = $userInfo['dir'] ?? null;
        }
        if (!$home) {
            $home = '/home/' . get_current_user();
        }
        
        $composerLocations = [
            $projectPath . '/composer.phar' => 'php composer.phar',
            $home . '/bin/composer' => $home . '/bin/composer',
            '/usr/local/bin/composer' => '/usr/local/bin/composer',
            '/usr/bin/composer' => '/usr/bin/composer',
            '/opt/cpanel/composer/bin/composer' => '/opt/cpanel/composer/bin/composer',
            '/usr/local/cpanel/3rdparty/bin/composer' => '/usr/local/cpanel/3rdparty/bin/composer',
            '/opt/alt/php' . PHP_MAJOR_VERSION . PHP_MINOR_VERSION . '/usr/bin/composer' => '/opt/alt/php' . PHP_MAJOR_VERSION . PHP_MINOR_VERSION . '/usr/bin/composer',
            $home . '/composer.phar' => 'php ' . $home . '/composer.phar',
            $home . '/bin/composer.phar' => 'php ' . $home . '/bin/composer.phar',
            $home . '/.config/composer/vendor/bin/composer' => $home . '/.config/composer/vendor/bin/composer',
            $home . '/.composer/vendor/bin/composer' => $home . '/.composer/vendor/bin/composer',
        ];
        
        foreach ($composerLocations as $path => $command) {
            if (file_exists($path) && is_readable($path)) {
                return $command;
            }
        }
        
        $output = [];
        $testCommand = "composer --version 2>&1";
        exec($testCommand, $output, $returnCode);
        if ($returnCode === 0 && !empty($output)) {
            $whichOutput = [];
            exec("which composer 2>/dev/null", $whichOutput, $whichReturn);
            if ($whichReturn === 0 && !empty($whichOutput[0])) {
                return trim($whichOutput[0]);
            }
            return 'composer';
        }
        
        $output = [];
        exec("export PATH=\"{$home}/bin:/opt/cpanel/composer/bin:/usr/local/cpanel/3rdparty/bin:\$PATH\" && which composer 2>/dev/null", $output, $returnCode);
        if ($returnCode === 0 && !empty($output[0])) {
            return trim($output[0]);
        }
        
        $output = [];
        exec('which composer.phar 2>/dev/null', $output, $returnCode);
        if ($returnCode === 0 && !empty($output[0])) {
            return 'php ' . trim($output[0]);
        }
        
        return null;
    }

    /**
     * Get the actual user home directory
     */
    private function getUserHome()
    {
        $home = getenv('HOME');
        if (!$home && function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
            $userInfo = posix_getpwuid(posix_getuid());
            $home = $userInfo['dir'] ?? null;
        }
        if (!$home) {
            $home = '/home/' . get_current_user();
        }
        return $home;
    }

    /**
     * Translate commands to use correct executable paths
     */
    private function translateCommand($command, $projectPath, $userHome)
    {
        $parts = explode(' ', $command, 2);
        $base = $parts[0];
        $args = $parts[1] ?? '';

        if ($base === 'composer') {
            $composerPath = $this->findComposerExecutable();
            if ($composerPath) {
                return $composerPath . ($args ? ' ' . $args : '');
            }
            if (file_exists($projectPath . '/composer.phar')) {
                return 'php composer.phar' . ($args ? ' ' . $args : '');
            }
        }

        if ($base === 'php' && isset($parts[1])) {
            $phpArgs = trim($parts[1]);
            if (strpos($phpArgs, 'composer ') === 0 || $phpArgs === 'composer') {
                $composerArgs = substr($phpArgs, 9);
                if (file_exists($projectPath . '/composer.phar')) {
                    return 'php composer.phar' . ($composerArgs ? ' ' . $composerArgs : '');
                }
            }
        }

        if ($base === 'artisan') {
            return 'php artisan' . ($args ? ' ' . $args : '');
        }

        if ($base === 'npm') {
            $npmPath = $this->findExecutable('npm');
            if ($npmPath) {
                return $npmPath . ($args ? ' ' . $args : '');
            }
        }

        if ($base === 'node') {
            $nodePath = $this->findExecutable('node');
            if ($nodePath) {
                return $nodePath . ($args ? ' ' . $args : '');
            }
        }

        return $command;
    }
}
