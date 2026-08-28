<?php
$pageTitle = '404 - Page Not Found';
ob_start();
?>

    <div class="text-center py-5">
        <h1 class="display-1 text-muted">404</h1>
        <h2 class="mb-4">Page Not Found</h2>
        <p class="lead mb-4">The page you are looking for doesn't exist or has been moved.</p>
        <a href="<?= URL_ROOT ?>/" class="btn btn-primary">
            <i class="bi bi-house"></i> Go Home
        </a>
    </div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layouts/main.php';
?>