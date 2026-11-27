<?php
namespace App\Core;

/**
 * HTTP Response encapsulation
 */
class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function success(string $message = 'Success', array $data = [], int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    public static function error(string $message = 'An error occurred', array $errors = [], int $statusCode = 400): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    public static function redirect(string $url, int $statusCode = 302): void
    {
        // If relative application route path given, resolve through url()
        if (str_starts_with($url, '/')) {
            $url = url($url);
        }

        // Commit session data to disk/storage before redirecting
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$referer}");
        exit;
    }

    public static function abort(int $statusCode = 404, string $message = 'Page Not Found'): void
    {
        http_response_code($statusCode);
        View::renderError($statusCode, $message);
        exit;
    }
}
