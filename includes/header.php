<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'Keepsake';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Keepsake</title>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= e(app_url('index.php')) ?>">Keepsake</a>
    <nav aria-label="Main navigation">
        <?php if (is_logged_in()): ?>
            <a href="<?= e(app_url('dashboard.php')) ?>">Dashboard</a>
            <a href="<?= e(app_url('items/index.php')) ?>">My keepsakes</a>
            <form class="nav-form" action="<?= e(app_url('logout.php')) ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button class="nav-link" type="submit">Log out</button>
            </form>
        <?php else: ?>
            <a href="<?= e(app_url('login.php')) ?>">Log in</a>
            <a href="<?= e(app_url('register.php')) ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
