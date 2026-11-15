<?php
require_once __DIR__ . '/../inc/config.php';
$db = db();

echo "Aligning categories and English metadata...\n";

// 1. Insert categories 1, 2, 3
$db->exec("INSERT INTO danh_muc (id, ten, ten_en, slug, mo_ta, mo_ta_en) VALUES
  (1, 'Công nghệ thông tin', 'Computer Science & Software Engineering', 'cong-nghe-thong-tin', 'Sách chuyên ngành CNTT, lập trình web, cấu trúc dữ liệu, AI và an toàn thông tin', 'University textbooks for Computer Science, Software Engineering, Algorithms, and System Architecture.'),
  (2, 'Kinh tế & Khởi nghiệp', 'Economics & Entrepreneurship', 'kinh-te-khoi-nghiep', 'Tài liệu kinh doanh, quản trị tài chính, khởi nghiệp tinh gọn và marketing số', 'Business administration, corporate finance, lean startup, and personal wealth.'),
  (3, 'Văn học & Nghệ thuật', 'Literature & Arts', 'van-hoc-nghe-thuat', 'Tiểu thuyết kinh điển, tác phẩm văn học Việt Nam và thế giới, nghệ thuật sáng tạo', 'Classic world literature, illustrated anthologies, and creative writing.')
ON DUPLICATE KEY UPDATE ten=VALUES(ten), ten_en=VALUES(ten_en), mo_ta=VALUES(mo_ta), mo_ta_en=VALUES(mo_ta_en)");

// 2. Add an Algorithms book or update Book 2
$db->exec("UPDATE san_pham 
           SET ten_en = 'Algorithms & Clean Architecture Systems', 
               mo_ta_en = 'Practical algorithms, data structures, and clean scalable system architecture.' 
           WHERE id = 2");

// 3. Re-create views so they reflect updated data
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

echo "Category alignment and views refreshed successfully!\n";
