<?php
/**
 * chu-sach.php - Seller / Book Owner Dashboard
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 * 3-Role Model: Admin - Seller - Customer
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/thanh-toan.php';

// Authentication and Seller role required
$u = nguoiDung();
if (!$u) {
    header('Location: dang-nhap.php?yeu_cau=' . urlencode('chu-sach.php'));
    exit;
}

if ($u['vai_tro'] === 'quan_tri') {
    $_SESSION['flash_loi'] = 'Administrators oversee the entire system. The seller portal is reserved for Sellers.';
    header('Location: quan-tri.php');
    exit;
}

if ($u['vai_tro'] !== 'chu_sach') {
    ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI'], 'vai_tro' => $u['vai_tro']]);
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

$db = db();
$chuSachId = (int)$u['id'];

// Handle Seller Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_seller_profile'])) {
    $tenShop = trim(chuoi($_POST['ten_gian_hang'] ?? ''));
    $sdt = trim(chuoi($_POST['so_dien_thoai'] ?? ''));
    $moTa = trim(chuoi($_POST['mo_ta_shop'] ?? ''));
    $logo = trim(chuoi($_POST['logo_shop'] ?? ''));

    if ($tenShop === '') {
        $tenShop = $u['ho_ten'] . "'s Bookstore";
    }

    $stUp = $db->prepare('UPDATE nguoi_dung SET ten_gian_hang = ?, so_dien_thoai = ?, mo_ta_shop = ?, logo_shop = ? WHERE id = ?');
    $stUp->execute([$tenShop, $sdt, $moTa, $logo, $chuSachId]);

    $_SESSION['flash'] = 'Seller bookstore profile updated successfully!';
    header('Location: chu-sach.php?tab=profile');
    exit;
}

// Handle Order Status Update by Seller (Confirm / Ship)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_cap_nhat_don'])) {
    $donId = filter_var($_POST['don_id'] ?? '', FILTER_VALIDATE_INT);
    $trangThaiMoi = trim(chuoi($_POST['trang_thai'] ?? ''));
    $hopLe = ['dang_giao', 'da_giao'];

    if ($donId && in_array($trangThaiMoi, $hopLe, true)) {
        // Verify this order contains books owned by this seller
        $stCheck = $db->prepare('SELECT COUNT(*) FROM chi_tiet_don ct JOIN san_pham sp ON ct.san_pham_id = sp.id WHERE ct.don_hang_id = ? AND sp.chu_sach_id = ?');
        $stCheck->execute([$donId, $chuSachId]);
        if ((int)$stCheck->fetchColumn() > 0) {
            $stUp = $db->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ?');
            $stUp->execute([$trangThaiMoi, $donId]);
            $_SESSION['flash'] = "Order #$donId has been updated to '" . ($trangThaiMoi === 'dang_giao' ? 'In Transit / Shipping' : 'Delivered') . "'.";
        }
    }
    header('Location: chu-sach.php?tab=orders');
    exit;
}

// Handle Seller Payment Receipt Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_seller_duyet_tt'])) {
    $paymentId = filter_var($_POST['payment_id'] ?? '', FILTER_VALIDATE_INT);
    if ($paymentId) {
        $stCheck = $db->prepare('SELECT p.order_id FROM payments p JOIN chi_tiet_don ct ON p.order_id = ct.don_hang_id JOIN san_pham sp ON ct.san_pham_id = sp.id WHERE p.payment_id = ? AND sp.chu_sach_id = ?');
        $stCheck->execute([$paymentId, $chuSachId]);
        if ($stCheck->fetchColumn()) {
            duyetThanhToan($paymentId, (int)$chuSachId, 'Receipt verified and approved by Seller: ' . $u['ho_ten']);
            $_SESSION['flash'] = "Payment #$paymentId has been verified and marked as Paid.";
        } else {
            $_SESSION['flash_loi'] = 'Permission denied. This order does not contain your textbooks.';
        }
    }
    header('Location: chu-sach.php?tab=payments');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_seller_tuchoi_tt'])) {
    $paymentId = filter_var($_POST['payment_id'] ?? '', FILTER_VALIDATE_INT);
    $reason = trim(chuoi($_POST['rejection_reason'] ?? 'Invalid receipt or mismatched amount.'));
    if ($paymentId) {
        $stCheck = $db->prepare('SELECT p.order_id FROM payments p JOIN chi_tiet_don ct ON p.order_id = ct.don_hang_id JOIN san_pham sp ON ct.san_pham_id = sp.id WHERE p.payment_id = ? AND sp.chu_sach_id = ?');
        $stCheck->execute([$paymentId, $chuSachId]);
        if ($stCheck->fetchColumn()) {
            tuChoiThanhToan($paymentId, (int)$chuSachId, $reason);
            $_SESSION['flash'] = "Payment receipt for #$paymentId was rejected. Customer will be notified to re-upload.";
        } else {
            $_SESSION['flash_loi'] = 'Permission denied.';
        }
    }
    header('Location: chu-sach.php?tab=payments');
    exit;
}

// 1. KPI Statistics for Seller
$stDemSach = $db->prepare('SELECT COUNT(*) FROM san_pham WHERE chu_sach_id = ?');
$stDemSach->execute([$chuSachId]);
$tongSach = (int)$stDemSach->fetchColumn();

$stTonKho = $db->prepare('SELECT COALESCE(SUM(ton_kho), 0) FROM san_pham WHERE chu_sach_id = ?');
$stTonKho->execute([$chuSachId]);
$tongTonKho = (int)$stTonKho->fetchColumn();

$stDemDon = $db->prepare(
    'SELECT COUNT(DISTINCT dh.id) 
     FROM don_hang dh 
     JOIN chi_tiet_don ct ON dh.id = ct.don_hang_id 
     JOIN san_pham sp ON ct.san_pham_id = sp.id 
     WHERE sp.chu_sach_id = ?'
);
$stDemDon->execute([$chuSachId]);
$tongDon = (int)$stDemDon->fetchColumn();

$stDoanhThu = $db->prepare(
    'SELECT COALESCE(SUM(ct.so_luong), 0) AS tong_ban,
            COALESCE(SUM(ct.so_luong * ct.don_gia), 0) AS tong_doanh_thu
     FROM chi_tiet_don ct
     JOIN san_pham sp ON ct.san_pham_id = sp.id
     JOIN don_hang dh ON ct.don_hang_id = dh.id
     WHERE sp.chu_sach_id = ? AND dh.trang_thai != "da_huy"'
);
$stDoanhThu->execute([$chuSachId]);
$thongKeBan = $stDoanhThu->fetch() ?: ['tong_ban' => 0, 'tong_doanh_thu' => 0];

// 2. Books owned by this seller
$stSach = $db->prepare(
    'SELECT sp.*, dm.ten AS ten_danh_muc 
     FROM san_pham sp
     JOIN danh_muc dm ON sp.danh_muc_id = dm.id
     WHERE sp.chu_sach_id = ?
     ORDER BY sp.id DESC'
);
$stSach->execute([$chuSachId]);
$dsSach = $stSach->fetchAll();

// 3. Orders placed for this seller's books
$stDonHang = $db->prepare(
    'SELECT ct.don_hang_id, ct.san_pham_id, ct.so_luong, ct.don_gia, 
            sp.ten AS ten_sach, sp.anh AS anh_sach,
            dh.id AS don_hang_id, dh.ngay_tao, dh.trang_thai, dh.dia_chi, dh.so_dien_thoai, dh.phuong_thuc_tt,
            nd.ho_ten AS ten_khach, nd.email AS email_khach
     FROM chi_tiet_don ct
     JOIN san_pham sp ON ct.san_pham_id = sp.id
     JOIN don_hang dh ON ct.don_hang_id = dh.id
     JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id
     WHERE sp.chu_sach_id = ?
     ORDER BY dh.ngay_tao DESC, dh.id DESC'
);
$stDonHang->execute([$chuSachId]);
$dsDonHang = $stDonHang->fetchAll();

// 4. Best-selling books for this seller
$stTopBooks = $db->prepare(
    'SELECT sp.id, sp.ten, sp.anh, sp.gia, sp.ton_kho,
            COALESCE(SUM(ct.so_luong), 0) AS units_sold,
            COALESCE(SUM(ct.so_luong * ct.don_gia), 0) AS revenue_earned
     FROM san_pham sp
     LEFT JOIN chi_tiet_don ct ON sp.id = ct.san_pham_id
     LEFT JOIN don_hang dh ON ct.don_hang_id = dh.id AND dh.trang_thai != "da_huy"
     WHERE sp.chu_sach_id = ?
     GROUP BY sp.id
     ORDER BY units_sold DESC, revenue_earned DESC
     LIMIT 5'
);
$stTopBooks->execute([$chuSachId]);
$dsTopBooks = $stTopBooks->fetchAll();

// 5. Payment transactions for this seller's books
$stSellerPayments = $db->prepare('
    SELECT p.*, dh.tong_tien as don_tong_tien, dh.ngay_tao as order_date, dh.trang_thai as order_status,
           nd.ho_ten as customer_name, nd.email as customer_email, nd.so_dien_thoai as customer_phone,
           SUM(ct.so_luong * ct.don_gia) as seller_share
    FROM payments p
    JOIN don_hang dh ON p.order_id = dh.id
    JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id
    JOIN chi_tiet_don ct ON dh.id = ct.don_hang_id
    JOIN san_pham sp ON ct.san_pham_id = sp.id
    WHERE sp.chu_sach_id = ?
    GROUP BY p.payment_id
    ORDER BY p.payment_id DESC
');
$stSellerPayments->execute([$chuSachId]);
$dsSellerPayments = $stSellerPayments->fetchAll();

$tongThuDaNhan = 0;
$soGiaoDichVerifying = 0;
foreach ($dsSellerPayments as $sp) {
    if ($sp['payment_status'] === 'paid') {
        $tongThuDaNhan += (int)$sp['seller_share'];
    } elseif ($sp['payment_status'] === 'verifying') {
        $soGiaoDichVerifying++;
    }
}

$thongBao = $_SESSION['flash'] ?? '';
$thongBaoLoi = $_SESSION['flash_loi'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_loi']);

$tabHienTai = $_GET['tab'] ?? 'dashboard';

$tieuDe = 'Seller Dashboard - ' . ($u['ten_gian_hang'] ?: 'Bookstore Hub');
$trang = 'chu-sach';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="padding-top: 25px; padding-bottom: 50px;">
  
  <!-- Seller Welcome Banner -->
  <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); color: #ffffff; padding: 28px 32px; border-radius: 16px; margin-bottom: 28px; box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.3); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 18px; border: 1px solid rgba(255,255,255,0.08); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -20px; top: -20px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(234, 88, 12, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
    <div style="position: relative; z-index: 1;">
      <span style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: #ffffff; font-size: 0.75rem; font-weight: 800; padding: 5px 14px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35); display: inline-flex; align-items: center; gap: 6px;">
        <span>🏪</span> SELLER PORTAL &bull; <?= e($u['ten_gian_hang'] ?: 'My Bookstore') ?>
      </span>
      <h1 style="color: #ffffff; margin: 10px 0 6px 0; font-size: 1.85rem; font-weight: 800;">
        Welcome back, <?= e($u['ho_ten']) ?>!
      </h1>
      <p style="margin: 0; color: #94a3b8; font-size: 0.96rem;">
        Manage your textbook bookstore, fulfill student orders, and monitor sales revenue.
      </p>
    </div>
    <div style="position: relative; z-index: 1; display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="them-san-pham.php" class="nut nut--chinh" style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); font-weight: 700; padding: 11px 20px; border-radius: 10px; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 20px rgba(234, 88, 12, 0.35); border: none;">
        <span>➕</span> Add New Book
      </a>
      <a href="danh-sach.php" class="nut nut--vien" style="color: #ffffff; border-color: rgba(255,255,255,0.3); font-weight: 600; padding: 11px 18px; border-radius: 10px;">
        👁️ View Public Catalog
      </a>
    </div>
  </div>

  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 10px; margin-bottom: 22px; font-weight: 600;" aria-live="polite">
      ✓ <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <?php if ($thongBaoLoi !== ''): ?>
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: 10px; margin-bottom: 22px; font-weight: 600;" aria-live="assertive">
      ⚠ <?= e($thongBaoLoi) ?>
    </div>
  <?php endif; ?>

  <!-- 5 KPI CARDS FOR SELLER -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
    
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.82rem; font-weight: 700; text-transform: uppercase;">Total Books</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #1e3b6d; margin-top: 4px;">📚 <?= number_format($tongSach) ?></div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">Titles listed on store</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.82rem; font-weight: 700; text-transform: uppercase;">Available Stock</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #059669; margin-top: 4px;">📦 <?= number_format($tongTonKho) ?></div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">Copies in warehouse</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.82rem; font-weight: 700; text-transform: uppercase;">Total Orders</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #2563eb; margin-top: 4px;">🛒 <?= number_format($tongDon) ?></div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">Customer purchases</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.82rem; font-weight: 700; text-transform: uppercase;">Units Sold</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #d97706; margin-top: 4px;">🛍️ <?= number_format($thongKeBan['tong_ban']) ?></div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">Completed deliveries</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.82rem; font-weight: 700; text-transform: uppercase;">Total Revenue</div>
      <div style="font-size: 1.6rem; font-weight: 800; color: #ea580c; margin-top: 4px;"><?= vnd((int)$thongKeBan['tong_doanh_thu']) ?></div>
      <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">Net sales earned</div>
    </div>

  </div>

  <!-- Layout: Sidebar Nav + Tab Content -->
  <div style="display: grid; grid-template-columns: 240px 1fr; gap: 24px; align-items: flex-start;">
    
    <!-- Sidebar Navigation -->
    <aside style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <nav style="display: flex; flex-direction: column; gap: 4px;">
        <a href="chu-sach.php?tab=dashboard" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'dashboard' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'dashboard' ? '#fff7ed' : 'transparent' ?>;">
          <span>📊</span> Dashboard &amp; Charts
        </a>
        <a href="chu-sach.php?tab=books" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'books' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'books' ? '#fff7ed' : 'transparent' ?>;">
          <span>📚</span> Book Management (<?= count($dsSach) ?>)
        </a>
        <a href="chu-sach.php?tab=orders" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'orders' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'orders' ? '#fff7ed' : 'transparent' ?>;">
          <span>📦</span> Orders &amp; Fulfillment (<?= count($dsDonHang) ?>)
        </a>
        <a href="chu-sach.php?tab=payments" style="display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'payments' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'payments' ? '#fff7ed' : 'transparent' ?>;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span>💳</span> Payments &amp; Receipts
          </div>
          <?php if ($soGiaoDichVerifying > 0): ?>
            <span style="background: #dc2626; color: #ffffff; padding: 2px 7px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;"><?= $soGiaoDichVerifying ?></span>
          <?php endif; ?>
        </a>
        <a href="chu-sach.php?tab=sales" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'sales' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'sales' ? '#fff7ed' : 'transparent' ?>;">
          <span>📈</span> Sales &amp; Best Sellers
        </a>
        <a href="chu-sach.php?tab=profile" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.93rem; color: <?= $tabHienTai === 'profile' ? '#ea580c' : '#334155' ?>; background: <?= $tabHienTai === 'profile' ? '#fff7ed' : 'transparent' ?>;">
          <span>🏪</span> Seller Store Profile
        </a>
      </nav>
    </aside>

    <!-- Main Tab Content Area -->
    <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 28px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">

      <?php if ($tabHienTai === 'dashboard'): ?>
        <!-- TAB 1: DASHBOARD & CHARTS -->
        <h2 style="margin: 0 0 16px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Sales Performance &amp; Revenue Chart
        </h2>
        
        <!-- SVG Sales Chart -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 28px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.05rem; color: #1e3b6d;">Monthly Revenue Overview (2026)</h3>
            <span style="font-size: 0.85rem; color: #16a34a; font-weight: 700;">● Active Store</span>
          </div>

          <!-- Responsive SVG Bar Chart -->
          <svg viewBox="0 0 600 200" style="width: 100%; height: auto; display: block;" aria-label="Sales Chart">
            <!-- Grid lines -->
            <line x1="50" y1="20" x2="580" y2="20" stroke="#e2e8f0" stroke-dasharray="4" />
            <line x1="50" y1="70" x2="580" y2="70" stroke="#e2e8f0" stroke-dasharray="4" />
            <line x1="50" y1="120" x2="580" y2="120" stroke="#e2e8f0" stroke-dasharray="4" />
            <line x1="50" y1="170" x2="580" y2="170" stroke="#cbd5e1" stroke-width="2" />

            <!-- Y Axis labels -->
            <text x="40" y="24" font-size="10" fill="#64748b" text-anchor="end">1.5M</text>
            <text x="40" y="74" font-size="10" fill="#64748b" text-anchor="end">1.0M</text>
            <text x="40" y="124" font-size="10" fill="#64748b" text-anchor="end">500k</text>
            <text x="40" y="174" font-size="10" fill="#64748b" text-anchor="end">0</text>

            <!-- Bars for months -->
            <!-- Jan -->
            <rect x="80" y="110" width="36" height="60" rx="4" fill="#cbd5e1" />
            <text x="98" y="188" font-size="11" fill="#64748b" text-anchor="middle">Jan</text>
            <!-- Feb -->
            <rect x="145" y="85" width="36" height="85" rx="4" fill="#93c5fd" />
            <text x="163" y="188" font-size="11" fill="#64748b" text-anchor="middle">Feb</text>
            <!-- Mar -->
            <rect x="210" y="60" width="36" height="110" rx="4" fill="#3b82f6" />
            <text x="228" y="188" font-size="11" fill="#64748b" text-anchor="middle">Mar</text>
            <!-- Apr -->
            <rect x="275" y="50" width="36" height="120" rx="4" fill="#60a5fa" />
            <text x="293" y="188" font-size="11" fill="#64748b" text-anchor="middle">Apr</text>
            <!-- May -->
            <rect x="340" y="30" width="36" height="140" rx="4" fill="#ea580c" />
            <text x="358" y="188" font-size="11" fill="#ea580c" font-weight="700" text-anchor="middle">May (Current)</text>
            <!-- Jun -->
            <rect x="405" y="65" width="36" height="105" rx="4" fill="#fed7aa" />
            <text x="423" y="188" font-size="11" fill="#64748b" text-anchor="middle">Jun</text>
            <!-- Jul -->
            <rect x="470" y="90" width="36" height="80" rx="4" fill="#e2e8f0" />
            <text x="488" y="188" font-size="11" fill="#64748b" text-anchor="middle">Jul</text>
          </svg>
        </div>

        <h3 style="margin: 0 0 14px 0; font-size: 1.15rem; color: #0f172a;">
          Top 5 Best-Selling Textbooks
        </h3>
        <?php if (empty($dsTopBooks)): ?>
          <p style="color: #64748b;">No sales recorded yet.</p>
        <?php else: ?>
          <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
              <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1;">
                <th style="padding: 10px 12px;">Cover</th>
                <th style="padding: 10px 12px;">Title</th>
                <th style="padding: 10px 12px; text-align: right;">Price</th>
                <th style="padding: 10px 12px; text-align: center;">Stock</th>
                <th style="padding: 10px 12px; text-align: center;">Units Sold</th>
                <th style="padding: 10px 12px; text-align: right;">Revenue</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dsTopBooks as $tb): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <td style="padding: 10px 12px; width: 50px;">
                    <img src="<?= e(anhSach($tb['anh'])) ?>" alt="Cover" width="38" height="50" style="object-fit: cover; border-radius: 4px;">
                  </td>
                  <td style="padding: 10px 12px;">
                    <strong><a href="chi-tiet.php?id=<?= $tb['id'] ?>" target="_blank" style="color: #1e3b6d; text-decoration: none;"><?= e($tb['ten']) ?></a></strong>
                  </td>
                  <td style="padding: 10px 12px; text-align: right; color: #475569;"><?= vnd($tb['gia']) ?></td>
                  <td style="padding: 10px 12px; text-align: center; font-weight: 700; color: <?= $tb['ton_kho'] > 0 ? '#16a34a' : '#dc2626' ?>;"><?= $tb['ton_kho'] ?></td>
                  <td style="padding: 10px 12px; text-align: center; font-weight: 800; color: #ea580c;"><?= $tb['units_sold'] ?></td>
                  <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #059669;"><?= vnd($tb['revenue_earned']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'books'): ?>
        <!-- TAB 2: BOOK MANAGEMENT -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h2 style="margin: 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
              Textbook Inventory (<?= count($dsSach) ?>)
            </h2>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">
              Manage titles, authors, categories, prices, inventory stock, and approval status.
            </p>
          </div>
          <a href="them-san-pham.php" class="nut nut--chinh" style="padding: 9px 18px; font-size: 0.92rem; font-weight: 700; border-radius: 8px;">
            ➕ List New Book
          </a>
        </div>

        <?php if (empty($dsSach)): ?>
          <div style="text-align: center; padding: 45px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <p style="font-size: 2.2rem; margin: 0 0 10px 0;">📚</p>
            <h3>No books listed yet</h3>
            <p style="color: #64748b;">Add your textbooks to start receiving student orders!</p>
            <a href="them-san-pham.php" class="nut nut--chinh" style="padding: 9px 18px;">Add New Book Now</a>
          </div>
        <?php else: ?>
          <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
              <thead>
                <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1; color: #475569;">
                  <th style="padding: 10px 12px; width: 50px;">ID</th>
                  <th style="padding: 10px 12px; width: 65px;">Cover</th>
                  <th style="padding: 10px 12px;">Book Title &amp; Author</th>
                  <th style="padding: 10px 12px; width: 140px;">Category</th>
                  <th style="padding: 10px 12px; text-align: right; width: 110px;">Price</th>
                  <th style="padding: 10px 12px; text-align: center; width: 80px;">Stock</th>
                  <th style="padding: 10px 12px; text-align: center; width: 95px;">Status</th>
                  <th style="padding: 10px 12px; text-align: center; width: 130px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dsSach as $s): ?>
                  <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 12px; font-weight: 700; color: #64748b;">#<?= $s['id'] ?></td>
                    <td style="padding: 10px 12px;">
                      <img src="<?= e(anhSach($s['anh'])) ?>" alt="Cover" width="45" height="60" style="object-fit: cover; border-radius: 4px;">
                    </td>
                    <td style="padding: 10px 12px;">
                      <strong><a href="chi-tiet.php?id=<?= $s['id'] ?>" target="_blank" style="color: #1e3b6d; text-decoration: none;"><?= e($s['ten']) ?></a></strong>
                      <div style="font-size: 0.8rem; color: #64748b;">
                        Author: <?= e($s['tac_gia'] ?: 'UED Editorial Board') ?> &bull; ISBN: <code><?= e($s['isbn'] ?: 'N/A') ?></code>
                      </div>
                    </td>
                    <td style="padding: 10px 12px;">
                      <span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                        <?= e(layTenDanhMucEn($s['danh_muc_id'], $s['ten_danh_muc'])) ?>
                      </span>
                    </td>
                    <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #dc2626;">
                      <?= vnd($s['gia']) ?>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <span style="font-weight: 800; color: <?= $s['ton_kho'] > 0 ? '#16a34a' : '#dc2626' ?>; background: <?= $s['ton_kho'] > 0 ? '#f0fdf4' : '#fef2f2' ?>; padding: 2px 8px; border-radius: 999px; font-size: 0.82rem;">
                        <?= $s['ton_kho'] ?>
                      </span>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <?php if (($s['trang_thai_duyet'] ?? 'approved') === 'approved'): ?>
                        <span style="background: #ecfdf5; color: #047857; font-size: 0.78rem; font-weight: 700; padding: 3px 8px; border-radius: 4px;">✓ Approved</span>
                      <?php elseif ($s['trang_thai_duyet'] === 'rejected'): ?>
                        <span style="background: #fee2e2; color: #991b1b; font-size: 0.78rem; font-weight: 700; padding: 3px 8px; border-radius: 4px;">✕ Rejected</span>
                      <?php else: ?>
                        <span style="background: #fef3c7; color: #92400e; font-size: 0.78rem; font-weight: 700; padding: 3px 8px; border-radius: 4px;">⏳ Pending</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <div style="display: flex; gap: 6px; justify-content: center;">
                        <a href="sua-san-pham.php?id=<?= $s['id'] ?>" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.8rem;" title="Edit book details">✏️ Edit</a>
                        <form method="POST" action="xoa-san-pham.php" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this book?');">
                          <?= csrfField() ?>
                          <input type="hidden" name="id" value="<?= $s['id'] ?>">
                          <button type="submit" class="nut nut--nguy-hiem" style="padding: 4px 8px; font-size: 0.8rem;" title="Delete book">🗑️</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'orders'): ?>
        <!-- TAB 3: ORDER FULFILLMENT -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Order Fulfillment (<?= count($dsDonHang) ?>)
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Customer orders containing your textbooks. Confirm and mark items as shipped.
        </p>

        <?php if (empty($dsDonHang)): ?>
          <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <p style="font-size: 2.2rem; margin: 0 0 8px 0;">📦</p>
            <h3>No orders received yet</h3>
            <p style="color: #64748b;">Customer orders for your books will appear here for fulfillment.</p>
          </div>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($dsDonHang as $dh): 
              $subtotal = $dh['so_luong'] * $dh['don_gia'];
            ?>
              <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                  <div>
                    <span style="font-weight: 800; color: #1e3b6d; font-size: 1.05rem;">Order #<?= $dh['don_hang_id'] ?></span>
                    <span style="color: #64748b; font-size: 0.85rem; margin-left: 10px;"><?= e($dh['ngay_tao']) ?></span>
                  </div>
                  <div>
                    <?php if ($dh['trang_thai'] === 'da_giao'): ?>
                      <span style="background: #ecfdf5; color: #047857; font-weight: 700; font-size: 0.82rem; padding: 4px 10px; border-radius: 6px;">✓ Completed / Delivered</span>
                    <?php elseif ($dh['trang_thai'] === 'dang_giao'): ?>
                      <span style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.82rem; padding: 4px 10px; border-radius: 6px;">⚡ In Transit / Shipping</span>
                    <?php elseif ($dh['trang_thai'] === 'da_huy'): ?>
                      <span style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.82rem; padding: 4px 10px; border-radius: 6px;">✕ Cancelled</span>
                    <?php else: ?>
                      <span style="background: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.82rem; padding: 4px 10px; border-radius: 6px;">⏳ Pending Confirmation</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: center;">
                  <div>
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                      📖 <?= e($dh['ten_sach']) ?>
                    </div>
                    <div style="font-size: 0.88rem; color: #475569;">
                      Quantity: <strong><?= $dh['so_luong'] ?></strong> &bull; Unit Price: <?= vnd($dh['don_gia']) ?> &bull; Subtotal: <strong style="color:#059669;"><?= vnd($subtotal) ?></strong>
                    </div>
                    <div style="font-size: 0.85rem; color: #64748b; margin-top: 6px;">
                      Customer: <strong><?= e($dh['ten_khach']) ?></strong> (<?= e($dh['email_khach']) ?>) &bull; Phone: <?= e($dh['so_dien_thoai']) ?><br>
                      Address: <?= e($dh['dia_chi']) ?>
                    </div>
                  </div>

                  <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">
                    <?php if ($dh['trang_thai'] === 'cho_xu_ly'): ?>
                      <form method="POST" action="chu-sach.php?tab=orders" style="margin: 0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action_cap_nhat_don" value="1">
                        <input type="hidden" name="don_id" value="<?= $dh['don_hang_id'] ?>">
                        <input type="hidden" name="trang_thai" value="dang_giao">
                        <button type="submit" class="nut nut--chinh" style="padding: 7px 14px; font-size: 0.85rem; border-radius: 6px; font-weight: 700;">
                          Confirm &amp; Ship &rarr;
                        </button>
                      </form>
                    <?php elseif ($dh['trang_thai'] === 'dang_giao'): ?>
                      <form method="POST" action="chu-sach.php?tab=orders" style="margin: 0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action_cap_nhat_don" value="1">
                        <input type="hidden" name="don_id" value="<?= $dh['don_hang_id'] ?>">
                        <input type="hidden" name="trang_thai" value="da_giao">
                        <button type="submit" class="nut" style="background:#059669;color:#fff;padding: 7px 14px; font-size: 0.85rem; border-radius: 6px; font-weight: 700; border: none;">
                          ✓ Mark Delivered
                        </button>
                      </form>
                    <?php endif; ?>

                    <a href="don-hang.php?id=<?= $dh['don_hang_id'] ?>" class="nut nut--vien" style="padding: 6px 12px; font-size: 0.82rem;">
                      Full Order Details
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'sales'): ?>
        <!-- TAB 4: SALES & REVENUE TRANSACTIONS -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Sales &amp; Revenue Transactions
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Detailed breakdown of all completed book sales and earnings.
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px; display: flex; justify-content: space-around; text-align: center;">
          <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 700;">Total Books Sold</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-top: 4px;"><?= $thongKeBan['tong_ban'] ?> copies</div>
          </div>
          <div style="border-left: 1px solid #cbd5e1; height: 50px;"></div>
          <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 700;">Total Net Revenue</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #ea580c; margin-top: 4px;"><?= vnd((int)$thongKeBan['tong_doanh_thu']) ?></div>
          </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
          <thead>
            <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1;">
              <th style="padding: 10px 12px;">Order ID</th>
              <th style="padding: 10px 12px;">Date</th>
              <th style="padding: 10px 12px;">Book Title</th>
              <th style="padding: 10px 12px; text-align: center;">Qty</th>
              <th style="padding: 10px 12px; text-align: right;">Unit Price</th>
              <th style="padding: 10px 12px; text-align: right;">Net Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dsDonHang as $dh): 
              $amt = $dh['so_luong'] * $dh['don_gia'];
            ?>
              <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px 12px; font-weight: 700; color: #1e3b6d;">#<?= $dh['don_hang_id'] ?></td>
                <td style="padding: 10px 12px; color: #64748b;"><?= e(date('d/m/Y H:i', strtotime($dh['ngay_tao']))) ?></td>
                <td style="padding: 10px 12px; font-weight: 600;"><?= e($dh['ten_sach']) ?></td>
                <td style="padding: 10px 12px; text-align: center; font-weight: 800;"><?= $dh['so_luong'] ?></td>
                <td style="padding: 10px 12px; text-align: right;"><?= vnd($dh['don_gia']) ?></td>
                <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #059669;"><?= vnd($amt) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      <?php elseif ($tabHienTai === 'payments'): ?>
        <!-- TAB: SELLER PAYMENTS & RECEIPT VERIFICATION -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Payment Transactions &amp; Receipt Verification
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Review payments for orders containing your textbooks, verify customer transfer receipts, and monitor earnings.
        </p>

        <!-- KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 25px;">
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
            <div style="font-size: 0.82rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Sales Volume</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #1e3b6d; margin-top: 4px;">
              <?= vnd((int)$thongKeBan['tong_doanh_thu']) ?>
            </div>
            <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">All orders for your books</div>
          </div>

          <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 18px;">
            <div style="font-size: 0.82rem; font-weight: 700; color: #166534; text-transform: uppercase;">Confirmed Paid Revenue</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #059669; margin-top: 4px;">
              <?= vnd($tongThuDaNhan) ?>
            </div>
            <div style="font-size: 0.8rem; color: #15803d; margin-top: 2px;">Settled transactions</div>
          </div>

          <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 18px;">
            <div style="font-size: 0.82rem; font-weight: 700; color: #1e40af; text-transform: uppercase;">Receipts Awaiting Check</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #2563eb; margin-top: 4px;">
              <?= $soGiaoDichVerifying ?>
            </div>
            <div style="font-size: 0.8rem; color: #3b82f6; margin-top: 2px;">Transfers to verify</div>
          </div>
        </div>

        <?php if (empty($dsSellerPayments)): ?>
          <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <p style="font-size: 2.5rem; margin: 0 0 10px 0;">💳</p>
            <h3>No payment transactions found</h3>
            <p style="color: #64748b;">Transactions for your textbook orders will appear here.</p>
          </div>
        <?php else: ?>
          <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left;">
              <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                  <th style="padding: 10px 12px;">Transaction Ref</th>
                  <th style="padding: 10px 12px; width: 70px;">Order</th>
                  <th style="padding: 10px 12px;">Customer</th>
                  <th style="padding: 10px 12px; text-align: right;">Your Share</th>
                  <th style="padding: 10px 12px;">Method</th>
                  <th style="padding: 10px 12px;">Status</th>
                  <th style="padding: 10px 12px;">Receipt Proof</th>
                  <th style="padding: 10px 12px; text-align: center; width: 170px;">Verification Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dsSellerPayments as $sp): ?>
                  <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 12px;">
                      <code style="font-weight: 700; color: #1e40af;"><?= e($sp['transaction_id'] ?: 'TXN-BN-' . $sp['payment_id']) ?></code>
                      <div style="color: #64748b; font-size: 0.78rem; margin-top: 2px;">
                        <?= $sp['payment_date'] ? date('d/m/Y H:i', strtotime($sp['payment_date'])) : date('d/m/Y H:i', strtotime($sp['created_at'])) ?>
                      </div>
                    </td>
                    <td style="padding: 10px 12px; font-weight: 700;">
                      <a href="don-hang.php?id=<?= $sp['order_id'] ?>" style="color: #0284c7; text-decoration: none;">
                        #<?= $sp['order_id'] ?>
                      </a>
                    </td>
                    <td style="padding: 10px 12px;">
                      <strong><?= e($sp['customer_name']) ?></strong><br>
                      <small style="color: #64748b;">📞 <?= e($sp['customer_phone']) ?></small>
                    </td>
                    <td style="padding: 10px 12px; text-align: right;">
                      <strong style="color: #059669; font-size: 0.95rem;"><?= vnd((int)$sp['seller_share']) ?></strong><br>
                      <small style="color: #64748b;">Total: <?= vnd($sp['don_tong_tien']) ?></small>
                    </td>
                    <td style="padding: 10px 12px;">
                      <?= nhanPhuongThucTT($sp['payment_method']) ?>
                    </td>
                    <td style="padding: 10px 12px;">
                      <?= huyHieuTrangThaiTT($sp['payment_status']) ?>
                      <?php if (!empty($sp['admin_note'])): ?>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px; max-width: 140px;">
                          <?= e($sp['admin_note']) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px;">
                      <?php if (!empty($sp['payment_proof'])): ?>
                        <a href="uploads/receipts/<?= e($sp['payment_proof']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; color: #1d4ed8; text-decoration: underline; font-weight: 600; font-size: 0.82rem;">
                          📄 View Receipt
                        </a>
                      <?php else: ?>
                        <span style="color: #94a3b8; font-size: 0.8rem;">No upload</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <?php if ($sp['payment_status'] === 'verifying' || ($sp['payment_status'] === 'pending' && !empty($sp['payment_proof']))): ?>
                        <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                          <!-- Approve Form -->
                          <form method="POST" action="chu-sach.php?tab=payments" style="margin: 0;" onsubmit="return confirm('Approve bank transfer for Order #<?= $sp['order_id'] ?>?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action_seller_duyet_tt" value="1">
                            <input type="hidden" name="payment_id" value="<?= $sp['payment_id'] ?>">
                            <button type="submit" class="nut nut--chinh" style="padding: 4px 8px; font-size: 0.78rem; background: #059669; border-color: #059669;">
                              ✓ Approve
                            </button>
                          </form>

                          <!-- Reject Form -->
                          <form method="POST" action="chu-sach.php?tab=payments" style="margin: 0;" onsubmit="var r = prompt('Reason for rejecting receipt:'); if(!r) return false; this.rejection_reason.value = r; return true;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action_seller_tuchoi_tt" value="1">
                            <input type="hidden" name="payment_id" value="<?= $sp['payment_id'] ?>">
                            <input type="hidden" name="rejection_reason" value="">
                            <button type="submit" class="nut" style="padding: 4px 8px; font-size: 0.78rem; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                              ✗ Reject
                            </button>
                          </form>
                        </div>
                      <?php elseif ($sp['payment_status'] === 'paid'): ?>
                        <a href="thanh-toan.php?action=receipt&order_id=<?= $sp['order_id'] ?>" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.78rem;">
                          🧾 Receipt
                        </a>
                      <?php else: ?>
                        <span style="color: #94a3b8; font-size: 0.8rem;">Waiting customer</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      <?php elseif ($tabHienTai === 'profile'): ?>
        <!-- TAB 5: SELLER STORE PROFILE -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Bookstore &amp; Stall Profile
        </h2>
        <p style="color: #64748b; font-size: 0.92rem; margin: 0 0 20px 0;">
          Customize your bookstore identity, shop description, and contact info displayed to students.
        </p>

        <form method="POST" action="chu-sach.php?tab=profile" style="max-width: 580px;">
          <?= csrfField() ?>
          <input type="hidden" name="action_seller_profile" value="1">

          <div style="margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Bookstore Name <span style="color:#dc2626;">*</span></label>
            <input type="text" name="ten_gian_hang" value="<?= e($u['ten_gian_hang'] ?? ($u['ho_ten'] . "'s Bookstore")) ?>" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Seller Contact Phone</label>
            <input type="tel" name="so_dien_thoai" value="<?= e($u['so_dien_thoai'] ?? '') ?>" placeholder="e.g. 0905123456" style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 18px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Shop Logo Image URL / Filename</label>
            <input type="text" name="logo_shop" value="<?= e($u['logo_shop'] ?? '') ?>" placeholder="e.g. https://... or logo-shop.png" style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 24px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Store Description &amp; Policies</label>
            <textarea name="mo_ta_shop" rows="4" placeholder="Describe your bookstore specialties, pickup locations, etc." style="width: 100%; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;"><?= e($u['mo_ta_shop'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="nut nut--chinh" style="padding: 11px 24px; font-size: 0.95rem; font-weight: 700; border-radius: 8px;">
            Save Store Profile
          </button>
        </form>

      <?php endif; ?>

    </div>
  </div>

</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
