<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) { header('Location: mainpage.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || mb_strlen($name) > 100) {
        $error = 'Please enter your name (max 100 characters).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $error = 'That email is already registered.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (:n, :e, :p, \'user\')');
            $stmt->execute([':n' => $name, ':e' => $email, ':p' => password_hash($password, PASSWORD_DEFAULT)]);
            login_user(['user_id' => $pdo->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'user']);
            header('Location: mainpage.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – Movie Review System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="register">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Create account</h1>
            <p class="subtitle">Sign up to review movies</p>
            <?php if ($error): ?><div class="error-msg"><?= e($error) ?></div><?php endif; ?>
            <form method="POST" action="register.php">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Jane Smith" value="<?= e($_POST['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password (min 8 characters)</label>
                    <input type="password" id="password" name="password" minlength="8" required>
                </div>
                <button type="submit" class="btn-auth">Create Account</button>
            </form>
            <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </div>
</body>
</html>
