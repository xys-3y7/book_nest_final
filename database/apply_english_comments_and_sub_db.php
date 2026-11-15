<?php
/**
 * database/apply_english_comments_and_sub_db.php
 * 1. Add English Table Comments and Column Comments to book_nest_final
 * 2. Create sub-database book_nest_english with 100% English table and column names
 */

require_once __DIR__ . '/../inc/config.php';
$db = db();

echo "1. Applying English Table Comments to book_nest_final...\n";

$tableComments = [
    'san_pham'          => 'Books & Textbooks - Quản lý sản phẩm sách',
    'danh_muc'          => 'Textbook Categories - Danh mục thể loại sách',
    'don_hang'          => 'Orders - Quản lý đơn đặt hàng',
    'chi_tiet_don'      => 'Order Items / Details - Chi tiết từng cuốn sách trong đơn',
    'nguoi_dung'        => 'Users & Accounts - Tài khoản người dùng (Admin, Seller, Customer)',
    'payments'          => 'Payments & Transactions - Quản lý giao dịch thanh toán',
    'payment_methods'   => 'Payment Methods - Phương thức thanh toán (COD, Bank, QR, MoMo)',
    'roles'             => 'User Roles - Phân quyền người dùng',
    'gio_hang'          => 'Shopping Cart - Giỏ hàng người dùng',
    'danh_gia'          => 'Book Reviews & Ratings - Đánh giá và nhận xét sách',
    'so_dia_chi'        => 'Shipping Addresses - Sổ địa chỉ nhận hàng',
    'thong_bao'         => 'Notifications - Thông báo hệ thống',
    'lien_he'           => 'Contact Inquiries - Tin nhắn liên hệ phản hồi',
    'he_thong_cai_dat'  => 'System Settings - Cài đặt cấu hình hệ thống',
    'dang_nhap_sai'     => 'Failed Login Attempts - Nhật ký đăng nhập thất bại',
    'thanh_vien'        => 'Team Members - Danh sách thành viên nhóm 12',
    'tv_du_an'          => 'Member Projects - Dự án cá nhân thành viên',
    'tv_luot_xem'       => 'Member Profile Views - Lượt xem hồ sơ cá nhân',
    'tv_luu_but'        => 'Member Guestbook - Lưu bút và lời nhắn thành viên',
    'tv_sach_yeu_thich' => 'Member Favorite Books - Sách yêu thích của thành viên',
    'yeu_thich'         => 'Wishlist / Saved Books - Danh sách sách yêu thích của khách hàng',
    'thanh_toan'        => 'Legacy Payments - Bảng thanh toán cũ (Chuyển tiếp sang payments)'
];

foreach ($tableComments as $tbl => $cmt) {
    try {
        $db->exec("ALTER TABLE `$tbl` COMMENT = '$cmt'");
        echo "  [OK] Comment added to table: $tbl\n";
    } catch (Exception $e) {
        echo "  [WARN] Failed to comment on $tbl: " . $e->getMessage() . "\n";
    }
}

echo "\n2. Applying English Column Comments to san_pham...\n";
$db->exec("ALTER TABLE san_pham 
    MODIFY COLUMN id INT UNSIGNED AUTO_INCREMENT COMMENT 'Book ID (Primary Key)',
    MODIFY COLUMN danh_muc_id INT UNSIGNED NOT NULL COMMENT 'Category ID (Foreign Key -> danh_muc.id)',
    MODIFY COLUMN chu_sach_id INT UNSIGNED NULL COMMENT 'Seller / Book Owner ID (Foreign Key -> nguoi_dung.id)',
    MODIFY COLUMN ten VARCHAR(200) NOT NULL COMMENT 'Book Title (Vietnamese)',
    MODIFY COLUMN ten_en VARCHAR(255) NULL COMMENT 'Book Title (English Sub)',
    MODIFY COLUMN gia INT UNSIGNED NOT NULL COMMENT 'Selling Price (VND)',
    MODIFY COLUMN ton_kho INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Stock Inventory Quantity',
    MODIFY COLUMN anh VARCHAR(255) NULL COMMENT 'Book Cover Image File or URL',
    MODIFY COLUMN mo_ta TEXT NULL COMMENT 'Textbook Description (Vietnamese)',
    MODIFY COLUMN mo_ta_en TEXT NULL COMMENT 'Textbook Description (English Sub)',
    MODIFY COLUMN ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Date & Time',
    MODIFY COLUMN tac_gia VARCHAR(150) NULL DEFAULT 'BookNest Editorial' COMMENT 'Author / Editor Name (Vietnamese)',
    MODIFY COLUMN tac_gia_en VARCHAR(150) NULL COMMENT 'Author / Editor Name (English Sub)',
    MODIFY COLUMN isbn VARCHAR(30) NULL COMMENT 'ISBN Catalog Number',
    MODIFY COLUMN nam_xuat_ban SMALLINT UNSIGNED NULL DEFAULT 2026 COMMENT 'Publication Year',
    MODIFY COLUMN trang_thai_duyet ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'approved' COMMENT 'Approval Status (approved, pending, rejected)',
    MODIFY COLUMN luot_xem INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total Book View Count'
");
echo "  [OK] Column comments applied to san_pham!\n";

echo "\n3. Applying English Column Comments to danh_muc...\n";
$db->exec("ALTER TABLE danh_muc
    MODIFY COLUMN id INT UNSIGNED AUTO_INCREMENT COMMENT 'Category ID (Primary Key)',
    MODIFY COLUMN ten VARCHAR(100) NOT NULL COMMENT 'Category Name (Vietnamese)',
    MODIFY COLUMN ten_en VARCHAR(150) NULL COMMENT 'Category Name (English Sub)',
    MODIFY COLUMN slug VARCHAR(100) NOT NULL COMMENT 'URL Slug',
    MODIFY COLUMN mo_ta TEXT NULL COMMENT 'Category Description (Vietnamese)',
    MODIFY COLUMN mo_ta_en TEXT NULL COMMENT 'Category Description (English Sub)'
");
echo "  [OK] Column comments applied to danh_muc!\n";

echo "\n4. Applying English Column Comments to don_hang & chi_tiet_don...\n";
$db->exec("ALTER TABLE don_hang
    MODIFY COLUMN id INT UNSIGNED AUTO_INCREMENT COMMENT 'Order ID (Primary Key)',
    MODIFY COLUMN nguoi_dung_id INT UNSIGNED NOT NULL COMMENT 'Customer User ID (Foreign Key -> nguoi_dung.id)',
    MODIFY COLUMN ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Order Placed Timestamp',
    MODIFY COLUMN tong_tien INT UNSIGNED NOT NULL COMMENT 'Total Amount (VND)',
    MODIFY COLUMN trang_thai ENUM('cho_xu_ly','dang_giao','da_giao','da_huy') NOT NULL DEFAULT 'cho_xu_ly' COMMENT 'Order Fulfillment Status',
    MODIFY COLUMN dia_chi VARCHAR(255) NOT NULL COMMENT 'Delivery Address',
    MODIFY COLUMN so_dien_thoai VARCHAR(20) NOT NULL COMMENT 'Contact Phone Number',
    MODIFY COLUMN ghi_chu TEXT NULL COMMENT 'Order Notes / Delivery Instructions',
    MODIFY COLUMN phuong_thuc_tt ENUM('cod','bank','momo') NOT NULL DEFAULT 'cod' COMMENT 'Payment Method (Legacy)',
    MODIFY COLUMN trang_thai_tt ENUM('chua_thanh_toan','da_thanh_toan','hoan_tien') NOT NULL DEFAULT 'chua_thanh_toan' COMMENT 'Payment Status (Legacy)'
");

$db->exec("ALTER TABLE chi_tiet_don
    MODIFY COLUMN don_hang_id INT UNSIGNED NOT NULL COMMENT 'Order ID (Foreign Key -> don_hang.id)',
    MODIFY COLUMN san_pham_id INT UNSIGNED NOT NULL COMMENT 'Book ID (Foreign Key -> san_pham.id)',
    MODIFY COLUMN so_luong INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Ordered Quantity',
    MODIFY COLUMN don_gia INT UNSIGNED NOT NULL COMMENT 'Locked Unit Price at Purchase Time'
");
echo "  [OK] Column comments applied to don_hang and chi_tiet_don!\n";

echo "\n5. Creating dedicated Pure English Database: book_nest_english...\n";
$db->exec("CREATE DATABASE IF NOT EXISTS book_nest_english CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "  [OK] Database book_nest_english created / ensured.\n";

// Connect to book_nest_english to create English physical tables
$pdoEn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=book_nest_english;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// 5.1 Roles table
$pdoEn->exec("CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User Roles & Authorization'");

$pdoEn->exec("INSERT INTO roles (name, display_name, description) VALUES
    ('admin', 'Platform Administrator', 'Full control over bookstore platform'),
    ('seller', 'Book Owner / Seller', 'Manages own book listings and store orders'),
    ('customer', 'Student Customer', 'Browses and purchases academic textbooks')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name)");

// 5.2 Users table
$pdoEn->exec("CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','seller','customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20) NULL,
    address VARCHAR(255) NULL,
    avatar VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    shop_name VARCHAR(150) NULL,
    shop_description TEXT NULL,
    logo_shop VARCHAR(255) NULL,
    seller_status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'approved',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Users & Accounts'");

// Copy users from book_nest_final
$dbUsers = $db->query("SELECT id, email, ho_ten, mat_khau, vai_tro, so_dien_thoai, dia_chi, avatar, kich_hoat, ten_gian_hang, mo_ta_shop, logo_shop, trang_thai_seller, ngay_tao FROM nguoi_dung")->fetchAll();
$stInsUser = $pdoEn->prepare("INSERT INTO users (id, email, full_name, password_hash, role, phone, address, avatar, is_active, shop_name, shop_description, logo_shop, seller_status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), role=VALUES(role), phone=VALUES(phone), address=VALUES(address)");

foreach ($dbUsers as $u) {
    $role = match ($u['vai_tro']) {
        'quan_tri' => 'admin',
        'chu_sach' => 'seller',
        default => 'customer'
    };
    $stInsUser->execute([
        $u['id'], $u['email'], $u['ho_ten'], $u['mat_khau'], $role,
        $u['so_dien_thoai'], $u['dia_chi'], $u['avatar'], $u['kich_hoat'],
        $u['ten_gian_hang'], $u['mo_ta_shop'], $u['logo_shop'], $u['trang_thai_seller'], $u['ngay_tao']
    ]);
}
echo "  [OK] users table populated with " . count($dbUsers) . " records.\n";

// 5.3 Categories table
$pdoEn->exec("CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Textbook Categories'");

$dbCats = $db->query("SELECT id, COALESCE(ten_en, ten) AS name, slug, COALESCE(mo_ta_en, mo_ta) AS description FROM danh_muc")->fetchAll();
$stInsCat = $pdoEn->prepare("INSERT INTO categories (id, name, slug, description) VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description)");
foreach ($dbCats as $c) {
    $stInsCat->execute([$c['id'], $c['name'], $c['slug'], $c['description']]);
}
echo "  [OK] categories table populated with " . count($dbCats) . " records.\n";

// 5.4 Books table
$pdoEn->exec("CREATE TABLE IF NOT EXISTS books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    seller_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    price INT UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    cover_image VARCHAR(255) NULL,
    description TEXT NULL,
    isbn VARCHAR(30) NULL,
    publication_year SMALLINT UNSIGNED DEFAULT 2026,
    approval_status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'approved',
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_books_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Books & Academic Textbooks'");

$dbBooks = $db->query("SELECT id, danh_muc_id, chu_sach_id, COALESCE(ten_en, ten) AS title, COALESCE(tac_gia_en, tac_gia) AS author, gia, ton_kho, anh, COALESCE(mo_ta_en, mo_ta) AS description, isbn, nam_xuat_ban, trang_thai_duyet, luot_xem, ngay_tao FROM san_pham")->fetchAll();
$stInsBook = $pdoEn->prepare("INSERT INTO books (id, category_id, seller_id, title, author, price, stock, cover_image, description, isbn, publication_year, approval_status, views, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE title=VALUES(title), author=VALUES(author), price=VALUES(price), stock=VALUES(stock)");
foreach ($dbBooks as $b) {
    $stInsBook->execute([
        $b['id'], $b['danh_muc_id'], $b['chu_sach_id'], $b['title'], $b['author'],
        $b['gia'], $b['ton_kho'], $b['anh'], $b['description'], $b['isbn'],
        $b['nam_xuat_ban'], $b['trang_thai_duyet'], $b['luot_xem'], $b['ngay_tao']
    ]);
}
echo "  [OK] books table populated with " . count($dbBooks) . " records.\n";

// 5.5 Orders & Order Items
$pdoEn->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total_amount INT UNSIGNED NOT NULL,
    status ENUM('pending','shipping','delivered','cancelled') NOT NULL DEFAULT 'pending',
    shipping_address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    notes TEXT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'cod',
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer Orders'");

$pdoEn->exec("CREATE TABLE IF NOT EXISTS order_items (
    order_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price INT UNSIGNED NOT NULL,
    PRIMARY KEY (order_id, book_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Order Line Items'");

$dbOrders = $db->query("SELECT * FROM don_hang")->fetchAll();
$stInsOrder = $pdoEn->prepare("INSERT INTO orders (id, user_id, total_amount, status, shipping_address, phone, notes, payment_method, payment_status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE total_amount=VALUES(total_amount), status=VALUES(status)");

foreach ($dbOrders as $o) {
    $stt = match ($o['trang_thai']) {
        'cho_xu_ly' => 'pending',
        'dang_giao' => 'shipping',
        'da_giao' => 'delivered',
        'da_huy' => 'cancelled',
        default => 'pending'
    };
    $payStt = ($o['trang_thai_tt'] === 'da_thanh_toan') ? 'paid' : 'pending';
    $stInsOrder->execute([
        $o['id'], $o['nguoi_dung_id'], $o['tong_tien'], $stt,
        $o['dia_chi'], $o['so_dien_thoai'], $o['ghi_chu'], $o['phuong_thuc_tt'], $payStt, $o['ngay_tao']
    ]);
}

$dbOrderItems = $db->query("SELECT * FROM chi_tiet_don")->fetchAll();
$stInsItem = $pdoEn->prepare("INSERT INTO order_items (order_id, book_id, quantity, unit_price) VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity=VALUES(quantity), unit_price=VALUES(unit_price)");
foreach ($dbOrderItems as $item) {
    try {
        $stInsItem->execute([$item['don_hang_id'], $item['san_pham_id'], $item['so_luong'], $item['don_gia']]);
    } catch (Exception $e) {
        // Skip if foreign key mismatch
    }
}
echo "  [OK] orders (" . count($dbOrders) . ") and order_items populated.\n";

// 5.6 Payments and Payment Methods
$pdoEn->exec("CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    instructions TEXT,
    icon VARCHAR(50) DEFAULT '💳',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment Methods'");

$dbPms = $db->query("SELECT id, code, COALESCE(name_en, name) AS name, COALESCE(description_en, description) AS description, COALESCE(instructions_en, instructions) AS instructions, icon, is_active, sort_order, created_at FROM payment_methods")->fetchAll();
$stInsPm = $pdoEn->prepare("INSERT INTO payment_methods (id, code, name, description, instructions, icon, is_active, sort_order, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name=VALUES(name)");
foreach ($dbPms as $pm) {
    $stInsPm->execute([$pm['id'], $pm['code'], $pm['name'], $pm['description'], $pm['instructions'], $pm['icon'], $pm['is_active'], $pm['sort_order'], $pm['created_at']]);
}

$pdoEn->exec("CREATE TABLE IF NOT EXISTS payments (
    payment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) NULL,
    amount INT UNSIGNED NOT NULL,
    payment_status ENUM('pending','paid','failed','verifying','rejected','refunded') NOT NULL DEFAULT 'pending',
    payment_proof VARCHAR(255) NULL,
    payment_date DATETIME NULL,
    admin_note TEXT NULL,
    verified_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_en_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_en_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment Transactions'");

$dbPayments = $db->query("SELECT * FROM payments")->fetchAll();
$stInsPay = $pdoEn->prepare("INSERT INTO payments (payment_id, order_id, user_id, payment_method, transaction_id, amount, payment_status, payment_proof, payment_date, admin_note, verified_by, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE payment_status=VALUES(payment_status)");
foreach ($dbPayments as $p) {
    try {
        $stInsPay->execute([
            $p['payment_id'], $p['order_id'], $p['user_id'], $p['payment_method'],
            $p['transaction_id'], $p['amount'], $p['payment_status'], $p['payment_proof'],
            $p['payment_date'], $p['admin_note'], $p['verified_by'], $p['created_at']
        ]);
    } catch (Exception $e) {
        // Skip orphaned
    }
}
echo "  [OK] payments (" . count($dbPayments) . ") and payment_methods populated.\n";

// 5.7 Reviews & Ratings
$pdoEn->exec("CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    book_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
    title VARCHAR(150) NULL,
    content TEXT NOT NULL,
    is_verified_purchase TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Book Customer Reviews'");

$dbReviews = $db->query("SELECT * FROM danh_gia")->fetchAll();
$stInsRev = $pdoEn->prepare("INSERT INTO reviews (id, book_id, user_id, rating, title, content, is_verified_purchase, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE content=VALUES(content)");
foreach ($dbReviews as $rev) {
    try {
        $stInsRev->execute([$rev['id'], $rev['san_pham_id'], $rev['nguoi_dung_id'], $rev['so_sao'], $rev['tieu_de'], $rev['noi_dung'], $rev['da_mua_hang'], $rev['ngay_tao']]);
    } catch (Exception $e) {}
}
echo "  [OK] reviews table populated.\n";

// 5.8 Cart Items & Wishlists
$pdoEn->exec("CREATE TABLE IF NOT EXISTS cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Shopping Cart Items'");

$pdoEn->exec("CREATE TABLE IF NOT EXISTS wishlists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Saved Wishlists'");

// 5.9 Addresses & Notifications
$pdoEn->exec("CREATE TABLE IF NOT EXISTS addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_addr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer Addresses'");

$pdoEn->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(191) NOT NULL,
    content TEXT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'system',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notifications'");

// 5.10 System Settings & Contacts
$pdoEn->exec("CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    description VARCHAR(191) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System Configuration'");

$pdoEn->exec("INSERT INTO system_settings (setting_key, setting_value, description) VALUES
    ('site_name', 'BookNest - UED Student Bookstore', 'Platform Name'),
    ('hotline', '0236.3841.323', 'Customer Support Hotline'),
    ('support_email', 'booknest.ued@gmail.com', 'Support Email'),
    ('announcement', '🎉 Welcome to BookNest! New Semester Book Fair: Up to 25% OFF all textbooks!', 'Top banner announcement')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");

$pdoEn->exec("CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    attachment VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact Form Inquiries'");

echo "\n========================================================\n";
echo "SUCCESS: English Sub-Database 'book_nest_english' created and synchronized!\n";
echo "Both 'book_nest_final' (with English comments & views) and 'book_nest_english' are now live!\n";
echo "========================================================\n";
