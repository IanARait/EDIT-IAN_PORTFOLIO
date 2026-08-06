<?php
class Service {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        return $this->db->select("SELECT * FROM services ORDER BY sort_order ASC");
    }

    public function getById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM services WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        return $this->db->insert('services', $data);
    }

    public function update(int $id, array $data): int {
        return $this->db->update('services', $data, 'id = ?', [$id]);
    }

    public function delete(int $id): int {
        return $this->db->delete('services', 'id = ?', [$id]);
    }

    public function reorder(array $orders): void {
        foreach ($orders as $id => $order) {
            $this->db->update('services', ['sort_order' => $order], 'id = ?', [$id]);
        }
    }
}
