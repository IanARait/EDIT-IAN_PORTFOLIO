<?php
class Message {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(array $filters = []): array {
        $where = ['1=1'];
        $params = [];

        if (isset($filters['is_read']) && $filters['is_read'] !== '') {
            $where[] = 'is_read = ?';
            $params[] = (int)$filters['is_read'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(name LIKE ? OR email LIKE ? OR message LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);
        $page = $filters['page'] ?? 1;
        $perPage = defined('ADMIN_ITEMS_PER_PAGE') ? ADMIN_ITEMS_PER_PAGE : 10;
        $offset = ($page - 1) * $perPage;
        $orderBy = $filters['sort'] ?? 'created_at DESC';

        $total = $this->db->selectOne("SELECT COUNT(*) as cnt FROM messages WHERE {$whereClause}", $params)['cnt'];

        $sql = "SELECT * FROM messages WHERE {$whereClause} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data' => $this->db->select($sql, $params),
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM messages WHERE id = ?", [$id]);
    }

    public function create(array $data): int {
        return $this->db->insert('messages', $data);
    }

    public function delete(int $id): int {
        return $this->db->delete('messages', 'id = ?', [$id]);
    }

    public function markAsRead(int $id): int {
        return $this->db->update('messages', ['is_read' => 1], 'id = ?', [$id]);
    }

    public function markAsUnread(int $id): int {
        return $this->db->update('messages', ['is_read' => 0], 'id = ?', [$id]);
    }

    public function getUnreadCount(): int {
        return $this->db->count('messages', 'is_read = 0');
    }

    public function getTotalCount(): int {
        return $this->db->count('messages');
    }

    public function deleteRead(): int {
        return $this->db->delete('messages', 'is_read = 1');
    }
}
