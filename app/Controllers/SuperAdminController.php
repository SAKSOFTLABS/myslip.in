<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Company;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\AuditLog;

class SuperAdminController extends Controller
{
    private Company $companyModel;
    private User $userModel;
    private Plan $planModel;

    public function __construct()
    {
        parent::__construct();
        $this->companyModel = new Company();
        $this->userModel = new User();
        $this->planModel = new Plan();
    }

    public function dashboard(): void
    {
        $db = Database::getInstance();

        $totalCompanies = (int)$db->fetchValue("SELECT COUNT(*) FROM `companies`");
        $activeCompanies = (int)$db->fetchValue("SELECT COUNT(*) FROM `companies` WHERE `status` = 'active'");
        $totalUsers = (int)$db->fetchValue("SELECT COUNT(*) FROM `users`");
        $totalRevenue = (float)$db->fetchValue("SELECT COALESCE(SUM(amount_paid), 0) FROM `subscriptions` WHERE `status` = 'active'");

        $recentCompanies = $db->fetchAll(
            "SELECT c.*, u.full_name as owner_name, u.email as owner_email 
             FROM `companies` c 
             LEFT JOIN `users` u ON c.owner_user_id = u.id 
             ORDER BY c.id DESC LIMIT 10"
        );

        $this->view('superadmin.dashboard', [
            'pageTitle' => 'Super Admin Portal',
            'totalCompanies' => $totalCompanies,
            'activeCompanies' => $activeCompanies,
            'totalUsers' => $totalUsers,
            'totalRevenue' => $totalRevenue,
            'recentCompanies' => $recentCompanies
        ], 'superadmin');
    }

    public function companies(): void
    {
        $db = Database::getInstance();
        $companies = $db->fetchAll(
            "SELECT c.*, u.full_name as owner_name, u.email as owner_email,
                    (SELECT COUNT(*) FROM `sales` s WHERE s.company_id = c.id) as total_sales_count,
                    (SELECT COALESCE(SUM(s.grand_total), 0) FROM `sales` s WHERE s.company_id = c.id AND s.status = 'active') as total_sales_val
             FROM `companies` c 
             LEFT JOIN `users` u ON c.owner_user_id = u.id 
             ORDER BY c.id DESC"
        );

        $this->view('superadmin.companies', [
            'pageTitle' => 'Tenant Companies Directory',
            'companies' => $companies
        ], 'superadmin');
    }

    public function toggleCompanyStatus(int $id): void
    {
        $this->validateCsrf();
        $company = $this->companyModel->find($id);

        if ($company) {
            $newStatus = ($company['status'] === 'active') ? 'suspended' : 'active';
            $this->companyModel->update($id, ['status' => $newStatus]);
            AuditLog::log("Company Status Changed to {$newStatus}", 'SuperAdmin', $id);
            $this->flash('success', "Company [{$company['company_name']}] is now {$newStatus}.");
        }

        $this->redirect('/admin/companies');
    }

    public function plans(): void
    {
        $plans = $this->planModel->all([], 'price ASC');

        $this->view('superadmin.plans', [
            'pageTitle' => 'SaaS Subscription Plans',
            'plans' => $plans
        ], 'superadmin');
    }

    public function updatePlan(int $id): void
    {
        $this->validateCsrf();
        $plan = $this->planModel->find($id);

        if ($plan) {
            $price = (float)$this->request->input('price', $plan['price']);
            $maxInvoices = (int)$this->request->input('max_invoices_month', $plan['max_invoices_month']);
            $maxItems = (int)$this->request->input('max_items', $plan['max_items']);

            $this->planModel->update($id, [
                'price' => $price,
                'max_invoices_month' => $maxInvoices,
                'max_items' => $maxItems
            ]);

            $this->flash('success', "Plan [{$plan['name']}] updated successfully.");
        }

        $this->redirect('/admin/plans');
    }

    public function users(): void
    {
        $db = Database::getInstance();
        $users = $db->fetchAll(
            "SELECT u.*, 
                    (SELECT COUNT(*) FROM `company_users` cu WHERE cu.user_id = u.id) as company_count 
             FROM `users` u 
             ORDER BY u.id DESC"
        );

        $this->view('superadmin.users', [
            'pageTitle' => 'Registered Users Directory',
            'users' => $users
        ], 'superadmin');
    }

    public function logs(): void
    {
        $db = Database::getInstance();
        $logs = $db->fetchAll(
            "SELECT al.*, u.full_name as user_name, c.company_name 
             FROM `audit_logs` al 
             LEFT JOIN `users` u ON al.user_id = u.id 
             LEFT JOIN `companies` c ON al.company_id = c.id 
             ORDER BY al.id DESC LIMIT 100"
        );

        $this->view('superadmin.logs', [
            'pageTitle' => 'System Audit Logs',
            'logs' => $logs
        ], 'superadmin');
    }
}
