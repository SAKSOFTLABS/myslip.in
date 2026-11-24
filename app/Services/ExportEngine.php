<?php
namespace App\Services;

/**
 * Clean CSV / Excel Exporter
 */
class ExportEngine
{
    /**
     * Export data rows to a formatted CSV / Excel file
     */
    public static function exportCsv(string $filename, array $headers, array $rows, ?array $summaryRow = null): void
    {
        // Clear any previous output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Output UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write header row
        fputcsv($output, $headers);

        // Write data rows
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        // Write optional summary row
        if ($summaryRow) {
            fputcsv($output, $summaryRow);
        }

        fclose($output);
        exit;
    }
}
