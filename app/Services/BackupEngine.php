<?php
namespace App\Services;

use App\Core\Database;
use ZipArchive;
use Exception;

/**
 * Company-Scoped Backup & Restore Engine
 */
class BackupEngine
{
    private static array $tenantTables = [
        'companies',
        'financial_years',
        'invoice_settings',
        'company_settings',
        'item_groups',
        'units',
        'items',
        'customers',
        'vendors',
        'accounts',
        'sales',
        'sales_items',
        'purchases',
        'purchase_items',
        'sales_returns',
        'sales_return_items',
        'purchase_returns',
        'purchase_return_items',
        'receipts',
        'receipt_allocations',
        'payments',
        'payment_allocations',
        'journals',
        'journal_entries',
        'ledger_entries',
        'stock_movements',
        'numbering_series'
    ];

    /**
     * Create a JSON-safe single tenant backup archive
     */
    public static function createBackup(int $companyId, int $userId): string
    {
        $db = Database::getInstance();
        $backupData = [
            'version' => '1.0.0',
            'created_at' => date('Y-m-d H:i:s'),
            'company_id' => $companyId,
            'tables' => []
        ];

        foreach (self::$tenantTables as $table) {
            $sql = ($table === 'companies')
                ? "SELECT * FROM `{$table}` WHERE `id` = :comp_id"
                : "SELECT * FROM `{$table}` WHERE `company_id` = :comp_id";
            
            $rows = $db->fetchAll($sql, ['comp_id' => $companyId]);
            $backupData['tables'][$table] = $rows;
        }

        $jsonContent = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $storageDir = __DIR__ . '/../../storage/backups';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true);
        }

        $fileName = 'backup_comp_' . $companyId . '_' . date('Ymd_His') . '.json';
        $filePath = $storageDir . '/' . $fileName;
        file_put_contents($filePath, $jsonContent);

        // Record in backups table
        $fileSize = filesize($filePath);
        $db->execute(
            "INSERT INTO `backups` (`company_id`, `user_id`, `file_name`, `file_size`, `version`) 
             VALUES (:comp_id, :user_id, :fname, :fsize, '1.0.0')",
            [
                'comp_id' => $companyId,
                'user_id' => $userId,
                'fname' => $fileName,
                'fsize' => $fileSize
            ]
        );

        return $fileName;
    }

    /**
     * Restore company data from JSON backup file
     */
    public static function restoreBackup(int $companyId, string $jsonContent): bool
    {
        $data = json_decode($jsonContent, true);
        if (!$data || empty($data['tables'])) {
            throw new Exception("Invalid backup archive format.");
        }

        $db = Database::getInstance();

        return $db->transaction(function(Database $db) use ($companyId, $data) {
            // Delete existing tenant data in reverse order (preserving company header)
            $tablesToClean = array_reverse(self::$tenantTables);
            foreach ($tablesToClean as $tbl) {
                if ($tbl === 'companies') continue;
                $db->execute("DELETE FROM `{$tbl}` WHERE `company_id` = :comp_id", ['comp_id' => $companyId]);
            }

            // Re-insert tables
            foreach (self::$tenantTables as $tbl) {
                if ($tbl === 'companies' || empty($data['tables'][$tbl])) {
                    continue;
                }

                $rows = $data['tables'][$tbl];
                foreach ($rows as $row) {
                    $row['company_id'] = $companyId; // Strictly force current tenant ID
                    
                    $fields = array_keys($row);
                    $placeholders = array_map(fn($f) => ":{$f}", $fields);
                    
                    $sql = "INSERT INTO `{$tbl}` (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
                    $db->execute($sql, $row);
                }
            }

            return true;
        });
    }
}
