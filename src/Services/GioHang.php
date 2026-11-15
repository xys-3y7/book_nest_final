<?php
/**
 * src/Services/GioHang.php - Dịch vụ quản lý giỏ hàng lưu trong Session
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

namespace App\Services;

use App\Data\KhoSanPham;
use App\Models\SanPham;

class GioHang
{
    public function __construct()
    {
        if (!isset($_SESSION['gio']) || !is_array($_SESSION['gio'])) {
            $_SESSION['gio'] = [];
        }

        // Tự động nạp giỏ hàng từ CSDL MySQL nếu người dùng đã đăng nhập và phiên hiện tại rỗng
        if (function_exists('nguoiDung') && function_exists('db')) {
            $u = nguoiDung();
            if ($u && empty($_SESSION['gio'])) {
                try {
                    $st = db()->prepare('SELECT san_pham_id, so_luong FROM gio_hang WHERE nguoi_dung_id = ?');
                    $st->execute([(int)$u['id']]);
                    $rows = $st->fetchAll(\PDO::FETCH_KEY_PAIR);
                    if (!empty($rows)) {
                        $_SESSION['gio'] = array_map('intval', $rows);
                    }
                } catch (\Throwable $e) {}
            }
        }
    }

    /**
     * Đồng bộ giỏ hàng với CSDL MySQL khi người dùng đã đăng nhập
     */
    protected function dongBoCsdl(?int $spId = null, ?int $soLuong = null, string $thaoTac = 'set'): void
    {
        if (function_exists('nguoiDung') && function_exists('db')) {
            $u = nguoiDung();
            if ($u) {
                try {
                    $db = db();
                    $uid = (int)$u['id'];
                    if ($thaoTac === 'xoa_het') {
                        $db->prepare('DELETE FROM gio_hang WHERE nguoi_dung_id = ?')->execute([$uid]);
                    } elseif ($thaoTac === 'xoa' && $spId) {
                        $db->prepare('DELETE FROM gio_hang WHERE nguoi_dung_id = ? AND san_pham_id = ?')->execute([$uid, $spId]);
                    } elseif ($spId && $soLuong !== null && $soLuong > 0) {
                        $st = $db->prepare('INSERT INTO gio_hang (nguoi_dung_id, san_pham_id, so_luong) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE so_luong = VALUES(so_luong)');
                        $st->execute([$uid, $spId, $soLuong]);
                    }
                } catch (\Throwable $e) {}
            }
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (Đồng bộ CSDL MySQL)
     */
    public function them(int $id, int $soLuong, KhoSanPham $kho): bool
    {
        // Kiểm tra số lượng hợp lệ (1 - 99)
        if ($soLuong < 1 || $soLuong > 99) {
            return false;
        }

        // Kiểm tra ID sản phẩm có thực sự tồn tại và còn hàng trong CSDL không
        $sp = $kho->timTheoId($id);
        if ($sp === null || $sp->tonKho <= 0) {
            return false;
        }

        $hienCo = $_SESSION['gio'][$id] ?? 0;
        $gioiHanKho = min(99, (int)$sp->tonKho);
        $tongMoi = min($gioiHanKho, $hienCo + $soLuong);
        $_SESSION['gio'][$id] = $tongMoi;

        // Lưu vào CSDL MySQL
        $this->dongBoCsdl($id, $tongMoi, 'set');

        return true;
    }

    /**
     * Cập nhật số lượng của một sản phẩm trong giỏ (Đồng bộ CSDL MySQL)
     */
    public function capNhat(int $id, int $soLuong): bool
    {
        if ($soLuong <= 0) {
            $this->xoa($id);
            return true;
        }

        if ($soLuong > 99) {
            $soLuong = 99;
        }

        if (isset($_SESSION['gio'][$id])) {
            $_SESSION['gio'][$id] = $soLuong;
            $this->dongBoCsdl($id, $soLuong, 'set');
            return true;
        }

        return false;
    }

    /**
     * Xóa một sản phẩm khỏi giỏ (Đồng bộ CSDL MySQL)
     */
    public function xoa(int $id): void
    {
        unset($_SESSION['gio'][$id]);
        $this->dongBoCsdl($id, null, 'xoa');
    }

    /**
     * Xóa toàn bộ sản phẩm trong giỏ (Đồng bộ CSDL MySQL)
     */
    public function xoaHet(): void
    {
        $_SESSION['gio'] = [];
        $this->dongBoCsdl(null, null, 'xoa_het');
    }

    /**
     * Tổng số lượng mặt hàng (items) có trong giỏ
     */
    public function soMon(): int
    {
        return array_sum($_SESSION['gio'] ?? []);
    }

    /**
     * Lấy danh sách thô [id => soLuong]
     */
    public function danhSach(): array
    {
        return $_SESSION['gio'] ?? [];
    }

    /**
     * Lấy danh sách chi tiết các mặt hàng kèm đối tượng SanPham và thành tiền
     * TỐI ƯU HÓA: Sử dụng phương thức layTheoDanhSachId() chạy đúng 1 câu SQL WHERE id IN (...)
     * Tuyệt đối không truy vấn trong vòng lặp gây lỗi N + 1
     *
     * @return array<int, array{sanPham: SanPham, soLuong: int, thanhTien: int}>
     */
    public function layChiTiet(KhoSanPham $kho): array
    {
        $danhSach = $this->danhSach();
        if (empty($danhSach)) {
            return [];
        }

        $ids = array_keys($danhSach);
        $sanPhams = $kho->layTheoDanhSachId($ids);

        $ketQua = [];
        foreach ($sanPhams as $sp) {
            $sl = (int)($danhSach[$sp->id] ?? 0);
            if ($sl > 0) {
                $ketQua[] = [
                    'sanPham'   => $sp,
                    'soLuong'   => $sl,
                    'thanhTien' => $sp->gia * $sl
                ];
            }
        }
        return $ketQua;
    }

    /**
     * Tính tổng tiền cả giỏ hàng dựa trên giá máy chủ
     */
    public function tongTien(KhoSanPham $kho): int
    {
        $tong = 0;
        foreach ($this->layChiTiet($kho) as $item) {
            $tong += $item['thanhTien'];
        }
        return $tong;
    }
}
