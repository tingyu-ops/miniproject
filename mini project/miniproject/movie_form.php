<?php
require_once __DIR__ . '/auth.php';
require_role(['admin', 'movie_moderator']);

$id = (int)($_GET['id'] ?? $_POST['movie_id'] ?? 0);
$genres = $pdo->query('SELECT * FROM genres ORDER BY genre_name')->fetchAll();
$movie = ['title' => '', 'genre_id' => '', 'release_year' => '', 'description' => '', 'poster' => ''];

if ($id) {
    $s = $pdo->prepare('SELECT * FROM movies WHERE movie_id = :id');
    $s->execute([':id' => $id]);
    $movie = $s->fetch();
    if (!$movie) { flash('Movie not found.', 'error'); header('Location: admin.php'); exit; }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $movie = [
        'title'        => trim($_POST['title'] ?? ''),
        'genre_id'     => (int)($_POST['genre_id'] ?? 0),
        'release_year' => (int)($_POST['release_year'] ?? 0),
        'description'  => trim($_POST['description'] ?? ''),
        'poster'       => trim($_POST['poster'] ?? ''),
    ];
    $validGenre = in_array($movie['genre_id'], array_map('intval', array_column($genres, 'genre_id')), true);
    if ($movie['title'] === '' || mb_strlen($movie['title']) > 255) {
        $error = 'Title is required (max 255 characters).';
    } elseif (!$validGenre) {
        $error = 'Please choose a genre.';
    } elseif ($movie['release_year'] < 1888 || $movie['release_year'] > (int)date('Y') + 5) {
        $error = 'Please enter a valid release year.';
    } elseif ($movie['poster'] !== '' && !preg_match('#^(https?://|[\w.\- ]+\.(jpe?g|png|webp|gif)$)#i', $movie['poster'])) {
        $error = 'Poster must be an http(s) URL or an image file name (e.g. inception.jpg).';
    } else {
        $p = [':t' => $movie['title'], ':g' => $movie['genre_id'], ':y' => $movie['release_year'],
              ':d' => $movie['description'], ':p' => $movie['poster'] ?: null];
        if ($id) {
            $p[':id'] = $id;
            $pdo->prepare('UPDATE movies SET title=:t, genre_id=:g, release_year=:y, description=:d, poster=:p WHERE movie_id=:id')->execute($p);
            flash('Movie updated.');
        } else {
            $pdo->prepare('INSERT INTO movies (title, genre_id, release_year, description, poster) VALUES (:t,:g,:y,:d,:p)')->execute($p);
            flash('Movie added.');
        }
        header('Location: admin.php');
        exit;
    }
}
$pageTitle = $id ? 'Edit movie' : 'Add movie';
require __DIR__ . '/header.php';
?>
<h1><?= e($pageTitle) ?></h1>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="POST" class="card form-wide">
    <?= csrf_field() ?>
    <input type="hidden" name="movie_id" value="<?= $id ?>">
    <div class="form-group"><label>Title</label>
        <input type="text" name="title" maxlength="255" value="<?= e($movie['title']) ?>" required></div>
    <div class="form-group"><label>Genre</label>
        <select name="genre_id" required>
            <option value="">Select…</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?= $g['genre_id'] ?>" <?= (int)$movie['genre_id'] === (int)$g['genre_id'] ? 'selected' : '' ?>><?= e($g['genre_name']) ?></option>
            <?php endforeach; ?>
        </select></div>
    <div class="form-group"><label>Release year</label>
        <input type="number" name="release_year" min="1888" max="<?= date('Y') + 5 ?>" value="<?= e($movie['release_year']) ?>" required></div>
    <div class="form-group"><label>Description</label>
        <textarea name="description" rows="5"><?= e($movie['description']) ?></textarea></div>
    <div class="form-group"><label>Poster (URL, or file name inside the posters/ folder)</label>
        <input type="text" name="poster" value="<?= e($movie['poster']) ?>"></div>
    <button class="btn">Save</button>
    <a class="btn btn-secondary" href="admin.php">Cancel</a>
</form>
<?php require __DIR__ . '/footer.php'; ?>
