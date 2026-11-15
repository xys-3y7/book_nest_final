-- =====================================================================
-- nang-cap-csdl.sql: Database Schema Enhancement & Roles for BookNest
-- Supports 3 Roles: Admin, Seller, Customer
-- Tables: users, roles, books, categories, sellers, orders, order_items,
--         payments, shopping_carts, cart_items, wishlists, reviews,
--         addresses, notifications, system_settings
-- =====================================================================

USE book_nest_final;

-- 1. ENHANCE nguoi_dung (users)
ALTER TABLE nguoi_dung
  ADD COLUMN IF NOT EXISTS so_dien_thoai VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS dia_chi VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS kich_hoat TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS ten_gian_hang VARCHAR(150) NULL,
  ADD COLUMN IF NOT EXISTS mo_ta_shop TEXT NULL,
  ADD COLUMN IF NOT EXISTS logo_shop VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS trang_thai_seller ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'approved';

-- 2. ENHANCE san_pham (books)
ALTER TABLE san_pham
  ADD COLUMN IF NOT EXISTS ten_en VARCHAR(255) NULL AFTER ten,
  ADD COLUMN IF NOT EXISTS tac_gia VARCHAR(150) NULL DEFAULT 'BookNest Editorial',
  ADD COLUMN IF NOT EXISTS tac_gia_en VARCHAR(150) NULL AFTER tac_gia,
  ADD COLUMN IF NOT EXISTS mo_ta_en TEXT NULL AFTER mo_ta,
  ADD COLUMN IF NOT EXISTS isbn VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS nam_xuat_ban SMALLINT UNSIGNED NULL DEFAULT 2026,
  ADD COLUMN IF NOT EXISTS trang_thai_duyet ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'approved',
  ADD COLUMN IF NOT EXISTS luot_xem INT UNSIGNED NOT NULL DEFAULT 0;

-- 2.1 ENHANCE danh_muc (categories)
ALTER TABLE danh_muc
  ADD COLUMN IF NOT EXISTS ten_en VARCHAR(150) NULL AFTER ten,
  ADD COLUMN IF NOT EXISTS mo_ta_en TEXT NULL AFTER mo_ta;

-- 3. ENHANCE don_hang (orders)
ALTER TABLE don_hang
  ADD COLUMN IF NOT EXISTS phuong_thuc_tt ENUM('cod', 'bank', 'momo') NOT NULL DEFAULT 'cod',
  ADD COLUMN IF NOT EXISTS trang_thai_tt ENUM('chua_thanh_toan', 'da_thanh_toan', 'hoan_tien') NOT NULL DEFAULT 'chua_thanh_toan';

-- 4. CREATE ROLES TABLE
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL,
  display_name VARCHAR(100) NOT NULL,
  description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (name, display_name, description) VALUES
  ('quan_tri', 'Administrator', 'Full control over platform, users, books, orders, sellers, and settings'),
  ('chu_sach', 'Seller', 'Store owner who manages own book listings, orders, and sales revenue'),
  ('khach_hang', 'Customer', 'Buyer who browses, purchases books, reviews, and manages orders')
ON DUPLICATE KEY UPDATE display_name = VALUES(display_name), description = VALUES(description);

-- 5. CREATE danh_gia (reviews)
CREATE TABLE IF NOT EXISTS danh_gia (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  san_pham_id INT UNSIGNED NOT NULL,
  nguoi_dung_id INT UNSIGNED NOT NULL,
  so_sao TINYINT UNSIGNED NOT NULL DEFAULT 5,
  tieu_de VARCHAR(150) NULL,
  noi_dung TEXT NOT NULL,
  da_mua_hang TINYINT(1) NOT NULL DEFAULT 0,
  ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_review_sp (san_pham_id),
  INDEX idx_review_nd (nguoi_dung_id),
  CONSTRAINT fk_review_sp FOREIGN KEY (san_pham_id) REFERENCES san_pham(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_nd FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. CREATE payments AND payment_methods TABLES (Module 10)
CREATE TABLE IF NOT EXISTS payment_methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  name_en VARCHAR(100) NULL,
  description TEXT,
  description_en TEXT NULL,
  instructions TEXT,
  instructions_en TEXT NULL,
  icon VARCHAR(50) DEFAULT '💳',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO payment_methods (code, name, name_en, description, description_en, instructions, instructions_en, icon, is_active, sort_order) VALUES
  ('cod', 'Thanh toán khi nhận hàng (COD)', 'Cash on Delivery (COD)', 'Thanh toán tiền mặt khi nhận sách giáo trình tại Ký túc xá hoặc sảnh trường UED', 'Pay with cash upon textbook delivery at UED campus or dormitory', 'Vui lòng chuẩn bị đúng số tiền khi nhận sách từ shipper BookNest.', 'Please prepare the exact cash amount when receiving textbooks from our courier.', '💵', 1, 1),
  ('bank_transfer', 'Chuyển khoản Ngân hàng', 'Bank Transfer', 'Chuyển khoản trực tiếp tới tài khoản ngân hàng chính thức của BookNest UED', 'Transfer directly to BookNest official student account via mobile banking', 'Ngân hàng: Vietcombank - CN Đà Nẵng\nSố tài khoản: 02363841323\nTên chủ tài khoản: BOOKNEST STORE UED\nNội dung: BN{ORDER_ID}', 'Bank: Vietcombank - Danang Branch\nAccount No: 02363841323\nAccount Name: BOOKNEST STORE UED\nTransfer memo: BN{ORDER_ID}', '🏦', 1, 2),
  ('qr_code', 'Thanh toán mã QR', 'QR Code Payment', 'Quét mã QR động tiêu chuẩn VietQR / PromptPay để thanh toán tức thì', 'Scan dynamic QR code (PromptPay / VietQR standard) for instant settlement', 'Quét mã QR hiển thị trên màn hình đơn hàng bằng ứng dụng ngân hàng bất kỳ.', 'Scan the QR code displayed on the checkout or order screen using any banking or payment app.', '📱', 1, 3),
  ('e_wallet', 'Ví điện tử MoMo', 'E-Wallet (MoMo / TrueMoney)', 'Thanh toán nhanh chóng và an toàn qua Ví điện tử sinh viên MoMo', 'Fast and secure checkout via MoMo or digital student e-wallets', 'Quét mã MoMo hoặc chuyển đến số 0905123456 (BookNest Admin).', 'Scan MoMo QR code or transfer to 0905123456 (BookNest Admin).', '👛', 1, 4)
ON DUPLICATE KEY UPDATE name=VALUES(name), name_en=VALUES(name_en), description=VALUES(description), description_en=VALUES(description_en), instructions=VALUES(instructions), instructions_en=VALUES(instructions_en), icon=VALUES(icon);

CREATE TABLE IF NOT EXISTS payments (
  payment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  transaction_id VARCHAR(100) NULL,
  amount INT UNSIGNED NOT NULL,
  payment_status ENUM('pending', 'paid', 'failed', 'verifying', 'rejected', 'refunded') NOT NULL DEFAULT 'pending',
  payment_proof VARCHAR(255) NULL,
  payment_date DATETIME NULL,
  admin_note TEXT NULL,
  verified_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order (order_id),
  INDEX idx_user (user_id),
  INDEX idx_status (payment_status),
  INDEX idx_txn (transaction_id),
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES don_hang(id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CREATE so_dia_chi (addresses)
CREATE TABLE IF NOT EXISTS so_dia_chi (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nguoi_dung_id INT UNSIGNED NOT NULL,
  ho_ten VARCHAR(100) NOT NULL,
  so_dien_thoai VARCHAR(20) NOT NULL,
  dia_chi VARCHAR(255) NOT NULL,
  mac_dinh TINYINT(1) NOT NULL DEFAULT 0,
  ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_addr_nd (nguoi_dung_id),
  CONSTRAINT fk_addr_nd FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. CREATE thong_bao (notifications)
CREATE TABLE IF NOT EXISTS thong_bao (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nguoi_dung_id INT UNSIGNED NOT NULL,
  tieu_de VARCHAR(191) NOT NULL,
  noi_dung TEXT NOT NULL,
  loai ENUM('don_hang', 'sach', 'he_thong', 'khuyen_mai') NOT NULL DEFAULT 'he_thong',
  da_doc TINYINT(1) NOT NULL DEFAULT 0,
  ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tb_nd (nguoi_dung_id),
  CONSTRAINT fk_tb_nd FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. CREATE he_thong_cai_dat (system settings)
CREATE TABLE IF NOT EXISTS he_thong_cai_dat (
  khoa VARCHAR(50) PRIMARY KEY,
  gia_tri TEXT NOT NULL,
  mo_ta VARCHAR(191) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO he_thong_cai_dat (khoa, gia_tri, mo_ta) VALUES
  ('site_name', 'BookNest - UED Student Bookstore', 'Platform Name'),
  ('hotline', '0236.3841.323', 'Customer Support Hotline'),
  ('support_email', 'booknest.ued@gmail.com', 'Support Email'),
  ('announcement', '🎉 Welcome to BookNest! New Semester Book Fair: Up to 25% OFF all textbooks!', 'Top banner announcement'),
  ('promo_active', '1', 'Promotion banner active status')
ON DUPLICATE KEY UPDATE gia_tri = VALUES(gia_tri);

-- 10. CREATE ENGLISH COMPATIBILITY VIEWS
CREATE OR REPLACE VIEW users AS 
  SELECT id, email, ho_ten AS full_name, vai_tro AS role, kich_hoat AS is_active,
         so_dien_thoai AS phone, dia_chi AS address, avatar, ten_gian_hang AS shop_name,
         mo_ta_shop AS shop_description, trang_thai_seller AS seller_status, ngay_tao AS created_at
  FROM nguoi_dung;

CREATE OR REPLACE VIEW sellers AS
  SELECT id, email, ho_ten AS owner_name, ten_gian_hang AS shop_name,
         mo_ta_shop AS shop_description, logo_shop, so_dien_thoai AS phone,
         trang_thai_seller AS status, kich_hoat AS is_active, ngay_tao AS registered_at
  FROM nguoi_dung
  WHERE vai_tro = 'chu_sach';

CREATE OR REPLACE VIEW books AS 
  SELECT id, danh_muc_id AS category_id, chu_sach_id AS seller_id, 
         COALESCE(ten_en, ten) AS title, ten AS title_vi, ten_en AS title_en,
         COALESCE(tac_gia_en, tac_gia) AS author, tac_gia AS author_vi, tac_gia_en AS author_en,
         isbn, nam_xuat_ban AS publication_year, gia AS price,
         ton_kho AS stock, anh AS cover_image, 
         COALESCE(mo_ta_en, mo_ta) AS description, mo_ta AS description_vi, mo_ta_en AS description_en,
         trang_thai_duyet AS approval_status, luot_xem AS views, ngay_tao AS created_at
  FROM san_pham;

CREATE OR REPLACE VIEW categories AS 
  SELECT id, COALESCE(ten_en, ten) AS name, ten AS name_vi, ten_en AS name_en,
         slug, COALESCE(mo_ta_en, mo_ta) AS description, mo_ta AS description_vi, mo_ta_en AS description_en
  FROM danh_muc;

CREATE OR REPLACE VIEW orders AS 
  SELECT id, nguoi_dung_id AS user_id, tong_tien AS total_amount, trang_thai AS status,
         phuong_thuc_tt AS payment_method, trang_thai_tt AS payment_status,
         dia_chi AS shipping_address, so_dien_thoai AS phone, ghi_chu AS notes,
         ngay_tao AS created_at
  FROM don_hang;

CREATE OR REPLACE VIEW order_items AS 
  SELECT don_hang_id AS order_id, san_pham_id AS book_id, so_luong AS quantity,
         don_gia AS unit_price
  FROM chi_tiet_don;

CREATE OR REPLACE VIEW shopping_carts AS
  SELECT nguoi_dung_id AS user_id, san_pham_id AS book_id, so_luong AS quantity
  FROM gio_hang;

CREATE OR REPLACE VIEW cart_items AS
  SELECT nguoi_dung_id AS user_id, san_pham_id AS book_id, so_luong AS quantity
  FROM gio_hang;

CREATE OR REPLACE VIEW wishlists AS
  SELECT nguoi_dung_id AS user_id, san_pham_id AS book_id, ngay_tao AS created_at
  FROM yeu_thich;

CREATE OR REPLACE VIEW reviews AS 
  SELECT id, san_pham_id AS book_id, nguoi_dung_id AS user_id, so_sao AS rating,
         tieu_de AS title, noi_dung AS content, da_mua_hang AS is_verified_purchase, ngay_tao AS created_at
  FROM danh_gia;

CREATE OR REPLACE VIEW addresses AS 
  SELECT id, nguoi_dung_id AS user_id, ho_ten AS recipient_name,
         so_dien_thoai AS phone, dia_chi AS address, mac_dinh AS is_default,
         ngay_tao AS created_at
  FROM so_dia_chi;

CREATE OR REPLACE VIEW notifications AS 
  SELECT id, nguoi_dung_id AS user_id, tieu_de AS title, noi_dung AS content,
         loai AS type, da_doc AS is_read, ngay_tao AS created_at
  FROM thong_bao;

-- 11. SUB ENGLISH ANNOTATIONS (Table & Column Comments)
ALTER TABLE san_pham COMMENT = 'Books & Textbooks - Quản lý sản phẩm sách';
ALTER TABLE danh_muc COMMENT = 'Textbook Categories - Danh mục thể loại sách';
ALTER TABLE don_hang COMMENT = 'Orders - Quản lý đơn đặt hàng';
ALTER TABLE chi_tiet_don COMMENT = 'Order Items / Details - Chi tiết từng cuốn sách trong đơn';
ALTER TABLE nguoi_dung COMMENT = 'Users & Accounts - Tài khoản người dùng (Admin, Seller, Customer)';
ALTER TABLE payments COMMENT = 'Payments & Transactions - Quản lý giao dịch thanh toán';
ALTER TABLE payment_methods COMMENT = 'Payment Methods - Phương thức thanh toán (COD, Bank, QR, MoMo)';
ALTER TABLE roles COMMENT = 'User Roles - Phân quyền người dùng';
ALTER TABLE gio_hang COMMENT = 'Shopping Cart - Giỏ hàng người dùng';
ALTER TABLE danh_gia COMMENT = 'Book Reviews & Ratings - Đánh giá và nhận xét sách';
ALTER TABLE so_dia_chi COMMENT = 'Shipping Addresses - Sổ địa chỉ nhận hàng';
ALTER TABLE thong_bao COMMENT = 'Notifications - Thông báo hệ thống';
ALTER TABLE lien_he COMMENT = 'Contact Inquiries - Tin nhắn liên hệ phản hồi';
ALTER TABLE he_thong_cai_dat COMMENT = 'System Settings - Cài đặt cấu hình hệ thống';
ALTER TABLE dang_nhap_sai COMMENT = 'Failed Login Attempts - Nhật ký đăng nhập thất bại';
ALTER TABLE thanh_vien COMMENT = 'Team Members - Danh sách thành viên nhóm 12';
ALTER TABLE tv_du_an COMMENT = 'Member Projects - Dự án cá nhân thành viên';
ALTER TABLE tv_luot_xem COMMENT = 'Member Profile Views - Lượt xem hồ sơ cá nhân';
ALTER TABLE tv_luu_but COMMENT = 'Member Guestbook - Lưu bút và lời nhắn thành viên';
ALTER TABLE tv_sach_yeu_thich COMMENT = 'Member Favorite Books - Sách yêu thích của thành viên';
ALTER TABLE yeu_thich COMMENT = 'Wishlist / Saved Books - Danh sách sách yêu thích của khách hàng';
ALTER TABLE thanh_toan COMMENT = 'Legacy Payments - Bảng thanh toán cũ (Chuyển tiếp sang payments)';

-- 12. THE 16 EXACT SUB ENGLISH VIEWS
CREATE OR REPLACE VIEW `Books & Textbooks` AS
  SELECT id AS book_id, danh_muc_id AS category_id, chu_sach_id AS seller_id,
         COALESCE(ten_en, ten) AS title, ten AS title_vietnamese, ten_en AS title_english,
         COALESCE(tac_gia_en, tac_gia) AS author, tac_gia AS author_vietnamese, tac_gia_en AS author_english,
         gia AS price, ton_kho AS stock, anh AS cover_image,
         COALESCE(mo_ta_en, mo_ta) AS description, mo_ta AS description_vietnamese, mo_ta_en AS description_english,
         isbn, nam_xuat_ban AS publication_year, trang_thai_duyet AS approval_status,
         luot_xem AS views, ngay_tao AS created_at
  FROM san_pham;

CREATE OR REPLACE VIEW `Textbook Categories` AS
  SELECT id AS category_id, COALESCE(ten_en, ten) AS category_name,
         ten AS category_name_vietnamese, ten_en AS category_name_english,
         slug, COALESCE(mo_ta_en, mo_ta) AS description,
         mo_ta AS description_vietnamese, mo_ta_en AS description_english
  FROM danh_muc;

CREATE OR REPLACE VIEW `Orders` AS
  SELECT id AS order_id, nguoi_dung_id AS user_id, tong_tien AS total_amount,
         trang_thai AS status, phuong_thuc_tt AS payment_method, trang_thai_tt AS payment_status,
         dia_chi AS shipping_address, so_dien_thoai AS phone_number, ghi_chu AS notes,
         ngay_tao AS order_date
  FROM don_hang;

CREATE OR REPLACE VIEW `Order Items / Details` AS
  SELECT ctd.don_hang_id AS order_id, ctd.san_pham_id AS book_id,
         COALESCE(sp.ten_en, sp.ten) AS book_title, ctd.so_luong AS quantity,
         ctd.don_gia AS unit_price, (ctd.so_luong * ctd.don_gia) AS subtotal
  FROM chi_tiet_don ctd
  LEFT JOIN san_pham sp ON ctd.san_pham_id = sp.id;

CREATE OR REPLACE VIEW `Users & Accounts` AS
  SELECT id AS user_id, email, ho_ten AS full_name, vai_tro AS role,
         so_dien_thoai AS phone_number, dia_chi AS address, avatar, kich_hoat AS is_active,
         ten_gian_hang AS shop_name, mo_ta_shop AS shop_description,
         trang_thai_seller AS seller_status, ngay_tao AS created_at
  FROM nguoi_dung;

CREATE OR REPLACE VIEW `Payments & Transactions` AS
  SELECT payment_id, order_id, user_id, payment_method, transaction_id,
         amount, payment_status, payment_proof, payment_date, admin_note,
         verified_by, created_at
  FROM payments;

CREATE OR REPLACE VIEW `Payment Methods` AS
  SELECT id AS method_id, code, COALESCE(name_en, name) AS method_name,
         name AS name_vietnamese, name_en AS name_english,
         COALESCE(description_en, description) AS description,
         COALESCE(instructions_en, instructions) AS instructions,
         icon, is_active, sort_order, created_at
  FROM payment_methods;

CREATE OR REPLACE VIEW `User Roles` AS
  SELECT id AS role_id, name AS role_code, display_name, description
  FROM roles;

CREATE OR REPLACE VIEW `Shopping Cart` AS
  SELECT gh.id AS cart_item_id, gh.nguoi_dung_id AS user_id, gh.san_pham_id AS book_id,
         COALESCE(sp.ten_en, sp.ten) AS book_title, sp.gia AS unit_price,
         gh.so_luong AS quantity, (gh.so_luong * sp.gia) AS subtotal,
         gh.ngay_tao AS added_at, gh.ngay_cap_nhat AS updated_at
  FROM gio_hang gh
  LEFT JOIN san_pham sp ON gh.san_pham_id = sp.id;

CREATE OR REPLACE VIEW `Book Reviews & Ratings` AS
  SELECT dg.id AS review_id, dg.san_pham_id AS book_id,
         COALESCE(sp.ten_en, sp.ten) AS book_title, dg.nguoi_dung_id AS user_id,
         nd.ho_ten AS customer_name, dg.so_sao AS rating_stars,
         dg.tieu_de AS review_title, dg.noi_dung AS review_content,
         dg.da_mua_hang AS is_verified_purchase, dg.ngay_tao AS review_date
  FROM danh_gia dg
  LEFT JOIN san_pham sp ON dg.san_pham_id = sp.id
  LEFT JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id;

CREATE OR REPLACE VIEW `Shipping Addresses` AS
  SELECT id AS address_id, nguoi_dung_id AS user_id, ho_ten AS recipient_name,
         so_dien_thoai AS phone_number, dia_chi AS full_address,
         mac_dinh AS is_default, ngay_tao AS created_at
  FROM so_dia_chi;

CREATE OR REPLACE VIEW `Notifications` AS
  SELECT id AS notification_id, nguoi_dung_id AS user_id, tieu_de AS title,
         noi_dung AS content, loai AS notification_type, da_doc AS is_read,
         ngay_tao AS created_at
  FROM thong_bao;

CREATE OR REPLACE VIEW `Contact Inquiries` AS
  SELECT id AS inquiry_id, ho_ten AS full_name, email, chu_de AS subject,
         noi_dung AS message, anh AS attachment, ngay_gui AS submitted_at
  FROM lien_he;

CREATE OR REPLACE VIEW `System Settings` AS
  SELECT khoa AS setting_key, gia_tri AS setting_value, mo_ta AS description
  FROM he_thong_cai_dat;

CREATE OR REPLACE VIEW `Failed Login Attempts` AS
  SELECT id AS attempt_id, email, ip AS ip_address, thoi_gian AS attempted_at
  FROM dang_nhap_sai;

CREATE OR REPLACE VIEW `Wishlist / Saved Books` AS
  SELECT yt.id AS wishlist_id, yt.nguoi_dung_id AS user_id, yt.san_pham_id AS book_id,
         COALESCE(sp.ten_en, sp.ten) AS book_title, sp.gia AS book_price,
         yt.ngay_tao AS saved_at
  FROM yeu_thich yt
  LEFT JOIN san_pham sp ON yt.san_pham_id = sp.id;
