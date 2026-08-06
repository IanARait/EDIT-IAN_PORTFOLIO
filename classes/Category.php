<?php
class Category {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        return $this->db->select("SELECT c.*, (SELECT COUNT(*) FROM projects WHERE category_id = c.id) as project_count FROM categories c ORDER BY c.sort_order ASC, c.name ASC");
    }

    public function getById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM categories WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        return $this->db->insert('categories', $data);
    }

    public function update(int $id, array $data): int {
        return $this->db->update('categories', $data, 'id = ?', [$id]);
    }

    public function delete(int $id): int {
        $projectCount = $this->db->selectOne("SELECT COUNT(*) as cnt FROM projects WHERE category_id = ?", [$id])['cnt'] ?? 0;
        if ($projectCount > 0) {
            return -1;
        }
        return $this->db->delete('categories', 'id = ?', [$id]);
    }

    public static function slugify(string $text): string {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
