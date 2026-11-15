<?php
/**
 * thanhvien/3120224186_sauphasith/gioithieu.php - Trang cá nhân của Vongsena Sauphasith
 * Mã sinh viên: 3120224186 - Lớp: 24CNTT3
 * Chức năng Phần B:
 *   1. Bộ đếm lượt xem truy cập hàng ngày dùng INSERT ... ON DUPLICATE KEY UPDATE vào bảng tv_luot_xem.
 *   2. Bảng thống kê chi tiết lượt truy cập trong 7 ngày gần nhất từ CSDL MySQL có đồ thị thanh tỷ lệ.
 *   3. Biểu mẫu cập nhật/hiệu chỉnh số lượt xem hôm nay (chỉ chủ trang đăng nhập mới được thực hiện).
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
$soNgay = count($dsLuotXem);
$trungBinhNgay = $soNgay > 0 ? round($tong7Ngay / $soNgay) : 0;
$maxLuot = !empty($dsLuotXem) ? max(array_column($dsLuotXem, 'so_luot')) : 0;

$thongBao = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$tieuDe = 'Trang cá nhân - Vongsena Sauphasith | Thống kê lượt xem';
$trang = 'gioi-thieu';

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 960px; margin: 32px auto; padding: 0 16px;">
  <!-- Thẻ thông tin cá nhân (Profile Hero) -->
  <section style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 28px; overflow: hidden; position: relative;">
    <div style="height: 100px; background: linear-gradient(135deg, #065f46 0%, #059669 50%, #0284c7 100%);"></div>
    <div style="padding: 0 28px 24px; position: relative; display: flex; gap: 24px; align-items: flex-end; flex-wrap: wrap; margin-top: -50px;">
      <img src="<?= $goc ?>images/sauphasith-avatar.png" alt="Ảnh Vongsena Sauphasith" width="120" height="120" style="border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15); background: #ffffff;">
      <div style="flex: 1; min-width: 280px; padding-top: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
          <h1 style="margin: 0; color: #0f2b5c; font-size: 1.85rem; font-weight: 800;">Vongsena Sauphasith</h1>
          <span style="background: #d1fae5; color: #065f46; font-size: 0.8rem; font-weight: 700; padding: 4px 12px; border-radius: 9999px;">📈 Thành viên &bull; MSSV: 3120224186</span>
          <span style="background: #ecfdf5; color: #065f46; font-size: 0.8rem; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">Lớp: 24CNTT3</span>
        </div>
        <p style="margin: 6px 0 10px; color: #475569; font-size: 0.95rem; line-height: 1.5;">
          <strong>Trách nhiệm chính:</strong> Biểu mẫu liên hệ <code>lien-he.php</code>, kiểm tra upload ảnh an toàn MIME finfo, biến môi trường <code>.env</code> và quyền CSDL tối thiểu.
        </p>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <a href="https://github.com/happy2072005-prog" target="_blank" rel="noopener noreferrer" class="github-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #0f172a; color: #ffffff; border-radius: 20px; text-decoration: none; font-size: 0.85rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: happy2072005-prog</span>
          </a>
          <a href="../../gioi-thieu.php" class="nut nut--vien" style="padding: 5px 14px; font-size: 0.85rem; border-radius: 9999px;">&larr; Trang giới thiệu Nhóm 12</a>
          
          <?php if ($laChuTrang): ?>
            <span style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-size: 0.85rem; font-weight: 700; padding: 5px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;">
              ✓ Đang đăng nhập: Chủ trang (sauphasith@ued.vn)
            </span>
          <?php else: ?>
            <a href="../../dang-nhap.php?email=sauphasith@ued.vn" style="background: #fef3c7; border: 1px solid #fde68a; color: #b45309; font-size: 0.85rem; font-weight: 600; padding: 5px 12px; border-radius: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
              🔑 Đăng nhập tài khoản Sauphasith để cập nhật lượt xem &rarr;
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Nhiệm vụ báo cáo đồ án chi tiết (Theo Báo cáo Bài tập 7) -->
  <section style="background: #ffffff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h2 style="margin: 0; color: #0f2b5c; font-size: 1.3rem;">📋 Bảng phân công nhiệm vụ chi tiết của Vongsena Sauphasith</h2>
      <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-size: 0.82rem; font-weight: 700;">Đóng góp: 100% &bull; Hoàn thành xuất sắc</span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #059669;">
        <strong style="color: #059669; font-size: 1rem; display: block; margin-bottom: 8px;">1. Vai trò Lập trình Giao diện &amp; Liên hệ:</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li>Xây dựng trang liên hệ và góp ý <code>lien-he.php</code>, xử lý biểu mẫu liên hệ an toàn.</li>
          <li>Xây dựng cơ chế upload ảnh an toàn kiểm tra MIME bằng <code>finfo_file()</code> trong <code>inc/ham.php</code>.</li>
          <li>Đồng phát triển trang thông báo lỗi tùy biến an toàn <code>403.php</code>, <code>404.php</code>, <code>500.php</code>.</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #0284c7;">
        <strong style="color: #0284c7; font-size: 1rem; display: block; margin-bottom: 8px;">2. Biện pháp bảo mật đảm nhiệm (Chương 7):</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Biện pháp 4 (.env &amp; Composer):</strong> Quản lý biến môi trường với <code>vlucas/phpdotenv</code>, bảo vệ <code>.gitignore</code>, tạo <code>.env.example</code>, kiểm tra <code>composer audit</code> đạt 0 lỗ hổng.</li>
          <li><strong>Biện pháp 6 (Quyền tối thiểu CSDL):</strong> Tạo tài khoản CSDL <code>nhom12_app</code> chỉ cấp quyền DML, cấm DDL (chặn đứng câu lệnh DROP TABLE với mã lỗi MySQL 1142).</li>
          <li><strong>Thảo luận C.4:</strong> Xây dựng quy trình ứng cứu khẩn cấp 4 bước khi vô tình để lộ file <code>.env</code> lên GitHub.</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #16a34a;">
        <strong style="color: #16a34a; font-size: 1rem; display: block; margin-bottom: 8px;">3. Chức năng CSDL riêng Phần B:</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Bộ đếm lượt xem theo ngày:</strong> Bảng <code>tv_luot_xem</code>, câu lệnh nguyên tử <code>INSERT ... ON DUPLICATE KEY UPDATE so_luot = so_luot + 1</code>.</li>
          <li><strong>Đồ thị phân tích lưu lượng 7 ngày:</strong> Tính toán tỷ lệ phần trăm và biểu diễn qua thanh tiến trình động.</li>
          <li><strong>Phân quyền chủ trang:</strong> Chỉ tài khoản Sauphasith mới có form hiệu chỉnh số lượt xem hôm nay với CSRF.</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- Thông báo trạng thái -->
  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 8px; margin-bottom: 24px; font-weight: 500;" aria-live="polite">
      ✓ <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <!-- Chức năng CSDL 3: Hiệu chỉnh số lượt truy cập hôm nay (chỉ dành cho chủ trang) -->
  <?php if ($laChuTrang): ?>
    <section style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 22px; border-radius: 12px; margin-bottom: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
      <h2 style="margin: 0 0 6px; color: #166534; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
        <span>⚙️</span> Hiệu chỉnh lượt truy cập hôm nay (Quyền Chủ trang)
      </h2>
      <p style="margin: 0 0 16px; color: #15803d; font-size: 0.9rem;">
        Cập nhật trực tiếp số liệu của ngày hôm nay trong bảng <code>tv_luot_xem</code>:
      </p>

      <form method="POST" action="gioithieu.php" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; background: #ffffff; padding: 16px; border-radius: 8px; border: 1px solid #bbf7d0;">
        <?= csrfField() ?>
        <label for="so_moi" style="font-weight: 600; color: #1e293b; font-size: 0.9rem;">
          Số lượt truy cập mới:
        </label>
        <input type="number" id="so_moi" name="so_moi" value="<?= $luotHomNay ?>" min="1" max="999999" required style="width: 130px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 700; color: #0f2b5c; font-size: 1rem;">
        <button type="submit" class="nut nut--chinh" style="padding: 8px 18px; font-size: 0.9rem; border-radius: 6px; background: #166534; border-color: #166534;">
          💾 Lưu số lượt
        </button>
      </form>
    </section>
  <?php endif; ?>

  <!-- Chức năng CSDL 1 & 2: Bảng điều khiển lưu lượng truy cập 7 ngày -->
  <section style="background: #ffffff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 22px;">
      <div>
        <h2 style="margin: 0; color: #0f2b5c; font-size: 1.35rem; display: flex; align-items: center; gap: 8px;">
          <span>📈</span> Thống kê lưu lượng truy cập trang (Bảng CSDL: <code>tv_luot_xem</code>)
        </h2>
        <p style="margin: 4px 0 0; color: #64748b; font-size: 0.9rem;">
          Mỗi lần tải trang, số lượt xem tự động tăng qua <code>INSERT ... ON DUPLICATE KEY UPDATE</code>.
        </p>
      </div>

      <div style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 8px 18px; border-radius: 8px; color: #047857; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 1.2rem;">👁️</span> Hôm nay: <span style="font-size: 1.3rem; color: #065f46;"><?= $luotHomNay ?></span> lượt
      </div>
    </div>

    <!-- 4 Khối chỉ số thống kê (Stat KPI Cards) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-bottom: 26px;">
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">LƯỢT TRUY CẬP HÔM NAY</div>
        <div style="font-size: 2rem; font-weight: 800; color: #059669;"><?= $luotHomNay ?></div>
        <div style="color: #94a3b8; font-size: 0.78rem; margin-top: 2px;">Tự tăng khi F5 tải trang</div>
      </div>
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">TỔNG 7 NGÀY QUA</div>
        <div style="font-size: 2rem; font-weight: 800; color: #1e40af;"><?= $tong7Ngay ?></div>
        <div style="color: #94a3b8; font-size: 0.78rem; margin-top: 2px;">Trong 7 ngày gần nhất</div>
      </div>
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">TRUNG BÌNH MỖI NGÀY</div>
        <div style="font-size: 2rem; font-weight: 800; color: #0284c7;"><?= $trungBinhNgay ?></div>
        <div style="color: #94a3b8; font-size: 0.78rem; margin-top: 2px;">Lượt xem / ngày</div>
      </div>
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">NGÀY CAO ĐIỂM NHẤT</div>
        <div style="font-size: 2rem; font-weight: 800; color: #d97706;"><?= $maxLuot ?></div>
        <div style="color: #94a3b8; font-size: 0.78rem; margin-top: 2px;">Kỷ lục trong 7 ngày</div>
      </div>
    </div>

    <!-- Bảng phân tích chi tiết kèm biểu đồ tỷ lệ -->
    <h3 style="color: #1e293b; font-size: 1.15rem; margin: 0 0 14px;">
      Chi tiết lưu lượng truy cập theo ngày
    </h3>

    <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.92rem;">
        <thead>
          <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; color: #475569; font-size: 0.85rem;">
            <th style="padding: 12px 16px;">NGÀY THỐNG KÊ</th>
            <th style="padding: 12px 16px; text-align: right; width: 140px;">SỐ LƯỢT XEM</th>
            <th style="padding: 12px 16px; width: 45%;">TỶ LỆ LƯU LƯỢNG</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dsLuotXem as $row): 
            $phanTram = $tong7Ngay > 0 ? round(($row['so_luot'] / $tong7Ngay) * 100) : 0;
            $laHomNay = ($row['ngay'] === date('Y-m-d'));
          ?>
            <tr style="border-bottom: 1px solid #e2e8f0; background: <?= $laHomNay ? '#f0fdf4' : '#ffffff' ?>;">
              <td style="padding: 12px 16px; font-weight: 600; color: #1e293b;">
                <?= e(date('d/m/Y', strtotime($row['ngay']))) ?>
                <?php if ($laHomNay): ?>
                  <span style="background: #dcfce7; color: #166534; font-size: 0.75rem; padding: 2px 8px; border-radius: 9999px; margin-left: 8px; font-weight: 700;">Hôm nay</span>
                <?php endif; ?>
              </td>
              <td style="padding: 12px 16px; text-align: right; font-weight: 700; color: <?= $laHomNay ? '#059669' : '#1e40af' ?>;">
                <?= number_format($row['so_luot']) ?> lượt
              </td>
              <td style="padding: 12px 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div style="flex: 1; background: #e2e8f0; border-radius: 9999px; height: 12px; overflow: hidden;">
                    <div style="background: <?= $laHomNay ? 'linear-gradient(90deg, #10b981 0%, #059669 100%)' : 'linear-gradient(90deg, #38bdf8 0%, #0284c7 100%)' ?>; height: 100%; width: <?= $phanTram ?>%; border-radius: 9999px;"></div>
                  </div>
                  <span style="font-size: 0.8rem; font-weight: 600; color: #64748b; width: 40px; text-align: right;"><?= $phanTram ?>%</span>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
            <td style="padding: 14px 16px; color: #0f2b5c;">Tổng cộng 7 ngày:</td>
            <td style="padding: 14px 16px; text-align: right; color: #dc2626; font-size: 1.05rem;">
              <?= number_format($tong7Ngay) ?> lượt
            </td>
            <td style="padding: 14px 16px; color: #64748b; font-size: 0.85rem; font-weight: normal;">
              100% dữ liệu từ CSDL MySQL <code>tv_luot_xem</code>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
