<?php
/**
 * danh-sach.php - Danh mục sách: tìm kiếm, lọc theo danh mục & giá, sắp xếp, phân trang
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 2 (Bảng 1): LIKE + addcslashes, WHERE động, ORDER BY whitelist, COUNT(*) + LIMIT ? OFFSET ?, http_build_query
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham(db());
$duong = ['options' => ['min_range' => 1]];

// Lọc dữ liệu an toàn từ $_GET (sử dụng chuoi() chống lỗi TypeError khi nhận mảng ?q[]=ao)
$loc = [
    'q'        => trim(chuoi($_GET['q'] ?? '')),
    'dm'       => filter_var($_GET['dm'] ?? '', FILTER_VALIDATE_INT, $duong),
    'giaToiDa' => filter_var($_GET['gia'] ?? '', FILTER_VALIDATE_INT, $duong),
];

$kieuSx = [
    'moi'      => 'Mới nhất',
    'gia-tang' => 'Giá: Thấp đến Cao',
    'gia-giam' => 'Giá: Cao đến Thấp',
    'ten'      => 'Tên A–Z'
];

$sx = chuoi($_GET['sx'] ?? 'moi');
if (!array_key_exists($sx, $kieuSx)) {
    $sx = 'moi';
}

$moiTrang = 8;
$tong = $kho->dem($loc);
$soTrang = max(1, (int) ceil($tong / $moiTrang));

$hienTai = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'default' => 1]
]);
if ($hienTai === false || $hienTai < 1) {
    $hienTai = 1;
}
$hienTai = min($hienTai, $soTrang); // ?trang=99999 -> tự động về trang cuối

$ds = $kho->trang($loc, $sx, $moiTrang, ($hienTai - 1) * $moiTrang);

// Danh mục lấy từ CSDL MySQL qua PDO
$dsDanhMuc = db()->query('SELECT id, ten FROM danh_muc ORDER BY ten')->fetchAll();

/**
 * Hàm tạo liên kết chuyển trang bảo toàn mọi tham số lọc hiện tại
 */
function lienKetTrang(int $so): string
{
    $params = array_filter($_GET, fn($v) => is_scalar($v));
    return '?' . http_build_query(array_merge($params, ['trang' => $so]));
}

$tieuDe = 'Danh mục sách';
$trang = 'danh-sach';
$moTaTrang = 'Khám phá hơn 30 đầu giáo trình CNTT, Kinh tế, Kỹ năng sống và Văn học tại BookNest UED.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <h1>Danh mục giáo trình &amp; Sách sinh viên</h1>
  <p>Toàn bộ sách và giáo trình được quản lý trong hệ quản trị CSDL MySQL, hỗ trợ tìm kiếm phân trang tức thời.</p>

  <!-- Biểu mẫu tìm kiếm, lọc và sắp xếp (GET - Chia sẻ được liên kết) -->
  <section class="bo-loc-container" style="background: #f1f5f9; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
    <form method="GET" action="danh-sach.php" id="form-bo-loc" class="form-bo-loc" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
      
      <!-- Ô tìm kiếm từ khóa (có gắn lắng nghe cho tìm kiếm tức thời AJAX/Fetch) -->
      <div style="flex: 2; min-width: 220px; position: relative;">
        <label for="o-tim-kiem" style="display: block; font-weight: bold; margin-bottom: 5px;">Từ khóa tìm kiếm:</label>
        <input type="text" id="o-tim-kiem" name="q" value="<?= e($loc['q']) ?>" placeholder="Nhập tên sách (ví dụ: PHP, O'Reilly, Levi's...)" autocomplete="off" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        
        <!-- Khối kết quả gợi ý tức thời (Fetch API & Debounce) -->
        <div id="ket-qua-tuc-thoi" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 0 0 6px 6px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 50; max-height: 280px; overflow-y: auto;"></div>
      </div>

      <!-- Lọc theo danh mục -->
      <div style="flex: 1.5; min-width: 180px;">
        <label for="loc-danh-muc" style="display: block; font-weight: bold; margin-bottom: 5px;">Danh mục sách:</label>
        <select id="loc-danh-muc" name="dm" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
          <option value="">-- Tất cả danh mục --</option>
          <?php foreach ($dsDanhMuc as $dm): ?>
            <option value="<?= $dm['id'] ?>" <?= $loc['dm'] === (int)$dm['id'] ? 'selected' : '' ?>>
              <?= e($dm['ten']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Lọc theo giá tối đa -->
      <div style="flex: 1; min-width: 140px;">
        <label for="loc-gia" style="display: block; font-weight: bold; margin-bottom: 5px;">Giá tối đa (VNĐ):</label>
        <input type="number" id="loc-gia" name="gia" value="<?= e($loc['giaToiDa'] ?: '') ?>" placeholder="Ví dụ: 150000" step="5000" min="1000" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
      </div>

      <!-- Sắp xếp theo whitelist -->
      <div style="flex: 1; min-width: 150px;">
        <label for="sap-xep" style="display: block; font-weight: bold; margin-bottom: 5px;">Sắp xếp theo:</label>
        <select id="sap-xep" name="sx" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
          <?php foreach ($kieuSx as $maSx => $tenSx): ?>
            <option value="<?= e($maSx) ?>" <?= $sx === $maSx ? 'selected' : '' ?>>
              <?= e($tenSx) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Nút submit và reset -->
      <div style="display: flex; gap: 8px;">
        <button type="submit" class="nut nut--chinh" style="padding: 9px 18px;">Lọc sách</button>
        <?php if ($loc['q'] !== '' || $loc['dm'] || $loc['giaToiDa'] || $sx !== 'moi'): ?>
          <a href="danh-sach.php" class="nut nut--vien" style="padding: 9px 15px; text-decoration: none;">Đặt lại</a>
        <?php endif; ?>
      </div>
    </form>
  </section>

  <!-- Kết quả thống kê -->
  <div style="margin-bottom: 20px; color: #475569; font-size: 0.95rem;">
    Tìm thấy <strong><?= $tong ?></strong> đầu sách phù hợp (Hiển thị trang <?= $hienTai ?> / <?= $soTrang ?>)
  </div>

  <!-- Danh sách hiển thị sản phẩm -->
  <?php if ($tong === 0): ?>
    <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: 8px; border: 1px dashed #cbd5e1;">
      <p style="font-size: 3rem; margin: 0 0 10px 0;">🔍</p>
      <h2>Không tìm thấy cuốn sách nào phù hợp</h2>
      <p style="color: #64748b; max-width: 480px; margin: 10px auto 20px auto;">
        Rất tiếc, không có đầu sách nào khớp với tiêu chí tìm kiếm hoặc bộ lọc danh mục của bạn. Vui lòng thử lại với từ khóa khác hoặc bấm "Đặt lại" để xem toàn bộ danh mục.
      </p>
      <a href="danh-sach.php" class="nut nut--chinh">Xem lại toàn bộ sách</a>
    </div>
  <?php else: ?>
    <div class="luoi-the" id="danh-sach-sach">
      <?php foreach ($ds as $sp): ?>
        <article class="the">
          <figure class="the__hinh-anh">
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>
          <div class="the__than">
            <span class="the__the-loai" style="font-size:0.8rem;color:#1e40af;font-weight:bold;"><?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?></span>
            <h3 class="the__tieu-de" style="font-size:1.05rem;margin:0.25rem 0;"><?= e($sp->ten) ?></h3>
            <p class="the__gia" style="margin:0.4rem 0;"><strong><?= vnd($sp->gia) ?></strong></p>
            <p class="the__ton-kho" style="font-size:0.85rem;color:<?= $sp->tonKho > 0 ? '#16a34a' : '#dc2626' ?>;">
              <?= $sp->tonKho > 0 ? 'Còn hàng (' . $sp->tonKho . ' cuốn)' : 'Hết hàng' ?>
            </p>
            <!-- 4 NÚT THAO TÁC: ĐẶT MUA, THÊM GIỎ, YÊU THÍCH, CHI TIẾT (2x2 GRID ĐỐI XỨNG) -->
            <div class="the__chan">
              <!-- 1. Nút Đặt mua ngay (ปุ่มกดสั่งชื้อ) -->
              <?php if ($sp->tonKho > 0): ?>
                <form method="POST" action="gio-hang.php">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="mua_ngay">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="soLuong" value="1">
                  <button type="submit" class="nut nut--nhan btn-mua-ngay" title="Đặt mua sách ngay">⚡ Mua ngay</button>
                </form>
              <?php else: ?>
                <button type="button" disabled class="nut nut--vien" style="opacity:0.6;cursor:not-allowed;" title="Sách tạm thời hết hàng">⚡ Hết hàng</button>
              <?php endif; ?>

              <!-- 2. Nút Thêm vào giỏ hàng (ปุ่มตะกร้า) -->
              <?php if ($sp->tonKho > 0): ?>
                <form method="POST" action="gio-hang.php" class="form-them-nhanh">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="them">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="soLuong" value="1">
                  <button type="submit" class="nut nut--chinh btn-them-nhanh" title="Thêm vào giỏ hàng">🛒 Thêm giỏ</button>
                </form>
              <?php else: ?>
                <button type="button" disabled class="nut nut--vien" style="opacity:0.6;cursor:not-allowed;" title="Sách tạm thời hết hàng">🛒 Hết hàng</button>
              <?php endif; ?>

              <!-- 3. Nút Danh sách yêu thích (ปุ่มรายการโปรด) -->
              <form method="POST" action="yeu-thich.php" class="form-yeu-thich">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $sp->id ?>">
                <button type="submit" class="nut nut--vien btn-yeu-thich <?= daYeuThich($sp->id) ? 'da-thich' : '' ?>" title="<?= daYeuThich($sp->id) ? 'Đã yêu thích (Bấm để bỏ)' : 'Thêm vào yêu thích' ?>">
                  <?= daYeuThich($sp->id) ? '❤️ Đã thích' : '🤍 Yêu thích' ?>
                </button>
              </form>

              <!-- 4. Nút Xem chi tiết (ปุ่มดูรายละเอียด) -->
              <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut nut--vien" title="Xem chi tiết nội dung cuốn sách">🔍 Chi tiết</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Phân trang (Pagination) bảo toàn bộ lọc -->
    <?php if ($soTrang > 1): ?>
      <nav aria-label="Phân trang sách" style="margin-top: 40px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
        <?php if ($hienTai > 1): ?>
          <a href="<?= e(lienKetTrang($hienTai - 1)) ?>" class="nut nut--vien" style="padding: 6px 12px;">&laquo; Trang trước</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $soTrang; $i++): ?>
          <?php if ($i === $hienTai): ?>
            <span class="nut nut--chinh" style="padding: 6px 14px; font-weight: bold; pointer-events: none;" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a href="<?= e(lienKetTrang($i)) ?>" class="nut nut--vien" style="padding: 6px 14px;"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($hienTai < $soTrang): ?>
          <a href="<?= e(lienKetTrang($hienTai + 1)) ?>" class="nut nut--vien" style="padding: 6px 12px;">Trang sau &raquo;</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>

</main>

<script src="js/tim-kiem.js"></script>
<script src="js/gio-hang.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
