<?php
class Database {
    private static ?Database $instance = null;
    private ?\PDO $pdo = null;
    private static bool $connected = false;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$connected = true;
        } catch (\PDOException $e) {
            $this->pdo = null;
            self::$connected = false;
            error_log("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function isConnected(): bool {
        return self::$connected;
    }

    public function getConnection(): ?\PDO {
        return $this->pdo;
    }

    private function ensureConnected(): void {
        if (!$this->pdo) {
            throw new \RuntimeException('Database not connected. Please run setup.php first.');
        }
    }

    public function query(string $sql, array $params = []): \PDOStatement {
        $this->ensureConnected();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function select(string $sql, array $params = []): array {
        try {
            return $this->query($sql, $params)->fetchAll();
        } catch (\Exception $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function selectOne(string $sql, array $params = []): ?array {
        try {
            $result = $this->query($sql, $params)->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            error_log("Query error: " . $e->getMessage());
            return null;
        }
    }

    public function insert(string $table, array $data): int {
        $this->ensureConnected();
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $this->ensureConnected();
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $whereParams = []): int {
        $this->ensureConnected();
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $whereParams);
        return $stmt->rowCount();
    }

    public function count(string $table, string $where = '1=1', array $whereParams = []): int {
        $sql = "SELECT COUNT(*) as cnt FROM {$table} WHERE {$where}";
        $result = $this->selectOne($sql, $whereParams);
        return (int) ($result['cnt'] ?? 0);
    }

    public function exec(string $sql): bool {
        $this->ensureConnected();
        return $this->pdo->exec($sql);
    }
}
