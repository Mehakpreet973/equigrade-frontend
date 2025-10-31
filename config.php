<?php
// === Database Settings ===
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'equigrade');
define('DB_USER', 'root');
define('DB_PASS', '');

// === File Upload Settings ===
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024);

// === Allowed File Types ===
$ALLOWED_MIME = [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/msword',
    'text/plain'
];
?>
