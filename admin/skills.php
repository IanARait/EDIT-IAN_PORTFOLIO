<?php
$pageTitle = 'Skills';
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrf($_POST[CSRF_TOKEN_NAME])) {
        setFlash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/skills.php');
    }

    $action = $_POST['action'] ?? '';
    $skillModel = new Skill();

    if ($action === 'add_skill') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            setFlash('error', 'Skill name is required.');
            redirect(ADMIN_URL . '/skills.php');
        }
        $data = [
            'name'       => sanitize($name),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'percentage' => min(100, max(0, (int)($_POST['percentage'] ?? 0))),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $skillModel->create($data);
        setFlash('success', 'Skill created successfully!');
        redirect(ADMIN_URL . '/skills.php');
    }

    if ($action === 'edit_skill') {
        $id = (int)$_POST['skill_id'];
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            setFlash('error', 'Skill name is required.');
            redirect(ADMIN_URL . '/skills.php');
        }
        $data = [
            'name'       => sanitize($name),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'percentage' => min(100, max(0, (int)($_POST['percentage'] ?? 0))),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        $skillModel->update($id, $data);
        setFlash('success', 'Skill updated successfully!');
        redirect(ADMIN_URL . '/skills.php');
    }

    if ($action === 'delete_skill') {
        $id = (int)$_POST['skill_id'];
        $skillModel->delete($id);
        setFlash('success', 'Skill deleted successfully!');
        redirect(ADMIN_URL . '/skills.php');
    }

    redirect(ADMIN_URL . '/skills.php');
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$skillModel = new Skill();
$skills = $skillModel->getAll();
$catModel = new SkillCategory();
$skillCategories = $catModel->getAll();
?>

<div class="admin-content">
    <div class="admin-header">
        <div>
            <h1>Skills</h1>
            <p style="color:var(--text-secondary); font-size:14px; margin-top:4px;"><?= count($skills) ?> total skills</p>
        </div>
        <div class="admin-header-actions">
            <button class="btn btn-primary btn-sm" onclick="openSkillModal('add')">
                <i class="bi bi-plus-lg"></i> Add Skill
            </button>
        </div>
    </div>

    <div class="admin-body">
        <div class="content-card">
            <div class="content-card-body" style="padding:0; overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">Order</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Percentage</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($skills)): ?>
                        <?php foreach ($skills as $skill): ?>
                        <tr>
                            <td><?= (int)$skill['sort_order'] ?></td>
                            <td>
                                <div style="font-weight:500; font-size:14px;"><?= sanitize($skill['name']) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($skill['category_name'])): ?>
                                <span class="badge badge-primary"><?= sanitize($skill['category_name']) ?></span>
                                <?php else: ?>
                                <span style="color:var(--text-muted); font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="flex:1; max-width:200px; height:6px; background:var(--bg-elevated); border-radius:3px; overflow:hidden;">
                                        <div style="height:100%; width:<?= (int)$skill['percentage'] ?>%; background:var(--primary); border-radius:3px;"></div>
                                    </div>
                                    <span class="badge badge-primary" style="min-width:40px; text-align:center;"><?= (int)$skill['percentage'] ?>%</span>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <button class="action-btn edit" onclick="editSkill(<?= htmlspecialchars(json_encode($skill), ENT_QUOTES) ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button class="action-btn delete" onclick="confirmDeleteSkill(<?= $skill['id'] ?>, '<?= sanitize($skill['name']) ?>')" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:60px 20px; color:var(--text-secondary);">
                                <i class="bi bi-bar-chart" style="font-size:48px; display:block; margin-bottom:16px; color:var(--text-muted);"></i>
                                No skills yet. Click "Add Skill" to create your first skill.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Skill Modal -->
    <div class="modal fade" id="skillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:20px;">
                <div class="modal-header" style="border-bottom:1px solid var(--border-color); padding:20px 24px;">
                    <h3 class="modal-title" id="skillModalTitle" style="font-size:18px; font-weight:600;">Add Skill</h3>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="skillForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" id="skillFormAction" value="add_skill">
                    <input type="hidden" name="skill_id" id="skillFormId" value="">

                    <div class="modal-body" style="padding:24px;">
                        <div class="form-group">
                            <label class="form-label">Skill Name *</label>
                            <input type="text" class="form-control" name="name" id="skill_name" required placeholder="e.g., Premiere Pro, After Effects">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category_id" id="skill_category">
                                <option value="">None</option>
                                <?php foreach ($skillCategories as $sc): ?>
                                <option value="<?= (int)$sc['id'] ?>"><?= sanitize($sc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-hint">Optional: group this skill under a category</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Percentage (0-100)</label>
                            <input type="number" class="form-control" name="percentage" id="skill_percentage" value="0" min="0" max="100" style="max-width:120px;">
                            <p class="form-hint">Skill proficiency level</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" id="skill_sort_order" value="0" min="0" style="max-width:120px;">
                            <p class="form-hint">Lower numbers appear first</p>
                        </div>
                    </div>

                    <div class="modal-footer" style="border-top:1px solid var(--border-color); padding:16px 24px;">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Save Skill</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteSkillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:20px;">
                <div class="modal-header" style="border-bottom:1px solid var(--border-color);">
                    <h3 class="modal-title" style="font-size:16px;">Delete Skill</h3>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px; text-align:center;">
                    <i class="bi bi-exclamation-triangle" style="font-size:48px; color:#ff4444; margin-bottom:16px; display:block;"></i>
                    <p style="margin-bottom:4px; font-weight:600;">Are you sure you want to delete</p>
                    <p id="deleteSkillName" style="color:var(--primary); font-weight:600;"></p>
                    <p style="color:var(--text-secondary); font-size:13px; margin-top:8px;">This action cannot be undone.</p>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border-color); padding:16px 24px; justify-content:center;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="deleteSkillForm" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_skill">
                        <input type="hidden" name="skill_id" id="deleteSkillId" value="">
                        <button type="submit" class="btn btn-sm" style="background:#ff4444; color:#fff;"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openSkillModal(mode) {
    var form = document.getElementById('skillForm');
    form.reset();
    document.getElementById('skillFormAction').value = 'add_skill';
    document.getElementById('skillFormId').value = '';
    document.getElementById('skillModalTitle').textContent = 'Add Skill';
    new bootstrap.Modal(document.getElementById('skillModal')).show();
}

function editSkill(skill) {
    document.getElementById('skillFormAction').value = 'edit_skill';
    document.getElementById('skillFormId').value = skill.id;
    document.getElementById('skill_name').value = skill.name || '';
    document.getElementById('skill_category').value = skill.category_id || '';
    document.getElementById('skill_percentage').value = skill.percentage || 0;
    document.getElementById('skill_sort_order').value = skill.sort_order || 0;
    document.getElementById('skillModalTitle').textContent = 'Edit Skill';
    new bootstrap.Modal(document.getElementById('skillModal')).show();
}

function confirmDeleteSkill(id, name) {
    document.getElementById('deleteSkillId').value = id;
    document.getElementById('deleteSkillName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteSkillModal')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
