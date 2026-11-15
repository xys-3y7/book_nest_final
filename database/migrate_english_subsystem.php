<?php
/**
 * database/migrate_english_subsystem.php
 * Migration script to implement the English Database Subsystem for BookNest
 */

require_once __DIR__ . '/../inc/config.php';

$db = db();
$activeDb = $db->query("SELECT DATABASE()")->fetchColumn();
echo "Executing English Database Subsystem Migration on '$activeDb'...\n";

// 1. Alter danh_muc to add English translation columns
$colsDm = $db->query("DESCRIBE danh_muc")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('ten_en', $colsDm, true)) {
    $db->exec("ALTER TABLE danh_muc ADD COLUMN ten_en VARCHAR(150) NULL AFTER ten");
    echo "Added 'ten_en' to danh_muc.\n";
}
if (!in_array('mo_ta_en', $colsDm, true)) {
    $db->exec("ALTER TABLE danh_muc ADD COLUMN mo_ta_en TEXT NULL AFTER mo_ta");
    echo "Added 'mo_ta_en' to danh_muc.\n";
}

// 2. Alter san_pham to add English translation columns
$colsSp = $db->query("DESCRIBE san_pham")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('ten_en', $colsSp, true)) {
    $db->exec("ALTER TABLE san_pham ADD COLUMN ten_en VARCHAR(255) NULL AFTER ten");
    echo "Added 'ten_en' to san_pham.\n";
}
if (!in_array('tac_gia_en', $colsSp, true)) {
    $db->exec("ALTER TABLE san_pham ADD COLUMN tac_gia_en VARCHAR(150) NULL AFTER tac_gia");
    echo "Added 'tac_gia_en' to san_pham.\n";
}
if (!in_array('mo_ta_en', $colsSp, true)) {
    $db->exec("ALTER TABLE san_pham ADD COLUMN mo_ta_en TEXT NULL AFTER mo_ta");
    echo "Added 'mo_ta_en' to san_pham.\n";
}

// 3. Alter payment_methods to add English translation columns
$colsPm = $db->query("DESCRIBE payment_methods")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('name_en', $colsPm, true)) {
    $db->exec("ALTER TABLE payment_methods ADD COLUMN name_en VARCHAR(150) NULL AFTER name");
    echo "Added 'name_en' to payment_methods.\n";
}
if (!in_array('description_en', $colsPm, true)) {
    $db->exec("ALTER TABLE payment_methods ADD COLUMN description_en TEXT NULL AFTER description");
    echo "Added 'description_en' to payment_methods.\n";
}
if (!in_array('instructions_en', $colsPm, true)) {
    $db->exec("ALTER TABLE payment_methods ADD COLUMN instructions_en TEXT NULL AFTER instructions");
    echo "Added 'instructions_en' to payment_methods.\n";
}

// 4. Populate English categories
$categoriesData = [
    [
        'ten'        => 'Công Nghệ Thông Tin & Lập Trình',
        'ten_en'     => 'Computer Science & Software Engineering',
        'slug'       => 'cong-nghe-thong-tin-lap-trinh',
        'mo_ta'      => 'Giáo trình CNTT, kỹ thuật phần mềm, cấu trúc dữ liệu và giải thuật',
        'mo_ta_en'   => 'University textbooks for Computer Science, Software Engineering, Algorithms, and System Architecture.'
    ],
    [
        'ten'        => 'Toán Học & Khoa Học Tự Nhiên',
        'ten_en'     => 'Mathematics & Natural Sciences',
        'slug'       => 'toan-hoc-khoa-hoc-tu-nhien',
        'mo_ta'      => 'Giáo trình Giải tích, Đại số tuyến tính, Xác suất thống kê',
        'mo_ta_en'   => 'Core academic textbooks covering Calculus, Linear Algebra, Probability, and Discrete Mathematics.'
    ],
    [
        'ten'        => 'Kỹ năng sống & Phát triển',
        'ten_en'     => 'Life Skills & Personal Development',
        'slug'       => 'ky-nang-song',
        'mo_ta'      => 'Kỹ năng tư duy phản biện, giao tiếp, quản lý thời gian và phát triển bản thân',
        'mo_ta_en'   => 'Critical thinking, communication, time management, leadership, and personal growth for university students.'
    ],
    [
        'ten'        => 'Ngoại ngữ & Du học',
        'ten_en'     => 'Foreign Languages & Global Studies',
        'slug'       => 'ngoai-ngu-du-hoc',
        'mo_ta'      => 'Tài liệu luyện thi IELTS, TOEIC, tiếng Nhật, tiếng Trung',
        'mo_ta_en'   => 'Language proficiency textbooks, IELTS/TOEIC test preparation, Japanese, and academic English.'
    ],
    [
        'ten'        => 'Data Science & Artificial Intelligence',
        'ten_en'     => 'Data Science & Artificial Intelligence',
        'slug'       => 'data-science-artificial-intelligence',
        'mo_ta'      => 'Giáo trình chuyên sâu Machine Learning, Deep Neural Networks, Big Data và Trí tuệ nhân tạo',
        'mo_ta_en'   => 'Specialized university textbooks on Machine Learning, Deep Neural Networks, Big Data, and AI Engineering.'
    ]
];

$stUpDm = $db->prepare("INSERT INTO danh_muc (ten, ten_en, slug, mo_ta, mo_ta_en) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ten=VALUES(ten), ten_en=VALUES(ten_en), mo_ta=VALUES(mo_ta), mo_ta_en=VALUES(mo_ta_en)");
foreach ($categoriesData as $c) {
    $stUpDm->execute([$c['ten'], $c['ten_en'], $c['slug'], $c['mo_ta'], $c['mo_ta_en']]);
}
echo "Populated English categories.\n";

// 5. Populate English translations for Books
$books = $db->query("SELECT id, ten, tac_gia, mo_ta FROM san_pham")->fetchAll();
$stUpBook = $db->prepare("UPDATE san_pham SET ten_en = ?, tac_gia_en = ?, mo_ta_en = ? WHERE id = ?");

foreach ($books as $b) {
    $id = (int)$b['id'];
    $tenEn = $b['ten']; // Already largely in English or clean title
    $tacGiaEn = $b['tac_gia'] ?: 'BookNest Editorial';
    $moTaEn = $b['mo_ta'] ?: 'Standard academic textbook recommended by university faculties for semester coursework and exams.';

    // Customized English descriptions for recognized titles
    if (str_contains($b['ten'], 'Python')) {
        $tenEn = 'Python for Modern Software Engineering (2nd Edition)';
        $tacGiaEn = 'Dr. Robert Vance';
        $moTaEn = 'Comprehensive guide covering Python 3.12+, clean architecture, concurrency, and enterprise design patterns.';
    } elseif (str_contains($b['ten'], 'Clean Architecture')) {
        $tenEn = 'Clean Architecture & Scalable Systems';
        $tacGiaEn = 'Martin C. Fowler';
        $moTaEn = 'Essential architectural principles for decoupled, highly testable, and maintainable enterprise software.';
    } elseif (str_contains($b['ten'], 'Web Development') || str_contains($b['ten'], 'PHP')) {
        $tenEn = 'Modern Web Development with PHP & MySQL';
        $tacGiaEn = 'Prof. David Miller';
        $moTaEn = 'Practical textbook covering secure PHP 8+, PDO database abstraction, REST APIs, and OWASP security standards.';
    } elseif (str_contains($b['ten'], 'Database') || str_contains($b['ten'], 'SQL')) {
        $tenEn = 'Database Systems & SQL Optimization';
        $tacGiaEn = 'Elena Rostova';
        $moTaEn = 'In-depth exploration of relational database theory, index indexing algorithms, query tuning, and transactions.';
    } elseif (str_contains($b['ten'], 'Rich Dad') || str_contains($b['ten'], 'Finance')) {
        $tenEn = 'Rich Dad Poor Dad: Personal Finance Secrets';
        $tacGiaEn = 'Robert T. Kiyosaki';
        $moTaEn = 'Global bestselling guide on financial literacy, wealth creation, cashflow management, and asset accumulation.';
    } elseif (str_contains($b['ten'], 'Artificial Intelligence') || str_contains($b['ten'], 'AI')) {
        $tenEn = 'Artificial Intelligence: A Modern Approach (4th Edition)';
        $tacGiaEn = 'Stuart Russell & Peter Norvig';
        $moTaEn = 'The standard university textbook on AI, search heuristics, probability reasoning, and deep reinforcement learning.';
    } elseif (str_contains($b['ten'], 'Algorithms') || str_contains($b['ten'], 'Giải thuật')) {
        $tenEn = 'Introduction to Algorithms (CLRS 4th Edition)';
        $tacGiaEn = 'Thomas H. Cormen, Charles E. Leiserson';
        $moTaEn = 'The definitive global university reference for sorting, dynamic programming, graph algorithms, and NP-completeness.';
    }

    $stUpBook->execute([$tenEn, $tacGiaEn, $moTaEn, $id]);
}
echo "Populated English translations for " . count($books) . " books.\n";

// 6. Update English translations for payment_methods
$paymentMethodsEn = [
    'cod' => [
        'name_en'         => 'Cash on Delivery (COD)',
        'description_en'  => 'Pay with cash upon textbook delivery at UED campus or dormitory',
        'instructions_en' => 'Please prepare the exact cash amount when receiving textbooks from our student courier.'
    ],
    'bank_transfer' => [
        'name_en'         => 'Bank Transfer / QR Pay',
        'description_en'  => 'Transfer directly to BookNest official student account via mobile banking',
        'instructions_en' => "Bank: Vietcombank - Danang Branch\nAccount No: 02363841323\nAccount Name: BOOKNEST STORE UED\nTransfer memo: BN{ORDER_ID}"
    ],
    'qr_code' => [
        'name_en'         => 'QR Code Payment',
        'description_en'  => 'Scan dynamic QR code (PromptPay / VietQR standard) for instant settlement',
        'instructions_en' => 'Scan the QR code displayed on the checkout or order screen using any banking or payment app.'
    ],
    'e_wallet' => [
        'name_en'         => 'E-Wallet (MoMo / TrueMoney)',
        'description_en'  => 'Fast and secure checkout via MoMo or digital student e-wallets',
        'instructions_en' => 'Scan MoMo QR code or transfer to 0905123456 (BookNest Admin).'
    ]
];

$stUpPm = $db->prepare("UPDATE payment_methods SET name_en = ?, description_en = ?, instructions_en = ? WHERE code = ?");
foreach ($paymentMethodsEn as $code => $pme) {
    $stUpPm->execute([$pme['name_en'], $pme['description_en'], $pme['instructions_en'], $code]);
}
echo "Populated English payment methods.\n";

// 7. Recreate complete English Views
echo "Recreating English Compatibility Views...\n";

$db->exec("CREATE OR REPLACE VIEW books AS 
  SELECT id, danh_muc_id AS category_id, chu_sach_id AS seller_id, 
         COALESCE(ten_en, ten) AS title, ten AS title_vi, ten_en AS title_en,
         COALESCE(tac_gia_en, tac_gia) AS author, tac_gia AS author_vi, tac_gia_en AS author_en,
         isbn, nam_xuat_ban AS publication_year, gia AS price,
         ton_kho AS stock, anh AS cover_image, 
         COALESCE(mo_ta_en, mo_ta) AS description, mo_ta AS description_vi, mo_ta_en AS description_en,
         trang_thai_duyet AS approval_status, luot_xem AS views, ngay_tao AS created_at
  FROM san_pham");

$db->exec("CREATE OR REPLACE VIEW categories AS 
  SELECT id, COALESCE(ten_en, ten) AS name, ten AS name_vi, ten_en AS name_en,
         slug, COALESCE(mo_ta_en, mo_ta) AS description, mo_ta AS description_vi, mo_ta_en AS description_en
  FROM danh_muc");

$db->exec("CREATE OR REPLACE VIEW users AS 
  SELECT id, email, ho_ten AS full_name, vai_tro AS role, kich_hoat AS is_active,
         so_dien_thoai AS phone, dia_chi AS address, avatar, ten_gian_hang AS shop_name,
         mo_ta_shop AS shop_description, trang_thai_seller AS seller_status, ngay_tao AS created_at
  FROM nguoi_dung");

$db->exec("CREATE OR REPLACE VIEW sellers AS
  SELECT id, email, ho_ten AS owner_name, ten_gian_hang AS shop_name,
         mo_ta_shop AS shop_description, logo_shop, so_dien_thoai AS phone,
         trang_thai_seller AS status, kich_hoat AS is_active, ngay_tao AS registered_at
  FROM nguoi_dung
  WHERE vai_tro = 'chu_sach'");

$db->exec("CREATE OR REPLACE VIEW orders AS 
  SELECT id, nguoi_dung_id AS user_id, tong_tien AS total_amount, trang_thai AS status,
         phuong_thuc_tt AS payment_method, trang_thai_tt AS payment_status,
         dia_chi AS shipping_address, so_dien_thoai AS phone, ghi_chu AS notes,
         ngay_tao AS created_at
  FROM don_hang");

$db->exec("CREATE OR REPLACE VIEW order_items AS 
  SELECT don_hang_id AS order_id, san_pham_id AS book_id, so_luong AS quantity,
         don_gia AS unit_price
  FROM chi_tiet_don");

$db->exec("CREATE OR REPLACE VIEW shopping_carts AS
  SELECT nguoi_dung_id AS user_id, san_pham_id AS book_id, so_luong AS quantity
  FROM gio_hang");

$db->exec("CREATE OR REPLACE VIEW cart_items AS
  SELECT nguoi_dung_id AS user_id, san_pham_id AS book_id, so_luong AS quantity
  FROM gio_hang");

$db->exec("CREATE OR REPLACE VIEW wishlists AS
  SELECT nguoi_dung_id AS user_id, san_pham_id AS book_id, ngay_tao AS created_at
  FROM yeu_thich");

$db->exec("CREATE OR REPLACE VIEW reviews AS 
  SELECT id, san_pham_id AS book_id, nguoi_dung_id AS user_id, so_sao AS rating,
         tieu_de AS title, noi_dung AS content, da_mua_hang AS is_verified_purchase, ngay_tao AS created_at
  FROM danh_gia");

$db->exec("CREATE OR REPLACE VIEW addresses AS 
  SELECT id, nguoi_dung_id AS user_id, ho_ten AS recipient_name,
         so_dien_thoai AS phone, dia_chi AS address, mac_dinh AS is_default,
         ngay_tao AS created_at
  FROM so_dia_chi");

$db->exec("CREATE OR REPLACE VIEW notifications AS 
  SELECT id, nguoi_dung_id AS user_id, tieu_de AS title, noi_dung AS content,
         loai AS type, da_doc AS is_read, ngay_tao AS created_at
  FROM thong_bao");

echo "English Database Subsystem Migration Completed Successfully!\n";
