<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class SuperAdminMiddleware
{
    public function handle(Request $request): void
    {
        if (!Session::isAuthenticated()) {
            Session::flash('warning', 'Please login to access the admin panel.');
            Response::redirect('/login');
        }

        if (!Session::isSuperAdmin()) {
            Session::flash('danger', 'Access Denied: Super Admin privileges are required to access the admin portal.');
            Response::redirect('/dashboard');
        }
    }
}
