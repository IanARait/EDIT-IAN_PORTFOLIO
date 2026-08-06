<?php
class Setting {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function isAvailable(): bool {
        return Database::isConnected();
    }

    public function get(string $key): ?string {
        if (!$this->isAvailable()) return null;
        $result = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        return $result ? $result['setting_value'] : null;
    }

    public function set(string $key, ?string $value, string $group = 'general'): void {
        if (!$this->isAvailable()) return;
        $existing = $this->db->selectOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($existing) {
            $this->db->update('settings', ['setting_value' => $value, 'setting_group' => $group], 'setting_key = ?', [$key]);
        } else {
            $this->db->insert('settings', ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => $group]);
        }
    }

    public function getGroup(string $group): array {
        if (!$this->isAvailable()) return [];
        $results = $this->db->select("SELECT * FROM settings WHERE setting_group = ?", [$group]);
        $output = [];
        foreach ($results as $row) {
            $output[$row['setting_key']] = $row['setting_value'];
        }
        return $output;
    }

    public function getAll(): array {
        if (!$this->isAvailable()) return [];
        $results = $this->db->select("SELECT * FROM settings ORDER BY setting_group, setting_key");
        $output = [];
        foreach ($results as $row) {
            $output[$row['setting_key']] = $row['setting_value'];
        }
        return $output;
    }

    public function bulkUpdate(array $data): void {
        foreach ($data as $key => $value) {
            $existing = $this->db->selectOne("SELECT setting_group FROM settings WHERE setting_key = ?", [$key]);
            $group = $existing ? $existing['setting_group'] : 'general';
            $this->set($key, $value, $group);
        }
    }
}
