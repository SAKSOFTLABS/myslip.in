<?php
namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * Double-Entry Accounting & General Ledger Engine
 */
class AccountingEngine
{
    /**
     * Post balanced journal lines into General Ledger
     * @param array $lines Array of ['account_id' => int, 'debit' => float, 'credit' => float, 'narration' => string]
     */
    public static function postVoucher(
        int $companyId,
        int $financialYearId,
        string $voucherType,
        int $voucherId,
        string $voucherNumber,
        string $voucherDate,
        array $lines
    ): void {
        $db = Database::getInstance();
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        foreach ($lines as $line) {
            $debit = round((float)($line['debit'] ?? 0), 2);
            $credit = round((float)($line['credit'] ?? 0), 2);
            
            // Skip zero balance lines
            if ($debit == 0 && $credit == 0) {
                continue;
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        // Strict double-entry balance check (allow max 0.02 delta for floating roundoff tolerance)
        if (abs($totalDebit - $totalCredit) > 0.02) {
            throw new Exception("Double-entry accounting error: Total Debit (₹{$totalDebit}) must equal Total Credit (₹{$totalCredit}) for Voucher {$voucherNumber}");
        }

        // Insert ledger entries
        $stmt = $db->getConnection()->prepare(
            "INSERT INTO `ledger_entries` 
             (`company_id`, `financial_year_id`, `voucher_type`, `voucher_id`, `voucher_number`, `voucher_date`, `account_id`, `debit`, `credit`, `narration`, `is_cancelled`) 
             VALUES 
             (:company_id, :fy_id, :voucher_type, :voucher_id, :voucher_number, :voucher_date, :account_id, :debit, :credit, :narration, 0)"
        );

        foreach ($lines as $line) {
            $debit = round((float)($line['debit'] ?? 0), 2);
            $credit = round((float)($line['credit'] ?? 0), 2);

            if ($debit == 0 && $credit == 0) {
                continue;
            }

            $stmt->execute([
                'company_id' => $companyId,
                'fy_id' => $financialYearId,
                'voucher_type' => $voucherType,
                'voucher_id' => $voucherId,
                'voucher_number' => $voucherNumber,
                'voucher_date' => $voucherDate,
                'account_id' => (int)$line['account_id'],
                'debit' => $debit,
                'credit' => $credit,
                'narration' => $line['narration'] ?? "Posted for {$voucherNumber}"
            ]);
        }
    }

    /**
     * Cancel ledger entries for a voucher
     */
    public static function cancelVoucher(int $companyId, string $voucherType, int $voucherId): void
    {
        $db = Database::getInstance();
        $db->execute(
            "UPDATE `ledger_entries` 
             SET `is_cancelled` = 1 
             WHERE `company_id` = :company_id 
               AND `voucher_type` = :vouch_type 
               AND `voucher_id` = :vouch_id",
            [
                'company_id' => $companyId,
                'vouch_type' => $voucherType,
                'vouch_id' => $voucherId
            ]
        );
    }

    /**
     * Get or create a standard system account for a tenant
     */
    public static function getOrCreateAccount(int $companyId, string $accountName, string $category, int $groupId, bool $isCash = false, bool $isBank = false): int
    {
        $db = Database::getInstance();
        $acc = $db->fetchOne(
            "SELECT id FROM `accounts` WHERE `company_id` = :company_id AND `account_name` = :name AND `is_deleted` = 0",
            ['company_id' => $companyId, 'name' => $accountName]
        );

        if ($acc) {
            return (int)$acc['id'];
        }

        $db->execute(
            "INSERT INTO `accounts` (`company_id`, `group_id`, `account_name`, `opening_balance`, `balance_type`, `is_bank`, `is_cash`, `is_system`, `status`) 
             VALUES (:company_id, :group_id, :name, 0.00, 'DR', :is_bank, :is_cash, 1, 'active')",
            [
                'company_id' => $companyId,
                'group_id' => $groupId,
                'name' => $accountName,
                'is_bank' => $isBank ? 1 : 0,
                'is_cash' => $isCash ? 1 : 0
            ]
        );

        return $db->lastInsertId();
    }

    /**
     * Get or create customer ledger account
     */
    public static function getCustomerAccountId(int $companyId, int $customerId, string $customerName): int
    {
        $db = Database::getInstance();
        $accountName = "Customer - " . $customerName;
        
        $acc = $db->fetchOne(
            "SELECT id FROM `accounts` WHERE `company_id` = :comp_id AND `account_name` = :name AND `is_deleted` = 0",
            ['comp_id' => $companyId, 'name' => $accountName]
        );

        if ($acc) {
            return (int)$acc['id'];
        }

        // Group 3: Sundry Debtors
        return self::getOrCreateAccount($companyId, $accountName, 'assets', 3);
    }

    /**
     * Get or create vendor ledger account
     */
    public static function getVendorAccountId(int $companyId, int $vendorId, string $vendorName): int
    {
        $db = Database::getInstance();
        $accountName = "Vendor - " . $vendorName;
        
        $acc = $db->fetchOne(
            "SELECT id FROM `accounts` WHERE `company_id` = :comp_id AND `account_name` = :name AND `is_deleted` = 0",
            ['comp_id' => $companyId, 'name' => $accountName]
        );

        if ($acc) {
            return (int)$acc['id'];
        }

        // Group 7: Sundry Creditors
        return self::getOrCreateAccount($companyId, $accountName, 'liabilities', 7);
    }

    /**
     * Seed initial default chart of accounts for a new company
     */
    public static function seedDefaultAccountsForCompany(int $companyId): void
    {
        // 1. Cash (Group 1)
        self::getOrCreateAccount($companyId, 'Cash Account', 'assets', 1, true, false);

        // 2. Bank (Group 2)
        self::getOrCreateAccount($companyId, 'Primary Bank Account', 'assets', 2, false, true);

        // 3. Sales (Group 13)
        self::getOrCreateAccount($companyId, 'Sales Account', 'income', 13);
        self::getOrCreateAccount($companyId, 'Sales Return Account', 'income', 13);

        // 4. Purchase (Group 16)
        self::getOrCreateAccount($companyId, 'Purchase Account', 'expenses', 16);
        self::getOrCreateAccount($companyId, 'Purchase Return Account', 'expenses', 16);

        // 5. Duties & Taxes (Group 8)
        self::getOrCreateAccount($companyId, 'Output CGST', 'liabilities', 8);
        self::getOrCreateAccount($companyId, 'Output SGST', 'liabilities', 8);
        self::getOrCreateAccount($companyId, 'Output IGST', 'liabilities', 8);
        self::getOrCreateAccount($companyId, 'Input CGST', 'liabilities', 8);
        self::getOrCreateAccount($companyId, 'Input SGST', 'liabilities', 8);
        self::getOrCreateAccount($companyId, 'Input IGST', 'liabilities', 8);

        // 6. Round Off & Discounts
        self::getOrCreateAccount($companyId, 'Round Off Account', 'expenses', 18);
        self::getOrCreateAccount($companyId, 'Discount Allowed', 'expenses', 18);
        self::getOrCreateAccount($companyId, 'Discount Received', 'income', 15);

        // 7. Capital (Group 11)
        self::getOrCreateAccount($companyId, 'Owner Capital Account', 'capital', 11);
    }
}
