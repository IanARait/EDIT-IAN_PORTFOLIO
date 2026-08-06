<?php
$pageTitle = 'Messages';

// Include PHP deps only (no HTML) so POST + redirect() work
require_once __DIR__ . '/includes/bootstrap.php';

// Handle POST before any HTML output so redirect() works
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrf($_POST[CSRF_TOKEN_NAME])) {
        setFlash('error', 'Invalid CSRF token.');
        redirect(ADMIN_URL . '/messages.php');
    }

    $messageModel = new Message();

    switch ($action) {
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $messageModel->delete($id);
                setFlash('success', 'Message deleted successfully.');
            }
            break;

        case 'mark_read':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $messageModel->markAsRead($id);
                setFlash('success', 'Message marked as read.');
            }
            break;

        case 'mark_unread':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $messageModel->markAsUnread($id);
                setFlash('success', 'Message marked as unread.');
            }
            break;

        case 'delete_read':
            $messageModel->deleteRead();
            setFlash('success', 'All read messages deleted.');
            break;

        case 'bulk_mark_read':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $messageModel->markAsRead((int)$id);
                }
                setFlash('success', count($ids) . ' message(s) marked as read.');
            }
            break;

        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $messageModel->delete((int)$id);
                }
                setFlash('success', count($ids) . ' message(s) deleted.');
            }
            break;
    }

    redirect(ADMIN_URL . '/messages.php');
}

// ── Below this line: normal page rendering (HTML) ──
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$messageModel = new Message();

$search = $_GET['search'] ?? '';
$isRead = $_GET['is_read'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

$messages = $messageModel->getAll([
    'search' => $search,
    'is_read' => $isRead,
    'page' => $page,
]);

$unreadCount = $messageModel->getUnreadCount();
$totalCount = $messageModel->getTotalCount();
$allReadMessages = $messageModel->getAll(['is_read' => 1, 'page' => 1]);
$readCount = $allReadMessages['total'] ?? 0;
?>

<style>
    .summary-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
    }
    .summary-stat {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 140px;
    }
    .summary-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .summary-stat-icon.total {
        background-color: rgba(66, 133, 244, 0.1);
        color: var(--info);
    }
    .summary-stat-icon.unread {
        background-color: rgba(255, 68, 68, 0.1);
        color: var(--danger);
    }
    .summary-stat-icon.read {
        background-color: rgba(0, 230, 118, 0.1);
        color: var(--primary);
    }
    .summary-stat-value {
        font-size: 22px;
        font-weight: 700;
        font-family: var(--font-heading);
        line-height: 1;
    }
    .summary-stat-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .bulk-actions-bar {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        background: rgba(0, 230, 118, 0.05);
        border: 1px solid rgba(0, 230, 118, 0.15);
        border-radius: var(--radius-md);
        margin-bottom: 16px;
        font-size: 14px;
        color: #ccc;
    }
    .bulk-actions-bar .bulk-count {
        font-weight: 700;
        color: var(--primary);
    }
    .inline-confirm {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 8px 12px;
        z-index: 10;
        white-space: nowrap;
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    }
    .inline-confirm.active {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .inline-confirm-btn {
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background: transparent;
        color: #ccc;
        font-size: 12px;
        cursor: pointer;
        transition: var(--transition);
    }
    .inline-confirm-btn:hover {
        background: rgba(255,255,255,0.05);
        color: #fff;
    }
    .inline-confirm-btn.confirm-yes {
        background: rgba(255, 68, 68, 0.15);
        color: var(--danger);
        border-color: rgba(255, 68, 68, 0.3);
    }
    .inline-confirm-btn.confirm-yes:hover {
        background: var(--danger);
        color: #fff;
    }
    .inline-confirm-btn.confirm-no {
        background: transparent;
    }
    .row-actions-wrapper {
        position: relative;
        display: inline-flex;
    }
</style>

<div class="admin-content">
    <div class="admin-header">
        <div class="d-flex align-center" style="gap:12px;">
            <h1>Messages</h1>
            <?php if ($unreadCount > 0): ?>
            <span class="status-badge status-unread"><?= $unreadCount ?> unread</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-body">

        <!-- Summary Stats -->
        <div class="summary-stats">
            <div class="summary-stat">
                <div class="summary-stat-icon total">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div>
                    <div class="summary-stat-value"><?= $totalCount ?></div>
                    <div class="summary-stat-label">Total Messages</div>
                </div>
            </div>
            <div class="summary-stat">
                <div class="summary-stat-icon unread">
                    <i class="bi bi-envelope-exclamation-fill"></i>
                </div>
                <div>
                    <div class="summary-stat-value"><?= $unreadCount ?></div>
                    <div class="summary-stat-label">Unread</div>
                </div>
            </div>
            <div class="summary-stat">
                <div class="summary-stat-icon read">
                    <i class="bi bi-envelope-check-fill"></i>
                </div>
                <div>
                    <div class="summary-stat-value"><?= $readCount ?></div>
                    <div class="summary-stat-label">Read</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" class="filter-bar" id="filterForm">
            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="search-input" placeholder="Search by name, email, or message..." value="<?= sanitize($search) ?>">
            </div>
            <select name="is_read" class="form-select" onchange="document.getElementById('filterForm').submit();" style="width:auto; min-width:160px;">
                <option value="">All Status</option>
                <option value="0" <?= $isRead === '0' ? 'selected' : '' ?>>Unread</option>
                <option value="1" <?= $isRead === '1' ? 'selected' : '' ?>>Read</option>
            </select>
            <button type="submit" class="admin-btn admin-btn-secondary">
                <i class="bi bi-search"></i> Filter
            </button>
            <?php if ($search || $isRead !== ''): ?>
            <a href="<?= ADMIN_URL ?>/messages.php" class="admin-btn admin-btn-ghost" style="color:var(--text-secondary);">
                <i class="bi bi-x-circle"></i> Clear Filters
            </a>
            <?php endif; ?>
            <?php if ($readCount > 0): ?>
            <form method="POST" style="display:inline; margin-left:auto;" onsubmit="return confirm('Delete all read messages? This cannot be undone.');">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="delete_read">
                <button type="submit" class="admin-btn admin-btn-danger">
                    <i class="bi bi-trash3-fill"></i> Delete All Read
                </button>
            </form>
            <?php endif; ?>
        </form>

        <!-- Bulk Actions Bar -->
        <div class="bulk-actions-bar" id="bulkActionsBar">
            <span><span class="bulk-count" id="bulkCount">0</span> message(s) selected</span>
            <form method="POST" style="display:inline;" id="bulkMarkReadForm">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="bulk_mark_read">
                <input type="hidden" name="ids" id="bulkMarkReadIds" value="">
                <button type="submit" class="admin-btn admin-btn-secondary" style="padding:6px 14px; font-size:13px;">
                    <i class="bi bi-envelope-check"></i> Mark Read
                </button>
            </form>
            <form method="POST" style="display:inline;" id="bulkDeleteForm" onsubmit="return confirm('Delete selected messages?');">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="bulk_delete">
                <input type="hidden" name="ids" id="bulkDeleteIds" value="">
                <button type="submit" class="admin-btn admin-btn-danger" style="padding:6px 14px; font-size:13px;">
                    <i class="bi bi-trash3"></i> Delete Selected
                </button>
            </form>
        </div>

        <!-- Messages Table -->
        <div class="content-card">
            <div class="content-card-body" style="padding:0;">
                <?php if (!empty($messages['data'])): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" id="selectAll" class="row-checkbox">
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Budget</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages['data'] as $msg): ?>
                        <tr data-row-id="<?= $msg['id'] ?>" style="<?= !$msg['is_read'] ? 'background:rgba(0,230,118,0.02);' : '' ?>">
                            <td class="checkbox-cell">
                                <input type="checkbox" class="row-checkbox" value="<?= $msg['id'] ?>">
                            </td>
                            <td>
                                <span style="font-weight:<?= !$msg['is_read'] ? '700' : '500' ?>; color:#fff;">
                                    <?= sanitize($msg['name']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="mailto:<?= sanitize($msg['email']) ?>" style="color:var(--info); font-size:13px;">
                                    <?= sanitize($msg['email']) ?>
                                </a>
                            </td>
                            <td style="font-size:13px;">
                                <?= sanitize($msg['company'] ?? '—') ?>
                            </td>
                            <td style="font-size:13px;">
                                <?= sanitize($msg['budget'] ?? '—') ?>
                            </td>
                            <td>
                                <span style="color:var(--text-secondary); font-size:13px;">
                                    <?= sanitize(substr($msg['message'], 0, 50)) ?><?= strlen($msg['message']) > 50 ? '...' : '' ?>
                                </span>
                            </td>
                            <td>
                                <span style="color:var(--text-muted); white-space:nowrap; font-size:13px;">
                                    <?= timeAgo($msg['created_at']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($msg['is_read']): ?>
                                <span class="status-badge status-read">Read</span>
                                <?php else: ?>
                                <span class="status-badge status-unread">Unread</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row-actions-wrapper">
                                    <div class="d-flex align-center">
                                        <?php if ($msg['is_read']): ?>
                                        <form method="POST" style="display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="mark_unread">
                                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                            <button type="submit" class="action-btn" title="Mark as Unread">
                                                <i class="bi bi-envelope-fill"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <form method="POST" style="display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                            <button type="submit" class="action-btn" title="Mark as Read">
                                                <i class="bi bi-envelope-open-fill"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>

                                        <button class="action-btn view view-message-btn"
                                                data-id="<?= $msg['id'] ?>"
                                                data-name="<?= sanitize($msg['name']) ?>"
                                                data-email="<?= sanitize($msg['email']) ?>"
                                                data-company="<?= sanitize($msg['company'] ?? '') ?>"
                                                data-budget="<?= sanitize($msg['budget'] ?? '') ?>"
                                                data-message="<?= sanitize($msg['message']) ?>"
                                                data-date="<?= sanitize($msg['created_at']) ?>"
                                                data-isread="<?= $msg['is_read'] ?>"
                                                title="View Message">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>

                                        <button class="action-btn delete inline-delete-btn"
                                                data-id="<?= $msg['id'] ?>"
                                                title="Delete">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>

                                    <div class="inline-confirm" id="confirm-delete-<?= $msg['id'] ?>">
                                        <span style="font-size:12px; color:var(--text-secondary);">Delete?</span>
                                        <button type="button" class="inline-confirm-btn confirm-yes" onclick="document.getElementById('deleteForm-<?= $msg['id'] ?>').submit();">Yes</button>
                                        <button type="button" class="inline-confirm-btn confirm-no" onclick="this.closest('.inline-confirm').classList.remove('active');">No</button>
                                    </div>
                                    <form method="POST" id="deleteForm-<?= $msg['id'] ?>" style="display:none;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-envelope"></i>
                    <h3>No messages found</h3>
                    <p><?= $search || $isRead !== '' ? 'Try adjusting your filters or search terms.' : 'No messages have been received yet.' ?></p>
                    <?php if ($search || $isRead !== ''): ?>
                    <a href="<?= ADMIN_URL ?>/messages.php" class="admin-btn admin-btn-secondary" style="margin-top:8px;">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear Filters
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($messages['total_pages'] > 1): ?>
        <div class="pagination">
            <a href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>&is_read=<?= urlencode($isRead) ?>"
               class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <span class="page-link"><i class="bi bi-chevron-left"></i></span>
            </a>
            <?php
            $start = max(1, $page - 2);
            $end = min($messages['total_pages'], $page + 2);
            if ($start > 1): ?>
            <a href="?page=1&search=<?= urlencode($search) ?>&is_read=<?= urlencode($isRead) ?>" class="page-item">
                <span class="page-link">1</span>
            </a>
            <?php if ($start > 2): ?>
            <span class="page-link" style="border:none; color:var(--text-muted);">...</span>
            <?php endif; ?>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&is_read=<?= urlencode($isRead) ?>"
               class="page-item <?= $i === $page ? 'active' : '' ?>">
                <span class="page-link"><?= $i ?></span>
            </a>
            <?php endfor; ?>
            <?php if ($end < $messages['total_pages']): ?>
            <?php if ($end < $messages['total_pages'] - 1): ?>
            <span class="page-link" style="border:none; color:var(--text-muted);">...</span>
            <?php endif; ?>
            <a href="?page=<?= $messages['total_pages'] ?>&search=<?= urlencode($search) ?>&is_read=<?= urlencode($isRead) ?>" class="page-item">
                <span class="page-link"><?= $messages['total_pages'] ?></span>
            </a>
            <?php endif; ?>
            <a href="?page=<?= min($messages['total_pages'], $page + 1) ?>&search=<?= urlencode($search) ?>&is_read=<?= urlencode($isRead) ?>"
               class="page-item <?= $page >= $messages['total_pages'] ? 'disabled' : '' ?>">
                <span class="page-link"><i class="bi bi-chevron-right"></i></span>
            </a>
        </div>
        <div style="text-align:center; margin-top:8px; font-size:13px; color:var(--text-muted);">
            Showing <?= (($page - 1) * $messages['per_page']) + 1 ?>–<?= min($page * $messages['per_page'], $messages['total']) ?> of <?= $messages['total'] ?> messages
        </div>
        <?php endif; ?>

    </div>

    <div class="admin-footer">
        &copy; <?= date('Y') ?> Portfolio Admin Panel
    </div>
</div>

<!-- VIEW MESSAGE MODAL -->
<div class="modal-admin" id="messageModal">
    <div class="modal-overlay"></div>
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3>Message Details</h3>
            <button class="modal-close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom:24px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div>
                        <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">From</div>
                        <div style="font-size:16px; font-weight:600; color:#fff;" id="modalName"></div>
                        <div style="font-size:13px; color:var(--info); margin-top:2px;" id="modalEmail"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Date Received</div>
                        <div style="font-size:14px; color:#ccc;" id="modalDate"></div>
                        <div style="font-size:12px; color:var(--text-muted); margin-top:4px;" id="modalDateFull"></div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div>
                        <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Company</div>
                        <div style="font-size:14px; color:#ccc;" id="modalCompany"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Budget</div>
                        <div style="font-size:14px; color:#ccc;" id="modalBudget"></div>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Status</div>
                    <div id="modalStatus"></div>
                </div>

                <div>
                    <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Message</div>
                    <div style="background:#0d0d0d; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; font-size:14px; color:#ccc; line-height:1.8; white-space:pre-wrap; max-height:300px; overflow-y:auto;" id="modalMessage"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" class="admin-btn admin-btn-secondary" id="modalReplyBtn" target="_blank">
                <i class="bi bi-reply-fill"></i> Reply via Email
            </a>
            <form method="POST" style="display:inline;" id="modalToggleReadForm">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="mark_read" id="modalToggleAction">
                <input type="hidden" name="id" id="modalToggleId" value="">
                <button type="submit" class="admin-btn admin-btn-secondary" id="modalToggleBtn">
                    <i class="bi bi-envelope-fill"></i> <span id="modalToggleText">Mark as Read</span>
                </button>
            </form>
            <button class="admin-btn admin-btn-secondary modal-close">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View message modal
    var viewBtns = document.querySelectorAll('.view-message-btn');
    var modal = document.getElementById('messageModal');
    var modalName = document.getElementById('modalName');
    var modalEmail = document.getElementById('modalEmail');
    var modalCompany = document.getElementById('modalCompany');
    var modalBudget = document.getElementById('modalBudget');
    var modalDate = document.getElementById('modalDate');
    var modalDateFull = document.getElementById('modalDateFull');
    var modalMessage = document.getElementById('modalMessage');
    var modalStatus = document.getElementById('modalStatus');
    var modalReplyBtn = document.getElementById('modalReplyBtn');
    var modalToggleId = document.getElementById('modalToggleId');
    var modalToggleAction = document.getElementById('modalToggleAction');
    var modalToggleText = document.getElementById('modalToggleText');

    viewBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var name = this.getAttribute('data-name');
            var email = this.getAttribute('data-email');
            var company = this.getAttribute('data-company') || 'Not specified';
            var budget = this.getAttribute('data-budget') || 'Not specified';
            var message = this.getAttribute('data-message');
            var date = this.getAttribute('data-date');
            var isRead = this.getAttribute('data-isread');
            var msgId = this.getAttribute('data-id');

            modalName.textContent = name;
            modalEmail.textContent = email;
            modalCompany.textContent = company;
            modalBudget.textContent = budget;

            var d = new Date(date);
            modalDate.textContent = d.toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            modalDateFull.textContent = d.toISOString().replace('T', ' ').substring(0, 19);
            modalMessage.textContent = message;
            modalToggleId.value = msgId;

            if (isRead === '1') {
                modalStatus.innerHTML = '<span class="status-badge status-read">Read</span>';
                modalToggleAction.value = 'mark_unread';
                modalToggleText.textContent = 'Mark as Unread';
            } else {
                modalStatus.innerHTML = '<span class="status-badge status-unread">Unread</span>';
                modalToggleAction.value = 'mark_read';
                modalToggleText.textContent = 'Mark as Read';
            }

            modalReplyBtn.href = 'mailto:' + email + '?subject=Re: Your message&body=Hi ' + encodeURIComponent(name) + ',%0A%0A';
            modal.classList.add('active');
        });
    });

    modal.querySelectorAll('.modal-close, .modal-overlay').forEach(function(el) {
        el.addEventListener('click', function() {
            modal.classList.remove('active');
        });
    });

    // Inline delete confirm
    var inlineDeleteBtns = document.querySelectorAll('.inline-delete-btn');
    inlineDeleteBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var id = this.getAttribute('data-id');
            var confirmEl = document.getElementById('confirm-delete-' + id);
            if (confirmEl) {
                confirmEl.classList.toggle('active');
            }
        });
    });

    // Select all / bulk actions
    var selectAll = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.row-checkbox');
    var bulkBar = document.getElementById('bulkActionsBar');
    var bulkCount = document.getElementById('bulkCount');
    var bulkMarkReadIds = document.getElementById('bulkMarkReadIds');
    var bulkDeleteIds = document.getElementById('bulkDeleteIds');

    function updateBulk() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        var count = checked.length;
        var ids = [];
        checked.forEach(function(cb) {
            if (cb.value) ids.push(cb.value);
        });

        if (bulkBar) {
            bulkBar.style.display = count > 0 ? 'flex' : 'none';
        }
        if (bulkCount) {
            bulkCount.textContent = count;
        }
        if (bulkMarkReadIds) {
            bulkMarkReadIds.value = ids.join(',');
        }
        if (bulkDeleteIds) {
            bulkDeleteIds.value = ids.join(',');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var checked = this.checked;
            checkboxes.forEach(function(cb) { cb.checked = checked; });
            updateBulk();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            var allChecked = checkboxes.length === document.querySelectorAll('.row-checkbox:checked').length;
            if (selectAll) selectAll.checked = allChecked;
            updateBulk();
        });
    });

    // Close inline confirms when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.inline-delete-btn') && !e.target.closest('.inline-confirm')) {
            document.querySelectorAll('.inline-confirm.active').forEach(function(el) {
                el.classList.remove('active');
            });
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
