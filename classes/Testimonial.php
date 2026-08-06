<?php
class Testimonial {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        return $this->db->select("SELECT * FROM testimonials ORDER BY sort_order ASC");
    }

    public function getFeatured(int $limit = 5): array {
        return $this->db->select("SELECT * FROM testimonials ORDER BY RAND() LIMIT ?", [$limit]);
    }

    public function getById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM testimonials WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        return $this->db->insert('testimonials', $data);
    }

    public function update(int $id, array $data): int {
        return $this->db->update('testimonials', $data, 'id = ?', [$id]);
    }

    public function delete(int $id): int {
        return $this->db->delete('testimonials', 'id = ?', [$id]);
    }

    public function getAverageRating(): float {
        $result = $this->db->selectOne("SELECT COALESCE(AVG(rating), 0) as avg_rating FROM testimonials");
        return round((float)($result['avg_rating'] ?? 0), 1);
    }
}
