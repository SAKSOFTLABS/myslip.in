<?php
namespace App\Core;

/**
 * Base Controller
 */
abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Render view template
     */
    protected function view(string $viewPath, array $data = [], string $layout = 'app'): void
    {
        View::render($viewPath, $data, $layout);
    }

    /**
     * Send JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

    /**
     * Send JSON success response
     */
    protected function jsonSuccess(string $message = 'Success', array $data = [], int $statusCode = 200): void
    {
        Response::success($message, $data, $statusCode);
    }

    /**
     * Send JSON error response
     */
    protected function jsonError(string $message = 'Error', array $errors = [], int $statusCode = 400): void
    {
        Response::error($message, $errors, $statusCode);
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }

    /**
     * Flash a success or error message
     */
    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    /**
     * Verify CSRF token from request
     */
    protected function validateCsrf(): void
    {
        $token = $this->request->input('_token');
        if (!Security::validateCsrfToken($token)) {
            $this->flash('danger', 'Invalid or expired session token. Please try again.');
            Response::back();
        }
    }
}
