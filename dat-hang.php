<?php
/**
 * dat-hang.php - Confirm and place textbook order using ACID transaction
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/don-hang.php';
require_once __DIR__ . '/inc/thanh-toan.php';

// Authentication required
yeuCauDangNhap();

use App\Data\KhoSanPham;
use App\Services\GioHang;

$u = nguoiDung();
$kho = new KhoSanPham(db());
$gio = new GioHang();

$chiTietGio = $gio->layChiTiet($kho);
$tongTien = $gio->tongTien($kho);
$soMon = $gio->soMon();

if ($soMon === 0) {
    $_SESSION['flash_loi'] = 'Your shopping cart is empty. Please select textbooks before placing an order.';
    header('Location: danh-sach.php');
    exit;
}

$dsPhuongThuc = layDanhSachPhuongThuc(true);
$cacMaHopLe = array_column($dsPhuongThuc, 'code');

$du = [
    'dia_chi'        => $u['dia_chi'] ?? '',
    'so_dien_thoai'  => $u['so_dien_thoai'] ?? '',
    'ghi_chu'        => '',
    'phuong_thuc_tt' => $cacMaHopLe[0] ?? 'cod',
];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['dia_chi']        = trim(chuoi($_POST['dia_chi'] ?? ''));
    $du['so_dien_thoai']  = trim(chuoi($_POST['so_dien_thoai'] ?? ''));
    $du['ghi_chu']        = trim(chuoi($_POST['ghi_chu'] ?? ''));
    $du['phuong_thuc_tt'] = trim(chuoi($_POST['phuong_thuc_tt'] ?? 'cod'));

    if (!in_array($du['phuong_thuc_tt'], $cacMaHopLe, true)) {
        $du['phuong_thuc_tt'] = 'cod';
    }

    if ($du['dia_chi'] === '' || mb_strlen($du['dia_chi']) < 5) {
        $loi['dia_chi'] = 'Please provide a detailed delivery address (at least 5 characters).';
    }

    if ($du['so_dien_thoai'] === '' || !preg_match('/^(0|\+84)[0-9]{9,10}$/', $du['so_dien_thoai'])) {
        $loi['so_dien_thoai'] = 'Invalid phone number (must be 10-11 digits).';
    }

    // Pre-check stock
    $sachVuotTonKho = [];
    foreach ($chiTietGio as $item) {
        $sp = $item['sanPham'];
        if ($item['soLuong'] > $sp->tonKho) {
            $sachVuotTonKho[] = 'Textbook "' . e($sp->ten) . '" has ' . $item['soLuong'] . ' in cart, but only ' . $sp->tonKho . ' available.';
        }
    }
    if (!empty($sachVuotTonKho)) {
        $loi['he_thong'] = implode('<br>', $sachVuotTonKho) . ' Please <a href="gio-hang.php" style="color:#1d4ed8;font-weight:bold;text-decoration:underline;">click here to return to your cart</a> and adjust quantities.';
    }

    if (empty($loi)) {
        try {
            $donHangId = luuDonHang(
                (int)$u['id'],
                $gio->danhSach(),
                $du['dia_chi'],
                $du['so_dien_thoai'],
                $du['ghi_chu'] !== '' ? $du['ghi_chu'] : null,
                $du['phuong_thuc_tt']
            );

            $gio->xoaHet();

            $_SESSION['flash'] = "Congratulations! Your order #$donHangId has been placed successfully. Please review your payment details below.";
            header("Location: thanh-toan.php?order_id=$donHangId");
            exit;

        } catch (RuntimeException $e) {
            $loi['he_thong'] = $e->getMessage();
        } catch (Throwable $e) {
            error_log("Order placement error: " . $e->getMessage());
            $loi['he_thong'] = 'An unexpected error occurred while processing your order. Please try again.';
        }
    }
}

$tieuDe = 'Confirm Order';
$trang = 'gio-hang';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 900px; margin: 30px auto;">
  <h1>Checkout &amp; Place Order</h1>
  <p>Please review your selected textbooks and provide delivery information.</p>

  <?php if (isset($loi['he_thong'])): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 6px; margin-bottom: 25px;" aria-live="assertive">
      <strong>⚠ Transaction Notice:</strong> <?= $loi['he_thong'] ?>
    </div>
  <?php endif; ?>

  <div style="display: flex; gap: 30px; flex-wrap: wrap;">
    <!-- Shipping Address Form -->
    <div style="flex: 3; min-width: 320px; background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
      <h2 style="margin-top: 0; color: #1e3b6d; font-size: 1.3rem;">Delivery Information</h2>

      <form method="POST" action="dat-hang.php" novalidate>
        <?= csrfField() ?>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: bold; margin-bottom: 5px;">Recipient Name:</label>
          <input type="text" value="<?= e($u['ho_ten']) ?>" disabled style="width: 100%; padding: 9px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: bold; margin-bottom: 5px;">Account Email:</label>
          <input type="text" value="<?= e($u['email']) ?>" disabled style="width: 100%; padding: 9px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 16px;">
          <label for="so_dien_thoai" style="display: block; font-weight: bold; margin-bottom: 5px;">
            Phone Number <span style="color:#dc2626;">*</span>
          </label>
          <input type="tel" id="so_dien_thoai" name="so_dien_thoai" value="<?= e($du['so_dien_thoai']) ?>" placeholder="e.g. 0905123456" style="width: 100%; padding: 9px 12px; border: 1px solid <?= isset($loi['so_dien_thoai']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
          <?php if (isset($loi['so_dien_thoai'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['so_dien_thoai']) ?></p>
          <?php endif; ?>
        </div>

        <div style="margin-bottom: 16px;">
          <label for="dia_chi" style="display: block; font-weight: bold; margin-bottom: 5px;">
            Shipping Address (Dormitory / Home) <span style="color:#dc2626;">*</span>
          </label>
          <textarea id="dia_chi" name="dia_chi" rows="3" placeholder="Enter your detailed shipping address..." style="width: 100%; padding: 9px 12px; border: 1px solid <?= isset($loi['dia_chi']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;"><?= e($du['dia_chi']) ?></textarea>
          <?php if (isset($loi['dia_chi'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['dia_chi']) ?></p>
          <?php endif; ?>
        </div>

        <div style="margin-bottom: 20px;">
          <label for="ghi_chu" style="display: block; font-weight: bold; margin-bottom: 5px;">Delivery Notes (Optional):</label>
          <input type="text" id="ghi_chu" name="ghi_chu" value="<?= e($du['ghi_chu']) ?>" placeholder="e.g. Deliver after class in the afternoon..." style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <!-- Payment Method Selection -->
        <div style="margin-bottom: 25px;">
          <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #0f172a;">
            Payment Method <span style="color:#dc2626;">*</span>
          </label>
          
          <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($dsPhuongThuc as $pt): 
              $isSel = ($du['phuong_thuc_tt'] === $pt['code']);
            ?>
              <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px; border: 1.5px solid <?= $isSel ? '#10b981' : '#e2e8f0' ?>; background: <?= $isSel ? '#f0fdf4' : '#ffffff' ?>; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                <input type="radio" name="phuong_thuc_tt" value="<?= e($pt['code']) ?>" <?= $isSel ? 'checked' : '' ?> style="margin-top: 3px;" onchange="this.form.querySelectorAll('input[name=phuong_thuc_tt]').forEach(r => { r.closest('label').style.borderColor = r.checked ? '#10b981' : '#e2e8f0'; r.closest('label').style.background = r.checked ? '#f0fdf4' : '#ffffff'; });">
                <div>
                  <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                    <?= e($pt['icon']) ?> <?= e($pt['name']) ?>
                  </div>
                  <div style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">
                    <?= e($pt['description']) ?>
                  </div>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
          <button type="submit" class="nut nut--chinh" style="padding: 12px 28px; font-size: 1.05rem;">
            ✓ Confirm Order (<?= vnd($tongTien) ?>)
          </button>
          <a href="gio-hang.php" class="nut nut--vien">&larr; Edit Cart</a>
        </div>
      </form>
    </div>

    <!-- Order Summary -->
    <div style="flex: 2; min-width: 260px;">
      <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="margin-top: 0; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px;">
          Order Summary (<?= $soMon ?> <?= $soMon > 1 ? 'items' : 'item' ?>)
        </h3>

        <ul style="list-style: none; padding: 0; margin: 0 0 15px 0;">
          <?php foreach ($chiTietGio as $item): 
            $sp = $item['sanPham'];
          ?>
            <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: 0.9rem;">
              <div>
                <strong><?= e($sp->ten) ?></strong><br>
                <small style="color: #64748b;">Quantity: <strong><?= $item['soLuong'] ?></strong> x <?= vnd($sp->gia) ?></small><br>
                <small style="color: <?= $sp->tonKho >= $item['soLuong'] ? '#059669' : '#dc2626' ?>; font-weight: 600;">
                  📦 Available stock: <?= $sp->tonKho ?>
                  <?php if ($item['soLuong'] > $sp->tonKho): ?>
                    <span style="color: #dc2626; font-weight: 700;">(⚠ Exceeds stock!)</span>
                  <?php endif; ?>
                </small>
              </div>
              <div style="font-weight: bold; color: #dc2626;">
                <?= vnd($item['thanhTien']) ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>

        <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: bold; color: #0f172a; padding-top: 5px;">
          <span>Total Payment:</span>
          <span style="color: #dc2626;"><?= vnd($tongTien) ?></span>
        </div>
        <p style="font-size: 0.8rem; color: #64748b; margin-top: 10px;">
          * Unit prices are locked and permanently recorded upon checkout in the order details table.
        </p>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
