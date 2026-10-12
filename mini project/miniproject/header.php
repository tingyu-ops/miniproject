<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'Movie Review System';
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> – Movie Review System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="mainpage.php">🎬 Movie Review</a>
    <nav>
        <a href="mainpage.php">Browse</a>
        <?php if (user_role() === 'user'): ?>
            <a href="watchlist.php">My Watchlist</a>
        <?php endif; ?>
        <?php if (is_staff()): ?>
            <a href="admin.php">Staff Panel</a>
        <?php endif; ?>
        <?php if (is_logged_in()): ?>
            <a href="user.php">Profile</a>
            <span class="who"><?= e(current_user()['name']) ?> (<?= e(role_label(user_role())) ?>)</span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash[1]) ?>"><?= e($flash[0]) ?></div>
<?php endif; ?>
