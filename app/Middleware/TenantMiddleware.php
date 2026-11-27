<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;

class TenantMiddleware
{
    public function handle(Request $request): void
    {
        $companyId = Session::getCompanyId();
        $userId = Session::getUserId();
        $db = Database::getInstance();

        $company = null;
        if ($companyId) {
            // Verify active company actually exists in database
            $company = $db->fetchOne("SELECT * FROM `companies` WHERE `id` = :id LIMIT 1", ['id' => $companyId]);
        }

        if (!$company) {
            // Company not in session OR was deleted from database: auto-heal session
            if (Session::isSuperAdmin()) {
                // If super admin, pick first available company
                $company = $db->fetchOne("SELECT * FROM `companies` ORDER BY `id` ASC LIMIT 1");
                $roleId = 1;
            } else {
                // Check if user owns or belongs to any active company
                $sql = "SELECT c.*, cu.role_id 
                        FROM `companies` c
                        JOIN `company_users` cu ON c.id = cu.company_id
                        WHERE cu.user_id = :user_id AND cu.status = 'active'
                        ORDER BY c.id ASC LIMIT 1";
                $company = $db->fetchOne($sql, ['user_id' => $userId]);
                $roleId = $company ? (int)$company['role_id'] : 1;
            }

            if (!$company) {
                // If on company creation route, allow through
                if ($request->getUri() === '/company/create' || $request->getUri() === '/company/store' || str_starts_with($request->getUri(), '/admin')) {
                    return;
                }
                Session::flash('info', 'Please set up your company details to get started.');
                Response::redirect('/company/create');
                return;
            }

            // Fetch current financial year
            $fySql = "SELECT * FROM `financial_years` WHERE `company_id` = :company_id AND `is_current` = 1 LIMIT 1";
            $fy = $db->fetchOne($fySql, ['company_id' => $company['id']]);

            Session::setTenant($company, $fy, $roleId ?? 1);
        }
    }
}
