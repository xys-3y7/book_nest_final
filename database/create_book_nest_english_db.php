<?php
/**
 * database/create_book_nest_english_db.php
 * Provisions the standalone English database: `book_nest_english`
 * and maps all 22 tables from `book_nest_final`:
 * 1.  chi_tiet_don       -> order_details        / `Order Details`
 * 2.  dang_nhap_sai      -> failed_login_attempts/ `Failed Login Attempts`
 * 3.  danh_gia           -> reviews_ratings      / `Reviews / Ratings`
 * 4.  danh_muc           -> categories           / `Categories`
 * 5.  don_hang           -> orders               / `Orders`
 * 6.  gio_hang           -> shopping_cart        / `Shopping Cart`
 * 7.  he_thong_cai_dat   -> system_settings      / `System Settings`
 * 8.  lien_he            -> contact_messages     / `Contact Messages`
 * 9.  nguoi_dung         -> users                / `Users`
 * 10. payments           -> payments             / `Payments`
 * 11. payment_methods    -> payment_methods      / `Payment Methods`
 * 12. roles              -> user_roles           / `User Roles`
 * 13. san_pham           -> products_books       / `Products / Books`
 * 14. so_dia_chi         -> address_book         / `Address Book`
 * 15. thanh_toan         -> checkout_payment     / `Checkout / Payment`
 * 16. thanh_vien         -> members              / `Members`
 * 17. thong_bao          -> notifications        / `Notifications`
 * 18. tv_du_an           -> project_members      / `Project Members`
 * 19. tv_luot_xem        -> view_counts          / `View Counts`
 * 20. tv_luu_but         -> guestbook_entries    / `Guestbook Entries`
 * 21. tv_sach_yeu_thich  -> members_favorite_books / `Members' Favorite Books`
 * 22. yeu_thich          -> favorites            / `Favorites`
 */

require_once __DIR__ . '/../inc/config.php';
$sourceDb = db();

echo "========================================================\n";
echo "Provisioning book_nest_english Database & 22 Tables\n";
echo "========================================================\n";

$sourceDb->exec("CREATE DATABASE IF NOT EXISTS `book_nest_english` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "[OK] Database `book_nest_english` ready.\n";

$targetDb = new PDO('mysql:host=127.0.0.1;port=3306;dbname=book_nest_english;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// 1. Roles (User Roles)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `user_roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) UNIQUE NOT NULL,
    `display_name` VARCHAR(100) NOT NULL,
    `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User Roles'");

$targetDb->exec("INSERT INTO `user_roles` (`id`, `name`, `display_name`, `description`)
    SELECT `id`, `name`, `display_name`, `description` FROM `book_nest_final`.`roles`
    ON DUPLICATE KEY UPDATE `display_name`=VALUES(`display_name`), `description`=VALUES(`description`)");
echo "[OK] 12. user_roles populated.\n";

// 2. Users (Users)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) UNIQUE NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'customer',
    `phone` VARCHAR(20) NULL,
    `address` VARCHAR(255) NULL,
    `avatar` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `shop_name` VARCHAR(150) NULL,
    `shop_description` TEXT NULL,
    `shop_logo` VARCHAR(255) NULL,
    `seller_status` VARCHAR(50) NOT NULL DEFAULT 'approved',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Users'");

$targetDb->exec("INSERT INTO `users` (`id`, `email`, `full_name`, `password_hash`, `role`, `phone`, `address`, `avatar`, `is_active`, `shop_name`, `shop_description`, `shop_logo`, `seller_status`, `created_at`)
    SELECT `id`, `email`, `ho_ten`, `mat_khau`, 
           CASE `vai_tro` WHEN 'quan_tri' THEN 'admin' WHEN 'chu_sach' THEN 'seller' ELSE 'customer' END,
           `so_dien_thoai`, `dia_chi`, `avatar`, `kich_hoat`, `ten_gian_hang`, `mo_ta_shop`, `logo_shop`, `trang_thai_seller`, `ngay_tao`
    FROM `book_nest_final`.`nguoi_dung`
    ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`), `phone`=VALUES(`phone`), `address`=VALUES(`address`)");
echo "[OK] 9. users populated.\n";

// 3. Categories (Categories)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `name_vi` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `description_vi` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categories'");

$targetDb->exec("INSERT INTO `categories` (`id`, `name`, `name_vi`, `slug`, `description`, `description_vi`)
    SELECT `id`, COALESCE(`ten_en`, `ten`), `ten`, `slug`, COALESCE(`mo_ta_en`, `mo_ta`), `mo_ta`
    FROM `book_nest_final`.`danh_muc`
    ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`)");
echo "[OK] 4. categories populated.\n";

// 4. Products / Books (Products / Books)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `products_books` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `seller_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `title_vi` VARCHAR(255) NOT NULL,
    `author` VARCHAR(150) NOT NULL,
    `author_vi` VARCHAR(150) NOT NULL,
    `price` INT UNSIGNED NOT NULL,
    `stock` INT UNSIGNED NOT NULL DEFAULT 0,
    `cover_image` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `description_vi` TEXT NULL,
    `isbn` VARCHAR(30) NULL,
    `publication_year` SMALLINT UNSIGNED DEFAULT 2026,
    `approval_status` VARCHAR(50) NOT NULL DEFAULT 'approved',
    `views` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Products / Books'");

$targetDb->exec("INSERT INTO `products_books` (`id`, `category_id`, `seller_id`, `title`, `title_vi`, `author`, `author_vi`, `price`, `stock`, `cover_image`, `description`, `description_vi`, `isbn`, `publication_year`, `approval_status`, `views`, `created_at`)
    SELECT `id`, `danh_muc_id`, `chu_sach_id`, COALESCE(`ten_en`, `ten`), `ten`, COALESCE(`tac_gia_en`, `tac_gia`), `tac_gia`, `gia`, `ton_kho`, `anh`, COALESCE(`mo_ta_en`, `mo_ta`), `mo_ta`, `isbn`, `nam_xuat_ban`, `trang_thai_duyet`, `luot_xem`, `ngay_tao`
    FROM `book_nest_final`.`san_pham`
    ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `price`=VALUES(`price`), `stock`=VALUES(`stock`)");
echo "[OK] 13. products_books populated.\n";

// 5. Orders (Orders)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `total_amount` INT UNSIGNED NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'cho_xu_ly',
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cod',
    `payment_status` VARCHAR(50) NOT NULL DEFAULT 'chua_thanh_toan',
    `shipping_address` VARCHAR(255) NOT NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Orders'");

$targetDb->exec("INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_method`, `payment_status`, `shipping_address`, `phone_number`, `notes`, `created_at`)
    SELECT `id`, `nguoi_dung_id`, `tong_tien`, `trang_thai`, `phuong_thuc_tt`, `trang_thai_tt`, `dia_chi`, `so_dien_thoai`, `ghi_chu`, `ngay_tao`
    FROM `book_nest_final`.`don_hang`
    ON DUPLICATE KEY UPDATE `total_amount`=VALUES(`total_amount`), `status`=VALUES(`status`)");
echo "[OK] 5. orders populated.\n";

// 6. Order Details (Order Details)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `order_details` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `unit_price` INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Order Details'");

$targetDb->exec("TRUNCATE TABLE `order_details`");
$targetDb->exec("INSERT INTO `order_details` (`order_id`, `product_id`, `quantity`, `unit_price`)
    SELECT `don_hang_id`, `san_pham_id`, `so_luong`, `don_gia`
    FROM `book_nest_final`.`chi_tiet_don`");
echo "[OK] 1. order_details populated.\n";

// 7. Shopping Cart (Shopping Cart)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `shopping_cart` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Shopping Cart'");

$targetDb->exec("INSERT INTO `shopping_cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`)
    SELECT `id`, `nguoi_dung_id`, `san_pham_id`, `so_luong`, `ngay_tao`, `ngay_cap_nhat`
    FROM `book_nest_final`.`gio_hang`
    ON DUPLICATE KEY UPDATE `quantity`=VALUES(`quantity`)");
echo "[OK] 6. shopping_cart populated.\n";

// 8. System Settings (System Settings)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `system_settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` TEXT NULL,
    `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System Settings'");

$targetDb->exec("INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`)
    SELECT `khoa`, `gia_tri`, `mo_ta`
    FROM `book_nest_final`.`he_thong_cai_dat`
    ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`)");
echo "[OK] 7. system_settings populated.\n";

// 9. Contact Messages (Contact Messages)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sender_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `attachment` VARCHAR(255) NULL,
    `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact Messages'");

$targetDb->exec("INSERT INTO `contact_messages` (`id`, `sender_name`, `email`, `subject`, `message`, `attachment`, `sent_at`)
    SELECT `id`, `ho_ten`, `email`, `chu_de`, `noi_dung`, `anh`, `ngay_gui`
    FROM `book_nest_final`.`lien_he`
    ON DUPLICATE KEY UPDATE `message`=VALUES(`message`)");
echo "[OK] 8. contact_messages populated.\n";

// 10. Payment Methods (Payment Methods)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `payment_methods` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `name_vi` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `description_vi` TEXT NULL,
    `instructions` TEXT NULL,
    `instructions_vi` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment Methods'");

$targetDb->exec("INSERT INTO `payment_methods` (`id`, `code`, `name`, `name_vi`, `description`, `description_vi`, `instructions`, `instructions_vi`, `icon`, `is_active`, `sort_order`, `created_at`)
    SELECT `id`, `code`, COALESCE(`name_en`, `name`), `name`, COALESCE(`description_en`, `description`), `description`, COALESCE(`instructions_en`, `instructions`), `instructions`, `icon`, `is_active`, `sort_order`, `created_at`
    FROM `book_nest_final`.`payment_methods`
    ON DUPLICATE KEY UPDATE `name`=VALUES(`name`)");
echo "[OK] 11. payment_methods populated.\n";

// 11. Payments (Payments)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `payments` (
    `payment_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
    `amount` INT UNSIGNED NOT NULL,
    `payment_status` ENUM('pending','paid','failed','verifying','rejected','refunded') NOT NULL DEFAULT 'pending',
    `payment_proof` VARCHAR(255) NULL,
    `payment_date` DATETIME NULL,
    `admin_note` TEXT NULL,
    `verified_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payments'");

$targetDb->exec("INSERT INTO `payments` (`payment_id`, `order_id`, `user_id`, `payment_method`, `transaction_id`, `amount`, `payment_status`, `payment_proof`, `payment_date`, `admin_note`, `verified_by`, `created_at`)
    SELECT `payment_id`, `order_id`, `user_id`, `payment_method`, `transaction_id`, `amount`, `payment_status`, `payment_proof`, `payment_date`, `admin_note`, `verified_by`, `created_at`
    FROM `book_nest_final`.`payments`
    ON DUPLICATE KEY UPDATE `payment_status`=VALUES(`payment_status`)");
echo "[OK] 10. payments populated.\n";

// 12. Checkout / Payment (Legacy / Sub Checkout)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `checkout_payment` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `amount` INT UNSIGNED NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `transaction_code` VARCHAR(100) NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'dang_cho',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Checkout / Payment'");

$targetDb->exec("INSERT INTO `checkout_payment` (`id`, `order_id`, `amount`, `payment_method`, `transaction_code`, `status`, `created_at`)
    SELECT `id`, `don_hang_id`, `so_tien`, `phuong_thuc`, `ma_giao_dich`, `trang_thai`, `ngay_tao`
    FROM `book_nest_final`.`thanh_toan`
    ON DUPLICATE KEY UPDATE `amount`=VALUES(`amount`)");
echo "[OK] 15. checkout_payment populated.\n";

// 13. Reviews / Ratings (Reviews / Ratings)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `reviews_ratings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `title` VARCHAR(255) NULL,
    `content` TEXT NULL,
    `is_verified_purchase` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reviews / Ratings'");

$targetDb->exec("INSERT INTO `reviews_ratings` (`id`, `product_id`, `user_id`, `rating`, `title`, `content`, `is_verified_purchase`, `created_at`)
    SELECT `id`, `san_pham_id`, `nguoi_dung_id`, `so_sao`, `tieu_de`, `noi_dung`, `da_mua_hang`, `ngay_tao`
    FROM `book_nest_final`.`danh_gia`
    ON DUPLICATE KEY UPDATE `rating`=VALUES(`rating`)");
echo "[OK] 3. reviews_ratings populated.\n";

// 14. Address Book (Address Book)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `address_book` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `recipient_name` VARCHAR(100) NOT NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    `full_address` VARCHAR(255) NOT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Address Book'");

$targetDb->exec("INSERT INTO `address_book` (`id`, `user_id`, `recipient_name`, `phone_number`, `full_address`, `is_default`, `created_at`)
    SELECT `id`, `nguoi_dung_id`, `ho_ten`, `so_dien_thoai`, `dia_chi`, `mac_dinh`, `ngay_tao`
    FROM `book_nest_final`.`so_dia_chi`
    ON DUPLICATE KEY UPDATE `recipient_name`=VALUES(`recipient_name`)");
echo "[OK] 14. address_book populated.\n";

// 15. Notifications (Notifications)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'he_thong',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notifications'");

$targetDb->exec("INSERT INTO `notifications` (`id`, `user_id`, `title`, `content`, `type`, `is_read`, `created_at`)
    SELECT `id`, `nguoi_dung_id`, `tieu_de`, `noi_dung`, `loai`, `da_doc`, `ngay_tao`
    FROM `book_nest_final`.`thong_bao`
    ON DUPLICATE KEY UPDATE `title`=VALUES(`title`)");
echo "[OK] 17. notifications populated.\n";

// 16. Failed Login Attempts (Failed Login Attempts)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Failed Login Attempts'");

$targetDb->exec("INSERT INTO `failed_login_attempts` (`id`, `email`, `ip_address`, `attempted_at`)
    SELECT `id`, `email`, `ip`, `thoi_gian`
    FROM `book_nest_final`.`dang_nhap_sai`
    ON DUPLICATE KEY UPDATE `email`=VALUES(`email`)");
echo "[OK] 2. failed_login_attempts populated.\n";

// 17. Members (Members)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `members` (
    `student_id` VARCHAR(20) PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `user_id` INT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Members'");

$targetDb->exec("INSERT INTO `members` (`student_id`, `full_name`, `user_id`)
    SELECT `mssv`, `ho_ten`, `nguoi_dung_id`
    FROM `book_nest_final`.`thanh_vien`
    ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`)");
echo "[OK] 16. members populated.\n";

// 18. Project Members (Project Members)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `project_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(20) NOT NULL,
    `project_name` VARCHAR(200) NOT NULL,
    `technology_stack` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `github_link` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project Members'");

$targetDb->exec("INSERT INTO `project_members` (`id`, `student_id`, `project_name`, `technology_stack`, `description`, `github_link`)
    SELECT `id`, `mssv`, `ten_du_an`, `cong_nghe`, `mo_ta`, `link_github`
    FROM `book_nest_final`.`tv_du_an`
    ON DUPLICATE KEY UPDATE `project_name`=VALUES(`project_name`)");
echo "[OK] 18. project_members populated.\n";

// 19. View Counts (View Counts)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `view_counts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(20) NOT NULL,
    `view_date` DATE NOT NULL,
    `view_count` INT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='View Counts'");

$targetDb->exec("INSERT INTO `view_counts` (`id`, `student_id`, `view_date`, `view_count`)
    SELECT `id`, `mssv`, `ngay`, `so_luot`
    FROM `book_nest_final`.`tv_luot_xem`
    ON DUPLICATE KEY UPDATE `view_count`=VALUES(`view_count`)");
echo "[OK] 19. view_counts populated.\n";

// 20. Guestbook Entries (Guestbook Entries)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `guestbook_entries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(20) NOT NULL,
    `visitor_name` VARCHAR(100) NOT NULL,
    `message` TEXT NOT NULL,
    `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Guestbook Entries'");

$targetDb->exec("INSERT INTO `guestbook_entries` (`id`, `student_id`, `visitor_name`, `message`, `recorded_at`)
    SELECT `id`, `mssv`, `ten_khach`, `loi_nhan`, `thoi_gian`
    FROM `book_nest_final`.`tv_luu_but`
    ON DUPLICATE KEY UPDATE `message`=VALUES(`message`)");
echo "[OK] 20. guestbook_entries populated.\n";

// 21. Members' Favorite Books (Members' Favorite Books)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `members_favorite_books` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(20) NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `rating` TINYINT UNSIGNED NULL,
    `review` TEXT NULL,
    `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\"Members' Favorite Books\"");

$targetDb->exec("INSERT INTO `members_favorite_books` (`id`, `student_id`, `product_id`, `rating`, `review`, `added_at`)
    SELECT `id`, `mssv`, `san_pham_id`, `danh_gia`, `cam_nhan`, `ngay_them`
    FROM `book_nest_final`.`tv_sach_yeu_thich`
    ON DUPLICATE KEY UPDATE `review`=VALUES(`review`)");
echo "[OK] 21. members_favorite_books populated.\n";

// 22. Favorites (Favorites)
$targetDb->exec("CREATE TABLE IF NOT EXISTS `favorites` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Favorites'");

$targetDb->exec("INSERT INTO `favorites` (`id`, `user_id`, `product_id`, `created_at`)
    SELECT `id`, `nguoi_dung_id`, `san_pham_id`, `ngay_tao`
    FROM `book_nest_final`.`yeu_thich`
    ON DUPLICATE KEY UPDATE `created_at`=VALUES(`created_at`)");
echo "[OK] 22. favorites populated.\n";

// Create exact uppercase / formatted Views in book_nest_english for multi-word or special-character names
$exactViews = [
    'Order Details'            => "CREATE OR REPLACE VIEW `Order Details` AS SELECT * FROM `order_details`",
    'Failed Login Attempts'    => "CREATE OR REPLACE VIEW `Failed Login Attempts` AS SELECT * FROM `failed_login_attempts`",
    'Reviews / Ratings'        => "CREATE OR REPLACE VIEW `Reviews / Ratings` AS SELECT * FROM `reviews_ratings`",
    'Shopping Cart'            => "CREATE OR REPLACE VIEW `Shopping Cart` AS SELECT * FROM `shopping_cart`",
    'System Settings'          => "CREATE OR REPLACE VIEW `System Settings` AS SELECT * FROM `system_settings`",
    'Contact Messages'         => "CREATE OR REPLACE VIEW `Contact Messages` AS SELECT * FROM `contact_messages`",
    'User Roles'               => "CREATE OR REPLACE VIEW `User Roles` AS SELECT * FROM `user_roles`",
    'Products / Books'         => "CREATE OR REPLACE VIEW `Products / Books` AS SELECT * FROM `products_books`",
    'Address Book'             => "CREATE OR REPLACE VIEW `Address Book` AS SELECT * FROM `address_book`",
    'Checkout / Payment'       => "CREATE OR REPLACE VIEW `Checkout / Payment` AS SELECT * FROM `checkout_payment`",
    'Project Members'          => "CREATE OR REPLACE VIEW `Project Members` AS SELECT * FROM `project_members`",
    'View Counts'              => "CREATE OR REPLACE VIEW `View Counts` AS SELECT * FROM `view_counts`",
    'Guestbook Entries'        => "CREATE OR REPLACE VIEW `Guestbook Entries` AS SELECT * FROM `guestbook_entries`",
    "Members' Favorite Books"  => "CREATE OR REPLACE VIEW `Members' Favorite Books` AS SELECT * FROM `members_favorite_books`"
];

foreach ($exactViews as $vName => $vSql) {
    try {
        $targetDb->exec($vSql);
    } catch (Exception $e) {
        echo "  [WARN] View `$vName`: " . $e->getMessage() . "\n";
    }
}
echo "[OK] Multi-word and formatted English Views created in `book_nest_english`.\n";

echo "\nAll 22 Tables & Views successfully established in `book_nest_english`!\n";
