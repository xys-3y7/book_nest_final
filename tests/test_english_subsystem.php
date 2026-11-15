<?php
/**
 * tests/test_english_subsystem.php
 * Automated Test Suite for Database Sub English Subsystem & Bilingual Helpers
 * BookNest - UED Student Online Bookstore
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/ngon-ngu.php';

use App\Data\KhoSanPham;
use App\Models\SanPham;

$db = db();
$passed = 0;
$failed = 0;

function assertCondition(bool $cond, string $message): void {
    global $passed, $failed;
    if ($cond) {
        $passed++;
        echo "  [PASS] $message\n";
    } else {
        $failed++;
        echo "  [FAIL] $message\n";
    }
}

echo "========================================================\n";
echo "BookNest English Subsystem Automated Test Suite\n";
echo "Active Database: " . $db->query("SELECT DATABASE()")->fetchColumn() . "\n";
echo "========================================================\n\n";

// 1. Check Table Columns in san_pham
echo "1. Testing Schema Columns in san_pham...\n";
$colsSanPham = $db->query("SHOW COLUMNS FROM san_pham")->fetchAll(PDO::FETCH_COLUMN);
assertCondition(in_array('ten_en', $colsSanPham, true), "Column 'ten_en' exists in 'san_pham'");
assertCondition(in_array('tac_gia_en', $colsSanPham, true), "Column 'tac_gia_en' exists in 'san_pham'");
assertCondition(in_array('mo_ta_en', $colsSanPham, true), "Column 'mo_ta_en' exists in 'san_pham'");

// 2. Check Table Columns in danh_muc
echo "\n2. Testing Schema Columns in danh_muc...\n";
$colsDanhMuc = $db->query("SHOW COLUMNS FROM danh_muc")->fetchAll(PDO::FETCH_COLUMN);
assertCondition(in_array('ten_en', $colsDanhMuc, true), "Column 'ten_en' exists in 'danh_muc'");
assertCondition(in_array('mo_ta_en', $colsDanhMuc, true), "Column 'mo_ta_en' exists in 'danh_muc'");

// 3. Check Table Columns in payment_methods
echo "\n3. Testing Schema Columns in payment_methods...\n";
$colsPm = $db->query("SHOW COLUMNS FROM payment_methods")->fetchAll(PDO::FETCH_COLUMN);
assertCondition(in_array('name_en', $colsPm, true), "Column 'name_en' exists in 'payment_methods'");
assertCondition(in_array('description_en', $colsPm, true), "Column 'description_en' exists in 'payment_methods'");
assertCondition(in_array('instructions_en', $colsPm, true), "Column 'instructions_en' exists in 'payment_methods'");

// 4. Test English Compatibility Views
echo "\n4. Testing English Compatibility Views...\n";
$booksCount = $db->query("SELECT COUNT(*) FROM books")->fetchColumn();
assertCondition($booksCount > 0, "View 'books' is queryable and returned $booksCount rows");

$bookSample = $db->query("SELECT id, title, title_en, author, author_en, price, stock FROM books LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assertCondition(!empty($bookSample['title']), "View 'books' has column 'title': " . ($bookSample['title'] ?? ''));
assertCondition(array_key_exists('title_en', $bookSample), "View 'books' contains column 'title_en'");
assertCondition(array_key_exists('author_en', $bookSample), "View 'books' contains column 'author_en'");

$categoriesCount = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
assertCondition($categoriesCount >= 5, "View 'categories' is queryable and returned $categoriesCount rows");

$categorySample = $db->query("SELECT id, name, name_en, description FROM categories LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assertCondition(!empty($categorySample['name']), "View 'categories' has column 'name': " . ($categorySample['name'] ?? ''));
assertCondition(array_key_exists('name_en', $categorySample), "View 'categories' contains column 'name_en'");

$views = ['users', 'sellers', 'orders', 'order_items', 'shopping_carts', 'cart_items', 'wishlists', 'reviews', 'addresses', 'notifications'];
foreach ($views as $view) {
    $vCount = $db->query("SELECT COUNT(*) FROM $view")->fetchColumn();
    assertCondition($vCount !== false, "View '$view' exists and is readable (rows: $vCount)");
}

// 4.1 Test the 16 exact Sub English Views requested by user
echo "\n4.1 Testing the 16 exact Sub English Views...\n";
$exactViews = [
    'Books & Textbooks',
    'Textbook Categories',
    'Orders',
    'Order Items / Details',
    'Users & Accounts',
    'Payments & Transactions',
    'Payment Methods',
    'User Roles',
    'Shopping Cart',
    'Book Reviews & Ratings',
    'Shipping Addresses',
    'Notifications',
    'Contact Inquiries',
    'System Settings',
    'Failed Login Attempts',
    'Wishlist / Saved Books'
];

foreach ($exactViews as $ev) {
    $evCount = $db->query("SELECT COUNT(*) FROM `$ev`")->fetchColumn();
    assertCondition($evCount !== false, "Sub English View '`$ev`' exists and returns $evCount rows");
}

// 5. Test Model SanPham bilingual methods
echo "\n5. Testing Model SanPham Bilingual Getters...\n";
$mockData = [
    'id' => 999,
    'danh_muc_id' => 1,
    'ten' => 'Lập trình Web Cơ bản và Nâng cao',
    'ten_en' => 'Web Development with PHP & MySQL',
    'gia' => 125000,
    'ton_kho' => 20,
    'tac_gia' => 'Nguyễn Văn A',
    'tac_gia_en' => 'Dr. Alan Turing',
    'mo_ta' => 'Mô tả tiếng Việt cho giáo trình web',
    'mo_ta_en' => 'English comprehensive syllabus on fullstack web development',
    'ten_danh_muc' => 'Khoa học Máy tính',
    'ten_danh_muc_en' => 'Computer Science & Software'
];
$sp = SanPham::tuMang($mockData);

// Active language English
$_SESSION['booknest_lang'] = 'en';
assertCondition(laTiengAnh() === true, "laTiengAnh() returns true when active lang is 'en'");
assertCondition($sp->getTitle() === 'Web Development with PHP & MySQL', "sp->getTitle() returns English title in EN mode");
assertCondition($sp->getAuthor() === 'Dr. Alan Turing', "sp->getAuthor() returns English author in EN mode");
assertCondition($sp->getDescription() === 'English comprehensive syllabus on fullstack web development', "sp->getDescription() returns English description in EN mode");
assertCondition($sp->getCategoryName() === 'Computer Science & Software', "sp->getCategoryName() returns English category in EN mode");

// Active language Vietnamese
$_SESSION['booknest_lang'] = 'vi';
assertCondition(laTiengAnh() === false, "laTiengAnh() returns false when active lang is 'vi'");
assertCondition($sp->getTitle() === 'Lập trình Web Cơ bản và Nâng cao', "sp->getTitle() returns Vietnamese title in VI mode");
assertCondition($sp->getAuthor() === 'Nguyễn Văn A', "sp->getAuthor() returns Vietnamese author in VI mode");
assertCondition($sp->getDescription() === 'Mô tả tiếng Việt cho giáo trình web', "sp->getDescription() returns Vietnamese description in VI mode");
assertCondition($sp->getCategoryName() === 'Khoa học Máy tính', "sp->getCategoryName() returns Vietnamese category in VI mode");

// 6. Test Global Helper Functions (tieuDeSach, tacGiaSach, moTaSach, tenDanhMuc)
echo "\n6. Testing Global Helper Functions...\n";
$_SESSION['booknest_lang'] = 'en';
assertCondition(tieuDeSach($mockData) === 'Web Development with PHP & MySQL', "tieuDeSach() returns English title in EN mode");
assertCondition(tacGiaSach($mockData) === 'Dr. Alan Turing', "tacGiaSach() returns English author in EN mode");
assertCondition(moTaSach($mockData) === 'English comprehensive syllabus on fullstack web development', "moTaSach() returns English description in EN mode");
assertCondition(tenDanhMuc(['ten' => 'Toán Tin', 'ten_en' => 'Mathematics & Informatics']) === 'Mathematics & Informatics', "tenDanhMuc() returns English category name in EN mode");

$_SESSION['booknest_lang'] = 'vi';
assertCondition(tieuDeSach($mockData) === 'Lập trình Web Cơ bản và Nâng cao', "tieuDeSach() returns native title in VI mode");
assertCondition(tacGiaSach($mockData) === 'Nguyễn Văn A', "tacGiaSach() returns native author in VI mode");
assertCondition(moTaSach($mockData) === 'Mô tả tiếng Việt cho giáo trình web', "moTaSach() returns native description in VI mode");
assertCondition(tenDanhMuc(['ten' => 'Toán Tin', 'ten_en' => 'Mathematics & Informatics']) === 'Toán Tin', "tenDanhMuc() returns native category name in VI mode");

// Reset to English mode
$_SESSION['booknest_lang'] = 'en';

// 7. Test KhoSanPham Repository Keyword Search with English fields
echo "\n7. Testing KhoSanPham Repository English Keyword Search...\n";
$kho = new KhoSanPham($db);
$timPython = $kho->dem(['q' => 'Python']);
assertCondition($timPython > 0, "KhoSanPham found $timPython textbooks for keyword 'Python'");

$timAlgorithm = $kho->dem(['q' => 'Algorithm']);
assertCondition($timAlgorithm > 0, "KhoSanPham found $timAlgorithm textbooks for keyword 'Algorithm'");

$timDatabase = $kho->dem(['q' => 'Database']);
assertCondition($timDatabase > 0, "KhoSanPham found $timDatabase textbooks for keyword 'Database'");

$sachLayRa = $kho->trang(['q' => 'Python'], 'moi', 5, 0);
assertCondition(!empty($sachLayRa), "KhoSanPham->trang() returned matched books");
if (!empty($sachLayRa)) {
    $first = $sachLayRa[0];
    assertCondition(!empty($first->getTitle()), "Fetched book has title: " . $first->getTitle());
    assertCondition(!empty($first->getCategoryName()), "Fetched book has category name: " . $first->getCategoryName());
}

// 8. Test JSON Serialization
echo "\n8. Testing SanPham JSON Serialization...\n";
$jsonArr = $sp->jsonSerialize();
assertCondition($jsonArr['ten'] === 'Web Development with PHP & MySQL', "JSON 'ten' field is resolved to English");
assertCondition($jsonArr['title'] === 'Web Development with PHP & MySQL', "JSON 'title' field contains English title");
assertCondition($jsonArr['ten_vi'] === 'Lập trình Web Cơ bản và Nâng cao', "JSON 'ten_vi' contains native title");
assertCondition($jsonArr['tac_gia'] === 'Dr. Alan Turing', "JSON 'tac_gia' field is resolved to English");

echo "\n========================================================\n";
echo "SUMMARY: Passed: $passed | Failed: $failed\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}
