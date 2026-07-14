<?php
/**
 * Toggle a saved/wishlist item for the logged-in client.
 * POST type=land|house|project & id=NN  -> {ok, saved:bool, auth:bool}
 * If not logged in, returns auth:false so the frontend can redirect to login.
 */
declare(strict_types=1);
require_once __DIR__ . '/../app/auth.php';
boot_session();

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$client = client_user();
if (!$client) {
    json_out(['ok' => false, 'auth' => false, 'error' => 'Please sign in to save properties.'], 401);
}

$type = post('type');
$id   = post_int('id');
if (!in_array($type, ['land','house','project'], true) || $id <= 0) {
    json_out(['ok' => false, 'error' => 'Invalid item'], 422);
}

$pdo = db();
$sel = $pdo->prepare('SELECT id FROM saved_listings WHERE client_id=? AND item_type=? AND item_id=?');
$sel->execute([$client['id'], $type, $id]);
$existing = $sel->fetchColumn();

if ($existing) {
    $pdo->prepare('DELETE FROM saved_listings WHERE id=?')->execute([$existing]);
    json_out(['ok' => true, 'auth' => true, 'saved' => false]);
}

$pdo->prepare('INSERT INTO saved_listings (client_id,item_type,item_id) VALUES (?,?,?)')
    ->execute([$client['id'], $type, $id]);
json_out(['ok' => true, 'auth' => true, 'saved' => true]);
