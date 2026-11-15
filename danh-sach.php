<?php
/**
 * danh-sach.php - Danh mục sách: tìm kiếm, lọc theo danh mục & giá, sắp xếp, phân trang
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web (CSRF Protection, XSS Escaping, Parameterized PDO Queries)
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham(db());
$duong = ['options' => ['min_range' => 1]];

// Lọc dữ liệu an toàn từ $_GET
$loc = [
    'q'         => trim($_GET['q'] ?? $_GET['tu_khoa'] ?? ''),
    'dm'        => filter_var($_GET['dm'] ?? '', FILTER_VALIDATE_INT, $duong),
    'giaToiDa'  => filter_var($_GET['gia'] ?? '', FILTER_VALIDATE_INT, $duong),
    'chuSachId' => filter_var($_GET['chu_sach'] ?? '', FILTER_VALIDATE_INT, $duong),
];

$tenChuSachLoc = null;
if (!empty($loc['chuSachId'])) {
    $stCs = db()->prepare('SELECT ho_ten FROM nguoi_dung WHERE id = ?');
    $stCs->execute([$loc['chuSachId']]);
    $tenChuSachLoc = $stCs->fetchColumn() ?: null;
}

$kieuSx = [
    'moi'      => 'Newest',
    'ban-chay' => 'Popularity (Best-Selling)',
    'gia-tang' => 'Price: Low to High',
    'gia-giam' => 'Price: High to Low',
    'ten'      => 'Title A–Z'
];

$sx = $_GET['sx'] ?? 'moi';
if (!array_key_exists($sx, $kieuSx)) {
    $sx = 'moi';
}

// Mỗi trang 10 cuốn (vừa vặn 2 hàng x 5 cột = 10 cuốn đầy đủ, không bị khuyết ô)
$moiTrang = 10;
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
$dsDanhMuc = db()->query('SELECT id, ten, ten_en FROM danh_muc ORDER BY ten')->fetchAll();

/**
 * Hàm tạo liên kết chuyển trang bảo toàn mọi tham số lọc hiện tại
 */
function lienKetTrang(int $so): string
{
    return '?' . http_build_query(array_merge($_GET, ['trang' => $so]));
}

$tieuDe = 'Textbook Catalog';
$trang = 'danh-sach';
$moTaTrang = 'Explore over 30+ official Computer Science, Economics, and Life Skills textbooks at BookNest UED.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <!-- Breadcrumb & Header tiêu đề -->
  <div style="margin-bottom: 20px;">
    <div style="font-size: 0.82rem; color: #6b7280; margin-bottom: 8px;">
      <a href="index.php" style="color: #6b7280;">🏠 Home</a> &rsaquo; 
      <span style="color: #111827; font-weight: 700;">Textbook Catalog</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 10px;">
      <div>
        <h1 style="font-size: 1.85rem; font-weight: 800; color: #111827; margin: 0 0 6px 0; letter-spacing: -0.02em;">
          📚 UED Textbook &amp; Student Book Repository
        </h1>
        <p style="margin: 0; color: #6b7280; font-size: 0.92rem;">
          Browse all academic textbooks managed securely on MySQL database, supporting paginated search and instant purchase.
        </p>
      </div>
      <div style="background: #ebf5ff; color: #1a56db; font-size: 0.8rem; font-weight: 800; padding: 4px 12px; border-radius: 9999px; border: 1px solid #c3ddfd;">
        33+ 3NF STANDARD BOOKS
      </div>
    </div>
  </div>

  <!-- Các thẻ nhanh danh mục (Quick Category Chips) -->
  <div class="danh-muc-chips-wrap">
    <a href="danh-sach.php" class="chip-danhmuc <?= empty($loc['dm']) ? 'active' : '' ?>">
      <span>✨</span> All Textbooks
    </a>
    <?php foreach ($dsDanhMuc as $dm): ?>
      <a href="danh-sach.php?dm=<?= $dm['id'] ?>" class="chip-danhmuc <?= $loc['dm'] === (int)$dm['id'] ? 'active' : '' ?>">
        <span>📁</span> <?= e(tenDanhMuc($dm)) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- BỘ LỌC TÌM KIẾM CHUẨN SÀN THƯƠNG MẠI ĐIỆN TỬ -->
  <section class="bo-loc-card-tmdt">
    <div class="bo-loc-card-tmdt__header">
      <h3 class="bo-loc-card-tmdt__title">
        <span>🔎</span> Search &amp; Filter Textbooks
      </h3>
      <?php if ($loc['q'] !== '' || $loc['dm'] || $loc['giaToiDa'] || $sx !== 'moi'): ?>
        <a href="danh-sach.php" class="bo-loc-card-tmdt__reset">
          <span>🔄</span> Reset Filter
        </a>
      <?php endif; ?>
    </div>

    <form method="GET" action="danh-sach.php" id="form-bo-loc">
      <div class="bo-loc-grid-tmdt">
        
        <!-- 1. Từ khóa tìm kiếm -->
        <div class="bo-loc-group" style="position: relative;">
          <label for="o-tim-kiem" class="bo-loc-label">
            <span>🔍</span> Book Title / Author:
          </label>
          <div class="bo-loc-input-wrap">
            <span class="bo-loc-input-icon">📖</span>
            <input type="text" id="o-tim-kiem" name="q" value="<?= e($loc['q']) ?>" placeholder="e.g. PHP, Web, AI, Python..." class="bo-loc-input" autocomplete="off">
          </div>
          <!-- Gợi ý tức thời AJAX/Fetch -->
          <div id="ket-qua-tuc-thoi" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 50; max-height: 280px; overflow-y: auto;"></div>
        </div>

        <!-- 2. Lọc theo danh mục -->
        <div class="bo-loc-group">
          <label for="loc-danh-muc" class="bo-loc-label">
            <span>📁</span> Category:
          </label>
          <select id="loc-danh-muc" name="dm" class="bo-loc-select">
            <option value="">-- All Categories --</option>
            <?php foreach ($dsDanhMuc as $dm): ?>
              <option value="<?= $dm['id'] ?>" <?= $loc['dm'] === (int)$dm['id'] ? 'selected' : '' ?>>
                <?= e(tenDanhMuc($dm)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 3. Giá tối đa -->
        <div class="bo-loc-group">
          <label for="loc-gia" class="bo-loc-label">
            <span>💰</span> Max Price (VNĐ):
          </label>
          <input type="number" id="loc-gia" name="gia" value="<?= e($loc['giaToiDa'] ?: '') ?>" placeholder="e.g. 150000" step="5000" min="1000" class="bo-loc-input bo-loc-input--no-icon">
        </div>

        <!-- 4. Sắp xếp -->
        <div class="bo-loc-group">
          <label for="sap-xep" class="bo-loc-label">
            <span>⚡</span> Sort by:
          </label>
          <select id="sap-xep" name="sx" class="bo-loc-select">
            <?php foreach ($kieuSx as $maSx => $tenSx): ?>
              <option value="<?= e($maSx) ?>" <?= $sx === $maSx ? 'selected' : '' ?>>
                <?= e($tenSx) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 5. Nút submit & Reset -->
        <div class="bo-loc-buttons">
          <button type="submit" class="btn-loc-submit">
            <span>🔍</span> Filter Books
          </button>
          <?php if ($loc['q'] !== '' || $loc['dm'] || $loc['giaToiDa'] || $sx !== 'moi'): ?>
            <a href="danh-sach.php" class="btn-loc-reset" title="Reset filter">
              <span>✕</span>
            </a>
          <?php endif; ?>
        </div>

      </div>
    </form>
  </section>

  <!-- Thanh thống kê kết quả -->
  <?php if ($tenChuSachLoc): ?>
    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%); border: 1.5px solid #fcd34d; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 1.6rem;">🏪</span>
        <div>
          <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #b45309;">Quầy sách đối tác (Book Owner)</div>
          <div style="font-size: 1.1rem; font-weight: 800; color: #78350f;"><?= e($tenChuSachLoc) ?></div>
        </div>
      </div>
      <a href="danh-sach.php" style="background: #ffffff; color: #78350f; border: 1px solid #fcd34d; padding: 6px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 700; text-decoration: none;">
        ✕ View all books
      </a>
    </div>
  <?php endif; ?>

  <div class="thong-ke-tmdt">
    <div>
      Found <strong style="color: #1a56db; font-size: 1rem;"><?= $tong ?></strong> textbooks matching criteria
      <?php if (!empty($loc['q'])): ?>
        for keyword "<em><?= e($loc['q']) ?></em>"
      <?php endif; ?>
    </div>
    <div>Page <strong><?= $hienTai ?></strong> / <?= $soTrang ?></div>
  </div>

  <!-- Danh sách hiển thị sản phẩm -->
  <?php if ($tong === 0): ?>
    <div style="text-align: center; padding: 70px 20px; background: #ffffff; border-radius: 16px; border: 1.5px dashed #cbd5e1; box-shadow: var(--shadow-card);">
      <p style="font-size: 3.5rem; margin: 0 0 12px 0;">📚</p>
      <h2 style="justify-content: center; margin-bottom: 10px;">No textbooks available on shelves</h2>
      <p style="color: #64748b; max-width: 520px; margin: 10px auto 24px auto; line-height: 1.6;">
        All textbook inventory has been cleared. Sign in to your <strong>Seller</strong> account to list and sell your textbooks!
      </p>
      <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="them-san-pham.php" class="nut nut--chinh" style="padding: 10px 24px; text-decoration: none;">➕ List Textbook Now (Seller) &rarr;</a>
        <a href="index.php" class="nut nut--vien" style="padding: 10px 20px; text-decoration: none;">&larr; Back to Home</a>
      </div>
    </div>
  <?php else: ?>
    <div class="grid-books-tmdt" id="danh-sach-sach">
      <?php foreach ($ds as $sp): 
        $daThich = laYeuThich((int)$sp->id);
        $giaGoc = round($sp->gia * 1.25, -3);
      ?>
        <article class="the" data-id="<?= $sp->id ?>">
          <figure class="the__hinh-anh">
            <span class="the__the-loai-badge" style="background:#1a56db;"><?= e($sp->getCategoryName()) ?></span>
            <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button" aria-label="Favorite <?= e($sp->getTitle()) ?>" title="<?= $daThich ? 'Remove favorite' : 'Add to favorites' ?>">
              <?= $daThich ? '❤️' : '🤍' ?>
            </button>
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Book cover <?= e($sp->getTitle()) ?>" width="175" height="225" loading="lazy">
          </figure>

          <div class="the__than">
            <span class="the__the-loai"><?= e($sp->getCategoryName()) ?></span>
            <h3 class="the__tieu-de">
              <a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->getTitle()) ?></a>
            </h3>
            <div style="font-size: 0.76rem; color: #64748b; margin-top: -4px; margin-bottom: 4px;">
              Author: <strong><?= e($sp->getAuthor()) ?></strong>
            </div>

            <div class="rating-stars-tmdt">
              <span class="stars">★★★★★</span>
              <span>(4.8) &bull; <?= rand(12, 70) ?> sold</span>
            </div>

            <div style="font-size: 0.76rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
              <span><?= $sp->chuSachId ? '🏪' : '👑' ?></span>
              <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;" title="<?= e($sp->tenChuSach ?: 'BookNest UED') ?>">
                <?= e($sp->tenChuSach ?: 'BookNest UED') ?>
              </span>
            </div>

            <div class="the__gia-row">
              <span class="the__gia"><?= vnd($sp->gia) ?></span>
              <span class="the__gia-goc"><?= vnd($giaGoc) ?></span>
            </div>

            <p class="the__ton-kho <?= $sp->tonKho > 0 ? 'the__ton-kho--con' : 'the__ton-kho--het' ?>">
              <?= $sp->tonKho > 0 ? '✓ ' . $sp->tonKho . ' copies left' : '⚠ Out of stock' ?>
            </p>

            <!-- 4 NÚT HÀNH ĐỘNG (CSRF PROTECTED) -->
            <div class="the__chan">
              <?php if ($sp->tonKho > 0): ?>
                <div class="the__chan-hang1">
                  <form method="POST" action="gio-hang.php" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="mua_ngay">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-mua-ngay">⚡ Buy Now</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Cart</button>
                  </form>
                </div>
              <?php else: ?>
                <div style="color:#e02424;font-size:0.82rem;font-weight:700;text-align:center;padding:4px 0;background:#fdf2f2;border-radius:4px;">Out of stock</div>
              <?php endif; ?>

              <div class="the__chan-hang2">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" class="btn-chi-tiet">🔍 Details</a>
                <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'danh-sach.php') ?>" class="nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                  <?= $daThich ? '❤️ Saved' : '🤍 Wishlist' ?>
                </a>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Phân trang (Pagination) bảo toàn bộ lọc -->
    <?php if ($soTrang > 1): ?>
      <nav aria-label="Book pagination" style="margin-top: 40px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
        <?php if ($hienTai > 1): ?>
          <a href="<?= e(lienKetTrang($hienTai - 1)) ?>" class="nut nut--vien" style="padding: 6px 12px; border:1px solid #cbd5e1; background:#fff; color:#374151;">&laquo; Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $soTrang; $i++): ?>
          <?php if ($i === $hienTai): ?>
            <span class="nut nut--chinh" style="padding: 6px 14px; font-weight: bold; pointer-events: none;" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a href="<?= e(lienKetTrang($i)) ?>" class="nut nut--vien" style="padding: 6px 14px; border:1px solid #cbd5e1; background:#fff; color:#374151;"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($hienTai < $soTrang): ?>
          <a href="<?= e(lienKetTrang($hienTai + 1)) ?>" class="nut nut--vien" style="padding: 6px 12px; border:1px solid #cbd5e1; background:#fff; color:#374151;">Next &raquo;</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>

</main>

<script src="js/tim-kiem.js"></script>
<script src="js/gio-hang.js?v=<?= filemtime(__DIR__ . '/js/gio-hang.js') ?>"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
