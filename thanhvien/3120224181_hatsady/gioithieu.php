<?php
/**
 * thanhvien/3120224181_hatsady/gioithieu.php - Trang cá nhân của Phommaket Hatsady
 * Mã sinh viên: 3120224181 - Lớp: 24CNTT3
 * Chức năng Phần B:
 *   1. Danh sách dự án CNTT cá nhân từ bảng tv_du_an: tìm kiếm từ khóa, lọc theo công nghệ qua prepared statements.
 *   2. Biểu mẫu thêm dự án mới vào CSDL MySQL (chỉ chủ trang đăng nhập mới được thực hiện).
 * Cách thử: Gõ từ khóa tìm kiếm dự án; đăng nhập hatsady@ued.vn / matkhau123 để thêm dự án mới.
 */

require_once __DIR__ . '/../../inc/config.php';

const MSSV = '3120224181';
$goc = '../../';
$db = db();

$st = $db->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$st->execute([MSSV]);
$tv = $st->fetch();

$u = nguoiDung();
$laChuTrang = ($u['id'] ?? 0) === ($tv['nguoi_dung_id'] ?? -1);

$loi = [];

// Xử lý thêm dự án mới (chỉ chủ trang)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $laChuTrang) {
    $tenDuAn = trim($_POST['ten_du_an'] ?? '');
    $congNghe = trim($_POST['cong_nghe'] ?? '');
    $moTa = trim($_POST['mo_ta'] ?? '');
    $link = trim($_POST['link_github'] ?? '');

    if ($tenDuAn === '' || mb_strlen($tenDuAn) > 100) {
        $loi['ten_du_an'] = 'Tên dự án dài từ 1 đến 100 ký tự';
    }
    if ($congNghe === '' || mb_strlen($congNghe) > 100) {
        $loi['cong_nghe'] = 'Công nghệ sử dụng dài từ 1 đến 100 ký tự';
    }
    if ($moTa === '') {
        $loi['mo_ta'] = 'Vui lòng nhập mô tả tóm tắt dự án';
    }

    if (empty($loi)) {
        $stAdd = $db->prepare('INSERT INTO tv_du_an (mssv, ten_du_an, cong_nghe, mo_ta, link_github) VALUES (?, ?, ?, ?, ?)');
        $stAdd->execute([MSSV, $tenDuAn, $congNghe, $moTa, $link ?: null]);
        $_SESSION['flash'] = 'Thêm dự án mới thành công!';
        header('Location: gioithieu.php');
        exit;
    }
}

// Tìm kiếm và lọc dự án
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM tv_du_an WHERE mssv = ?';
$params = [MSSV];

if ($q !== '') {
    $sql .= ' AND (ten_du_an LIKE ? OR cong_nghe LIKE ?)';
    $params[] = '%' . addcslashes($q, '%_\\') . '%';
    $params[] = '%' . addcslashes($q, '%_\\') . '%';
}
$sql .= ' ORDER BY id DESC';

$stDs = $db->prepare($sql);
$stDs->execute($params);
$dsDuAn = $stDs->fetchAll();

$thongBao = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$tieuDe = 'Trang cá nhân - Phommaket Hatsady';
$trang = 'gioi-thieu';

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 900px; margin: 30px auto;">
  <section style="display: flex; gap: 30px; align-items: center; background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; flex-wrap: wrap;">
    <img src="<?= $goc ?>images/hatsady-avatar.png" alt="Ảnh Phommaket Hatsady" width="130" height="130" style="border-radius: 50%; object-fit: cover; border: 3px solid #1e3b6d;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d; font-size: 1.8rem;">Phommaket Hatsady</h1>
      <p style="margin: 5px 0; color: #64748b;">Mã sinh viên: <strong>3120224181</strong> | Lớp: <strong>24CNTT3</strong></p>
      <p style="margin: 5px 0;">Phụ trách Phần A: <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Danh mục sách &amp; Dịch vụ Giỏ hàng</span></p>
      <p style="margin: 5px 0; color: #475569;">Trách nhiệm: Lập trình chức năng lọc GET, phân trang danh mục, quản lý mảng giỏ hàng Session và REST Client.</p>
      <?php if ($laChuTrang): ?>
        <p style="margin: 8px 0 0 0; color: #16a34a; font-weight: bold;">✓ Bạn đang đăng nhập với tư cách Chủ trang (hatsady@ued.vn)</p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 25px;">
      <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <!-- Chức năng CSDL 2: Thêm dự án mới (chỉ chủ trang) -->
  <?php if ($laChuTrang): ?>
    <section style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
      <h2 style="margin-top: 0; color: #1e3b6d; font-size: 1.25rem;">+ Thêm dự án công nghệ mới (Bảng tv_du_an)</h2>
      <form method="POST" action="gioithieu.php">
        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 12px;">
          <div style="flex: 1; min-width: 220px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">Tên dự án *</label>
            <input type="text" name="ten_du_an" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px;box-sizing:border-box;">
          </div>
          <div style="flex: 1; min-width: 220px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">Công nghệ *</label>
            <input type="text" name="cong_nghe" placeholder="Ví dụ: PHP 8, MariaDB, Docker" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px;box-sizing:border-box;">
          </div>
        </div>
        <div style="margin-bottom: 12px;">
          <label style="display:block;font-weight:bold;margin-bottom:4px;">Mô tả dự án *</label>
          <textarea name="mo_ta" rows="2" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px;box-sizing:border-box;"></textarea>
        </div>
        <div style="margin-bottom: 15px;">
          <label style="display:block;font-weight:bold;margin-bottom:4px;">Link GitHub</label>
          <input type="url" name="link_github" placeholder="https://github.com/..." style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px;box-sizing:border-box;">
        </div>
        <button type="submit" class="nut nut--chinh">Lưu dự án vào CSDL</button>
      </form>
    </section>
  <?php endif; ?>

  <!-- Chức năng CSDL 1: Danh sách dự án kèm tìm kiếm -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
      <h2 style="margin: 0; color: #1e3b6d;">💻 Dự án cá nhân &amp; Đóng góp phần mềm</h2>
      <form method="GET" action="gioithieu.php" style="display: flex; gap: 6px;">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Tìm tên hoặc công nghệ..." style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px;">
        <button type="submit" class="nut nut--chinh" style="padding: 6px 12px;">Tìm</button>
        <?php if ($q !== ''): ?>
          <a href="gioithieu.php" class="nut nut--vien" style="padding: 6px 10px;">Đặt lại</a>
        <?php endif; ?>
      </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
      <?php foreach ($dsDuAn as $da): ?>
        <article style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <h3 style="margin: 0 0 8px 0; color: #1e40af; font-size: 1.1rem;"><?= e($da['ten_du_an']) ?></h3>
            <span style="display: inline-block; background: #e0f2fe; color: #0369a1; font-size: 0.8rem; padding: 2px 8px; border-radius: 4px; font-weight: bold; margin-bottom: 10px;">
              <?= e($da['cong_nghe']) ?>
            </span>
            <p style="margin: 0 0 15px 0; color: #334155; font-size: 0.9rem; line-height: 1.5;"><?= nl2br(e($da['mo_ta'])) ?></p>
          </div>
          <?php if (!empty($da['link_github'])): ?>
            <a href="<?= e($da['link_github']) ?>" target="_blank" rel="noopener" class="nut nut--vien" style="text-align: center; font-size: 0.85rem; padding: 6px;">
              Xem mã nguồn trên GitHub &rarr;
            </a>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
