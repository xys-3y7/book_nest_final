<?php
/**
 * inc/config.php — Cấu hình tập trung toàn hệ thống BookNest (Nhóm 12)
 * Chương 7: Bảo mật ứng dụng web - Nạp đầu tiên ở mọi trang và API
 */

// 1. Nạp Composer Autoload và thư viện vlucas/phpdotenv
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Đọc biến môi trường từ tệp .env ở thư mục gốc
$env = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$env->safeLoad(); // an toàn: không văng ngoại lệ nếu chưa có .env
$env->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME']);

// Biến điều khiển môi trường: APP_DEBUG (true/false)
define('DEBUG', (($_ENV['APP_DEBUG'] ?? '') === 'true'));
const MOI_TRUONG = DEBUG ? 'dev' : 'prod';

// 3. Cấu hình báo lỗi và ghi log
error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? '1' : '0');
ini_set('log_errors', '1');
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('error_log', $logDir . '/php-error.log');

// 4. Nạp các tệp trợ giúp, CSDL, xác thực và CSRF
require_once __DIR__ . '/ham.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/xac-thuc.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/ngon-ngu.php';

// 5. Header bảo mật gửi kèm mọi phản hồi HTTP
header("Content-Security-Policy: default-src 'self'; "
     . "script-src 'self' 'unsafe-inline'; "
     . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
     . "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; "
     . "img-src 'self' data: https:; "
     . "connect-src 'self' https://api.open-meteo.com; "
     . "object-src 'none'; base-uri 'self'; "
     . "form-action 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header_remove('X-Powered-By');

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// 6. Cấu hình Cookie phiên an toàn — Đặt TRƯỚC session_start()
ini_set('session.use_strict_mode', '1');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 7. Kiểm soát thời gian hoạt động của phiên: 30 phút không tương tác -> huỷ phiên
if (time() - ($_SESSION['hoat_dong'] ?? time()) > 30 * 60) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['hoat_dong'] = time();

// 8. Kiểm tra mã bảo vệ CSRF tập trung cho toàn bộ request POST (Form và AJAX/fetch)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !csrfHopLe()) {
    ghiLogBaoMat('csrf_sai', ['trang' => $_SERVER['REQUEST_URI'] ?? '/']);
    $loi = 'Trang đã hết hạn, hãy tải lại trang rồi gửi lại';
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json') || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'json')) {
        traJson(['loi' => $loi], 403);
    }
    http_response_code(403);
    echo "<!DOCTYPE html><html lang='vi'><head><meta charset='utf-8'><title>403 Forbidden - CSRF</title>";
    echo "<style>body{font-family:sans-serif;padding:40px;background:#f8fafc;color:#1e293b;text-align:center;}";
    echo ".box{background:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);display:inline-block;max-width:500px;}";
    echo "h1{color:#dc2626;font-size:24px;}p{margin:15px 0;line-height:1.5;}a{color:#2563eb;text-decoration:none;font-weight:bold;}</style></head><body>";
    echo "<div class='box'><h1>403 - Lỗi xác thực bảo mật (CSRF)</h1>";
    echo "<p>" . e($loi) . "</p>";
    echo "<p><a href='javascript:history.back()'>&larr; Quay lại</a> | <a href='index.php'>Về trang chủ</a></p></div></body></html>";
    exit;
}

// 9. Xử lý ngoại lệ toàn cục
set_exception_handler(function (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    if (DEBUG) {
        echo "<!DOCTYPE html><html lang='vi'><head><meta charset='utf-8'><title>Lỗi hệ thống (500 DEBUG)</title>";
        echo "<style>body{font-family:sans-serif;padding:30px;line-height:1.6;background:#f8fafc;color:#0f172a;}";
        echo "pre{background:#1e293b;color:#f8fafc;padding:15px;border-radius:6px;overflow-x:auto;}</style></head><body>";
        echo "<h1>Đã xảy ra ngoại lệ (Chế độ DEBUG đang bật)</h1>";
        echo "<p><strong>Thông điệp:</strong> " . e($e->getMessage()) . "</p>";
        echo "<p><strong>Vị trí:</strong> " . e($e->getFile()) . " dòng " . $e->getLine() . "</p>";
        echo "<h3>Chi tiết Stack Trace:</h3>";
        echo "<pre>" . e($e->getTraceAsString()) . "</pre>";
        echo "<p><a href='index.php'>&larr; Về trang chủ</a></p></body></html>";
        exit;
    } else {
        require __DIR__ . '/../500.php';
        exit;
    }
});
