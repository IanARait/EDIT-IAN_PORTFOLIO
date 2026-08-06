document.addEventListener('DOMContentLoaded', function () {

    // ===== SIDEBAR TOGGLE (MOBILE) =====
    // Handled in footer.php inline script to avoid duplicate listeners

    // ===== ACTIVE SIDEBAR LINK =====
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar-link');

    sidebarLinks.forEach(function (link) {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href) && href !== '/' && href !== '#') {
            link.classList.add('active');
        }
    });

    // ===== TOAST NOTIFICATIONS =====
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    window.showToast = function (message, type) {
        type = type || 'success';
        var iconMap = {
            success: 'ri-check-line',
            error: 'ri-close-circle-line',
            info: 'ri-information-line',
            warning: 'ri-alert-line'
        };

        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.innerHTML = '<i class="' + (iconMap[type] || iconMap.info) + '"></i><span>' + message + '</span>';

        toastContainer.appendChild(toast);

        setTimeout(function () {
            toast.classList.add('toast-out');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        }, 4000);
    };

    // ===== CONFIRM DIALOG =====
    window.showConfirm = function (message, callback) {
        var dialog = document.createElement('div');
        dialog.className = 'confirm-dialog';
        dialog.innerHTML =
            '<div class="confirm-dialog-content">' +
                '<i class="ri-error-warning-line"></i>' +
                '<h3>Are you sure?</h3>' +
                '<p>' + message + '</p>' +
                '<div class="confirm-dialog-actions">' +
                    '<button class="admin-btn admin-btn-secondary confirm-cancel">Cancel</button>' +
                    '<button class="admin-btn admin-btn-danger confirm-ok">Delete</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(dialog);

        dialog.querySelector('.confirm-cancel').addEventListener('click', function () {
            document.body.removeChild(dialog);
        });

        dialog.querySelector('.confirm-ok').addEventListener('click', function () {
            document.body.removeChild(dialog);
            if (typeof callback === 'function') callback();
        });

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                document.body.removeChild(dialog);
            }
        });
    };

    // ===== AJAX HELPER =====
    window.ajaxRequest = function (url, method, data) {
        method = method || 'GET';

        var options = {
            method: method,
            headers: {}
        };

        if (data && method !== 'GET') {
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        return fetch(url, options)
            .then(function (response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .catch(function (error) {
                console.error('AJAX Error:', error);
                showToast('An error occurred. Please try again.', 'error');
                throw error;
            });
    };

    // ===== FLASH MESSAGE AUTO-DISMISS =====
    var flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function (msg) {
        setTimeout(function () {
            msg.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-10px)';
            setTimeout(function () {
                if (msg.parentNode) msg.parentNode.removeChild(msg);
            }, 300);
        }, 5000);
    });

    // ===== PROJECT FORM HANDLING =====
    // Projects page handles its own form natively via Bootstrap modals + standard POST.

    // ===== MESSAGE HANDLING =====
    // Mark as Read/Unread
    var markReadBtns = document.querySelectorAll('.mark-read-btn');
    markReadBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var action = this.getAttribute('data-action') || 'mark_read';

            ajaxRequest('admin/ajax.php?action=' + action, 'POST', { id: id })
                .then(function (response) {
                    if (response && response.success) {
                        showToast(response.message || 'Message updated', 'success');
                        setTimeout(function () { location.reload(); }, 800);
                    }
                });
        });
    });

    // Delete Message
    var deleteMessageBtns = document.querySelectorAll('.delete-message-btn');
    deleteMessageBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');

            showConfirm('Are you sure you want to delete this message?', function () {
                ajaxRequest('admin/ajax.php?action=delete_message', 'POST', { id: id })
                    .then(function (response) {
                        if (response && response.success) {
                            showToast('Message deleted', 'success');
                            var row = document.querySelector('[data-row-id="' + id + '"]');
                            if (row) {
                                row.style.transition = 'opacity 0.3s ease';
                                row.style.opacity = '0';
                                setTimeout(function () {
                                    if (row.parentNode) row.parentNode.removeChild(row);
                                }, 300);
                            }
                        }
                    });
            });
        });
    });

    // Bulk Delete Read Messages
    var bulkDeleteBtn = document.querySelector('.bulk-delete-read-btn');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            showConfirm('Delete all read messages? This cannot be undone.', function () {
                ajaxRequest('admin/ajax.php?action=bulk_delete_messages', 'POST', { filter: 'read' })
                    .then(function (response) {
                        if (response && response.success) {
                            showToast(response.message || 'Read messages deleted', 'success');
                            setTimeout(function () { location.reload(); }, 800);
                        }
                    });
            });
        });
    }

    // ===== SETTINGS FORM =====
    // Settings page handles its own form natively via standard POST.

    // ===== AVATAR UPLOAD PREVIEW =====
    var avatarInput = document.getElementById('avatarInput');
    var avatarPreview = document.getElementById('avatarPreview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ===== THUMBNAIL UPLOAD PREVIEW =====
    var thumbInput = document.getElementById('thumbnailInput');
    var thumbPreview = document.querySelector('.thumbnail-preview');
    var uploadArea = document.querySelector('.upload-area');

    if (thumbInput) {
        thumbInput.addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    if (thumbPreview) {
                        thumbPreview.src = e.target.result;
                    }
                    if (uploadArea) {
                        uploadArea.classList.add('has-image');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ===== SEARCH DEBOUNCE =====
    var searchInput = document.querySelector('.search-input');
    if (searchInput) {
        var searchTimeout;
        searchInput.addEventListener('input', function () {
            var query = this.value.toLowerCase();
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(function () {
                var tableRows = document.querySelectorAll('.data-table tbody tr');
                tableRows.forEach(function (row) {
                    var text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            }, 300);
        });
    }

    // ===== TABLE SORTING =====
    var sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(function (header) {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function () {
            var table = this.closest('table');
            if (!table) return;

            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            var rows = Array.from(tbody.querySelectorAll('tr'));
            var colIndex = Array.from(this.parentNode.children).indexOf(this);
            var isAsc = this.classList.contains('sort-asc');

            // Reset all headers
            this.parentNode.querySelectorAll('.sortable').forEach(function (h) {
                h.classList.remove('sort-asc', 'sort-desc');
            });

            rows.sort(function (a, b) {
                var aVal = (a.children[colIndex] ? a.children[colIndex].textContent : '').trim().toLowerCase();
                var bVal = (b.children[colIndex] ? b.children[colIndex].textContent : '').trim().toLowerCase();

                if (!isNaN(parseFloat(aVal)) && !isNaN(parseFloat(bVal))) {
                    return isAsc ? parseFloat(bVal) - parseFloat(aVal) : parseFloat(aVal) - parseFloat(bVal);
                }

                return isAsc
                    ? bVal.localeCompare(aVal)
                    : aVal.localeCompare(bVal);
            });

            this.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

            rows.forEach(function (row) {
                tbody.appendChild(row);
            });
        });
    });

    // ===== SELECT ALL CHECKBOX =====
    var selectAll = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.row-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            var checked = this.checked;
            checkboxes.forEach(function (cb) {
                cb.checked = checked;
            });
            updateBulkActions();
        });

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                selectAll.checked = checkboxes.length === document.querySelectorAll('.row-checkbox:checked').length;
                updateBulkActions();
            });
        });
    }

    function updateBulkActions() {
        var checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        var bulkBar = document.querySelector('.bulk-actions');
        var countSpan = document.querySelector('.bulk-count');

        if (bulkBar) {
            bulkBar.style.display = checkedCount > 0 ? 'flex' : 'none';
        }
        if (countSpan) {
            countSpan.textContent = checkedCount;
        }
    }

    // Bulk delete selected
    var bulkDeleteSelected = document.querySelector('.bulk-delete-selected');
    if (bulkDeleteSelected) {
        bulkDeleteSelected.addEventListener('click', function () {
            var selected = [];
            document.querySelectorAll('.row-checkbox:checked').forEach(function (cb) {
                selected.push(cb.value);
            });

            if (selected.length === 0) return;

            showConfirm('Delete ' + selected.length + ' selected item(s)?', function () {
                ajaxRequest('admin/ajax.php?action=bulk_delete', 'POST', { ids: selected })
                    .then(function (response) {
                        if (response && response.success) {
                            showToast(response.message || 'Items deleted', 'success');
                            setTimeout(function () { location.reload(); }, 800);
                        }
                    });
            });
        });
    }

    // ===== COPY TO CLIPBOARD =====
    var copyBtns = document.querySelectorAll('.copy-url-btn');
    copyBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = this.getAttribute('data-url') || this.getAttribute('data-copy');
            if (url && navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function () {
                    showToast('URL copied to clipboard', 'success');
                }).catch(function () {
                    fallbackCopy(url);
                });
            }
        });
    });

    function fallbackCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showToast('URL copied to clipboard', 'success');
        } catch (e) {
            showToast('Failed to copy URL', 'error');
        }
        document.body.removeChild(textarea);
    }

    // ===== CONFIRM LOGOUT =====
    var logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var href = this.getAttribute('href') || 'admin/logout.php';

            showConfirm('Are you sure you want to log out?', function () {
                window.location.href = href;
            });
        });
    }

    // ===== PRINT PAGE =====
    var printBtn = document.querySelector('.print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }

    // ===== CLOSE MODALS =====
    var closeModalBtns = document.querySelectorAll('.modal-close, .modal-cancel');
    closeModalBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modal = this.closest('.modal-admin');
            if (modal) modal.classList.remove('active');
        });
    });

    // Close modal on overlay click
    var modalOverlays = document.querySelectorAll('.modal-overlay');
    modalOverlays.forEach(function (overlay) {
        overlay.addEventListener('click', function () {
            var modal = this.closest('.modal-admin');
            if (modal) modal.classList.remove('active');
        });
    });

    // ===== KEYBOARD SHORTCUTS =====
    document.addEventListener('keydown', function (e) {
        // Ctrl+K / Cmd+K - Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            var search = document.querySelector('.search-input');
            if (search) search.focus();
        }

        // Escape - Close modals
        if (e.key === 'Escape') {
            var openModal = document.querySelector('.modal-admin.active');
            if (openModal) {
                openModal.classList.remove('active');
                return;
            }

            var openConfirm = document.querySelector('.confirm-dialog');
            if (openConfirm) {
                openConfirm.parentNode.removeChild(openConfirm);
                return;
            }

            // Close sidebar on mobile
            if (window.innerWidth <= 992) {
                var sb = document.getElementById('adminSidebar');
                var ov = document.getElementById('sidebarOverlay');
                if (sb) sb.classList.remove('open');
                if (ov) ov.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });

    // ===== CLICK OUTSIDE DROPDOWN =====
    document.addEventListener('click', function (e) {
        var dropdowns = document.querySelectorAll('.dropdown-menu.active');
        dropdowns.forEach(function (dropdown) {
            if (!dropdown.parentElement.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });

    // ===== TOOLTIP INIT (if present) =====
    var tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(function (el) {
        el.title = el.getAttribute('data-tooltip');
    });

});
