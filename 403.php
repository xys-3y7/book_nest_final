<?php
/**
 * 403.php - Trang thông báo lỗi 403 Quyền truy cập bị từ chối (Forbidden)
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

require_once __DIR__ . '/inc/config.php';

http_response_code(403);
$tieuDe = '403 - Quyền truy cập bị từ chối';
$trang = '403';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <div style="text-align: center; padding: 60px 20px;">
    <h1 style="font-size: 4rem; color: #dc2626; margin-bottom: 10px;">403</h1>
    <h2>Quyền truy cập bị từ chối (Forbidden)</h2>
    <p style="max-width: 600px; margin: 15px auto; color: #64748b; font-size: 1.1rem;">
      Bạn không có quyền hạn cần thiết để truy cập khu vực này. Trang quản trị và các tác vụ chỉnh sửa chỉ dành riêng cho tài khoản có vai trò Quản trị viên (quan_tri).
    </p>
    <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
      <a href="index.php" class="nut nut--chinh">&larr; Quay lại Trang chủ</a>
      <a href="dang-nhap.php" class="nut nut--vien">Đăng nhập tài khoản khác</a>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
