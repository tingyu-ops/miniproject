<?php
$pageTitle = 'Browse Movies';
require_once __DIR__ . '/header.php';

$q        = trim($_GET['q'] ?? '');
$genreId  = (int)($_GET['genre'] ?? 0);
$minRate  = (int)($_GET['min_rating'] ?? 0);
$sort     = $_GET['sort'] ?? 'title';

$genres = $pdo->query('SELECT * FROM genres ORDER BY genre_name')->fetchAll();

$sql = 'SELECT m.movie_id, m.title, m.release_year, m.poster, g.genre_name,
               ROUND(AVG(r.rating),1) AS avg_rating, COUNT(r.rating_id) AS total_ratings
        FROM movies m
        JOIN genres g ON g.genre_id = m.genre_id
        LEFT JOIN ratings r ON r.movie_id = m.movie_id
        WHERE 1=1';
$params = [];
if ($q !== '')     { $sql .= ' AND m.title LIKE :q'; $params[':q'] = '%' . $q . '%'; }
if ($genreId > 0)  { $sql .= ' AND m.genre_id = :g'; $params[':g'] = $genreId; }
$sql .= ' GROUP BY m.movie_id, m.title, m.release_year, m.poster, g.genre_name';
if ($minRate >= 1 && $minRate <= 5) { $sql .= ' HAVING AVG(r.rating) >= :mr'; $params[':mr'] = $minRate; }
$order = ['title' => 'm.title ASC', 'rating' => 'avg_rating DESC', 'newest' => 'm.release_year DESC'];
$sql .= ' ORDER BY ' . ($order[$sort] ?? $order['title']);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();

// Simple recommendation: top-rated movies (needs at least one rating), shown when no filters are used.
$recommended = [];
if ($q === '' && !$genreId && !$minRate) {
    $recommended = $pdo->query(
        'SELECT m.movie_id, m.title, ROUND(AVG(r.rating),1) AS avg_rating
         FROM movies m JOIN ratings r ON r.movie_id = m.movie_id
         GROUP BY m.movie_id, m.title ORDER BY AVG(r.rating) DESC, COUNT(*) DESC LIMIT 3'
    )->fetchAll();
}
?>
<h1>Browse Movies</h1>

<form method="GET" class="filters">
    <input type="text" name="q" placeholder="Search by title…" value="<?= e($q) ?>">
    <select name="genre">
        <option value="0">All genres</option>
        <?php foreach ($genres as $g): ?>
            <option value="<?= $g['genre_id'] ?>" <?= $genreId === (int)$g['genre_id'] ? 'selected' : '' ?>><?= e($g['genre_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="min_rating">
        <option value="0">Any rating</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?= $i ?>" <?= $minRate === $i ? 'selected' : '' ?>><?= $i ?>★ &amp; up</option>
        <?php endfor; ?>
    </select>
    <select name="sort">
        <option value="title"  <?= $sort === 'title' ? 'selected' : '' ?>>Sort: Title</option>
        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Sort: Rating</option>
        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort: Newest</option>
    </select>
    <button type="submit" class="btn">Search</button>
    <a class="btn btn-secondary" href="mainpage.php">Reset</a>
</form>

<?php if ($recommended): ?>
    <section class="card">
        <h2>Top rated picks</h2>
        <ul class="plain">
            <?php foreach ($recommended as $r): ?>
                <li><a href="movie.php?id=<?= $r['movie_id'] ?>"><?= e($r['title']) ?></a> – <?= e($r['avg_rating']) ?>/5</li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if (!$movies): ?>
    <p class="muted">No movies match your search.</p>
<?php else: ?>
<div class="grid">
    <?php foreach ($movies as $m): ?>
        <a class="movie-card" href="movie.php?id=<?= $m['movie_id'] ?>">
            <?php if (poster_src($m['poster'])): ?>
                <img src="<?= e(poster_src($m['poster'])) ?>" alt="Poster of <?= e($m['title']) ?>" loading="lazy"
                     onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'poster-ph',textContent:'<?= e(addslashes($m['title'])) ?>'}))">
            <?php else: ?>
                <div class="poster-ph"><?= e($m['title']) ?></div>
            <?php endif; ?>
            <div class="movie-info">
                <strong><?= e($m['title']) ?></strong>
                <span class="muted"><?= e($m['release_year']) ?> · <?= e($m['genre_name']) ?></span>
                <span class="stars"><?= $m['total_ratings'] ? stars((float)$m['avg_rating']) . ' ' . e($m['avg_rating']) : 'No ratings yet' ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
