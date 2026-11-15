<?php
/**
 * dang-xuat.php — Secure logout via POST with CSRF token
 * Team 12 - Class 24CNTT3 - Security & Authentication
 */

require_once __DIR__ . '/inc/config.php';

// Strict HTTP method check: POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('405 Method Not Allowed — Logout must use POST method with a valid CSRF token.');
}

dangXuat();

header('Location: index.php');
exit;
