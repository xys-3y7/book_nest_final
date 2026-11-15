<?php
/**
 * database/create_sub_english_views.php
 * Creates the exact 16 Sub English Views requested by the user:
 * 1. Books & Textbooks
 * 2. Textbook Categories
 * 3. Orders
 * 4. Order Items / Details
 * 5. Users & Accounts
 * 6. Payments & Transactions
 * 7. Payment Methods
 * 8. User Roles
 * 9. Shopping Cart
 * 10. Book Reviews & Ratings
 * 11. Shipping Addresses
 * 12. Notifications
 * 13. Contact Inquiries
 * 14. System Settings
 * 15. Failed Login Attempts
 * 16. Wishlist / Saved Books
 */

require_once __DIR__ . '/../inc/config.php';
$db = db();

echo "Creating the 16 exact Sub English Views in book_nest_final...\n\n";

$queries = [
    'Books & Textbooks' => "
        CREATE OR REPLACE VIEW `Books & Textbooks` AS
        SELECT 
            id AS book_id,
            danh_muc_id AS category_id,
            chu_sach_id AS seller_id,
            COALESCE(ten_en, ten) AS title,
            ten AS title_vietnamese,
            ten_en AS title_english,
            COALESCE(tac_gia_en, tac_gia) AS author,
            tac_gia AS author_vietnamese,
            tac_gia_en AS author_english,
            gia AS price,
            ton_kho AS stock,
            anh AS cover_image,
            COALESCE(mo_ta_en, mo_ta) AS description,
            mo_ta AS description_vietnamese,
            mo_ta_en AS description_english,
            isbn,
            nam_xuat_ban AS publication_year,
            trang_thai_duyet AS approval_status,
            luot_xem AS views,
            ngay_tao AS created_at
        FROM san_pham
    ",

    'Textbook Categories' => "
        CREATE OR REPLACE VIEW `Textbook Categories` AS
        SELECT 
            id AS category_id,
            COALESCE(ten_en, ten) AS category_name,
            ten AS category_name_vietnamese,
            ten_en AS category_name_english,
            slug,
            COALESCE(mo_ta_en, mo_ta) AS description,
            mo_ta AS description_vietnamese,
            mo_ta_en AS description_english
        FROM danh_muc
    ",

    'Orders' => "
        CREATE OR REPLACE VIEW `Orders` AS
        SELECT 
            id AS order_id,
            nguoi_dung_id AS user_id,
            tong_tien AS total_amount,
            trang_thai AS status,
            phuong_thuc_tt AS payment_method,
            trang_thai_tt AS payment_status,
            dia_chi AS shipping_address,
            so_dien_thoai AS phone_number,
            ghi_chu AS notes,
            ngay_tao AS order_date
        FROM don_hang
    ",

    'Order Items / Details' => "
        CREATE OR REPLACE VIEW `Order Items / Details` AS
        SELECT 
            ctd.don_hang_id AS order_id,
            ctd.san_pham_id AS book_id,
            COALESCE(sp.ten_en, sp.ten) AS book_title,
            ctd.so_luong AS quantity,
            ctd.don_gia AS unit_price,
            (ctd.so_luong * ctd.don_gia) AS subtotal
        FROM chi_tiet_don ctd
        LEFT JOIN san_pham sp ON ctd.san_pham_id = sp.id
    ",

    'Users & Accounts' => "
        CREATE OR REPLACE VIEW `Users & Accounts` AS
        SELECT 
            id AS user_id,
            email,
            ho_ten AS full_name,
            vai_tro AS role,
            so_dien_thoai AS phone_number,
            dia_chi AS address,
            avatar,
            kich_hoat AS is_active,
            ten_gian_hang AS shop_name,
            mo_ta_shop AS shop_description,
            trang_thai_seller AS seller_status,
            ngay_tao AS created_at
        FROM nguoi_dung
    ",

    'Payments & Transactions' => "
        CREATE OR REPLACE VIEW `Payments & Transactions` AS
        SELECT 
            payment_id,
            order_id,
            user_id,
            payment_method,
            transaction_id,
            amount,
            payment_status,
            payment_proof,
            payment_date,
            admin_note,
            verified_by,
            created_at
        FROM payments
    ",

    'Payment Methods' => "
        CREATE OR REPLACE VIEW `Payment Methods` AS
        SELECT 
            id AS method_id,
            code,
            COALESCE(name_en, name) AS method_name,
            name AS name_vietnamese,
            name_en AS name_english,
            COALESCE(description_en, description) AS description,
            COALESCE(instructions_en, instructions) AS instructions,
            icon,
            is_active,
            sort_order,
            created_at
        FROM payment_methods
    ",

    'User Roles' => "
        CREATE OR REPLACE VIEW `User Roles` AS
        SELECT 
            id AS role_id,
            name AS role_code,
            display_name,
            description
        FROM roles
    ",

    'Shopping Cart' => "
        CREATE OR REPLACE VIEW `Shopping Cart` AS
        SELECT 
            gh.id AS cart_item_id,
            gh.nguoi_dung_id AS user_id,
            gh.san_pham_id AS book_id,
            COALESCE(sp.ten_en, sp.ten) AS book_title,
            sp.gia AS unit_price,
            gh.so_luong AS quantity,
            (gh.so_luong * sp.gia) AS subtotal,
            gh.ngay_tao AS added_at,
            gh.ngay_cap_nhat AS updated_at
        FROM gio_hang gh
        LEFT JOIN san_pham sp ON gh.san_pham_id = sp.id
    ",

    'Book Reviews & Ratings' => "
        CREATE OR REPLACE VIEW `Book Reviews & Ratings` AS
        SELECT 
            dg.id AS review_id,
            dg.san_pham_id AS book_id,
            COALESCE(sp.ten_en, sp.ten) AS book_title,
            dg.nguoi_dung_id AS user_id,
            nd.ho_ten AS customer_name,
            dg.so_sao AS rating_stars,
            dg.tieu_de AS review_title,
            dg.noi_dung AS review_content,
            dg.da_mua_hang AS is_verified_purchase,
            dg.ngay_tao AS review_date
        FROM danh_gia dg
        LEFT JOIN san_pham sp ON dg.san_pham_id = sp.id
        LEFT JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id
    ",

    'Shipping Addresses' => "
        CREATE OR REPLACE VIEW `Shipping Addresses` AS
        SELECT 
            id AS address_id,
            nguoi_dung_id AS user_id,
            ho_ten AS recipient_name,
            so_dien_thoai AS phone_number,
            dia_chi AS full_address,
            mac_dinh AS is_default,
            ngay_tao AS created_at
        FROM so_dia_chi
    ",

    'Notifications' => "
        CREATE OR REPLACE VIEW `Notifications` AS
        SELECT 
            id AS notification_id,
            nguoi_dung_id AS user_id,
            tieu_de AS title,
            noi_dung AS content,
            loai AS notification_type,
            da_doc AS is_read,
            ngay_tao AS created_at
        FROM thong_bao
    ",

    'Contact Inquiries' => "
        CREATE OR REPLACE VIEW `Contact Inquiries` AS
        SELECT 
            id AS inquiry_id,
            ho_ten AS full_name,
            email,
            chu_de AS subject,
            noi_dung AS message,
            anh AS attachment,
            ngay_gui AS submitted_at
        FROM lien_he
    ",

    'System Settings' => "
        CREATE OR REPLACE VIEW `System Settings` AS
        SELECT 
            khoa AS setting_key,
            gia_tri AS setting_value,
            mo_ta AS description
        FROM he_thong_cai_dat
    ",

    'Failed Login Attempts' => "
        CREATE OR REPLACE VIEW `Failed Login Attempts` AS
        SELECT 
            id AS attempt_id,
            email,
            ip AS ip_address,
            thoi_gian AS attempted_at
        FROM dang_nhap_sai
    ",

    'Wishlist / Saved Books' => "
        CREATE OR REPLACE VIEW `Wishlist / Saved Books` AS
        SELECT 
            yt.id AS wishlist_id,
            yt.nguoi_dung_id AS user_id,
            yt.san_pham_id AS book_id,
            COALESCE(sp.ten_en, sp.ten) AS book_title,
            sp.gia AS book_price,
            yt.ngay_tao AS saved_at
        FROM yeu_thich yt
        LEFT JOIN san_pham sp ON yt.san_pham_id = sp.id
    "
];

$success = 0;
foreach ($queries as $name => $sql) {
    try {
        $db->exec($sql);
        echo "  [OK] Created View: `$name`\n";
        $success++;
    } catch (Exception $e) {
        echo "  [FAIL] View `$name`: " . $e->getMessage() . "\n";
    }
}

echo "\nCompleted: $success / " . count($queries) . " Sub English Views created!\n";
