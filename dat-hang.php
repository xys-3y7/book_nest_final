<?php
/**
 * dat-hang.php - Trang xác nhận và đặt mua sách từ giỏ hàng trong giao dịch CSDL ACID
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 5 (Bảng 1): Phải đăng nhập, beginTransaction, commit, rollBack, UPDATE ton_kho >= ?, PRG + flash
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/don-hang.php';

// Yêu cầu người dùng phải đăng nhập trước khi tiến hành đặt hàng
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
    $_SESSION['flash_loi'] = 'Giỏ hàng của bạn đang trống. Vui lòng chọn sách trước khi đặt hàng.';
    header('Location: danh-sach.php');
    exit;
}

$du = [
    'dia_chi'       => '',
    'so_dien_thoai' => '',
    'ghi_chu'       => '',
];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['dia_chi']       = trim($_POST['dia_chi'] ?? '');
    $du['so_dien_thoai'] = trim($_POST['so_dien_thoai'] ?? '');
    $du['ghi_chu']       = trim($_POST['ghi_chu'] ?? '');

    if ($du['dia_chi'] === '' || mb_strlen($du['dia_chi']) < 5) {
        $loi['dia_chi'] = 'Vui lòng cung cấp địa chỉ nhận sách chi tiết (tối thiểu 5 ký tự).';
    }

    if ($du['so_dien_thoai'] === '' || !preg_match('/^(0|\+84)[0-9]{9,10}$/', $du['so_dien_thoai'])) {
        $loi['so_dien_thoai'] = 'Số điện thoại nhận hàng không hợp lệ (gồm 10-11 chữ số).';
    }

    if (empty($loi)) {
        try {
            // Thực thi lưu đơn hàng trong Transaction ACID (kiểm tra tồn kho nguyên tử)
            $donHangId = luuDonHang(
                (int)$u['id'],
                $gio->danhSach(),
                $du['dia_chi'],
                $du['so_dien_thoai'],
                $du['ghi_chu'] !== '' ? $du['ghi_chu'] : null
            );

            // Làm trống giỏ hàng sau khi đặt thành công
            $gio->xoaHet();

            // Mẫu thiết kế PRG (Post/Redirect/Get) - Chống đặt trùng khi người dùng bấm F5
            $_SESSION['flash'] = "Chúc mừng bạn đã đặt hàng thành công! Mã đơn hàng của bạn là #$donHangId.";
            header("Location: don-hang.php?id=$donHangId");
            exit;

        } catch (RuntimeException $e) {
            // Lỗi nghiệp vụ (hết hàng, rollback thành công)
            $loi['he_thong'] = $e->getMessage();
        } catch (Throwable $e) {
            error_log("Lỗi đặt hàng: " . $e->getMessage());
            $loi['he_thong'] = 'Đã xảy ra lỗi trong quá trình xử lý giao dịch. Đơn hàng chưa được lưu.';
        }
    }
}

$tieuDe = 'Xác nhận đặt sách';
$trang = 'gio-hang';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 900px; margin: 30px auto;">
  <h1>Xác nhận đặt hàng sách BookNest</h1>
  <p>Vui lòng kiểm tra lại danh sách sách trong giỏ và điền thông tin địa chỉ giao nhận.</p>

  <?php if (isset($loi['he_thong'])): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 6px; margin-bottom: 25px;" aria-live="assertive">
      <strong>⚠ Sự cố giao dịch:</strong> <?= e($loi['he_thong']) ?>
    </div>
  <?php endif; ?>

  <div style="display: flex; gap: 30px; flex-wrap: wrap;">
    <!-- Biểu mẫu địa chỉ nhận sách -->
    <div style="flex: 3; min-width: 320px; background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
      <h2 style="margin-top: 0; color: #1e3b6d; font-size: 1.3rem;">Thông tin giao nhận sách</h2>

      <form method="POST" action="dat-hang.php" novalidate>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: bold; margin-bottom: 5px;">Họ tên người nhận:</label>
          <input type="text" value="<?= e($u['ho_ten']) ?>" disabled style="width: 100%; padding: 9px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: bold; margin-bottom: 5px;">Email tài khoản:</label>
          <input type="text" value="<?= e($u['email']) ?>" disabled style="width: 100%; padding: 9px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 16px;">
          <label for="so_dien_thoai" style="display: block; font-weight: bold; margin-bottom: 5px;">
            Số điện thoại nhận hàng <span style="color:#dc2626;">*</span>
          </label>
          <input type="tel" id="so_dien_thoai" name="so_dien_thoai" value="<?= e($du['so_dien_thoai']) ?>" placeholder="Ví dụ: 0905123456" style="width: 100%; padding: 9px 12px; border: 1px solid <?= isset($loi['so_dien_thoai']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
          <?php if (isset($loi['so_dien_thoai'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['so_dien_thoai']) ?></p>
          <?php endif; ?>
        </div>

        <div style="margin-bottom: 16px;">
          <label for="dia_chi" style="display: block; font-weight: bold; margin-bottom: 5px;">
            Địa chỉ nhận sách (Ký túc xá / Nhà riêng) <span style="color:#dc2626;">*</span>
          </label>
          <textarea id="dia_chi" name="dia_chi" rows="3" placeholder="Nhập địa chỉ nhận sách cụ thể..." style="width: 100%; padding: 9px 12px; border: 1px solid <?= isset($loi['dia_chi']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;"><?= e($du['dia_chi']) ?></textarea>
          <?php if (isset($loi['dia_chi'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['dia_chi']) ?></p>
          <?php endif; ?>
        </div>

        <div style="margin-bottom: 25px;">
          <label for="ghi_chu" style="display: block; font-weight: bold; margin-bottom: 5px;">Ghi chú giao hàng (Tùy chọn):</label>
          <input type="text" id="ghi_chu" name="ghi_chu" value="<?= e($du['ghi_chu']) ?>" placeholder="Ví dụ: Giao vào buổi chiều sau giờ học..." style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
          <button type="submit" class="nut nut--chinh" style="padding: 12px 28px; font-size: 1.05rem;">
            ✓ Xác nhận đặt mua (<?= vnd($tongTien) ?>)
          </button>
          <a href="gio-hang.php" class="nut nut--vien">&larr; Sửa giỏ hàng</a>
        </div>
      </form>
    </div>

    <!-- Tóm tắt đơn hàng -->
    <div style="flex: 2; min-width: 260px;">
      <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="margin-top: 0; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px;">
          Tóm tắt đơn hàng (<?= $soMon ?> cuốn)
        </h3>

        <ul style="list-style: none; padding: 0; margin: 0 0 15px 0;">
          <?php foreach ($chiTietGio as $item): 
            $sp = $item['sanPham'];
          ?>
            <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: 0.9rem;">
              <div>
                <strong><?= e($sp->ten) ?></strong><br>
                <small style="color: #64748b;">Số lượng: <?= $item['soLuong'] ?> x <?= vnd($sp->gia) ?></small>
              </div>
              <div style="font-weight: bold; color: #dc2626;">
                <?= vnd($item['thanhTien']) ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>

        <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: bold; color: #0f172a; padding-top: 5px;">
          <span>Tổng thanh toán:</span>
          <span style="color: #dc2626;"><?= vnd($tongTien) ?></span>
        </div>
        <p style="font-size: 0.8rem; color: #64748b; margin-top: 10px;">
          * Đơn giá được chốt và lưu cố định tại thời điểm đặt trong bảng chi tiết đơn hàng.
        </p>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
