<?php
class Auth {
    public function login(string $email, string $password): bool {
        $db = Database::getInstance();
        $admin = $db->selectOne("SELECT * FROM admins WHERE email = ?", [$email]);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];
            $db->update('admins', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$admin['id']]);
            return true;
        }
        return false;
    }

    public function logout(): void {
        session_destroy();
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }

    public static function check(): void {
        if (!isLoggedIn()) {
            setFlash('error', 'Please log in to access the admin panel.');
            redirect(ADMIN_URL . '/login.php');
        }
    }

    public static function getCurrentUser(): ?array {
        if (!isLoggedIn()) return null;
        $db = Database::getInstance();
        return $db->selectOne("SELECT id, name, email, avatar, role, created_at FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
    }

    public static function updateProfile(array $data): array {
        $db = Database::getInstance();

        if (isset($data['email'])) {
            $existing = $db->selectOne("SELECT id FROM admins WHERE email = ? AND id != ?", [$data['email'], $_SESSION['admin_id']]);
            if ($existing) {
                return ['success' => false, 'error' => 'Email is already taken by another admin.'];
            }
            $_SESSION['admin_email'] = $data['email'];
        }

        $db->update('admins', $data, 'id = ?', [$_SESSION['admin_id']]);
        if (isset($data['name'])) $_SESSION['admin_name'] = $data['name'];
        return ['success' => true];
    }

    public static function updatePassword(string $currentPassword, string $newPassword): array {
        $db = Database::getInstance();
        $admin = $db->selectOne("SELECT password FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
        
        if (!$admin || !password_verify($currentPassword, $admin['password'])) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }
        
        $hashed = password_hash($newPassword, HASH_ALGO, ['cost' => HASH_COST]);
        $db->update('admins', ['password' => $hashed], 'id = ?', [$_SESSION['admin_id']]);
        return ['success' => true];
    }

    public static function getAll(): array {
        $db = Database::getInstance();
        return $db->select("SELECT id, name, email, role, last_login, created_at FROM admins ORDER BY created_at ASC");
    }

    public static function create(array $data): array {
        $db = Database::getInstance();
        
        $existing = $db->selectOne("SELECT id FROM admins WHERE email = ?", [$data['email']]);
        if ($existing) {
            return ['success' => false, 'error' => 'An admin with this email already exists.'];
        }
        
        $hashed = password_hash($data['password'], HASH_ALGO, ['cost' => HASH_COST]);
        $id = $db->insert('admins', [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $hashed,
            'role'     => $data['role'] ?? 'editor',
        ]);
        
        return ['success' => true, 'id' => $id];
    }

    public static function delete(int $id): array {
        $db = Database::getInstance();
        
        if ($id === (int)$_SESSION['admin_id']) {
            return ['success' => false, 'error' => 'You cannot delete your own account.'];
        }
        
        $admin = $db->selectOne("SELECT id FROM admins WHERE id = ?", [$id]);
        if (!$admin) {
            return ['success' => false, 'error' => 'Admin not found.'];
        }
        
        $db->delete('admins', 'id = ?', [$id]);
        return ['success' => true];
    }
}
