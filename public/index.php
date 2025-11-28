<?php
// public/index.php - central entrypoint for the restructured prototype
session_start();

// Basic routing by `page` parameter (safe, minimal)
$page = $_GET['page'] ?? 'home';

// If the user is not authenticated, force login page (except for the login page itself)
if ($page !== 'login' && empty($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

// Base path for includes
$basePath = __DIR__ . '/..';

// If the login page is requested, render it standalone (no global header), then exit
$pageFile = $basePath . '/pages/' . basename($page) . '.php';
if ($page === 'login' && file_exists($pageFile)) {
    include $pageFile;
    exit;
}

// Otherwise include standard header/footer and page content
require_once $basePath . '/includes/header.php';
if (file_exists($pageFile)) {
    include $pageFile;
} else {
    // Default home: show prototype content (keeps frontend-only behavior)
    echo '<main class="container">';
    echo '<div class="card"><h2>Bienvenue — Prototype</h2><p>Use the navigation to view pages (this is the restructured copy).</p></div>';
    echo '</main>';
}

require_once $basePath . '/includes/footer.php';
?>
