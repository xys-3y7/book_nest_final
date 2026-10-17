<?php
/**
 * dang-xuat.php - Hủy phiên đăng nhập và đăng xuất an toàn khỏi hệ thống BookNest
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

require_once __DIR__ . '/inc/config.php';

// Xóa toàn bộ thông tin định danh phiên
unset($_SESSION['uid']);

// Cấp lại Session ID mới để hủy bỏ phiên cũ
session_regenerate_id(true);

$_SESSION['flash'] = 'Bạn đã đăng xuất khỏi hệ thống thành công.';
header('Location: index.php');
exit;
