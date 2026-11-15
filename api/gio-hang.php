<?php
/**
 * api/gio-hang.php - REST API for cart management via JSON
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    error_log("[API GioHang Error] " . $e->getMessage());
    echo json_encode(['loi' => 'Internal server error'], JSON_UNESCAPED_UNICODE);
    exit;
});

require_once __DIR__ . '/../inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$kho = new KhoSanPham(db());
$gio = new GioHang();

// Authentication required
$u = nguoiDung();
if (!$u) {
    http_response_code(401);
    echo json_encode([
        'loi' => 'Please sign in to add textbooks to your shopping cart!',
        'require_login' => true,
        'redirect' => 'dang-nhap.php'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Supports GET & POST
if (!in_array($method, ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    echo json_encode(['loi' => "Method $method not allowed"], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. GET: Overview
if ($method === 'GET') {
    http_response_code(200);
    echo json_encode([
        'so_mon'    => $gio->soMon(),
        'tong_tien' => $gio->tongTien($kho),
        'danh_sach' => $gio->danhSach()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 2. POST: Add item via JSON
$bodyRaw = file_get_contents('php://input');
$data = json_decode($bodyRaw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['loi' => 'Invalid JSON payload received'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$sl = filter_var($data['sl'] ?? 1, FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    http_response_code(400);
    echo json_encode(['loi' => 'Product ID is missing or invalid'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sp = $kho->timTheoId($id);
if ($sp === null) {
    http_response_code(404);
    echo json_encode(['loi' => "Book #$id not found"], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($sl === false || $sl <= 0) {
    http_response_code(422);
    echo json_encode(['loi' => 'Quantity must be a positive integer'], JSON_UNESCAPED_UNICODE);
    exit;
}

$hienTaiTrongGio = $gio->danhSach()[$id] ?? 0;
if (($hienTaiTrongGio + $sl) > $sp->tonKho) {
    http_response_code(422);
    echo json_encode([
        'loi'     => "Requested quantity exceeds available stock (In stock: {$sp->tonKho})",
        'ton_kho' => $sp->tonKho
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$gio->them($id, $sl, $kho);

http_response_code(200);
echo json_encode([
    'thanh_cong' => true,
    'thong_bao'  => "Added \"{$sp->ten}\" to cart!",
    'so_mon'     => $gio->soMon(),
    'san_pham'   => [
        'id'  => $sp->id,
        'ten' => $sp->ten,
        'gia' => $sp->gia
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
