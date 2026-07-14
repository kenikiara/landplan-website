<?php
/**
 * Public lead / enquiry capture.
 * Accepts POST (form or JSON). Stores the lead and emails a notification.
 * Fields: name*, phone*, email, interest, message, source, item_type, item_id, company(honeypot)
 */
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

// Accept JSON bodies too
$in = $_POST;
if (empty($in) && str_contains((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    $in = json_decode((string)file_get_contents('php://input'), true) ?: [];
}
$f = fn(string $k) => trim((string)($in[$k] ?? ''));

// Honeypot — bots fill hidden fields. Pretend success.
if ($f('company') !== '') json_out(['ok' => true]);

$name    = $f('name');
$phone   = $f('phone');
$email   = strtolower($f('email'));
$message = $f('message');
$interest= $f('interest');
$source  = $f('source') ?: 'Website';

if ($name === '' || ($phone === '' && $email === '')) {
    json_out(['ok' => false, 'error' => 'Please provide your name and a phone number or email.'], 422);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(['ok' => false, 'error' => 'Please enter a valid email address.'], 422);
}

$itemType = in_array($f('item_type'), ['land','house','project'], true) ? $f('item_type') : null;
$itemId   = $f('item_id') !== '' ? (int)$f('item_id') : null;

// Link to a logged-in client if there is a session
$clientId = null;
try {
    require_once __DIR__ . '/../app/auth.php';
    boot_session();
    $c = client_user();
    if ($c) $clientId = (int)$c['id'];
} catch (Throwable $e) {}

try {
    $stmt = db()->prepare(
        'INSERT INTO leads (name,email,phone,interest,message,source,item_type,item_id,client_id)
         VALUES (?,?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([$name, $email ?: null, $phone, $interest, $message, $source, $itemType, $itemId, $clientId]);
} catch (Throwable $e) {
    error_log('Lead insert failed: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'Something went wrong. Please try again or call us.'], 500);
}

// Notify staff by email (best-effort; never blocks the response result)
$to = setting('lead_notify_email', (string)config('mail.to'));
if ($to && filter_var($to, FILTER_VALIDATE_EMAIL)) {
    $fromName = (string)(config('mail.from_name') ?: 'Landplan Website');
    $from     = (string)(config('mail.from') ?: ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')));
    $subject  = 'New enquiry from ' . $name;
    $body  = "You have a new website enquiry:\n\n";
    $body .= "Name:     $name\n";
    $body .= "Phone:    $phone\n";
    $body .= "Email:    " . ($email ?: '-') . "\n";
    $body .= "Interest: " . ($interest ?: '-') . "\n";
    $body .= "Source:   $source\n";
    if ($itemType) $body .= "Property: $itemType #$itemId\n";
    $body .= "\nMessage:\n" . ($message ?: '-') . "\n";
    $headers  = 'From: ' . $fromName . ' <' . $from . ">\r\n";
    if ($email) $headers .= 'Reply-To: ' . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    @mail($to, $subject, $body, $headers);
}

json_out(['ok' => true, 'message' => 'Thank you! Your enquiry has been received. Our team will be in touch shortly.']);
