<?php
$pageTitle = 'Categories';
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrf($_POST[CSRF_TOKEN_NAME])) {
        setFlash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/categories.php');
    }

    $action = $_POST['action'] ?? '';
    $catModel = new Category();

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            setFlash('error', 'Category name is required.');
            redirect(ADMIN_URL . '/categories.php');
        }
        $data = [
            'name'        => sanitize($name),
            'slug'        => Category::slugify($name),
            'description' => sanitize($_POST['description'] ?? ''),
            'sort_order'  => (int)($_POST['sort_order'] ?? 0),
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        try {
            $catModel->create($data);
            setFlash('success', 'Category created successfully!');
            redirect(ADMIN_URL . '/categories.php');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                setFlash('error', 'A category with the name "' . sanitize($name) . '" already exists.');
            } else {
                setFlash('error', 'Database error: ' . $e->getMessage());
            }
            redirect(ADMIN_URL . '/categories.php');
        }
    }

    if ($action === 'edit_category') {
        $id = (int)$_POST['category_id'];
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            setFlash('error', 'Category name is required.');
            redirect(ADMIN_URL . '/categories.php');
        }
        $data = [
            'name'        => sanitize($name),
            'slug'        => Category::slugify($name),
            'description' => sanitize($_POST['description'] ?? ''),
            'sort_order'  => (int)($_POST['sort_order'] ?? 0),
        ];
        try {
            $catModel->update($id, $data);
            setFlash('success', 'Category updated successfully!');
            redirect(ADMIN_URL . '/categories.php');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                setFlash('error', 'A category with the name "' . sanitize($name) . '" already exists.');
            } else {
                setFlash('error', 'Database error: ' . $e->getMessage());
            }
            redirect(ADMIN_URL . '/categories.php');
        }
    }

    if ($action === 'delete_category') {
        $id = (int)$_POST['category_id'];
        $result = $catModel->delete($id);
        if ($result === -1) {
            setFlash('error', 'Cannot delete category — it still has projects assigned to it.');
        } else {
            setFlash('success', 'Category deleted successfully!');
        }
        redirect(ADMIN_URL . '/categories.php');
    }

    redirect(ADMIN_URL . '/categories.php');
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$catModel = new Category();
$categories = $catModel->getAll();
?>

<div class="admin-content">
    <div class="admin-header">
        <div>
            <h1>Categories</h1>
            <p style="color:var(--text-secondary); font-size:14px; margin-top:4px;"><?= count($categories) ?> total categories</p>
        </div>
        <div class="admin-header-actions">
            <button class="btn btn-primary btn-sm" onclick="openCategoryModal('add')">
                <i class="bi bi-plus-lg"></i> Add Category
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
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Projects</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= (int)$cat['sort_order'] ?></td>
                            <td>
                                <div style="font-weight:500; font-size:14px;"><?= sanitize($cat['name']) ?></div>
                            </td>
                            <td><code style="font-size:12px; color:var(--text-secondary); background:var(--bg-elevated); padding:2px 8px; border-radius:4px;"><?= sanitize($cat['slug']) ?></code></td>
                            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text-secondary); font-size:13px;"><?= sanitize($cat['description'] ?? '') ?></td>
                            <td><span class="badge badge-primary"><?= (int)$cat['project_count'] ?></span></td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <button class="action-btn edit" onclick="editCategory(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES) ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button class="action-btn delete" onclick="confirmDeleteCategory(<?= $cat['id'] ?>, '<?= sanitize($cat['name']) ?>', <?= (int)$cat['project_count'] ?>)" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:60px 20px; color:var(--text-secondary);">
                                <i class="bi bi-tags" style="font-size:48px; display:block; margin-bottom:16px; color:var(--text-muted);"></i>
                                No categories yet. Click "Add Category" to create your first category.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:20px;">
                <div class="modal-header" style="border-bottom:1px solid var(--border-color); padding:20px 24px;">
                    <h3 class="modal-title" id="categoryModalTitle" style="font-size:18px; font-weight:600;">Add Category</h3>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="categoryForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" id="categoryFormAction" value="add_category">
                    <input type="hidden" name="category_id" id="categoryFormId" value="">

                    <div class="modal-body" style="padding:24px;">
                        <div class="form-group">
                            <label class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="name" id="cat_name" required placeholder="e.g., VSL, UGC, Commercial">
                            <p class="form-hint">Slug will be auto-generated from the name</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="cat_description" rows="3" placeholder="Brief description of this category..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" id="cat_sort_order" value="0" min="0" style="max-width:120px;">
                            <p class="form-hint">Lower numbers appear first</p>
                        </div>
                    </div>

                    <div class="modal-footer" style="border-top:1px solid var(--border-color); padding:16px 24px;">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:20px;">
                <div class="modal-header" style="border-bottom:1px solid var(--border-color);">
                    <h3 class="modal-title" style="font-size:16px;">Delete Category</h3>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px; text-align:center;">
                    <i class="bi bi-exclamation-triangle" style="font-size:48px; color:#ff4444; margin-bottom:16px; display:block;"></i>
                    <p style="margin-bottom:4px; font-weight:600;">Are you sure you want to delete</p>
                    <p id="deleteCategoryName" style="color:var(--primary); font-weight:600;"></p>
                    <p id="deleteCategoryWarning" style="color:#ff4444; font-size:13px; margin-top:8px; display:none;">This category has projects — move them first!</p>
                    <p style="color:var(--text-secondary); font-size:13px; margin-top:8px;">This action cannot be undone.</p>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border-color); padding:16px 24px; justify-content:center;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="deleteCategoryForm" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" id="deleteCategoryId" value="">
                        <button type="submit" class="btn btn-sm" style="background:#ff4444; color:#fff;"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openCategoryModal(mode) {
    var form = document.getElementById('categoryForm');
    form.reset();
    document.getElementById('categoryFormAction').value = 'add_category';
    document.getElementById('categoryFormId').value = '';
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function editCategory(cat) {
    document.getElementById('categoryFormAction').value = 'edit_category';
    document.getElementById('categoryFormId').value = cat.id;
    document.getElementById('cat_name').value = cat.name || '';
    document.getElementById('cat_description').value = cat.description || '';
    document.getElementById('cat_sort_order').value = cat.sort_order || 0;
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function confirmDeleteCategory(id, name, projectCount) {
    document.getElementById('deleteCategoryId').value = id;
    document.getElementById('deleteCategoryName').textContent = name;
    var warn = document.getElementById('deleteCategoryWarning');
    var btn = document.querySelector('#deleteCategoryForm button[type="submit"]');
    if (projectCount > 0) {
        warn.style.display = 'block';
        btn.disabled = true;
        btn.style.opacity = '0.4';
    } else {
        warn.style.display = 'none';
        btn.disabled = false;
        btn.style.opacity = '1';
    }
    new bootstrap.Modal(document.getElementById('deleteCategoryModal')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
