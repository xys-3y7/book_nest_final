# Đồ Án BookNest &ndash; Nhà Sách Trực Tuyến Sinh Viên UED

**Học phần:** Thiết kế và Lập trình Web (Web Design and Programming)  
**Nhóm thực hiện:** Nhóm 12 &ndash; Lớp học phần: 24CNTT3  
**Khoa:** Toán &ndash; Tin, Trường Đại học Sư phạm &ndash; Đại học Đà Nẵng  
**Bài tập thực hành:** Bài tập nhóm số 7 (Chương 7 &ndash; Bảo mật ứng dụng web)  
**Link kho GitHub:** [https://github.com/xys-3y7/ltweb-doan-nhom12](https://github.com/xys-3y7/ltweb-doan-nhom12)

---

## 1. Giới thiệu tổng quan
Dự án **BookNest** được gia cố bảo mật toàn diện theo chuẩn **OWASP Top 10:2025** và yêu cầu của Chương 7:
- **Chống tấn công CSRF:** Cơ chế sinh mã `csrfToken()` ngẫu nhiên an toàn mã hóa, tự động kiểm tra tập trung trong `inc/config.php` cho 100% biểu mẫu POST và lời gọi AJAX/fetch (header `X-CSRF-Token`). Thao tác đăng xuất chỉ nhận POST, GET trả 405 Method Not Allowed.
- **Bảo vệ Cookie và Phiên làm việc:** Bật `session.use_strict_mode = 1`, cookie phiên có cờ `HttpOnly`, `SameSite=Lax`, `Secure` (khi chạy HTTPS). Phiên tự động hết hạn sau 30 phút không hoạt động. Tái tạo mã định danh phiên (`session_regenerate_id(true)`) ngay khi đăng nhập thành công chống cố định phiên.
- **Header bảo mật và Content Security Policy (CSP):** Thiết lập `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, gỡ bỏ `X-Powered-By`. Toàn bộ mã script và style nội tuyến được chuẩn hóa chuyển sang tệp `.js` và `.css`.
- **Cấu hình an toàn với tệp .env:** Sử dụng thư viện `vlucas/phpdotenv`. Tệp `.env` được bảo vệ tuyệt đối trong `.gitignore`, không bao giờ được đưa lên kho Git. Kho chỉ lưu tệp mẫu `.env.example`.
- **Phân quyền truy cập máy chủ Apache (.htaccess):** Cấm duyệt thư mục (`Options -Indexes`), chặn truy cập trực tiếp các thư mục nội bộ (`inc/`, `src/`, `vendor/`, `database/`, `logs/`, `storage/`, `kiemtra/`) và tệp nhạy cảm (`.env`, `.git/`, `.sql`, `.log`, `composer.json`). Thư mục `uploads/.htaccess` cấm chạy mọi phần mở rộng mã thực thi.
- **Tài khoản CSDL quyền tối thiểu:** Cung cấp tệp `database/tai-khoan.sql` tạo người dùng `nhom12_app` chỉ có 4 quyền DML (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) trên CSDL `ltweb_nhom12`, loại bỏ hoàn toàn các quyền nguy hiểm `DROP`, `CREATE`, `ALTER`.
- **Chống dò quét vét cạn mật khẩu và Ghi nhật ký bảo mật:** Tạo bảng `dang_nhap_sai`, giới hạn tối đa 5 lần đăng nhập sai trong vòng 15 phút (vượt quá trả về HTTP 429 Too Many Requests). Hàm `ghiLogBaoMat()` ghi nhận chi tiết các sự kiện `csrf_sai`, `cam_truy_cap`, `dang_nhap_sai` vào `logs/bao-mat.log` mà không lưu dữ liệu nhạy cảm.

---

## 2. Thành viên nhóm & Bảng phân công nhiệm vụ (Bảng 1)
| STT | Họ và tên sinh viên | Mã sinh viên | Tài khoản GitHub | Vai trò & Biện pháp bảo mật phụ trách |
|:---:|:---|:---:|:---|:---|
| 1 | **Xaiyasith Yoi** *(Nhóm trưởng)* | 3120224189 | [@xys-3y7](https://github.com/xys-3y7) | Biện pháp 1 (Mã CSRF 19 form), Biện pháp 7 (Rate limit 429 & Nhật ký bảo mật), Bộ kiểm thử 14 ca tự động, chủ trì báo cáo. |
| 2 | **Phommaket Hatsady** | 3120224181 | [@daimobile33076-eng](https://github.com/daimobile33076-eng) | Biện pháp 5 (Cấu hình Apache .htaccess & uploads/.htaccess), Trang cá nhân danh mục dự án, Thảo luận C.3. |
| 3 | **Vongsena Sauphasith** | 3120224186 | [@happy2072005-prog](https://github.com/happy2072005-prog) | Biện pháp 4 (Bí mật .env với phpdotenv), Biện pháp 6 (Tài khoản CSDL quyền tối thiểu), Thảo luận C.4. |
| 4 | **Nguyễn Thị Sinh** | 3120223169 | [@chingg2005](https://github.com/chingg2005) | Biện pháp 2 (Bảo vệ Session HttpOnly/SameSite), Biện pháp 3 (Security Headers & CSP), Thảo luận C.2. |

### Các nút tương tác sản phẩm chính trên website:
1. ⚡ **Nút Đặt mua ngay (ปุ่มกดสั่งชื้อ):** Thêm sách vào đơn và chuyển hướng tức thời sang trang thanh toán/xác nhận đặt hàng (`dat-hang.php`).
2. 🛒 **Nút Thêm vào giỏ hàng (ปุ่มตะกร้า):** Thêm sách vào giỏ hàng PHP Session qua Fetch API không tải lại trang hoặc form POST truyền thống (`gio-hang.php`).
3. ❤️ **Nút Danh sách yêu thích (ปุ่มรายการโปรด):** Lưu trữ và quản lý các đầu sách sinh viên quan tâm vào danh mục yêu thích (`yeu-thich.php`).
4. 🔍 **Nút Xem chi tiết (ปุ่มดูรายละเอียด):** Truy cập trang thông tin chi tiết đầy đủ của cuốn sách (`chi-tiet.php?id=...`).

---

## 3. Hướng dẫn cài đặt và chạy ứng dụng

### Yêu cầu môi trường:
- **PHP:** Phiên bản ≥ 8.1 (bật extension `pdo_mysql`, `mbstring`, `fileinfo`).
- **MySQL / MariaDB:** Khởi động dịch vụ trong XAMPP hoặc Laragon.
- **Composer:** Để cài đặt các gói phụ thuộc (`vlucas/phpdotenv`).

### Các bước cài đặt:
1. **Cài đặt thư viện phụ thuộc bằng Composer:**
   ```bash
   composer install
   ```
2. **Khởi tạo cơ sở dữ liệu `ltweb_nhom12`:**
   - Sử dụng cửa sổ dòng lệnh (Terminal / Command Prompt / Git Bash):
     ```bash
     mysql -u root < database/schema.sql
     mysql -u root ltweb_nhom12 < database/seed.sql
     ```
   - Hoặc import lần lượt 2 tệp trên qua giao diện **phpMyAdmin**.
3. **Cấu hình tệp môi trường `.env`:**
   - Sao chép tệp mẫu cấu hình:
     ```bash
     # Trên Windows:
     copy .env.example .env

     # Trên Linux / macOS / Git Bash:
     cp .env.example .env
     ```
   - Kiểm tra các biến trong tệp `.env`:
     ```env
     APP_DEBUG=true
     DB_HOST=127.0.0.1
     DB_DATABASE=ltweb_nhom12
     DB_USERNAME=root
     DB_PASSWORD=
     ```
4. **(Tùy chọn) Kích hoạt tài khoản CSDL quyền tối thiểu:**
   - Chạy tệp SQL khởi tạo tài khoản bằng quyền `root`:
     ```bash
     mysql -u root < database/tai-khoan.sql
     ```
   - Chỉnh sửa thông tin kết nối trong tệp `.env`:
     ```env
     DB_USERNAME=nhom12_app
     DB_PASSWORD=MatKhau_Nhom12_BaoMat2026!
     ```
5. **Khởi động website:**
   - **Cách 1 (Máy chủ tích hợp PHP CLI):**
     ```bash
     php -S localhost:8000
     ```
     Truy cập tại: [http://localhost:8000](http://localhost:8000)
   - **Cách 2 (Máy chủ web Apache của XAMPP để kiểm tra .htaccess):**
     Đặt thư mục dự án vào `C:\xampp\htdocs\ltweb-doan-nhom12\` và truy cập qua đường dẫn: [http://localhost/ltweb-doan-nhom12/](http://localhost/ltweb-doan-nhom12/).

---

## 4. Tài khoản thử nghiệm (Mật khẩu chung: `matkhau123`)
| Email | Họ và tên | Vai trò | Quyền hạn & Chức năng |
|:---|:---|:---:|:---|
| `admin@ued.vn` | Quản trị viên Hệ thống | `quan_tri` | Truy cập `quan-tri.php`, CRUD sản phẩm, cập nhật đơn hàng |
| `lan@ued.vn` | Nguyễn Thị Lan | `khach_hang` | Mua sách, thanh toán, xem lịch sử đơn hàng tại `don-hang.php` |
| `an@ued.vn` | Trần Văn An | `khach_hang` | Đặt mua giáo trình, quản lý giỏ hàng |
| `yoi@ued.vn` | Xaiyasith Yoi | `quan_tri` | Chủ trang cá nhân Yoi, có quyền xóa lưu bút |
| `hatsady@ued.vn` | Phommaket Hatsady | `khach_hang` | Chủ trang cá nhân Hatsady, quyền thêm/xóa dự án |
| `sauphasith@ued.vn`| Vongsena Sauphasith | `khach_hang`| Chủ trang cá nhân Sauphasith, quản lý lượt xem |
| `sinh@ued.vn` | Nguyễn Thị Sinh | `khach_hang` | Chủ trang cá nhân Sinh, đánh giá sách yêu thích |

---

## 5. Kiểm thử bảo mật (Negative Testing)
Hệ thống cung cấp sẵn 2 tệp kịch bản kiểm thử:
- `tests/bao-mat.http`: Sử dụng extension **REST Client** trên Visual Studio Code để thực hiện 14 ca kiểm thử phủ định.
- `tests/bao-mat.sh`: Kịch bản lệnh **cURL** tự động chạy kiểm thử trên terminal Git Bash.
- Kiểm tra mã nguồn bằng công cụ:
  ```bash
  composer audit
  git log --all --oneline -- .env
  git ls-files .env
  ```
