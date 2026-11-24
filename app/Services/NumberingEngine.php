<?php
namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * Voucher & Invoice Numbering Sequence Engine
 */
class NumberingEngine
{
    private static array $defaultPrefixes = [
        'sale' => 'INV',
        'purchase' => 'PUR',
        'sales_return' => 'SR',
        'purchase_return' => 'PR',
        'receipt' => 'REC',
        'payment' => 'PAY',
        'journal' => 'JRN'
    ];

    /**
     * Generate next voucher number and increment sequence
     */
    public static function getNextNumber(int $companyId, int $financialYearId, string $voucherType): string
    {
        $db = Database::getInstance();

        // Lock row for update
        $sql = "SELECT * FROM `numbering_series` 
                WHERE `company_id` = :company_id 
                  AND `financial_year_id` = :fy_id 
                  AND `voucher_type` = :vouch_type 
                FOR UPDATE";
        
        $series = $db->fetchOne($sql, [
            'company_id' => $companyId,
            'fy_id' => $financialYearId,
            'vouch_type' => $voucherType
        ]);

        $prefix = self::$defaultPrefixes[$voucherType] ?? strtoupper($voucherType);
        $nextNum = 1;
        $zeroPad = 5;
        $suffix = '';

        if ($series) {
            $prefix = $series['prefix'];
            $nextNum = (int)$series['next_number'];
            $zeroPad = (int)$series['zero_pad'];
            $suffix = $series['suffix'] ?? '';

            // Increment
            $db->execute("UPDATE `numbering_series` SET `next_number` = `next_number` + 1 WHERE `id` = :id", [
                'id' => $series['id']
            ]);
        } else {
            // Get custom company invoice prefix if sales
            if ($voucherType === 'sale') {
                $comp = $db->fetchOne("SELECT invoice_prefix FROM `companies` WHERE id = :id", ['id' => $companyId]);
                if (!empty($comp['invoice_prefix'])) {
                    $prefix = $comp['invoice_prefix'];
                }
            }

            // Create series record
            $db->execute("INSERT INTO `numbering_series` (`company_id`, `financial_year_id`, `voucher_type`, `prefix`, `next_number`, `zero_pad`) 
                          VALUES (:company_id, :fy_id, :vouch_type, :prefix, 2, :zero_pad)", [
                'company_id' => $companyId,
                'fy_id' => $financialYearId,
                'vouch_type' => $voucherType,
                'prefix' => $prefix,
                'zero_pad' => $zeroPad
            ]);
        }

        $padded = str_pad((string)$nextNum, $zeroPad, '0', STR_PAD_LEFT);
        return $prefix . '-' . $padded . ($suffix ? '-' . $suffix : '');
    }

    /**
     * Preview next number without incrementing
     */
    public static function previewNextNumber(int $companyId, int $financialYearId, string $voucherType): string
    {
        $db = Database::getInstance();
        $series = $db->fetchOne("SELECT * FROM `numbering_series` 
                                WHERE `company_id` = :company_id 
                                  AND `financial_year_id` = :fy_id 
                                  AND `voucher_type` = :vouch_type", [
            'company_id' => $companyId,
            'fy_id' => $financialYearId,
            'vouch_type' => $voucherType
        ]);

        $prefix = self::$defaultPrefixes[$voucherType] ?? strtoupper($voucherType);
        $nextNum = 1;
        $zeroPad = 5;
        $suffix = '';

        if ($series) {
            $prefix = $series['prefix'];
            $nextNum = (int)$series['next_number'];
            $zeroPad = (int)$series['zero_pad'];
            $suffix = $series['suffix'] ?? '';
        } else if ($voucherType === 'sale') {
            $comp = $db->fetchOne("SELECT invoice_prefix FROM `companies` WHERE id = :id", ['id' => $companyId]);
            if (!empty($comp['invoice_prefix'])) {
                $prefix = $comp['invoice_prefix'];
            }
        }

        $padded = str_pad((string)$nextNum, $zeroPad, '0', STR_PAD_LEFT);
        return $prefix . '-' . $padded . ($suffix ? '-' . $suffix : '');
    }
}
