<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) { header('Location: mainpage.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch();

    if ($row && password_verify($password, $row['password'])) {
        login_user($row);
        header('Location: ' . (is_staff() ? 'admin.php' : 'mainpage.php'));
        exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Movie Review System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="register">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Welcome back</h1>
            <p class="subtitle">Log in to review movies</p>
            <?php if ($error): ?><div class="error-msg"><?= e($error) ?></div><?php endif; ?>
            <form method="POST" action="login.php">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-auth">Log in</button>
            </form>
            <p class="auth-switch">No account? <a href="register.php">Register</a> · <a href="mainpage.php">Browse as guest</a></p>
        </div>
    </div>
</body>
</html>
