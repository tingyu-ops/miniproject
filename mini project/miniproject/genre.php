<?php
require_once __DIR__ . '/auth.php';
require_role(['admin', 'movie_moderator']);
$pageTitle = 'Manage Genres';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $name   = trim($_POST['genre_name'] ?? '');
    $gid    = (int)($_POST['genre_id'] ?? 0);
    try {
        if ($action === 'add' && $name !== '' && mb_strlen($name) <= 100) {
            $pdo->prepare('INSERT INTO genres (genre_name) VALUES (:n)')->execute([':n' => $name]);
            flash('Genre added.');
        } elseif ($action === 'rename' && $name !== '' && mb_strlen($name) <= 100) {
            $pdo->prepare('UPDATE genres SET genre_name = :n WHERE genre_id = :id')->execute([':n' => $name, ':id' => $gid]);
            flash('Genre updated.');
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM genres WHERE genre_id = :id')->execute([':id' => $gid]);
            flash('Genre deleted.');
        } else {
            flash('Please enter a genre name (max 100 characters).', 'error');
        }
    } catch (PDOException $ex) {
        if ($ex->getCode() === '23000') {
            flash('That genre name already exists, or the genre is still used by movies.', 'error');
        } else { throw $ex; }
    }
    header('Location: genre.php');
    exit;
}

$genres = $pdo->query('SELECT g.*, COUNT(m.movie_id) AS movie_count FROM genres g
                       LEFT JOIN movies m ON m.genre_id = g.genre_id GROUP BY g.genre_id ORDER BY g.genre_name')->fetchAll();
require __DIR__ . '/header.php';
?>
<h1>Manage Genres</h1>
<form method="POST" class="filters">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <input type="text" name="genre_name" placeholder="New genre name" maxlength="100" required>
    <button class="btn">Add genre</button>
</form>
<table class="table">
    <tr><th>Genre</th><th>Movies</th><th>Actions</th></tr>
    <?php foreach ($genres as $g): ?>
        <tr>
            <td>
                <form method="POST" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="genre_id" value="<?= $g['genre_id'] ?>">
                    <input type="text" name="genre_name" value="<?= e($g['genre_name']) ?>" maxlength="100" required>
                    <button class="btn btn-small">Rename</button>
                </form>
            </td>
            <td><?= (int)$g['movie_count'] ?></td>
            <td>
                <?php if ((int)$g['movie_count'] === 0): ?>
                <form method="POST" class="inline" onsubmit="return confirm('Delete this genre?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="genre_id" value="<?= $g['genre_id'] ?>">
                    <button class="btn btn-small btn-danger">Delete</button>
                </form>
                <?php else: ?><span class="muted">In use</span><?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<p><a href="admin.php">&larr; Staff panel</a></p>
<?php require __DIR__ . '/footer.php'; ?>
