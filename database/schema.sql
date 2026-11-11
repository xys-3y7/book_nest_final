-- =============================================================================
-- HỌC PHẦN: THIẾT KẾ VÀ LẬP TRÌNH WEB (WEB DESIGN AND PROGRAMMING)
-- BÀI TẬP THỰC HÀNH NHÓM SỐ 6 - CHƯƠNG 6: PHP VÀ CƠ SỞ DỮ LIỆU MYSQL
-- ĐỒ ÁN: BOOKNEST - NHÀ SÁCH TRỰC TUYẾN SINH VIÊN UED
-- NHÓM THỰC HIỆN: NHÓM 12 (LỚP 24CNTT3)
-- TẬP TIN: database/schema.sql - KHỞI TẠO VÀ TÁI LẬP CSDL TỪ ĐẦU
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS ltweb_nhom12;
CREATE DATABASE ltweb_nhom12
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ltweb_nhom12;

-- 1. BẢNG DANH MỤC SÁCH (danh_muc)
CREATE TABLE danh_muc (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten       VARCHAR(100) NOT NULL,
  slug      VARCHAR(100) NOT NULL UNIQUE,
  mo_ta     TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG NGƯỜI DÙNG (nguoi_dung) - Hỗ trợ 3 Roles: Admin (quan_tri), Customer (khach_hang), Book Owner (chu_sach)
CREATE TABLE nguoi_dung (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email     VARCHAR(191) NOT NULL UNIQUE,
  ho_ten    VARCHAR(100) NOT NULL,
  mat_khau  VARCHAR(255) NOT NULL,
  vai_tro   ENUM('quan_tri', 'khach_hang', 'chu_sach') NOT NULL DEFAULT 'khach_hang',
  ngay_tao  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG SẢN PHẨM / SÁCH (san_pham)
-- Chuẩn 3NF, ràng buộc CHECK giá >= 1000 đồng, tồn kho >= 0
-- Hỗ trợ đa người bán (Multi-vendor): chu_sach_id liên kết với tài khoản Book Owner (chu_sach)
CREATE TABLE san_pham (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  danh_muc_id INT UNSIGNED NOT NULL,
  chu_sach_id INT UNSIGNED NULL,
  ten         VARCHAR(200) NOT NULL,
  gia         INT UNSIGNED NOT NULL CHECK (gia >= 1000),
  ton_kho     INT UNSIGNED NOT NULL DEFAULT 0,
  anh         VARCHAR(255) NULL,
  mo_ta       TEXT NULL,
  ngay_tao    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_dm_gia (danh_muc_id, gia),
  INDEX idx_chu_sach (chu_sach_id),
  CONSTRAINT fk_sanpham_danhmuc FOREIGN KEY (danh_muc_id) REFERENCES danh_muc(id),
  CONSTRAINT fk_sanpham_chusach FOREIGN KEY (chu_sach_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BẢNG ĐƠN HÀNG (don_hang)
CREATE TABLE don_hang (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nguoi_dung_id INT UNSIGNED NOT NULL,
  ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tong_tien     INT UNSIGNED NOT NULL DEFAULT 0,
  trang_thai    ENUM('cho_xu_ly', 'dang_giao', 'da_giao', 'da_huy') NOT NULL DEFAULT 'cho_xu_ly',
  dia_chi       VARCHAR(255) NOT NULL,
  so_dien_thoai VARCHAR(20) NOT NULL,
  ghi_chu       TEXT NULL,
  CONSTRAINT fk_donhang_nguoidung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. BẢNG CHI TIẾT ĐƠN HÀNG (chi_tiet_don)
-- Khóa chính ghép (don_hang_id, san_pham_id).
-- Cột don_gia lưu đơn giá tại thời điểm đặt hàng (chủ đích lưu lặp để không đổi khi giá sản phẩm thay đổi).
CREATE TABLE chi_tiet_don (
  don_hang_id INT UNSIGNED NOT NULL,
  san_pham_id INT UNSIGNED NOT NULL,
  so_luong    SMALLINT UNSIGNED NOT NULL CHECK (so_luong > 0),
  don_gia     INT UNSIGNED NOT NULL,
  PRIMARY KEY (don_hang_id, san_pham_id),
  CONSTRAINT fk_chitiet_donhang FOREIGN KEY (don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE,
  CONSTRAINT fk_chitiet_sanpham FOREIGN KEY (san_pham_id) REFERENCES san_pham(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. BẢNG LIÊN HỆ & GÓP Ý (lien_he)
CREATE TABLE lien_he (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ho_ten    VARCHAR(100) NOT NULL,
  email     VARCHAR(191) NOT NULL,
  chu_de    VARCHAR(200) NOT NULL,
  noi_dung  TEXT NOT NULL,
  anh       VARCHAR(255) NULL,
  ngay_gui  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. BẢNG THÀNH VIÊN NHÓM (thanh_vien) - PHẦN B
CREATE TABLE thanh_vien (
  mssv          CHAR(10) PRIMARY KEY,
  ho_ten        VARCHAR(100) NOT NULL,
  nguoi_dung_id INT UNSIGNED NULL UNIQUE,
  CONSTRAINT fk_thanhvien_nguoidung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. CÁC BẢNG RIÊNG CHO TRANG CÁ NHÂN (PHẦN B - tv_...)

-- 8.1. Bảng riêng của Xaiyasith Yoi: Sổ lưu bút (tv_luu_but)
CREATE TABLE tv_luu_but (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10) NOT NULL,
  ten_khach VARCHAR(50) NOT NULL,
  loi_nhan  VARCHAR(500) NOT NULL,
  thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_luubut_thanhvien FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.2. Bảng riêng của Phommaket Hatsady: Danh sách dự án (tv_du_an)
CREATE TABLE tv_du_an (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv        CHAR(10) NOT NULL,
  ten_du_an   VARCHAR(100) NOT NULL,
  cong_nghe   VARCHAR(100) NOT NULL,
  mo_ta       TEXT NOT NULL,
  link_github VARCHAR(255) NULL,
  CONSTRAINT fk_duan_thanhvien FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.3. Bảng riêng của Vongsena Sauphasith: Thống kê lượt xem theo ngày (tv_luot_xem)
CREATE TABLE tv_luot_xem (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv      CHAR(10) NOT NULL,
  ngay      DATE NOT NULL,
  so_luot   INT UNSIGNED NOT NULL DEFAULT 1,
  UNIQUE KEY uk_mssv_ngay (mssv, ngay),
  CONSTRAINT fk_luotxem_thanhvien FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.4. Bảng riêng của Nguyễn Thị Sinh: Sách yêu thích & đánh giá (tv_sach_yeu_thich)
CREATE TABLE tv_sach_yeu_thich (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mssv        CHAR(10) NOT NULL,
  san_pham_id INT UNSIGNED NOT NULL,
  danh_gia    TINYINT UNSIGNED NOT NULL CHECK (danh_gia BETWEEN 1 AND 5),
  cam_nhan    TEXT NULL,
  ngay_them   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_mssv_sp (mssv, san_pham_id),
  CONSTRAINT fk_sachyeuthich_thanhvien FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE,
  CONSTRAINT fk_sachyeuthich_sanpham FOREIGN KEY (san_pham_id) REFERENCES san_pham(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. BẢNG GHI NHẬN ĐĂNG NHẬP SAI (dang_nhap_sai) - CHƯƠNG 7 BẢO MẬT
CREATE TABLE dang_nhap_sai (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email     VARCHAR(255) NOT NULL,
  ip        VARCHAR(45) NOT NULL,
  thoi_gian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_thoigian (email, thoi_gian)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
