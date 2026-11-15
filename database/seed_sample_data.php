<?php
/**
 * database/seed_sample_data.php - Seed realistic demonstration data for BookNest
 * Includes 3 roles: Admin, Seller, Customer with books, orders, reviews, addresses
 */

require_once __DIR__ . '/../inc/config.php';

$db = db();
echo "Seeding BookNest realistic data...\n";

// 1. Ensure Password Hashes
$passAdmin = password_hash('admin123', PASSWORD_DEFAULT);
$passSeller = password_hash('seller123', PASSWORD_DEFAULT);
$passCustomer = password_hash('customer123', PASSWORD_DEFAULT);
$passMatkhau = password_hash('matkhau123', PASSWORD_DEFAULT);

// 2. Insert / Update Users
$users = [
    [
        'email' => 'admin@ued.vn',
        'ho_ten' => 'System Administrator',
        'mat_khau' => $passAdmin,
        'vai_tro' => 'quan_tri',
        'kich_hoat' => 1,
        'so_dien_thoai' => '0236.3841.323',
        'dia_chi' => 'Faculty of Math & IT, UED Campus, Da Nang',
        'ten_gian_hang' => null,
    ],
    [
        'email' => 'seller@ued.vn',
        'ho_ten' => 'Dang Thuy Duong',
        'mat_khau' => $passSeller,
        'vai_tro' => 'chu_sach',
        'kich_hoat' => 1,
        'so_dien_thoai' => '0905112233',
        'dia_chi' => 'Zone B, 459 Ton Duc Thang, Da Nang',
        'ten_gian_hang' => 'TechBooks & Campus Store',
        'mo_ta_shop' => 'Official student bookstore specializing in Computer Science, Software Engineering and Foreign Language textbooks.',
        'trang_thai_seller' => 'approved'
    ],
    [
        'email' => 'alpha.books@ued.vn',
        'ho_ten' => 'Marcus Sterling',
        'mat_khau' => $passSeller,
        'vai_tro' => 'chu_sach',
        'kich_hoat' => 1,
        'so_dien_thoai' => '0905445566',
        'dia_chi' => 'Building C Bookstore, UED Danang',
        'ten_gian_hang' => 'Alpha Academic Bookstore',
        'mo_ta_shop' => 'Curated collection of Business, Economics, Life Skills, and World Literature for university scholars.',
        'trang_thai_seller' => 'approved'
    ],
    [
        'email' => 'customer@ued.vn',
        'ho_ten' => 'Alex Johnson',
        'mat_khau' => $passCustomer,
        'vai_tro' => 'khach_hang',
        'kich_hoat' => 1,
        'so_dien_thoai' => '0912345678',
        'dia_chi' => 'Room 304, Building A3 Dormitory, UED Campus, Da Nang',
    ],
    [
        'email' => 'lan@ued.vn',
        'ho_ten' => 'Nguyen Thi Lan',
        'mat_khau' => $passMatkhau,
        'vai_tro' => 'khach_hang',
        'kich_hoat' => 1,
        'so_dien_thoai' => '0905987654',
        'dia_chi' => '128 Ton Duc Thang, Lien Chieu, Da Nang',
    ],
];

foreach ($users as $u) {
    $st = $db->prepare('SELECT id FROM nguoi_dung WHERE email = ?');
    $st->execute([$u['email']]);
    $existing = $st->fetchColumn();

    if ($existing) {
        $stUp = $db->prepare('UPDATE nguoi_dung SET 
            ho_ten = ?, mat_khau = ?, vai_tro = ?, kich_hoat = ?, 
            so_dien_thoai = ?, dia_chi = ?, ten_gian_hang = ?, 
            mo_ta_shop = ?, trang_thai_seller = ?
            WHERE id = ?');
        $stUp->execute([
            $u['ho_ten'],
            $u['mat_khau'],
            $u['vai_tro'],
            $u['kich_hoat'],
            $u['so_dien_thoai'] ?? null,
            $u['dia_chi'] ?? null,
            $u['ten_gian_hang'] ?? null,
            $u['mo_ta_shop'] ?? null,
            $u['trang_thai_seller'] ?? 'approved',
            $existing
        ]);
    } else {
        $stIn = $db->prepare('INSERT INTO nguoi_dung 
            (email, ho_ten, mat_khau, vai_tro, kich_hoat, so_dien_thoai, dia_chi, ten_gian_hang, mo_ta_shop, trang_thai_seller)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stIn->execute([
            $u['email'],
            $u['ho_ten'],
            $u['mat_khau'],
            $u['vai_tro'],
            $u['kich_hoat'],
            $u['so_dien_thoai'] ?? null,
            $u['dia_chi'] ?? null,
            $u['ten_gian_hang'] ?? null,
            $u['mo_ta_shop'] ?? null,
            $u['trang_thai_seller'] ?? 'approved'
        ]);
    }
}

// Fetch Seller IDs
$seller1Id = (int)$db->query("SELECT id FROM nguoi_dung WHERE email = 'seller@ued.vn'")->fetchColumn();
$seller2Id = (int)$db->query("SELECT id FROM nguoi_dung WHERE email = 'alpha.books@ued.vn'")->fetchColumn();
$customer1Id = (int)$db->query("SELECT id FROM nguoi_dung WHERE email = 'customer@ued.vn'")->fetchColumn();
$customer2Id = (int)$db->query("SELECT id FROM nguoi_dung WHERE email = 'lan@ued.vn'")->fetchColumn();

// 3. Clear existing books and insert fresh realistic books
$db->exec('SET FOREIGN_KEY_CHECKS = 0;');
$db->exec('TRUNCATE TABLE chi_tiet_don;');
$db->exec('TRUNCATE TABLE danh_gia;');
$db->exec('TRUNCATE TABLE thanh_toan;');
$db->exec('TRUNCATE TABLE don_hang;');
$db->exec('TRUNCATE TABLE san_pham;');
$db->exec('SET FOREIGN_KEY_CHECKS = 1;');

$sampleBooks = [
    // Information Technology (Cat 1)
    [
        'danh_muc_id' => 1,
        'chu_sach_id' => $seller1Id,
        'ten' => 'Python for Modern Software Engineering',
        'tac_gia' => 'Dr. Robert Vance',
        'isbn' => '978-0134685991',
        'nam_xuat_ban' => 2026,
        'gia' => 145000,
        'ton_kho' => 35,
        'anh' => 'book_53ddc2905e87fd3f.png',
        'mo_ta' => 'A comprehensive guide to object-oriented programming, modern design patterns, async IO, and clean code best practices in Python 3.12+.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 1240
    ],
    [
        'danh_muc_id' => 1,
        'chu_sach_id' => $seller1Id,
        'ten' => 'Clean Architecture & Scalable Systems',
        'tac_gia' => 'Martin C. Fowler',
        'isbn' => '978-0134494166',
        'nam_xuat_ban' => 2025,
        'gia' => 165000,
        'ton_kho' => 20,
        'anh' => 'book_a6eb1abe32481d65.png',
        'mo_ta' => 'Essential architectural rules and domain-driven design principles for building maintainable, testable, and robust enterprise applications.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 980
    ],
    [
        'danh_muc_id' => 1,
        'chu_sach_id' => $seller1Id,
        'ten' => 'Modern Web Development with PHP & MySQL',
        'tac_gia' => 'Prof. David Miller',
        'isbn' => '978-1491954621',
        'nam_xuat_ban' => 2026,
        'gia' => 120000,
        'ton_kho' => 45,
        'anh' => 'sach-laptrinh-web.png',
        'mo_ta' => 'Official course textbook covering PHP 8.3 OOP, PDO prepared statements, security against OWASP Top 10, REST API design, and modern front-end integration.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 2150
    ],
    [
        'danh_muc_id' => 1,
        'chu_sach_id' => $seller1Id,
        'ten' => 'Database Systems & SQL Optimization',
        'tac_gia' => 'Elena Rostova',
        'isbn' => '978-0133970777',
        'nam_xuat_ban' => 2025,
        'gia' => 135000,
        'ton_kho' => 25,
        'anh' => 'sach-csdl-sql.png',
        'mo_ta' => 'In-depth coverage of relational schema normalization, B-tree indexes, execution query planners, transactions with ACID guarantees, and database security.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 870
    ],

    // Economics & Entrepreneurship (Cat 2)
    [
        'danh_muc_id' => 2,
        'chu_sach_id' => $seller2Id,
        'ten' => 'Rich Dad Poor Dad: Personal Finance Secrets',
        'tac_gia' => 'Robert T. Kiyosaki',
        'isbn' => '978-1612680194',
        'nam_xuat_ban' => 2024,
        'gia' => 110000,
        'ton_kho' => 50,
        'anh' => 'book_e0a5fc3e123622fa.png',
        'mo_ta' => 'The #1 personal finance book of all time. Explodes the myth that you need to earn a high income to be rich and explains the difference between assets and liabilities.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 3400
    ],
    [
        'danh_muc_id' => 2,
        'chu_sach_id' => $seller2Id,
        'ten' => 'Fake: Fake Money, Fake Teachers, Fake Assets',
        'tac_gia' => 'Robert T. Kiyosaki',
        'isbn' => '978-1612680842',
        'nam_xuat_ban' => 2024,
        'gia' => 125000,
        'ton_kho' => 30,
        'anh' => 'book_c264f7e317d4a643.png',
        'mo_ta' => 'An eye-opening analysis of modern monetary policy, financial education, and strategies to build resilient tangible wealth in uncertain times.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 1650
    ],
    [
        'danh_muc_id' => 2,
        'chu_sach_id' => $seller2Id,
        'ten' => 'Zero to One: Notes on Startups & Future Tech',
        'tac_gia' => 'Peter Thiel',
        'isbn' => '978-0804139298',
        'nam_xuat_ban' => 2025,
        'gia' => 130000,
        'ton_kho' => 28,
        'anh' => 'book_a6eb1abe32481d65.png',
        'mo_ta' => 'Legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create new things and escape competition through innovation.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 1420
    ],

    // Literature & Arts (Cat 3)
    [
        'danh_muc_id' => 3,
        'chu_sach_id' => $seller2Id,
        'ten' => 'The Great Gatsby - Centennial Illustrated Edition',
        'tac_gia' => 'F. Scott Fitzgerald',
        'isbn' => '978-0743273565',
        'nam_xuat_ban' => 2025,
        'gia' => 95000,
        'ton_kho' => 40,
        'anh' => 'sach-mang-may-tinh.png',
        'mo_ta' => 'The exemplary novel of the Jazz Age, capturing the disillusionment and excess of post-war American society with breathtaking poetic prose.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 1100
    ],
    [
        'danh_muc_id' => 3,
        'chu_sach_id' => $seller2Id,
        'ten' => 'To Kill a Mockingbird - Classic Library',
        'tac_gia' => 'Harper Lee',
        'isbn' => '978-0061120084',
        'nam_xuat_ban' => 2024,
        'gia' => 105000,
        'ton_kho' => 32,
        'anh' => 'sach-ctdl-gt.png',
        'mo_ta' => 'A timeless masterpiece of American literature exploring moral courage, empathy, and integrity in the deep American South.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 890
    ],

    // Life Skills & Development (Cat 4)
    [
        'danh_muc_id' => 4,
        'chu_sach_id' => $seller2Id,
        'ten' => 'Atomic Habits: Tiny Changes, Remarkable Results',
        'tac_gia' => 'James Clear',
        'isbn' => '978-0735211292',
        'nam_xuat_ban' => 2025,
        'gia' => 140000,
        'ton_kho' => 60,
        'anh' => 'book_53ddc2905e87fd3f.png',
        'mo_ta' => 'A proven, scientifically-backed framework for improving every day. Learn practical strategies to form good habits, break bad ones, and master tiny behaviors that lead to remarkable results.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 4200
    ],
    [
        'danh_muc_id' => 4,
        'chu_sach_id' => $seller2Id,
        'ten' => 'Deep Work: Focused Success in a Distracted World',
        'tac_gia' => 'Cal Newport',
        'isbn' => '978-1455586691',
        'nam_xuat_ban' => 2025,
        'gia' => 125000,
        'ton_kho' => 22,
        'anh' => 'sach-csdl-sql.png',
        'mo_ta' => 'One of the most valuable skills in our economy is becoming increasingly rare. If you master this skill, you will achieve true mastery and high productivity.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 1560
    ],

    // Foreign Languages & Study Abroad (Cat 5)
    [
        'danh_muc_id' => 5,
        'chu_sach_id' => $seller1Id,
        'ten' => 'Cambridge IELTS 19 Academic Student Book with Answers',
        'tac_gia' => 'Cambridge Assessment English',
        'isbn' => '978-1009438018',
        'nam_xuat_ban' => 2026,
        'gia' => 175000,
        'ton_kho' => 45,
        'anh' => 'sach-laptrinh-web.png',
        'mo_ta' => 'Authentic examination papers from Cambridge provide perfect practice because they are EXACTLY like the real test. Contains four complete tests for Academic candidates.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 2890
    ],
    [
        'danh_muc_id' => 5,
        'chu_sach_id' => $seller1Id,
        'ten' => 'English Grammar in Use - Self-Study Reference',
        'tac_gia' => 'Raymond Murphy',
        'isbn' => '978-1108457651',
        'nam_xuat_ban' => 2025,
        'gia' => 155000,
        'ton_kho' => 38,
        'anh' => 'sach-mang-may-tinh.png',
        'mo_ta' => 'The worlds best-selling grammar book for intermediate learners of English. Clear explanations and innovative practice exercises trusted by millions.',
        'trang_thai_duyet' => 'approved',
        'luot_xem' => 2100
    ],
];

$stBook = $db->prepare('INSERT INTO san_pham 
    (danh_muc_id, chu_sach_id, ten, tac_gia, isbn, nam_xuat_ban, gia, ton_kho, anh, mo_ta, trang_thai_duyet, luot_xem)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

$insertedBookIds = [];
foreach ($sampleBooks as $b) {
    $stBook->execute([
        $b['danh_muc_id'],
        $b['chu_sach_id'],
        $b['ten'],
        $b['tac_gia'],
        $b['isbn'],
        $b['nam_xuat_ban'],
        $b['gia'],
        $b['ton_kho'],
        $b['anh'],
        $b['mo_ta'],
        $b['trang_thai_duyet'],
        $b['luot_xem']
    ]);
    $insertedBookIds[] = (int)$db->lastInsertId();
}

echo "Inserted " . count($insertedBookIds) . " sample books.\n";

// 4. Insert Realistic Reviews
$reviews = [
    [
        'san_pham_id' => $insertedBookIds[0], // Python
        'nguoi_dung_id' => $customer1Id,
        'so_sao' => 5,
        'tieu_de' => 'Exceptional guide for CS majors!',
        'noi_dung' => 'This textbook made async programming and OOP patterns crystal clear. Perfect companion for university lab projects!'
    ],
    [
        'san_pham_id' => $insertedBookIds[0],
        'nguoi_dung_id' => $customer2Id,
        'so_sao' => 5,
        'tieu_de' => 'Clear explanations and real examples',
        'noi_dung' => 'Fast delivery to the dormitory. High quality printing and the code examples work right out of the box.'
    ],
    [
        'san_pham_id' => $insertedBookIds[2], // Web Dev
        'nguoi_dung_id' => $customer1Id,
        'so_sao' => 5,
        'tieu_de' => 'Must-have for Web Programming course',
        'noi_dung' => 'Directly follows our syllabus at UED. The chapters on PDO security and defense-in-depth are top notch.'
    ],
    [
        'san_pham_id' => $insertedBookIds[4], // Rich Dad
        'nguoi_dung_id' => $customer2Id,
        'so_sao' => 5,
        'tieu_de' => 'Changed my mindset completely',
        'noi_dung' => 'A life-changing perspective on finances. Every student should read this before graduating.'
    ],
    [
        'san_pham_id' => $insertedBookIds[9], // Atomic Habits
        'nguoi_dung_id' => $customer1Id,
        'so_sao' => 5,
        'tieu_de' => 'Actionable and transformative',
        'noi_dung' => 'The 1% better every day philosophy helped me maintain focus during exam seasons. Highly recommended!'
    ],
    [
        'san_pham_id' => $insertedBookIds[11], // IELTS 19
        'nguoi_dung_id' => $customer2Id,
        'so_sao' => 5,
        'tieu_de' => 'Original test papers with clear audio links',
        'noi_dung' => 'Helped me achieve a 7.5 on my test practice. Delivered safely in 24 hours.'
    ]
];

$stRev = $db->prepare('INSERT INTO danh_gia 
    (san_pham_id, nguoi_dung_id, so_sao, tieu_de, noi_dung, ngay_tao)
    VALUES (?, ?, ?, ?, ?, NOW() - INTERVAL FLOOR(RAND() * 10) DAY)');

foreach ($reviews as $r) {
    $stRev->execute([
        $r['san_pham_id'],
        $r['nguoi_dung_id'],
        $r['so_sao'],
        $r['tieu_de'],
        $r['noi_dung']
    ]);
}
echo "Inserted " . count($reviews) . " customer reviews.\n";

// 5. Insert Sample Completed & Active Orders
$orders = [
    [
        'nguoi_dung_id' => $customer1Id,
        'tong_tien' => 265000,
        'trang_thai' => 'da_giao',
        'phuong_thuc_tt' => 'cod',
        'trang_thai_tt' => 'da_thanh_toan',
        'dia_chi' => 'Room 304, Building A3 Dormitory, UED Campus, Da Nang',
        'so_dien_thoai' => '0912345678',
        'ghi_chu' => 'Call before delivery in afternoon',
        'items' => [
            ['san_pham_id' => $insertedBookIds[0], 'so_luong' => 1, 'don_gia' => 145000],
            ['san_pham_id' => $insertedBookIds[2], 'so_luong' => 1, 'don_gia' => 120000]
        ]
    ],
    [
        'nguoi_dung_id' => $customer2Id,
        'tong_tien' => 250000,
        'trang_thai' => 'dang_giao',
        'phuong_thuc_tt' => 'bank',
        'trang_thai_tt' => 'da_thanh_toan',
        'dia_chi' => '128 Ton Duc Thang, Lien Chieu, Da Nang',
        'so_dien_thoai' => '0905987654',
        'ghi_chu' => 'Please leave with dorm security if not in room',
        'items' => [
            ['san_pham_id' => $insertedBookIds[4], 'so_luong' => 1, 'don_gia' => 110000],
            ['san_pham_id' => $insertedBookIds[9], 'so_luong' => 1, 'don_gia' => 140000]
        ]
    ],
    [
        'nguoi_dung_id' => $customer1Id,
        'tong_tien' => 175000,
        'trang_thai' => 'cho_xu_ly',
        'phuong_thuc_tt' => 'momo',
        'trang_thai_tt' => 'da_thanh_toan',
        'dia_chi' => 'Room 304, Building A3 Dormitory, UED Campus, Da Nang',
        'so_dien_thoai' => '0912345678',
        'ghi_chu' => 'Priority exam preparation material',
        'items' => [
            ['san_pham_id' => $insertedBookIds[11], 'so_luong' => 1, 'don_gia' => 175000]
        ]
    ]
];

$stOrder = $db->prepare('INSERT INTO don_hang 
    (nguoi_dung_id, tong_tien, trang_thai, phuong_thuc_tt, trang_thai_tt, dia_chi, so_dien_thoai, ghi_chu, ngay_tao)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW() - INTERVAL FLOOR(RAND() * 5) DAY)');

$stItem = $db->prepare('INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia) VALUES (?, ?, ?, ?)');
$stPay = $db->prepare('INSERT INTO thanh_toan (don_hang_id, phuong_thuc, so_tien, ma_giao_dich, trang_thai) VALUES (?, ?, ?, ?, ?)');

foreach ($orders as $ord) {
    $stOrder->execute([
        $ord['nguoi_dung_id'],
        $ord['tong_tien'],
        $ord['trang_thai'],
        $ord['phuong_thuc_tt'],
        $ord['trang_thai_tt'],
        $ord['dia_chi'],
        $ord['so_dien_thoai'],
        $ord['ghi_chu']
    ]);
    $orderId = (int)$db->lastInsertId();

    foreach ($ord['items'] as $item) {
        $stItem->execute([$orderId, $item['san_pham_id'], $item['so_luong'], $item['don_gia']]);
    }

    $stPay->execute([
        $orderId,
        $ord['phuong_thuc_tt'],
        $ord['tong_tien'],
        'PAY-' . strtoupper(bin2hex(random_bytes(4))),
        $ord['trang_thai_tt'] === 'da_thanh_toan' ? 'thanh_cong' : 'dang_cho'
    ]);
}
echo "Inserted " . count($orders) . " sample orders & payments.\n";

// 6. Insert Sample Addresses
$db->exec("INSERT INTO so_dia_chi (nguoi_dung_id, ho_ten, so_dien_thoai, dia_chi, mac_dinh) VALUES
    ($customer1Id, 'Alex Johnson', '0912345678', 'Room 304, Building A3 Dormitory, UED Campus, Da Nang', 1),
    ($customer1Id, 'Alex Johnson (Home)', '0912345678', '45 Nguyen Luong Bang, Lien Chieu, Da Nang', 0),
    ($customer2Id, 'Nguyen Thi Lan', '0905987654', '128 Ton Duc Thang, Lien Chieu, Da Nang', 1)
ON DUPLICATE KEY UPDATE dia_chi = VALUES(dia_chi);");

// 7. Insert Sample Notifications
$db->exec("INSERT INTO thong_bao (nguoi_dung_id, tieu_de, noi_dung, loai, da_doc) VALUES
    ($customer1Id, 'Welcome to BookNest!', 'Thank you for joining the UED online student bookstore. Enjoy exclusive semester discounts!', 'he_thong', 0),
    ($customer1Id, 'Order #1 Delivered', 'Your order containing Python for Modern Software Engineering has been delivered successfully.', 'don_hang', 1),
    ($seller1Id, 'Book Approved', 'Your textbook \"Modern Web Development with PHP & MySQL\" was reviewed and approved for sale.', 'sach', 0),
    ($seller2Id, 'New Order Received', 'Customer Alex Johnson has ordered 1 copy of \"Atomic Habits\". Please fulfill promptly.', 'don_hang', 0);");

// 8. Insert Sample Wishlist Items
$db->exec("INSERT IGNORE INTO yeu_thich (nguoi_dung_id, san_pham_id) VALUES
    ($customer1Id, {$insertedBookIds[4]}),
    ($customer1Id, {$insertedBookIds[9]}),
    ($customer2Id, {$insertedBookIds[0]}),
    ($customer2Id, {$insertedBookIds[11]});");

echo "Seeding completed successfully!\n";
