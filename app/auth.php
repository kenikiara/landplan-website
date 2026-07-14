<?php
/**
 * Authentication, sessions and CSRF for both the admin (staff) area
 * and the client portal. Two independent identities live in one session:
 *   $_SESSION['admin_id']  — a staff user (users table)
 *   $_SESSION['client_id'] — a portal client (clients table)
 */
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function boot_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $name = (string)(config('security.session_name') ?: 'landplan_sess');
    session_name($name);
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $https,
    ]);
    session_start();
}

/* ---------------- CSRF ---------------- */

function csrf_token(): string
{
    boot_session();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    boot_session();
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || $sent === '' || !hash_equals($_SESSION['_csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Session expired or invalid request token. Go back and try again.');
    }
}

/* ---------------- Admin (staff) auth ---------------- */

function admin_user(): ?array
{
    boot_session();
    if (empty($_SESSION['admin_id'])) return null;
    static $u = null;
    if ($u !== null) return $u;
    $stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $u = $stmt->fetch() ?: null;
    return $u;
}

function admin_login(string $email, string $password): bool
{
    boot_session();
    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([strtolower(trim($email))]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) return false;
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$row['id'];
    db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([(int)$row['id']]);
    activity_log('login', 'user', (int)$row['id']);
    return true;
}

function require_admin(string $role = ''): array
{
    $u = admin_user();
    if (!$u) redirect('index.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? 'dashboard.php'));
    if ($role !== '' && $u['role'] !== $role) {
        http_response_code(403);
        exit('You do not have permission to view this page.');
    }
    return $u;
}

function admin_logout(): void
{
    boot_session();
    unset($_SESSION['admin_id']);
}

/* ---------------- Client (portal) auth ---------------- */

function client_user(): ?array
{
    boot_session();
    if (empty($_SESSION['client_id'])) return null;
    static $c = null;
    if ($c !== null) return $c;
    $stmt = db()->prepare('SELECT id, name, email, phone FROM clients WHERE id = ? AND password_hash IS NOT NULL');
    $stmt->execute([$_SESSION['client_id']]);
    $c = $stmt->fetch() ?: null;
    return $c;
}

function client_login(string $email, string $password): bool
{
    boot_session();
    $stmt = db()->prepare('SELECT id, password_hash FROM clients WHERE email = ? AND password_hash IS NOT NULL');
    $stmt->execute([strtolower(trim($email))]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, (string)$row['password_hash'])) return false;
    session_regenerate_id(true);
    $_SESSION['client_id'] = (int)$row['id'];
    return true;
}

/**
 * Register a portal account. If a client record with this email already
 * exists (e.g. created by staff), attach a password to it; otherwise create one.
 * Returns [success, message].
 */
function client_register(string $name, string $email, string $phone, string $password): array
{
    boot_session();
    $email = strtolower(trim($email));
    $pdo = db();

    $stmt = $pdo->prepare('SELECT id, password_hash FROM clients WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($existing) {
        if (!empty($existing['password_hash'])) {
            return [false, 'An account with this email already exists. Please log in.'];
        }
        $up = $pdo->prepare('UPDATE clients SET name = ?, phone = ?, password_hash = ? WHERE id = ?');
        $up->execute([$name, $phone, $hash, $existing['id']]);
        $_SESSION['client_id'] = (int)$existing['id'];
    } else {
        $ins = $pdo->prepare('INSERT INTO clients (name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
        $ins->execute([$name, $email, $phone, $hash]);
        $_SESSION['client_id'] = (int)$pdo->lastInsertId();
    }
    session_regenerate_id(true);
    return [true, 'Welcome to your Landplan account.'];
}

function require_client(): array
{
    $c = client_user();
    if (!$c) redirect('index.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? 'dashboard.php'));
    return $c;
}

function client_logout(): void
{
    boot_session();
    unset($_SESSION['client_id']);
}

/* ---------------- Activity log ---------------- */

function activity_log(string $action, string $entity = '', ?int $entityId = null, string $detail = ''): void
{
    try {
        $u = null;
        if (!empty($_SESSION['admin_id'])) $u = (int)$_SESSION['admin_id'];
        $stmt = db()->prepare(
            'INSERT INTO activity_log (user_id, action, entity, entity_id, detail, ip)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $u, $action, $entity, $entityId, $detail,
            substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        ]);
    } catch (Throwable $e) {
        // logging must never break a request
    }
}
