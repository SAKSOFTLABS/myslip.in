<?php
namespace App\Services;

use App\Core\Session;

/**
 * GST Calculation & Tax Determination Engine
 */
class GstEngine
{
    /**
     * Check if a transaction is Interstate (IGST) or Intrastate (CGST + SGST)
     */
    public static function isInterstate(string $companyStateCode, string $partyStateCode): bool
    {
        $companyStateCode = trim($companyStateCode);
        $partyStateCode = trim($partyStateCode);
        
        if (empty($companyStateCode) || empty($partyStateCode)) {
            return false;
        }

        return $companyStateCode !== $partyStateCode;
    }

    /**
     * Calculate tax breakdown from Taxable Amount and GST Rate
     */
    public static function calculateTax(float $taxableAmount, float $gstRate, bool $isInterstate, bool $gstEnabled = true): array
    {
        if (!$gstEnabled || $gstRate <= 0) {
            return [
                'taxable_amount' => round($taxableAmount, 2),
                'gst_rate' => 0.00,
                'cgst_rate' => 0.00,
                'cgst_amount' => 0.00,
                'sgst_rate' => 0.00,
                'sgst_amount' => 0.00,
                'igst_rate' => 0.00,
                'igst_amount' => 0.00,
                'total_tax' => 0.00,
                'total_amount' => round($taxableAmount, 2)
            ];
        }

        $taxableAmount = round($taxableAmount, 2);
        $totalTax = round(($taxableAmount * $gstRate) / 100, 2);

        if ($isInterstate) {
            return [
                'taxable_amount' => $taxableAmount,
                'gst_rate' => $gstRate,
                'cgst_rate' => 0.00,
                'cgst_amount' => 0.00,
                'sgst_rate' => 0.00,
                'sgst_amount' => 0.00,
                'igst_rate' => $gstRate,
                'igst_amount' => $totalTax,
                'total_tax' => $totalTax,
                'total_amount' => round($taxableAmount + $totalTax, 2)
            ];
        }

        $halfRate = round($gstRate / 2, 2);
        $halfTax = round($totalTax / 2, 2);

        return [
            'taxable_amount' => $taxableAmount,
            'gst_rate' => $gstRate,
            'cgst_rate' => $halfRate,
            'cgst_amount' => $halfTax,
            'sgst_rate' => $halfRate,
            'sgst_amount' => $halfTax,
            'igst_rate' => 0.00,
            'igst_amount' => 0.00,
            'total_tax' => round($halfTax * 2, 2),
            'total_amount' => round($taxableAmount + ($halfTax * 2), 2)
        ];
    }

    /**
     * Compute line item taxable amount after discounts
     */
    public static function computeLineItem(float $quantity, float $rate, float $discountPercent, float $discountAmount, float $gstRate, bool $isInterstate, bool $gstEnabled = true): array
    {
        $gross = round($quantity * $rate, 2);
        
        if ($discountPercent > 0) {
            $discountAmount = round(($gross * $discountPercent) / 100, 2);
        }

        $taxableAmount = max(0, round($gross - $discountAmount, 2));
        $tax = self::calculateTax($taxableAmount, $gstRate, $isInterstate, $gstEnabled);

        return array_merge([
            'gross_amount' => $gross,
            'discount_amount' => $discountAmount,
            'quantity' => $quantity,
            'rate' => $rate
        ], $tax);
    }
}
