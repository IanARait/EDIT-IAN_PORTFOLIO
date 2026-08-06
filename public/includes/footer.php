<footer>
    &copy; <?= date('Y') ?> <?= sanitize($siteName) ?>. All rights reserved.
    <a href="<?= BASE_URL ?>/admin/">Admin</a>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Back to top">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/js/main.js"></script>
<script src="<?= ASSETS_URL ?>/js/arcade.js"></script>
</body>
</html>
