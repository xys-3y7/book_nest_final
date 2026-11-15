<?php
/**
 * thanhvien/3120223169_sinh/gioithieu.php - Trang cá nhân của Nguyễn Thị Sinh
 * Mã sinh viên: 3120223169 - Lớp: 24CNTT3
 * Chức năng Phần B:
 *   1. Danh sách sách yêu thích và đánh giá sao (1 - 5 sao) từ bảng tv_sach_yeu_thich JOIN với san_pham.
 *   2. Biểu mẫu gửi cảm nhận, xếp hạng và nút xóa sách yêu thích khỏi CSDL (chỉ chủ trang đăng nhập mới được làm).
 * Cách thử: Xem danh sách sách và đánh giá sao; đăng nhập tài khoản sinh@ued.vn / matkhau123 để thêm hoặc xóa sách yêu thích.
 */

require_once __DIR__ . '/../../inc/config.php';

const MSSV = '3120223169';
$goc = '../../';
$db = db();

$st = $db->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$st->execute([MSSV]);
$tv = $st->fetch();

$u = nguoiDung();
$laChuTrang = ($u['id'] ?? 0) === ($tv['nguoi_dung_id'] ?? -1);

$loi = [];

// Xử lý thêm hoặc xóa sách yêu thích (chỉ chủ trang có quyền)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$laChuTrang) {
        ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        exit('403 Forbidden — Bạn không có quyền thao tác trên trang này.');
    }

    $xoaSpId = filter_var($_POST['xoa_sp_id'] ?? '', FILTER_VALIDATE_INT);
    if ($xoaSpId) {
        $stDel = $db->prepare('DELETE FROM tv_sach_yeu_thich WHERE san_pham_id = ? AND mssv = ?');
        $stDel->execute([$xoaSpId, MSSV]);
        $_SESSION['flash'] = 'Đã xóa sách khỏi danh sách yêu thích thành công!';
        header('Location: gioithieu.php');
        exit;
    }

    $spId = filter_var($_POST['san_pham_id'] ?? '', FILTER_VALIDATE_INT);
    $danhGia = filter_var($_POST['danh_gia'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    $camNhan = trim(chuoi($_POST['cam_nhan'] ?? ''));

    if (!$spId) {
        $loi['san_pham_id'] = 'Vui lòng chọn một cuốn sách trong danh mục';
    }
    if (!$danhGia) {
        $loi['danh_gia'] = 'Đánh giá phải từ 1 đến 5 sao';
    }

    if (empty($loi)) {
        try {
            $stAdd = $db->prepare(
                'INSERT INTO tv_sach_yeu_thich (mssv, san_pham_id, danh_gia, cam_nhan) 
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE danh_gia = VALUES(danh_gia), cam_nhan = VALUES(cam_nhan)'
            );
            $stAdd->execute([MSSV, $spId, $danhGia, $camNhan ?: null]);
            $_SESSION['flash'] = 'Đã lưu đánh giá sách yêu thích thành công!';
            header('Location: gioithieu.php');
            exit;
        } catch (PDOException $e) {
            $loi['he_thong'] = 'Lỗi lưu CSDL: ' . $e->getMessage();
        }
    }
}

// Chức năng 1: Lấy danh sách sách yêu thích JOIN với bảng san_pham
$stDs = $db->prepare(
    'SELECT syt.*, sp.ten AS ten_sach, sp.gia, sp.anh 
     FROM tv_sach_yeu_thich syt 
     JOIN san_pham sp ON syt.san_pham_id = sp.id 
     WHERE syt.mssv = ? 
     ORDER BY syt.id DESC'
);
$stDs->execute([MSSV]);
$dsYeuThich = $stDs->fetchAll();

// Lấy danh sách toàn bộ sách để điền vào select (nếu là chủ trang)
$dsTatCaSach = $laChuTrang ? $db->query('SELECT id, ten FROM san_pham ORDER BY ten')->fetchAll() : [];

$tongSachYeuThich = count($dsYeuThich);

$thongBao = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$tieuDe = 'Trang cá nhân - Nguyễn Thị Sinh | Tủ sách yêu thích';
$trang = 'gioi-thieu';

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 960px; margin: 32px auto; padding: 0 16px;">
  <!-- Thẻ thông tin cá nhân (Profile Hero) -->
  <section style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 28px; overflow: hidden; position: relative;">
    <div style="height: 100px; background: linear-gradient(135deg, #be185d 0%, #ec4899 50%, #8b5cf6 100%);"></div>
    <div style="padding: 0 28px 24px; position: relative; display: flex; gap: 24px; align-items: flex-end; flex-wrap: wrap; margin-top: -50px;">
      <img src="<?= $goc ?>images/sinh-avatar.png" alt="Ảnh Nguyễn Thị Sinh" width="120" height="120" style="border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15); background: #ffffff;">
      <div style="flex: 1; min-width: 280px; padding-top: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
          <h1 style="margin: 0; color: #0f2b5c; font-size: 1.85rem; font-weight: 800;">Nguyễn Thị Sinh</h1>
          <span style="background: #fce7f3; color: #be185d; font-size: 0.8rem; font-weight: 700; padding: 4px 12px; border-radius: 9999px;">📚 Thành viên &bull; MSSV: 3120223169</span>
          <span style="background: #ecfdf5; color: #065f46; font-size: 0.8rem; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">Lớp: 24CNTT3</span>
        </div>
        <p style="margin: 6px 0 10px; color: #475569; font-size: 0.95rem; line-height: 1.5;">
          <strong>Trách nhiệm chính:</strong> Trang chi tiết <code>chi-tiet.php</code>, cookie đã xem, đăng ký / đăng nhập, bảo vệ phiên làm việc an toàn Session Security.
        </p>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <a href="https://github.com/chingg2005" target="_blank" rel="noopener noreferrer" class="github-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #0f172a; color: #ffffff; border-radius: 20px; text-decoration: none; font-size: 0.85rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: chingg2005</span>
          </a>
          <a href="../../gioi-thieu.php" class="nut nut--vien" style="padding: 5px 14px; font-size: 0.85rem; border-radius: 9999px;">&larr; Trang giới thiệu Nhóm 12</a>
          
          <?php if ($laChuTrang): ?>
            <span style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-size: 0.85rem; font-weight: 700; padding: 5px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;">
              ✓ Đang đăng nhập: Chủ trang (sinh@ued.vn)
            </span>
          <?php else: ?>
            <a href="../../dang-nhap.php?email=sinh@ued.vn" style="background: #fef3c7; border: 1px solid #fde68a; color: #b45309; font-size: 0.85rem; font-weight: 600; padding: 5px 12px; border-radius: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
              🔑 Đăng nhập tài khoản Sinh để thêm / xóa sách yêu thích &rarr;
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Nhiệm vụ báo cáo đồ án chi tiết (Theo Báo cáo Bài tập 7) -->
  <section style="background: #ffffff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h2 style="margin: 0; color: #0f2b5c; font-size: 1.3rem;">📋 Bảng phân công nhiệm vụ chi tiết của Nguyễn Thị Sinh</h2>
      <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-size: 0.82rem; font-weight: 700;">Đóng góp: 100% &bull; Hoàn thành xuất sắc</span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #be185d;">
        <strong style="color: #be185d; font-size: 1rem; display: block; margin-bottom: 8px;">1. Vai trò Lập trình Chi tiết Sách &amp; Tài khoản:</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li>Xây dựng trang xem chi tiết giáo trình <code>chi-tiet.php</code>, hiển thị thông tin, giá tiền và mô tả.</li>
          <li>Xây dựng tính năng ghi nhận lịch sử sách vừa xem qua cookie an toàn <code>da_xem</code>.</li>
          <li>Đồng phát triển luồng xác thực đăng nhập (<code>dang-nhap.php</code>) và đăng ký (<code>dang-ky.php</code>).</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #8b5cf6;">
        <strong style="color: #8b5cf6; font-size: 1rem; display: block; margin-bottom: 8px;">2. Biện pháp bảo mật đảm nhiệm (Chương 7):</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Biện pháp 2 (Bảo vệ Phiên làm việc):</strong> Cấu hình <code>HttpOnly</code>, <code>SameSite=Lax</code>, <code>session.use_strict_mode</code>, hủy sau 30 phút, gọi <code>session_regenerate_id()</code> chống Session Fixation.</li>
          <li><strong>Bảo vệ Cookie &amp; Ẩn lỗi hệ thống:</strong> Thiết lập cờ bảo vệ cookie, ẩn chi tiết biệt lập lỗi trên production để chống rò rỉ cấu hình.</li>
          <li><strong>Thảo luận C.2:</strong> Phân tích cơ chế HttpOnly vs XSS và mô hình 3 lớp phòng vệ toàn diện: <code>e()</code>, <code>HttpOnly</code>, và <code>CSP</code>.</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #16a34a;">
        <strong style="color: #16a34a; font-size: 1rem; display: block; margin-bottom: 8px;">3. Chức năng CSDL riêng Phần B:</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Tủ sách yêu thích &amp; Đánh giá sao:</strong> Bảng <code>tv_sach_yeu_thich</code> kết nối JOIN bảng <code>san_pham</code>.</li>
          <li><strong>Đánh giá chuyên môn:</strong> Hiển thị xếp hạng 1-5 sao ⭐ kèm trích dẫn cảm nhận cá nhân.</li>
          <li><strong>Phân quyền chủ trang:</strong> Chỉ tài khoản Sinh mới có quyền thêm nhận xét mới và xóa sách khỏi tủ sách (403 Forbidden nếu trái quyền).</li>
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

  <!-- Chức năng CSDL 2: Biểu mẫu thêm sách yêu thích & đánh giá sao (chỉ dành cho chủ trang) -->
  <?php if ($laChuTrang): ?>
    <section style="background: #fdf2f8; border: 1px solid #fbcfe8; padding: 24px; border-radius: 12px; margin-bottom: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
      <h2 style="margin: 0 0 6px; color: #9d174d; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
        <span>⭐</span> Thêm sách yêu thích &amp; Viết đánh giá (Quyền Chủ trang)
      </h2>
      <p style="margin: 0 0 16px; color: #be185d; font-size: 0.9rem;">
        Dữ liệu sẽ được lưu an toàn vào bảng <code>tv_sach_yeu_thich</code> qua Prepared Statement.
      </p>

      <form method="POST" action="gioithieu.php" style="background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #fbcfe8;">
        <?= csrfField() ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 14px;">
          <div>
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
              Chọn cuốn sách trong kho <span style="color: #ef4444;">*</span>
            </label>
            <select name="san_pham_id" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.92rem;">
              <option value="">-- Chọn một cuốn sách trong CSDL --</option>
              <?php foreach ($dsTatCaSach as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['ten']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($loi['san_pham_id'])): ?>
              <p style="color: #ef4444; font-size: 0.85rem; margin: 4px 0 0;"><?= e($loi['san_pham_id']) ?></p>
            <?php endif; ?>
          </div>

          <div>
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
              Xếp hạng đánh giá <span style="color: #ef4444;">*</span>
            </label>
            <select name="danh_gia" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.92rem;">
              <option value="5">⭐⭐⭐⭐⭐ (5 sao - Xuất sắc / Khuyên đọc)</option>
              <option value="4">⭐⭐⭐⭐ (4 sao - Rất hay / Giá trị cao)</option>
              <option value="3">⭐⭐⭐ (3 sao - Khá / Tham khảo tốt)</option>
              <option value="2">⭐⭐ (2 sao - Trung bình)</option>
              <option value="1">⭐ (1 sao - Cần cải thiện)</option>
            </select>
          </div>
        </div>

        <div style="margin-bottom: 18px;">
          <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
            Cảm nhận &amp; Bình luận chuyên môn (Tùy chọn)
          </label>
          <textarea name="cam_nhan" rows="2" placeholder="Ghi chú cảm nhận cá nhân, điểm tâm đắc hoặc bài học từ cuốn sách này..." style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; line-height: 1.5; font-size: 0.92rem;"></textarea>
        </div>

        <button type="submit" class="nut nut--chinh" style="padding: 10px 22px; font-size: 0.95rem; border-radius: 6px; background: #be185d; border-color: #be185d;">
          💖 Lưu vào Tủ sách yêu thích
        </button>
      </form>
    </section>
  <?php endif; ?>

  <!-- Chức năng CSDL 1: Tủ sách yêu thích JOIN bảng san_pham -->
  <section style="background: #ffffff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 22px;">
      <div>
        <h2 style="margin: 0; color: #0f2b5c; font-size: 1.35rem; display: flex; align-items: center; gap: 8px;">
          <span>📚</span> Tủ sách yêu thích &amp; Đánh giá sao (Bảng: <code>tv_sach_yeu_thich</code>)
        </h2>
        <p style="margin: 4px 0 0; color: #64748b; font-size: 0.9rem;">
          Truy vấn JOIN với bảng <code>san_pham</code> hiển thị ảnh bìa, giá tiền, số sao và cảm nhận.
        </p>
      </div>

      <span style="background: #fce7f3; color: #be185d; font-size: 0.85rem; font-weight: 700; padding: 4px 12px; border-radius: 20px;">
        Tổng: <?= $tongSachYeuThich ?> cuốn yêu thích
      </span>
    </div>

    <?php if (empty($dsYeuThich)): ?>
      <div style="text-align: center; padding: 36px 20px; background: #f8fafc; border-radius: 8px; color: #64748b;">
        <p style="margin: 0; font-style: italic;">Chưa có cuốn sách nào trong tủ sách yêu thích.</p>
      </div>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 18px;">
        <?php foreach ($dsYeuThich as $yt): ?>
          <article style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; position: relative;">
            <a href="<?= $goc ?>chi-tiet.php?id=<?= $yt['san_pham_id'] ?>" style="flex-shrink: 0;">
              <img src="<?= $goc . e(anhSach($yt['anh'])) ?>" alt="<?= e($yt['ten_sach']) ?>" width="80" height="110" style="border-radius: 6px; object-fit: cover; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
            </a>

            <div style="flex: 1; min-width: 260px;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700;">
                  <a href="<?= $goc ?>chi-tiet.php?id=<?= $yt['san_pham_id'] ?>" style="color: #0f2b5c; text-decoration: none;">
                    <?= e($yt['ten_sach']) ?>
                  </a>
                </h3>

                <?php if ($laChuTrang): ?>
                  <form method="POST" action="gioithieu.php" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cuốn sách này khỏi danh sách yêu thích?');">
                    <?= csrfField() ?>
                    <input type="hidden" name="xoa_sp_id" value="<?= $yt['san_pham_id'] ?>">
                    <button type="submit" title="Xóa khỏi yêu thích" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 4px 10px; border-radius: 4px; font-size: 0.78rem; cursor: pointer; font-weight: 600;">
                      🗑️ Xóa
                    </button>
                  </form>
                <?php endif; ?>
              </div>

              <!-- Đánh giá sao -->
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                <span style="color: #f59e0b; font-size: 1.15rem; letter-spacing: 2px;">
                  <?= str_repeat('★', (int)$yt['danh_gia']) . str_repeat('☆', 5 - (int)$yt['danh_gia']) ?>
                </span>
                <span style="background: #fef3c7; color: #b45309; font-size: 0.78rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                  <?= $yt['danh_gia'] ?> / 5 sao
                </span>
                <span style="color: #059669; font-weight: 700; font-size: 0.9rem; margin-left: auto;">
                  <?= number_format($yt['gia'], 0, ',', '.') ?> VNĐ
                </span>
              </div>

              <!-- Cảm nhận cá nhân -->
              <div style="background: #ffffff; border-left: 3px solid #ec4899; padding: 10px 14px; border-radius: 0 6px 6px 0; margin-bottom: 10px;">
                <p style="margin: 0; color: #334155; font-size: 0.92rem; line-height: 1.5; font-style: italic;">
                  "<?= e($yt['cam_nhan'] ?: 'Một cuốn sách tuyệt vời xứng đáng có trong tủ sách sinh viên CNTT.') ?>"
                </p>
              </div>

              <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #94a3b8;">
                <span>Đã lưu vào CSDL: <?= e($yt['ngay_them']) ?></span>
                <a href="<?= $goc ?>chi-tiet.php?id=<?= $yt['san_pham_id'] ?>" style="color: #1e40af; text-decoration: none; font-weight: 600;">
                  Xem chi tiết giáo trình &rarr;
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
