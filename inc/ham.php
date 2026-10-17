<?php
/**
 * inc/ham.php - Các hàm trợ giúp dùng chung cho website BookNest
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

/**
 * Thoát ký tự đặc biệt trong chuỗi để in an toàn ra HTML chống XSS
 * Sử dụng htmlspecialchars với ENT_QUOTES và UTF-8
 */
function e(?string $chuoi): string
{
    return htmlspecialchars((string)$chuoi, ENT_QUOTES, 'UTF-8');
}

/**
 * Định dạng tiền tệ Việt Nam (VNĐ)
 */
function vnd(int|float $so): string
{
    return number_format($so, 0, ',', '.') . ' VNĐ';
}

/**
 * Lấy đường dẫn ảnh sách an toàn có ảnh mặc định thay thế
 */
function anhSach(?string $anh): string
{
    if (!empty($anh) && file_exists(__DIR__ . '/../uploads/' . $anh)) {
        return 'uploads/' . $anh;
    }
    if (!empty($anh) && file_exists(__DIR__ . '/../images/' . $anh)) {
        return 'images/' . $anh;
    }
    return 'images/sach-laptrinh-web.png';
}

/**
 * Phản hồi dữ liệu JSON chuẩn REST API kèm mã HTTP Status
 */
function traJson(mixed $duLieu, int $maTrangThai = 200, array $headers = []): void
{
    http_response_code($maTrangThai);
    header('Content-Type: application/json; charset=utf-8');
    foreach ($headers as $k => $v) {
        header("$k: $v");
    }
    echo json_encode($duLieu, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Kiểm tra dữ liệu đầu vào chỉ nhận chuỗi an toàn (?q[]=ao -> chuỗi rỗng)
 */
function chuoi(mixed $v): string
{
    return is_string($v) ? $v : '';
}

/**
 * Ghi nhật ký sự kiện bảo mật vào logs/bao-mat.log
 * Tuyệt đối không ghi mật khẩu, mã phiên, mã CSRF
 */
function ghiLogBaoMat(string $suKien, array $chiTiet = []): void
{
    $chiTiet += [
        'ip'  => $_SERVER['REMOTE_ADDR'] ?? '-',
        'uid' => $_SESSION['uid'] ?? null
    ];
    $json = json_encode($chiTiet, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $dong = date('Y-m-d H:i:s') . " $suKien $json";
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    error_log($dong . PHP_EOL, 3, $logDir . '/bao-mat.log');
}
