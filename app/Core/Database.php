<?php
namespace App\Core;

use PDO;
use PDOException;
use Exception;

/**
 * Singleton Database Manager wrapping PDO
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require __DIR__ . '/../Config/database.php';
        $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        
        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("<div style='font-family:sans-serif; max-width:650px; margin:40px auto; padding:25px; background:#fff1f2; border:1px solid #fecdd3; border-radius:10px; color:#9f1239; line-height:1.6;'>"
                . "<h2 style='margin-top:0; color:#881337;'>⚠️ Database Connection Failed</h2>"
                . "<p><strong>Error Details:</strong> <code>" . htmlspecialchars($e->getMessage()) . "</code></p>"
                . "<p><strong>Connecting to Host:</strong> <code>" . htmlspecialchars($config['host']) . "</code></p>"
                . "<p><strong>Target Database:</strong> <code>" . htmlspecialchars($config['database']) . "</code></p>"
                . "<p><strong>Username:</strong> <code>" . htmlspecialchars($config['username']) . "</code></p>"
                . "<hr style='border:0; border-top:1px solid #fecdd3; margin:20px 0;'>"
                . "<p style='color:#4c0519; font-size:14px; margin-bottom:0;'>💡 <strong>How to fix:</strong> Open your <code>.env</code> file in cPanel File Manager and verify that <code>DB_DATABASE</code>, <code>DB_USERNAME</code>, and <code>DB_PASSWORD</code> match your cPanel MySQL settings.</p>"
                . "</div>");
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a SELECT query and fetch all rows
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a SELECT query and fetch single row
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Execute a SELECT query and fetch a single column scalar value
     */
    public function fetchValue(string $sql, array $params = []): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get last inserted auto-increment ID
     */
    public function lastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Begin a database transaction
     */
    public function beginTransaction(): bool
    {
        if (!$this->pdo->inTransaction()) {
            return $this->pdo->beginTransaction();
        }
        return true;
    }

    /**
     * Commit a database transaction
     */
    public function commit(): bool
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->commit();
        }
        return true;
    }

    /**
     * Rollback a database transaction
     */
    public function rollBack(): bool
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->rollBack();
        }
        return true;
    }

    /**
     * Wrap execution inside an atomic transaction
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
}
