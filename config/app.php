<?php
// config/app.php
// ─────────────────────────────────────────────────────────────────────────────
// Application secrets — DO NOT commit to git (add config/app.php to .gitignore)
// On production: copy this file to server, update keys, never push to git.
// ─────────────────────────────────────────────────────────────────────────────

return [

    // 256-bit (32-byte) key for AES-256-GCM cookie encryption.
    // Generated: php -r "echo base64_encode(random_bytes(32));"
    'cookie_encryption_key' => base64_decode('yl1QYWF7YcrqNZRCULyqIZKnpExpfcCvdjW1E36392w='),

    // Separate HMAC-SHA256 signing key (encrypt-then-MAC defence-in-depth)
    'cookie_hmac_key'       => 'Sjqrx9+bKzCLOIYmxGHxIlN7S3hF0eM3R6z0SSDcKYg=',

];
