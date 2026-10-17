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

// Xử lý thêm sách yêu thích mới (chỉ chủ trang)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $laChuTrang) {
    $spId = filter_var($_POST['san_pham_id'] ?? '', FILTER_VALIDATE_INT);
    $danhGia = filter_var($_POST['danh_gia'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    $camNhan = trim($_POST['cam_nhan'] ?? '');

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
  <section style="display: flex; gap: 30px; align-items: center; background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; flex-wrap: wrap;">
    <img src="<?= $goc ?>images/sinh-avatar.png" alt="Ảnh Nguyễn Thị Sinh" width="130" height="130" style="border-radius: 50%; object-fit: cover; border: 3px solid #1e3b6d;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d; font-size: 1.8rem;">Nguyễn Thị Sinh</h1>
      <p style="margin: 5px 0; color: #64748b;">Mã sinh viên: <strong>3120223169</strong> | Lớp: <strong>24CNTT3</strong></p>
      <p style="margin: 5px 0;">Phụ trách Phần A: <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Trang Chi tiết sách &amp; Quản trị Sản phẩm</span></p>
      <p style="margin: 5px 0; color: #475569;">Trách nhiệm: Xử lý hiển thị sách chi tiết JOIN danh mục, sách liên quan, khối cookie đã xem và form thêm/sửa sách quản trị.</p>
      <?php if ($laChuTrang): ?>
        <p style="margin: 8px 0 0 0; color: #16a34a; font-weight: bold;">✓ Bạn đang đăng nhập với tư cách Chủ trang (sinh@ued.vn)</p>
      <?php endif; ?>
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
