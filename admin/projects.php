<?php
$pageTitle = 'Projects';

// Include PHP deps only (no HTML) so POST + redirect() work
require_once __DIR__ . '/includes/bootstrap.php';

// Handle POST before any HTML output so redirect() works
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrf($_POST[CSRF_TOKEN_NAME])) {
        setFlash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/projects.php');
    }

    $action = $_POST['action'] ?? '';
    $projectModel = new Project();

    if ($action === 'add_project') {
        $data = [
            'title'         => sanitize($_POST['title']),
            'client'        => sanitize($_POST['client'] ?? ''),
            'category_id'   => (int)$_POST['category_id'],
            'description'   => sanitize($_POST['description'] ?? ''),
            'video_url'     => sanitize($_POST['video_url'] ?? ''),
            'github_url'    => trim($_POST['github_url'] ?? ''),
            'year'          => (int)($_POST['year'] ?? date('Y')),
            'duration'      => sanitize($_POST['duration'] ?? ''),
            'software_used' => sanitize($_POST['software_used'] ?? ''),
            'views'         => (int)($_POST['views'] ?? 0),
            'featured'      => isset($_POST['featured']) ? 1 : 0,
            'status'        => $_POST['status'] ?? 'published',
            'thumbnail_url' => trim($_POST['thumbnail_url'] ?? ''),
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        if (!empty($_FILES['thumbnail']['tmp_name'])) {
            $upload = uploadFile($_FILES['thumbnail'], THUMBNAILS_PATH, ALLOWED_IMAGE_TYPES);
            if ($upload['success']) {
                $data['thumbnail'] = $upload['filename'];
            }
        }

        if (!empty($_FILES['video_file']['tmp_name'])) {
            $upload = uploadFile($_FILES['video_file'], VIDEOS_PATH, ALLOWED_VIDEO_TYPES, MAX_VIDEO_SIZE);
            if ($upload['success']) {
                $data['video_file'] = $upload['filename'];
            }
        }

        $projectModel->create($data);
        setFlash('success', 'Project created successfully!');
        redirect(ADMIN_URL . '/projects.php');
    }

    if ($action === 'edit_project') {
        $id = (int)$_POST['project_id'];
        $data = [
            'title'         => sanitize($_POST['title']),
            'client'        => sanitize($_POST['client'] ?? ''),
            'category_id'   => (int)$_POST['category_id'],
            'description'   => sanitize($_POST['description'] ?? ''),
            'video_url'     => sanitize($_POST['video_url'] ?? ''),
            'github_url'    => trim($_POST['github_url'] ?? ''),
            'year'          => (int)($_POST['year'] ?? date('Y')),
            'duration'      => sanitize($_POST['duration'] ?? ''),
            'software_used' => sanitize($_POST['software_used'] ?? ''),
            'views'         => (int)($_POST['views'] ?? 0),
            'featured'      => isset($_POST['featured']) ? 1 : 0,
            'status'        => $_POST['status'] ?? 'published',
            'thumbnail_url' => trim($_POST['thumbnail_url'] ?? ''),
        ];

        if (!empty($_FILES['thumbnail']['tmp_name'])) {
            $upload = uploadFile($_FILES['thumbnail'], THUMBNAILS_PATH, ALLOWED_IMAGE_TYPES);
            if ($upload['success']) {
                $existing = $projectModel->getById($id);
                if ($existing && !empty($existing['thumbnail'])) {
                    $oldPath = THUMBNAILS_PATH . '/' . $existing['thumbnail'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $data['thumbnail'] = $upload['filename'];
            }
        }

        if (!empty($_FILES['video_file']['tmp_name'])) {
            $upload = uploadFile($_FILES['video_file'], VIDEOS_PATH, ALLOWED_VIDEO_TYPES, MAX_VIDEO_SIZE);
            if ($upload['success']) {
                $existing = $projectModel->getById($id);
                if ($existing && !empty($existing['video_file'])) {
                    $oldPath = VIDEOS_PATH . '/' . $existing['video_file'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $data['video_file'] = $upload['filename'];
            }
        }

        $projectModel->update($id, $data);
        setFlash('success', 'Project updated successfully!');
        redirect(ADMIN_URL . '/projects.php');
    }

    if ($action === 'delete_project') {
        $id = (int)$_POST['project_id'];
        $projectModel->delete($id);
        setFlash('success', 'Project deleted successfully!');
        redirect(ADMIN_URL . '/projects.php');
    }

    // Unknown action — just redirect back
    redirect(ADMIN_URL . '/projects.php');
}

// ── Below this line: normal page rendering (HTML) ──
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$projectModel = new Project();
$filters = [];
if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
if (!empty($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
$filters['page'] = max(1, (int)($_GET['page'] ?? 1));

$projects = $projectModel->getAllAdmin($filters);
$categories = $projectModel->getCategories();
$stats = $projectModel->getStats();
?>

<div class="admin-content">
    <div class="admin-header">
        <div>
            <h1>Projects</h1>
            <p style="color:var(--text-secondary); font-size:14px; margin-top:4px;"><?= $projects['total'] ?> total projects</p>
        </div>
        <div class="admin-header-actions">
            <button class="btn btn-primary btn-sm" onclick="openProjectModal('add')">
                <i class="bi bi-plus-lg"></i> Add Project
            </button>
        </div>
    </div>

    <div class="admin-body">
        <!-- Filters -->
        <div class="filter-bar">
            <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; width:100%;">
                <div class="search-bar" style="flex:1; min-width:200px;">
                    <input type="text" class="form-control" name="search" placeholder="Search projects..." value="<?= sanitize($_GET['search'] ?? '') ?>">
                </div>
                <select name="category_id" class="form-control" style="width:auto; min-width:160px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (int)($_GET['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="form-control" style="width:auto; min-width:140px;">
                    <option value="">All Status</option>
                    <option value="published" <?= ($_GET['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= ($_GET['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="archived" <?= ($_GET['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-search"></i> Filter</button>
                <a href="<?= ADMIN_URL ?>/projects.php" class="btn btn-ghost btn-sm">Clear</a>
            </form>
        </div>

        <!-- Data Table -->
        <div class="content-card">
            <div class="content-card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                            <th>Project</th>
                            <th>Category</th>
                            <th>Year</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($projects['data'])): ?>
                        <?php foreach ($projects['data'] as $project):
                            $thumbnail_display = '';
                            if (!empty($project['thumbnail_url'])) {
                                $thumbnail_display = driveImageUrl($project['thumbnail_url']);
                            } elseif (!empty($project['thumbnail'])) {
                                $thumbnail_display = UPLOADS_URL . '/thumbnails/' . $project['thumbnail'];
                            }
                        ?>
                        <tr>
                            <td><input type="checkbox" class="form-check-input row-select" value="<?= $project['id'] ?>"></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:48px; height:48px; border-radius:8px; overflow:hidden; flex-shrink:0; background:var(--bg-elevated);">
                                        <?php if ($thumbnail_display): ?>
                                        <img src="<?= htmlspecialchars($thumbnail_display) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--text-muted);"><i class="bi bi-film"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:500; font-size:14px;"><?= sanitize($project['title']) ?></div>
                                        <div style="font-size:12px; color:var(--text-secondary);"><?= sanitize($project['client'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-primary"><?= sanitize($project['category_name'] ?? 'N/A') ?></span></td>
                            <td><?= sanitize($project['year'] ?? '') ?></td>
                            <td>
                                <button class="action-btn" onclick="toggleFeatured(<?= $project['id'] ?>, this)" title="Toggle Featured">
                                    <?php if ($project['featured']): ?>
                                    <i class="bi bi-star-fill" style="color:#FFD700;"></i>
                                    <?php else: ?>
                                    <i class="bi bi-star" style="color:var(--text-muted);"></i>
                                    <?php endif; ?>
                                </button>
                            </td>
                            <td><span class="status-badge status-<?= $project['status'] ?>"><?= ucfirst($project['status']) ?></span></td>
                            <td><?= formatNumber($project['views']) ?></td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <button class="action-btn view" onclick="viewProject(<?= htmlspecialchars(json_encode($project + ['thumbnail_display' => $thumbnail_display]), ENT_QUOTES) ?>)" title="View"><i class="bi bi-eye"></i></button>
                                    <button class="action-btn edit" onclick="editProject(<?= htmlspecialchars(json_encode($project + ['thumbnail_display' => $thumbnail_display]), ENT_QUOTES) ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button class="action-btn delete" onclick="confirmDeleteProject(<?= $project['id'] ?>, '<?= sanitize($project['title']) ?>')" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:60px 20px; color:var(--text-secondary);">
                                <i class="bi bi-collection-play" style="font-size:48px; display:block; margin-bottom:16px; color:var(--text-muted);"></i>
                                No projects found. Click "Add Project" to create your first project.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($projects['total_pages'] > 1): ?>
        <div style="display:flex; justify-content:center; margin-top:24px;">
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $projects['total_pages']; $i++): ?>
                    <li class="page-item <?= $i === $projects['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= sanitize($_GET['search'] ?? '') ?>&category_id=<?= (int)($_GET['category_id'] ?? '') ?>&status=<?= sanitize($_GET['status'] ?? '') ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Project Modal -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:20px;">
            <div class="modal-header" style="border-bottom:1px solid var(--border); padding:20px 24px;">
                <h3 class="modal-title" id="projectModalTitle" style="font-size:18px; font-weight:600;">Add Project</h3>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="projectForm">
                <?= csrfField() ?>
                <input type="hidden" name="action" id="projectFormAction" value="add_project">
                <input type="hidden" name="project_id" id="projectFormId" value="">
                
                <div class="modal-body" style="padding:24px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Project Title *</label>
                            <input type="text" class="form-control" name="title" id="pf_title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" name="client" id="pf_client">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category_id" id="pf_category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Video URL (YouTube/Vimeo/Google Drive)</label>
                            <input type="url" class="form-control" name="video_url" id="pf_video_url" placeholder="https://youtube.com/watch?v=...">
                            <p class="form-hint">Paste a YouTube, Vimeo, or Google Drive link. Or upload a file below.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">GitHub / Website URL</label>
                        <input type="url" class="form-control" name="github_url" id="pf_github_url" placeholder="https://github.com/username/repo">
                        <p class="form-hint">Optional. For the Website category — viewers can open the live site from here.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload Video File</label>
                        <div style="display:flex; gap:16px; align-items:flex-start;">
                            <div style="flex:1;">
                                <input type="file" class="form-control" name="video_file" id="pf_video_file" accept="video/mp4,video/webm,video/ogg" onchange="previewVideoFile(this)">
                                <p class="form-hint">MP4, WebM, OGG — Max 100MB</p>
                            </div>
                            <div id="videoFilePreview" style="width:200px; border-radius:8px; border:1px solid var(--border); overflow:hidden; display:none; flex-shrink:0;">
                                <video src="" style="width:100%; display:block;" muted></video>
                            </div>
                        </div>
                        <div id="existingVideoFile" style="margin-top:8px; display:none;">
                            <p style="font-size:13px; color:var(--text-secondary);">Current: <a id="existingVideoLink" href="" target="_blank" style="color:var(--primary);"></a></p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="pf_description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Year</label>
                            <input type="number" class="form-control" name="year" id="pf_year" value="<?= date('Y') ?>" min="2000" max="<?= date('Y') + 1 ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="duration" id="pf_duration" placeholder="e.g., 60 sec, 12 min">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Software Used</label>
                            <input type="text" class="form-control" name="software_used" id="pf_software" placeholder="e.g., Premiere Pro, After Effects">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Views</label>
                            <input type="number" class="form-control" name="views" id="pf_views" value="0" min="0">
                            <p class="form-hint">Number of views for this project</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thumbnail</label>
                        <div style="display:flex; gap:16px; align-items:flex-start;">
                            <div style="flex:1;">
                                <input type="text" class="form-control" name="thumbnail_url" id="pf_thumbnail_url" placeholder="Paste a Google Drive photo link">
                                <p class="form-hint">Paste a Google Drive photo link, or upload a thumbnail file below.</p>
                                <input type="file" class="form-control" name="thumbnail" id="pf_thumbnail" accept="image/*" onchange="previewThumbnail(this)" style="margin-top:8px;">
                            </div>
                            <div id="thumbnailPreview" style="width:120px; height:68px; border-radius:8px; border:1px solid var(--border); overflow:hidden; display:none; flex-shrink:0;">
                                <img src="" alt="Preview" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="pf_status">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Featured</label>
                            <div style="padding-top:8px;">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="featured" id="pf_featured" role="switch">
                                    <label class="form-check-label" for="pf_featured">Mark as Featured</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer" style="border-top:1px solid var(--border); padding:16px 24px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:20px;">
            <div class="modal-header" style="border-bottom:1px solid var(--border);">
                <h3 class="modal-title" style="font-size:16px;">Delete Project</h3>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px; text-align:center;">
                <i class="bi bi-exclamation-triangle" style="font-size:48px; color:#ff4444; margin-bottom:16px; display:block;"></i>
                <p style="margin-bottom:4px; font-weight:600;">Are you sure you want to delete</p>
                <p id="deleteProjectName" style="color:var(--primary); font-weight:600;"></p>
                <p style="color:var(--text-secondary); font-size:13px; margin-top:8px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border); padding:16px 24px; justify-content:center;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display:inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete_project">
                    <input type="hidden" name="project_id" id="deleteProjectId" value="">
                    <button type="submit" class="btn btn-sm" style="background:#ff4444; color:#fff;"><i class="bi bi-trash"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Project Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background:var(--bg-surface); border:1px solid var(--border); border-radius:20px;">
            <div class="modal-header" style="border-bottom:1px solid var(--border); padding:20px 24px;">
                <h3 class="modal-title" style="font-size:18px; font-weight:600;">Project Details</h3>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div id="viewVideoContainer" style="width:100%; aspect-ratio:16/9; border-radius:12px; overflow:hidden; margin-bottom:24px; background:#000; display:none;">
                    <iframe id="viewIframe" src="" style="width:100%; height:100%; border:none;" allowfullscreen></iframe>
                    <video id="viewVideo" controls style="width:100%; height:100%; display:none;"></video>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Title</div>
                        <div id="viewTitle" style="font-weight:600;"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Client</div>
                        <div id="viewClient"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Category</div>
                        <div id="viewCategory"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Year</div>
                        <div id="viewYear"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Duration</div>
                        <div id="viewDuration"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Software</div>
                        <div id="viewSoftware"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Views</div>
                        <div id="viewViews"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Status</div>
                        <div id="viewStatus"></div>
                    </div>
                </div>
                <div>
                    <div style="font-size:12px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Description</div>
                    <p id="viewDescription" style="color:var(--text-secondary); line-height:1.8;"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openProjectModal(mode) {
    const modal = document.getElementById('projectModal');
    const form = document.getElementById('projectForm');
    const title = document.getElementById('projectModalTitle');
    
    form.reset();
    document.getElementById('projectFormAction').value = 'add_project';
    document.getElementById('projectFormId').value = '';
    document.getElementById('thumbnailPreview').style.display = 'none';
    document.getElementById('videoFilePreview').style.display = 'none';
    document.getElementById('existingVideoFile').style.display = 'none';
    title.textContent = 'Add Project';
    
    new bootstrap.Modal(modal).show();
}

function editProject(project) {
    const modal = document.getElementById('projectModal');
    const title = document.getElementById('projectModalTitle');
    
    document.getElementById('projectFormAction').value = 'edit_project';
    document.getElementById('projectFormId').value = project.id;
    document.getElementById('pf_title').value = project.title || '';
    document.getElementById('pf_client').value = project.client || '';
    document.getElementById('pf_category').value = project.category_id || '';
    document.getElementById('pf_video_url').value = project.video_url || '';
    document.getElementById('pf_github_url').value = project.github_url || '';
    document.getElementById('pf_description').value = project.description || '';
    document.getElementById('pf_year').value = project.year || new Date().getFullYear();
    document.getElementById('pf_duration').value = project.duration || '';
    document.getElementById('pf_software').value = project.software_used || '';
    document.getElementById('pf_views').value = project.views || 0;
    document.getElementById('pf_status').value = project.status || 'published';
    document.getElementById('pf_featured').checked = !!parseInt(project.featured);
    document.getElementById('pf_thumbnail_url').value = project.thumbnail_url || '';
    title.textContent = 'Edit Project';
    
    if (project.thumbnail_display) {
        const preview = document.getElementById('thumbnailPreview');
        preview.querySelector('img').src = project.thumbnail_display;
        preview.style.display = 'block';
    } else {
        document.getElementById('thumbnailPreview').style.display = 'none';
    }

    var vfilePreview = document.getElementById('videoFilePreview');
    var existVfile = document.getElementById('existingVideoFile');
    var existLink = document.getElementById('existingVideoLink');
    vfilePreview.style.display = 'none';
    if (project.video_file) {
        existLink.textContent = project.video_file;
        existLink.href = '<?= UPLOADS_URL ?>/videos/' + project.video_file;
        existVfile.style.display = 'block';
    } else {
        existVfile.style.display = 'none';
    }
    
    new bootstrap.Modal(modal).show();
}

function viewProject(project) {
    document.getElementById('viewTitle').textContent = project.title;
    document.getElementById('viewClient').textContent = project.client || 'N/A';
    document.getElementById('viewCategory').textContent = project.category_name || 'N/A';
    document.getElementById('viewYear').textContent = project.year || 'N/A';
    document.getElementById('viewDuration').textContent = project.duration || 'N/A';
    document.getElementById('viewSoftware').textContent = project.software_used || 'N/A';
    document.getElementById('viewViews').textContent = project.views || '0';
    document.getElementById('viewStatus').textContent = project.status || 'N/A';
    document.getElementById('viewDescription').textContent = project.description || 'No description provided.';
    
    const videoContainer = document.getElementById('viewVideoContainer');
    const iframe = document.getElementById('viewIframe');
    const videoEl = document.getElementById('viewVideo');
    videoContainer.style.display = 'none';
    iframe.src = '';
    if (videoEl) { videoEl.src = ''; videoEl.style.display = 'none'; }

    if (project.video_url && !project.video_file) {
        var embed = getEmbedUrl(project.video_url);
        if (embed) {
            iframe.src = embed;
            iframe.style.display = 'block';
            videoContainer.style.display = 'block';
        }
    } else if (project.video_file) {
        videoEl.src = '<?= UPLOADS_URL ?>/videos/' + project.video_file;
        videoEl.style.display = 'block';
        videoContainer.style.display = 'block';
        iframe.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function getEmbedUrl(url) {
    if (url.includes('youtube.com/watch?v=')) {
        const id = url.split('v=')[1]?.split('&')[0];
        return 'https://www.youtube.com/embed/' + id;
    }
    if (url.includes('youtu.be/')) {
        return 'https://www.youtube.com/embed/' + url.split('youtu.be/')[1];
    }
    if (url.includes('vimeo.com/')) {
        const id = url.split('vimeo.com/')[1];
        return 'https://player.vimeo.com/video/' + id;
    }
    if (url.includes('drive.google.com/file/d/')) {
        const id = url.split('drive.google.com/file/d/')[1].split('/')[0];
        return 'https://drive.google.com/file/d/' + id + '/preview';
    }
    if (url.includes('drive.google.com/open')) {
        const id = new URL(url).searchParams.get('id');
        return id ? 'https://drive.google.com/file/d/' + id + '/preview' : null;
    }
    return url;
}

function confirmDeleteProject(id, name) {
    document.getElementById('deleteProjectId').value = id;
    document.getElementById('deleteProjectName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function previewThumbnail(input) {
    const preview = document.getElementById('thumbnailPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewVideoFile(input) {
    const preview = document.getElementById('videoFilePreview');
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var url = URL.createObjectURL(file);
        var vid = preview.querySelector('video');
        vid.src = url;
        preview.style.display = 'block';
    }
}

function toggleFeatured(id, btn) {
    fetch('<?= ADMIN_URL ?>/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'action=toggle_featured&project_id=' + id + '&<?= CSRF_TOKEN_NAME ?>=<?= csrfToken() ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const icon = btn.querySelector('i');
            if (data.featured) {
                icon.className = 'bi bi-star-fill';
                icon.style.color = '#FFD700';
            } else {
                icon.className = 'bi bi-star';
                icon.style.color = 'var(--text-muted)';
            }
        }
    });
}

document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => cb.checked = this.checked);
});

document.getElementById('projectForm')?.addEventListener('submit', function() {
    console.log('Project form submitting. Action:', document.getElementById('projectFormAction').value);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
