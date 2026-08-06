<?php
class SkillCategory {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        return $this->db->select("SELECT sc.*, (SELECT COUNT(*) FROM skills WHERE category_id = sc.id) as skill_count FROM skill_categories sc ORDER BY sc.sort_order ASC, sc.name ASC");
    }

    public function getById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM skill_categories WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        return $this->db->insert('skill_categories', $data);
    }

    public function update(int $id, array $data): int {
        return $this->db->update('skill_categories', $data, 'id = ?', [$id]);
    }

    public function delete(int $id): int {
        $skillCount = $this->db->selectOne("SELECT COUNT(*) as cnt FROM skills WHERE category_id = ?", [$id])['cnt'] ?? 0;
        if ($skillCount > 0) {
            return -1;
        }
        return $this->db->delete('skill_categories', 'id = ?', [$id]);
    }
}
