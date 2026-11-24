<?php
namespace App\Services;

use App\Core\Database;

/**
 * Subscription, Trial & License Management Engine
 */
class SubscriptionEngine
{
    /**
     * Start a 30-day trial for a newly created company
     */
    public static function startTrial(int $companyId, int $days = 30): int
    {
        $db = Database::getInstance();
        $startDate = date('Y-m-d');
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));

        // Fetch Trial Plan (Plan ID 1)
        $plan = $db->fetchOne("SELECT * FROM `plans` WHERE `slug` = 'trial' OR `id` = 1 LIMIT 1");
        $planId = $plan ? (int)$plan['id'] : 1;

        $db->execute(
            "INSERT INTO `subscriptions` 
             (`company_id`, `plan_id`, `status`, `start_date`, `expiry_date`, `trial_start_date`, `trial_expiry_date`, `amount_paid`, `notes`) 
             VALUES 
             (:company_id, :plan_id, 'trial', :start_date, :expiry_date, :trial_start, :trial_expiry, 0.00, '30-Day Free Trial Activated')",
            [
                'company_id' => $companyId,
                'plan_id' => $planId,
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'trial_start' => $startDate,
                'trial_expiry' => $expiryDate
            ]
        );

        return $db->lastInsertId();
    }

    /**
     * Get current subscription info for a company
     */
    public static function getCompanySubscription(int $companyId): ?array
    {
        $db = Database::getInstance();
        $sql = "SELECT s.*, p.name as plan_name, p.slug as plan_slug, p.features_json, p.max_users, p.max_items 
                FROM `subscriptions` s
                JOIN `plans` p ON s.plan_id = p.id
                WHERE s.company_id = :comp_id 
                ORDER BY s.id DESC LIMIT 1";
        
        return $db->fetchOne($sql, ['comp_id' => $companyId]);
    }

    /**
     * Calculate remaining days in subscription/trial
     */
    public static function getDaysRemaining(?array $sub): int
    {
        if (!$sub) {
            return 0;
        }

        $expiryDate = $sub['expiry_date'];
        if ($sub['status'] === 'trial' && !empty($sub['trial_expiry_date'])) {
            $expiryDate = $sub['trial_expiry_date'];
        }

        $diff = strtotime($expiryDate) - strtotime(date('Y-m-d'));
        return (int)ceil($diff / 86400);
    }
}
