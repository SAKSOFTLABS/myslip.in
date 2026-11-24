<?php
namespace App\Core;

use Throwable;

/**
 * Application Bootstrapper & Lifecycle Orchestrator
 */
class App
{
    /**
     * Load environment variables from .env if present
     */
    private static function loadEnvironment(): void
    {
        $envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, " \t\n\r\0\x0B\"'");
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    public static function run(): void
    {
        // 0. Load Environment Variables (.env if exists)
        self::loadEnvironment();

        // 1. Load Helpers & Constants
        require_once __DIR__ . '/../Config/constants.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        // 2. Set Timezone & Error Reporting
        $config = require __DIR__ . '/../Config/app.php';
        date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');

        if ($config['debug']) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        // Set default UTF-8 response header
        if (!headers_sent() && php_sapi_name() !== 'cli') {
            header('Content-Type: text/html; charset=UTF-8');
        }

        // 3. Register Autoloader for App Classes
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $baseDir = __DIR__ . '/../';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });

        // 4. Initialize Secure Session
        Session::start();

        // 5. Load Web and API Routes
        require_once __DIR__ . '/../../routes/web.php';

        // 6. Handle Request
        try {
            $request = new Request();
            Router::dispatch($request);
        } catch (Throwable $e) {
            error_log("Unhandled Application Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if ($config['debug']) {
                echo "<div style='font-family:monospace; background:#fee2e2; color:#991b1b; padding:20px; border-radius:8px; margin:20px;'>";
                echo "<h3>Application Error</h3>";
                echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")</p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                echo "</div>";
            } else {
                Response::abort(500, "Something went wrong. Please try again or contact support.");
            }
        }
    }
}
