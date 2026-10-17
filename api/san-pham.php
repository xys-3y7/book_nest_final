<?php
/**
 * api/san-pham.php - REST API truy vấn dữ liệu sản phẩm / sách trả về JSON
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 6 (Bảng 1): traJson, 200, 400, 404, 405, 500 handler, JsonSerializable
 */

// Đặt header Content-Type JSON ngay từ đầu
header('Content-Type: application/json; charset=utf-8');

// Đăng ký Exception Handler riêng cho API để luôn trả về cấu trúc JSON mã 500 khi có lỗi máy chủ
set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    error_log("[API SanPham Error] " . $e->getMessage());
    echo json_encode(['loi' => 'Lỗi máy chủ nội bộ'], JSON_UNESCAPED_UNICODE);
    exit;
});

require_once __DIR__ . '/../inc/config.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham(db());

// API chỉ chấp nhận phương thức GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['loi' => 'Phương thức không được hỗ trợ (Chỉ chấp nhận GET)'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Trường hợp 1: Tra cứu một sản phẩm theo ID (?id=...)
if (isset($_GET['id'])) {
    $rawId = $_GET['id'];
    $id = filter_var($rawId, FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        http_response_code(400);
        echo json_encode(['loi' => 'Mã sản phẩm id không hợp lệ (Phải là số nguyên dương)'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sp = $kho->timTheoId($id);
    if ($sp === null) {
        http_response_code(404);
        echo json_encode(['loi' => "Không tìm thấy sản phẩm với mã #$id"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(200);
    // Tự động kích hoạt JsonSerializable trong class SanPham
    echo json_encode(['du_lieu' => $sp], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Trường hợp 2: Tìm kiếm tức thời theo từ khóa (?q=...)
$q = trim($_GET['q'] ?? '');
$ketQua = $kho->timKiemNhanh($q, 8);

http_response_code(200);
echo json_encode([
    'tong_so' => count($ketQua),
    'du_lieu' => $ketQua
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
