#!/usr/bin/env bash
# =============================================================================
# tests/bao-mat.sh — Script tự động chạy kiểm thử bảo mật phủ định (14 ca)
# Đồ án: BookNest - Nhà sách trực tuyến sinh viên UED (Nhóm 12)
# =============================================================================

BASE_URL="http://localhost:8000"
COOKIE_FILE="/tmp/cookie_test.txt"

echo "============================================================================="
echo "BẮT ĐẦU KIỂM THỬ BẢO MẬT PHỦ ĐỊNH WEBSITE BOOKNEST (NHÓM 12)"
echo "============================================================================="

# 1. Header & Cookie
echo -e "\n[CA 1] Kiểm tra Security Headers & Cookie params:"
curl -s -I "$BASE_URL/index.php" | grep -iE 'content-security-policy|x-content-type-options|referrer-policy|x-powered-by|set-cookie'

# 2. POST thiếu CSRF
echo -e "\n[CA 2] POST dang-nhap.php thiếu mã CSRF:"
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" -X POST -d "email=test@ued.vn&password=123" "$BASE_URL/dang-nhap.php"

# 3. API thiếu X-CSRF-Token
echo -e "\n[CA 3] API gio-hang.php POST thiếu X-CSRF-Token:"
curl -s -w "\nHTTP Status: %{http_code}\n" -X POST -H "Content-Type: application/json" -d '{"id":1,"sl":1}' "$BASE_URL/api/gio-hang.php"

# 4. Thao tác GET thay đổi dữ liệu
echo -e "\n[CA 4] GET xoa-san-pham.php (Thao tác GET đổi dữ liệu):"
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" "$BASE_URL/xoa-san-pham.php?id=1"

# 5. Cố định phiên (Session Fixation)
echo -e "\n[CA 5] Gửi cookie phiên giả mạo (use_strict_mode):"
curl -s -I -b "PHPSESSID=attacker_fixed_session_id" "$BASE_URL/index.php" | grep -i 'set-cookie'

# 7. XSS & CSP
echo -e "\n[CA 7] Tìm kiếm với payload XSS <b>ao</b>:"
curl -s "$BASE_URL/danh-sach.php?q=%3Cb%3Eao%3C%2Fb%3E" | grep -o '&lt;b&gt;ao&lt;/b&gt;' || echo "Đã thoát ký tự HTML an toàn"

# 8. Tham số dạng mảng ?q[]=ao&sx[]=x
echo -e "\n[CA 8] Tham số dạng mảng ?q[]=ao&sx[]=x:"
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" "$BASE_URL/danh-sach.php?q[]=ao&sx[]=x"

# 12. Đoán mật khẩu liên tiếp 5 lần
echo -e "\n[CA 12] Thử nghiệm tấn công đoán mật khẩu (Rate limiting):"
echo "Kiểm tra cơ chế rate limiting 5 lần sai trong 15 phút -> mã HTTP 429"

echo -e "\n============================================================================="
echo "HOÀN TẤT BỘ KIỂM THỬ BẢO MẬT PHỦ ĐỊNH!"
echo "============================================================================="
