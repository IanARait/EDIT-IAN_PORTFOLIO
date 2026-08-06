<?php
require_once __DIR__ . '/bootstrap.php';

$currentUser = Auth::getCurrentUser();
$messageModel = new Message();
$unreadMessages = isLoggedIn() ? $messageModel->getUnreadCount() : 0;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= isset($pageTitle) ? sanitize($pageTitle) : 'Dashboard' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= ADMIN_URL ?>/assets/css/admin.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body>
    <?php if ($flash): ?>
    <div class="toast-container" id="flashToast" style="position:fixed;top:20px;right:20px;z-index:9999;">
        <div class="toast show toast-<?= $flash['type'] ?>" style="min-width:300px;">
            <div class="toast-body" style="display:flex;align-items:center;gap:10px;">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill text-success' : ($flash['type'] === 'error' ? 'exclamation-circle-fill text-danger' : 'info-circle-fill text-info') ?>"></i>
                <?= sanitize($flash['message']) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="admin-layout">
