<?php
/**
 * src/Data/KhoLienHe.php - Quản lý và lưu trữ liên hệ vào bảng lien_he MySQL qua PDO
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

namespace App\Data;

use PDO;

class KhoLienHe
{
    public function __construct(private PDO $db) {}

    /**
     * Lưu thông tin liên hệ mới vào bảng lien_he
     */
    public function luu(string $hoTen, string $email, string $chuDe, string $noiDung, ?string $anh = null): bool
    {
        $st = $this->db->prepare(
            'INSERT INTO lien_he (ho_ten, email, chu_de, noi_dung, anh) VALUES (?, ?, ?, ?, ?)'
        );
        return $st->execute([$hoTen, $email, $chuDe, $noiDung, $anh]);
    }

    /**
     * Lấy toàn bộ danh sách liên hệ đã nhận, sắp xếp mới nhất xếp trước
     */
    public function tatCa(): array
    {
        $st = $this->db->query('SELECT * FROM lien_he ORDER BY ngay_gui DESC, id DESC');
        return $st->fetchAll();
    }
}
