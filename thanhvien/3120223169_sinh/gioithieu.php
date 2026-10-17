<?php
/**
 * thanhvien/3120223169_sinh/gioithieu.php - Trang cá nhân của Nguyễn Thị Sinh
 * Mã sinh viên: 3120223169 - Lớp: 24CNTT3
 * Chức năng Phần B:
 *   1. Danh sách sách yêu thích và đánh giá sao (1 - 5 sao) từ bảng tv_sach_yeu_thich JOIN với san_pham.
 *   2. Biểu mẫu gửi cảm nhận và thêm sách yêu thích mới vào CSDL (chỉ chủ trang đăng nhập mới được làm).
 * Cách thử: Xem danh sách sách và đánh giá sao; đăng nhập tài khoản sinh@ued.vn / matkhau123 để thêm đánh giá mới.
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
        $_SESSION['flash'] = 'Đã xóa sách khỏi danh sách yêu thích!';
        header('Location: gioithieu.php');
        exit;
    }

    $spId = filter_var($_POST['san_pham_id'] ?? '', FILTER_VALIDATE_INT);
    $danhGia = filter_var($_POST['danh_gia'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    $camNhan = trim(chuoi($_POST['cam_nhan'] ?? ''));

    if (!$spId) {
        $loi['san_pham_id'] = 'Vui lòng chọn một cuốn sách';
    }
    if (!$danhGia) {
        $loi['danh_gia'] = 'Đánh giá từ 1 đến 5 sao';
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

$thongBao = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$tieuDe = 'Trang cá nhân - Nguyễn Thị Sinh';
$trang = 'gioi-thieu';

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 900px; margin: 30px auto;">
  <!-- Thông tin cá nhân -->
  <section style="display: flex; gap: 30px; align-items: center; background: #ffffff; padding: 28px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 30px; flex-wrap: wrap; box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05); position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #ec4899 0%, #a855f7 100%);"></div>
    <img src="<?= $goc ?>images/sinh-avatar.png" alt="Ảnh Nguyễn Thị Sinh" width="130" height="130" style="border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.15);">
    <div style="flex: 1; min-width: 280px;">
      <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 6px;">
        <h1 style="margin: 0; color: #0f2b5c; font-size: 1.85rem; font-weight: 800;">Nguyễn Thị Sinh</h1>
        <span class="huy-hieu huy-hieu--thong-tin" style="font-size: 0.75rem; padding: 4px 10px;">Thành viên</span>
      </div>
      <p style="margin: 4px 0; color: #64748b; font-size: 0.95rem;">Mã sinh viên: <strong>3120223169</strong> &bull; Lớp: <strong>24CNTT3</strong></p>
      <div style="margin: 8px 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <a href="https://github.com/chingg2005" target="_blank" rel="noopener noreferrer" class="github-pill">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
          <span>chingg2005</span>
        </a>
        <a href="../../gioi-thieu.php" class="nut nut--vien" style="padding: 4px 12px; font-size: 0.85rem; border-radius: 9999px;">&larr; Trang giới thiệu nhóm</a>
      </div>
      <p style="margin: 4px 0; color: #475569; font-size: 0.9rem;">
        <strong>Trách nhiệm:</strong> Trang chi tiết chi-tiet.php, cookie da_xem, đăng ký/đăng nhập và phân quyền quản trị sản phẩm.
      </p>
      <?php if ($laChuTrang): ?>
        <p style="margin: 8px 0 0 0; color: #16a34a; font-weight: bold; font-size: 0.9rem;">✓ Bạn đang đăng nhập với tư cách Chủ trang (sinh@ued.vn)</p>
      <?php else: ?>
        <p style="margin: 8px 0 0 0; font-size: 0.85rem; color: #64748b;">(Đăng nhập tài khoản <code>sinh@ued.vn</code> / <code>matkhau123</code> để kích hoạt quyền xóa lưu bút)</p>
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
          <li><strong>Biện pháp 2:</strong> Bảo vệ phiên làm việc (Session Security): HttpOnly, SameSite=Lax, hủy phiên 30 phút, tái tạo session ID chống Fixation.</li>
          <li><strong>Biện pháp 3:</strong> Bộ tiêu đề bảo mật HTTP (CSP script-src 'self', frame-ancestors 'none', nosniff, gỡ bỏ X-Powered-By).</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #ea580c;">
        <h4 style="margin: 0 0 8px 0; color: #ea580c;">2. Nội dung biên soạn báo cáo</h4>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Phần A:</strong> Phân tích cơ chế bảo mật Session và giải trình bảng tiêu đề HTTP trong Bảng 1.</li>
          <li><strong>Phần B:</strong> Báo cáo bảo mật trang cá nhân <code>nguyenthisinh.php</code>, kiểm tra quyền chủ trang khi xóa đánh giá <code>tv_sach_yeu_thich</code>, 5 ca kiểm thử thực nghiệm.</li>
          <li><strong>Phần C:</strong> Biên soạn giải đáp chuyên sâu <strong>Câu C.2</strong> (HttpOnly vs XSS, và mô hình 3 lớp phòng vệ: e(), HttpOnly, CSP).</li>
        </ul>
      </div>
    </div>

    <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.9rem; color: #334155; line-height: 1.6;">
      <strong style="color: #1e3b6d;">3. Thực thi kiểm thử &amp; Minh chứng chất lượng:</strong>
      <ul style="margin: 6px 0 0 0; padding-left: 18px;">
        <li>Thực hiện kiểm tra tiêu đề Response Headers qua Network tab và kiểm tra thuộc tính HttpOnly/SameSite của PHPSESSID qua Application tab.</li>
        <li>Chụp và chèn ảnh minh chứng <strong>Hình 2</strong> (Headers) và <strong>Hình 3</strong> (Cookies) vào báo cáo đồ án.</li>
        <li>Commit Git phụ trách: <code>395aa4c</code> (Biện pháp 2 &amp; 3) và <code>5d868b4</code> (Phần B cá nhân).</li>
      </ul>
    </div>
  </section>

  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 25px;">
      <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <!-- Chức năng CSDL 2: Thêm đánh giá sách yêu thích (chỉ chủ trang) -->
  <?php if ($laChuTrang): ?>
    <section style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
      <h2 style="margin-top: 0; color: #1e3b6d; font-size: 1.25rem;">+ Thêm sách yêu thích &amp; Đánh giá sao (Bảng tv_sach_yeu_thich)</h2>
      <form method="POST" action="gioithieu.php">
        <?= csrfField() ?>
        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 12px;">
          <div style="flex: 2; min-width: 240px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">Chọn sách *</label>
            <select name="san_pham_id" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px;box-sizing:border-box;">
              <option value="">-- Chọn một cuốn sách trong CSDL --</option>
              <?php foreach ($dsTatCaSach as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['ten']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="flex: 1; min-width: 140px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">Đánh giá sao *</label>
            <select name="danh_gia" style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px;box-sizing:border-box;">
              <option value="5">⭐⭐⭐⭐⭐ (5 sao)</option>
              <option value="4">⭐⭐⭐⭐ (4 sao)</option>
              <option value="3">⭐⭐⭐ (3 sao)</option>
              <option value="2">⭐⭐ (2 sao)</option>
              <option value="1">⭐ (1 sao)</option>
            </select>
          </div>
        </div>
        <div style="margin-bottom: 15px;">
          <label style="display:block;font-weight:bold;margin-bottom:4px;">Cảm nhận cá nhân</label>
          <textarea name="cam_nhan" rows="2" placeholder="Cảm nhận của bạn về cuốn sách này..." style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px;box-sizing:border-box;"></textarea>
        </div>
        <button type="submit" class="nut nut--chinh">Lưu vào danh sách yêu thích</button>
      </form>
    </section>
  <?php endif; ?>

  <!-- Chức năng CSDL 1: Danh sách sách yêu thích JOIN với san_pham -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
    <h2 style="margin: 0 0 20px 0; color: #1e3b6d;">📚 Tủ sách yêu thích &amp; Đánh giá chuyên môn</h2>

    <div style="display: flex; flex-direction: column; gap: 16px;">
      <?php foreach ($dsYeuThich as $yt): ?>
        <article style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 18px; display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
          <img src="<?= $goc . e(anhSach($yt['anh'])) ?>" alt="Bìa" width="70" height="90" style="border-radius: 4px; object-fit: cover;">
          <div style="flex: 1; min-width: 250px;">
            <h3 style="margin: 0 0 6px 0; font-size: 1.1rem;">
              <a href="<?= $goc ?>chi-tiet.php?id=<?= $yt['san_pham_id'] ?>" style="color: #1e40af; text-decoration: none;">
                <?= e($yt['ten_sach']) ?>
              </a>
            </h3>
            <div style="color: #d97706; font-size: 1.1rem; margin-bottom: 8px;">
              <?= str_repeat('★', (int)$yt['danh_gia']) . str_repeat('☆', 5 - (int)$yt['danh_gia']) ?>
              <span style="font-size: 0.85rem; color: #64748b; margin-left: 6px;">(<?= $yt['danh_gia'] ?> / 5 sao)</span>
            </div>
            <p style="margin: 0 0 8px 0; color: #334155; line-height: 1.5; font-style: italic;">
              "<?= e($yt['cam_nhan'] ?? 'Chưa có ghi chú cảm nhận.') ?>"
            </p>
            <small style="color: #94a3b8;">Đã thêm: <?= e($yt['ngay_them']) ?></small>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
