<?php
/**
 * chi-tiet.php - Trang chi tiết sản phẩm / sách theo tham số URL ?id=
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web (CSRF Protection, HttpOnly Cookie, Input Validation)
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
$cu  = array_map('intval', explode(',', $_COOKIE['da_xem'] ?? ''));
$moi = array_unique([$sp->id, ...array_filter($cu)]);
setcookie('da_xem', implode(',', array_slice($moi, 0, 4)), [
    'expires'  => time() + 30 * 24 * 3600, // 30 ngày
    'path'     => '/',
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
  <nav aria-label="Đường dẫn trang" style="margin-bottom: 24px; font-size: 0.92rem; color: #64748b; display: flex; align-items: center; gap: 8px;">
    <a href="index.php" style="color: #64748b; text-decoration: none;">🏠 Trang chủ</a>
    <span>&rsaquo;</span>
    <a href="danh-sach.php" style="color: #64748b; text-decoration: none;">Danh mục sách</a>
    <span>&rsaquo;</span>
    <span style="color: #0f172a; font-weight: 700;"><?= e($sp->ten) ?></span>
  </nav>

  <!-- Chi tiết sách -->
  <article class="chi-tiet-container">
    
    <!-- Ảnh bìa sách -->
    <div class="chi-tiet__cot-anh">
      <figure style="margin: 0; text-align: center;">
        <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" style="border-radius:12px;box-shadow:var(--shadow-card);">
      </figure>
      <div style="margin-top: 18px; text-align: center;">
        <span class="the__the-loai-badge" style="position: static; font-size: 0.85rem; padding: 6px 16px; background:#1a56db; color:#fff; border-radius:9999px;">
          📂 <?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?>
        </span>
      </div>
    </div>

    <!-- Thông tin và Đặt mua -->
    <div class="chi-tiet__cot-thong-tin">
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #111827; margin: 0 0 12px 0;"><?= e($sp->ten) ?></h1>
      
      <div class="chi-tiet__gia" style="font-size: 1.85rem; font-weight: 800; color: #e02424; margin-bottom: 16px;">
        <?= vnd($sp->gia) ?>
        <span style="font-size: 1.05rem; color: #9ca3af; text-decoration: line-through; font-weight: 500; margin-left: 10px;">
          <?= vnd(round($sp->gia * 1.25, -3)) ?>
        </span>
        <span style="background: #fdf2f2; color: #e02424; font-size: 0.8rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #f8b4b4; margin-left: 8px;">-25% SV</span>
      </div>

      <!-- Khối thông tin Người bán / Chủ sách (Book Owner Card) -->
      <div style="display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 44px; height: 44px; border-radius: 50%; background: <?= $sp->chuSachId ? '#fef3c7' : '#e0e7ff' ?>; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; border: 1.5px solid <?= $sp->chuSachId ? '#fcd34d' : '#c7d2fe' ?>; flex-shrink: 0;">
            <?= $sp->chuSachId ? '🏪' : '👑' ?>
          </div>
          <div>
            <div style="font-size: 0.76rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; color: <?= $sp->chuSachId ? '#b45309' : '#4338ca' ?>;">
              <?= $sp->chuSachId ? 'Chủ sách đối tác (Book Owner / Seller)' : 'Phân phối chính thức (Official Store)' ?>
            </div>
            <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">
              <?= e($sp->tenChuSach ?: 'Nhà sách sinh viên BookNest UED') ?>
            </div>
            <div style="font-size: 0.82rem; color: #64748b;">
              <span>⭐ 4.9/5 (120+ lượt mua)</span> &bull; 
              <span style="color: #10b981; font-weight: 600;">✓ Đã xác minh</span>
            </div>
          </div>
        </div>
        <div>
          <?php if ($sp->chuSachId): ?>
            <a href="danh-sach.php?chu_sach=<?= $sp->chuSachId ?>" style="background: #ffffff; color: #b45309; border: 1px solid #fcd34d; font-weight: 700; font-size: 0.82rem; padding: 7px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
              <span>🏪</span> Xem quầy sách
            </a>
          <?php else: ?>
            <span style="background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 0.82rem; padding: 7px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
              <span>🛡️</span> Gian hàng chuẩn UED
            </span>
          <?php endif; ?>
        </div>
      </div>

      <table class="bang-thong-so" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tbody>
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600; width: 140px;">Mã giáo trình:</th>
            <td style="padding: 8px 0; color: #111827;"><strong>#<?= $sp->id ?></strong></td>
          </tr>
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600;">Chuyên ngành:</th>
            <td style="padding: 8px 0; color: #111827;"><?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?></td>
          </tr>
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600;">Tình trạng kho:</th>
            <td style="padding: 8px 0;">
              <?php if ($sp->tonKho > 0): ?>
                <span style="color: #0e9f6e; font-weight: 700;">● Còn hàng (<?= $sp->tonKho ?> cuốn tại kho UED)</span>
              <?php else: ?>
                <span style="color: #e02424; font-weight: 700;">○ Hết hàng tạm thời</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600;">Chính sách:</th>
            <td style="padding: 8px 0; color: #4b5563;">Trợ giá sinh viên &bull; Đổi trả trong 7 ngày nếu lỗi in &bull; Giao KTX 24h</td>
          </tr>
        </tbody>
      </table>

      <!-- Biểu mẫu Mua ngay, Thêm vào giỏ hàng và Nút Yêu thích (CSRF Protected) -->
      <div class="chi-tiet__hop-mua" style="background: #f9fafb; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 25px;">
        <?php if ($sp->tonKho > 0): ?>
          <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 18px; flex-wrap: wrap;">
            <label for="so-luong-nhap" style="font-weight: 700; color: #374151;">Số lượng mua:</label>
            <input type="number" id="so-luong-nhap" name="soLuong" value="1" min="1" max="<?= min(99, $sp->tonKho) ?>" style="width: 80px; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; text-align: center; font-size: 1.05rem; font-weight: 800; color: #111827;">
            <span style="color: #6b7280; font-size: 0.88rem;">(Tối đa <?= min(99, $sp->tonKho) ?> cuốn/đơn)</span>
          </div>

          <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <!-- 1. ปุ่มกดซื้อ (Mua ngay) -->
            <form method="POST" action="gio-hang.php" id="form-mua-ngay" style="margin: 0;">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="action" value="mua_ngay">
              <input type="hidden" name="id" value="<?= $sp->id ?>">
              <input type="hidden" name="soLuong" id="so-luong-mua-ngay" value="1">
              <button type="submit" class="nut btn-mua-ngay" style="padding: 12px 24px; font-size: 1rem; border-radius: 10px;">
                ⚡ Mua ngay
              </button>
            </form>

            <!-- 2. ปุ่มตะกร้า (Thêm vào giỏ) -->
            <form method="POST" action="gio-hang.php" id="form-them-gio" class="form-them-gio" style="margin: 0; display: inline-flex; align-items: center; gap: 10px;">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="action" value="them">
              <input type="hidden" name="id" value="<?= $sp->id ?>">
              <input type="hidden" name="soLuong" id="so-luong-them-gio" value="1">
              <button type="submit" id="btn-them-gio" class="nut btn-gio-hang" style="padding: 12px 22px; font-size: 1rem; border-radius: 10px;">
                🛒 Thêm vào giỏ hàng
              </button>
              <span id="thong-bao-gio" style="color: #0e9f6e; font-weight: 700; font-size: 0.92rem;"></span>
            </form>

            <!-- 3. ปุ่มรายการโปรด (Yêu thích) -->
            <?php $daThich = laYeuThich((int)$sp->id); ?>
            <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'chi-tiet.php?id=' . $sp->id) ?>" 
               class="nut nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" 
               data-id="<?= $sp->id ?>" 
               data-dang-chi-tiet="1" 
               role="button" 
               aria-label="<?= $daThich ? 'Bỏ thích' : 'Thêm vào yêu thích' ?> <?= e($sp->ten) ?>" 
               style="padding: 11px 20px; font-size: 0.95rem; text-decoration: none; border-radius: 10px;">
              <?= $daThich ? '❤️ Đã lưu vào yêu thích' : '🤍 Lưu vào yêu thích' ?>
            </a>
          </div>
        <?php else: ?>
          <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <p style="color: #e02424; font-weight: 600; margin: 0;">
              * Giáo trình này hiện đã tạm hết hàng tại kho UED. Bạn có thể lưu vào danh sách yêu thích để nhận thông báo khi có hàng.
            </p>
            <?php $daThich = laYeuThich((int)$sp->id); ?>
            <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'chi-tiet.php?id=' . $sp->id) ?>" 
               class="nut nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" 
               data-id="<?= $sp->id ?>" 
               data-dang-chi-tiet="1" 
               role="button" 
               style="padding: 10px 18px; font-size: 0.92rem; text-decoration: none;">
              <?= $daThich ? '❤️ Đã lưu vào yêu thích' : '🤍 Lưu vào yêu thích khi có hàng' ?>
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Nội dung giới thiệu tóm tắt giáo trình -->
      <h2 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin: 24px 0 10px 0;">📖 Mô tả nội dung giáo trình</h2>
      <p style="line-height: 1.75; color: #4b5563; font-size: 1rem; margin: 0;">
        <?= nl2br(e($sp->moTa ?? 'Chưa có mô tả chi tiết cho đầu sách này.')) ?>
      </p>
    </div>
  </article>

  <!-- Khối sách liên quan cùng chuyên ngành (Tối đa 4 cuốn) -->
  <?php if (!empty($sachLienQuan)): ?>
    <section style="margin-top: 50px;">
      <div class="section-tmdt-head">
        <h2 class="section-tmdt-head__title">
          <span>📚</span> Sách cùng danh mục bạn có thể quan tâm
        </h2>
      </div>

      <div class="grid-books-tmdt">
        <?php foreach ($sachLienQuan as $lq): 
          $daThichLq = laYeuThich((int)$lq->id);
          $giaGocLq = round($lq->gia * 1.25, -3);
        ?>
          <article class="the" data-id="<?= $lq->id ?>">
            <figure class="the__hinh-anh">
              <span class="the__the-loai-badge" style="background:#1a56db;"><?= e($lq->tenDanhMuc ?? 'Giáo trình') ?></span>
              <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThichLq ? 'da-thich' : '' ?>" data-id="<?= $lq->id ?>" role="button">
                <?= $daThichLq ? '❤️' : '🤍' ?>
              </button>
              <img src="<?= e(anhSach($lq->anh)) ?>" alt="Bìa sách <?= e($lq->ten) ?>" width="175" height="225" loading="lazy">
            </figure>
            <div class="the__than">
              <h3 class="the__tieu-de"><a href="chi-tiet.php?id=<?= $lq->id ?>"><?= e($lq->ten) ?></a></h3>
              <div class="the__gia-row">
                <span class="the__gia"><?= vnd($lq->gia) ?></span>
                <span class="the__gia-goc"><?= vnd($giaGocLq) ?></span>
              </div>
              <div class="the__chan">
                <div class="the__chan-hang1">
                  <form method="POST" action="gio-hang.php" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="mua_ngay">
                    <input type="hidden" name="id" value="<?= $lq->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-mua-ngay">⚡ Mua ngay</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $lq->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Giỏ</button>
                  </form>
                </div>
                <div class="the__chan-hang2">
                  <a href="chi-tiet.php?id=<?= $lq->id ?>" class="btn-chi-tiet">🔍 Chi tiết</a>
                  <a href="yeu-thich.php?action=toggle&id=<?= $lq->id ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'chi-tiet.php?id=' . $sp->id) ?>" class="nut-bam-yeu-thich <?= $daThichLq ? 'da-thich' : '' ?>" data-id="<?= $lq->id ?>" role="button">
                    <?= $daThichLq ? '❤️ Đã thích' : '🤍 Yêu thích' ?>
                  </a>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

<script>
  // Đồng bộ số lượng nhập vào form Mua ngay và form Thêm vào giỏ
  const oNhap = document.getElementById('so-luong-nhap');
  const oMuaNgay = document.getElementById('so-luong-mua-ngay');
  const oThemGio = document.getElementById('so-luong-them-gio');

  if (oNhap && oMuaNgay && oThemGio) {
    oNhap.addEventListener('input', function() {
      const val = parseInt(this.value, 10) || 1;
      oMuaNgay.value = val;
      oThemGio.value = val;
    });
  }
</script>

<script src="js/gio-hang.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
