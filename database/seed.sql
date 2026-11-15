-- =============================================================================
-- HỌC PHẦN: THIẾT KẾ VÀ LẬP TRÌNH WEB (WEB DESIGN AND PROGRAMMING)
-- BÀI TẬP THỰC HÀNH NHÓM SỐ 6 - CHƯƠNG 6: PHP VÀ CƠ SỞ DỮ LIỆU MYSQL
-- ĐỒ ÁN: BOOKNEST - NHÀ SÁCH TRỰC TUYẾN SINH VIÊN UED
-- NHÓM THỰC HIỆN: NHÓM 12 (LỚP 24CNTT3)
-- TẬP TIN: database/seed.sql - DỮ LIỆU MẪU KIỂM THỬ HỆ THỐNG
-- =============================================================================

SET NAMES utf8mb4;
USE ltweb_nhom12;

-- 1. DỮ LIỆU DANH MỤC SÁCH
-- Danh mục 5 (ngoai-ngu-du-hoc) cố ý để trống không có sách để kiểm thử ca 2
INSERT INTO danh_muc (id, ten, slug, mo_ta) VALUES
  (1, 'Công nghệ thông tin', 'cong-nghe-thong-tin', 'Sách chuyên ngành CNTT, lập trình web, cấu trúc dữ liệu, AI và an toàn thông tin'),
  (2, 'Kinh tế & Khởi nghiệp', 'kinh-te-khoi-nghiep', 'Tài liệu kinh doanh, quản trị tài chính, khởi nghiệp tinh gọn và marketing số'),
  (3, 'Văn học & Nghệ thuật', 'van-hoc-nghe-thuat', 'Tiểu thuyết kinh điển, tác phẩm văn học Việt Nam và thế giới, nghệ thuật sáng tạo'),
  (4, 'Kỹ năng sống & Phát triển', 'ky-nang-song', 'Kỹ năng tư duy phản biện, giao tiếp, quản lý thời gian và phát triển bản thân'),
  (5, 'Ngoại ngữ & Du học', 'ngoai-ngu-du-hoc', 'Tài liệu luyện thi IELTS, TOEIC, tiếng Nhật, tiếng Trung (Hiện chưa có sách)');

-- 2. DỮ LIỆU TÀI KHOẢN NGƯỜI DÙNG
-- Tài khoản Admin: admin / admin123 (hoặc admin@ued.vn / admin123)
-- Tài khoản người dùng khác: matkhau123
INSERT INTO nguoi_dung (id, email, ho_ten, mat_khau, vai_tro) VALUES
  (1, 'admin@ued.vn', 'Quản trị viên Hệ thống', '$2y$12$EhvGWBjtejID4xV8jeIWO.PgcMjwYTR5D.M5Q3OM3uFttpMDUbObC', 'quan_tri'),
  (2, 'lan@ued.vn', 'Nguyễn Thị Lan', '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'khach_hang'),
  (3, 'an@ued.vn', 'Trần Văn An', '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'khach_hang'),
  (4, 'yoi@ued.vn', 'Xaiyasith Yoi', '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'quan_tri'),
  (5, 'hatsady@ued.vn', 'Phommaket Hatsady', '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'khach_hang'),
  (6, 'sauphasith@ued.vn', 'Vongsena Sauphasith', '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'khach_hang'),
  (7, 'sinh@ued.vn', 'Nguyễn Thị Sinh', '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'khach_hang'),
  (8, 'seller@ued.vn', 'Đặng Thùy Dương (Book Owner)', '$2y$12$qHb8qF.xgsQsDEN5nvxo6OsTDLDQmDQmTnjDdlNCp7MqAzd4fkWBG', 'chu_sach');

-- 3. DỮ LIỆU SẢN PHẨM SÁCH (33 CUỐN SÁCH - ĐẠT YÊU CẦU >= 30 CUỐN)
-- Có sách hết hàng (id=12 ton_kho=0)
-- Có sách chứa dấu nháy đơn (id=3: "Lập trình PHP & MySQL chuyên sâu của O'Reilly")
-- Có sách chứa dấu nháy đơn và từ khóa kiểm thử (id=31: "Quần jean Levi's 501 và Văn hóa Pop")
-- Có sách chứa "50%" (id=32: "Giảm giá 50% - Bí quyết Tự học Lập trình")
INSERT INTO san_pham (id, danh_muc_id, ten, gia, ton_kho, anh, mo_ta) VALUES
  (1, 1, 'Lập trình Web với PHP 8 và MySQL', 125000, 45, 'php-mysql.jpg', 'Giáo trình toàn diện về phát triển ứng dụng web động với PHP 8 và hệ quản trị CSDL MySQL.'),
  (2, 1, 'Cấu trúc dữ liệu và Giải thuật thực hành', 98000, 30, 'ctdl-gt.jpg', 'Phương pháp phân tích thuật toán, cấu trúc dữ liệu căn bản và nâng cao có ví dụ minh họa.'),
  (3, 1, 'Lập trình PHP & MySQL chuyên sâu của O''Reilly', 195000, 25, 'oreilly-php.jpg', 'Tác phẩm kinh điển hướng dẫn kiến trúc hướng đối tượng, PDO, giao dịch ACID và bảo mật OWASP.'),
  (4, 1, 'Nhập môn Trí tuệ nhân tạo và Học máy', 160000, 18, 'ai-ml.jpg', 'Tổng quan nền tảng lý thuyết và ứng dụng thực tế của AI hiện đại trong giải quyết bài toán lớn.'),
  (5, 1, 'Kiến trúc Vi dịch vụ (Microservices Architecture)', 210000, 15, 'microservices.jpg', 'Thiết kế hệ thống phân tán chịu tải cao, container hóa với Docker và Kubernetes.'),
  (6, 1, 'Bảo mật Ứng dụng Web và Phòng chống Tấn công', 145000, 22, 'web-security.jpg', 'Phòng vệ trước SQL Injection, XSS, CSRF và các lỗ hổng bảo mật phổ biến theo OWASP Top 10.'),
  (7, 1, 'Thiết kế Cơ sở dữ liệu Quan hệ chuẩn hóa', 88000, 40, 'database-design.jpg', 'Hướng dẫn từ sơ đồ ERD, chuẩn hóa 1NF - 3NF, BCNF đến tối ưu chỉ mục index và câu truy vấn.'),
  (8, 1, 'Lập trình Giao diện Hiện đại với JavaScript ES6+', 115000, 35, 'modern-js.jpg', 'Làm chủ JavaScript hiện đại, Fetch API, Promise, Async/Await và Single Page Applications.'),

  (9, 2, 'Khởi nghiệp Tinh gọn (The Lean Startup)', 110000, 28, 'lean-startup.jpg', 'Phương pháp xây dựng doanh nghiệp đột phá từ những thử nghiệm liên tục và phản hồi khách hàng.'),
  (10, 2, 'Từ Tốt đến Vĩ đại (Good to Great)', 135000, 20, 'good-to-great.jpg', 'Nghiên cứu sâu sắc về cách thức các công ty bình thường bứt phá trở thành vĩ đại bền vững.'),
  (11, 2, 'Kinh tế học Hài hước (Freakonomics)', 95000, 14, 'freakonomics.jpg', 'Góc nhìn bất ngờ và thú vị về các quy luật kinh tế tác động ngầm đến hành vi con người hàng ngày.'),
  (12, 2, 'Đắc Nhân Tâm trong Kinh doanh (Hết hàng)', 89000, 0, 'dac-nhan-tam.jpg', 'Nghệ thuật thấu hiểu đối tác, giao tiếp thuyết phục và xây dựng mạng lưới quan hệ kinh doanh.'),
  (13, 2, 'Tư duy Nhanh và Chậm (Thinking, Fast and Slow)', 175000, 19, 'fast-slow.jpg', 'Hai hệ thống điều khiển cách con người suy nghĩ và đưa ra các quyết định tài chính quan trọng.'),
  (14, 2, 'Quản trị Dự án Thực chiến theo Agile & Scrum', 130000, 26, 'agile-scrum.jpg', 'Phương pháp quản trị dự án phần mềm linh hoạt, gia tăng năng suất đội ngũ và tiến độ bàn giao.'),
  (15, 2, 'Tài chính Doanh nghiệp căn bản cho Sinh viên', 105000, 32, 'corporate-finance.jpg', 'Cẩm nang quản lý dòng tiền, đọc hiểu báo cáo tài chính và thẩm định hiệu quả dự án đầu tư.'),
  (16, 2, 'Chiến lược Đại dương Xanh (Blue Ocean Strategy)', 140000, 17, 'blue-ocean.jpg', 'Tạo lập khoảng trống thị trường không có cạnh tranh và biến đối thủ thành không quan trọng.'),

  (17, 3, 'Nhà giả kim (The Alchemist)', 79000, 50, 'nha-gia-kim.jpg', 'Cuộc phiêu lưu tìm kiếm kho báu và bài học sâu sắc về việc theo đuổi vận mệnh cuộc đời.'),
  (18, 3, 'Rừng Na Uy (Norwegian Wood)', 115000, 23, 'rung-na-uy.jpg', 'Tiểu thuyết sâu lắng của Haruki Murakami về tuổi trẻ, tình yêu, sự mất mát và hoài niệm.'),
  (19, 3, 'Chiếc thuyền ngoài xa và Những truyện ngắn chọn lọc', 65000, 40, 'chiec-thuyen.jpg', 'Tập truyện ngắn đặc sắc của nhà văn Nguyễn Minh Châu về chiều sâu đa diện của số phận con người.'),
  (20, 3, 'Số Đỏ (Vũ Trọng Phụng)', 72000, 38, 'so-do.jpg', 'Kiệt tác văn học hiện thực trào phúng phản ánh bức tranh xã hội thành thị Việt Nam thời kỳ 1930.'),
  (21, 3, 'Hoàng tử bé (Le Petit Prince)', 58000, 60, 'hoang-tu-be.jpg', 'Câu chuyện triết lý nhẹ nhàng nhưng lay động trái tim về tình bạn, tình yêu và trách nhiệm.'),
  (22, 3, 'Trăm năm cô đơn (One Hundred Years of Solitude)', 165000, 15, 'tram-nam-co-don.jpg', 'Kiệt tác chủ nghĩa hiện thực huyền ảo đoạt giải Nobel Văn học của Gabriel García Márquez.'),
  (23, 3, 'Bố Già (The Godfather)', 128000, 27, 'bo-gia.jpg', 'Tác phẩm đỉnh cao về gia tộc Mafia Corleone, quyền lực, danh dự và lòng trung thành gia đình.'),
  (24, 3, 'Tôi thấy hoa vàng trên cỏ xanh', 82000, 33, 'hoa-vang.jpg', 'Những trang văn trong trẻo tuổi thơ miền quê của nhà văn Nguyễn Nhật Ánh.'),

  (25, 4, '7 Thói quen của Bạn trẻ Thành đạt', 99000, 36, '7-thoi-quen.jpg', 'Bộ quy tắc nền tảng định hướng hành vi, giúp sinh viên làm chủ bản thân và xây dựng tương lai.'),
  (26, 4, 'Kỹ năng Thuyết trình và Nói trước công chúng', 85000, 25, 'thuyet-trinh.jpg', 'Vượt qua nỗi sợ sân khấu, làm chủ ngôn ngữ cơ thể và kỹ thuật soạn thảo slide trực quan.'),
  (27, 4, 'Quản lý Thời gian Thông minh để Thành công', 78000, 42, 'quan-ly-thoi-gian.jpg', 'Phương pháp sắp xếp công việc ưu tiên ma trận Eisenhower và kỹ thuật Pomodoro tăng tập trung.'),
  (28, 4, 'Tư duy Phản biện: Khám phá Chân lý qua Lý luận', 112000, 21, 'tu-duy-phan-bien.jpg', 'Rèn luyện khả năng phân tích logic, nhận diện ngụy biện và đưa ra kết luận khách quan.'),
  (29, 4, 'Bí quyết Học tập Hiệu quả bậc Đại học', 69000, 55, 'hoc-tap-dai-hoc.jpg', 'Phương pháp ghi chép Cornell, bản đồ tư duy Mindmap và ôn tập ngắt quãng Spaced Repetition.'),
  (30, 4, 'Sức mạnh của Thói quen (The Power of Habit)', 120000, 19, 'suc-manh-thoi-quen.jpg', 'Cơ chế hình thành vòng lặp thói quen trong não bộ và cách thức thay đổi thói quen xấu bền vững.'),

  -- Các mục bổ sung đặc thù phục vụ kiểm thử ca 1: Dấu nháy đơn và từ khóa 50%
  (31, 3, 'Quần jean Levi''s 501 và Văn hóa Pop', 135000, 12, 'levis-501.jpg', 'Lịch sử phát triển và biểu tượng văn hóa thanh niên gắn liền với dòng trang phục kinh điển.'),
  (32, 1, 'Giảm giá 50% - Bí quyết Tự học Lập trình', 85000, 10, 'sale-50.jpg', 'Bộ tài liệu khuyến mãi 50% hướng dẫn lộ trình tự học Fullstack Web từ con số không.'),
  (33, 4, 'Sống trọn vẹn từng khoảnh khắc tại UED', 92000, 24, 'song-tron-ven.jpg', 'Tác phẩm truyền cảm hứng sống tích cực, vượt qua áp lực học tập và thi cử của sinh viên Sư phạm.');

-- 4. DỮ LIỆU ĐƠN HÀNG VÀ CHI TIẾT ĐƠN HÀNG (Mỗi khách hàng thử nghiệm có ít nhất một đơn)
INSERT INTO don_hang (id, nguoi_dung_id, ngay_tao, tong_tien, trang_thai, dia_chi, so_dien_thoai, ghi_chu) VALUES
  (1, 2, '2026-10-15 14:30:00', 320000, 'da_giao', 'Ký túc xá Đại học Sư phạm, 459 Tôn Đức Thắng, Đà Nẵng', '0905123456', 'Giao vào giờ hành chính'),
  (2, 3, '2026-10-16 09:15:00', 195000, 'dang_giao', 'Phòng Trọ 12, Đường Ngô Thì Nhậm, Liên Chiểu, Đà Nẵng', '0914987654', 'Gọi trước khi giao hàng'),
  (3, 2, '2026-10-17 07:00:00', 235000, 'cho_xu_ly', 'Ký túc xá Đại học Sư phạm, 459 Tôn Đức Thắng, Đà Nẵng', '0905123456', 'Đơn hàng mới tạo');

-- Chi tiết đơn hàng: lưu đơn giá lúc đặt (chủ đích lưu lặp)
INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia) VALUES
  (1, 1, 1, 125000),
  (1, 3, 1, 195000),
  (2, 3, 1, 195000),
  (3, 9, 1, 110000),
  (3, 1, 1, 125000);

-- 5. DỮ LIỆU BẢNG LIÊN HỆ
INSERT INTO lien_he (id, ho_ten, email, chu_de, noi_dung, anh, ngay_gui) VALUES
  (1, 'Lê Hoàng Nam', 'nam.lh@gmail.com', 'Hỏi về sách giáo trình PHP 8', 'Xin hỏi nhà sách khi nào có thêm sách bài tập thực hành PHP 8 tái bản mới?', NULL, '2026-10-14 10:20:00'),
  (2, 'Trần Minh Quang', 'quang.tm@gmail.com', 'Góp ý giao diện giỏ hàng', 'Giao diện đặt sách BookNest rất trực quan và tốc độ tải rất nhanh. Cảm ơn nhóm!', NULL, '2026-10-15 16:45:00'),
  (3, 'Đặng Thu Hằng', 'hang.dt@ued.vn', 'Đề xuất bổ sung đầu sách IELTS', 'Em mong nhà sách bổ sung thêm tài liệu ngoại ngữ vào danh mục Ngoại ngữ & Du học ạ.', NULL, '2026-10-16 08:30:00');

-- 6. DỮ LIỆU BẢNG THÀNH VIÊN (PHẦN B)
INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) VALUES
  ('3120224189', 'Xaiyasith Yoi', 4),
  ('3120224181', 'Phommaket Hatsady', 5),
  ('3120224186', 'Vongsena Sauphasith', 6),
  ('3120223169', 'Nguyễn Thị Sinh', 7);

-- 7. DỮ LIỆU CÁC BẢNG RIÊNG (PHẦN B - tv_...)

-- 7.1. Sổ lưu bút của Xaiyasith Yoi (3120224189)
INSERT INTO tv_luu_but (id, mssv, ten_khach, loi_nhan, thoi_gian) VALUES
  (1, '3120224189', 'Nguyễn Thị Sinh', 'Chúc bạn Yoi hoàn thành xuất sắc đồ án môn Web nhé!', '2026-10-15 15:00:00'),
  (2, '3120224189', 'Phommaket Hatsady', 'Trang web BookNest nhóm mình xây dựng đẹp và mượt lắm.', '2026-10-15 17:30:00'),
  (3, '3120224189', 'Vongsena Sauphasith', 'Cùng cố gắng đạt điểm tối đa bài tập nhóm số 6 nhé nhóm trưởng!', '2026-10-16 08:00:00'),
  (4, '3120224189', 'Thầy Hướng Dẫn', 'Cấu trúc mã nguồn PDO và phân tách tầng dữ liệu tổ chức rất tốt.', '2026-10-16 14:20:00'),
  (5, '3120224189', 'Trần Văn An', 'Rất thích các đầu sách công nghệ thông tin trên BookNest.', '2026-10-16 19:40:00'),
  (6, '3120224189', 'Nguyễn Thị Lan', 'Giao diện mua sách trên di động hiển thị rất chuẩn.', '2026-10-17 07:10:00');

-- 7.2. Danh sách dự án của Phommaket Hatsady (3120224181)
INSERT INTO tv_du_an (id, mssv, ten_du_an, cong_nghe, mo_ta, link_github) VALUES
  (1, '3120224181', 'Hệ thống Quản lý Thư viện Sinh viên', 'PHP 8, MariaDB, Bootstrap 5', 'Ứng dụng quản lý mượn trả sách, tra cứu tài liệu học tập trực tuyến.', 'https://github.com/daimobile33076-eng/lib-manager'),
  (2, '3120224181', 'Cổng Đăng ký Học phần UED', 'JavaScript ES6, REST API, HTML5', 'Giao diện mô phỏng đăng ký tín chỉ thời gian thực chống nghẽn mạng.', 'https://github.com/daimobile33076-eng/course-reg'),
  (3, '3120224181', 'Ứng dụng Điểm danh Sinh viên bằng QR', 'Python, OpenCV, SQLite', 'Hệ thống nhận diện mã QR sinh viên tự động điểm danh vào phòng thi.', 'https://github.com/daimobile33076-eng/qr-attendance');

-- 7.3. Thống kê lượt xem của Vongsena Sauphasith (3120224186)
INSERT INTO tv_luot_xem (mssv, ngay, so_luot) VALUES
  ('3120224186', '2026-10-11', 42),
  ('3120224186', '2026-10-12', 58),
  ('3120224186', '2026-10-13', 65),
  ('3120224186', '2026-10-14', 89),
  ('3120224186', '2026-10-15', 104),
  ('3120224186', '2026-10-16', 135),
  ('3120224186', '2026-10-17', 76);

-- 7.4. Sách yêu thích của Nguyễn Thị Sinh (3120223169)
INSERT INTO tv_sach_yeu_thich (mssv, san_pham_id, danh_gia, cam_nhan) VALUES
  ('3120223169', 1, 5, 'Cuốn sách nền tảng tuyệt vời nhất để bắt đầu lập trình web PHP hiện đại.'),
  ('3120223169', 3, 5, 'Kiến trúc chuyên sâu của O''Reilly giúp mình hiểu rõ bản chất giao dịch ACID và PDO.'),
  ('3120223169', 17, 5, 'Nhà giả kim tiếp thêm nguồn cảm hứng theo đuổi đam mê công nghệ thông tin.'),
  ('3120223169', 25, 4, 'Sách kỹ năng thực tế giúp rèn luyện tư duy tổ chức công việc khoa học.');
