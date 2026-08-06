<?php
class Skill {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        return $this->db->select("SELECT s.*, sc.name as category_name FROM skills s LEFT JOIN skill_categories sc ON s.category_id = sc.id ORDER BY sc.sort_order ASC, s.sort_order ASC, s.name ASC");
    }

    public function getById(int $id): ?array {
        return $this->db->selectOne("SELECT s.*, sc.name as category_name FROM skills s LEFT JOIN skill_categories sc ON s.category_id = sc.id WHERE s.id = ?", [$id]);
    }

    public function create(array $data): int {
        return $this->db->insert('skills', $data);
    }

    public function update(int $id, array $data): int {
        return $this->db->update('skills', $data, 'id = ?', [$id]);
    }

    public function delete(int $id): int {
        return $this->db->delete('skills', 'id = ?', [$id]);
    }
}
