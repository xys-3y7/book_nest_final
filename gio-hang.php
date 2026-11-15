<?php
/**
 * gio-hang.php - Manage shopping cart stored in Session
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;
use App\Services\GioHang;

$kho = new KhoSanPham(db());
$gio = new GioHang();

$u = nguoiDung();

// SECURITY: Require authentication to access cart
if (!$u) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $_SESSION['flash_error'] = 'Please sign in to add books to your shopping cart!';
        $_SESSION['redirect_sau_dang_nhap'] = $id ? "chi-tiet.php?id=$id" : "danh-sach.php";
    } else {
        $_SESSION['flash_error'] = 'Please sign in to view and manage your shopping cart.';
        $_SESSION['redirect_sau_dang_nhap'] = 'gio-hang.php';
    }
    header('Location: dang-nhap.php');
    exit;
}

// Handle cart actions via POST (Post/Redirect/Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'them' || $action === 'mua_ngay') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $soLuong = filter_var($_POST['soLuong'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 99]
        ]);

        $sp = $id ? $kho->timTheoId($id) : null;
        if ($id === false || $soLuong === false || $sp === null) {
            $_SESSION['flash_loi'] = 'Invalid operation: Book ID not found or quantity outside 1 - 99.';
            header('Location: gio-hang.php');
            exit;
        }

        // Check stock availability
        if ($sp->tonKho <= 0) {
            $_SESSION['flash_loi'] = 'Sorry! The textbook "' . $sp->ten . '" is currently out of stock.';
            header('Location: ' . ($action === 'mua_ngay' ? 'chi-tiet.php?id=' . $id : 'gio-hang.php'));
            exit;
        }

        $hienCo = $_SESSION['gio'][$id] ?? 0;
        if ($hienCo + $soLuong > $sp->tonKho) {
            $duocThem = max(0, $sp->tonKho - $hienCo);
            if ($duocThem > 0) {
                $gio->them($id, $duocThem, $kho);
                $_SESSION['flash'] = 'Only ' . $duocThem . ' copies added because available stock is ' . $sp->tonKho . ' (you already have ' . $hienCo . ' in your cart).';
            } else {
                $_SESSION['flash_loi'] = 'You already have ' . $hienCo . ' copies in your cart, which equals the total available stock (' . $sp->tonKho . ' copies).';
            }
        } else {
            $gio->them($id, $soLuong, $kho);
            if ($action === 'mua_ngay') {
                $_SESSION['flash'] = 'Textbook added to order. Please confirm your delivery details!';
                header('Location: dat-hang.php');
                exit;
            }
            $_SESSION['flash'] = 'Textbook added to cart successfully!';
        }
        header('Location: ' . ($action === 'mua_ngay' ? 'dat-hang.php' : 'gio-hang.php'));
        exit;
    }

    if ($action === 'capnhat') {
        $cacSoLuong = $_POST['so_luong'] ?? [];
        $dieuChinh = false;
        if (is_array($cacSoLuong)) {
            foreach ($cacSoLuong as $id => $sl) {
                $idInt = filter_var($id, FILTER_VALIDATE_INT);
                $slInt = filter_var($sl, FILTER_VALIDATE_INT);
                if ($idInt !== false && $slInt !== false) {
                    $sp = $kho->timTheoId($idInt);
                    if ($sp) {
                        if ($slInt > $sp->tonKho) {
                            $slInt = max(0, $sp->tonKho);
                            $dieuChinh = true;
                        }
                        $gio->capNhat($idInt, (int)$slInt);
                    }
                }
            }
            if ($dieuChinh) {
                $_SESSION['flash'] = 'Cart updated: Some quantities were automatically adjusted to match available stock.';
            } else {
                $_SESSION['flash'] = 'Cart quantities updated successfully!';
            }
        }
        header('Location: gio-hang.php');
        exit;
    }

    if ($action === 'xoa') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id !== false) {
            $gio->xoa($id);
            $_SESSION['flash'] = 'Book removed from shopping cart.';
        }
        header('Location: gio-hang.php');
        exit;
    }

    if ($action === 'xoahet') {
        $gio->xoaHet();
        $_SESSION['flash'] = 'Shopping cart cleared.';
        header('Location: gio-hang.php');
        exit;
    }
}

$thongBao = $_SESSION['flash'] ?? '';
$thongBaoLoi = $_SESSION['flash_loi'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_loi']);

$chiTietGio = $gio->layChiTiet($kho);
$tongTien = $gio->tongTien($kho);
$soMon = $gio->soMon();

$tieuDe = 'Shopping Cart';
$trang = 'gio-hang';
$moTaTrang = 'View and manage selected textbooks in your BookNest shopping cart.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <h1>Your Shopping Cart</h1>
  <p>All items are securely kept in your server session (PHP Session).</p>

  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;" aria-live="polite">
      <strong>✓ Success:</strong> <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <?php if ($thongBaoLoi !== ''): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;" aria-live="assertive">
      <strong>⚠ Error:</strong> <?= e($thongBaoLoi) ?>
    </div>
  <?php endif; ?>

  <?php if ($soMon > 0): ?>
    <form method="POST" action="gio-hang.php">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="capnhat">

      <div style="overflow-x: auto; margin-bottom: 25px;">
        <table style="width: 100%; border-collapse: collapse; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
          <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; text-align: left;">
              <th style="padding: 12px 15px;">Cover</th>
              <th style="padding: 12px 15px;">Textbook Title</th>
              <th style="padding: 12px 15px;">Unit Price</th>
              <th style="padding: 12px 15px; width: 120px;">Quantity</th>
              <th style="padding: 12px 15px;">Subtotal</th>
              <th style="padding: 12px 15px; text-align: center;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($chiTietGio as $item): 
              $sp = $item['sanPham'];
            ?>
              <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 12px 15px; width: 80px;">
                  <img src="<?= e(anhSach($sp->anh)) ?>" alt="Cover <?= e($sp->ten) ?>" width="60" height="75" style="border-radius: 4px; object-fit: cover;">
                </td>
                <td style="padding: 12px 15px;">
                  <a href="chi-tiet.php?id=<?= $sp->id ?>" style="font-weight: bold; color: #1e40af; text-decoration: none;">
                    <?= e($sp->ten) ?>
                  </a>
                  <div style="color: #64748b; font-size: 0.85rem; margin-top: 3px;">Category: <?= e(layTenDanhMucEn($sp->danhMucId, $sp->tenDanhMuc)) ?></div>
                </td>
                <td style="padding: 12px 15px; font-weight: 500;">
                  <?= vnd($sp->gia) ?>
                </td>
                <td style="padding: 12px 15px;">
                  <input type="number" name="so_luong[<?= $sp->id ?>]" value="<?= $item['soLuong'] ?>" min="0" max="<?= max(1, (int)$sp->tonKho) ?>" style="width: 75px; padding: 6px 8px; border: 1.5px solid <?= $item['soLuong'] > $sp->tonKho ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; text-align: center; font-weight: 700;">
                  <div style="font-size: 0.78rem; margin-top: 4px; line-height: 1.3;">
                    <?php if ($sp->tonKho > 0): ?>
                      <span style="color: <?= $item['soLuong'] > $sp->tonKho ? '#dc2626' : '#059669' ?>; font-weight: 600;">
                        In stock: <?= $sp->tonKho ?> copies
                      </span>
                    <?php else: ?>
                      <span style="color: #dc2626; font-weight: 700;">⚠ Out of stock</span>
                    <?php endif; ?>
                    <?php if ($item['soLuong'] > $sp->tonKho): ?>
                      <div style="color: #dc2626; font-weight: 700; font-size: 0.72rem;">⚠ Exceeds stock</div>
                    <?php endif; ?>
                  </div>
                </td>
                <td style="padding: 12px 15px; font-weight: bold; color: #dc2626;">
                  <?= vnd($item['thanhTien']) ?>
                </td>
                <td style="padding: 12px 15px; text-align: center;">
                  <button type="submit" form="form-xoa-<?= $sp->id ?>" class="nut nut--vien" style="padding: 5px 10px; color: #dc2626; border-color: #fca5a5; font-size: 0.85rem;">
                    Remove
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="background: #f8fafc; font-size: 1.1rem; border-top: 2px solid #cbd5e1;">
              <td colspan="3" style="padding: 15px; font-weight: bold; text-align: right;">
                Total (<?= $soMon ?> <?= $soMon > 1 ? 'items' : 'item' ?>):
              </td>
              <td colspan="3" style="padding: 15px; font-weight: bold; color: #dc2626; font-size: 1.3rem;">
                <?= vnd($tongTien) ?>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div style="display: flex; gap: 15px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; gap: 10px;">
          <a href="danh-sach.php" class="nut nut--vien">&larr; Continue Shopping</a>
          <button type="submit" class="nut nut--vien">🔄 Update Cart</button>
        </div>
        <div style="display: flex; gap: 10px;">
          <button type="submit" form="form-xoa-het" class="nut nut--vien" style="color: #dc2626; border-color: #fca5a5;">
            🗑️ Clear Cart
          </button>
          <a href="dat-hang.php" class="nut nut--chinh" style="padding: 10px 24px; font-size: 1rem;">
            Proceed to Checkout &rarr;
          </a>
        </div>
      </div>
    </form>

    <?php foreach ($chiTietGio as $item): ?>
      <form id="form-xoa-<?= $item['sanPham']->id ?>" method="POST" action="gio-hang.php" style="display: none;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="xoa">
        <input type="hidden" name="id" value="<?= $item['sanPham']->id ?>">
      </form>
    <?php endforeach; ?>

    <form id="form-xoa-het" method="POST" action="gio-hang.php" style="display: none;">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="xoahet">
    </form>

  <?php else: ?>
    <div style="text-align: center; padding: 50px 20px; background: #ffffff; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 20px;">
      <p style="font-size: 3rem; margin: 0 0 10px 0;">🛒</p>
      <h2>Your shopping cart is empty</h2>
      <p style="color: #64748b; max-width: 450px; margin: 10px auto 25px auto;">
        You haven't added any textbooks to your cart yet. Browse our catalog to find valuable study materials!
      </p>
      <a href="danh-sach.php" class="nut nut--chinh" style="padding: 10px 24px;">Explore Book Catalog Now</a>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
