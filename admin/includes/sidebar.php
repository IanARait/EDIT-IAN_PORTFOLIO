<button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
    <i class="bi bi-list"></i>
</button>
<aside class="admin-sidebar" id="adminSidebar">
     <br>
    <div class="sidebar-header">
        <a href="<?= ADMIN_URL ?>/" class="sidebar-logo">
           
            <span class="logo-highlight">&lt;</span> Admin Portal<span class="logo-highlight">/&gt;</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="<?= ADMIN_URL ?>/" class="sidebar-link <?= $currentPage === 'index' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                Dashboard
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Content</div>
            <a href="<?= ADMIN_URL ?>/projects.php" class="sidebar-link <?= $currentPage === 'projects' ? 'active' : '' ?>">
                <i class="bi bi-collection-play-fill"></i>
                Projects
            </a>
            <a href="<?= ADMIN_URL ?>/categories.php" class="sidebar-link <?= $currentPage === 'categories' ? 'active' : '' ?>">
                <i class="bi bi-tags-fill"></i>
                Categories
            </a>
            <a href="<?= ADMIN_URL ?>/skills.php" class="sidebar-link <?= $currentPage === 'skills' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-fill"></i>
                Skills
            </a>
            <a href="<?= ADMIN_URL ?>/skill-categories.php" class="sidebar-link <?= $currentPage === 'skill-categories' ? 'active' : '' ?>">
                <i class="bi bi-tags-fill"></i>
                Skill Categories
            </a>
            <a href="<?= ADMIN_URL ?>/messages.php" class="sidebar-link <?= $currentPage === 'messages' ? 'active' : '' ?>">
                <i class="bi bi-envelope-fill"></i>
                Messages
                <?php if ($unreadMessages > 0): ?>
                <span class="badge"><?= $unreadMessages ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Configuration</div>
            <a href="<?= ADMIN_URL ?>/users.php" class="sidebar-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i>
                Users
            </a>
            <a href="<?= ADMIN_URL ?>/settings.php" class="sidebar-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                <i class="bi bi-gear-fill"></i>
                Settings
            </a>
        </div>
        
        <div class="nav-section" style="margin-top:auto; padding-top:20px; border-top:1px solid #222;">
            <a href="<?= BASE_URL ?>/public/" class="sidebar-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i>
                View Site
            </a>
            <a href="<?= ADMIN_URL ?>/logout.php" class="sidebar-link" onclick="return confirm('Are you sure you want to logout?')">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>
        </div>
    </nav>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
