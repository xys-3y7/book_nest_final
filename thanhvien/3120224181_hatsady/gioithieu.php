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

// Xử lý thêm hoặc xóa dự án (chỉ chủ trang có quyền)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$laChuTrang) {
        ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        exit('403 Forbidden — Bạn không có quyền thao tác trên trang này.');
    }

    $xoaId = filter_var($_POST['xoa_id'] ?? '', FILTER_VALIDATE_INT);
    if ($xoaId) {
        $stDel = $db->prepare('DELETE FROM tv_du_an WHERE id = ? AND mssv = ?');
        $stDel->execute([$xoaId, MSSV]);
        $_SESSION['flash'] = 'Đã xóa dự án thành công!';
        header('Location: gioithieu.php');
        exit;
    }

    $tenDuAn = trim(chuoi($_POST['ten_du_an'] ?? ''));
    $congNghe = trim(chuoi($_POST['cong_nghe'] ?? ''));
    $moTa = trim(chuoi($_POST['mo_ta'] ?? ''));
    $link = trim(chuoi($_POST['link_github'] ?? ''));

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

// Tìm kiếm và lọc dự án an toàn với chuoi()
$q = trim(chuoi($_GET['q'] ?? ''));
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
      <p style="margin: 5px 0;">GitHub: <a href="https://github.com/daimobile33076-eng" target="_blank" rel="noopener noreferrer" style="color: #0369a1; font-weight: bold;">@daimobile33076-eng (https://github.com/daimobile33076-eng)</a></p>
      <p style="margin: 5px 0;">Vai trò: <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Thành viên Nhóm 12</span></p>
      <p style="margin: 5px 0; color: #475569;">Trách nhiệm website: Trang danh mục sách danh-sach.php, bộ lọc GET an toàn, phân trang và dịch vụ Giỏ hàng.</p>
      <div style="margin-top: 10px;">
        <a href="../../gioi-thieu.php" class="nut nut--vien" style="padding: 4px 12px; font-size: 0.85rem;">&larr; Quay lại trang Giới thiệu nhóm</a>
      </div>
      <?php if ($laChuTrang): ?>
        <p style="margin: 8px 0 0 0; color: #16a34a; font-weight: bold;">✓ Bạn đang đăng nhập với tư cách Chủ trang (hatsady@ued.vn)</p>
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
          <li><strong>Biện pháp 5:</strong> Bảo vệ máy chủ Apache qua tập tin <code>.htaccess</code> và <code>uploads/.htaccess</code>.</li>
          <li>Chặn liệt kê thư mục (<code>Options -Indexes</code>).</li>
          <li>Chặn truy cập trực tiếp <code>.env</code>, <code>.git</code>, <code>.sql</code>, <code>.log</code>, <code>composer.json</code>, các thư mục nội bộ (HTTP 404/403).</li>
          <li>Cấm thực thi mã PHP trong <code>uploads/</code> (<code>SetHandler None</code>), thêm <code>nosniff</code>.</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #ea580c;">
        <h4 style="margin: 0 0 8px 0; color: #ea580c;">2. Nội dung biên soạn báo cáo</h4>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Phần A:</strong> Phân tích nguy cơ lộ lọt cấu hình máy chủ và giải trình giải pháp <code>.htaccess</code> trong Bảng 1.</li>
          <li><strong>Phần B:</strong> Báo cáo bảo mật trang cá nhân <code>phommaket.php</code>, kiểm tra quyền chủ trang khi Thêm/Xóa dự án <code>tv_du_an</code>, 5 ca kiểm thử thực nghiệm.</li>
          <li><strong>Phần C:</strong> Biên soạn giải đáp <strong>Câu C.3</strong> (.htaccess vs php -S, finfo_file và giải pháp kiến trúc Web Root).</li>
        </ul>
      </div>
    </div>

    <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.9rem; color: #334155; line-height: 1.6;">
      <strong style="color: #1e3b6d;">3. Thực thi kiểm thử &amp; Minh chứng chất lượng:</strong>
      <ul style="margin: 6px 0 0 0; padding-left: 18px;">
        <li>Thực hiện các ca kiểm thử bảo mật phủ định: cố tình tải tệp nhạy cảm <code>.env</code>, <code>.git/HEAD</code>, <code>database/schema.sql</code>, tệp PHP trong <code>uploads/</code>.</li>
        <li>Chụp và chèn ảnh minh chứng <strong>Hình 7</strong> trong báo cáo đồ án.</li>
        <li>Commit Git phụ trách: <code>ee32b9d</code> (Biện pháp 5) và <code>f1c6fde</code> (Phần B cá nhân).</li>
      </ul>
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
        <?= csrfField() ?>
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
