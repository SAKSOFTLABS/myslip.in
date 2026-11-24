<?php
namespace App\Core;

/**
 * Base Model with Multi-Tenant Scoping and CRUD Helpers
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected bool $tenantScoped = true; // Automatically enforce company_id
    protected bool $useSoftDelete = false;

    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get current company ID from authenticated session
     */
    protected function getCompanyId(): ?int
    {
        return Session::getCompanyId();
    }

    /**
     * Find single record by primary key (strictly tenant isolated)
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $params = ['id' => $id];

        if ($this->tenantScoped) {
            $companyId = $this->getCompanyId();
            if ($companyId === null) {
                return null;
            }
            $sql .= " AND `company_id` = :company_id";
            $params['company_id'] = $companyId;
        }

        if ($this->useSoftDelete) {
            $sql .= " AND `is_deleted` = 0";
        }

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Query all records matching conditions
     */
    public function all(array $conditions = [], string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE 1=1";
        $params = [];

        if ($this->tenantScoped) {
            $companyId = $this->getCompanyId();
            if ($companyId !== null) {
                $sql .= " AND `company_id` = :company_id";
                $params['company_id'] = $companyId;
            }
        }

        if ($this->useSoftDelete) {
            $sql .= " AND `is_deleted` = 0";
        }

        foreach ($conditions as $field => $val) {
            $paramKey = 'p_' . preg_replace('/[^a-zA-Z0-9_]/', '', $field);
            $sql .= " AND `{$field}` = :{$paramKey}";
            $params[$paramKey] = $val;
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Count records matching conditions
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE 1=1";
        $params = [];

        if ($this->tenantScoped) {
            $companyId = $this->getCompanyId();
            if ($companyId !== null) {
                $sql .= " AND `company_id` = :company_id";
                $params['company_id'] = $companyId;
            }
        }

        if ($this->useSoftDelete) {
            $sql .= " AND `is_deleted` = 0";
        }

        foreach ($conditions as $field => $val) {
            $paramKey = 'cnt_' . preg_replace('/[^a-zA-Z0-9_]/', '', $field);
            $sql .= " AND `{$field}` = :{$paramKey}";
            $params[$paramKey] = $val;
        }

        return (int)$this->db->fetchValue($sql, $params);
    }

    /**
     * Insert a new record
     */
    public function create(array $data): int
    {
        if ($this->tenantScoped && !isset($data['company_id'])) {
            $companyId = $this->getCompanyId();
            if ($companyId !== null) {
                $data['company_id'] = $companyId;
            }
        }

        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":{$f}", $fields);

        $sql = "INSERT INTO `{$this->table}` (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        $this->db->execute($sql, $data);

        return $this->db->lastInsertId();
    }

    /**
     * Update an existing record
     */
    public function update(int $id, array $data): bool
    {
        $setClauses = [];
        $params = ['pk_id' => $id];

        foreach ($data as $field => $val) {
            $setClauses[] = "`{$field}` = :set_{$field}";
            $params["set_{$field}"] = $val;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setClauses) . " WHERE `{$this->primaryKey}` = :pk_id";

        if ($this->tenantScoped) {
            $companyId = $this->getCompanyId();
            if ($companyId !== null) {
                $sql .= " AND `company_id` = :company_id";
                $params['company_id'] = $companyId;
            }
        }

        return $this->db->execute($sql, $params);
    }

    /**
     * Delete or soft-delete a record
     */
    public function delete(int $id): bool
    {
        $params = ['pk_id' => $id];
        
        if ($this->useSoftDelete) {
            $sql = "UPDATE `{$this->table}` SET `is_deleted` = 1 WHERE `{$this->primaryKey}` = :pk_id";
        } else {
            $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :pk_id";
        }

        if ($this->tenantScoped) {
            $companyId = $this->getCompanyId();
            if ($companyId !== null) {
                $sql .= " AND `company_id` = :company_id";
                $params['company_id'] = $companyId;
            }
        }

        return $this->db->execute($sql, $params);
    }

    /**
     * Paginated result set
     */
    public function paginate(int $page = 1, int $perPage = 15, array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        $total = $this->count($conditions);
        $totalPages = (int)ceil($total / $perPage);

        $sql = "SELECT * FROM `{$this->table}` WHERE 1=1";
        $params = [];

        if ($this->tenantScoped) {
            $companyId = $this->getCompanyId();
            if ($companyId !== null) {
                $sql .= " AND `company_id` = :company_id";
                $params['company_id'] = $companyId;
            }
        }

        if ($this->useSoftDelete) {
            $sql .= " AND `is_deleted` = 0";
        }

        foreach ($conditions as $field => $val) {
            $paramKey = 'p_' . preg_replace('/[^a-zA-Z0-9_]/', '', $field);
            $sql .= " AND `{$field}` = :{$paramKey}";
            $params[$paramKey] = $val;
        }

        $sql .= " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->db->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ];
    }
}
