<?php
/**
 * yeu-thich.php - Trang Quản lý danh sách sách yêu thích (Wishlist) của người dùng
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Lưu trữ danh sách yêu thích trong Session, bảo vệ CSRF và hỗ trợ các nút thao tác
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$kho = new KhoSanPham(db());
$gio = new GioHang();

if (!isset($_SESSION['yeu_thich']) || !is_array($_SESSION['yeu_thich'])) {
    $_SESSION['yeu_thich'] = [];
}

// Xử lý yêu cầu toggle hoặc xóa (Hỗ trợ cả GET, POST và AJAX Fetch)
$action = $_REQUEST['action'] ?? '';
if ($action === 'toggle' || $action === 'xoa' || $action === 'xoa_het') {
    $id = filter_var($_REQUEST['id'] ?? '', FILTER_VALIDATE_INT);
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
              || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

    if ($action === 'xoa_het') {
        $_SESSION['yeu_thich'] = [];
        header('Location: yeu-thich.php');
        exit;
    }

    if ($id === false || $id <= 0) {
        if ($isAjax) {
            traJson(['thanh_cong' => false, 'loi' => 'Mã sách không hợp lệ'], 400);
        }
        $_SESSION['flash_loi'] = 'Mã sách không hợp lệ.';
        header('Location: ' . ($_REQUEST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'yeu-thich.php'));
        exit;
    }

    $sp = $kho->timTheoId($id);
    if (!$sp) {
        if ($isAjax) {
            traJson(['thanh_cong' => false, 'loi' => 'Sách không tồn tại'], 404);
        }
        $_SESSION['flash_loi'] = 'Sách không tồn tại trong hệ thống.';
        header('Location: ' . ($_REQUEST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'yeu-thich.php'));
        exit;
    }

    if ($action === 'toggle') {
        $idx = array_search($id, $_SESSION['yeu_thich'], true);
        if ($idx !== false) {
            unset($_SESSION['yeu_thich'][$idx]);
            $_SESSION['yeu_thich'] = array_values($_SESSION['yeu_thich']);
            $daThich = false;
            $thongBao = 'Đã bỏ sách "' . $sp->ten . '" khỏi danh sách yêu thích.';
        } else {
            $_SESSION['yeu_thich'][] = $id;
            $daThich = true;
            $thongBao = 'Đã thêm sách "' . $sp->ten . '" vào danh sách yêu thích! ❤️';
        }

        if ($isAjax) {
            traJson([
                'thanh_cong' => true,
                'da_thich'   => $daThich,
                'so_luong'   => count($_SESSION['yeu_thich']),
                'thong_bao'  => $thongBao
            ]);
        }
        $_SESSION['flash'] = $thongBao;
        $chuyenHuong = $_REQUEST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? 'yeu-thich.php');
        header('Location: ' . $chuyenHuong);
        exit;
    }

    if ($action === 'xoa') {
        $idx = array_search($id, $_SESSION['yeu_thich'], true);
        if ($idx !== false) {
            unset($_SESSION['yeu_thich'][$idx]);
            $_SESSION['yeu_thich'] = array_values($_SESSION['yeu_thich']);
        }
        $_SESSION['flash'] = 'Đã xóa cuốn sách khỏi danh sách yêu thích.';
        header('Location: yeu-thich.php');
        exit;
    }
}

// Lấy danh sách sản phẩm yêu thích từ CSDL
$idsYeuThich = array_filter(array_map('intval', $_SESSION['yeu_thich'] ?? []));
$dsYeuThich = !empty($idsYeuThich) ? $kho->layTheoDanhSachId($idsYeuThich) : [];

$tieuDe = 'Danh sách sách yêu thích';
$trang = 'yeu-thich';
$moTaTrang = 'Danh sách các giáo trình và sách chuyên ngành sinh viên yêu thích tại BookNest.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 1000px; margin: 30px auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d; font-size: 1.8rem;">❤️ Danh sách sách yêu thích (Wishlist)</h1>
      <p style="margin: 5px 0 0 0; color: #64748b;">Lưu giữ những cuốn sách bạn quan tâm để dễ dàng theo dõi, đặt mua hoặc thêm vào giỏ hàng.</p>
    </div>
    <a href="danh-sach.php" class="nut nut--vien">&larr; Tiếp tục khám phá sách</a>
  </div>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 6px; margin-bottom: 25px;">
      ✓ <?= e($_SESSION['flash']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_loi'])): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 6px; margin-bottom: 25px;">
      ⚠ <?= e($_SESSION['flash_loi']) ?>
    </div>
    <?php unset($_SESSION['flash_loi']); ?>
  <?php endif; ?>

  <?php if (empty($dsYeuThich)): ?>
    <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: 8px; border: 1px dashed #cbd5e1;">
      <p style="font-size: 3.5rem; margin: 0 0 10px 0;">🤍</p>
      <h2>Danh sách yêu thích của bạn đang trống</h2>
      <p style="color: #64748b; max-width: 480px; margin: 10px auto 25px auto;">
        Bạn chưa thêm cuốn sách nào vào mục yêu thích. Hãy bấm vào biểu tượng "❤️ Yêu thích" ở mỗi cuốn sách để lưu lại tại đây nhé!
      </p>
      <a href="danh-sach.php" class="nut nut--chinh" style="padding: 10px 24px;">Xem danh mục sách BookNest</a>
    </div>
  <?php else: ?>
    <p style="color: #475569; margin-bottom: 20px;">
      Bạn đang lưu <strong><?= count($dsYeuThich) ?></strong> cuốn sách trong danh sách yêu thích:
    </p>

    <div class="luoi-the">
      <?php foreach ($dsYeuThich as $sp): ?>
        <article class="the">
          <figure class="the__hinh-anh">
            <span class="the__tag-noi-bat">❤️ ĐÃ LƯU</span>
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>
          <div class="the__than">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <span class="the__the-loai"><?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?></span>
              <div class="the__danh-gia">
                <span>★</span> 4.9 (24)
              </div>
            </div>
            <h3 class="the__tieu-de" title="<?= e($sp->ten) ?>"><?= e($sp->ten) ?></h3>
            <div style="display: flex; align-items: baseline; gap: 8px; margin: 4px 0 6px 0;">
              <span class="the__gia" style="margin: 0;"><?= vnd($sp->gia) ?></span>
              <span class="the__gia-goc"><?= vnd(round($sp->gia * 1.25, -3)) ?></span>
            </div>
            <p class="the__ton-kho" style="color:<?= $sp->tonKho > 0 ? '#16a34a' : '#dc2626' ?>;">
              <?= $sp->tonKho > 0 ? 'Còn hàng (' . $sp->tonKho . ' cuốn)' : 'Hết hàng tạm thời' ?>
            </p>

            <!-- 4 NÚT THAO TÁC THEO YÊU CẦU: ĐẶT MUA, THÊM GIỎ, BỎ THÍCH, CHI TIẾT (2x2 GRID) -->
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

              <!-- 3. Nút Xóa khỏi danh sách yêu thích -->
              <form method="POST" action="yeu-thich.php">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="xoa">
                <input type="hidden" name="id" value="<?= $sp->id ?>">
                <button type="submit" class="nut nut--vien btn-yeu-thich da-thich" title="Bỏ khỏi yêu thích">❌ Bỏ thích</button>
              </form>

              <!-- 4. Nút Xem chi tiết (ปุ่มดูรายละเอียด) -->
              <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut nut--vien" title="Xem chi tiết nội dung cuốn sách">🔍 Chi tiết</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<script src="js/gio-hang.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
