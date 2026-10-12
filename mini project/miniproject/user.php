<?php
require_once __DIR__ . '/auth.php';
require_login();
$pageTitle = 'Profile';
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $newPw = $_POST['password'] ?? '';
        if ($name === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('Enter a valid name and email.', 'error');
        } elseif ($newPw !== '' && strlen($newPw) < 8) {
            flash('New password must be at least 8 characters.', 'error');
        } else {
            $dup = $pdo->prepare('SELECT 1 FROM users WHERE email = :e AND user_id <> :id');
            $dup->execute([':e' => $email, ':id' => $me['id']]);
            if ($dup->fetchColumn()) {
                flash('That email is already used by another account.', 'error');
            } else {
                $pdo->prepare('UPDATE users SET name = :n, email = :e WHERE user_id = :id')
                    ->execute([':n' => $name, ':e' => $email, ':id' => $me['id']]);
                if ($newPw !== '') {
                    $pdo->prepare('UPDATE users SET password = :p WHERE user_id = :id')
                        ->execute([':p' => password_hash($newPw, PASSWORD_DEFAULT), ':id' => $me['id']]);
                }
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                flash('Profile updated.');
            }
        }
    } elseif (is_admin() && $action === 'set_role') {
        $uid = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? '';
        if ($uid === $me['id']) {
            flash('You cannot change your own role.', 'error');
        } elseif (in_array($role, ['admin', 'movie_moderator', 'user'], true)) {
            $pdo->prepare('UPDATE users SET role = :r WHERE user_id = :id')->execute([':r' => $role, ':id' => $uid]);
            flash('Role updated.');
        }
    } elseif (is_admin() && $action === 'delete_user') {
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid === $me['id']) {
            flash('You cannot delete your own account.', 'error');
        } else {
            $pdo->prepare('DELETE FROM users WHERE user_id = :id')->execute([':id' => $uid]);
            flash('User deleted.');
        }
    }
    header('Location: user.php');
    exit;
}

$row = $pdo->prepare('SELECT name, email FROM users WHERE user_id = :id');
$row->execute([':id' => $me['id']]);
$row = $row->fetch();
$users = is_admin() ? $pdo->query('SELECT user_id, name, email, role, created_at FROM users ORDER BY created_at')->fetchAll() : [];
require __DIR__ . '/header.php';
?>
<h1>My Profile</h1>
<form method="POST" class="card form-wide">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="profile">
    <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= e($row['name']) ?>" maxlength="100" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= e($row['email']) ?>" required></div>
    <div class="form-group"><label>New password (leave blank to keep current)</label><input type="password" name="password" minlength="8"></div>
    <button class="btn">Save changes</button>
</form>

<?php if (is_admin()): ?>
<h2>Manage Users</h2>
<table class="table">
    <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td>
                <?php if ((int)$u['user_id'] === $me['id']): ?>
                    <?= e(role_label($u['role'])) ?> (you)
                <?php else: ?>
                <form method="POST" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="set_role">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <select name="role">
                        <?php foreach (['user', 'movie_moderator', 'admin'] as $r): ?>
                            <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= e(role_label($r)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-small">Set</button>
                </form>
                <?php endif; ?>
            </td>
            <td><?= e(substr($u['created_at'], 0, 10)) ?></td>
            <td>
                <?php if ((int)$u['user_id'] !== $me['id']): ?>
                <form method="POST" class="inline" onsubmit="return confirm('Delete this user and all their reviews?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <button class="btn btn-small btn-danger">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
