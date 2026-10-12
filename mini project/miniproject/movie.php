<?php
require_once __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? $_POST['movie_id'] ?? 0);
$stmt = $pdo->prepare('SELECT m.*, g.genre_name FROM movies m JOIN genres g ON g.genre_id = m.genre_id WHERE m.movie_id = :id');
$stmt->execute([':id' => $id]);
$movie = $stmt->fetch();
if (!$movie) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/header.php';
    echo '<div class="card"><h1>Movie not found</h1><p><a href="mainpage.php">Back to browse</a></p></div>';
    require __DIR__ . '/footer.php';
    exit;
}

$uid  = is_logged_in() ? current_user()['id'] : 0;
$isUser = user_role() === 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    require_login();
    $action = $_POST['action'] ?? '';
    $self = 'Location: movie.php?id=' . $id;

    if ($isUser && $action === 'save_review') {
        $rating  = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5 || $comment === '' || mb_strlen($comment) > 2000) {
            flash('Choose a rating (1–5) and write a comment (max 2000 characters).', 'error');
        } else {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO ratings (user_id, movie_id, rating) VALUES (:u,:m,:r)
                           ON DUPLICATE KEY UPDATE rating = VALUES(rating)')
                ->execute([':u' => $uid, ':m' => $id, ':r' => $rating]);
            // Saving or editing a review sends it back to moderation.
            $pdo->prepare('INSERT INTO reviews (user_id, movie_id, rating, comment, review_status) VALUES (:u,:m,:r,:c,\'pending\')
                           ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment),
                           review_status = \'pending\', review_date = CURRENT_TIMESTAMP')
                ->execute([':u' => $uid, ':m' => $id, ':r' => $rating, ':c' => $comment]);
            $pdo->commit();
            flash('Thanks! Your review was saved and is waiting for moderator approval.');
        }
    } elseif ($isUser && $action === 'delete_review') {
        $pdo->prepare('DELETE FROM reviews WHERE user_id = :u AND movie_id = :m')->execute([':u' => $uid, ':m' => $id]);
        $pdo->prepare('DELETE FROM ratings WHERE user_id = :u AND movie_id = :m')->execute([':u' => $uid, ':m' => $id]);
        flash('Your review and rating were deleted.');
    } elseif ($isUser && $action === 'watch_add') {
        $pdo->prepare('INSERT IGNORE INTO watchlist (user_id, movie_id) VALUES (:u,:m)')->execute([':u' => $uid, ':m' => $id]);
        flash('Added to your watchlist.');
    } elseif ($isUser && $action === 'watch_remove') {
        $pdo->prepare('DELETE FROM watchlist WHERE user_id = :u AND movie_id = :m')->execute([':u' => $uid, ':m' => $id]);
        flash('Removed from your watchlist.');
    } elseif ($isUser && $action === 'report') {
        $reviewId = (int)($_POST['review_id'] ?? 0);
        $reason   = trim($_POST['reason'] ?? '');
        $chk = $pdo->prepare('SELECT review_id FROM reviews WHERE review_id = :r AND movie_id = :m AND user_id <> :u AND review_status = \'approved\'');
        $chk->execute([':r' => $reviewId, ':m' => $id, ':u' => $uid]);
        if ($chk->fetch() && $reason !== '' && mb_strlen($reason) <= 255) {
            $pdo->prepare('INSERT IGNORE INTO review_reports (review_id, user_id, reason) VALUES (:r,:u,:why)')
                ->execute([':r' => $reviewId, ':u' => $uid, ':why' => $reason]);
            flash('Report sent to the moderators.');
        } else {
            flash('Could not send the report. Please give a reason (max 255 characters).', 'error');
        }
    }
    header($self);
    exit;
}

$pageTitle = $movie['title'];
$sum = $pdo->prepare('SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS n FROM ratings WHERE movie_id = :m');
$sum->execute([':m' => $id]);
$sum = $sum->fetch();

$rv = $pdo->prepare('SELECT r.*, u.name FROM reviews r JOIN users u ON u.user_id = r.user_id
                     WHERE r.movie_id = :m AND r.review_status = \'approved\' ORDER BY r.review_date DESC');
$rv->execute([':m' => $id]);
$reviews = $rv->fetchAll();

$mine = null; $inWatch = false;
if ($isUser) {
    $s = $pdo->prepare('SELECT * FROM reviews WHERE user_id = :u AND movie_id = :m');
    $s->execute([':u' => $uid, ':m' => $id]);
    $mine = $s->fetch() ?: null;
    $s = $pdo->prepare('SELECT 1 FROM watchlist WHERE user_id = :u AND movie_id = :m');
    $s->execute([':u' => $uid, ':m' => $id]);
    $inWatch = (bool)$s->fetchColumn();
}
require __DIR__ . '/header.php';
?>
<p><a href="mainpage.php">&larr; Back to browse</a></p>
<div class="detail">
    <?php if (poster_src($movie['poster'])): ?>
        <img class="detail-poster" src="<?= e(poster_src($movie['poster'])) ?>" alt="Poster of <?= e($movie['title']) ?>">
    <?php else: ?>
        <div class="poster-ph detail-poster"><?= e($movie['title']) ?></div>
    <?php endif; ?>
    <div>
        <h1><?= e($movie['title']) ?></h1>
        <p class="muted"><?= e($movie['release_year']) ?> · <?= e($movie['genre_name']) ?></p>
        <p class="stars big"><?= $sum['n'] ? stars((float)$sum['avg_rating']) . ' ' . e($sum['avg_rating']) . '/5 (' . (int)$sum['n'] . ' ratings)' : 'No ratings yet' ?></p>
        <p><?= nl2br(e($movie['description'])) ?></p>

        <?php if ($isUser): ?>
            <form method="POST" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="movie_id" value="<?= $id ?>">
                <input type="hidden" name="action" value="<?= $inWatch ? 'watch_remove' : 'watch_add' ?>">
                <button class="btn"><?= $inWatch ? '✓ In watchlist – remove' : '+ Add to watchlist' ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($isUser): ?>
<section class="card">
    <h2><?= $mine ? 'Your review' : 'Write a review' ?></h2>
    <?php if ($mine): ?>
        <p class="muted">Status: <strong><?= e($mine['review_status']) ?></strong></p>
    <?php endif; ?>
    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="movie_id" value="<?= $id ?>">
        <input type="hidden" name="action" value="save_review">
        <div class="form-group">
            <label for="rating">Rating</label>
            <select name="rating" id="rating" required>
                <option value="">Select…</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>" <?= ($mine && (int)$mine['rating'] === $i) ? 'selected' : '' ?>><?= $i ?> – <?= str_repeat('★', $i) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="comment">Comment</label>
            <textarea name="comment" id="comment" rows="4" maxlength="2000" required><?= e($mine['comment'] ?? '') ?></textarea>
        </div>
        <button class="btn"><?= $mine ? 'Update review' : 'Submit review' ?></button>
    </form>
    <?php if ($mine): ?>
        <form method="POST" class="inline" onsubmit="return confirm('Delete your review and rating?');">
            <?= csrf_field() ?>
            <input type="hidden" name="movie_id" value="<?= $id ?>">
            <input type="hidden" name="action" value="delete_review">
            <button class="btn btn-danger">Delete my review</button>
        </form>
    <?php endif; ?>
</section>
<?php elseif (!is_logged_in()): ?>
    <p class="card"><a href="login.php">Log in</a> as a user to rate, review and save movies to your watchlist.</p>
<?php endif; ?>

<section>
    <h2>Reviews (<?= count($reviews) ?>)</h2>
    <?php if (!$reviews): ?><p class="muted">No approved reviews yet.</p><?php endif; ?>
    <?php foreach ($reviews as $r): ?>
        <article class="card review">
            <div><strong><?= e($r['name']) ?></strong> <span class="stars"><?= stars((float)$r['rating']) ?></span>
                <span class="muted"><?= e(substr($r['review_date'], 0, 10)) ?></span></div>
            <p><?= nl2br(e($r['comment'])) ?></p>
            <?php if ($isUser && (int)$r['user_id'] !== $uid): ?>
                <details>
                    <summary>Report</summary>
                    <form method="POST" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="movie_id" value="<?= $id ?>">
                        <input type="hidden" name="action" value="report">
                        <input type="hidden" name="review_id" value="<?= (int)$r['review_id'] ?>">
                        <input type="text" name="reason" placeholder="Reason" maxlength="255" required>
                        <button class="btn btn-small">Send report</button>
                    </form>
                </details>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
