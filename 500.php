<?php
/**
 * 500.php - Trang thông báo lỗi máy chủ nội bộ thân thiện (Môi trường prod)
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * TUÂN THỦ NGHIÊM NGẶT: Không truy vấn CSDL, không nạp header có gọi nguoiDung()
 * เพื่อป้องกันข้อผิดพลาดซ้ำซ้อนเมื่อเซิร์ฟเวอร์ CSDL ไม่พร้อมทำงาน
 */

http_response_code(500);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>500 - Lỗi hệ thống máy chủ | BookNest UED</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: #f8fafc;
      color: #1e293b;
      margin: 0;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .error-box {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      max-width: 550px;
      padding: 40px;
      text-align: center;
      margin: 20px;
    }
    h1 {
      font-size: 3.5rem;
      color: #dc2626;
      margin: 0 0 10px 0;
    }
    h2 {
      font-size: 1.5rem;
      margin: 0 0 15px 0;
      color: #0f172a;
    }
    p {
      color: #64748b;
      line-height: 1.6;
      margin-bottom: 25px;
    }
    .btn {
      display: inline-block;
      background: #1e40af;
      color: #ffffff;
      padding: 10px 22px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
      transition: background 0.2s;
    }
    .btn:hover {
      background: #1d4ed8;
    }
  </style>
</head>
<body>
  <div class="error-box">
    <h1>500</h1>
    <h2>Lỗi máy chủ nội bộ (Internal Server Error)</h2>
    <p>
      Hệ thống BookNest đang gặp sự cố kỹ thuật tạm thời. Chi tiết lỗi đã được máy chủ ghi nhận an toàn vào nhật ký hệ thống. Vui lòng thử lại sau giây lát.
    </p>
    <a href="index.php" class="btn">&larr; Quay lại Trang chủ</a>
  </div>
</body>
</html>
