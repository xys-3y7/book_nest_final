<?php
/**
 * chi-tiet.php - Trang chi tiết sản phẩm / sách theo tham số URL ?id=
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 1 (Bảng 1): Đọc chi tiết kèm tên danh mục qua JOIN và 4 mục cùng danh mục
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham(db());

// Kiểm tra tham số id: ép kiểm tra số nguyên nghiêm ngặt bằng filter_var
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$sp = $id ? $kho->timTheoId($id) : null;

// Nếu id thiếu, không phải số nguyên hoặc sản phẩm không tồn tại -> Trả về mã 404
if ($sp === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Ghi nhớ mục đã xem vào Cookie 'da_xem' (Tối đa 4 ID mới nhất đứng đầu)
// setcookie() gửi HTTP Header nên BẮT BUỘC phải gọi trước mọi output HTML
$cu  = array_map('intval', explode(',', $_COOKIE['da_xem'] ?? ''));
$moi = array_unique([$sp->id, ...array_filter($cu)]);
setcookie('da_xem', implode(',', array_slice($moi, 0, 4)), [
    'expires'  => time() + 30 * 24 * 3600, // 30 ngày
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);

$tieuDe = $sp->ten;
$trang = 'danh-sach';
$moTaTrang = 'Xem thông tin chi tiết giáo trình ' . $sp->ten . ' tại BookNest UED.';

// Lấy 4 đầu sách liên quan cùng danh mục qua KhoSanPham
$sachLienQuan = $kho->sanPhamLienQuan($sp->danhMucId, $sp->id, 4);

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <!-- Điều hướng đường dẫn (Breadcrumbs) -->
  <nav aria-label="Đường dẫn trang" style="margin-bottom: 20px; font-size: 0.95rem; color: #64748b;">
    <a href="index.php">Trang chủ</a> &gt; 
    <a href="danh-sach.php">Danh mục sách</a> &gt; 
    <span style="color: #1e293b; font-weight: bold;"><?= e($sp->ten) ?></span>
  </nav>

  <!-- Chi tiết sách -->
  <article class="chi-tiet-san-pham" style="display: flex; gap: 40px; flex-wrap: wrap; margin-bottom: 50px; background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
    
    <!-- Ảnh bìa sách -->
    <div style="flex: 1; min-width: 280px; text-align: center;">
      <figure style="margin: 0;">
        <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" style="max-width: 100%; height: auto; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
      </figure>
      <p style="margin-top: 15px; font-weight: bold; color: #1e40af;">
        Danh mục: <?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?>
      </p>
    </div>

    <!-- Thông tin và Đặt mua -->
    <div style="flex: 2; min-width: 300px;">
      <h1 style="margin-top: 0; color: #0f172a; font-size: 1.7rem;"><?= e($sp->ten) ?></h1>
      
      <div style="font-size: 1.6rem; color: #dc2626; font-weight: bold; margin: 15px 0;">
        <?= vnd($sp->gia) ?>
      </div>

      <div style="background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <p style="margin: 6px 0;"><strong>Mã sản phẩm:</strong> #<?= $sp->id ?></p>
        <p style="margin: 6px 0;"><strong>Thể loại:</strong> <?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?></p>
        <p style="margin: 6px 0;"><strong>Tình trạng kho:</strong> 
          <?php if ($sp->tonKho > 0): ?>
            <span style="color: #16a34a; font-weight: bold;">Còn hàng (<?= $sp->tonKho ?> cuốn tại kho UED)</span>
          <?php else: ?>
            <span style="color: #dc2626; font-weight: bold;">Hết hàng tạm thời</span>
          <?php endif; ?>
        </p>
      </div>

      <!-- Biểu mẫu Thêm vào giỏ hàng (Hỗ trợ cả POST thường khi tắt JS và fetch khi bật JS) -->
      <?php if ($sp->tonKho > 0): ?>
        <form method="POST" action="gio-hang.php" id="form-them-gio" class="form-them-gio" style="display: flex; gap: 15px; align-items: center; margin-bottom: 30px; flex-wrap: wrap;">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="them">
          <input type="hidden" name="id" value="<?= $sp->id ?>">
          
          <label for="so-luong-nhap" style="font-weight: bold;">Số lượng:</label>
          <input type="number" id="so-luong-nhap" name="soLuong" value="1" min="1" max="<?= min(99, $sp->tonKho) ?>" style="width: 70px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; text-align: center;">

          <button type="submit" id="btn-them-gio" class="nut nut--chinh" style="padding: 10px 24px; font-size: 1rem;">
            🛒 Thêm vào giỏ hàng
          </button>
          <span id="thong-bao-gio" style="color: #16a34a; font-weight: bold; margin-left: 10px;"></span>
        </form>
      <?php else: ?>
        <p style="color: #dc2626; font-style: italic; margin-bottom: 25px;">
          * Sách này hiện đã hết hàng, vui lòng liên hệ để đặt trước hoặc quay lại sau.
        </p>
      <?php endif; ?>

      <!-- Nội dung giới thiệu tóm tắt giáo trình -->
      <h2>Mô tả nội dung giáo trình</h2>
      <p style="line-height: 1.7; color: #334155;">
        <?= nl2br(e($sp->moTa ?? 'Chưa có mô tả chi tiết cho đầu sách này.')) ?>
      </p>
    </div>
  </article>

  <!-- Khối sách liên quan cùng chuyên ngành (Tối đa 4 cuốn) -->
  <?php if (!empty($sachLienQuan)): ?>
    <section style="margin-top: 40px;">
      <h2>Sách cùng danh mục bạn có thể quan tâm</h2>
      <div class="luoi-the">
        <?php foreach ($sachLienQuan as $lq): ?>
          <article class="the">
            <figure class="the__hinh-anh">
              <img src="<?= e(anhSach($lq->anh)) ?>" alt="Bìa sách <?= e($lq->ten) ?>" width="175" height="225" loading="lazy">
            </figure>
            <div class="the__than">
              <h3 class="the__tieu-de"><a href="chi-tiet.php?id=<?= $lq->id ?>"><?= e($lq->ten) ?></a></h3>
              <p class="the__gia"><?= vnd($lq->gia) ?></p>
              <div class="the__chan">
                <a href="chi-tiet.php?id=<?= $lq->id ?>" class="nut nut--vien">Xem chi tiết</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

<script src="js/gio-hang.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
