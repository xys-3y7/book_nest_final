<?php
/**
 * sua-san-pham.php - Chỉnh sửa thông tin sách trong CSDL MySQL
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 3 (Bảng 1): yeuCauQuyen('quan_tri'), kiemTraSanPham(), UPDATE, PRG + Flash
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/kiem-tra.php';

yeuCauQuyen('quan_tri');

$db = db();
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

// Đọc dữ liệu sách cũ từ CSDL để điền vào form
$st = $db->prepare('SELECT * FROM san_pham WHERE id = ?');
$st->execute([$id]);
$cu = $st->fetch();

if ($cu === false) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$danhMucList = $db->query('SELECT id, ten FROM danh_muc ORDER BY ten')->fetchAll();
$idDanhMuc = array_column($danhMucList, 'id');

$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$du, $loi] = kiemTraSanPham($_POST, $idDanhMuc);

    $tenAnh = $cu['anh']; // Mặc định giữ ảnh cũ
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
        $st = $db->prepare(
            'UPDATE san_pham 
             SET ten = :ten, gia = :gia, ton_kho = :ton_kho, danh_muc_id = :danh_muc_id, mo_ta = :mo_ta, anh = :anh 
             WHERE id = :id'
        );
        $st->execute($du + ['anh' => $tenAnh, 'id' => $id]);

        $_SESSION['flash'] = "Đã cập nhật sách #$id thành công!";
        header('Location: quan-tri.php');
        exit;
    }
}

// Điền lại form: nếu vừa gửi lỗi thì lấy $_POST, mới mở thì lấy dữ liệu cũ $cu
$f = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $cu;

$tieuDe = 'Sửa sách #' . $id;
$trang = 'quan-tri';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 800px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h1>Chỉnh sửa thông tin sách #<?= $id ?></h1>
    <a href="quan-tri.php" class="nut nut--vien">&larr; Quay lại quản trị</a>
  </div>

  <div style="background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0;">
    <form method="POST" action="sua-san-pham.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate>
      <?= csrfField() ?>
      
      <!-- Tên sách -->
      <div style="margin-bottom: 20px;">
        <label for="ten" style="display: block; font-weight: bold; margin-bottom: 6px;">Tên giáo trình / Sách <span style="color:#dc2626;">*</span></label>
        <input type="text" id="ten" name="ten" value="<?= e($f['ten'] ?? '') ?>" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['ten']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
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
              <option value="<?= $dm['id'] ?>" <?= (int)($f['danh_muc_id'] ?? 0) === (int)$dm['id'] ? 'selected' : '' ?>>
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
          <input type="number" id="gia" name="gia" value="<?= e($f['gia'] ?? '') ?>" placeholder="Từ 1000 trở lên" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['gia']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
          <?php if (isset($loi['gia'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['gia']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Tồn kho -->
        <div style="flex: 1; min-width: 140px;">
          <label for="ton_kho" style="display: block; font-weight: bold; margin-bottom: 6px;">Số lượng kho <span style="color:#dc2626;">*</span></label>
          <input type="number" id="ton_kho" name="ton_kho" value="<?= e($f['ton_kho'] ?? '') ?>" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['ton_kho']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
          <?php if (isset($loi['ton_kho'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['ton_kho']) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Ảnh bìa sách -->
      <div style="margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">
        <label for="anh" style="display: block; font-weight: bold; margin-bottom: 6px;">Thay ảnh bìa sách (Tùy chọn, tối đa 2 MB):</label>
        <?php if (!empty($cu['anh'])): ?>
          <div style="margin-bottom: 10px;">
            <img src="<?= e(anhSach($cu['anh'])) ?>" alt="Ảnh hiện tại" width="60" height="75" style="border-radius: 4px; object-fit: cover; vertical-align: middle;">
            <span style="font-size: 0.85rem; color: #64748b; margin-left: 10px;">Ảnh hiện tại: <?= e($cu['anh']) ?></span>
          </div>
        <?php endif; ?>
        <input type="file" id="anh" name="anh" accept="image/jpeg,image/png,image/webp">
        <?php if (isset($loi['anh'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['anh']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Mô tả tóm tắt -->
      <div style="margin-bottom: 25px;">
        <label for="mo_ta" style="display: block; font-weight: bold; margin-bottom: 6px;">Mô tả nội dung giáo trình:</label>
        <textarea id="mo_ta" name="mo_ta" rows="5" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['mo_ta']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;"><?= e($f['mo_ta'] ?? '') ?></textarea>
        <?php if (isset($loi['mo_ta'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['mo_ta']) ?></p>
        <?php endif; ?>
      </div>

      <div style="display: flex; gap: 15px;">
        <button type="submit" class="nut nut--chinh" style="padding: 10px 24px;">Lưu thay đổi</button>
        <a href="quan-tri.php" class="nut nut--vien" style="padding: 10px 20px;">Hủy bỏ</a>
      </div>
    </form>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
