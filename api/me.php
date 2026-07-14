<?php
/** Tiny endpoint: is a client logged in? Used to show/hide the "My Account" button. */
declare(strict_types=1);
require_once __DIR__ . '/../app/auth.php';
boot_session();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$c = client_user();
echo json_encode([
    'loggedIn' => (bool)$c,
    'name'     => $c['name'] ?? null,
]);
