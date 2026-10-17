<?php
/**
 * inc/xac-thuc.php - Xác thực và phân quyền người dùng phía máy chủ
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
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
        $st = db()->prepare('SELECT id, email, ho_ten, vai_tro, ngay_tao FROM nguoi_dung WHERE id = ?');
        $st->execute([$uid]);
        $u = $st->fetch();
        $nguoiDungHienTai = $u ?: null;
    } catch (Throwable $e) {
        $nguoiDungHienTai = null;
    }

    $daDoc = true;
    return $nguoiDungHienTai;
}

/**
 * Yêu cầu người dùng phải đăng nhập trước khi xem trang
 */
function yeuCauDangNhap(): void
{
    if (!nguoiDung()) {
        $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục.';
        $_SESSION['redirect_sau_dang_nhap'] = $_SERVER['REQUEST_URI'];
        header('Location: dang-nhap.php');
        exit;
    }
}

/**
 * Yêu cầu người dùng phải có vai trò cụ thể (ví dụ: 'quan_tri')
 */
function yeuCauQuyen(string $vaiTro): void
{
    yeuCauDangNhap();
    $u = nguoiDung();
    if (($u['vai_tro'] ?? '') !== $vaiTro) {
        http_response_code(403);
        require __DIR__ . '/../403.php';
        exit;
    }
}
