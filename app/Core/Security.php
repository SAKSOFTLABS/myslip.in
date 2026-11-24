<?php
namespace App\Core;

/**
 * Security utilities: CSRF, XSS prevention, Password hashing, Sanitization
 */
class Security
{
    /**
     * Generate or return existing CSRF token
     */
    public static function generateCsrfToken(): string
    {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    /**
     * Render hidden CSRF form input HTML
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate submitted CSRF token
     */
    public static function validateCsrfToken(?string $token): bool
    {
        Session::start();
        $stored = Session::get('_csrf_token');
        if (!$stored || !$token) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    /**
     * Sanitize string for HTML output (XSS Prevention)
     */
    public static function e(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Hash password securely
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Verify password hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure random string token
     */
    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Sanitize array inputs recursively
     */
    public static function cleanInput(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([self::class, 'cleanInput'], $data);
        }
        if (is_string($data)) {
            return trim($data);
        }
        return $data;
    }
}
