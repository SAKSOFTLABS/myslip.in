<?php
namespace App\Core;

/**
 * Secure Session & Flash Message Manager
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../Config/app.php';
            $sessConfig = $config['session'];

            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                       (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                       (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
                       (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

            // Only enforce secure cookies if the connection is truly HTTPS
            $cookieSecure = !empty($sessConfig['secure']) && $isHttps;

            session_name($sessConfig['name']);
            session_set_cookie_params([
                'lifetime' => $sessConfig['lifetime'] ?? 86400 * 7,
                'path' => '/',
                'domain' => '',
                'secure' => $cookieSecure,
                'httponly' => true,
                'samesite' => $sessConfig['samesite'] ?? 'Lax'
            ]);

            session_start();

            if (isset($_SESSION['currency_symbol'])) {
                $curr = $_SESSION['currency_symbol'];
                if (empty($curr) || str_contains($curr, 'Ô') || str_contains($curr, 'â') || str_contains($curr, 'Ã')) {
                    $_SESSION['currency_symbol'] = '₹';
                }
            }
        }
    }

    public static function isGstEnabled(): bool
    {
        if (!isset($_SESSION['gst_enabled'])) {
            return false;
        }
        return !empty($_SESSION['gst_enabled']) && $_SESSION['gst_enabled'] !== '0' && $_SESSION['gst_enabled'] !== 0;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if ($key === 'gst_enabled') {
            return self::isGstEnabled();
        }
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(false);
        }
    }

    // Flash Messages
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    public static function getFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    // Authentication & Tenant Helpers
    public static function login(array $user): void
    {
        self::regenerate();
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_super_admin'] = (bool)($user['is_super_admin'] ?? false);
    }

    public static function setTenant(array $company, ?array $financialYear = null, ?int $roleId = null): void
    {
        $_SESSION['company_id'] = (int)$company['id'];
        $_SESSION['company_name'] = $company['company_name'];
        $_SESSION['gst_enabled'] = !empty($company['gst_enabled']) && $company['gst_enabled'] !== '0' && $company['gst_enabled'] !== 0;

        $curr = $company['currency_symbol'] ?? '₹';
        if (empty($curr) || str_contains($curr, 'Ô') || str_contains($curr, 'â') || str_contains($curr, 'Ã')) {
            $curr = '₹';
        }
        $_SESSION['currency_symbol'] = $curr;

        $_SESSION['company_status'] = $company['status'];
        $_SESSION['company_state'] = $company['state'];
        $_SESSION['company_state_code'] = $company['state_code'];

        if ($financialYear) {
            $_SESSION['financial_year_id'] = (int)$financialYear['id'];
            $_SESSION['financial_year_name'] = $financialYear['fy_name'];
        }

        if ($roleId !== null) {
            $_SESSION['role_id'] = (int)$roleId;
        }
    }

    public static function getUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function getCompanyId(): ?int
    {
        return isset($_SESSION['company_id']) ? (int)$_SESSION['company_id'] : null;
    }

    public static function getFinancialYearId(): ?int
    {
        return isset($_SESSION['financial_year_id']) ? (int)$_SESSION['financial_year_id'] : null;
    }

    public static function isSuperAdmin(): bool
    {
        return !empty($_SESSION['is_super_admin']);
    }

    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function hasCompany(): bool
    {
        return !empty($_SESSION['company_id']);
    }
}
