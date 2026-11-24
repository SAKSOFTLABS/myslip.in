<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Subscription;
use App\Models\Plan;
use App\Services\SubscriptionEngine;
use App\Models\AuditLog;

class SubscriptionController extends Controller
{
    private Subscription $subscriptionModel;
    private Plan $planModel;

    public function __construct()
    {
        parent::__construct();
        $this->subscriptionModel = new Subscription();
        $this->planModel = new Plan();
    }

    public function index(): void
    {
        $companyId = Session::getCompanyId();
        $sub = SubscriptionEngine::getCompanySubscription($companyId);
        $plans = $this->planModel->all(['status' => 'active'], 'price ASC');

        $daysRemaining = SubscriptionEngine::getDaysRemaining($sub);

        $this->view('subscription.index', [
            'pageTitle' => 'Subscription & SaaS Plans',
            'subscription' => $sub,
            'plans' => $plans,
            'daysRemaining' => $daysRemaining
        ]);
    }

    public function upgrade(int $planId): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $plan = $this->planModel->find($planId);

        if (!$plan) {
            $this->flash('danger', 'Selected plan does not exist.');
            $this->redirect('/subscription');
        }

        $billingCycle = $this->request->input('billing_cycle', 'yearly');
        $durationDays = ($billingCycle === 'yearly') ? 365 : 30;
        $amount = ($billingCycle === 'yearly') ? (float)$plan['price_yearly'] : (float)$plan['price_monthly'];

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$durationDays} days"));

        $db = Database::getInstance();
        $db->transaction(function(Database $db) use ($companyId, $planId, $plan, $billingCycle, $amount, $startDate, $endDate) {
            // Expire old subscription
            $db->execute("UPDATE `subscriptions` SET `status` = 'expired' WHERE `company_id` = :comp_id AND `status` = 'active'", ['comp_id' => $companyId]);

            // Create new active subscription
            $db->execute(
                "INSERT INTO `subscriptions` (`company_id`, `plan_id`, `status`, `start_date`, `expiry_date`, `payment_method`, `amount_paid`, `notes`) 
                 VALUES (:comp_id, :pid, 'active', :st, :en, 'upi_direct', :amt, :notes)",
                [
                    'comp_id' => $companyId,
                    'pid' => $planId,
                    'st' => $startDate,
                    'en' => $endDate,
                    'amt' => $amount,
                    'notes' => 'Upgraded to ' . $plan['name']
                ]
            );

            // Update company plan_type
            $db->execute("UPDATE `companies` SET `plan_type` = :pname, `subscription_end` = :en WHERE `id` = :id", [
                'pname' => strtolower($plan['name']),
                'en' => $endDate,
                'id' => $companyId
            ]);
        });

        AuditLog::log('Plan Upgraded', 'Subscriptions', $planId, ['plan' => $plan['name'], 'amount' => $amount]);
        $this->flash('success', "Upgraded to [{$plan['name']}] successfully. Valid until " . formatDate($endDate) . ".");
        $this->redirect('/subscription');
    }
}
