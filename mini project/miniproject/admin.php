<?php
require_once __DIR__ . '/auth.php';
require_role(['admin', 'movie_moderator']);
$pageTitle = 'Staff Panel';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_movie') {
    verify_csrf();
    $pdo->prepare('DELETE FROM movies WHERE movie_id = :id')->execute([':id' => (int) ($_POST['movie_id'] ?? 0)]);
    flash('Movie deleted.');
    header('Location: admin.php');
    exit;
}

$movies = $pdo->query('SELECT m.movie_id, m.title, m.release_year, g.genre_name
                       FROM movies m JOIN genres g ON g.genre_id = m.genre_id ORDER BY m.title')->fetchAll();
$pendingReviews = (int) $pdo->query("SELECT COUNT(*) FROM reviews WHERE review_status = 'pending'")->fetchColumn();
$openReports = (int) $pdo->query("SELECT COUNT(*) FROM review_reports WHERE status = 'open'")->fetchColumn();
require __DIR__ . '/header.php';
?>
<h1>Staff Panel</h1>
<div class="quick">
    <a class="btn" href="movie_form.php">+ Add movie</a>
    <a class="btn" href="genre.php">Manage genres</a>
    <a class="btn" href="review.php">Reviews (<?= $pendingReviews ?> pending, <?= $openReports ?> reports)</a>
    <?php if (is_admin()): ?>
        <a class="btn" href="user.php">Manage users</a>
        <a class="btn" href="dashboard.php">Statistics</a>
    <?php endif; ?>
</div>

<h2>Movies</h2>
<table class="table">
    <tr>
        <th>Title</th>
        <th>Year</th>
        <th>Genre</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($movies as $m): ?>
        <tr>
            <td><a href="movie.php?id=<?= $m['movie_id'] ?>"><?= e($m['title']) ?></a></td>
            <td><?= e($m['release_year']) ?></td>
            <td><?= e($m['genre_name']) ?></td>
            <td>
                <a class="btn btn-small" href="movie_form.php?id=<?= $m['movie_id'] ?>">Edit</a>
                <form method="POST" class="inline" onsubmit="return confirm('Delete this movie and all its reviews?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_movie">
                    <input type="hidden" name="movie_id" value="<?= $m['movie_id'] ?>">
                    <button class="btn btn-small btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php require __DIR__ . '/footer.php'; ?>