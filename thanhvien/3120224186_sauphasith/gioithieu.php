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

// Kiểm tra quyền chủ trang từ bảng thanh_vien
$st = $db->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$st->execute([MSSV]);
$tv = $st->fetch();

$u = nguoiDung();
$laChuTrang = ($u['id'] ?? 0) === ($tv['nguoi_dung_id'] ?? -1);

// Xử lý cập nhật / đặt lại lượt truy cập hôm nay (chỉ chủ trang)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$laChuTrang) {
        ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        exit('403 Forbidden — Bạn không có quyền thao tác trên trang này.');
    }
    $soMoi = filter_var($_POST['so_moi'] ?? '', FILTER_VALIDATE_INT);
    if ($soMoi !== false && $soMoi >= 1) {
        $stUp = $db->prepare('UPDATE tv_luot_xem SET so_luot = ? WHERE mssv = ? AND ngay = CURDATE()');
        $stUp->execute([$soMoi, MSSV]);
        $_SESSION['flash'] = 'Đã cập nhật số lượt truy cập hôm nay thành công!';
        header('Location: gioithieu.php');
        exit;
    }
}

// Chức năng 1: Ghi nhận hoặc tăng số lượt xem trong ngày hôm nay bằng INSERT ... ON DUPLICATE KEY UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stDem = $db->prepare(
        'INSERT INTO tv_luot_xem (mssv, ngay, so_luot) 
         VALUES (?, CURDATE(), 1) 
         ON DUPLICATE KEY UPDATE so_luot = so_luot + 1'
    );
    $stDem->execute([MSSV]);
}

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

$thongBao = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

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
      <p style="margin: 5px 0;">GitHub: <a href="https://github.com/happy2072005-prog" target="_blank" rel="noopener noreferrer" style="color: #0369a1; font-weight: bold;">@happy2072005-prog (https://github.com/happy2072005-prog)</a></p>
      <p style="margin: 5px 0;">Vai trò: <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Thành viên Nhóm 12</span></p>
      <p style="margin: 5px 0; color: #475569;">Trách nhiệm website: Biểu mẫu liên hệ lien-he.php, kiểm tra upload ảnh an toàn MIME finfo và xử lý lỗi hệ thống.</p>
      <div style="margin-top: 10px;">
        <a href="../../gioi-thieu.php" class="nut nut--vien" style="padding: 4px 12px; font-size: 0.85rem;">&larr; Quay lại trang Giới thiệu nhóm</a>
      </div>
      <?php if ($laChuTrang): ?>
        <p style="margin: 8px 0 0 0; color: #16a34a; font-weight: bold;">✓ Bạn đang đăng nhập với tư cách Chủ trang (sauphasith@ued.vn)</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- BẢNG NHIỆM VỤ CHI TIẾT TRONG VIỆC LÀM BÁO CÁO BÀI TẬP 7 (THEO YÊU CẦU) -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h2 style="margin: 0; color: #1e3b6d; font-size: 1.35rem;">📋 Nhiệm vụ &amp; Đóng góp trong Báo cáo Bài tập nhóm số 7 (Bảo mật Web)</h2>
      <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;">Đóng góp: 100%</span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-bottom: 18px;">
      <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #1e3b6d;">
        <h4 style="margin: 0 0 8px 0; color: #1e3b6d;">1. Biện pháp bảo mật phụ trách</h4>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Biện pháp 4:</strong> Biến môi trường <code>.env</code> với <code>vlucas/phpdotenv</code> v5.7, cam kết không lộ mật khẩu vào Git (<code>git ls-files .env</code> = 0).</li>
          <li>Thực hiện kiểm tra an toàn thư viện bên thứ ba với <code>composer audit</code> (0 lỗ hổng).</li>
          <li><strong>Biện pháp 6:</strong> Phân quyền CSDL đặc quyền tối thiểu (Least Privilege) với tài khoản <code>nhom12_app</code> (chỉ DML, cấm DDL báo lỗi 1142).</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #ea580c;">
        <h4 style="margin: 0 0 8px 0; color: #ea580c;">2. Nội dung biên soạn báo cáo</h4>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Phần A:</strong> Báo cáo cấu hình <code>.env</code>, <code>.gitignore</code>, <code>.env.example</code> và tài khoản CSDL <code>database/tai-khoan.sql</code> trong Bảng 1.</li>
          <li><strong>Phần B:</strong> Báo cáo bảo mật trang cá nhân <code>vongsena.php</code>, kiểm tra quyền chủ trang cập nhật lượt xem <code>tv_luot_xem</code>, 5 ca kiểm thử thực nghiệm.</li>
          <li><strong>Phần C:</strong> Biên soạn phương án ứng cứu khẩn cấp 4 bước cho <strong>Câu C.4</strong> (Ứng phó khi rò rỉ .env lên GitHub).</li>
        </ul>
      </div>
    </div>

    <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.9rem; color: #334155; line-height: 1.6;">
      <strong style="color: #1e3b6d;">3. Thực thi kiểm thử &amp; Minh chứng chất lượng:</strong>
      <ul style="margin: 6px 0 0 0; padding-left: 18px;">
        <li>Thực thi kiểm tra quyền MySQL bằng lệnh <code>DROP TABLE san_pham</code> trả về lỗi <code>Error 1142 (42000): DROP command denied</code>.</li>
        <li>Chụp và chèn ảnh minh chứng <strong>Hình 8</strong> vào báo cáo đồ án.</li>
        <li>Commit Git phụ trách: <code>0997cec</code> (Biện pháp 4), <code>988d5d8</code> (Biện pháp 6), <code>e9fc487</code> (Phần B cá nhân).</li>
      </ul>
    </div>
  </section>

  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 25px;">
      <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <?php if ($laChuTrang): ?>
    <section style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
      <h2 style="margin-top: 0; color: #1e3b6d; font-size: 1.25rem;">⚙️ Quản lý lượt truy cập (Dành cho chủ trang)</h2>
      <form method="POST" action="gioithieu.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <?= csrfField() ?>
        <label for="so_moi" style="font-weight: bold;">Cập nhật lượt xem hôm nay:</label>
        <input type="number" id="so_moi" name="so_moi" value="<?= $luotHomNay ?>" min="1" max="99999" style="width: 100px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
        <button type="submit" class="nut nut--chinh">Lưu số lượt</button>
      </form>
    </section>
  <?php endif; ?>

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
