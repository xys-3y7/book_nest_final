<?php
/**
 * them-san-pham.php - Thêm sách mới vào CSDL MySQL (Dành riêng cho Quản trị viên)
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 3 (Bảng 1): yeuCauQuyen('quan_tri'), kiemTraSanPham(), INSERT + lastInsertId, PRG + Flash
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/kiem-tra.php';

yeuCauQuyen('quan_tri');

$db = db();
$danhMucList = $db->query('SELECT id, ten FROM danh_muc ORDER BY ten')->fetchAll();
$idDanhMuc = array_column($danhMucList, 'id');

$du = [
    'ten'         => '',
    'gia'         => '',
    'ton_kho'     => '10',
    'danh_muc_id' => '',
    'mo_ta'       => '',
];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$du, $loi] = kiemTraSanPham($_POST, $idDanhMuc);

    $tenAnh = null;
    if (isset($_FILES['anh']) && $_FILES['anh']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['anh'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Lỗi trong quá trình tải ảnh bìa (Mã: ' . $file['error'] . ')';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $loi['anh'] = 'Ảnh bìa sách không vượt quá 2 MB';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $hopLe = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($hopLe[$mime])) {
                $loi['anh'] = 'Chỉ chấp nhận định dạng JPG, PNG hoặc WebP';
            } else {
                $ext = $hopLe[$mime];
                $tenAnh = 'book_' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (!is_dir(__DIR__ . '/uploads')) {
                    mkdir(__DIR__ . '/uploads', 0777, true);
                }
                move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $tenAnh);
            }
        }
    }

    if (empty($loi)) {
        try {
            $st = $db->prepare(
                'INSERT INTO san_pham (ten, gia, ton_kho, danh_muc_id, mo_ta, anh) 
                 VALUES (:ten, :gia, :ton_kho, :danh_muc_id, :mo_ta, :anh)'
            );
            $st->execute($du + ['anh' => $tenAnh]);
            $idMoi = $db->lastInsertId();

            $_SESSION['flash'] = "Thêm sách mới #$idMoi thành công!";
            header('Location: quan-tri.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $loi['he_thong'] = 'Lỗi ràng buộc toàn vẹn dữ liệu CSDL (Mã 23000).';
            } else {
                throw $e;
            }
        }
    }
}

$tieuDe = 'Thêm sách mới';
$trang = 'quan-tri';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 800px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1>Thêm đầu sách mới vào kho</h1>
    <a href="quan-tri.php" class="nut nut--vien">&larr; Quay lại quản trị</a>
  </div>

  <?php if (isset($loi['he_thong'])): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
      <?= e($loi['he_thong']) ?>
    </div>
  <?php endif; ?>

  <div style="background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0;">
    <form method="POST" action="them-san-pham.php" enctype="multipart/form-data" novalidate>
      <?= csrfField() ?>
      
      <!-- Tên sách -->
      <div style="margin-bottom: 20px;">
        <label for="ten" style="display: block; font-weight: bold; margin-bottom: 6px;">Tên giáo trình / Sách <span style="color:#dc2626;">*</span></label>
        <input type="text" id="ten" name="ten" value="<?= e($du['ten']) ?>" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['ten']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
        <?php if (isset($loi['ten'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['ten']) ?></p>
        <?php endif; ?>
      </div>

      <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
        <!-- Danh mục -->
        <div style="flex: 1; min-width: 220px;">
          <label for="danh_muc_id" style="display: block; font-weight: bold; margin-bottom: 6px;">Danh mục <span style="color:#dc2626;">*</span></label>
          <select id="danh_muc_id" name="danh_muc_id" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['danh_muc_id']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
            <option value="">-- Chọn danh mục --</option>
            <?php foreach ($danhMucList as $dm): ?>
              <option value="<?= $dm['id'] ?>" <?= (int)$du['danh_muc_id'] === (int)$dm['id'] ? 'selected' : '' ?>>
                <?= e($dm['ten']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($loi['danh_muc_id'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['danh_muc_id']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Đơn giá -->
        <div style="flex: 1; min-width: 160px;">
          <label for="gia" style="display: block; font-weight: bold; margin-bottom: 6px;">Đơn giá (VNĐ) <span style="color:#dc2626;">*</span></label>
          <input type="number" id="gia" name="gia" value="<?= e($du['gia']) ?>" placeholder="Từ 1000 trở lên" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['gia']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
          <?php if (isset($loi['gia'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['gia']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Tồn kho -->
        <div style="flex: 1; min-width: 140px;">
          <label for="ton_kho" style="display: block; font-weight: bold; margin-bottom: 6px;">Số lượng kho <span style="color:#dc2626;">*</span></label>
          <input type="number" id="ton_kho" name="ton_kho" value="<?= e($du['ton_kho']) ?>" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['ton_kho']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
          <?php if (isset($loi['ton_kho'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['ton_kho']) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Ảnh bìa sách -->
      <div style="margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">
        <label for="anh" style="display: block; font-weight: bold; margin-bottom: 6px;">Ảnh bìa sách (Tùy chọn, tối đa 2 MB):</label>
        <input type="file" id="anh" name="anh" accept="image/jpeg,image/png,image/webp">
        <?php if (isset($loi['anh'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['anh']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Mô tả tóm tắt -->
      <div style="margin-bottom: 25px;">
        <label for="mo_ta" style="display: block; font-weight: bold; margin-bottom: 6px;">Mô tả nội dung giáo trình:</label>
        <textarea id="mo_ta" name="mo_ta" rows="5" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['mo_ta']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;"><?= e($du['mo_ta']) ?></textarea>
        <?php if (isset($loi['mo_ta'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['mo_ta']) ?></p>
        <?php endif; ?>
      </div>

      <div style="display: flex; gap: 15px;">
        <button type="submit" class="nut nut--chinh" style="padding: 10px 24px;">Lưu cuốn sách</button>
        <a href="quan-tri.php" class="nut nut--vien" style="padding: 10px 20px;">Hủy bỏ</a>
      </div>
    </form>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
