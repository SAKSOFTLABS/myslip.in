<?php
namespace App\Models;

use App\Core\Model;

class BillTemplate extends Model
{
    protected string $table = 'bill_templates';
    protected bool $tenantScoped = false; // Global templates managed by Super Admin

    /**
     * Auto-ensure bill design tables exist on live server
     */
    public static function ensureTablesExist(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            $db = \App\Core\Database::getInstance();
            $tableExists = $db->fetchOne("SHOW TABLES LIKE 'bill_templates'");
            if (!$tableExists) {
                $migrationFile = dirname(__DIR__, 2) . '/database/migrations/2026_11_27_create_bill_design_module_tables.sql';
                if (file_exists($migrationFile)) {
                    $sql = file_get_contents($migrationFile);
                    $db->getConnection()->exec($sql);
                }
            }
        } catch (\Throwable $e) {
            error_log("Error ensuring bill_templates table: " . $e->getMessage());
        }
    }

    public function __construct()
    {
        parent::__construct();
        self::ensureTablesExist();
    }

    /**
     * Fetch published templates for user gallery with filters and sorting
     */
    public function getPublished(array $filters = [], string $sort = 'display_order ASC'): array
    {
        $sql = "SELECT bt.* FROM `bill_templates` bt WHERE bt.`status` = 'published'";
        $params = [];

        if (!empty($filters['pricing_type']) && in_array($filters['pricing_type'], ['free', 'paid'])) {
            $sql .= " AND bt.`pricing_type` = :pricing_type";
            $params['pricing_type'] = $filters['pricing_type'];
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $sql .= " AND bt.`category` = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['paper_size']) && $filters['paper_size'] !== 'all') {
            $sql .= " AND bt.`paper_size` = :paper_size";
            $params['paper_size'] = $filters['paper_size'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (bt.`name` LIKE :search OR bt.`description` LIKE :search OR bt.`category` LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        $allowedSorts = [
            'display_order ASC' => 'bt.`display_order` ASC, bt.`id` ASC',
            'display_order DESC' => 'bt.`display_order` DESC, bt.`id` DESC',
            'name ASC' => 'bt.`name` ASC',
            'name DESC' => 'bt.`name` DESC',
            'price ASC' => 'bt.`price` ASC',
            'price DESC' => 'bt.`price` DESC',
            'newest' => 'bt.`id` DESC',
            'featured' => 'bt.`is_featured` DESC, bt.`display_order` ASC'
        ];

        $orderClause = $allowedSorts[$sort] ?? 'bt.`display_order` ASC, bt.`id` ASC';
        $sql .= " ORDER BY " . $orderClause;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Find a template by unique template key
     */
    public function findByKey(string $key): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `bill_templates` WHERE `template_key` = :k LIMIT 1", ['k' => $key]);
    }

    /**
     * Duplicate an existing template
     */
    public function duplicate(int $id, int $userId): ?int
    {
        $original = $this->find($id);
        if (!$original) {
            return null;
        }

        $baseKey = $original['template_key'] . '-copy';
        $uniqueKey = $baseKey;
        $counter = 1;
        while ($this->findByKey($uniqueKey)) {
            $uniqueKey = $baseKey . '-' . $counter;
            $counter++;
        }

        $name = $original['name'] . ' (Copy)';
        $newId = $this->create([
            'name' => $name,
            'template_key' => $uniqueKey,
            'description' => $original['description'],
            'category' => $original['category'],
            'paper_size' => $original['paper_size'],
            'preview_image' => $original['preview_image'],
            'template_config' => $original['template_config'],
            'version' => $original['version'],
            'pricing_type' => $original['pricing_type'],
            'price' => $original['price'],
            'currency' => $original['currency'],
            'is_featured' => 0,
            'display_order' => (int)$original['display_order'] + 1,
            'status' => 'draft',
            'created_by' => $userId
        ]);

        return $newId;
    }
}
