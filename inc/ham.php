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
    if (empty($anh)) {
        return 'images/sach-laptrinh-web.png';
    }
    // Hỗ trợ link ảnh trực tuyến (URL http:// hoặc https://)
    if (str_starts_with($anh, 'http://') || str_starts_with($anh, 'https://')) {
        return $anh;
    }
    if (file_exists(__DIR__ . '/../uploads/' . $anh)) {
        return 'uploads/' . $anh;
    }
    if (file_exists(__DIR__ . '/../images/' . $anh)) {
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

/**
 * Kiểm tra xem cuốn sách có nằm trong danh sách yêu thích của người dùng không (Kết nối CSDL MySQL)
 */
function daYeuThich(int $id): bool
{
    $u = nguoiDung();
    if ($u) {
        try {
            $st = db()->prepare('SELECT 1 FROM yeu_thich WHERE nguoi_dung_id = ? AND san_pham_id = ?');
            $st->execute([(int)$u['id'], $id]);
            return (bool)$st->fetchColumn();
        } catch (\Throwable $e) {
            // fallback session
        }
    }
    return in_array($id, $_SESSION['yeu_thich'] ?? [], true);
}

function laYeuThich(int $id): bool
{
    return daYeuThich($id);
}

/**
 * Đếm số lượng sản phẩm trong danh sách yêu thích (Kết nối CSDL MySQL)
 */
function soLuongYeuThich(): int
{
    $u = nguoiDung();
    if ($u) {
        try {
            $st = db()->prepare('SELECT COUNT(*) FROM yeu_thich WHERE nguoi_dung_id = ?');
            $st->execute([(int)$u['id']]);
            return (int)$st->fetchColumn();
        } catch (\Throwable $e) {
            // fallback session
        }
    }
    return count($_SESSION['yeu_thich'] ?? []);
}

/**
 * Lấy danh sách ID các sản phẩm yêu thích (Kết nối CSDL MySQL)
 */
function layDanhSachYeuThich(): array
{
    $u = nguoiDung();
    if ($u) {
        try {
            $st = db()->prepare('SELECT san_pham_id FROM yeu_thich WHERE nguoi_dung_id = ? ORDER BY ngay_tao DESC');
            $st->execute([(int)$u['id']]);
            return array_map('intval', $st->fetchAll(\PDO::FETCH_COLUMN));
        } catch (\Throwable $e) {
            // fallback session
        }
    }
    return $_SESSION['yeu_thich'] ?? [];
}

/**
 * Chuyển đổi tên danh mục hoặc mã danh mục sang tiếng Anh cho giao diện hiển thị
 */
function layTenDanhMucEn(int|string|null $id, ?string $tenGoc = ''): string
{
    $bangId = [
        1 => 'Information Technology',
        2 => 'Economics & Entrepreneurship',
        3 => 'Literature & Arts',
        4 => 'Life Skills & Development',
        5 => 'Foreign Languages & Study Abroad',
        'cong-nghe-thong-tin' => 'Information Technology',
        'kinh-te-khoi-nghiep' => 'Economics & Entrepreneurship',
        'van-hoc-nghe-thuat'  => 'Literature & Arts',
        'ky-nang-song'        => 'Life Skills & Development',
        'ngoai-ngu-du-hoc'     => 'Foreign Languages & Study Abroad',
    ];

    if ($id !== null && isset($bangId[$id])) {
        return $bangId[$id];
    }

    $bangTen = [
        'Công nghệ thông tin'        => 'Information Technology',
        'Kinh tế & Khởi nghiệp'      => 'Economics & Entrepreneurship',
        'Văn học & Nghệ thuật'       => 'Literature & Arts',
        'Kỹ năng sống & Phát triển'  => 'Life Skills & Development',
        'Ngoại ngữ & Du học'         => 'Foreign Languages & Study Abroad',
        'Giáo trình'                 => 'Textbook',
        'Chuyên ngành'               => 'Category'
    ];

    if ($tenGoc && isset($bangTen[$tenGoc])) {
        return $bangTen[$tenGoc];
    }

    return $tenGoc ?: ($id ? "Category $id" : '');
}

/**
 * Generate a unique URL slug for category name
 */
function taoSlugCategory(string $str, ?PDO $db = null): string
{
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
    $str = preg_replace('/(đ)/', 'd', $str);
    $str = preg_replace('/[^a-z0-9\-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    $slugGoc = trim($str, '-');
    if ($slugGoc === '') {
        $slugGoc = 'category-' . time();
    }
    if (!$db) {
        return $slugGoc;
    }
    $slug = $slugGoc;
    $count = 1;
    while (true) {
        $st = $db->prepare('SELECT COUNT(*) FROM danh_muc WHERE slug = ?');
        $st->execute([$slug]);
        if ((int)$st->fetchColumn() === 0) {
            break;
        }
        $count++;
        $slug = $slugGoc . '-' . $count;
    }
    return $slug;
}


