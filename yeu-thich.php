<?php
/**
 * yeu-thich.php - Manage user wishlist
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$kho = new KhoSanPham(db());
$gio = new GioHang();

$u = nguoiDung();
$db = db();

// SECURITY: Require authentication to access wishlist
if (!$u) {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
              || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    if ($isAjax) {
        traJson([
            'thanh_cong' => false,
            'require_login' => true,
            'loi' => 'Please sign in to add books to your wishlist!',
            'redirect' => 'dang-nhap.php'
        ], 401);
    }
    $_SESSION['flash_error'] = 'Please sign in to view and save books to your wishlist!';
    $_SESSION['redirect_sau_dang_nhap'] = $_REQUEST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'yeu-thich.php';
    header('Location: dang-nhap.php');
    exit;
}

if (!isset($_SESSION['yeu_thich']) || !is_array($_SESSION['yeu_thich'])) {
    $_SESSION['yeu_thich'] = [];
}

// Sync from MySQL database if empty in session
if ($u && empty($_SESSION['yeu_thich'])) {
    try {
        $stDb = $db->prepare('SELECT san_pham_id FROM yeu_thich WHERE nguoi_dung_id = ? ORDER BY ngay_tao DESC');
        $stDb->execute([(int)$u['id']]);
        $_SESSION['yeu_thich'] = array_map('intval', $stDb->fetchAll(PDO::FETCH_COLUMN));
    } catch (\Throwable $e) {}
}

// Handle actions: toggle, xoa, xoa_het
$action = $_REQUEST['action'] ?? '';
if ($action === 'toggle' || $action === 'xoa' || $action === 'xoa_het') {
    $id = filter_var($_REQUEST['id'] ?? '', FILTER_VALIDATE_INT);
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
              || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

    if ($action === 'xoa_het') {
        $_SESSION['yeu_thich'] = [];
        if ($u) {
            try {
                $db->prepare('DELETE FROM yeu_thich WHERE nguoi_dung_id = ?')->execute([(int)$u['id']]);
            } catch (\Throwable $e) {}
        }
        header('Location: yeu-thich.php');
        exit;
    }

    if ($id === false || $id <= 0) {
        if ($isAjax) {
            traJson(['thanh_cong' => false, 'loi' => 'Invalid book ID'], 400);
        }
        $_SESSION['flash_loi'] = 'Invalid book ID.';
        header('Location: ' . ($_REQUEST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'yeu-thich.php'));
        exit;
    }

    $sp = $kho->timTheoId($id);
    if (!$sp) {
        if ($isAjax) {
            traJson(['thanh_cong' => false, 'loi' => 'Book not found'], 404);
        }
        $_SESSION['flash_loi'] = 'Textbook does not exist in the system.';
        header('Location: ' . ($_REQUEST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'yeu-thich.php'));
        exit;
    }

    if ($action === 'toggle') {
        $daThichHienTai = false;
        if ($u) {
            try {
                $stCheck = $db->prepare('SELECT 1 FROM yeu_thich WHERE nguoi_dung_id = ? AND san_pham_id = ?');
                $stCheck->execute([(int)$u['id'], $id]);
                $daThichHienTai = (bool)$stCheck->fetchColumn();
            } catch (\Throwable $e) {}
        } else {
            $daThichHienTai = in_array($id, $_SESSION['yeu_thich'], true);
        }

        if ($daThichHienTai) {
            if ($u) {
                try {
                    $db->prepare('DELETE FROM yeu_thich WHERE nguoi_dung_id = ? AND san_pham_id = ?')->execute([(int)$u['id'], $id]);
                } catch (\Throwable $e) {}
            }
            $idx = array_search($id, $_SESSION['yeu_thich'], true);
            if ($idx !== false) {
                unset($_SESSION['yeu_thich'][$idx]);
                $_SESSION['yeu_thich'] = array_values($_SESSION['yeu_thich']);
            }
            $daThich = false;
            $thongBao = 'Removed "' . $sp->ten . '" from your wishlist.';
        } else {
            if ($u) {
                try {
                    $db->prepare('INSERT IGNORE INTO yeu_thich (nguoi_dung_id, san_pham_id) VALUES (?, ?)')->execute([(int)$u['id'], $id]);
                } catch (\Throwable $e) {}
            }
            if (!in_array($id, $_SESSION['yeu_thich'], true)) {
                $_SESSION['yeu_thich'][] = $id;
            }
            $daThich = true;
            $thongBao = 'Added "' . $sp->ten . '" to your wishlist! ❤️';
        }

        if ($isAjax) {
            traJson([
                'thanh_cong' => true,
                'da_thich'   => $daThich,
                'so_luong'   => soLuongYeuThich(),
                'thong_bao'  => $thongBao
            ]);
        }
        $_SESSION['flash'] = $thongBao;
        $chuyenHuong = $_REQUEST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? 'yeu-thich.php');
        header('Location: ' . $chuyenHuong);
        exit;
    }

    if ($action === 'xoa') {
        if ($u) {
            try {
                $db->prepare('DELETE FROM yeu_thich WHERE nguoi_dung_id = ? AND san_pham_id = ?')->execute([(int)$u['id'], $id]);
            } catch (\Throwable $e) {}
        }
        $idx = array_search($id, $_SESSION['yeu_thich'], true);
        if ($idx !== false) {
            unset($_SESSION['yeu_thich'][$idx]);
            $_SESSION['yeu_thich'] = array_values($_SESSION['yeu_thich']);
        }
        $_SESSION['flash'] = 'Book removed from your wishlist.';
        header('Location: yeu-thich.php');
        exit;
    }
}

$idsYeuThich = layDanhSachYeuThich();
$dsYeuThich = !empty($idsYeuThich) ? $kho->layTheoDanhSachId($idsYeuThich) : [];

$tieuDe = 'My Wishlist';
$trang = 'yeu-thich';
$moTaTrang = 'List of textbooks and reference books saved in your wishlist.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 1000px; margin: 30px auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d; font-size: 1.8rem;">❤️ My Wishlist</h1>
      <p style="margin: 5px 0 0 0; color: #64748b;">Save textbooks you care about to track, buy immediately, or add to cart anytime.</p>
    </div>
    <a href="danh-sach.php" class="nut nut--vien">&larr; Continue Exploring Books</a>
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
      <h2>Your wishlist is empty</h2>
      <p style="color: #64748b; max-width: 480px; margin: 10px auto 25px auto;">
        You haven't saved any books yet. Click the "❤️ Wishlist" button on any book card to save it here!
      </p>
      <a href="danh-sach.php" class="nut nut--chinh" style="padding: 10px 24px;">Explore Book Catalog</a>
    </div>
  <?php else: ?>
    <p style="color: #475569; margin-bottom: 20px;">
      You have <strong><?= count($dsYeuThich) ?></strong> <?= count($dsYeuThich) > 1 ? 'books' : 'book' ?> saved in your wishlist:
    </p>

    <div class="luoi-the">
      <?php foreach ($dsYeuThich as $sp): ?>
        <article class="the">
          <figure class="the__hinh-anh">
            <span class="the__tag-noi-bat">❤️ SAVED</span>
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Cover <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>
          <div class="the__than">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <span class="the__the-loai"><?= e(layTenDanhMucEn($sp->danhMucId, $sp->tenDanhMuc)) ?></span>
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
              <?= $sp->tonKho > 0 ? 'In Stock (' . $sp->tonKho . ' copies)' : 'Temporarily Out of Stock' ?>
            </p>

            <div class="the__chan">
              <!-- 1. Buy Now -->
              <?php if ($sp->tonKho > 0): ?>
                <form method="POST" action="gio-hang.php">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="mua_ngay">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="soLuong" value="1">
                  <button type="submit" class="nut nut--nhan btn-mua-ngay" title="Buy this textbook now">⚡ Buy Now</button>
                </form>
              <?php else: ?>
                <button type="button" disabled class="nut nut--vien" style="opacity:0.6;cursor:not-allowed;" title="Out of stock">⚡ Sold Out</button>
              <?php endif; ?>

              <!-- 2. Add to Cart -->
              <?php if ($sp->tonKho > 0): ?>
                <form method="POST" action="gio-hang.php" class="form-them-nhanh">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="them">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="soLuong" value="1">
                  <button type="submit" class="nut nut--chinh btn-them-nhanh" title="Add to Cart">🛒 Add to Cart</button>
                </form>
              <?php else: ?>
                <button type="button" disabled class="nut nut--vien" style="opacity:0.6;cursor:not-allowed;" title="Out of stock">🛒 Sold Out</button>
              <?php endif; ?>

              <!-- 3. Remove from Wishlist -->
              <form method="POST" action="yeu-thich.php">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="xoa">
                <input type="hidden" name="id" value="<?= $sp->id ?>">
                <button type="submit" class="nut nut--vien btn-yeu-thich da-thich" title="Remove from wishlist">❌ Remove</button>
              </form>

              <!-- 4. Details -->
              <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut nut--vien" title="View details">🔍 Details</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<script src="js/gio-hang.js?v=<?= filemtime(__DIR__ . '/js/gio-hang.js') ?>"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
