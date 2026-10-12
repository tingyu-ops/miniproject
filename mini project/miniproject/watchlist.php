<?php
require_once __DIR__ . '/auth.php';
require_role(['user']);
$pageTitle = 'My Watchlist';
$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $pdo->prepare('DELETE FROM watchlist WHERE user_id = :u AND movie_id = :m')
        ->execute([':u' => $uid, ':m' => (int)($_POST['movie_id'] ?? 0)]);
    flash('Removed from your watchlist.');
    header('Location: watchlist.php');
    exit;
}

$stmt = $pdo->prepare('SELECT m.movie_id, m.title, m.release_year, g.genre_name, w.added_date
                       FROM watchlist w JOIN movies m ON m.movie_id = w.movie_id
                       JOIN genres g ON g.genre_id = m.genre_id
                       WHERE w.user_id = :u ORDER BY w.added_date DESC');
$stmt->execute([':u' => $uid]);
$items = $stmt->fetchAll();
require __DIR__ . '/header.php';
?>
<h1>My Watchlist</h1>
<?php if (!$items): ?>
    <p class="muted">Your watchlist is empty. <a href="mainpage.php">Browse movies</a> to add some.</p>
<?php else: ?>
<table class="table">
    <tr><th>Title</th><th>Year</th><th>Genre</th><th>Added</th><th></th></tr>
    <?php foreach ($items as $i): ?>
        <tr>
            <td><a href="movie.php?id=<?= $i['movie_id'] ?>"><?= e($i['title']) ?></a></td>
            <td><?= e($i['release_year']) ?></td>
            <td><?= e($i['genre_name']) ?></td>
            <td><?= e(substr($i['added_date'], 0, 10)) ?></td>
            <td>
                <form method="POST" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="movie_id" value="<?= $i['movie_id'] ?>">
                    <button class="btn btn-small btn-danger">Remove</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
