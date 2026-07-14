<?php
require_once __DIR__ . '/../app/auth.php';
boot_session();
client_logout();
redirect('index.php');
