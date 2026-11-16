<?php
/**
 * database/apply_exact_22_english_tables.php
 * Creates the exact 22 English Views and updates Table Comments in book_nest_final
 * according to user's exact specification:
 * 1. chi_tiet_don       -> Order Details
 * 2. dang_nhap_sai      -> Failed Login Attempts
 * 3. danh_gia           -> Reviews / Ratings
 * 4. danh_muc           -> Categories
 * 5. don_hang           -> Orders
 * 6. gio_hang           -> Shopping Cart
 * 7. he_thong_cai_dat   -> System Settings
 * 8. lien_he            -> Contact Messages
 * 9. nguoi_dung         -> Users
 * 10. payments          -> Payments
 * 11. payment_methods   -> Payment Methods
 * 12. roles             -> User Roles
 * 13. san_pham          -> Products / Books
 * 14. so_dia_chi        -> Address Book
 * 15. thanh_toan        -> Checkout / Payment
 * 16. thanh_vien        -> Members
 * 17. thong_bao         -> Notifications
 * 18. tv_du_an          -> Project Members
 * 19. tv_luot_xem       -> View Counts
 * 20. tv_luu_but        -> Guestbook Entries
 * 21. tv_sach_yeu_thich -> Members' Favorite Books
 * 22. yeu_thich         -> Favorites
 */

require_once __DIR__ . '/../inc/config.php';
$db = db();

echo "1. Updating Table Comments for all 22 tables to exact user names...\n";

$tableComments = [
    'chi_tiet_don'      => 'Order Details',
    'dang_nhap_sai'     => 'Failed Login Attempts',
    'danh_gia'          => 'Reviews / Ratings',
    'danh_muc'          => 'Categories',
    'don_hang'          => 'Orders',
    'gio_hang'          => 'Shopping Cart',
    'he_thong_cai_dat'  => 'System Settings',
    'lien_he'           => 'Contact Messages',
    'nguoi_dung'        => 'Users',
    'payments'          => 'Payments',
    'payment_methods'   => 'Payment Methods',
    'roles'             => 'User Roles',
    'san_pham'          => 'Products / Books',
    'so_dia_chi'        => 'Address Book',
    'thanh_toan'        => 'Checkout / Payment',
    'thanh_vien'        => 'Members',
    'thong_bao'         => 'Notifications',
    'tv_du_an'          => 'Project Members',
    'tv_luot_xem'       => 'View Counts',
    'tv_luu_but'        => 'Guestbook Entries',
    'tv_sach_yeu_thich' => "Members' Favorite Books",
    'yeu_thich'         => 'Favorites'
];

foreach ($tableComments as $tbl => $cmt) {
    try {
        $db->exec("ALTER TABLE `$tbl` COMMENT = " . $db->quote($cmt));
        echo "  [OK] Comment on `$tbl` -> '$cmt'\n";
    } catch (Exception $e) {
        echo "  [FAIL] Comment on `$tbl`: " . $e->getMessage() . "\n";
    }
}

echo "\n2. Creating the exact 22 English Views...\n";

$viewDefinitions = [
    // 1. Order Details
    'Order Details' => "
        CREATE OR REPLACE VIEW `Order Details` AS
        SELECT 
            ctd.don_hang_id AS order_id,
            ctd.san_pham_id AS product_id,
            COALESCE(sp.ten_en, sp.ten) AS product_name,
            ctd.so_luong AS quantity,
            ctd.don_gia AS unit_price,
            (ctd.so_luong * ctd.don_gia) AS total_price
        FROM chi_tiet_don ctd
        LEFT JOIN san_pham sp ON ctd.san_pham_id = sp.id
    ",

    // 2. Failed Login Attempts
    'Failed Login Attempts' => "
        CREATE OR REPLACE VIEW `Failed Login Attempts` AS
        SELECT 
            id AS attempt_id,
            email,
            ip AS ip_address,
            thoi_gian AS attempted_at
        FROM dang_nhap_sai
    ",

    // 3. Reviews / Ratings
    'Reviews / Ratings' => "
        CREATE OR REPLACE VIEW `Reviews / Ratings` AS
        SELECT 
            dg.id AS review_id,
            dg.san_pham_id AS product_id,
            COALESCE(sp.ten_en, sp.ten) AS product_name,
            dg.nguoi_dung_id AS user_id,
            nd.ho_ten AS user_name,
            dg.so_sao AS rating,
            dg.tieu_de AS review_title,
            dg.noi_dung AS review_content,
            dg.da_mua_hang AS is_verified_purchase,
            dg.ngay_tao AS created_at
        FROM danh_gia dg
        LEFT JOIN san_pham sp ON dg.san_pham_id = sp.id
        LEFT JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id
    ",

    // 4. Categories
    'Categories' => "
        CREATE OR REPLACE VIEW `Categories` AS
        SELECT 
            id,
            id AS category_id,
            COALESCE(ten_en, ten) AS name,
            COALESCE(ten_en, ten) AS category_name,
            ten AS name_vi,
            ten AS category_name_vi,
            ten_en AS name_en,
            ten_en AS category_name_en,
            slug,
            COALESCE(mo_ta_en, mo_ta) AS description,
            mo_ta AS description_vi,
            mo_ta_en AS description_en
        FROM danh_muc
    ",

    // 5. Orders
    'Orders' => "
        CREATE OR REPLACE VIEW `Orders` AS
        SELECT 
            id AS order_id,
            nguoi_dung_id AS user_id,
            tong_tien AS total_amount,
            trang_thai AS order_status,
            phuong_thuc_tt AS payment_method,
            trang_thai_tt AS payment_status,
            dia_chi AS shipping_address,
            so_dien_thoai AS phone_number,
            ghi_chu AS notes,
            ngay_tao AS order_date
        FROM don_hang
    ",

    // 6. Shopping Cart
    'Shopping Cart' => "
        CREATE OR REPLACE VIEW `Shopping Cart` AS
        SELECT 
            gh.id AS cart_item_id,
            gh.nguoi_dung_id AS user_id,
            gh.san_pham_id AS product_id,
            COALESCE(sp.ten_en, sp.ten) AS product_name,
            sp.gia AS unit_price,
            gh.so_luong AS quantity,
            (gh.so_luong * sp.gia) AS subtotal,
            gh.ngay_tao AS created_at,
            gh.ngay_cap_nhat AS updated_at
        FROM gio_hang gh
        LEFT JOIN san_pham sp ON gh.san_pham_id = sp.id
    ",

    // 7. System Settings
    'System Settings' => "
        CREATE OR REPLACE VIEW `System Settings` AS
        SELECT 
            khoa AS setting_key,
            gia_tri AS setting_value,
            mo_ta AS description
        FROM he_thong_cai_dat
    ",

    // 8. Contact Messages
    'Contact Messages' => "
        CREATE OR REPLACE VIEW `Contact Messages` AS
        SELECT 
            id AS message_id,
            ho_ten AS sender_name,
            email,
            chu_de AS subject,
            noi_dung AS message,
            anh AS attachment,
            ngay_gui AS sent_at
        FROM lien_he
    ",

    // 9. Users
    'Users' => "
        CREATE OR REPLACE VIEW `Users` AS
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

    // 11. Payment Methods
    'Payment Methods' => "
        CREATE OR REPLACE VIEW `Payment Methods` AS
        SELECT 
            id AS method_id,
            code,
            COALESCE(name_en, name) AS method_name,
            name AS name_vi,
            name_en AS name_en,
            COALESCE(description_en, description) AS description,
            COALESCE(instructions_en, instructions) AS instructions,
            icon,
            is_active,
            sort_order,
            created_at
        FROM payment_methods
    ",

    // 12. User Roles
    'User Roles' => "
        CREATE OR REPLACE VIEW `User Roles` AS
        SELECT 
            id AS role_id,
            name AS role_code,
            display_name,
            description
        FROM roles
    ",

    // 13. Products / Books
    'Products / Books' => "
        CREATE OR REPLACE VIEW `Products / Books` AS
        SELECT 
            id AS product_id,
            danh_muc_id AS category_id,
            chu_sach_id AS seller_id,
            COALESCE(ten_en, ten) AS product_title,
            ten AS title_vi,
            ten_en AS title_en,
            COALESCE(tac_gia_en, tac_gia) AS author,
            tac_gia AS author_vi,
            tac_gia_en AS author_en,
            gia AS price,
            ton_kho AS stock,
            anh AS cover_image,
            COALESCE(mo_ta_en, mo_ta) AS description,
            mo_ta AS description_vi,
            mo_ta_en AS description_en,
            isbn,
            nam_xuat_ban AS publication_year,
            trang_thai_duyet AS approval_status,
            luot_xem AS views,
            ngay_tao AS created_at
        FROM san_pham
    ",

    // 14. Address Book
    'Address Book' => "
        CREATE OR REPLACE VIEW `Address Book` AS
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

    // 15. Checkout / Payment
    'Checkout / Payment' => "
        CREATE OR REPLACE VIEW `Checkout / Payment` AS
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

    // 16. Members
    'Members' => "
        CREATE OR REPLACE VIEW `Members` AS
        SELECT 
            tv.mssv AS student_id,
            tv.ho_ten AS member_name,
            tv.nguoi_dung_id AS user_id,
            nd.email,
            nd.avatar
        FROM thanh_vien tv
        LEFT JOIN nguoi_dung nd ON tv.nguoi_dung_id = nd.id
    ",

    // 17. Notifications
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

    // 18. Project Members
    'Project Members' => "
        CREATE OR REPLACE VIEW `Project Members` AS
        SELECT 
            da.id AS project_id,
            da.mssv AS student_id,
            tv.ho_ten AS student_name,
            da.ten_du_an AS project_name,
            da.cong_nghe AS technology_stack,
            da.mo_ta AS description,
            da.link_github AS github_link
        FROM tv_du_an da
        LEFT JOIN thanh_vien tv ON da.mssv = tv.mssv
    ",

    // 19. View Counts
    'View Counts' => "
        CREATE OR REPLACE VIEW `View Counts` AS
        SELECT 
            lx.id AS count_id,
            lx.mssv AS student_id,
            tv.ho_ten AS student_name,
            lx.ngay AS view_date,
            lx.so_luot AS view_count
        FROM tv_luot_xem lx
        LEFT JOIN thanh_vien tv ON lx.mssv = tv.mssv
    ",

    // 20. Guestbook Entries
    'Guestbook Entries' => "
        CREATE OR REPLACE VIEW `Guestbook Entries` AS
        SELECT 
            lb.id AS entry_id,
            lb.mssv AS student_id,
            tv.ho_ten AS student_name,
            lb.ten_khach AS visitor_name,
            lb.loi_nhan AS message,
            lb.thoi_gian AS recorded_at
        FROM tv_luu_but lb
        LEFT JOIN thanh_vien tv ON lb.mssv = tv.mssv
    ",

    // 21. Members' Favorite Books
    "Members' Favorite Books" => "
        CREATE OR REPLACE VIEW `Members' Favorite Books` AS
        SELECT 
            syt.id AS favorite_id,
            syt.mssv AS student_id,
            tv.ho_ten AS student_name,
            syt.san_pham_id AS product_id,
            COALESCE(sp.ten_en, sp.ten) AS book_title,
            syt.danh_gia AS rating,
            syt.cam_nhan AS thoughts_and_review,
            syt.ngay_them AS added_date
        FROM tv_sach_yeu_thich syt
        LEFT JOIN thanh_vien tv ON syt.mssv = tv.mssv
        LEFT JOIN san_pham sp ON syt.san_pham_id = sp.id
    ",

    // 22. Favorites
    'Favorites' => "
        CREATE OR REPLACE VIEW `Favorites` AS
        SELECT 
            yt.id AS favorite_id,
            yt.nguoi_dung_id AS user_id,
            yt.san_pham_id AS product_id,
            COALESCE(sp.ten_en, sp.ten) AS product_name,
            sp.gia AS price,
            yt.ngay_tao AS saved_at
        FROM yeu_thich yt
        LEFT JOIN san_pham sp ON yt.san_pham_id = sp.id
    "
];

$success = 0;
foreach ($viewDefinitions as $name => $sql) {
    try {
        $db->exec($sql);
        echo "  [OK] View `$name` created successfully!\n";
        $success++;
    } catch (Exception $e) {
        echo "  [FAIL] View `$name`: " . $e->getMessage() . "\n";
    }
}

echo "\nCompleted: $success / " . count($viewDefinitions) . " exact English views created!\n";
