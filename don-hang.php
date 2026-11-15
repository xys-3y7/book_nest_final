<?php
/**
 * don-hang.php - Manage and view user / admin orders
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/don-hang.php';

// Authentication required
yeuCauDangNhap();

$u = nguoiDung();
$db = db();

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_huy_don'])) {
    $cancelId = filter_var($_POST['don_id'] ?? '', FILTER_VALIDATE_INT);
    if ($cancelId) {
        try {
            $laAdmin = ($u['vai_tro'] === 'quan_tri');
            huyDonHang($cancelId, (int)$u['id'], $laAdmin);
            $_SESSION['flash'] = "Order #$cancelId has been cancelled and stock restored successfully.";
        } catch (Throwable $e) {
            $_SESSION['flash_loi'] = $e->getMessage();
        }
    }
    header('Location: ' . ($_POST['redirect'] ?? 'don-hang.php'));
    exit;
}

$idDon = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

// Case 1: View order details
if ($idDon) {
    $st = $db->prepare(
        'SELECT dh.*, nd.ho_ten, nd.email 
         FROM don_hang dh 
         JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
         WHERE dh.id = ?'
    );
    $st->execute([$idDon]);
    $don = $st->fetch();

    // Data ownership security check
    if (!$don || ($u['vai_tro'] !== 'quan_tri' && (int)$don['nguoi_dung_id'] !== (int)$u['id'])) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }

    $stItems = $db->prepare(
        'SELECT ctd.*, sp.ten, sp.anh 
         FROM chi_tiet_don ctd 
         JOIN san_pham sp ON ctd.san_pham_id = sp.id 
         WHERE ctd.don_hang_id = ?'
    );
    $stItems->execute([$idDon]);
    $items = $stItems->fetchAll();

    $tieuDe = 'Order Details #' . $idDon;
    $trang = 'don-hang';
    require __DIR__ . '/inc/header.php';
    ?>
    <main class="noi-dung-chinh khung-chua" style="max-width: 850px; margin: 30px auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0; color: #1e3b6d;">Order Details #<?= $idDon ?></h1>
        <a href="don-hang.php" class="nut nut--vien">&larr; Back to Orders</a>
      </div>

      <?php if (!empty($_SESSION['flash'])): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;" aria-live="polite">
          <strong>✓ Notice:</strong> <?= e($_SESSION['flash']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
      <?php endif; ?>

      <div style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 15px;">
          <div>
            <p style="margin: 3px 0;"><strong>Order Date:</strong> <?= e($don['ngay_tao']) ?></p>
            <p style="margin: 3px 0;"><strong>Customer:</strong> <?= e($don['ho_ten']) ?> (<code><?= e($don['email']) ?></code>)</p>
            <p style="margin: 3px 0;"><strong>Phone:</strong> <?= e($don['so_dien_thoai']) ?></p>
            <p style="margin: 3px 0;"><strong>Shipping Address:</strong> <?= e($don['dia_chi']) ?></p>
            <?php if (!empty($don['ghi_chu'])): ?>
              <p style="margin: 3px 0; color: #64748b;"><strong>Notes:</strong> <?= e($don['ghi_chu']) ?></p>
            <?php endif; ?>
          </div>
          <div style="text-align: right;">
            <p style="margin: 3px 0;"><strong>Status:</strong>
              <?php
              $nhanTrangThai = [
                  'cho_xu_ly' => '<span style="background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:4px;font-weight:bold;">Pending</span>',
                  'dang_giao' => '<span style="background:#e0f2fe;color:#0369a1;padding:4px 10px;border-radius:4px;font-weight:bold;">Shipping</span>',
                  'da_giao'   => '<span style="background:#ecfdf5;color:#065f46;padding:4px 10px;border-radius:4px;font-weight:bold;">Delivered</span>',
                  'da_huy'    => '<span style="background:#fee2e2;color:#991b1b;padding:4px 10px;border-radius:4px;font-weight:bold;">Cancelled</span>',
              ];
              echo $nhanTrangThai[$don['trang_thai']] ?? e($don['trang_thai']);
              ?>
            </p>
            <p style="margin: 3px 0; color: #475569; font-size: 0.88rem;">
              <strong>Payment:</strong> <?= e($don['phuong_thuc_tt'] ?? 'COD') ?> (<?= $don['trang_thai_tt'] === 'da_thanh_toan' ? 'Paid' : 'Unpaid' ?>)
            </p>
            <p style="margin: 8px 0 0 0; font-size: 1.25rem; font-weight: bold; color: #dc2626;">
              Total: <?= vnd($don['tong_tien']) ?>
            </p>
            <?php if ($don['trang_thai'] === 'cho_xu_ly'): ?>
              <form method="POST" action="don-hang.php" onsubmit="return confirm('Are you sure you want to cancel this order? Stock will be restored.');" style="margin-top: 10px;">
                <?= csrfField() ?>
                <input type="hidden" name="action_huy_don" value="1">
                <input type="hidden" name="don_id" value="<?= $don['id'] ?>">
                <input type="hidden" name="redirect" value="don-hang.php?id=<?= $don['id'] ?>">
                <button type="submit" class="nut" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:6px 14px;border-radius:6px;font-weight:bold;font-size:0.85rem;">
                  Cancel Order
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <h3 style="margin: 0 0 12px 0;">Purchased Books</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
          <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
              <th style="padding: 10px;">Cover</th>
              <th style="padding: 10px;">Textbook Title</th>
              <th style="padding: 10px; width: 120px;">Price</th>
              <th style="padding: 10px; width: 80px; text-align: center;">Qty</th>
              <th style="padding: 10px; width: 130px; text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px; width: 60px;">
                  <img src="<?= e(anhSach($item['anh'])) ?>" alt="Cover" width="45" height="55" style="border-radius: 4px; object-fit: cover;">
                </td>
                <td style="padding: 10px;">
                  <a href="chi-tiet.php?id=<?= $item['san_pham_id'] ?>" style="font-weight: bold; color: #1e40af; text-decoration: none;">
                    <?= e($item['ten']) ?>
                  </a>
                </td>
                <td style="padding: 10px;"><?= vnd($item['don_gia']) ?></td>
                <td style="padding: 10px; text-align: center; font-weight: bold;"><?= $item['so_luong'] ?></td>
                <td style="padding: 10px; text-align: right; font-weight: bold; color: #dc2626;">
                  <?= vnd($item['don_gia'] * $item['so_luong']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

// Case 2: List orders
$laQuanTri = $u['vai_tro'] === 'quan_tri';

if ($laQuanTri) {
    $st = $db->query(
        'SELECT dh.*, nd.ho_ten, nd.email 
         FROM don_hang dh 
         JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
         ORDER BY dh.ngay_tao DESC, dh.id DESC'
    );
} else {
    $st = $db->prepare(
        'SELECT dh.*, nd.ho_ten, nd.email 
         FROM don_hang dh 
         JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
         WHERE dh.nguoi_dung_id = ? 
         ORDER BY dh.ngay_tao DESC, dh.id DESC'
    );
    $st->execute([$u['id']]);
}
$danhSachDon = $st->fetchAll();

$tieuDe = $laQuanTri ? 'System Orders' : 'My Orders';
$trang = 'don-hang';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d;"><?= $laQuanTri ? 'All Orders Management' : 'My Orders' ?></h1>
      <p style="margin: 5px 0 0 0; color: #64748b;">
        <?= $laQuanTri ? 'Track and manage orders across the BookNest platform.' : 'View history and tracking status of your textbook orders.' ?>
      </p>
    </div>
    <a href="danh-sach.php" class="nut nut--chinh">+ Continue Shopping</a>
  </div>

  <?php if (empty($danhSachDon)): ?>
    <div style="text-align: center; padding: 50px 20px; background: #ffffff; border-radius: 8px; border: 1px dashed #cbd5e1;">
      <p style="font-size: 3rem; margin: 0 0 10px 0;">📦</p>
      <h2>You have no orders yet</h2>
      <p style="color: #64748b; margin-bottom: 20px;">Browse our catalog and order the textbooks you need today!</p>
      <a href="danh-sach.php" class="nut nut--chinh">Explore Book Catalog</a>
    </div>
  <?php else: ?>
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
            <th style="padding: 12px 15px; width: 80px;">Order ID</th>
            <th style="padding: 12px 15px; width: 160px;">Date</th>
            <?php if ($laQuanTri): ?>
              <th style="padding: 12px 15px;">Customer</th>
            <?php endif; ?>
            <th style="padding: 12px 15px;">Shipping Address</th>
            <th style="padding: 12px 15px; width: 130px;">Total</th>
            <th style="padding: 12px 15px; width: 130px;">Status</th>
            <th style="padding: 12px 15px; width: 120px; text-align: center;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($danhSachDon as $d): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 12px 15px; font-weight: bold; color: #1e40af;">
                #<?= $d['id'] ?>
              </td>
              <td style="padding: 12px 15px; font-size: 0.85rem; color: #64748b;">
                <?= e($d['ngay_tao']) ?>
              </td>
              <?php if ($laQuanTri): ?>
                <td style="padding: 12px 15px;">
                  <strong><?= e($d['ho_ten']) ?></strong><br>
                  <small style="color: #64748b;"><?= e($d['email']) ?></small>
                </td>
              <?php endif; ?>
              <td style="padding: 12px 15px; font-size: 0.9rem; color: #334155;">
                <?= e($d['dia_chi']) ?> (Phone: <?= e($d['so_dien_thoai']) ?>)
              </td>
              <td style="padding: 12px 15px; font-weight: bold; color: #dc2626;">
                <?= vnd($d['tong_tien']) ?>
              </td>
              <td style="padding: 12px 15px;">
                <?php
                $nhanTrangThai = [
                    'cho_xu_ly' => '<span style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold;">Pending</span>',
                    'dang_giao' => '<span style="background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold;">Shipping</span>',
                    'da_giao'   => '<span style="background:#ecfdf5;color:#065f46;padding:3px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold;">Delivered</span>',
                    'da_huy'    => '<span style="background:#fee2e2;color:#991b1b;padding:3px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold;">Cancelled</span>',
                ];
                echo $nhanTrangThai[$d['trang_thai']] ?? e($d['trang_thai']);
                ?>
              </td>
              <td style="padding: 12px 15px; text-align: center;">
                <a href="don-hang.php?id=<?= $d['id'] ?>" class="nut nut--vien" style="padding: 5px 12px; font-size: 0.85rem;">
                  Details &rarr;
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
