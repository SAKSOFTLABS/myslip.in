<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Session;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected bool $tenantScoped = false;

    public static function log(string $action, string $module, ?int $recordId = null, ?array $details = null): void
    {
        $db = \App\Core\Database::getInstance();
        $db->execute(
            "INSERT INTO `audit_logs` (`company_id`, `user_id`, `action`, `module`, `record_id`, `ip_address`, `user_agent`, `details_json`) 
             VALUES (:comp_id, :user_id, :action, :module, :rec_id, :ip, :ua, :details)",
            [
                'comp_id' => Session::getCompanyId(),
                'user_id' => Session::getUserId(),
                'action' => $action,
                'module' => $module,
                'rec_id' => $recordId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'details' => $details ? json_encode($details) : null
            ]
        );
    }
}
