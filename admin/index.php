<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$projectModel = new Project();
$messageModel = new Message();
$testimonialModel = new Testimonial();

$stats = $projectModel->getStats();
$recentProjects = $projectModel->getAllAdmin(['limit' => 5]);
$recentMessages = $messageModel->getAll(['limit' => 5]);
$totalMessages = $messageModel->getTotalCount();
$avgRating = $testimonialModel->getAverageRating();
?>

<div class="admin-content">
    <div class="admin-header">
        <div>
            <h1>Dashboard</h1>
            <p style="color:var(--text-secondary); font-size:14px; margin-top:4px;">Welcome back, <?= sanitize($_SESSION['admin_name'] ?? 'Admin') ?></p>
        </div>
        <div class="admin-header-actions">
            <a href="<?= BASE_URL ?>/public/" target="_blank" class="btn btn-secondary btn-sm" style="border-color:var(--border);">
                <i class="bi bi-box-arrow-up-right"></i> View Site
            </a>
            <a href="<?= ADMIN_URL ?>/projects.php?action=add" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> New Project
            </a>
        </div>
    </div>
    
    <div class="admin-body">
        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-icon green">
                    <i class="bi bi-collection-play-fill"></i>
                </div>
                <div class="stat-card-value"><?= $stats['total'] ?></div>
                <div class="stat-card-label">Total Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <div class="stat-card-value"><?= formatNumber($stats['total_views']) ?></div>
                <div class="stat-card-label">Total Views</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon orange">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div class="stat-card-value"><?= $totalMessages ?></div>
                <div class="stat-card-label">Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon purple">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div class="stat-card-value"><?= $avgRating ?></div>
                <div class="stat-card-label">Avg Rating</div>
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
            <!-- Recent Projects -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3>Recent Projects</h3>
                    <a href="<?= ADMIN_URL ?>/projects.php" class="btn btn-ghost btn-sm" style="color:var(--primary);">View All</a>
                </div>
                <div class="content-card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Featured</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentProjects['data'])): ?>
                            <?php foreach ($recentProjects['data'] as $project): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div style="width:40px; height:40px; border-radius:8px; bg:var(--bg-elevated); overflow:hidden; flex-shrink:0;">
                                            <?php if (!empty($project['thumbnail_url'])): ?>
                                            <img src="<?= htmlspecialchars(driveImageUrl($project['thumbnail_url'])) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                            <?php elseif ($project['thumbnail']): ?>
                                            <img src="<?= UPLOADS_URL ?>/thumbnails/<?= $project['thumbnail'] ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                            <?php else: ?>
                                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--bg-elevated); color:var(--text-muted);"><i class="bi bi-film"></i></div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:500; font-size:14px;"><?= sanitize($project['title']) ?></div>
                                            <div style="font-size:12px; color:var(--text-secondary);"><?= sanitize($project['client'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-primary"><?= sanitize($project['category_name'] ?? '') ?></span></td>
                                <td><span class="status-badge status-<?= $project['status'] ?>"><?= ucfirst($project['status']) ?></span></td>
                                <td><?php if ($project['featured']): ?><i class="bi bi-star-fill" style="color:#FFD700;"></i><?php else: ?><span style="color:var(--text-muted);">—</span><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-secondary);">No projects yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Messages -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3>Recent Messages</h3>
                    <a href="<?= ADMIN_URL ?>/messages.php" class="btn btn-ghost btn-sm" style="color:var(--primary);">View All</a>
                </div>
                <div class="content-card-body" style="padding:0;">
                    <?php if (!empty($recentMessages['data'])): ?>
                    <?php foreach ($recentMessages['data'] as $msg): ?>
                    <div style="padding:16px 24px; border-bottom:1px solid #1a1a1a; <?= !$msg['is_read'] ? 'background:rgba(0,230,118,0.02);' : '' ?>">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <span style="font-weight:500; font-size:14px;"><?= sanitize($msg['name']) ?></span>
                            <?php if (!$msg['is_read']): ?>
                            <span style="width:8px; height:8px; border-radius:50%; background:var(--primary); flex-shrink:0;"></span>
                            <?php endif; ?>
                        </div>
                        <p style="font-size:13px; color:var(--text-secondary); margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= sanitize(substr($msg['message'], 0, 60)) ?>...</p>
                        <span style="font-size:11px; color:var(--text-muted);"><?= timeAgo($msg['created_at']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div style="padding:40px; text-align:center; color:var(--text-secondary);">No messages yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-footer">
        &copy; <?= date('Y') ?> Portfolio Admin Panel
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
