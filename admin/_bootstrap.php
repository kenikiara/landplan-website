<?php
/**
 * Admin bootstrap — include at the very top of every admin page.
 * Sets up sessions, timezone and the app core. Does NOT force login
 * (so index.php / setup.php can include it too). Use _guard.php to require login.
 */
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';

date_default_timezone_set((string)(config('site.timezone') ?: 'Africa/Nairobi'));
boot_session();

/** Count of new (unhandled) leads — shown as a sidebar badge. */
function new_leads_count(): int
{
    try {
        return (int)db()->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/** Whether any admin account exists yet (controls setup.php). */
function admin_exists(): bool
{
    try {
        return (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}
