<?php
/**
 * Shared helpers used across public site, admin and client portal.
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/* ---------------- Output / escaping ---------------- */

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escape an attribute value (alias of e for readability). */
function ea(?string $s): string { return e($s); }

/** Very small allow-list HTML sanitiser for admin-authored rich text bodies. */
function safe_html(?string $html): string
{
    if ($html === null || $html === '') return '';
    // Admins are trusted; strip <script>/<style>/<iframe> and on* handlers as defence-in-depth.
    $html = preg_replace('#<\s*(script|style|iframe|object|embed)\b.*?<\s*/\s*\1\s*>#is', '', $html);
    $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
    $html = preg_replace('#(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*\2#i', '$1="#"', $html);
    return $html;
}

/* ---------------- Strings ---------------- */

function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text) ?? $text;
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower(trim($text, '-'));
    $text = preg_replace('~[^-a-z0-9]+~', '', $text) ?? $text;
    return $text !== '' ? $text : 'item-' . substr(md5((string)mt_rand()), 0, 6);
}

/** Ensure a slug is unique within a table (optionally excluding a row id). */
function unique_slug(string $base, string $table, int $ignoreId = 0): string
{
    $pdo  = db();
    $slug = $base;
    $i    = 2;
    while (true) {
        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE slug = ?";
        $params = [$slug];
        if ($ignoreId > 0) { $sql .= " AND id <> ?"; $params[] = $ignoreId; }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ((int)$stmt->fetchColumn() === 0) return $slug;
        $slug = $base . '-' . $i++;
    }
}

function excerpt(string $text, int $words = 28): string
{
    $text = trim(strip_tags($text));
    $parts = preg_split('/\s+/', $text) ?: [];
    if (count($parts) <= $words) return $text;
    return implode(' ', array_slice($parts, 0, $words)) . '…';
}

/* ---------------- Money / dates ---------------- */

function ksh($amount): string
{
    if ($amount === null || $amount === '') return 'Price on request';
    return 'KSh ' . number_format((float)$amount, 0, '.', ',');
}

function nice_date($dt): string
{
    if (!$dt) return '';
    $ts = is_numeric($dt) ? (int)$dt : strtotime((string)$dt);
    return $ts ? date('j M Y', $ts) : '';
}

function nice_datetime($dt): string
{
    if (!$dt) return '';
    $ts = is_numeric($dt) ? (int)$dt : strtotime((string)$dt);
    return $ts ? date('j M Y, g:i a', $ts) : '';
}

function time_ago($dt): string
{
    $ts = is_numeric($dt) ? (int)$dt : strtotime((string)$dt);
    if (!$ts) return '';
    $diff = time() - $ts;
    if ($diff < 60)      return 'just now';
    if ($diff < 3600)    return floor($diff / 60) . 'm ago';
    if ($diff < 86400)   return floor($diff / 3600) . 'h ago';
    if ($diff < 604800)  return floor($diff / 86400) . 'd ago';
    return nice_date($dt);
}

/* ---------------- HTTP ---------------- */

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key, $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : (string)$default;
}

function post_int(string $key, int $default = 0): int
{
    return isset($_POST[$key]) && $_POST[$key] !== '' ? (int)$_POST[$key] : $default;
}

function get(string $key, $default = ''): string
{
    return isset($_GET[$key]) ? trim((string)$_GET[$key]) : (string)$default;
}

function json_out($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* ---------------- Flash messages ---------------- */

function flash(string $msg, string $type = 'success'): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) return;
    $_SESSION['_flash'][] = ['msg' => $msg, 'type' => $type];
}

function take_flashes(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) return [];
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/* ---------------- Settings (key/value store) ---------------- */

function settings(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    try {
        foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) {
        $cache = [];
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return $s[$key] ?? $default;
}

function save_setting(string $key, string $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$key, $value]);
}

/* ---------------- Pagination ---------------- */

function paginate(int $total, int $perPage, int $page): array
{
    $pages = max(1, (int)ceil($total / max(1, $perPage)));
    $page  = max(1, min($page, $pages));
    return [
        'total'   => $total,
        'perPage' => $perPage,
        'page'    => $page,
        'pages'   => $pages,
        'offset'  => ($page - 1) * $perPage,
        'hasPrev' => $page > 1,
        'hasNext' => $page < $pages,
    ];
}

/* ---------------- File uploads ---------------- */

/**
 * Handle a single uploaded file from $_FILES[$field].
 * Returns the PUBLIC url path (e.g. /uploads/land/abc.jpg) or null.
 * On validation error, pushes a flash and returns null.
 */
function handle_upload(string $field, string $subdir = 'misc', bool $docs = false): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash('Upload failed (error code ' . $file['error'] . ').', 'error');
        return null;
    }

    $maxBytes = (int)config('uploads.max_bytes');
    if ($file['size'] > $maxBytes) {
        flash('File too large. Max ' . round($maxBytes / 1048576) . ' MB.', 'error');
        return null;
    }

    $allowed = $docs ? config('uploads.allowed_docs') : config('uploads.allowed_ext');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        flash('File type ".' . $ext . '" not allowed.', 'error');
        return null;
    }

    $baseDir = rtrim((string)config('uploads.dir'), '/\\');
    $destDir = $baseDir . '/' . trim($subdir, '/');
    if (!is_dir($destDir) && !@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        flash('Upload folder is not writable: ' . $destDir, 'error');
        return null;
    }

    $name = date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;
    $dest = $destDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        flash('Could not save uploaded file.', 'error');
        return null;
    }

    return rtrim((string)config('uploads.url'), '/') . '/' . trim($subdir, '/') . '/' . $name;
}

/** Delete a previously-uploaded file given its public url path. */
function delete_upload(?string $publicUrl): void
{
    if (!$publicUrl) return;
    $urlPrefix = rtrim((string)config('uploads.url'), '/');
    if (strpos($publicUrl, $urlPrefix . '/') !== 0) return; // only touch our uploads
    $rel  = substr($publicUrl, strlen($urlPrefix));
    $path = rtrim((string)config('uploads.dir'), '/\\') . $rel;
    if (is_file($path)) @unlink($path);
}

/* ---------------- Misc ---------------- */

function base_url(string $path = ''): string
{
    $base = rtrim((string)config('site.base_url'), '/');
    return $base . '/' . ltrim($path, '/');
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function set_old(array $data): void
{
    if (session_status() === PHP_SESSION_ACTIVE) $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

/* ---------------- Client saved items ---------------- */

/** Return a client's saved land/houses/projects, enriched with title, price, image, url. */
function client_saved_items(int $clientId): array
{
    $map = [
        'land'    => ['land_listings', 'land-detail.php'],
        'house'   => ['houses',        'house-detail.php'],
        'project' => ['projects',      'project-detail.php'],
    ];
    $out = [];
    $sv = db()->prepare('SELECT id, item_type, item_id FROM saved_listings WHERE client_id=? ORDER BY created_at DESC');
    $sv->execute([$clientId]);
    foreach ($sv as $row) {
        if (!isset($map[$row['item_type']])) continue;
        [$tbl, $page] = $map[$row['item_type']];
        $hasPrice = $row['item_type'] !== 'project';
        $cols = 'title, slug, cover_image' . ($hasPrice ? ', price' : '');
        $q = db()->prepare("SELECT $cols FROM `$tbl` WHERE id=?");
        $q->execute([$row['item_id']]);
        $it = $q->fetch();
        if (!$it) continue;
        $out[] = [
            'save_id' => (int)$row['id'],
            'type'    => $row['item_type'],
            'title'   => $it['title'],
            'price'   => $hasPrice ? $it['price'] : null,
            'image'   => $it['cover_image'],
            'url'     => '../' . $page . '?slug=' . $it['slug'],
        ];
    }
    return $out;
}
