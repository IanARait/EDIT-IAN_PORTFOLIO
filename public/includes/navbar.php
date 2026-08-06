<?php
$siteLogo = $settings->get('site_logo');
$siteName = $settings->get('site_name') ?: 'EDITOR';
?>
<nav>
    <a href="<?= BASE_URL ?>/public/" class="nav-logo">
        <?php if ($siteLogo): ?>
            <div class="nav-logo-icon"><img src="<?= UPLOADS_URL ?>/logos/<?= sanitize($siteLogo) ?>" alt="<?= sanitize($siteName) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;"></div>
            <span class="nav-logo-text">IAN EDIT'S</span>
        <?php else: ?>
            <div class="nav-logo-icon">IE</div>
            <span class="nav-logo-text">IAN EDIT'S</span>
        <?php endif; ?>
    </a>
    <div class="nav-links" id="navMenu">
        <a href="#home">Home</a>
        <a href="#work">Work</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>
        <a href="#contact" class="nav-cta-mobile">HIRE ME</a>
    </div>
    <a href="#contact" class="nav-cta">HIRE ME</a>
    <button class="hamburger" id="hamburger" aria-label="Toggle navigation">
        <span></span>
        <span></span>
        <span></span>
    </button>
</nav>
<div class="nav-overlay" id="navOverlay"></div>
