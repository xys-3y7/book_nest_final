<?php
/**
 * tai-khoan.php - Customer Account Dashboard
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 * Customer Role: Profile, Security, Orders, Wishlist, Reviews
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/don-hang.php';
require_once __DIR__ . '/inc/thanh-toan.php';

// Authentication required
$u = yeuCauDangNhap();

// If user is Seller or Admin, provide convenient link to their dashboard
$laChuSach = ($u['vai_tro'] === 'chu_sach');
$laAdmin = ($u['vai_tro'] === 'quan_tri');

$db = db();
$uid = (int)$u['id'];

$thongBao = $_SESSION['flash'] ?? '';
$thongBaoLoi = $_SESSION['flash_loi'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_loi']);

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_cap_nhat_ho_so'])) {
    $hoTen = trim(chuoi($_POST['ho_ten'] ?? ''));
    $soDienThoai = trim(chuoi($_POST['so_dien_thoai'] ?? ''));
    $diaChi = trim(chuoi($_POST['dia_chi'] ?? ''));

    if (mb_strlen($hoTen) < 2) {
        $thongBaoLoi = 'Full name must be at least 2 characters.';
    } elseif ($soDienThoai !== '' && !preg_match('/^(0|\+84)[0-9]{9,10}$/', $soDienThoai)) {
        $thongBaoLoi = 'Invalid phone number format.';
    } else {
        $st = $db->prepare('UPDATE nguoi_dung SET ho_ten = ?, so_dien_thoai = ?, dia_chi = ? WHERE id = ?');
        $st->execute([$hoTen, $soDienThoai, $diaChi, $uid]);

        // Also update or add to address book
        if ($diaChi !== '') {
            $stAddr = $db->prepare('INSERT INTO so_dia_chi (nguoi_dung_id, ho_ten, so_dien_thoai, dia_chi, mac_dinh) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE dia_chi = VALUES(dia_chi), so_dien_thoai = VALUES(so_dien_thoai)');
            $stAddr->execute([$uid, $hoTen, $soDienThoai, $diaChi]);
        }

        $_SESSION['flash'] = 'Your profile and delivery information have been updated successfully!';
        header('Location: tai-khoan.php?tab=profile');
        exit;
    }
}

// Handle Change Password POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_doi_mat_khau'])) {
    $mkCu = chuoi($_POST['mat_khau_cu'] ?? '');
    $mkMoi = chuoi($_POST['mat_khau_moi'] ?? '');
    $xacNhan = chuoi($_POST['xac_nhan'] ?? '');

    $stCheck = $db->prepare('SELECT mat_khau FROM nguoi_dung WHERE id = ?');
    $stCheck->execute([$uid]);
    $matKhauHienTai = $stCheck->fetchColumn();

    if (!password_verify($mkCu, $matKhauHienTai)) {
        $thongBaoLoi = 'Current password is incorrect.';
    } elseif (mb_strlen($mkMoi) < 8) {
        $thongBaoLoi = 'New password must be at least 8 characters.';
    } elseif ($mkMoi !== $xacNhan) {
        $thongBaoLoi = 'Password confirmation does not match.';
    } else {
        $hash = password_hash($mkMoi, PASSWORD_DEFAULT);
        $stUp = $db->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?');
        $stUp->execute([$hash, $uid]);

        $_SESSION['flash'] = 'Your password has been updated successfully!';
        header('Location: tai-khoan.php?tab=security');
        exit;
    }
}

// Handle Order Cancellation POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_huy_don'])) {
    $donId = filter_var($_POST['don_id'] ?? '', FILTER_VALIDATE_INT);
    if ($donId) {
        try {
            huyDonHang($donId, $uid, false);
            $_SESSION['flash'] = "Order #$donId has been successfully cancelled and stock restored.";
        } catch (Throwable $e) {
            $_SESSION['flash_loi'] = $e->getMessage();
        }
    }
    header('Location: tai-khoan.php?tab=orders');
    exit;
}

// Fetch active customer statistics
$stDemDon = $db->prepare('SELECT COUNT(*) FROM don_hang WHERE nguoi_dung_id = ?');
$stDemDon->execute([$uid]);
$tongDon = (int)$stDemDon->fetchColumn();

$stDemYeuThich = $db->prepare('SELECT COUNT(*) FROM yeu_thich WHERE nguoi_dung_id = ?');
$stDemYeuThich->execute([$uid]);
$tongYeuThich = (int)$stDemYeuThich->fetchColumn();

$stDemReview = $db->prepare('SELECT COUNT(*) FROM danh_gia WHERE nguoi_dung_id = ?');
$stDemReview->execute([$uid]);
$tongReview = (int)$stDemReview->fetchColumn();

// Fetch customer payments count
$stPayCount = $db->prepare('SELECT COUNT(*) FROM payments WHERE user_id = ?');
$stPayCount->execute([$uid]);
$tongThanhToan = (int)$stPayCount->fetchColumn();

// Fetch customer payments
$stMyPayments = $db->prepare('
    SELECT p.*, dh.tong_tien, dh.ngay_tao as order_date 
    FROM payments p 
    JOIN don_hang dh ON p.order_id = dh.id 
    WHERE p.user_id = ? 
    ORDER BY p.payment_id DESC
');
$stMyPayments->execute([$uid]);
$dsThanhToan = $stMyPayments->fetchAll();

// Fetch customer orders
$stOrders = $db->prepare('SELECT * FROM don_hang WHERE nguoi_dung_id = ? ORDER BY ngay_tao DESC, id DESC');
$stOrders->execute([$uid]);
$dsDonHang = $stOrders->fetchAll();

// Fetch wishlist books
$stWishlist = $db->prepare(
    'SELECT sp.*, dm.ten AS ten_danh_muc, yt.ngay_tao AS ngay_thich
     FROM yeu_thich yt
     JOIN san_pham sp ON yt.san_pham_id = sp.id
     JOIN danh_muc dm ON sp.danh_muc_id = dm.id
     WHERE yt.nguoi_dung_id = ?
     ORDER BY yt.ngay_tao DESC'
);
$stWishlist->execute([$uid]);
$dsWishlist = $stWishlist->fetchAll();

// Fetch customer reviews
$stReviews = $db->prepare(
    'SELECT dg.*, sp.ten AS ten_sach, sp.anh AS anh_sach
     FROM danh_gia dg
     JOIN san_pham sp ON dg.san_pham_id = sp.id
     WHERE dg.nguoi_dung_id = ?
     ORDER BY dg.ngay_tao DESC'
);
$stReviews->execute([$uid]);
$dsReviews = $stReviews->fetchAll();

// Active tab
$tabHienTai = $_GET['tab'] ?? 'overview';

$tieuDe = 'Customer Dashboard - My Account';
$trang = 'tai-khoan';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="padding-top: 25px; padding-bottom: 50px;">
  
  <!-- Customer Welcome Banner -->
  <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3b6d 100%); color: #ffffff; padding: 26px 30px; border-radius: 16px; margin-bottom: 28px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div style="display: flex; align-items: center; gap: 18px;">
      <div style="width: 60px; height: 60px; border-radius: 50%; background: #f97316; color: #ffffff; font-size: 1.8rem; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);">
        <?= mb_strtoupper(mb_substr($u['ho_ten'], 0, 1)) ?>
      </div>
      <div>
        <span style="background: rgba(255,255,255,0.15); color: #ffffff; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px;">
          <?= $u['vai_tro'] === 'quan_tri' ? '👑 Administrator' : ($u['vai_tro'] === 'chu_sach' ? '🏪 Book Seller' : '👤 Customer Account') ?>
        </span>
        <h1 style="color: #ffffff; margin: 6px 0 2px 0; font-size: 1.7rem; font-weight: 800;">
          Welcome back, <?= e($u['ho_ten']) ?>!
        </h1>
        <p style="margin: 0; color: #94a3b8; font-size: 0.88rem;">
          <?= e($u['email']) ?> &bull; Member since <?= date('M Y', strtotime($u['ngay_tao'])) ?>
        </p>
      </div>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <?php if ($laAdmin): ?>
        <a href="quan-tri.php" class="nut" style="background:#dc2626;color:#fff;border-radius:8px;padding:9px 16px;text-decoration:none;font-weight:700;">
          ⚙️ Admin Dashboard
        </a>
      <?php elseif ($laChuSach): ?>
        <a href="chu-sach.php" class="nut" style="background:#ea580c;color:#fff;border-radius:8px;padding:9px 16px;text-decoration:none;font-weight:700;">
          🏪 Seller Dashboard
        </a>
      <?php endif; ?>
      <a href="danh-sach.php" class="nut nut--chinh" style="border-radius:8px;padding:9px 18px;text-decoration:none;font-weight:700;">
        📚 Browse Catalog
      </a>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if ($thongBao): ?>
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 10px; margin-bottom: 22px; font-weight: 600;" aria-live="polite">
      ✓ <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <?php if ($thongBaoLoi): ?>
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: 10px; margin-bottom: 22px; font-weight: 600;" aria-live="assertive">
      ⚠ <?= e($thongBaoLoi) ?>
    </div>
  <?php endif; ?>

  <!-- KPI Statistics Cards -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 28px;">
    
    <div style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 4px;">Total Orders</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #1e3b6d;">📦 <?= $tongDon ?></div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 4px;">Orders placed on BookNest</div>
    </div>

    <div style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 4px;">Wishlist Items</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #ea580c;">❤️ <?= $tongYeuThich ?></div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 4px;">Books saved for later</div>
    </div>

    <div style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 4px;">Reviews &amp; Ratings</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #059669;">⭐ <?= $tongReview ?></div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 4px;">Feedback shared with peers</div>
    </div>

    <div style="background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 4px;">Account Status</div>
      <div style="font-size: 1.4rem; font-weight: 800; color: #10b981; margin-top: 6px;">✓ Active</div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 6px;">Verified Student Account</div>
    </div>

  </div>

  <!-- Layout: Sidebar Nav + Main Tab Content -->
  <div style="display: grid; grid-template-columns: 240px 1fr; gap: 24px; align-items: flex-start;">
    
    <!-- Sidebar Navigation -->
    <aside style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <nav style="display: flex; flex-direction: column; gap: 4px;">
        <a href="tai-khoan.php?tab=overview" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'overview' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'overview' ? '#fff7ed' : 'transparent' ?>;">
          <span>📊</span> Overview
        </a>
        <a href="tai-khoan.php?tab=orders" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'orders' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'orders' ? '#fff7ed' : 'transparent' ?>;">
          <span>📦</span> My Orders (<?= $tongDon ?>)
        </a>
        <a href="tai-khoan.php?tab=payments" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'payments' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'payments' ? '#fff7ed' : 'transparent' ?>;">
          <span>💳</span> Payments &amp; Receipts (<?= $tongThanhToan ?>)
        </a>
        <a href="tai-khoan.php?tab=wishlist" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'wishlist' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'wishlist' ? '#fff7ed' : 'transparent' ?>;">
          <span>❤️</span> Wishlist (<?= $tongYeuThich ?>)
        </a>
        <a href="tai-khoan.php?tab=reviews" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'reviews' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'reviews' ? '#fff7ed' : 'transparent' ?>;">
          <span>⭐</span> My Reviews (<?= $tongReview ?>)
        </a>
        <a href="tai-khoan.php?tab=profile" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'profile' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'profile' ? '#fff7ed' : 'transparent' ?>;">
          <span>👤</span> Personal Info &amp; Address
        </a>
        <a href="tai-khoan.php?tab=security" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'security' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'security' ? '#fff7ed' : 'transparent' ?>;">
          <span>🔐</span> Password &amp; Security
        </a>
      </nav>
    </aside>

    <!-- Main Tab Content Area -->
    <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 28px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">

      <?php if ($tabHienTai === 'overview'): ?>
        <!-- TAB 1: OVERVIEW -->
        <h2 style="margin: 0 0 18px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Account Overview
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
            <h3 style="margin: 0 0 10px 0; font-size: 1rem; color: #1e3b6d;">Default Delivery Address</h3>
            <p style="margin: 0 0 6px 0; color: #334155; font-size: 0.95rem; font-weight: 600;"><?= e($u['ho_ten']) ?></p>
            <p style="margin: 0 0 6px 0; color: #64748b; font-size: 0.88rem;">📞 <?= e($u['so_dien_thoai'] ?: 'Not provided yet') ?></p>
            <p style="margin: 0 0 12px 0; color: #475569; font-size: 0.9rem; line-height: 1.4;">📍 <?= e($u['dia_chi'] ?: 'No address saved yet') ?></p>
            <a href="tai-khoan.php?tab=profile" style="color: #ea580c; font-weight: 700; font-size: 0.85rem; text-decoration: none;">Edit Delivery Info &rarr;</a>
          </div>

          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
            <h3 style="margin: 0 0 10px 0; font-size: 1rem; color: #1e3b6d;">Fast Shortcuts</h3>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <a href="danh-sach.php" style="color: #2563eb; text-decoration: none; font-size: 0.9rem; font-weight: 600;">🔍 Search for textbooks by major</a>
              <a href="gio-hang.php" style="color: #2563eb; text-decoration: none; font-size: 0.9rem; font-weight: 600;">🛒 View shopping cart</a>
              <a href="yeu-thich.php" style="color: #2563eb; text-decoration: none; font-size: 0.9rem; font-weight: 600;">❤️ View saved wishlist</a>
              <a href="lien-he.php" style="color: #2563eb; text-decoration: none; font-size: 0.9rem; font-weight: 600;">💬 Student Support Hotline</a>
            </div>
          </div>
        </div>

        <h3 style="margin: 25px 0 14px 0; font-size: 1.15rem; color: #0f172a;">Recent Orders</h3>
        <?php if (empty($dsDonHang)): ?>
          <p style="color: #64748b; font-style: italic;">You have not placed any orders yet. <a href="danh-sach.php" style="color:#ea580c;font-weight:700;">Explore our textbooks catalog &rarr;</a></p>
        <?php else: ?>
          <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
              <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1;">
                <th style="padding: 10px 12px;">Order ID</th>
                <th style="padding: 10px 12px;">Date</th>
                <th style="padding: 10px 12px;">Total</th>
                <th style="padding: 10px 12px;">Status</th>
                <th style="padding: 10px 12px; text-align: center;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($dsDonHang, 0, 5) as $d): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <td style="padding: 10px 12px; font-weight: 700; color: #1e3b6d;">#<?= $d['id'] ?></td>
                  <td style="padding: 10px 12px; color: #64748b;"><?= e(date('d/m/Y H:i', strtotime($d['ngay_tao']))) ?></td>
                  <td style="padding: 10px 12px; font-weight: 700; color: #dc2626;"><?= vnd($d['tong_tien']) ?></td>
                  <td style="padding: 10px 12px;">
                    <?php
                    $badges = [
                        'cho_xu_ly' => '<span style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:4px;font-weight:bold;font-size:0.8rem;">Pending</span>',
                        'dang_giao' => '<span style="background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-weight:bold;font-size:0.8rem;">Shipping</span>',
                        'da_giao'   => '<span style="background:#ecfdf5;color:#065f46;padding:3px 8px;border-radius:4px;font-weight:bold;font-size:0.8rem;">Delivered</span>',
                        'da_huy'    => '<span style="background:#fee2e2;color:#991b1b;padding:3px 8px;border-radius:4px;font-weight:bold;font-size:0.8rem;">Cancelled</span>',
                    ];
                    echo $badges[$d['trang_thai']] ?? e($d['trang_thai']);
                    ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: center;">
                    <a href="don-hang.php?id=<?= $d['id'] ?>" class="nut nut--vien" style="padding: 4px 10px; font-size: 0.82rem;">Details</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'orders'): ?>
        <!-- TAB 2: MY ORDERS & TRACKING -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          My Orders &amp; Tracking
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Track your package deliveries, view order history, or cancel eligible pending orders.
        </p>

        <?php if (empty($dsDonHang)): ?>
          <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <p style="font-size: 2.5rem; margin: 0 0 10px 0;">📦</p>
            <h3>No orders found</h3>
            <p style="color: #64748b;">Browse textbooks and place your first order today!</p>
            <a href="danh-sach.php" class="nut nut--chinh" style="padding: 8px 16px;">Browse Textbooks</a>
          </div>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($dsDonHang as $d): ?>
              <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                  <div>
                    <span style="font-weight: 800; color: #1e3b6d; font-size: 1.05rem;">Order #<?= $d['id'] ?></span>
                    <span style="color: #64748b; font-size: 0.85rem; margin-left: 10px;">Placed on <?= e($d['ngay_tao']) ?></span>
                  </div>
                  <div>
                    <?php
                    $badges = [
                        'cho_xu_ly' => '<span style="background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:6px;font-weight:bold;font-size:0.85rem;">Pending Confirmation</span>',
                        'dang_giao' => '<span style="background:#e0f2fe;color:#0369a1;padding:4px 10px;border-radius:6px;font-weight:bold;font-size:0.85rem;">In Transit / Shipping</span>',
                        'da_giao'   => '<span style="background:#ecfdf5;color:#065f46;padding:4px 10px;border-radius:6px;font-weight:bold;font-size:0.85rem;">Delivered</span>',
                        'da_huy'    => '<span style="background:#fee2e2;color:#991b1b;padding:4px 10px;border-radius:6px;font-weight:bold;font-size:0.85rem;">Cancelled</span>',
                    ];
                    echo $badges[$d['trang_thai']] ?? e($d['trang_thai']);
                    ?>
                  </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                  <div style="font-size: 0.88rem; color: #475569;">
                    <div>📍 <strong>Address:</strong> <?= e($d['dia_chi']) ?></div>
                    <div>📞 <strong>Phone:</strong> <?= e($d['so_dien_thoai']) ?></div>
                    <div>💳 <strong>Payment:</strong> <?= e($d['phuong_thuc_tt'] ?? 'COD') ?> &bull; <strong style="color: #dc2626;"><?= vnd($d['tong_tien']) ?></strong></div>
                  </div>

                  <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="don-hang.php?id=<?= $d['id'] ?>" class="nut nut--vien" style="padding: 7px 14px; font-size: 0.88rem;">
                      View Details &rarr;
                    </a>

                    <?php if ($d['trang_thai'] === 'cho_xu_ly'): ?>
                      <form method="POST" action="tai-khoan.php?tab=orders" onsubmit="return confirm('Are you sure you want to cancel Order #<?= $d['id'] ?>? Stock will be restored.');" style="margin: 0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action_huy_don" value="1">
                        <input type="hidden" name="don_id" value="<?= $d['id'] ?>">
                        <button type="submit" class="nut" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:7px 12px;font-size:0.85rem;border-radius:6px;font-weight:700;">
                          Cancel Order
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'payments'): ?>
        <!-- TAB: PAYMENTS & RECEIPTS -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          My Payment History &amp; Receipts (<?= count($dsThanhToan) ?>)
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Track all payment transactions, check receipt verification status, and download official digital receipts.
        </p>

        <?php if (empty($dsThanhToan)): ?>
          <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <p style="font-size: 2.5rem; margin: 0 0 10px 0;">💳</p>
            <h3 style="margin: 0 0 6px 0; color: #0f172a;">No payment transactions recorded</h3>
            <p style="color: #64748b; margin-bottom: 15px; font-size: 0.9rem;">Your payment receipts and transaction records will appear here.</p>
            <a href="danh-sach.php" class="nut nut--chinh">Shop Textbooks</a>
          </div>
        <?php else: ?>
          <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
              <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                  <th style="padding: 10px 12px;">Transaction ID</th>
                  <th style="padding: 10px 12px; width: 80px;">Order</th>
                  <th style="padding: 10px 12px; width: 140px;">Method</th>
                  <th style="padding: 10px 12px; width: 110px; text-align: right;">Amount</th>
                  <th style="padding: 10px 12px; width: 110px;">Status</th>
                  <th style="padding: 10px 12px; width: 130px;">Date</th>
                  <th style="padding: 10px 12px; text-align: center; width: 130px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dsThanhToan as $pt): ?>
                  <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 12px;">
                      <code style="font-weight: 700; color: #1e40af;"><?= e($pt['transaction_id'] ?: 'TXN-BN-' . $pt['payment_id']) ?></code>
                      <?php if (!empty($pt['payment_proof'])): ?>
                        <div style="margin-top: 3px;">
                          <a href="uploads/receipts/<?= e($pt['payment_proof']) ?>" target="_blank" style="color: #0284c7; font-size: 0.78rem; text-decoration: underline;">
                            📎 View Proof
                          </a>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; font-weight: 600;">
                      <a href="don-hang.php?id=<?= $pt['order_id'] ?>" style="color: #0f172a; text-decoration: none;">
                        #<?= $pt['order_id'] ?>
                      </a>
                    </td>
                    <td style="padding: 10px 12px;">
                      <?= nhanPhuongThucTT($pt['payment_method']) ?>
                    </td>
                    <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #dc2626;">
                      <?= vnd($pt['amount']) ?>
                    </td>
                    <td style="padding: 10px 12px;">
                      <?= huyHieuTrangThaiTT($pt['payment_status']) ?>
                    </td>
                    <td style="padding: 10px 12px; color: #64748b; font-size: 0.82rem;">
                      <?= $pt['payment_date'] ? date('d/m/Y H:i', strtotime($pt['payment_date'])) : date('d/m/Y H:i', strtotime($pt['created_at'])) ?>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <?php if ($pt['payment_status'] === 'paid'): ?>
                        <a href="thanh-toan.php?action=receipt&order_id=<?= $pt['order_id'] ?>" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.8rem;" title="Print Digital Receipt">
                          🧾 Receipt
                        </a>
                      <?php else: ?>
                        <a href="thanh-toan.php?order_id=<?= $pt['order_id'] ?>" class="nut nut--chinh" style="padding: 4px 8px; font-size: 0.8rem;" title="Pay or Upload Receipt">
                          Pay / Proof &rarr;
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'wishlist'): ?>
        <!-- TAB 3: WISHLIST -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          My Saved Wishlist (<?= count($dsWishlist) ?>)
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Books you've saved to buy later or monitor for price drops.
        </p>

        <?php if (empty($dsWishlist)): ?>
          <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <p style="font-size: 2.5rem; margin: 0 0 10px 0;">❤️</p>
            <h3>Your wishlist is empty</h3>
            <p style="color: #64748b;">Browse textbooks and click the heart icon to save items.</p>
            <a href="danh-sach.php" class="nut nut--chinh" style="padding: 8px 16px;">Browse Textbooks</a>
          </div>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
            <?php foreach ($dsWishlist as $sp): ?>
              <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; background: #ffffff; display: flex; flex-direction: column;">
                <a href="chi-tiet.php?id=<?= $sp['id'] ?>" style="text-decoration: none; text-align: center;">
                  <img src="<?= e(anhSach($sp['anh'])) ?>" alt="Cover" style="width: 100%; height: 180px; object-fit: contain; border-radius: 6px; margin-bottom: 10px;">
                </a>
                <h4 style="margin: 0 0 6px 0; font-size: 0.92rem; line-height: 1.3;">
                  <a href="chi-tiet.php?id=<?= $sp['id'] ?>" style="color: #0f172a; text-decoration: none; font-weight: 700;">
                    <?= e($sp['ten']) ?>
                  </a>
                </h4>
                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 8px;">
                  <?= e($sp['ten_danh_muc']) ?>
                </div>
                <div style="font-size: 1.05rem; font-weight: 800; color: #dc2626; margin-bottom: 12px; margin-top: auto;">
                  <?= vnd($sp['gia']) ?>
                </div>
                
                <div style="display: flex; gap: 6px;">
                  <form method="POST" action="gio-hang.php" style="margin: 0; flex: 1;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $sp['id'] ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="nut nut--chinh" style="width: 100%; padding: 6px 8px; font-size: 0.8rem;">
                      🛒 Cart
                    </button>
                  </form>
                  <a href="yeu-thich.php?action=toggle&id=<?= $sp['id'] ?>&redirect=tai-khoan.php?tab=wishlist" class="nut nut--vien" style="padding: 6px 10px; font-size: 0.8rem;" title="Remove from wishlist">
                    ✕
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'reviews'): ?>
        <!-- TAB 4: MY REVIEWS -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          My Reviews &amp; Ratings (<?= count($dsReviews) ?>)
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Feedback and star ratings you have submitted for textbooks.
        </p>

        <?php if (empty($dsReviews)): ?>
          <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <p style="font-size: 2.5rem; margin: 0 0 10px 0;">⭐</p>
            <h3>No reviews written yet</h3>
            <p style="color: #64748b;">Visit any textbook details page to submit a rating and review!</p>
            <a href="danh-sach.php" class="nut nut--chinh" style="padding: 8px 16px;">Browse Textbooks</a>
          </div>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($dsReviews as $rev): ?>
              <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; background: #ffffff; display: flex; gap: 16px; align-items: flex-start;">
                <img src="<?= e(anhSach($rev['anh_sach'])) ?>" alt="Cover" style="width: 60px; height: 80px; object-fit: cover; border-radius: 6px; flex-shrink: 0;">
                <div style="flex: 1;">
                  <h4 style="margin: 0 0 4px 0; font-size: 1rem;">
                    <a href="chi-tiet.php?id=<?= $rev['san_pham_id'] ?>" style="color: #1e3b6d; text-decoration: none; font-weight: 700;">
                      <?= e($rev['ten_sach']) ?>
                    </a>
                  </h4>
                  <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="color: #f59e0b; font-size: 1rem;">
                      <?= str_repeat('★', (int)$rev['so_sao']) . str_repeat('☆', 5 - (int)$rev['so_sao']) ?>
                    </span>
                    <span style="font-size: 0.8rem; color: #64748b;">
                      <?= e(date('M d, Y', strtotime($rev['ngay_tao']))) ?>
                    </span>
                  </div>
                  <p style="margin: 0; color: #334155; font-size: 0.92rem; line-height: 1.45;">
                    <?= nl2br(e($rev['noi_dung'])) ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'profile'): ?>
        <!-- TAB 5: PROFILE & DELIVERY ADDRESS -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Personal Info &amp; Delivery Address
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Update your contact details and default delivery address for faster checkout.
        </p>

        <form method="POST" action="tai-khoan.php?tab=profile" style="max-width: 580px;">
          <?= csrfField() ?>
          <input type="hidden" name="action_cap_nhat_ho_so" value="1">

          <div style="margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Full Name <span style="color:#dc2626;">*</span></label>
            <input type="text" name="ho_ten" value="<?= e($u['ho_ten']) ?>" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Email Address</label>
            <input type="email" value="<?= e($u['email']) ?>" disabled style="width: 100%; padding: 10px 14px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; color: #64748b; box-sizing: border-box;">
            <small style="color: #64748b;">Email address cannot be modified as it is tied to your login identity.</small>
          </div>

          <div style="margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Phone Number</label>
            <input type="tel" name="so_dien_thoai" value="<?= e($u['so_dien_thoai'] ?? '') ?>" placeholder="e.g. 0905123456" style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 24px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Default Shipping Address (Dormitory / Room / Home)</label>
            <textarea name="dia_chi" rows="3" placeholder="Enter your detailed shipping address..." style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;"><?= e($u['dia_chi'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="nut nut--chinh" style="padding: 11px 24px; font-size: 0.95rem; font-weight: 700; border-radius: 8px;">
            Save Changes
          </button>
        </form>

      <?php elseif ($tabHienTai === 'security'): ?>
        <!-- TAB 6: SECURITY & PASSWORD -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Password &amp; Security
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Ensure your account uses a secure password of at least 8 characters.
        </p>

        <form method="POST" action="tai-khoan.php?tab=security" style="max-width: 520px;">
          <?= csrfField() ?>
          <input type="hidden" name="action_doi_mat_khau" value="1">

          <div style="margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Current Password <span style="color:#dc2626;">*</span></label>
            <input type="password" name="mat_khau_cu" required placeholder="Enter current password..." style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">New Password (At least 8 chars) <span style="color:#dc2626;">*</span></label>
            <input type="password" name="mat_khau_moi" required placeholder="Enter new secure password..." style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 24px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Confirm New Password <span style="color:#dc2626;">*</span></label>
            <input type="password" name="xac_nhan" required placeholder="Re-enter new password..." style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
          </div>

          <button type="submit" class="nut nut--chinh" style="padding: 11px 24px; font-size: 0.95rem; font-weight: 700; border-radius: 8px;">
            Update Password
          </button>
        </form>

      <?php endif; ?>

    </div>
  </div>

</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
