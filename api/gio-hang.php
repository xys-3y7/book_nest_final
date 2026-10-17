<?php
/**
 * api/gio-hang.php - REST API quản lý giỏ hàng phía máy chủ qua JSON
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 6 (Bảng 1): php://input, mã trạng thái 200, 400, 404, 405, 422, 500
 */

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    error_log("[API GioHang Error] " . $e->getMessage());
    echo json_encode(['loi' => 'Lỗi máy chủ nội bộ'], JSON_UNESCAPED_UNICODE);
    exit;
});

require_once __DIR__ . '/../inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$kho = new KhoSanPham(db());
$gio = new GioHang();

$method = $_SERVER['REQUEST_METHOD'];

// Chỉ hỗ trợ GET (xem giỏ) và POST (thêm vào giỏ)
if (!in_array($method, ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    echo json_encode(['loi' => "Phương thức $method không được hỗ trợ"], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. GET: Lấy thông tin tổng quan giỏ hàng
if ($method === 'GET') {
    http_response_code(200);
    echo json_encode([
        'so_mon'    => $gio->soMon(),
        'tong_tien' => $gio->tongTien($kho),
        'danh_sach' => $gio->danhSach()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 2. POST: Thêm sản phẩm vào giỏ hàng qua thân JSON (php://input)
$bodyRaw = file_get_contents('php://input');
$data = json_decode($bodyRaw, true);

// Kiểm tra lỗi cú pháp JSON
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['loi' => 'Dữ liệu gửi lên không phải là định dạng JSON hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$sl = filter_var($data['sl'] ?? 1, FILTER_VALIDATE_INT);

// Kiểm tra mã sản phẩm
if ($id === false || $id === null || $id <= 0) {
    http_response_code(400);
    echo json_encode(['loi' => 'Mã sản phẩm (id) bị thiếu hoặc không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra sản phẩm có tồn tại trong CSDL không
$sp = $kho->timTheoId($id);
if ($sp === null) {
    http_response_code(404);
    echo json_encode(['loi' => "Sản phẩm mang mã #$id không tồn tại"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra số lượng hợp lệ (phải là số dương)
if ($sl === false || $sl <= 0) {
    http_response_code(422);
    echo json_encode(['loi' => 'Số lượng sách yêu cầu phải là số nguyên lớn hơn 0'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra số lượng yêu cầu so với tồn kho thực tế
$hienTaiTrongGio = $gio->danhSach()[$id] ?? 0;
if (($hienTaiTrongGio + $sl) > $sp->tonKho) {
    http_response_code(422);
    echo json_encode([
        'loi'     => "Số lượng yêu cầu vượt quá tồn kho hiện có (Kho còn: {$sp->tonKho} cuốn)",
        'ton_kho' => $sp->tonKho
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Thêm vào giỏ hàng an toàn
$gio->them($id, $sl, $kho);

http_response_code(200);
echo json_encode([
    'thanh_cong' => true,
    'thong_bao'  => "Đã thêm \"{$sp->ten}\" vào giỏ hàng!",
    'so_mon'     => $gio->soMon(),
    'san_pham'   => [
        'id'  => $sp->id,
        'ten' => $sp->ten,
        'gia' => $sp->gia
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
