<?php
require_once __DIR__ . '/auth.php';
require_role(['admin', 'movie_moderator']);
$pageTitle = 'Manage Reviews';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $rid = (int)($_POST['review_id'] ?? 0);
    if ($action === 'approve' || $action === 'reject') {
        $pdo->prepare('UPDATE reviews SET review_status = :s WHERE review_id = :id')
            ->execute([':s' => $action === 'approve' ? 'approved' : 'rejected', ':id' => $rid]);
        flash('Review ' . ($action === 'approve' ? 'approved' : 'rejected') . '.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM reviews WHERE review_id = :id')->execute([':id' => $rid]);
        flash('Review deleted.');
    } elseif ($action === 'dismiss_report') {
        $pdo->prepare("UPDATE review_reports SET status = 'resolved' WHERE report_id = :id")
            ->execute([':id' => (int)($_POST['report_id'] ?? 0)]);
        flash('Report dismissed.');
    }
    header('Location: review.php');
    exit;
}

$filter = $_GET['status'] ?? 'pending';
if (!in_array($filter, ['pending', 'approved', 'rejected', 'all'], true)) { $filter = 'pending'; }
$sql = 'SELECT r.*, m.title, u.name FROM reviews r JOIN movies m ON m.movie_id = r.movie_id
        JOIN users u ON u.user_id = r.user_id';
$params = [];
if ($filter !== 'all') { $sql .= ' WHERE r.review_status = :s'; $params[':s'] = $filter; }
$stmt = $pdo->prepare($sql . ' ORDER BY r.review_date DESC');
$stmt->execute($params);
$reviews = $stmt->fetchAll();

$reports = $pdo->query("SELECT rp.report_id, rp.reason, rp.report_date, r.review_id, r.comment, m.title,
                               author.name AS author, reporter.name AS reporter
                        FROM review_reports rp
                        JOIN reviews r ON r.review_id = rp.review_id
                        JOIN movies m ON m.movie_id = r.movie_id
                        JOIN users author ON author.user_id = r.user_id
                        JOIN users reporter ON reporter.user_id = rp.user_id
                        WHERE rp.status = 'open' ORDER BY rp.report_date DESC")->fetchAll();
require __DIR__ . '/header.php';

function review_btn(string $action, int $rid, string $label, string $cls = ''): void {
    echo '<form method="POST" class="inline">' . csrf_field()
       . '<input type="hidden" name="action" value="' . e($action) . '">'
       . '<input type="hidden" name="review_id" value="' . $rid . '">'
       . '<button class="btn btn-small ' . $cls . '">' . e($label) . '</button></form> ';
}
?>
<h1>Manage Reviews</h1>

<h2>Reported reviews (<?= count($reports) ?>)</h2>
<?php if (!$reports): ?><p class="muted">No open reports.</p><?php else: ?>
<table class="table">
    <tr><th>Movie</th><th>Review</th><th>Reported by</th><th>Reason</th><th>Actions</th></tr>
    <?php foreach ($reports as $r): ?>
        <tr>
            <td><?= e($r['title']) ?></td>
            <td><strong><?= e($r['author']) ?>:</strong> <?= e($r['comment']) ?></td>
            <td><?= e($r['reporter']) ?></td>
            <td><?= e($r['reason']) ?></td>
            <td>
                <form method="POST" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="dismiss_report">
                    <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                    <button class="btn btn-small btn-secondary">Dismiss</button>
                </form>
                <?php review_btn('reject', (int)$r['review_id'], 'Reject review'); ?>
                <?php review_btn('delete', (int)$r['review_id'], 'Delete', 'btn-danger'); ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Reviews</h2>
<p class="tabs">
    <?php foreach (['pending', 'approved', 'rejected', 'all'] as $t): ?>
        <a class="<?= $filter === $t ? 'active' : '' ?>" href="review.php?status=<?= $t ?>"><?= ucfirst($t) ?></a>
    <?php endforeach; ?>
</p>
<?php if (!$reviews): ?><p class="muted">Nothing here.</p><?php else: ?>
<table class="table">
    <tr><th>Movie</th><th>User</th><th>Rating</th><th>Comment</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($reviews as $r): ?>
        <tr>
            <td><a href="movie.php?id=<?= $r['movie_id'] ?>"><?= e($r['title']) ?></a></td>
            <td><?= e($r['name']) ?></td>
            <td><?= (int)$r['rating'] ?>/5</td>
            <td><?= e($r['comment']) ?></td>
            <td><?= e($r['review_status']) ?></td>
            <td>
                <?php if ($r['review_status'] !== 'approved') review_btn('approve', (int)$r['review_id'], 'Approve'); ?>
                <?php if ($r['review_status'] !== 'rejected') review_btn('reject', (int)$r['review_id'], 'Reject', 'btn-secondary'); ?>
                <?php review_btn('delete', (int)$r['review_id'], 'Delete', 'btn-danger'); ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<p><a href="admin.php">&larr; Staff panel</a></p>
<?php require __DIR__ . '/footer.php'; ?>
