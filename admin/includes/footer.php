    <div class="admin-footer">
        &copy; <?= date('Y') ?> Portfolio Admin Panel
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ADMIN_URL ?>/assets/js/admin.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . ADMIN_URL . '/assets/js/admin.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var flash = document.getElementById('flashToast');
    if (flash) {
        setTimeout(function() {
            flash.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(function() { flash.remove(); }, 400);
        }, 4000);
    }

    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        sidebarToggle.style.display = '';
        document.body.style.overflow = '';
    }

    if (sidebarToggle && sidebar && overlay) {
        sidebarToggle.addEventListener('click', function() {
            var isOpen = sidebar.classList.contains('open');
            if (isOpen) {
                closeSidebar();
            } else {
                sidebar.classList.add('open');
                overlay.classList.add('active');
                sidebarToggle.style.display = 'none';
                document.body.style.overflow = 'hidden';
            }
        });

        overlay.addEventListener('click', closeSidebar);

        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                closeSidebar();
            }
        });
    }
});
</script>
</body>
</html>
