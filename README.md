# Đồ Án BookNest &ndash; Nhà Sách Trực Tuyến Sinh Viên UED

**Học phần:** Thiết kế và Lập trình Web (Web Design and Programming)  
**Nhóm thực hiện:** Nhóm 12 &ndash; Lớp học phần: 24CNTT3  
**Khoa:** Toán &ndash; Tin, Trường Đại học Sư phạm &ndash; Đại học Đà Nẵng  
**Bài tập thực hành:** Bài tập nhóm số 6 (Chương 6 &ndash; PHP và cơ sở dữ liệu MySQL)

---

## 1. Giới thiệu tổng quan
Dự án **BookNest** được nâng cấp toàn diện từ nền tảng tệp JSON sang hệ quản trị CSDL quan hệ **MySQL / MariaDB** thông qua tầng truy xuất dữ liệu PDO an toàn. Ứng dụng hỗ trợ đầy đủ các tính năng web động:
- Thiết kế CSDL chuẩn hóa 3NF, ràng buộc toàn vẹn khóa ngoại, CHECK giá sách, chỉ mục tối ưu truy vấn.
- Toàn bộ câu truy vấn chứa dữ liệu người dùng sử dụng Prepared Statements, loại bỏ 100% nguy cơ SQL Injection.
- Tìm kiếm tức thời (Debounce + Fetch API), lọc nhiều tiêu chí, sắp xếp theo whitelist và phân trang bảo toàn tham số lọc qua `http_build_query`.
- Đặt hàng an toàn trong giao dịch ACID (`beginTransaction`, `commit`, `rollBack`), kiểm tra tồn kho nguyên tử (`UPDATE ... WHERE ton_kho >= ?`) chống bán vượt tồn kho.
- Xác thực và phân quyền máy chủ: mật khẩu băm BCRYPT qua `password_hash`, Session bảo mật chỉ lưu `uid`, phân quyền quản trị và phân quyền chủ sở hữu dữ liệu.
- Bộ REST API trả JSON chuẩn HTTP Status (200, 400, 404, 405, 422, 500) và tệp kiểm thử `api.http`.

---

## 2. Thành viên nhóm và phân công nhiệm vụ
| TT | Họ và tên | Mã sinh viên | Tài khoản GitHub | Nhiệm vụ chính (Chương 6) |
|:--:|:---|:---:|:---:|:---|
| 1 | **Xaiyasith Yoi** *(Nhóm trưởng)* | 3120224189 | `xys-3y7` | Kiến trúc CSDL, inc/db.php, giao dịch đặt hàng, Bảng tv_luu_but |
| 2 | **Phommaket Hatsady** | 3120224181 | `hatsady-pk` | Trang danh mục sách, bộ lọc GET, phân trang, Bảng tv_du_an |
| 3 | **Vongsena Sauphasith** | 3120224186 | `sauphasith-vs` | Biểu mẫu liên hệ, upload ảnh, xử lý lỗi 403/404, Bảng tv_luot_xem |
| 4 | **Nguyễn Thị Sinh** | 3120223169 | `sinh-nguyen` | Chi tiết sách JOIN danh mục, CRUD quản trị, Bảng tv_sach_yeu_thich |

---

## 3. Yêu cầu môi trường & Cài đặt

### Yêu cầu hệ thống:
- **PHP:** Phiên bản ≥ 8.1 (bật extension `pdo_mysql`, `mbstring`, `fileinfo`).
- **CSDL:** MySQL ≥ 8.0 hoặc MariaDB ≥ 10.4 (có sẵn trong XAMPP hoặc Laragon).
- **Composer:** Để nạp tự động PSR-4 (`App\\`: `src/`).

### Các bước cài đặt và chạy:
1. **Cài đặt thư viện:**
   ```bash
   composer install
   ```
2. **Khởi tạo CSDL `ltweb_nhom12` (Xóa và tạo lại từ đầu):**
   - Mở `phpMyAdmin` &rarr; Import `database/schema.sql`, sau đó Import `database/seed.sql`.
   - Hoặc gõ lệnh trong terminal (mysql XAMPP ở `C:\xampp\mysql\bin`):
     ```bash
     mysql -u root < database/schema.sql
     mysql -u root ltweb_nhom12 < database/seed.sql
     ```
3. **Khởi động máy chủ phát triển:**
   ```bash
   php -S localhost:8000
   ```
   Sau đó mở trình duyệt truy cập: [http://localhost:8000](http://localhost:8000).

---

## 4. Tài khoản thử nghiệm (Mật khẩu chung: `matkhau123`)
| Email | Họ và tên | Vai trò | Quyền hạn & Chức năng |
|:---|:---|:---:|:---|
| `admin@ued.vn` | Quản trị viên Hệ thống | `quan_tri` | Toàn quyền vào `quan-tri.php`, thêm/sửa/xóa sách, đổi trạng thái đơn |
| `lan@ued.vn` | Nguyễn Thị Lan | `khach_hang` | Đặt mua sách, xem đơn hàng cá nhân trong `don-hang.php` |
| `an@ued.vn` | Trần Văn An | `khach_hang` | Đặt mua sách, xem đơn hàng cá nhân trong `don-hang.php` |
| `yoi@ued.vn` | Xaiyasith Yoi | `quan_tri` | Chủ trang cá nhân Yoi, quyền xóa lưu bút |
| `hatsady@ued.vn` | Phommaket Hatsady | `khach_hang` | Chủ trang cá nhân Hatsady, thêm dự án mới |
| `sinh@ued.vn` | Nguyễn Thị Sinh | `khach_hang` | Chủ trang cá nhân Sinh, thêm đánh giá sách |

---

## 5. Bảng chức năng & Đường dẫn URL hệ thống
| Chức năng | Đường dẫn URL | Tệp xử lý | Người phụ trách |
|:---|:---|:---|:---|
| Trang chủ & Đã xem gần đây | `/index.php` | `index.php` | Xaiyasith Yoi |
| Danh mục & Tìm kiếm phân trang | `/danh-sach.php` | `danh-sach.php` | Phommaket Hatsady |
| Chi tiết sách & Sách cùng loại | `/chi-tiet.php?id=1` | `chi-tiet.php` | Nguyễn Thị Sinh |
| Giỏ hàng mua sách | `/gio-hang.php` | `gio-hang.php` | Phommaket Hatsady |
| Đặt hàng trong giao dịch ACID | `/dat-hang.php` | `dat-hang.php` | Xaiyasith Yoi |
| Đơn hàng của tôi | `/don-hang.php` | `don-hang.php` | Xaiyasith Yoi |
| Quản trị sách & Đơn hàng | `/quan-tri.php` | `quan-tri.php` | Nguyễn Thị Sinh |
| Thêm sách mới | `/them-san-pham.php` | `them-san-pham.php` | Nguyễn Thị Sinh |
| Chỉnh sửa sách | `/sua-san-pham.php?id=1` | `sua-san-pham.php` | Nguyễn Thị Sinh |
| Xóa sách (Chỉ nhận POST) | `/xoa-san-pham.php` | `xoa-san-pham.php` | Nguyễn Thị Sinh |
| Đăng ký tài khoản | `/dang-ky.php` | `dang-ky.php` | Xaiyasith Yoi |
| Đăng nhập hệ thống | `/dang-nhap.php` | `dang-nhap.php` | Xaiyasith Yoi |
| Đăng xuất an toàn | `/dang-xuat.php` | `dang-xuat.php` | Xaiyasith Yoi |
| Liên hệ & Upload ảnh minh chứng | `/lien-he.php` | `lien-he.php` | Vongsena Sauphasith |

---

## 6. Danh mục REST API Endpoints
| Phương thức | Endpoint URL | Tham số | Mã HTTP | Mô tả phản hồi |
|:---:|:---|:---|:---:|:---|
| **GET** | `/api/san-pham.php` | `?q=tu_khoa` | 200 | Tìm kiếm nhanh sách theo từ khóa (JSON) |
| **GET** | `/api/san-pham.php` | `?id=1` | 200, 400, 404 | Chi tiết 1 cuốn sách (chỉ chứa trường công khai) |
| **GET** | `/api/gio-hang.php` | Không | 200 | Xem tổng quan số lượng và danh sách giỏ hàng |
| **POST** | `/api/gio-hang.php` | Thân JSON: `{"id": 1, "sl": 2}` | 200, 400, 404, 422 | Thêm sách vào giỏ không tải lại trang |
| Khác | `/api/...` | Phương thức không hỗ trợ | 405 | Header `Allow`, thông báo lỗi JSON |
