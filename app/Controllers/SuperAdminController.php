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

    public function deleteCompany(int $id): void
    {
        $this->validateCsrf();
        $db = Database::getInstance();

        $company = $this->companyModel->find($id);
        if (!$company) {
            $this->flash('danger', 'Tenant company not found.');
            $this->redirect('/admin/companies');
        }

        try {
            $db->transaction(function(Database $db) use ($id, $company) {
                // Delete in reverse dependency order to avoid foreign key errors
                $db->execute("DELETE FROM `sale_items` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `purchase_items` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `sales_return_items` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `purchase_return_items` WHERE `company_id` = :id", ['id' => $id]);

                $db->execute("DELETE FROM `sales` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `purchases` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `sales_returns` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `purchase_returns` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `receipts` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `payments` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `journals` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `ledger_entries` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `stock_movements` WHERE `company_id` = :id", ['id' => $id]);

                $db->execute("DELETE FROM `items` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `item_groups` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `customers` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `vendors` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `accounts` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `numbering_series` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `invoice_settings` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `company_settings` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `company_users` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `subscriptions` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `financial_years` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `backups` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `payment_gateway_settings` WHERE `company_id` = :id", ['id' => $id]);
                $db->execute("DELETE FROM `audit_logs` WHERE `company_id` = :id", ['id' => $id]);

                // Delete company record
                $db->execute("DELETE FROM `companies` WHERE `id` = :id", ['id' => $id]);

                AuditLog::log("Tenant [{$company['company_name']}] (ID: {$id}) permanently deleted", 'SuperAdmin', $id);
            });

            // If the deleted company was currently active in session, clear it
            if (Session::getCompanyId() === $id) {
                Session::remove('company_id');
                Session::remove('company_name');
                Session::remove('gst_enabled');
                Session::remove('currency_symbol');
                Session::remove('company_status');
                Session::remove('company_state');
                Session::remove('company_state_code');
                Session::remove('financial_year_id');
                Session::remove('financial_year_name');
            }

            $this->flash('success', "Tenant company [{$company['company_name']}] and all associated data have been permanently deleted.");
        } catch (\Throwable $e) {
            error_log("Failed to delete company {$id}: " . $e->getMessage());
            $this->flash('danger', 'Failed to delete tenant company: ' . $e->getMessage());
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
