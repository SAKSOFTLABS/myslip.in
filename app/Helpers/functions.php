<?php
/**
 * MYSLIP.IN — Global Helper Functions
 */

use App\Core\Security;
use App\Core\Session;

/**
 * Get an environment variable with robust fallbacks
 */
function env(string $key, mixed $default = null): mixed
{
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        $val = $_ENV[$key];
    } elseif (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        $val = $_SERVER[$key];
    } else {
        $val = function_exists('getenv') ? getenv($key) : false;
        if ($val === false || $val === '') {
            return $default;
        }
    }

    $lower = is_string($val) ? strtolower($val) : '';
    if ($lower === 'true' || $lower === '(true)') {
        return true;
    }
    if ($lower === 'false' || $lower === '(false)') {
        return false;
    }
    if ($lower === 'empty' || $lower === '(empty)' || $lower === 'null') {
        return '';
    }

    return $val;
}

/**
 * Sanitize string for HTML display
 */
function e(?string $str): string
{
    return Security::e($str);
}

/**
 * Format currency in Indian numbering system (e.g. 1,23,456.78)
 */
function formatCurrency(float|int|string|null $amount, bool $withSymbol = true): string
{
    $amount = (float)($amount ?? 0);
    $isNegative = $amount < 0;
    $amount = abs($amount);

    $decimalPart = sprintf("%01.2f", $amount);
    $parts = explode('.', $decimalPart);
    $intPart = $parts[0];
    $decPart = $parts[1];

    if (strlen($intPart) > 3) {
        $lastThree = substr($intPart, -3);
        $remaining = substr($intPart, 0, -3);
        $remaining = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $remaining);
        $formatted = $remaining . ',' . $lastThree;
    } else {
        $formatted = $intPart;
    }

    $formatted = $formatted . '.' . $decPart;
    if ($isNegative) {
        $formatted = '-' . $formatted;
    }

    $curr = Session::get('currency_symbol');
    if (empty($curr) || str_contains($curr, 'Ô') || str_contains($curr, 'â') || str_contains($curr, 'Ã')) {
        $curr = '₹';
    }
    $symbol = $withSymbol ? ($curr . ' ') : '';
    return $symbol . $formatted;
}

/**
 * Format quantity with custom decimal places
 */
function formatQty(float|int|string|null $qty, int $decimals = 2): string
{
    $qty = (float)($qty ?? 0);
    // Remove trailing zero decimals if integer
    if (floor($qty) == $qty) {
        return (string)(int)$qty;
    }
    return number_format($qty, $decimals);
}

/**
 * Format date in Indian standard format (d-m-Y)
 */
function formatDate(?string $date, string $format = 'd-m-Y'): string
{
    if (!$date || $date === '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Convert numbers to Indian Rupees in words
 */
function numberToWords(float $number): string
{
    $no = floor($number);
    $point = round($number - $no, 2) * 100;
    $hundred = null;
    $digits_1 = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen',
        15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
        18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
        60 => 'Sixty', 70 => 'Seventy',
        80 => 'Eighty', 90 => 'Ninety'
    );
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number] .
                " " . $digits[$counter] . $plural . " " . $hundred
                :
                $words[floor($number / 10) * 10]
                . " " . $words[$number % 10] . " "
                . $digits[$counter] . $plural . " " . $hundred;
        } else $str[] = null;
    }
    $str = array_reverse($str);
    $result = implode('', $str);
    $points = ($point) ? " and " . $words[floor($point / 10) * 10] . " " . $words[$point = $point % 10] . " Paise" : '';
    
    $out = trim($result) ? trim($result) . " Rupees" : "Zero Rupees";
    if ($points) {
        $out .= $points;
    }
    return $out . " Only";
}

/**
 * Generate application URL
 */
function url(string $path = ''): string
{
    $config = require __DIR__ . '/../Config/app.php';
    $base = rtrim($config['url'], '/');
    $path = '/' . ltrim($path, '/');
    return $path === '/' ? $base : $base . $path;
}

/**
 * Generate asset URL
 */
function asset(string $path): string
{
    $config = require __DIR__ . '/../Config/app.php';
    $base = rtrim($config['url'], '/');
    return $base . '/assets/' . ltrim($path, '/');
}

/**
 * Generate CSRF token input
 */
function csrf_field(): string
{
    return Security::csrfField();
}

/**
 * Check if a permission is granted for the currently logged in user
 */
function can(string $module, string $action): bool
{
    if (Session::isSuperAdmin()) {
        return true;
    }

    $roleId = Session::get('role_id');
    if (!$roleId) {
        return false;
    }

    // Role 1 (Company Admin) has all permissions
    if ($roleId === 1) {
        return true;
    }

    $db = \App\Core\Database::getInstance();
    $sql = "SELECT COUNT(*) FROM `role_permissions` rp
            JOIN `permissions` p ON rp.permission_id = p.id
            WHERE rp.role_id = :role_id AND p.module = :module AND p.action = :action";
    
    $cnt = (int)$db->fetchValue($sql, [
        'role_id' => $roleId,
        'module' => $module,
        'action' => $action
    ]);

    return $cnt > 0;
}
