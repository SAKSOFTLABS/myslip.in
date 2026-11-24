<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;

class SubscriptionMiddleware
{
    public function handle(Request $request): void
    {
        $companyId = Session::getCompanyId();
        if (!$companyId) {
            return;
        }

        $db = Database::getInstance();
        $sub = $db->fetchOne("SELECT * FROM `subscriptions` WHERE `company_id` = :company_id ORDER BY `id` DESC LIMIT 1", [
            'company_id' => $companyId
        ]);

        if (!$sub) {
            return;
        }

        $today = date('Y-m-d');
        $isExpired = false;
        $expiryDate = $sub['expiry_date'];

        if ($sub['status'] === 'trial' && $sub['trial_expiry_date']) {
            $expiryDate = $sub['trial_expiry_date'];
        }

        if ($expiryDate < $today || $sub['status'] === 'expired' || $sub['status'] === 'suspended') {
            $isExpired = true;
        }

        Session::set('is_subscription_expired', $isExpired);
        Session::set('subscription_expiry_date', $expiryDate);

        // If company is expired and attempting to create/update/cancel transactions via POST/PUT/DELETE
        if ($isExpired && in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $uri = $request->getUri();
            // Allow subscription upgrade, checkout, and logout routes
            if (!str_starts_with($uri, '/subscription') && !str_starts_with($uri, '/logout') && !str_starts_with($uri, '/company/switch')) {
                if ($request->isAjax()) {
                    Response::error('Your MYSLIP subscription/trial has expired. Please renew your plan to perform this operation.', [], 403);
                } else {
                    Session::flash('danger', 'Your MYSLIP subscription/trial has expired. Please upgrade or renew your plan.');
                    Response::redirect('/subscription/plans');
                }
            }
        }
    }
}
