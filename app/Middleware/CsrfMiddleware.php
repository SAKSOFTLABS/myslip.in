<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $token = $request->input('_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!Security::validateCsrfToken($token)) {
                if ($request->isAjax()) {
                    Response::error('Invalid or expired CSRF security token.', [], 419);
                } else {
                    Session::flash('danger', 'Your session has expired. Please try again.');
                    Response::back();
                }
            }
        }
    }
}
