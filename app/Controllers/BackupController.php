<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\BackupEngine;
use App\Models\AuditLog;
use Exception;

class BackupController extends Controller
{
    public function index(): void
    {
        $this->view('settings.backup', [
            'pageTitle' => 'Database Backup & Restore'
        ]);
    }

    public function download(): void
    {
        $companyId = Session::getCompanyId();
        $userId = Session::getUserId();
        $fileName = BackupEngine::createBackup($companyId, $userId);
        $filePath = __DIR__ . '/../../storage/backups/' . $fileName;

        AuditLog::log('Backup Downloaded', 'Backup', $companyId);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function restore(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();

        if (empty($_FILES['backup_file']['tmp_name'])) {
            $this->flash('danger', 'Please upload a valid JSON backup file.');
            $this->redirect('/settings/backup');
        }

        $jsonContent = file_get_contents($_FILES['backup_file']['tmp_name']);
        if (!$jsonContent) {
            $this->flash('danger', 'Failed to read uploaded backup file.');
            $this->redirect('/settings/backup');
        }

        try {
            $success = BackupEngine::restoreBackup($companyId, $jsonContent);
            if ($success) {
                AuditLog::log('Backup Restored', 'Backup', $companyId);
                $this->flash('success', 'Company database restored successfully from backup.');
            } else {
                $this->flash('danger', 'Failed to restore backup archive.');
            }
        } catch (Exception $e) {
            $this->flash('danger', 'Restore error: ' . $e->getMessage());
        }

        $this->redirect('/settings/backup');
    }
}
