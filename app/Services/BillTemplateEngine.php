<?php
namespace App\Services;

class BillTemplateEngine
{
    /**
     * Map template keys to their view file paths
     */
    public static function getViewPath(string $templateKey): string
    {
        return match ($templateKey) {
            'modern-blue' => 'invoices.templates.modern_blue',
            'minimal-clean' => 'invoices.templates.minimal_clean',
            'retail-pos' => 'invoices.templates.retail_pos',
            'thermal-80mm' => 'invoices.templates.thermal_80mm',
            'thermal-58mm' => 'invoices.templates.thermal_58mm',
            'executive-gold' => 'invoices.templates.executive_gold',
            'wholesale-grid' => 'invoices.templates.wholesale_grid',
            'creative-emerald' => 'invoices.templates.creative_emerald',
            default => 'invoices.templates.classic_gst'
        };
    }

    /**
     * Generate safe, realistic invoice data for previews using the company's own identity
     */
    public static function getSampleInvoiceData(?array $company = null): array
    {
        $compName = $company['company_name'] ?? 'DEMO TRADERS & ENTERPRISES';
        $state = $company['state'] ?? 'Maharashtra';
        $stateCode = $company['state_code'] ?? '27';
        $isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;

        $items = [
            [
                'item_name' => 'Premium Wireless Mechanical Keyboard',
                'barcode' => '8901234567890',
                'hsn_sac' => '8471',
                'quantity' => 2.00,
                'unit_name' => 'PCS',
                'rate' => 3500.00,
                'discount_amount' => 200.00,
                'taxable_amount' => 6800.00,
                'gst_rate' => 18.00,
                'cgst_amount' => 612.00,
                'sgst_amount' => 612.00,
                'igst_amount' => 0.00,
                'total_amount' => 8024.00
            ],
            [
                'item_name' => 'Ergonomic Optical Gaming Mouse',
                'barcode' => '8909876543211',
                'hsn_sac' => '8471',
                'quantity' => 3.00,
                'unit_name' => 'PCS',
                'rate' => 1250.00,
                'discount_amount' => 0.00,
                'taxable_amount' => 3750.00,
                'gst_rate' => 18.00,
                'cgst_amount' => 337.50,
                'sgst_amount' => 337.50,
                'igst_amount' => 0.00,
                'total_amount' => 4425.00
            ],
            [
                'item_name' => 'High-Speed Type-C Braided Cable 2M',
                'barcode' => '8905544332211',
                'hsn_sac' => '8544',
                'quantity' => 5.00,
                'unit_name' => 'PCS',
                'rate' => 480.00,
                'discount_amount' => 0.00,
                'taxable_amount' => 2400.00,
                'gst_rate' => 18.00,
                'cgst_amount' => 216.00,
                'sgst_amount' => 216.00,
                'igst_amount' => 0.00,
                'total_amount' => 2832.00
            ]
        ];

        $taxable = 12950.00;
        $cgst = 1165.50;
        $sgst = 1165.50;
        $igst = 0.00;
        $totalTax = $cgst + $sgst + $igst;
        $gross = $taxable + $totalTax; // 15281.00
        $roundOff = 0.00;
        $grandTotal = $gross + $roundOff;

        return [
            'id' => 99999,
            'invoice_number' => 'INV-SAMPLE-001',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+15 days')),
            'customer_name' => 'Apex Retail & Solutions Pvt Ltd',
            'customer_mobile' => '9820011223',
            'customer_gstin' => $isGst ? '27AABCA1234F1Z9' : '',
            'customer_state' => $state,
            'customer_state_code' => $stateCode,
            'place_of_supply' => $state . ' (' . $stateCode . ')',
            'is_interstate' => 0,
            'payment_mode' => 'UPI / Online Transfer',
            'sales_type' => $isGst ? 'tax_invoice' : 'bill_of_supply',
            'reference_no' => 'PO-2026-904',
            'gross_amount' => 13150.00,
            'item_discount_amount' => 200.00,
            'taxable_amount' => $taxable,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => $igst,
            'total_tax_amount' => $totalTax,
            'round_off' => $roundOff,
            'grand_total' => $grandTotal,
            'paid_amount' => $grandTotal,
            'balance_amount' => 0.00,
            'payment_status' => 'paid',
            'notes' => 'Thank you for your valued business. Goods delivered in good condition.',
            'items' => $items
        ];
    }
}
