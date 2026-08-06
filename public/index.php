<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$projectModel = new Project();
$testimonialModel = new Testimonial();
$featuredProjects = $projectModel->getAll();
$stats = $projectModel->getStats();
?>

<section class="hero" id="home">
    <div class="hero-tag">Available for Projects</div>
    <h1><?= sanitize($settings->get('hero_title') ?: 'Professional <span class="highlight">Video Editor</span>') ?></h1>
    <p class="hero-sub"><?= sanitize($settings->get('hero_subtitle') ?: 'Helping brands and businesses create high-converting video content that captivates audiences and drives results.') ?></p>
    <div class="hero-btns">
        <a href="#work" class="btn-primary"><?= sanitize($settings->get('cta_primary_text') ?: 'View My Work') ?></a>
        <a href="#contact" class="btn-outline"><?= sanitize($settings->get('cta_secondary_text') ?: 'Get in Touch') ?></a>
    </div>
</section>

<section class="section" id="work">
    <div class="section-header">
        <h2>Featured <span>Work</span></h2>
        <p>A selection of recent projects — click any card to preview.</p>
    </div>

    <div class="toolbar">
        <button class="filter-btn active" data-filter="all">All</button>
        <?php
        $catModel = new Category();
        $categories = $catModel->getAll();
        foreach ($categories as $cat):
        ?>
        <button class="filter-btn" data-filter="<?= sanitize($cat['slug']) ?>"><?= sanitize($cat['name']) ?></button>
        <?php endforeach; ?>
    </div>

    <div class="portfolio-search">
        <input type="text" id="portfolioSearch" placeholder="Search projects..." autocomplete="off">
    </div>

    <div class="portfolio-grid" id="portfolioGrid">
        <?php if (!empty($featuredProjects)): ?>
            <?php foreach ($featuredProjects as $index => $project):
                $thumb = '';
                if (!empty($project['thumbnail_url'])) {
                    $thumb = driveImageUrl($project['thumbnail_url']);
                } elseif (!empty($project['thumbnail'])) {
                    $thumb = UPLOADS_URL . '/thumbnails/' . $project['thumbnail'];
                }
                $categorySlug = $project['category_slug'] ?? 'default';
                $categoryName = $project['category_name'] ?? 'Project';
                $emoji = match($categorySlug) {
                    'vsl' => '🎬',
                    'ugc' => '🎥',
                    'commercial' => '📺',
                    'youtube' => '▶️',
                    'tiktok' => '🎵',
                    'facebook-ads' => '📣',
                    'podcast' => '🎙️',
                    'motion-graphics' => '✨',
                    'website' => '🌐',
                    default => '🎬',
                };
            ?>
            <div class="work-card"
                 data-category="<?= htmlspecialchars($categorySlug) ?>"
                 data-title="<?= htmlspecialchars($project['title']) ?>"
                 data-client="<?= htmlspecialchars($project['client'] ?? '') ?>"
                 data-video="<?= htmlspecialchars($project['video_url'] ?? '') ?>"
                 data-vfile="<?= htmlspecialchars($project['video_file'] ?? '') ?>"
                 data-github="<?= htmlspecialchars($project['github_url'] ?? '') ?>"
                 data-description="<?= htmlspecialchars($project['description'] ?? '') ?>"
                 data-software="<?= htmlspecialchars($project['software_used'] ?? '') ?>"
                 data-duration="<?= htmlspecialchars($project['duration'] ?? '') ?>"
                 data-thumb="<?= htmlspecialchars($thumb) ?>"
                 data-emoji="<?= $emoji ?>"
                 data-year="<?= htmlspecialchars($project['year'] ?? '') ?>"
                 data-views="<?= (int)($project['views'] ?? 0) ?>"
                 data-status="<?= htmlspecialchars($project['status'] ?? 'published') ?>">
                <div class="work-thumb">
                    <?php if ($thumb): ?>
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($project['title']) ?>" loading="lazy">
                    <?php else: ?>
                        <?= $emoji ?>
                    <?php endif; ?>
                    <div class="play-overlay">
                        <div class="play-icon">&#9654;</div>
                    </div>
                </div>
                <div class="work-body">
                    <span class="work-tag" data-cat="<?= htmlspecialchars($categorySlug) ?>"><?= htmlspecialchars($categoryName) ?></span>
                    <div class="work-title"><?= htmlspecialchars($project['title']) ?></div>
                    <div class="work-client">Client: <?= htmlspecialchars($project['client'] ?? 'N/A') ?></div>
                    <?php if (!empty($project['github_url'])): ?>
                    <a class="work-github" href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" rel="noopener noreferrer">GitHub / View Website &nearr;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="es-icon">📂</div>
                <h3>No projects yet</h3>
                <p>Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section" id="about">
    <div class="section-header">
        <h2>About <span>Me</span></h2>
    </div>
    <div class="about-grid">
        <div class="about-card">
            <h3>Bio</h3>
            <p class="about-bio">
                <?= nl2br(sanitize($settings->get('about_text') ?: 'With over <strong>3 years of hands-on experience</strong> in video editing and post-production, I specialize in transforming raw footage into compelling visual stories. Whether it\'s a high-energy commercial, a polished VSL, or an engaging social media reel, I bring meticulous attention to detail and a deep understanding of pacing, color, and sound design. My goal is simple: make your videos impossible to scroll past.')) ?>
            </p>
            <?php
            $expYears = (int)($settings->get('experience_years') ?: 0);
            $totalProjects = (int)($settings->get('total_projects') ?: 0);
            $totalClients = (int)($settings->get('total_clients') ?: 0);
            $videosEdited = (int)($settings->get('videos_edited') ?: 0);
            if ($expYears > 0 || $totalProjects > 0 || $totalClients > 0 || $videosEdited > 0):
            ?>
            <div class="stats-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:1.5rem;">
                <?php if ($expYears > 0): ?>
                <div class="stat-item" style="text-align:center; padding:12px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.06);">
                    <div style="font-size:24px; font-weight:700; color:var(--primary);"><?= $expYears ?>+</div>
                    <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">Years Experience</div>
                </div>
                <?php endif; ?>
                <?php if ($totalProjects > 0): ?>
                <div class="stat-item" style="text-align:center; padding:12px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.06);">
                    <div style="font-size:24px; font-weight:700; color:var(--primary);"><?= $totalProjects ?>+</div>
                    <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">Projects Done</div>
                </div>
                <?php endif; ?>
                <?php if ($totalClients > 0): ?>
                <div class="stat-item" style="text-align:center; padding:12px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.06);">
                    <div style="font-size:24px; font-weight:700; color:var(--primary);"><?= $totalClients ?>+</div>
                    <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">Happy Clients</div>
                </div>
                <?php endif; ?>
                <?php if ($videosEdited > 0): ?>
                <div class="stat-item" style="text-align:center; padding:12px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.06);">
                    <div style="font-size:24px; font-weight:700; color:var(--primary);"><?= $videosEdited ?>+</div>
                    <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">Videos Edited</div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php $resumeFile = $settings->get('resume_file'); if ($resumeFile): ?>
            <div style="margin-top:1.5rem;">
                <a href="<?= UPLOADS_URL . '/' . sanitize($resumeFile) ?>" target="_blank" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; font-size:13px;">
                    <i class="bi bi-download"></i> Download CV
                </a>
            </div>
            <?php endif; ?>
            <div class="social-links" style="margin-top: 1.5rem;">
                <?php
                $socialIcons = [
                    'social_youtube' => 'bi-youtube',
                    'social_instagram' => 'bi-instagram',
                    'social_twitter' => 'bi-twitter-x',
                    'social_linkedin' => 'bi-linkedin',
                    'social_tiktok' => 'bi-tiktok',
                ];
                foreach ($socialIcons as $key => $icon):
                    $url = $settings->get($key);
                    if ($url):
                ?>
                <a href="<?= sanitize($url) ?>" class="social-link" target="_blank" rel="noopener"><i class="bi <?= $icon ?>"></i></a>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <div class="about-card">
            <h3>Skills</h3>
            <?php
            $skillModel = new Skill();
            $skills = $skillModel->getAll();
            $grouped = [];
            foreach ($skills as $skill) {
                $catName = $skill['category_name'] ?? 'Other';
                $grouped[$catName][] = $skill;
            }
            ?>
            <?php if (!empty($grouped)): ?>
            <?php foreach ($grouped as $catName => $catSkills): ?>
            <div style="margin-bottom:16px;">
                <div style="font-size:12px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); margin-bottom:8px; font-weight:600;"><?= sanitize($catName) ?></div>
                <?php foreach ($catSkills as $skill): ?>
                <div class="skill-bar">
                    <div class="skill-bar-label"><span><?= sanitize($skill['name']) ?></span><span><?= (int)$skill['percentage'] ?>%</span></div>
                    <div class="skill-bar-track"><div class="skill-bar-fill" style="width: <?= (int)$skill['percentage'] ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="arcade-section" id="arcade">
  <div id="menu">
    <p class="eyebrow">While waiting for my reply</p>
    <h1 class="arcade-title">PLAY SOME GAMES</h1>
    <p class="sub">Five machines, five ways to pass the time. Pick a cabinet to play.</p>
    <div class="row">
      <button class="cabinet-card" type="button" data-game="tetris" aria-label="Play Tetris">
        <div class="icon-wrap"><svg viewBox="0 0 84 84" xmlns="http://www.w3.org/2000/svg"><rect x="14" y="42" width="18" height="18" rx="2" fill="#00e676"/><rect x="32" y="42" width="18" height="18" rx="2" fill="#00e676"/><rect x="32" y="24" width="18" height="18" rx="2" fill="#00c853"/><rect x="50" y="24" width="18" height="18" rx="2" fill="#00c853"/><rect x="14" y="60" width="18" height="18" rx="2" fill="#00e676" opacity="0.6"/><rect x="50" y="60" width="18" height="18" rx="2" fill="#00c853" opacity="0.6"/></svg></div>
        <div class="game-name">TETRIS</div>
        <div class="game-tag">Stack &amp; clear</div>
      </button>
      <button class="cabinet-card" type="button" data-game="ttt" aria-label="Play Tic Tac Toe">
        <div class="icon-wrap"><svg viewBox="0 0 84 84" xmlns="http://www.w3.org/2000/svg"><g stroke="#eee" stroke-width="4" stroke-linecap="round"><line x1="30" y1="12" x2="30" y2="72"/><line x1="54" y1="12" x2="54" y2="72"/><line x1="12" y1="30" x2="72" y2="30"/><line x1="12" y1="54" x2="72" y2="54"/></g><g stroke="#00e676" stroke-width="5" stroke-linecap="round"><line x1="18" y1="18" x2="26" y2="26"/><line x1="26" y1="18" x2="18" y2="26"/></g><g stroke="#00c853" stroke-width="5" stroke-linecap="round"><line x1="42" y1="42" x2="50" y2="50"/><line x1="50" y1="42" x2="42" y2="50"/></g><circle cx="63" cy="21" r="6.5" fill="none" stroke="#00e676" stroke-width="4.5" opacity="0.6"/></svg></div>
        <div class="game-name">TIC-TAC-TOE</div>
        <div class="game-tag">Three in a row</div>
      </button>
      <button class="cabinet-card" type="button" data-game="chess" aria-label="Play Chess">
        <div class="icon-wrap"><svg viewBox="0 0 84 84" xmlns="http://www.w3.org/2000/svg"><rect x="10" y="10" width="64" height="64" rx="3" fill="none" stroke="rgba(238,238,238,0.15)" stroke-width="2"/><path d="M42 14c-3.5 0-6 2.7-6 6 0 2 1 3.7 2.5 4.8-4 1.6-6.5 5-6.5 9.2h20c0-4.2-2.5-7.6-6.5-9.2C47 23.7 48 22 48 20c0-3.3-2.5-6-6-6z" fill="#eee"/><rect x="30" y="34" width="24" height="6" fill="#eee"/><path d="M26 66c1-8 4-11 6-13h20c2 2 5 5 6 13z" fill="#eee"/><rect x="22" y="66" width="40" height="7" rx="2" fill="#eee"/></svg></div>
        <div class="game-name">CHESS</div>
        <div class="game-tag">Checkmate the king</div>
      </button>
      <button class="cabinet-card" type="button" data-game="dama" aria-label="Play Dama">
        <div class="icon-wrap"><svg viewBox="0 0 84 84" xmlns="http://www.w3.org/2000/svg"><rect x="10" y="10" width="64" height="64" rx="3" fill="#161616"/><g fill="#eee"><rect x="10" y="10" width="16" height="16"/><rect x="42" y="10" width="16" height="16"/><rect x="26" y="26" width="16" height="16"/><rect x="58" y="26" width="16" height="16"/><rect x="10" y="42" width="16" height="16"/><rect x="42" y="42" width="16" height="16"/><rect x="26" y="58" width="16" height="16"/><rect x="58" y="58" width="16" height="16"/></g><circle cx="18" cy="18" r="6.5" fill="#e53935"/><circle cx="66" cy="18" r="6.5" fill="#e53935"/><circle cx="34" cy="34" r="6.5" fill="#00e676"/><circle cx="34" cy="66" r="6.5" fill="#00e676"/><circle cx="66" cy="66" r="6.5" fill="#00e676"/></svg></div>
        <div class="game-name">DAMA</div>
        <div class="game-tag">Jump &amp; capture</div>
      </button>
      <button class="cabinet-card" type="button" data-game="snake" aria-label="Play Snake">
        <div class="icon-wrap"><svg viewBox="0 0 84 84" xmlns="http://www.w3.org/2000/svg"><g fill="#00e676"><rect x="14" y="50" width="12" height="12" rx="2"/><rect x="26" y="50" width="12" height="12" rx="2"/><rect x="26" y="38" width="12" height="12" rx="2"/><rect x="26" y="26" width="12" height="12" rx="2"/><rect x="38" y="26" width="12" height="12" rx="2"/><rect x="50" y="26" width="12" height="12" rx="2"/></g><rect x="50" y="14" width="12" height="12" rx="2" fill="#00c853"/><circle cx="55" cy="19" r="1.6" fill="#eee"/><rect x="58" y="58" width="10" height="10" rx="2" fill="#e53935"/></svg></div>
        <div class="game-name">SNAKE</div>
        <div class="game-tag">Eat &amp; grow</div>
      </button>
      <button class="cabinet-card" type="button" data-game="maze" aria-label="Play Maze">
        <div class="icon-wrap"><svg viewBox="0 0 84 84" xmlns="http://www.w3.org/2000/svg"><rect x="10" y="10" width="64" height="64" rx="3" fill="none" stroke="rgba(238,238,238,0.15)" stroke-width="2"/><g stroke="#00e676" stroke-width="4" stroke-linecap="round" fill="none"><path d="M22 10V30h24"/><path d="M22 46h28v20"/><path d="M46 46V22"/><path d="M62 30V46H50"/><path d="M10 46h4"/><path d="M70 30V46h-4"/></g><circle cx="22" cy="16" r="4.5" fill="#eee"/><circle cx="62" cy="60" r="4.5" fill="#e53935"/></svg></div>
        <div class="game-name">MAZE</div>
        <div class="game-tag">3 levels · timed</div>
      </button>
    </div>
    <div class="arcade-footer">six cabinets · one row · press start</div>
  </div>
  <div id="gameContainer"></div>
</section>

<section class="section contact-section" id="contact">
    <div class="section-header">
        <h2>Get in <span>Touch</span></h2>
        <p>Available for freelance work and long-term contracts.</p>
    </div>
    <div class="contact-info">
        <div class="contact-item">
            <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
            <a href="mailto:<?= sanitize($settings->get('contact_email') ?: 'hello@portfolio.com') ?>"><?= sanitize($settings->get('contact_email') ?: 'hello@portfolio.com') ?></a>
        </div>
        <div class="contact-item">
            <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
            <a href="tel:<?= sanitize($settings->get('contact_phone') ?: '+15551234567') ?>"><?= sanitize($settings->get('contact_phone') ?: '+1 (555) 123-4567') ?></a>
        </div>
        <div class="contact-item">
            <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
            <?= sanitize($settings->get('contact_location') ?: 'Available Worldwide') ?>
        </div>
    </div>
</section>

<!-- Project Preview Modal -->
<div class="modal-overlay" id="projectModal">
    <div class="project-modal">
        <div class="modal-header">
            <h3 id="modalTitle">Project Preview</h3>
            <button class="modal-close" id="modalClose" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterBtns = document.querySelectorAll('.filter-btn');
    var workCards = document.querySelectorAll('.work-card');
    var searchInput = document.getElementById('portfolioSearch');
    var debounceTimer;

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterBtns.forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var filter = btn.getAttribute('data-filter');
            workCards.forEach(function(card) {
                var cat = card.getAttribute('data-category') || '';
                card.style.display = (filter === 'all' || cat.includes(filter)) ? '' : 'none';
            });
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            var query = this.value.toLowerCase().trim();
            debounceTimer = setTimeout(function() {
                workCards.forEach(function(card) {
                    var title = (card.getAttribute('data-title') || '').toLowerCase();
                    var client = (card.getAttribute('data-client') || '').toLowerCase();
                    var match = query === '' || title.includes(query) || client.includes(query);
                    card.style.display = match ? '' : 'none';
                });
            }, 300);
        });
    }

    function getEmbedUrl(url) {
        if (!url) return null;
        try {
            if (url.includes('youtube.com/watch')) {
                var id = new URL(url).searchParams.get('v');
                return id ? 'https://www.youtube.com/embed/' + id : null;
            }
            if (url.includes('youtu.be/')) {
                var id = url.split('youtu.be/')[1].split('?')[0];
                return 'https://www.youtube.com/embed/' + id;
            }
            if (url.includes('vimeo.com/')) {
                var id = url.split('vimeo.com/')[1].split('?')[0];
                return 'https://player.vimeo.com/video/' + id;
            }
            if (url.includes('drive.google.com/file/d/')) {
                var id = url.split('drive.google.com/file/d/')[1].split('/')[0];
                return 'https://drive.google.com/file/d/' + id + '/preview';
            }
            if (url.includes('drive.google.com/open')) {
                var id = new URL(url).searchParams.get('id');
                return id ? 'https://drive.google.com/file/d/' + id + '/preview' : null;
            }
        } catch(e) { return null; }
        return null;
    }

    var modal = document.getElementById('projectModal');
    var modalTitle = document.getElementById('modalTitle');
    var modalBody = document.getElementById('modalBody');

    workCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.work-github')) return;
            var title = card.getAttribute('data-title') || '';
            var videoUrl = card.getAttribute('data-video') || '';
            var videoFile = card.getAttribute('data-vfile') || '';
            var githubUrl = card.getAttribute('data-github') || '';
            var client = card.getAttribute('data-client') || '';
            var category = card.getAttribute('data-category') || '';
            var software = card.getAttribute('data-software') || '';
            var duration = card.getAttribute('data-duration') || '';
            var description = card.getAttribute('data-description') || '';
            var thumb = card.getAttribute('data-thumb') || '';
            var emoji = card.getAttribute('data-emoji') || '🎬';
            var year = card.getAttribute('data-year') || '';
            var views = card.getAttribute('data-views') || '0';
            var status = card.getAttribute('data-status') || 'published';

            modalTitle.textContent = title;

            var html = '';
            if (videoFile) {
                html += '<div class="video-frame-wrap"><video src="<?= UPLOADS_URL ?>/videos/' + videoFile + '" muted playsinline controls style="width:100%;height:100%;object-fit:cover;background:#000;"></video></div>';
            } else {
                var embed = getEmbedUrl(videoUrl);
                if (embed) {
                    html += '<div class="video-frame-wrap"><iframe src="' + embed + '" allow="autoplay; encrypted-media; fullscreen" allowfullscreen></iframe></div>';
                } else if (thumb) {
                    html += '<div class="video-frame-wrap"><img src="' + thumb + '" alt="' + title + '" style="width:100%;height:100%;object-fit:cover;"></div>';
                } else {
                    html += '<div class="no-video-box">' + emoji + '</div>';
                }
            }

            html += '<div class="video-meta">';
            if (githubUrl) {
                html += '<a class="github-cta" href="' + githubUrl + '" target="_blank" rel="noopener noreferrer">View on GitHub &nearr;</a>';
            }
            html += '<div style="margin-bottom:16px;">';
            html += '<h3 style="font-size:18px; font-weight:700; color:#fff; margin-bottom:4px;">' + title + '</h3>';
            html += '</div>';
            html += '<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">';
            html += '<div><span style="color:#555; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Client</span><div style="color:#ccc; font-size:14px; margin-top:2px;">' + (client || 'N/A') + '</div></div>';
            html += '<div><span style="color:#555; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Category</span><div style="color:#ccc; font-size:14px; margin-top:2px;">' + (category || 'N/A') + '</div></div>';
            html += '<div><span style="color:#555; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Year</span><div style="color:#ccc; font-size:14px; margin-top:2px;">' + (year || 'N/A') + '</div></div>';
            html += '<div><span style="color:#555; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Duration</span><div style="color:#ccc; font-size:14px; margin-top:2px;">' + (duration || 'N/A') + '</div></div>';
            html += '<div><span style="color:#555; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Software</span><div style="color:#ccc; font-size:14px; margin-top:2px;">' + (software || 'N/A') + '</div></div>';
            html += '<div><span style="color:#555; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Views</span><div style="color:#ccc; font-size:14px; margin-top:2px;">' + Number(views).toLocaleString() + '</div></div>';
            html += '</div>';
            html += '<div style="padding-top:12px; border-top:1px solid #1a1a1a;">';
            html += '<span style="color:#555; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Description</span>';
            html += '<p style="color:#999; font-size:14px; line-height:1.7; margin-top:6px;">' + (description || 'No description available.') + '</p>';
            html += '</div>';
            html += '</div>';

            modalBody.innerHTML = html;
            var vidEl = modalBody.querySelector('video');
            if (vidEl) {
                var captureFrame = function() {
                    try {
                        var canvas = document.createElement('canvas');
                        canvas.width = vidEl.videoWidth;
                        canvas.height = vidEl.videoHeight;
                        canvas.getContext('2d').drawImage(vidEl, 0, 0, canvas.width, canvas.height);
                        vidEl.poster = canvas.toDataURL('image/jpeg', 0.7);
                    } catch(e) {}
                };
                vidEl.addEventListener('loadeddata', function() { try { vidEl.currentTime = 0.1; } catch(e) {} });
                vidEl.addEventListener('seeked', function() {
                    captureFrame();
                    vidEl.currentTime = 0;
                    vidEl.play().catch(function() {});
                });
            }
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    });

    document.getElementById('modalClose').addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        var iframe = modal.querySelector('iframe');
        if (iframe) iframe.src = '';
        var vid = modal.querySelector('video');
        if (vid) { vid.pause(); vid.src = ''; }
        modalBody.innerHTML = '';
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
