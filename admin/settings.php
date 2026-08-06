<?php
$pageTitle = 'Settings';

// Include PHP deps only (no HTML) so POST + redirect() work
require_once __DIR__ . '/includes/bootstrap.php';

// Handle POST before any HTML output so redirect() works
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrf($_POST[CSRF_TOKEN_NAME])) {
        setFlash('error', 'Invalid CSRF token.');
        redirect(ADMIN_URL . '/settings.php');
    }

    $settingModel = new Setting();
    $settingsData = [
        'site_name'         => $_POST['site_name'] ?? '',
        'site_tagline'      => $_POST['site_tagline'] ?? '',
        'site_description'  => $_POST['site_description'] ?? '',
        'hero_title'        => $_POST['hero_title'] ?? '',
        'hero_subtitle'     => $_POST['hero_subtitle'] ?? '',
        'cta_primary_text'  => $_POST['cta_primary_text'] ?? '',
        'cta_secondary_text'=> $_POST['cta_secondary_text'] ?? '',
        'about_text'        => $_POST['about_text'] ?? '',
        'experience_years'  => $_POST['experience_years'] ?? '0',
        'total_projects'    => $_POST['total_projects'] ?? '0',
        'total_clients'     => $_POST['total_clients'] ?? '0',
        'videos_edited'     => $_POST['videos_edited'] ?? '0',
        'contact_email'     => $_POST['contact_email'] ?? '',
        'contact_phone'     => $_POST['contact_phone'] ?? '',
        'contact_location'  => $_POST['contact_location'] ?? '',
        'social_youtube'    => $_POST['social_youtube'] ?? '',
        'social_instagram'  => $_POST['social_instagram'] ?? '',
        'social_twitter'    => $_POST['social_twitter'] ?? '',
        'social_linkedin'   => $_POST['social_linkedin'] ?? '',
        'social_tiktok'     => $_POST['social_tiktok'] ?? '',
        'smtp_host'         => $_POST['smtp_host'] ?? '',
        'smtp_port'         => $_POST['smtp_port'] ?? '',
        'smtp_username'     => $_POST['smtp_username'] ?? '',
        'smtp_password'     => $_POST['smtp_password'] ?? '',
        'smtp_encryption'   => $_POST['smtp_encryption'] ?? 'tls',
        'footer_text'       => $_POST['footer_text'] ?? '',
    ];

    if (!empty($_FILES['site_logo']['tmp_name'])) {
        $logoUpload = uploadFile($_FILES['site_logo'], LOGOS_PATH, ALLOWED_IMAGE_TYPES);
        if ($logoUpload['success']) {
            $oldLogo = $settingModel->get('site_logo');
            if ($oldLogo) {
                $oldPath = LOGOS_PATH . '/' . $oldLogo;
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $settingsData['site_logo'] = $logoUpload['filename'];
        } else {
            setFlash('error', 'Logo upload failed: ' . $logoUpload['error']);
            redirect(ADMIN_URL . '/settings.php');
        }
    }

    if (!empty($_FILES['resume_file']['tmp_name'])) {
        $resumeUpload = uploadFile($_FILES['resume_file'], UPLOADS_PATH, ALLOWED_DOC_TYPES);
        if ($resumeUpload['success']) {
            $oldResume = $settingModel->get('resume_file');
            if ($oldResume) {
                $oldPath = UPLOADS_PATH . '/' . $oldResume;
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $settingsData['resume_file'] = $resumeUpload['filename'];
        } else {
            setFlash('error', 'Resume upload failed: ' . $resumeUpload['error']);
            redirect(ADMIN_URL . '/settings.php');
        }
    }

    $settingModel->bulkUpdate($settingsData);

    $savedFields = count($settingsData);
    setFlash('success', "Settings saved successfully. {$savedFields} fields updated.");
    redirect(ADMIN_URL . '/settings.php');
}

// ── Below this line: normal page rendering (HTML) ──
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$settingModel = new Setting();
$settings = $settingModel->getAll();

function val($key, $settings) {
    return sanitize($settings[$key] ?? '');
}
?>

<div class="admin-content">
    <div class="admin-header">
        <div>
            <h1>Settings</h1>
            <p style="color:var(--text-secondary); font-size:14px; margin-top:4px;">Manage your site configuration</p>
        </div>
    </div>

    <div class="admin-body">

        <form method="POST" enctype="multipart/form-data" id="settingsForm" novalidate>
            <?= csrfField() ?>

            <ul class="nav nav-tabs" id="settingsTabs" style="border-bottom:1px solid var(--border-color); margin-bottom:24px; display:flex; gap:0; list-style:none; padding:0;">
                <li>
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-general" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-gear-fill"></i> General
                    </a>
                </li>
                <li>
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-hero" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-display"></i> Hero
                    </a>
                </li>
                <li>
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-about" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-person-fill"></i> About & Stats
                    </a>
                </li>
                <li>
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-contact" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-telephone-fill"></i> Contact
                    </a>
                </li>
                <li>
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-social" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-share-fill"></i> Social
                    </a>
                </li>
                <li>
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-email" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-envelope-at-fill"></i> Email
                    </a>
                </li>
                <li>
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-footer" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-layout-split"></i> Footer
                    </a>
                </li>
                <li>
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-preview" style="display:inline-block; padding:12px 20px; font-size:14px; font-weight:500; color:#777; border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s ease;">
                        <i class="bi bi-eye-fill"></i> Overview
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                <!-- TAB: General Settings -->
                <div class="tab-pane fade show active" id="tab-general">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>General Settings</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="form-group">
                                <label class="form-label">Site Name <span class="required">*</span></label>
                                <input type="text" name="site_name" class="form-control" value="<?= val('site_name', $settings) ?>" placeholder="My Portfolio" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Site Tagline</label>
                                <input type="text" name="site_tagline" class="form-control" value="<?= val('site_tagline', $settings) ?>" placeholder="Creative Video Editor">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Site Description</label>
                                <textarea name="site_description" class="form-control" rows="4" placeholder="A brief description of your portfolio site..."><?= val('site_description', $settings) ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Site Logo</label>
                                <?php if (!empty($settings['site_logo'])): ?>
                                <div style="margin-bottom:12px;">
                                    <img src="<?= UPLOADS_URL . '/logos/' . $settings['site_logo'] ?>" alt="Current Logo" style="max-height:60px; background:#0d0d0d; padding:8px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                                </div>
                                <?php endif; ?>
                                <div class="upload-area" id="logoUploadArea">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <p>Click or drag to upload logo</p>
                                    <p class="upload-hint">PNG, JPG, SVG, WebP — Max 10MB</p>
                                    <input type="file" name="site_logo" id="logoInput" accept="image/*">
                                </div>
                                <img id="logoPreview" src="" alt="" style="display:none; max-height:60px; margin-top:12px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-save-fill"></i> Save General Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: Hero Settings -->
                <div class="tab-pane fade" id="tab-hero">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Hero Section</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="form-group">
                                <label class="form-label">Hero Title</label>
                                <input type="text" name="hero_title" class="form-control" value="<?= val('hero_title', $settings) ?>" placeholder="Crafting Visual Stories">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Hero Subtitle</label>
                                <textarea name="hero_subtitle" class="form-control" rows="3" placeholder="Professional video editor with a passion for storytelling..."><?= val('hero_subtitle', $settings) ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">CTA Primary Text</label>
                                    <input type="text" name="cta_primary_text" class="form-control" value="<?= val('cta_primary_text', $settings) ?>" placeholder="View My Work">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">CTA Secondary Text</label>
                                    <input type="text" name="cta_secondary_text" class="form-control" value="<?= val('cta_secondary_text', $settings) ?>" placeholder="Get In Touch">
                                </div>
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-save-fill"></i> Save Hero Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: About & Stats -->
                <div class="tab-pane fade" id="tab-about">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>About & Statistics</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="form-group">
                                <label class="form-label">About Text</label>
                                <textarea name="about_text" class="form-control" rows="5" placeholder="Tell visitors about yourself..."><?= val('about_text', $settings) ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Experience Years</label>
                                    <input type="number" name="experience_years" class="form-control" value="<?= val('experience_years', $settings) ?>" min="0" placeholder="5">
                                    <p class="form-hint">Years of professional video editing experience</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Total Projects</label>
                                    <input type="number" name="total_projects" class="form-control" value="<?= val('total_projects', $settings) ?>" min="0" placeholder="100">
                                    <p class="form-hint">Approximate total completed projects</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Total Clients</label>
                                    <input type="number" name="total_clients" class="form-control" value="<?= val('total_clients', $settings) ?>" min="0" placeholder="50">
                                    <p class="form-hint">Number of clients served</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Videos Edited</label>
                                    <input type="number" name="videos_edited" class="form-control" value="<?= val('videos_edited', $settings) ?>" min="0" placeholder="200">
                                    <p class="form-hint">Total number of videos edited</p>
                                </div>
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-save-fill"></i> Save About Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: Contact Info -->
                <div class="tab-pane fade" id="tab-contact">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Contact Information</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="form-group">
                                <label class="form-label">Email Address <span class="required">*</span></label>
                                <input type="email" name="contact_email" class="form-control" value="<?= val('contact_email', $settings) ?>" placeholder="hello@example.com" required>
                                <p class="form-hint">Primary contact email displayed on the site</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="contact_phone" class="form-control" value="<?= val('contact_phone', $settings) ?>" placeholder="+1 (555) 123-4567">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Location</label>
                                <input type="text" name="contact_location" class="form-control" value="<?= val('contact_location', $settings) ?>" placeholder="Los Angeles, CA">
                                <p class="form-hint">City, State / Country</p>
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-save-fill"></i> Save Contact Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: Social Links -->
                <div class="tab-pane fade" id="tab-social">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Social Media Links</h3>
                        </div>
                        <div class="content-card-body">
                            <p style="font-size:13px; color:var(--text-secondary); margin-bottom:20px;">Enter full URLs including https://. Leave blank to hide a social icon on the public site.</p>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-youtube" style="color:#FF0000; font-size:16px;"></i>
                                    YouTube URL
                                </label>
                                <input type="url" name="social_youtube" class="form-control" value="<?= val('social_youtube', $settings) ?>" placeholder="https://youtube.com/@yourchannel">
                                <p class="form-hint">Your YouTube channel or profile URL</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-instagram" style="color:#E1306C; font-size:16px;"></i>
                                    Instagram URL
                                </label>
                                <input type="url" name="social_instagram" class="form-control" value="<?= val('social_instagram', $settings) ?>" placeholder="https://instagram.com/yourusername">
                                <p class="form-hint">Your Instagram profile URL</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-twitter-x" style="color:#fff; font-size:16px;"></i>
                                    Twitter / X URL
                                </label>
                                <input type="url" name="social_twitter" class="form-control" value="<?= val('social_twitter', $settings) ?>" placeholder="https://x.com/yourusername">
                                <p class="form-hint">Your X (formerly Twitter) profile URL</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-linkedin" style="color:#0A66C2; font-size:16px;"></i>
                                    LinkedIn URL
                                </label>
                                <input type="url" name="social_linkedin" class="form-control" value="<?= val('social_linkedin', $settings) ?>" placeholder="https://linkedin.com/in/yourusername">
                                <p class="form-hint">Your LinkedIn profile or company page URL</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-tiktok" style="font-size:16px;"></i>
                                    TikTok URL
                                </label>
                                <input type="url" name="social_tiktok" class="form-control" value="<?= val('social_tiktok', $settings) ?>" placeholder="https://tiktok.com/@yourusername">
                                <p class="form-hint">Your TikTok profile URL</p>
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-save-fill"></i> Save Social Links
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: Email Settings -->
                <div class="tab-pane fade" id="tab-email">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Email / SMTP Settings</h3>
                        </div>
                        <div class="content-card-body">
                            <p style="font-size:13px; color:var(--text-secondary); margin-bottom:20px;">Configure the SMTP server used for sending contact form notifications and other automated emails.</p>

                            <div style="background:rgba(66,133,244,0.05); border:1px solid rgba(66,133,244,0.15); border-radius:var(--radius-md); padding:14px 18px; margin-bottom:24px; font-size:13px; color:var(--info); display:flex; align-items:flex-start; gap:10px;">
                                <i class="bi bi-info-circle-fill" style="margin-top:2px; flex-shrink:0;"></i>
                                <div>
                                    Common SMTP providers: Gmail (smtp.gmail.com:587), Outlook (smtp.office365.com:587), SendGrid (smtp.sendgrid.net:587).
                                    For Gmail, you'll need an App Password instead of your regular password.
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">SMTP Host <span class="required">*</span></label>
                                    <input type="text" name="smtp_host" class="form-control" value="<?= val('smtp_host', $settings) ?>" placeholder="smtp.gmail.com">
                                    <p class="form-hint">The hostname of your mail server</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">SMTP Port <span class="required">*</span></label>
                                    <input type="text" name="smtp_port" class="form-control" value="<?= val('smtp_port', $settings) ?>" placeholder="587">
                                    <p class="form-hint">Common ports: 587 (TLS) or 465 (SSL)</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" name="smtp_username" class="form-control" value="<?= val('smtp_username', $settings) ?>" placeholder="your@email.com">
                                    <p class="form-hint">Usually your full email address</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" name="smtp_password" class="form-control" value="<?= val('smtp_password', $settings) ?>" placeholder="Enter SMTP password" autocomplete="off">
                                    <p class="form-hint">Use an App Password for Gmail and similar services</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">SMTP Encryption</label>
                                <select name="smtp_encryption" class="form-select" style="width:auto; min-width:200px;">
                                    <option value="tls" <?= val('smtp_encryption', $settings) === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                                    <option value="ssl" <?= val('smtp_encryption', $settings) === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= val('smtp_encryption', $settings) === 'none' ? 'selected' : '' ?>>None (Not Recommended)</option>
                                </select>
                                <p class="form-hint">TLS is recommended for secure connections. Use SSL only if required by your mail server.</p>
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-save-fill"></i> Save Email Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: Footer -->
                <div class="tab-pane fade" id="tab-footer">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Footer & Download Settings</h3>
                        </div>
                        <div class="content-card-body">
                            <div class="form-group">
                                <label class="form-label">Footer Text</label>
                                <textarea name="footer_text" class="form-control" rows="3" placeholder="© 2024 Your Name. All rights reserved."><?= val('footer_text', $settings) ?></textarea>
                                <p class="form-hint">Text displayed at the bottom of every page on the public site. HTML is allowed for links (e.g., &lt;a href="..."&gt;...&lt;/a&gt;).</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Resume / CV File</label>
                                <?php if (!empty($settings['resume_file'])): ?>
                                <div style="margin-bottom:12px; display:flex; align-items:center; gap:10px; padding:12px 16px; background:#0d0d0d; border:1px solid var(--border-color); border-radius:var(--radius-md);">
                                    <i class="bi bi-file-earmark-pdf" style="color:var(--danger); font-size:24px;"></i>
                                    <div>
                                        <a href="<?= UPLOADS_URL . '/' . $settings['resume_file'] ?>" target="_blank" style="color:var(--info); font-size:14px;">
                                            Current resume file
                                        </a>
                                        <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                            Uploaded previously
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <input type="file" name="resume_file" class="form-control" accept=".pdf" style="background:#0d0d0d;">
                                <p class="form-hint">Upload a PDF resume file for the download link on the public site. Max 10MB. The previous file will be replaced.</p>
                            </div>

                            <div style="background:rgba(255,193,7,0.05); border:1px solid rgba(255,193,7,0.15); border-radius:var(--radius-md); padding:14px 18px; margin-bottom:24px; font-size:13px; color:var(--warning); display:flex; align-items:flex-start; gap:10px;">
                                <i class="bi bi-exclamation-triangle-fill" style="margin-top:2px; flex-shrink:0;"></i>
                                <div>
                                    Uploading a new logo or resume file will replace the existing one. Make sure to save after uploading.
                                </div>
                            </div>

                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="bi bi-save-fill"></i> Save Footer Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: Preview -->
                <div class="tab-pane fade" id="tab-preview">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Settings Overview</h3>
                        </div>
                        <div class="content-card-body">
                            <p style="font-size:13px; color:var(--text-secondary); margin-bottom:20px;">Quick overview of all configured settings. Edit values in their respective tabs.</p>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Site Name</div>
                                    <div style="font-size:14px; color:#fff; font-weight:500;"><?= val('site_name', $settings) ?: '<span style="color:var(--text-muted);">Not set</span>' ?></div>
                                </div>
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Tagline</div>
                                    <div style="font-size:14px; color:#ccc;"><?= val('site_tagline', $settings) ?: '<span style="color:var(--text-muted);">Not set</span>' ?></div>
                                </div>
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Contact Email</div>
                                    <div style="font-size:14px; color:var(--info);"><?= val('contact_email', $settings) ?: '<span style="color:var(--text-muted);">Not set</span>' ?></div>
                                </div>
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Location</div>
                                    <div style="font-size:14px; color:#ccc;"><?= val('contact_location', $settings) ?: '<span style="color:var(--text-muted);">Not set</span>' ?></div>
                                </div>
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">SMTP Host</div>
                                    <div style="font-size:14px; color:#ccc;"><?= val('smtp_host', $settings) ?: '<span style="color:var(--text-muted);">Not configured</span>' ?></div>
                                </div>
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Site Logo</div>
                                    <div style="font-size:14px; color:#ccc;"><?= !empty($settings['site_logo']) ? '<span style="color:var(--primary);">Uploaded</span>' : '<span style="color:var(--text-muted);">Not uploaded</span>' ?></div>
                                </div>
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Resume File</div>
                                    <div style="font-size:14px; color:#ccc;"><?= !empty($settings['resume_file']) ? '<span style="color:var(--primary);">Uploaded</span>' : '<span style="color:var(--text-muted);">Not uploaded</span>' ?></div>
                                </div>
                                <div>
                                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Social Links</div>
                                    <div style="font-size:14px; color:#ccc;">
                                        <?php
                                        $socialCount = 0;
                                        foreach (['social_youtube', 'social_instagram', 'social_twitter', 'social_linkedin', 'social_tiktok'] as $sk) {
                                            if (!empty($settings[$sk])) $socialCount++;
                                        }
                                        ?>
                                        <?= $socialCount ?> of 5 configured
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="admin-footer">
        &copy; <?= date('Y') ?> Portfolio Admin Panel
    </div>
</div>

<style>
.nav-tabs .nav-link.active {
    color: var(--primary) !important;
    border-bottom-color: var(--primary) !important;
    background: transparent;
}
.nav-tabs .nav-link:hover:not(.active) {
    color: #ccc !important;
    background: rgba(255,255,255,0.03);
}
.nav-tabs {
    border: none !important;
}
.tab-content .tab-pane {
    display: none;
}
.tab-content .tab-pane.active.show {
    display: block;
    animation: fadeIn 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('#settingsTabs .nav-link');
    var tabPanes = document.querySelectorAll('.tab-content .tab-pane');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            tabs.forEach(function(t) { t.classList.remove('active'); });
            tabPanes.forEach(function(p) {
                p.classList.remove('active', 'show');
            });

            this.classList.add('active');
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.classList.add('active', 'show');
            }
        });
    });

    var settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            var activeTab = document.querySelector('.tab-pane.active.show');
            if (!activeTab) return;
            var requiredFields = activeTab.querySelectorAll('[required]');
            var firstInvalid = null;
            requiredFields.forEach(function(f) {
                f.style.boxShadow = '';
                if (!f.value.trim()) {
                    f.style.boxShadow = '0 0 0 2px #e53935';
                    if (!firstInvalid) firstInvalid = f;
                }
            });
            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.focus();
            }
        });
    }

    var logoInput = document.getElementById('logoInput');
    var logoPreview = document.getElementById('logoPreview');
    var logoArea = document.getElementById('logoUploadArea');

    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                    logoPreview.style.display = 'block';
                    logoArea.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
