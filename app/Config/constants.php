<?php
/**
 * MYSLIP.IN — System Constants & Master Data Definitions
 */

// User Roles
define('ROLE_SUPER_ADMIN', 0);
define('ROLE_COMPANY_ADMIN', 1);
define('ROLE_ACCOUNTANT', 2);
define('ROLE_BILLING_STAFF', 3);
define('ROLE_VIEWER', 4);

// Voucher Types
define('VOUCHER_SALE', 'sale');
define('VOUCHER_PURCHASE', 'purchase');
define('VOUCHER_SALES_RETURN', 'sales_return');
define('VOUCHER_PURCHASE_RETURN', 'purchase_return');
define('VOUCHER_RECEIPT', 'receipt');
define('VOUCHER_PAYMENT', 'payment');
define('VOUCHER_JOURNAL', 'journal');
define('VOUCHER_OPENING', 'opening');

// Standard Indian State Codes for GST
const INDIAN_STATES = [
    '01' => 'Jammu and Kashmir',
    '02' => 'Himachal Pradesh',
    '03' => 'Punjab',
    '04' => 'Chandigarh',
    '05' => 'Uttarakhand',
    '06' => 'Haryana',
    '07' => 'Delhi',
    '08' => 'Rajasthan',
    '09' => 'Uttar Pradesh',
    '10' => 'Bihar',
    '11' => 'Sikkim',
    '12' => 'Arunachal Pradesh',
    '13' => 'Nagaland',
    '14' => 'Manipur',
    '15' => 'Mizoram',
    '16' => 'Tripura',
    '17' => 'Meghalaya',
    '18' => 'Assam',
    '19' => 'West Bengal',
    '20' => 'Jharkhand',
    '21' => 'Odisha',
    '22' => 'Chhattisgarh',
    '23' => 'Madhya Pradesh',
    '24' => 'Gujarat',
    '26' => 'Dadra and Nagar Haveli and Daman and Diu',
    '27' => 'Maharashtra',
    '28' => 'Andhra Pradesh',
    '29' => 'Karnataka',
    '30' => 'Goa',
    '31' => 'Lakshadweep',
    '32' => 'Kerala',
    '33' => 'Tamil Nadu',
    '34' => 'Puducherry',
    '35' => 'Andaman and Nicobar Islands',
    '36' => 'Telangana',
    '37' => 'Andhra Pradesh (New)',
    '38' => 'Ladakh',
    '97' => 'Other Territory'
];

// GST Standard Rates in India
const GST_RATES = [
    0.00 => '0% (Exempted)',
    0.10 => '0.10%',
    0.25 => '0.25%',
    3.00 => '3% (Precious Metals)',
    5.00 => '5%',
    12.00 => '12%',
    18.00 => '18% (Standard)',
    28.00 => '28% (Luxury/Sin Goods)'
];

// Business Types
const BUSINESS_TYPES = [
    'retail' => 'Retail Shop / Store',
    'wholesale' => 'Wholesale / Distribution',
    'service' => 'Service Provider / Freelancer',
    'trading' => 'Trading & Commerce',
    'manufacturing' => 'Manufacturing & Production',
    'other' => 'Other Business'
];

// Payment Modes
const PAYMENT_MODES = [
    'cash' => 'Cash',
    'bank' => 'Bank Transfer (NEFT/RTGS/IMPS)',
    'upi' => 'UPI (GPay/PhonePe/Paytm)',
    'cheque' => 'Cheque',
    'credit' => 'Credit (Pending / Unpaid)',
    'other' => 'Other Payment Method'
];
