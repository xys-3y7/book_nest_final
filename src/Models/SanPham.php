<?php
/**
 * src/Models/SanPham.php - Thực thể Sách / Giáo trình trong hệ thống BookNest
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

namespace App\Models;

use JsonSerializable;

class SanPham implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly int $danhMucId,
        public readonly string $ten,
        public readonly int $gia,
        public readonly int $tonKho,
        public readonly ?string $anh = null,
        public readonly ?string $moTa = null,
        public readonly string $ngayTao = '',
        public readonly ?string $tenDanhMuc = null,
        public readonly ?int $chuSachId = null,
        public readonly ?string $tenChuSach = null,
        public readonly ?string $tacGia = null,
        public readonly ?string $isbn = null,
        public readonly ?int $namXuatBan = null,
        public readonly string $trangThaiDuyet = 'approved',
        public readonly int $luotXem = 0,
        public readonly ?string $tenEn = null,
        public readonly ?string $tacGiaEn = null,
        public readonly ?string $moTaEn = null,
        public readonly ?string $tenDanhMucEn = null
    ) {}

    /**
     * Resolve English or native title based on active language
     */
    public function getTitle(): string
    {
        if (function_exists('laTiengAnh') && laTiengAnh() && !empty($this->tenEn)) {
            return $this->tenEn;
        }
        return !empty($this->ten) ? $this->ten : ($this->tenEn ?: '');
    }

    /**
     * Resolve English or native author based on active language
     */
    public function getAuthor(): string
    {
        if (function_exists('laTiengAnh') && laTiengAnh() && !empty($this->tacGiaEn)) {
            return $this->tacGiaEn;
        }
        return !empty($this->tacGia) ? $this->tacGia : ($this->tacGiaEn ?: 'BookNest Editorial');
    }

    /**
     * Resolve English or native description
     */
    public function getDescription(): string
    {
        if (function_exists('laTiengAnh') && laTiengAnh() && !empty($this->moTaEn)) {
            return $this->moTaEn;
        }
        return !empty($this->moTa) ? $this->moTa : ($this->moTaEn ?: '');
    }

    /**
     * Resolve English or native category name
     */
    public function getCategoryName(): string
    {
        if (function_exists('laTiengAnh') && laTiengAnh() && !empty($this->tenDanhMucEn)) {
            return $this->tenDanhMucEn;
        }
        return !empty($this->tenDanhMuc) ? $this->tenDanhMuc : ($this->tenDanhMucEn ?: '');
    }

    /**
     * Khởi tạo đối tượng SanPham từ một dòng dữ liệu CSDL MySQL qua PDO
     */
    public static function tuMang(array $d): self
    {
        return new self(
            id: (int)($d['id'] ?? 0),
            danhMucId: (int)($d['danh_muc_id'] ?? 0),
            ten: (string)($d['ten'] ?? ''),
            gia: (int)($d['gia'] ?? 0),
            tonKho: (int)($d['ton_kho'] ?? 0),
            anh: !empty($d['anh']) ? (string)$d['anh'] : null,
            moTa: !empty($d['mo_ta']) ? (string)$d['mo_ta'] : null,
            ngayTao: (string)($d['ngay_tao'] ?? ''),
            tenDanhMuc: isset($d['ten_danh_muc']) ? (string)$d['ten_danh_muc'] : null,
            chuSachId: isset($d['chu_sach_id']) ? (int)$d['chu_sach_id'] : null,
            tenChuSach: isset($d['ten_chu_sach']) ? (string)$d['ten_chu_sach'] : null,
            tacGia: !empty($d['tac_gia']) ? (string)$d['tac_gia'] : null,
            isbn: !empty($d['isbn']) ? (string)$d['isbn'] : null,
            namXuatBan: isset($d['nam_xuat_ban']) && $d['nam_xuat_ban'] !== '' ? (int)$d['nam_xuat_ban'] : null,
            trangThaiDuyet: (string)($d['trang_thai_duyet'] ?? 'approved'),
            luotXem: (int)($d['luot_xem'] ?? 0),
            tenEn: !empty($d['ten_en']) ? (string)$d['ten_en'] : null,
            tacGiaEn: !empty($d['tac_gia_en']) ? (string)$d['tac_gia_en'] : null,
            moTaEn: !empty($d['mo_ta_en']) ? (string)$d['mo_ta_en'] : null,
            tenDanhMucEn: !empty($d['ten_danh_muc_en']) ? (string)$d['ten_danh_muc_en'] : null
        );
    }

    /**
     * Triển khai JsonSerializable: chỉ trả về các trường công khai cần thiết cho REST API
     * Tuyệt đối không để lộ các thông tin nội bộ máy chủ
     */
    public function jsonSerialize(): array
    {
        return [
            'id'               => $this->id,
            'danh_muc_id'      => $this->danhMucId,
            'ten'              => $this->getTitle(),
            'title'            => $this->getTitle(),
            'ten_vi'           => $this->ten,
            'ten_en'           => $this->tenEn,
            'gia'              => $this->gia,
            'ton_kho'          => $this->tonKho,
            'anh'              => $this->anh,
            'mo_ta'            => $this->getDescription(),
            'tac_gia'          => $this->getAuthor(),
            'tac_gia_en'       => $this->tacGiaEn,
            'ten_danh_muc'     => $this->getCategoryName(),
            'ten_danh_muc_en'  => $this->tenDanhMucEn,
            'isbn'             => $this->isbn,
            'nam_xuat_ban'     => $this->namXuatBan,
            'trang_thai_duyet' => $this->trangThaiDuyet,
        ];
    }
}
