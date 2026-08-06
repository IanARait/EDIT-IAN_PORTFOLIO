<?php
class Project {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(array $filters = []): array {
        $where = ["p.status = 'published'"];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['featured'])) {
            $where[] = 'p.featured = 1';
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.title LIKE ? OR p.client LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        if (!empty($filters['status'])) {
            $where[0] = "p.status = ?";
            $params[0] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);
        $orderBy = $filters['sort'] ?? 'p.featured DESC, p.sort_order ASC, p.created_at DESC';
        $limit = isset($filters['limit']) ? ' LIMIT ' . (int)$filters['limit'] : '';

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM projects p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$whereClause} 
                ORDER BY {$orderBy}{$limit}";

        return $this->db->select($sql, $params);
    }

    public function getById(int $id): ?array {
        return $this->db->selectOne(
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
             FROM projects p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ?",
            [$id]
        );
    }

    public function create(array $data): int {
        return $this->db->insert('projects', $data);
    }

    public function update(int $id, array $data): int {
        return $this->db->update('projects', $data, 'id = ?', [$id]);
    }

    public function delete(int $id): int {
        $project = $this->getById($id);
        if ($project) {
            if (!empty($project['thumbnail'])) {
                $path = THUMBNAILS_PATH . '/' . $project['thumbnail'];
                if (file_exists($path)) unlink($path);
            }
            if (!empty($project['video_file'])) {
                $path = VIDEOS_PATH . '/' . $project['video_file'];
                if (file_exists($path)) unlink($path);
            }
        }
        return $this->db->delete('projects', 'id = ?', [$id]);
    }

    public function getFeatured(int $limit = 6): array {
        return $this->getAll(['featured' => true, 'limit' => $limit]);
    }

    public function getRecent(int $limit = 6): array {
        return $this->getAll(['limit' => $limit, 'sort' => 'p.created_at DESC']);
    }

    public function getCategories(): array {
        return $this->db->select("SELECT * FROM categories ORDER BY sort_order ASC");
    }

    public function incrementViews(int $id): void {
        $this->db->query("UPDATE projects SET views = views + 1 WHERE id = ?", [$id]);
    }

    public function getTotalCount(): int {
        return $this->db->count('projects');
    }

    public function getStats(): array {
        return [
            'total' => $this->getTotalCount(),
            'published' => $this->db->count('projects', "status = 'published'"),
            'featured' => $this->db->count('projects', "featured = 1"),
            'draft' => $this->db->count('projects', "status = 'draft'"),
            'total_views' => $this->db->selectOne("SELECT COALESCE(SUM(views), 0) as total FROM projects")['total'] ?? 0,
        ];
    }

    public function getAllAdmin(array $filters = []): array {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.title LIKE ? OR p.client LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);
        $page = $filters['page'] ?? 1;
        $perPage = defined('ADMIN_ITEMS_PER_PAGE') ? ADMIN_ITEMS_PER_PAGE : 10;
        $offset = ($page - 1) * $perPage;
        $orderBy = $filters['sort'] ?? 'p.created_at DESC';

        $total = $this->db->selectOne("SELECT COUNT(*) as cnt FROM projects p WHERE {$whereClause}", $params)['cnt'];

        $sql = "SELECT p.*, c.name as category_name 
                FROM projects p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$whereClause} 
                ORDER BY {$orderBy} 
                LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data' => $this->db->select($sql, $params),
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }
}
