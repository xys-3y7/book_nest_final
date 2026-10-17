<?php
/**
 * dang-xuat.php — Đăng xuất an toàn chỉ chấp nhận phương thức POST có mã CSRF
 * Nhóm 12 - Lớp 24CNTT3 - Chương 7: Bảo mật ứng dụng web
 */

require_once __DIR__ . '/inc/config.php';

// Kiểm tra nghiêm ngặt phương thức HTTP: Chỉ cho phép POST, chặn hoàn toàn GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('405 Method Not Allowed — Đăng xuất bắt buộc phải sử dụng phương thức POST kèm mã CSRF.');
}

dangXuat();

header('Location: index.php');
exit;
