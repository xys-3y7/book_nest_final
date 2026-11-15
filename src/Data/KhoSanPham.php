<?php
/**
 * src/Data/KhoSanPham.php - Lớp truy xuất dữ liệu sản phẩm qua PDO MySQL
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

namespace App\Data;

use App\Models\SanPham;
use PDO;

class KhoSanPham
{
    public function __construct(private PDO $db) {}

    /**
     * Xây dựng điều kiện WHERE và mảng tham số cho bộ lọc tìm kiếm
     */
    private function dieuKien(array $loc): array
    {
        $dk = [];
        $ts = [];

        if (!empty($loc['q'])) {
            $dk[] = '(s.ten LIKE ? OR s.tac_gia LIKE ? OR s.isbn LIKE ?)';
            $kw = '%' . addcslashes($loc['q'], '%_\\') . '%';
            $ts[] = $kw;
            $ts[] = $kw;
            $ts[] = $kw;
        }

        if (!empty($loc['dm'])) {
            $dk[] = 's.danh_muc_id = ?';
            $ts[] = (int)$loc['dm'];
        }

        if (!empty($loc['giaToiDa'])) {
            $dk[] = 's.gia <= ?';
            $ts[] = (int)$loc['giaToiDa'];
        }

        if (!empty($loc['chuSachId'])) {
            $dk[] = 's.chu_sach_id = ?';
            $ts[] = (int)$loc['chuSachId'];
        }

        if (isset($loc['trangThaiDuyet']) && $loc['trangThaiDuyet'] !== 'all') {
            $dk[] = 's.trang_thai_duyet = ?';
            $ts[] = $loc['trangThaiDuyet'];
        }

        $where = $dk ? 'WHERE ' . implode(' AND ', $dk) : '';
        return [$where, $ts];
    }

    /**
     * Đếm tổng số sản phẩm thỏa mãn điều kiện lọc
     */
    public function dem(array $loc): int
    {
        [$where, $ts] = $this->dieuKien($loc);
        $st = $this->db->prepare("SELECT COUNT(*) FROM san_pham s $where");
        $st->execute($ts);
        return (int)$st->fetchColumn();
    }

    /**
     * Lấy danh sách sản phẩm theo trang có phân trang và sắp xếp theo whitelist
     * @return SanPham[]
     */
    public function trang(array $loc, string $sx, int $soDong, int $boQua): array
    {
        [$where, $ts] = $this->dieuKien($loc);

        // Danh sách cho phép (Whitelist) chống SQL Injection ở mệnh đề ORDER BY
        $orderBy = match ($sx) {
            'gia-tang'   => 's.gia ASC, s.id ASC',
            'gia-giam'   => 's.gia DESC, s.id DESC',
            'ten'        => 's.ten ASC, s.id ASC',
            'ban-chay', 'pho-bien' => 'COALESCE((SELECT SUM(ct.so_luong) FROM chi_tiet_don ct WHERE ct.san_pham_id = s.id), 0) DESC, s.id DESC',
            default      => 's.id DESC', // Mặc định: sách mới nhất
        };

        $sql = "SELECT s.*, dm.ten AS ten_danh_muc, nd.ho_ten AS ten_chu_sach
                FROM san_pham s
                JOIN danh_muc dm ON s.danh_muc_id = dm.id
                LEFT JOIN nguoi_dung nd ON s.chu_sach_id = nd.id
                $where
                ORDER BY $orderBy
                LIMIT ? OFFSET ?";

        $st = $this->db->prepare($sql);
        $st->execute([...$ts, $soDong, $boQua]);

        return array_map(fn($d) => SanPham::tuMang($d), $st->fetchAll());
    }

    /**
     * Tìm sách theo mã ID số nguyên duy nhất kèm tên danh mục qua JOIN
     */
    public function timTheoId(int $id): ?SanPham
    {
        $st = $this->db->prepare(
            'SELECT s.*, dm.ten AS ten_danh_muc, nd.ho_ten AS ten_chu_sach
             FROM san_pham s
             JOIN danh_muc dm ON s.danh_muc_id = dm.id
             LEFT JOIN nguoi_dung nd ON s.chu_sach_id = nd.id
             WHERE s.id = ?'
        );
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ? SanPham::tuMang($row) : null;
    }

    /**
     * Lấy các sản phẩm liên quan cùng danh mục
     * @return SanPham[]
     */
    public function sanPhamLienQuan(int $danhMucId, int $idHienTai, int $soLuong = 4): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, dm.ten AS ten_danh_muc, nd.ho_ten AS ten_chu_sach
             FROM san_pham s
             JOIN danh_muc dm ON s.danh_muc_id = dm.id
             LEFT JOIN nguoi_dung nd ON s.chu_sach_id = nd.id
             WHERE s.danh_muc_id = ? AND s.id != ?
             ORDER BY s.id DESC
             LIMIT ?'
        );
        $st->execute([$danhMucId, $idHienTai, $soLuong]);
        return array_map(fn($d) => SanPham::tuMang($d), $st->fetchAll());
    }

    /**
     * Lấy danh sách sản phẩm mới nhất hiển thị trang chủ
     * @return SanPham[]
     */
    public function sanPhamMoi(int $soLuong = 6): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, dm.ten AS ten_danh_muc, nd.ho_ten AS ten_chu_sach
             FROM san_pham s
             JOIN danh_muc dm ON s.danh_muc_id = dm.id
             LEFT JOIN nguoi_dung nd ON s.chu_sach_id = nd.id
             ORDER BY s.id DESC
             LIMIT ?'
        );
        $st->execute([$soLuong]);
        return array_map(fn($d) => SanPham::tuMang($d), $st->fetchAll());
    }

    /**
     * Lấy danh sách sách bán chạy (Best-Selling Books)
     * @return SanPham[]
     */
    public function sanPhamBanChay(int $soLuong = 6): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, dm.ten AS ten_danh_muc, nd.ho_ten AS ten_chu_sach,
                    COALESCE(SUM(ct.so_luong), 0) AS da_ban
             FROM san_pham s
             JOIN danh_muc dm ON s.danh_muc_id = dm.id
             LEFT JOIN nguoi_dung nd ON s.chu_sach_id = nd.id
             LEFT JOIN chi_tiet_don ct ON ct.san_pham_id = s.id
             GROUP BY s.id
             ORDER BY da_ban DESC, s.id DESC
             LIMIT ?'
        );
        $st->execute([$soLuong]);
        return array_map(fn($d) => SanPham::tuMang($d), $st->fetchAll());
    }

    /**
     * Lấy danh sách sách nổi bật (Featured Books)
     * @return SanPham[]
     */
    public function sanPhamNoiBat(int $soLuong = 6): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, dm.ten AS ten_danh_muc, nd.ho_ten AS ten_chu_sach
             FROM san_pham s
             JOIN danh_muc dm ON s.danh_muc_id = dm.id
             LEFT JOIN nguoi_dung nd ON s.chu_sach_id = nd.id
             WHERE s.ton_kho > 5
             ORDER BY s.id ASC
             LIMIT ?'
        );
        $st->execute([$soLuong]);
        return array_map(fn($d) => SanPham::tuMang($d), $st->fetchAll());
    }

    /**
     * Lấy danh sách sách đề xuất (Recommended Books)
     * @return SanPham[]
     */
    public function sanPhamGoiY(int $soLuong = 6): array
    {
        $st = $this->db->prepare(
            'SELECT s.*, dm.ten AS ten_danh_muc, nd.ho_ten AS ten_chu_sach
             FROM san_pham s
             JOIN danh_muc dm ON s.danh_muc_id = dm.id
             LEFT JOIN nguoi_dung nd ON s.chu_sach_id = nd.id
             ORDER BY RAND()
             LIMIT ?'
        );
        $st->execute([$soLuong]);
        return array_map(fn($d) => SanPham::tuMang($d), $st->fetchAll());
    }

    /**
     * Lấy danh sách sản phẩm theo danh sách ID (dùng cho Giỏ hàng và khối Đã xem gần đây)
     * Tránh lỗi truy vấn lặp N + 1 bằng cách gom tất cả ID vào một câu WHERE id IN (...)
     * @param int[] $ids
     * @return SanPham[]
     */
    public function layTheoDanhSachId(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $ids = array_unique(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT s.*, dm.ten AS ten_danh_muc
                FROM san_pham s
                JOIN danh_muc dm ON s.danh_muc_id = dm.id
                WHERE s.id IN ($placeholders)";

        $st = $this->db->prepare($sql);
        $st->execute(array_values($ids));

        $ketQua = [];
        foreach ($st->fetchAll() as $row) {
            $sp = SanPham::tuMang($row);
            $ketQua[$sp->id] = $sp;
        }

        // Sắp xếp lại theo đúng thứ tự mảng $ids truyền vào
        $sapXep = [];
        foreach ($ids as $id) {
            if (isset($ketQua[$id])) {
                $sapXep[] = $ketQua[$id];
            }
        }

        return $sapXep;
    }

    /**
     * Tìm kiếm nhanh phục vụ REST API tức thời
     * @return SanPham[]
     */
    public function timKiemNhanh(string $tuKhoa, int $gioiHan = 8): array
    {
        $tuKhoa = trim($tuKhoa);
        if ($tuKhoa === '') {
            return [];
        }

        $st = $this->db->prepare(
            'SELECT s.*, dm.ten AS ten_danh_muc
             FROM san_pham s
             JOIN danh_muc dm ON s.danh_muc_id = dm.id
             WHERE s.ten LIKE ?
             ORDER BY s.id DESC
             LIMIT ?'
        );
        $st->execute(['%' . addcslashes($tuKhoa, '%_\\') . '%', $gioiHan]);
        return array_map(fn($d) => SanPham::tuMang($d), $st->fetchAll());
    }
}
