<?php
/**
 * thanhvien/3120224186_sauphasith/gioithieu.php - Trang cá nhân của Vongsena Sauphasith
 * Mã sinh viên: 3120224186 - Lớp: 24CNTT3
 * Chức năng Phần B:
 *   1. Bộ đếm lượt xem truy cập hàng ngày dùng INSERT ... ON DUPLICATE KEY UPDATE vào bảng tv_luot_xem.
 *   2. Bảng thống kê chi tiết lượt truy cập trong 7 ngày gần nhất từ CSDL MySQL.
 * Cách thử: Mỗi lần tải lại trang, số lượt xem hôm nay tự động tăng thêm 1 trong bảng tv_luot_xem.
 */

require_once __DIR__ . '/../../inc/config.php';

const MSSV = '3120224186';
$goc = '../../';
$db = db();

// Chức năng 1: Ghi nhận hoặc tăng số lượt xem trong ngày hôm nay bằng INSERT ... ON DUPLICATE KEY UPDATE
$stDem = $db->prepare(
    'INSERT INTO tv_luot_xem (mssv, ngay, so_luot) 
     VALUES (?, CURDATE(), 1) 
     ON DUPLICATE KEY UPDATE so_luot = so_luot + 1'
);
$stDem->execute([MSSV]);

// Lấy số lượt xem hôm nay
$stHomNay = $db->prepare('SELECT so_luot FROM tv_luot_xem WHERE mssv = ? AND ngay = CURDATE()');
$stHomNay->execute([MSSV]);
$luotHomNay = (int)$stHomNay->fetchColumn();

// Chức năng 2: Đọc dữ liệu thống kê lượt xem trong 7 ngày gần nhất
$st7Ngay = $db->prepare(
    'SELECT ngay, so_luot 
     FROM tv_luot_xem 
     WHERE mssv = ? 
     ORDER BY ngay DESC 
     LIMIT 7'
);
$st7Ngay->execute([MSSV]);
$dsLuotXem = $st7Ngay->fetchAll();
$tong7Ngay = array_sum(array_column($dsLuotXem, 'so_luot'));

$tieuDe = 'Trang cá nhân - Vongsena Sauphasith';
$trang = 'gioi-thieu';

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 900px; margin: 30px auto;">
  <section style="display: flex; gap: 30px; align-items: center; background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; flex-wrap: wrap;">
    <img src="<?= $goc ?>images/sauphasith-avatar.png" alt="Ảnh Vongsena Sauphasith" width="130" height="130" style="border-radius: 50%; object-fit: cover; border: 3px solid #1e3b6d;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d; font-size: 1.8rem;">Vongsena Sauphasith</h1>
      <p style="margin: 5px 0; color: #64748b;">Mã sinh viên: <strong>3120224186</strong> | Lớp: <strong>24CNTT3</strong></p>
      <p style="margin: 5px 0;">Phụ trách Phần A: <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Biểu mẫu Liên hệ &amp; Upload tệp finfo</span></p>
      <p style="margin: 5px 0; color: #475569;">Trách nhiệm: Xử lý lưu trữ liên hệ vào bảng lien_he MySQL, kiểm tra an toàn MIME-type tệp tải lên và xử lý lỗi hệ thống 403/404.</p>
    </div>
  </section>

  <!-- Khối hiển thị số lượt xem -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
      <h2 style="margin: 0; color: #1e3b6d; font-size: 1.3rem;">📈 Thống kê lưu lượng truy cập trang (Bảng tv_luot_xem)</h2>
      <div style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 6px 14px; border-radius: 6px; color: #065f46; font-weight: bold;">
        Lượt xem hôm nay: <?= $luotHomNay ?> lượt
      </div>
    </div>

    <p style="color: #64748b; font-size: 0.95rem;">
      Bộ đếm sử dụng câu lệnh SQL <code>INSERT ... ON DUPLICATE KEY UPDATE</code> để tự động khởi tạo hoặc tăng số lượt xem theo khóa duy nhất <code>(mssv, ngay)</code>:
    </p>

    <!-- Bảng chi tiết 7 ngày -->
    <div style="overflow-x: auto; margin-top: 15px;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
            <th style="padding: 10px 14px;">Ngày thống kê</th>
            <th style="padding: 10px 14px; width: 150px; text-align: right;">Số lượt truy cập</th>
            <th style="padding: 10px 14px; width: 45%;">Tỷ lệ tương đối</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dsLuotXem as $row): 
            $phanTram = $tong7Ngay > 0 ? round(($row['so_luot'] / $tong7Ngay) * 100) : 0;
          ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 10px 14px; font-weight: 500;">
                <?= e(date('d/m/Y', strtotime($row['ngay']))) ?>
                <?php if ($row['ngay'] === date('Y-m-d')): ?>
                  <span style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; padding: 1px 6px; border-radius: 3px; margin-left: 6px;">Hôm nay</span>
                <?php endif; ?>
              </td>
              <td style="padding: 10px 14px; text-align: right; font-weight: bold; color: #1e40af;">
                <?= $row['so_luot'] ?> lượt
              </td>
              <td style="padding: 10px 14px;">
                <div style="background: #e2e8f0; border-radius: 4px; height: 16px; overflow: hidden;">
                  <div style="background: #0284c7; height: 100%; width: <?= $phanTram ?>%;"></div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
            <td style="padding: 12px 14px;">Tổng cộng 7 ngày:</td>
            <td style="padding: 12px 14px; text-align: right; color: #dc2626;"><?= $tong7Ngay ?> lượt</td>
            <td style="padding: 12px 14px;"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
