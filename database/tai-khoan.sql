-- =============================================================================
-- database/tai-khoan.sql — Chạy MỘT lần bằng tài khoản root (phpMyAdmin -> SQL)
-- Thiết lập tài khoản MySQL quyền tối thiểu cho ứng dụng BookNest (Nhóm 12)
-- =============================================================================

CREATE USER IF NOT EXISTS 'nhom12_app'@'localhost' IDENTIFIED BY 'MatKhau_Nhom12_BaoMat2026!';

-- Chỉ cấp 4 quyền DML thao tác trên dữ liệu: SELECT, INSERT, UPDATE, DELETE
-- Tuyệt đối không cấp DROP, CREATE, ALTER, GRANT OPTION nhằm giảm thiểu rủi ro khi bị tấn công
GRANT SELECT, INSERT, UPDATE, DELETE
  ON ltweb_nhom12.* TO 'nhom12_app'@'localhost';

FLUSH PRIVILEGES;
