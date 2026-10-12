<?php
require_once __DIR__ . '/auth.php';
require_role(['admin']);
$pageTitle = 'System Statistics';

$count = fn(string $sql) => (int)$pdo->query($sql)->fetchColumn();
$stats = [
    'Users'            => $count('SELECT COUNT(*) FROM users'),
    'Movies'           => $count('SELECT COUNT(*) FROM movies'),
    'Genres'           => $count('SELECT COUNT(*) FROM genres'),
    'Approved reviews' => $count("SELECT COUNT(*) FROM reviews WHERE review_status = 'approved'"),
    'Pending reviews'  => $count("SELECT COUNT(*) FROM reviews WHERE review_status = 'pending'"),
    'Open reports'     => $count("SELECT COUNT(*) FROM review_reports WHERE status = 'open'"),
    'Watchlist items'  => $count('SELECT COUNT(*) FROM watchlist'),
];
$top = $pdo->query('SELECT m.movie_id, m.title, ROUND(AVG(r.rating),1) AS avg_rating, COUNT(*) AS n
                    FROM movies m JOIN ratings r ON r.movie_id = m.movie_id
                    GROUP BY m.movie_id, m.title ORDER BY AVG(r.rating) DESC, COUNT(*) DESC LIMIT 5')->fetchAll();
$byGenre = $pdo->query('SELECT g.genre_name, COUNT(m.movie_id) AS n FROM genres g
                        LEFT JOIN movies m ON m.genre_id = g.genre_id GROUP BY g.genre_id ORDER BY n DESC, g.genre_name')->fetchAll();
$roles = $pdo->query('SELECT role, COUNT(*) AS n FROM users GROUP BY role')->fetchAll();
require __DIR__ . '/header.php';
?>
<h1>System Statistics</h1>
<div class="stat-grid">
    <?php foreach ($stats as $label => $val): ?>
        <div class="stat"><span class="num"><?= $val ?></span><span><?= e($label) ?></span></div>
    <?php endforeach; ?>
</div>

<div class="two-col">
    <section class="card">
        <h2>Top rated movies</h2>
        <?php if (!$top): ?><p class="muted">No ratings yet.</p><?php endif; ?>
        <ol>
            <?php foreach ($top as $t): ?>
                <li><a href="movie.php?id=<?= $t['movie_id'] ?>"><?= e($t['title']) ?></a> – <?= e($t['avg_rating']) ?>/5 (<?= (int)$t['n'] ?>)</li>
            <?php endforeach; ?>
        </ol>
    </section>
    <section class="card">
        <h2>Movies per genre</h2>
        <ul class="plain">
            <?php foreach ($byGenre as $g): ?><li><?= e($g['genre_name']) ?>: <?= (int)$g['n'] ?></li><?php endforeach; ?>
        </ul>
    </section>
    <section class="card">
        <h2>Users by role</h2>
        <ul class="plain">
            <?php foreach ($roles as $r): ?><li><?= e(role_label($r['role'])) ?>: <?= (int)$r['n'] ?></li><?php endforeach; ?>
        </ul>
    </section>
</div>
<p><a href="admin.php">&larr; Staff panel</a></p>
<?php require __DIR__ . '/footer.php'; ?>
