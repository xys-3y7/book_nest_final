<?php
/**
 * inc/xac-thuc.php - Xác thực và phân quyền người dùng phía máy chủ
 * Nhóm 12 - Lớp 24CNTT3 - Chương 7: Bảo mật ứng dụng web
 */

/**
 * Lấy thông tin tài khoản đang đăng nhập từ Session (chỉ lưu $_SESSION['uid'])
 * Đọc trực tiếp từ CSDL và lưu đệm theo vòng đời request
 */
function nguoiDung(): ?array
{
    static $nguoiDungHienTai = null;
    static $daDoc = false;

    if ($daDoc) {
        return $nguoiDungHienTai;
    }

    $uid = $_SESSION['uid'] ?? null;
    if (!$uid) {
        $daDoc = true;
        return null;
    }

    try {
        $st = db()->prepare('SELECT id, email, ho_ten, vai_tro, ngay_tao, so_dien_thoai, dia_chi, avatar, kich_hoat, ten_gian_hang, mo_ta_shop, logo_shop, trang_thai_seller FROM nguoi_dung WHERE id = ?');
        $st->execute([$uid]);
        $u = $st->fetch();
        if ($u && isset($u['kich_hoat']) && (int)$u['kich_hoat'] === 0) {
            // Suspended/deactivated account
            dangXuat();
            $_SESSION['flash_error'] = 'Your account has been deactivated or suspended by the administrator.';
            $nguoiDungHienTai = null;
        } else {
            $nguoiDungHienTai = $u ?: null;
        }
    } catch (Throwable $e) {
        $nguoiDungHienTai = null;
    }

    $daDoc = true;
    return $nguoiDungHienTai;
}

/**
 * Đăng nhập an toàn và tái tạo mã định danh phiên (chống Session Fixation)
 */
function dangNhap(int|string $id): void
{
    $_SESSION['uid'] = (int)$id;
    $_SESSION['hoat_dong'] = time();
    session_regenerate_id(true);
}

/**
 * Đăng xuất an toàn, xóa toàn bộ mảng phiên và vô hiệu cookie phiên
 */
function dangXuat(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Yêu cầu người dùng phải đăng nhập trước khi xem trang
 */
function yeuCauDangNhap(?string $thongBao = null): array
{
    $nd = nguoiDung();
    if (!$nd) {
        $_SESSION['flash_error'] = $thongBao ?? 'Vui lòng đăng nhập để tiếp tục.';
        $_SESSION['redirect_sau_dang_nhap'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        header('Location: dang-nhap.php');
        exit;
    }
    return $nd;
}

/**
 * Yêu cầu người dùng phải có vai trò cụ thể (ví dụ: 'quan_tri')
 * Ghi nhật ký cam_truy_cap khi có truy cập trái phép
 */
function yeuCauQuyen(string $vaiTro): array
{
    $nd = yeuCauDangNhap();
    if (($nd['vai_tro'] ?? '') !== $vaiTro) {
        ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        require __DIR__ . '/../403.php';
        exit;
    }
    return $nd;
}

/**
 * Đếm số lần đăng nhập sai của email trong vòng 15 phút gần nhất
 */
function soLanSai(string $email): int
{
    $st = db()->prepare('SELECT COUNT(*) FROM dang_nhap_sai WHERE email = ? AND thoi_gian > NOW() - INTERVAL 15 MINUTE');
    $st->execute([$email]);
    return (int) $st->fetchColumn();
}

/**
 * Ghi nhận một lần đăng nhập sai vào CSDL và nhật ký bảo mật
 */
function ghiLanSai(string $email): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
    $st = db()->prepare('INSERT INTO dang_nhap_sai (email, ip) VALUES (?, ?)');
    $st->execute([$email, $ip]);
    ghiLogBaoMat('dang_nhap_sai', ['email' => $email]);
}
