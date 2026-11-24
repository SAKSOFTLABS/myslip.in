<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Session::isAuthenticated()) {
            Session::flash('warning', 'Please login to access your account.');
            Response::redirect('/login');
        }
    }
}
