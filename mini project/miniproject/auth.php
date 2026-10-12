<?php
// Session, role and CSRF helpers shared by every page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool   { return current_user() !== null; }
function user_role(): string    { return current_user()['role'] ?? 'guest'; }
function is_admin(): bool       { return user_role() === 'admin'; }
function is_staff(): bool       { return in_array(user_role(), ['admin', 'movie_moderator'], true); }

function require_login(): void {
    if (!is_logged_in()) { header('Location: login.php'); exit; }
}
function require_role(array $roles): void {
    require_login();
    if (!in_array(user_role(), $roles, true)) {
        http_response_code(403);
        $pageTitle = 'Access denied';
        require __DIR__ . '/header.php';
        echo '<div class="card"><h1>Access denied</h1><p>You do not have permission to view this page.</p></div>';
        require __DIR__ . '/footer.php';
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Invalid or expired form. Go back, refresh the page and try again.');
    }
}

function flash(string $msg, string $type = 'success'): void { $_SESSION['flash'] = [$msg, $type]; }
function pull_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function login_user(array $row): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'    => (int)$row['user_id'],
        'name'  => $row['name'],
        'email' => $row['email'],
        'role'  => $row['role'],
    ];
}

function role_label(string $role): string {
    return ['admin' => 'Admin', 'movie_moderator' => 'Movie Moderator', 'user' => 'User'][$role] ?? $role;
}

function stars(float $n): string {
    $full = (int)round($n);
    return str_repeat('★', $full) . str_repeat('☆', 5 - $full);
}

function poster_src(?string $p): string {
    if (!$p) return '';
    if (preg_match('#^https?://#i', $p)) return $p;
    return 'posters/' . rawurlencode(basename($p));
}
