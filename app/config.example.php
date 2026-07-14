<?php
/**
 * LANDPLAN.CO.KE — configuration
 * Copy this file to  app/config.php  and fill in your cPanel values.
 * config.php is git-ignored so real credentials never leave your server.
 */
return [
    // ----- Database (create in cPanel → MySQL Databases) -----
    'db' => [
        'host'    => 'localhost',            // almost always 'localhost' on cPanel
        'name'    => 'cpaneluser_landplan',  // the database you create
        'user'    => 'cpaneluser_lpuser',    // the DB user you assign to it
        'pass'    => 'CHANGE_ME',            // that user's password
        'charset' => 'utf8mb4',
    ],

    // ----- Site -----
    'site' => [
        'name'     => 'Landplan.co.ke',
        'base_url' => 'https://yourdomain.co.ke',  // final domain, no trailing slash
        'timezone' => 'Africa/Nairobi',
    ],

    // ----- Contact-form email (lead notifications) -----
    'mail' => [
        'to'        => 'info@yourdomain.co.ke',    // where enquiries are sent
        'from'      => 'no-reply@yourdomain.co.ke', // must be a mailbox on your domain
        'from_name' => 'Landplan Website',
    ],

    // ----- Security -----
    'security' => [
        'session_name' => 'landplan_sess',
        // Used to sign CSRF tokens — set to a long random string (50+ chars):
        'app_key'      => 'CHANGE_ME_TO_A_LONG_RANDOM_STRING',
    ],

    // ----- Uploads -----
    'uploads' => [
        'dir'          => __DIR__ . '/../uploads',      // filesystem path (writable)
        'url'          => '/uploads',                    // public URL prefix
        'max_bytes'    => 6 * 1024 * 1024,               // 6 MB per file
        'allowed_ext'  => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'allowed_docs' => ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'],
    ],
];
