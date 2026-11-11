<?php
/**
 * inc/csrf.php — Quản lý mã chống tấn công CSRF (Cross-Site Request Forgery)
 * Nhóm 12 - Lớp 24CNTT3 - Chương 7: Bảo mật ứng dụng web
 */

/**
 * Lấy hoặc khởi tạo mã CSRF ngẫu nhiên an toàn mã hóa cho phiên hiện tại
 */
function csrfToken(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

/**
 * Tạo trường ẩn chứa mã CSRF để nhúng vào MỌI biểu mẫu gửi qua POST
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . csrfToken() . '">';
}

/**
 * Kiểm tra tính hợp lệ của mã CSRF gửi lên từ POST form hoặc header X-CSRF-Token
 */
function csrfHopLe(): bool
{
    $gui = $_POST['csrf'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($gui) && !empty($gui) && hash_equals(csrfToken(), $gui);
}
