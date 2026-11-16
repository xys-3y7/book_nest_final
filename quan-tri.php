<?php
/**
 * quan-tri.php - Administrator Dashboard: Full Platform Control
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 * Admin Role: Dashboard, Users, Books, Categories, Orders, Sellers, Reports, Settings
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/don-hang.php';
require_once __DIR__ . '/inc/thanh-toan.php';

// Requires 'quan_tri' role
yeuCauQuyen('quan_tri');

$db = db();
$u = nguoiDung();

// Handle Payment Report CSV Export
if (isset($_GET['export_payments_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="booknest_payments_' . date('Ymd_His') . '.csv"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM
    fputcsv($output, ['Payment ID', 'Transaction ID', 'Order ID', 'Customer Name', 'Customer Email', 'Payment Method', 'Amount (VND)', 'Status', 'Payment Date', 'Created At', 'Admin Note']);

    $stExp = $db->query('
        SELECT p.*, nd.ho_ten, nd.email 
        FROM payments p 
        JOIN nguoi_dung nd ON p.user_id = nd.id 
        ORDER BY p.payment_id DESC
    ');
    while ($row = $stExp->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['payment_id'],
            $row['transaction_id'],
            $row['order_id'],
            $row['ho_ten'],
            $row['email'],
            $row['payment_method'],
            $row['amount'],
            $row['payment_status'],
            $row['payment_date'],
            $row['created_at'],
            $row['admin_note']
        ]);
    }
    fclose($output);
    exit;
}

// -------------------------------------------------------------
// POST HANDLERS FOR ADMIN OPERATIONS
// -------------------------------------------------------------

// 1. User Management Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_them_user'])) {
    $hoTen = trim(chuoi($_POST['ho_ten'] ?? ''));
    $email = trim(chuoi($_POST['email'] ?? ''));
    $pass = $_POST['mat_khau'] ?? '';
    $role = chuoi($_POST['vai_tro'] ?? 'khach_hang');

    // Strict Single Admin Policy: Only 1 Super Administrator account is permitted
    if ($role === 'quan_tri') {
        $_SESSION['flash_loi'] = 'Policy restriction: BookNest strictly permits only 1 Super Administrator account. New accounts must be Customer or Seller.';
        header('Location: quan-tri.php?tab=users');
        exit;
    }
    $hopLeRole = ['khach_hang', 'chu_sach'];

    if ($hoTen !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($pass) >= 6 && in_array($role, $hopLeRole, true)) {
        try {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $st = $db->prepare('INSERT INTO nguoi_dung (ho_ten, email, mat_khau, vai_tro, kich_hoat) VALUES (?, ?, ?, ?, 1)');
            $st->execute([$hoTen, $email, $hash, $role]);
            $_SESSION['flash'] = "User '$email' successfully created with role '$role'.";
        } catch (PDOException $e) {
            $_SESSION['flash_loi'] = "Cannot create user: Email already registered.";
        }
    } else {
        $_SESSION['flash_loi'] = "Invalid user input (Name, valid email, and min 6-char password required).";
    }
    header('Location: quan-tri.php?tab=users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_kich_hoat_user'])) {
    $targetUid = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);
    $kichHoatMoi = (int)($_POST['trang_thai_moi'] ?? 1);

    if ($targetUid && $targetUid !== (int)$u['id']) {
        $st = $db->prepare('UPDATE nguoi_dung SET kich_hoat = ? WHERE id = ?');
        $st->execute([$kichHoatMoi, $targetUid]);
        $_SESSION['flash'] = "User #$targetUid status updated to " . ($kichHoatMoi === 1 ? 'Active' : 'Suspended') . ".";
    }
    header('Location: quan-tri.php?tab=users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_doi_vai_tro'])) {
    $targetUid = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);
    $vaiTroMoi = trim(chuoi($_POST['vai_tro'] ?? ''));

    if ($targetUid) {
        $stCheck = $db->prepare('SELECT id, vai_tro FROM nguoi_dung WHERE id = ?');
        $stCheck->execute([$targetUid]);
        $targetUser = $stCheck->fetch();

        if ($targetUser) {
            if ($targetUser['vai_tro'] === 'quan_tri' || $targetUid === (int)$u['id']) {
                $_SESSION['flash_loi'] = 'Policy restriction: The Super Administrator account role cannot be changed.';
            } elseif ($vaiTroMoi === 'quan_tri') {
                $_SESSION['flash_loi'] = 'Policy restriction: Only 1 Super Administrator account is permitted. Cannot promote accounts to Admin.';
            } elseif (in_array($vaiTroMoi, ['khach_hang', 'chu_sach'], true)) {
                $st = $db->prepare('UPDATE nguoi_dung SET vai_tro = ? WHERE id = ?');
                $st->execute([$vaiTroMoi, $targetUid]);
                $_SESSION['flash'] = "User #$targetUid role updated to '$vaiTroMoi'.";
            }
        }
    }
    header('Location: quan-tri.php?tab=users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_xoa_user'])) {
    $targetUid = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);
    if ($targetUid) {
        $stCheck = $db->prepare('SELECT vai_tro FROM nguoi_dung WHERE id = ?');
        $stCheck->execute([$targetUid]);
        $targetRole = $stCheck->fetchColumn();

        if ($targetRole === 'quan_tri' || $targetUid === (int)$u['id']) {
            $_SESSION['flash_loi'] = 'Policy restriction: Cannot delete the primary Super Administrator account.';
        } else {
            // Remove user data references
            $db->prepare('DELETE FROM gio_hang WHERE nguoi_dung_id = ?')->execute([$targetUid]);
            $db->prepare('DELETE FROM yeu_thich WHERE nguoi_dung_id = ?')->execute([$targetUid]);
            $db->prepare('DELETE FROM danh_gia WHERE nguoi_dung_id = ?')->execute([$targetUid]);
            $db->prepare('DELETE FROM so_dia_chi WHERE nguoi_dung_id = ?')->execute([$targetUid]);
            $st = $db->prepare('DELETE FROM nguoi_dung WHERE id = ?');
            $st->execute([$targetUid]);
            $_SESSION['flash'] = "User account #$targetUid has been deleted.";
        }
    }
    header('Location: quan-tri.php?tab=users');
    exit;
}

// 2. Book Supervision & Approval Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_duyet_sach'])) {
    $bookId = filter_var($_POST['book_id'] ?? '', FILTER_VALIDATE_INT);
    $status = trim(chuoi($_POST['status'] ?? 'approved'));
    if ($bookId && in_array($status, ['approved', 'rejected', 'pending'], true)) {
        $st = $db->prepare('UPDATE san_pham SET trang_thai_duyet = ? WHERE id = ?');
        $st->execute([$status, $bookId]);
        $_SESSION['flash'] = "Book #$bookId status set to '$status'.";
    }
    header('Location: quan-tri.php?tab=books');
    exit;
}

// 3. Category Management Actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_them_danh_muc'])) {
    $tenDm = trim(chuoi($_POST['ten_danh_muc'] ?? ''));
    $moTaDm = trim(chuoi($_POST['mo_ta'] ?? ''));
    if ($tenDm !== '') {
        $slug = taoSlugCategory($tenDm, $db);
        $st = $db->prepare('INSERT INTO danh_muc (ten, slug, mo_ta) VALUES (?, ?, ?)');
        $st->execute([$tenDm, $slug, $moTaDm]);
        $_SESSION['flash'] = "✓ Category '$tenDm' added successfully (Slug: $slug).";
    } else {
        $_SESSION['flash_loi'] = "Category name cannot be empty.";
    }
    header('Location: quan-tri.php?tab=books#categories');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_sua_danh_muc'])) {
    $dmId = filter_var($_POST['danh_muc_id'] ?? '', FILTER_VALIDATE_INT);
    $tenMoi = trim(chuoi($_POST['ten_danh_muc'] ?? ''));
    $moTaMoi = trim(chuoi($_POST['mo_ta'] ?? ''));
    if ($dmId && $tenMoi !== '') {
        $st = $db->prepare('UPDATE danh_muc SET ten = ?, mo_ta = ? WHERE id = ?');
        $st->execute([$tenMoi, $moTaMoi, $dmId]);
        $_SESSION['flash'] = "✓ Category #$dmId updated successfully.";
    }
    header('Location: quan-tri.php?tab=books#categories');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_xoa_danh_muc'])) {
    $dmId = filter_var($_POST['danh_muc_id'] ?? '', FILTER_VALIDATE_INT);
    if ($dmId) {
        $stCheck = $db->prepare('SELECT COUNT(*) FROM san_pham WHERE danh_muc_id = ?');
        $stCheck->execute([$dmId]);
        if ((int)$stCheck->fetchColumn() > 0) {
            $_SESSION['flash_loi'] = "Cannot delete category: Textbooks currently belong to this category.";
        } else {
            $db->prepare('DELETE FROM danh_muc WHERE id = ?')->execute([$dmId]);
            $_SESSION['flash'] = "✓ Category #$dmId deleted successfully.";
        }
    }
    header('Location: quan-tri.php?tab=books#categories');
    exit;
}

// 4. Order Management Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_cap_nhat_don_admin'])) {
    $donId = filter_var($_POST['don_id'] ?? '', FILTER_VALIDATE_INT);
    $trangThaiMoi = trim(chuoi($_POST['trang_thai'] ?? ''));
    $hopLe = ['cho_xu_ly', 'dang_giao', 'da_giao', 'da_huy'];

    if ($donId && in_array($trangThaiMoi, $hopLe, true)) {
        if ($trangThaiMoi === 'da_huy') {
            try {
                huyDonHang($donId, 0, true);
                $_SESSION['flash'] = "Order #$donId cancelled and inventory restored.";
            } catch (Throwable $e) {
                $_SESSION['flash_loi'] = $e->getMessage();
            }
        } else {
            $st = $db->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ?');
            $st->execute([$trangThaiMoi, $donId]);
            if ($trangThaiMoi === 'da_giao') {
                $db->prepare('UPDATE don_hang SET trang_thai_tt = "da_thanh_toan" WHERE id = ?')->execute([$donId]);
                $db->prepare('UPDATE payments SET payment_status = "paid", payment_date = NOW() WHERE order_id = ? AND payment_status != "paid"')->execute([$donId]);
                try {
                    $db->prepare('UPDATE thanh_toan SET trang_thai = "completed" WHERE don_hang_id = ?')->execute([$donId]);
                } catch (Throwable) {}
            }
            $_SESSION['flash'] = "Order status #$donId updated to '$trangThaiMoi'.";
        }
    }
    header('Location: quan-tri.php?tab=orders');
    exit;
}

// 4.1 Admin Payment Verification & Approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_admin_duyet_tt'])) {
    $paymentId = filter_var($_POST['payment_id'] ?? '', FILTER_VALIDATE_INT);
    if ($paymentId) {
        duyetThanhToan($paymentId, (int)$u['id'], 'Verified and approved by Super Admin');
        $_SESSION['flash'] = "Payment #$paymentId approved and marked as Paid.";
    }
    header('Location: quan-tri.php?tab=payments');
    exit;
}

// 4.2 Admin Payment Receipt Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_admin_tuchoi_tt'])) {
    $paymentId = filter_var($_POST['payment_id'] ?? '', FILTER_VALIDATE_INT);
    $reason = trim(chuoi($_POST['rejection_reason'] ?? 'Invalid payment receipt or mismatched amount.'));
    if ($paymentId) {
        tuChoiThanhToan($paymentId, (int)$u['id'], $reason);
        $_SESSION['flash'] = "Payment receipt for #$paymentId has been rejected.";
    }
    header('Location: quan-tri.php?tab=payments');
    exit;
}

// 4.3 Admin Process Refund
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_admin_hoan_tien'])) {
    $paymentId = filter_var($_POST['payment_id'] ?? '', FILTER_VALIDATE_INT);
    $reason = trim(chuoi($_POST['refund_reason'] ?? 'Customer cancellation or product refund.'));
    if ($paymentId) {
        hoanTienThanhToan($paymentId, (int)$u['id'], $reason);
        $_SESSION['flash'] = "Payment #$paymentId has been refunded.";
    }
    header('Location: quan-tri.php?tab=payments');
    exit;
}

// 4.4 Admin Manage Payment Methods
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_luu_phuong_thuc'])) {
    $methodId = filter_var($_POST['method_id'] ?? '', FILTER_VALIDATE_INT);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $instructions = trim(chuoi($_POST['instructions'] ?? ''));
    $desc = trim(chuoi($_POST['description'] ?? ''));
    if ($methodId) {
        $stUp = $db->prepare('UPDATE payment_methods SET is_active = ?, instructions = ?, description = ? WHERE id = ?');
        $stUp->execute([$isActive, $instructions, $desc, $methodId]);
        $_SESSION['flash'] = 'Payment method configuration updated successfully!';
    }
    header('Location: quan-tri.php?tab=payments');
    exit;
}

// 4.5 Admin Save Bank Account & Payment Gateway Configurations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_luu_cai_dat_ngan_hang'])) {
    $res = luuCaiDatThanhToan($_POST, $_FILES['qr_code_file'] ?? null);
    if ($res['success']) {
        $_SESSION['flash'] = '✓ ' . $res['message'];
    } else {
        $_SESSION['flash_loi'] = $res['error'] ?? 'Failed to update payment settings.';
    }
    header('Location: quan-tri.php?tab=payments');
    exit;
}

// 5. Seller Management Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_duyet_seller'])) {
    $sellerId = filter_var($_POST['seller_id'] ?? '', FILTER_VALIDATE_INT);
    $sellerStatus = trim(chuoi($_POST['seller_status'] ?? 'active'));
    if ($sellerId && in_array($sellerStatus, ['active', 'pending', 'rejected', 'suspended'], true)) {
        $st = $db->prepare('UPDATE nguoi_dung SET trang_thai_seller = ? WHERE id = ?');
        $st->execute([$sellerStatus, $sellerId]);
        $_SESSION['flash'] = "Seller #$sellerId status updated to '$sellerStatus'.";
    }
    header('Location: quan-tri.php?tab=sellers');
    exit;
}

// 6. System Settings Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_luu_cai_dat'])) {
    $keys = ['site_name', 'hotline', 'email_lien_he', 'dia_chi', 'banner_thong_bao', 'khuyen_mai_active'];
    $stSet = $db->prepare('INSERT INTO he_thong_cai_dat (khoa, gia_tri) VALUES (?, ?) ON DUPLICATE KEY UPDATE gia_tri = VALUES(gia_tri)');
    foreach ($keys as $k) {
        $v = trim(chuoi($_POST[$k] ?? ''));
        $stSet->execute([$k, $v]);
    }
    $_SESSION['flash'] = 'Platform system settings updated successfully!';
    header('Location: quan-tri.php?tab=settings');
    exit;
}

// -------------------------------------------------------------
// DATA QUERIES FOR DASHBOARD & TABS
// -------------------------------------------------------------

// Dashboard KPI Counts
$tongNguoiDung = (int)$db->query('SELECT COUNT(*) FROM nguoi_dung')->fetchColumn();
$tongSeller = (int)$db->query('SELECT COUNT(*) FROM nguoi_dung WHERE vai_tro = "chu_sach"')->fetchColumn();
$tongKhachHang = (int)$db->query('SELECT COUNT(*) FROM nguoi_dung WHERE vai_tro = "khach_hang"')->fetchColumn();
$tongSach = (int)$db->query('SELECT COUNT(*) FROM san_pham')->fetchColumn();
$tongDonHang = (int)$db->query('SELECT COUNT(*) FROM don_hang')->fetchColumn();
$tongDoanhThu = (int)$db->query('SELECT COALESCE(SUM(tong_tien), 0) FROM don_hang WHERE trang_thai != "da_huy"')->fetchColumn();

// All Users
$stUsers = $db->query(
    'SELECT nd.*, COUNT(sp.id) AS so_sach,
            COALESCE((SELECT COUNT(*) FROM don_hang dh WHERE dh.nguoi_dung_id = nd.id), 0) AS so_don
     FROM nguoi_dung nd
     LEFT JOIN san_pham sp ON sp.chu_sach_id = nd.id
     GROUP BY nd.id
     ORDER BY nd.id ASC'
);
$dsNguoiDung = $stUsers->fetchAll();

// All Books
$stBooks = $db->query(
    'SELECT sp.*, dm.ten AS ten_danh_muc, nd.ho_ten AS ten_chu_sach, nd.ten_gian_hang
     FROM san_pham sp
     JOIN danh_muc dm ON sp.danh_muc_id = dm.id
     LEFT JOIN nguoi_dung nd ON sp.chu_sach_id = nd.id
     ORDER BY sp.id DESC'
);
$dsTatCaSach = $stBooks->fetchAll();

// Categories
$dsDanhMuc = $db->query('SELECT dm.*, COUNT(sp.id) AS so_sach FROM danh_muc dm LEFT JOIN san_pham sp ON sp.danh_muc_id = dm.id GROUP BY dm.id ORDER BY dm.id ASC')->fetchAll();

// All Orders
$stDon = $db->query(
    'SELECT dh.*, nd.ho_ten, nd.email,
            (SELECT COUNT(*) FROM chi_tiet_don ct WHERE ct.don_hang_id = dh.id) AS so_mon
     FROM don_hang dh 
     JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
     ORDER BY dh.ngay_tao DESC, dh.id DESC'
);
$dsDonHang = $stDon->fetchAll();

// Sellers List
$stSellers = $db->query(
    'SELECT nd.*, COUNT(sp.id) AS so_sach,
            COALESCE(SUM(ct.so_luong * ct.don_gia), 0) AS doanh_thu
     FROM nguoi_dung nd
     LEFT JOIN san_pham sp ON sp.chu_sach_id = nd.id
     LEFT JOIN chi_tiet_don ct ON ct.san_pham_id = sp.id
     WHERE nd.vai_tro = "chu_sach"
     GROUP BY nd.id
     ORDER BY nd.id ASC'
);
$dsSellers = $stSellers->fetchAll();

// Top Selling Books for Platform
$stTopPlatform = $db->query(
    'SELECT sp.id, sp.ten, sp.anh, sp.gia, dm.ten AS ten_danh_muc,
            COALESCE(SUM(ct.so_luong), 0) AS units_sold,
            COALESCE(SUM(ct.so_luong * ct.don_gia), 0) AS revenue
     FROM san_pham sp
     JOIN danh_muc dm ON sp.danh_muc_id = dm.id
     LEFT JOIN chi_tiet_don ct ON ct.san_pham_id = sp.id
     LEFT JOIN don_hang dh ON ct.don_hang_id = dh.id AND dh.trang_thai != "da_huy"
     GROUP BY sp.id
     ORDER BY units_sold DESC, revenue DESC
     LIMIT 6'
);
$dsTopPlatform = $stTopPlatform->fetchAll();

// Settings
$stSettings = $db->query('SELECT khoa, gia_tri FROM he_thong_cai_dat');
$caiDatMap = $stSettings->fetchAll(PDO::FETCH_KEY_PAIR);

// Payments Data for Admin Tab
$locStatus = trim(chuoi($_GET['pay_status'] ?? 'all'));
$locMethod = trim(chuoi($_GET['pay_method'] ?? 'all'));
$searchQ = trim(chuoi($_GET['pay_q'] ?? ''));

$sqlPayments = '
    SELECT p.*, dh.tong_tien as don_tong_tien, dh.ngay_tao as order_date, dh.trang_thai as order_status,
           nd.ho_ten as customer_name, nd.email as customer_email, nd.so_dien_thoai as customer_phone
    FROM payments p
    JOIN don_hang dh ON p.order_id = dh.id
    JOIN nguoi_dung nd ON p.user_id = nd.id
    WHERE 1=1
';
$payParams = [];

if ($locStatus !== 'all' && in_array($locStatus, ['pending', 'verifying', 'paid', 'rejected', 'refunded', 'failed'], true)) {
    $sqlPayments .= ' AND p.payment_status = ?';
    $payParams[] = $locStatus;
}
if ($locMethod !== 'all') {
    $sqlPayments .= ' AND p.payment_method = ?';
    $payParams[] = $locMethod;
}
if ($searchQ !== '') {
    $sqlPayments .= ' AND (p.transaction_id LIKE ? OR p.order_id LIKE ? OR nd.ho_ten LIKE ? OR nd.email LIKE ?)';
    $searchTerm = "%$searchQ%";
    $payParams[] = $searchTerm;
    $payParams[] = $searchTerm;
    $payParams[] = $searchTerm;
    $payParams[] = $searchTerm;
}
$sqlPayments .= ' ORDER BY p.payment_id DESC';
$stAllPayments = $db->prepare($sqlPayments);
$stAllPayments->execute($payParams);
$dsAllPayments = $stAllPayments->fetchAll();

// Payment KPI Metrics
$stPayMetrics = $db->query('
    SELECT 
        COALESCE(SUM(amount), 0) as total_volume,
        COALESCE(SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END), 0) as paid_volume,
        COALESCE(SUM(CASE WHEN payment_status = "verifying" THEN amount ELSE 0 END), 0) as verifying_volume,
        COALESCE(SUM(CASE WHEN payment_status = "refunded" THEN amount ELSE 0 END), 0) as refunded_volume,
        COUNT(CASE WHEN payment_status = "verifying" THEN 1 END) as verifying_count
    FROM payments
');
$payMetrics = $stPayMetrics->fetch();

// Check Suspicious Transactions
$dsNghiVan = kiemTraGiaoDichNghiVan();

// Payment Methods & Gateway Settings
$dsAllMethods = layDanhSachPhuongThuc(false);
$caiDatNganHang = layCaiDatThanhToan();

$thongBao = $_SESSION['flash'] ?? '';
$thongBaoLoi = $_SESSION['flash_loi'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_loi']);

$tabHienTai = $_GET['tab'] ?? 'dashboard';

$tieuDe = 'Administrator Dashboard';
$trang = 'quan-tri';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="padding-top: 25px; padding-bottom: 50px;">
  
  <!-- Super Admin Banner -->
  <div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; padding: 26px 30px; border-radius: 16px; margin-bottom: 28px; box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.35); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border: 1px solid rgba(255,255,255,0.08);">
    <div>
      <span style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; font-size: 0.75rem; font-weight: 800; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);">
        👑 SUPER ADMIN PORTAL
      </span>
      <h1 style="color: #ffffff; margin: 10px 0 4px 0; font-size: 1.85rem; font-weight: 800;">
        Administrator Dashboard
      </h1>
      <p style="margin: 0; color: #94a3b8; font-size: 0.95rem;">
        Full oversight of users, book catalog, orders, sellers, financial reports, and platform settings.
      </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="danh-sach.php" class="nut nut--vien" style="color: #ffffff; border-color: rgba(255,255,255,0.3); font-weight: 600; padding: 10px 18px; border-radius: 8px;">
        👁️ Public Catalog
      </a>
      <a href="tai-khoan.php" class="nut" style="background: rgba(255,255,255,0.15); color: #fff; font-weight: 600; padding: 10px 18px; border-radius: 8px; text-decoration: none;">
        👤 My Account
      </a>
    </div>
  </div>

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

  <!-- 6 KPI STATS CARDS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px;">
    
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Total Users</div>
      <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-top: 4px;">👥 <?= number_format($tongNguoiDung) ?></div>
      <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">Registered accounts</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Total Sellers</div>
      <div style="font-size: 1.75rem; font-weight: 800; color: #ea580c; margin-top: 4px;">🏪 <?= number_format($tongSeller) ?></div>
      <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">Partner bookstores</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Total Customers</div>
      <div style="font-size: 1.75rem; font-weight: 800; color: #16a34a; margin-top: 4px;">👤 <?= number_format($tongKhachHang) ?></div>
      <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">Active students</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Total Books</div>
      <div style="font-size: 1.75rem; font-weight: 800; color: #2563eb; margin-top: 4px;">📚 <?= number_format($tongSach) ?></div>
      <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">Listed textbooks</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Total Orders</div>
      <div style="font-size: 1.75rem; font-weight: 800; color: #7c3aed; margin-top: 4px;">🛒 <?= number_format($tongDonHang) ?></div>
      <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">Platform transactions</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Total Revenue</div>
      <div style="font-size: 1.55rem; font-weight: 800; color: #dc2626; margin-top: 4px;"><?= vnd($tongDoanhThu) ?></div>
      <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">Platform volume</div>
    </div>

  </div>

  <!-- Layout: Sidebar Tabs + Content Area -->
  <div style="display: grid; grid-template-columns: 240px 1fr; gap: 24px; align-items: flex-start;">
    
    <!-- Sidebar Navigation -->
    <aside style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
      <nav style="display: flex; flex-direction: column; gap: 4px;">
        <a href="quan-tri.php?tab=dashboard" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.92rem; color: <?= $tabHienTai === 'dashboard' ? '#dc2626' : '#334155' ?>; background: <?= $tabHienTai === 'dashboard' ? '#fef2f2' : 'transparent' ?>;">
          <span>📊</span> Dashboard &amp; Analytics
        </a>
        <a href="quan-tri.php?tab=users" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.92rem; color: <?= $tabHienTai === 'users' ? '#dc2626' : '#334155' ?>; background: <?= $tabHienTai === 'users' ? '#fef2f2' : 'transparent' ?>;">
          <span>👥</span> User Management (<?= count($dsNguoiDung) ?>)
        </a>
        <a href="quan-tri.php?tab=books" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.92rem; color: <?= $tabHienTai === 'books' ? '#dc2626' : '#334155' ?>; background: <?= $tabHienTai === 'books' ? '#fef2f2' : 'transparent' ?>;">
          <span>📚</span> Book Supervision (<?= count($dsTatCaSach) ?>)
        </a>
        <a href="quan-tri.php?tab=orders" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.92rem; color: <?= $tabHienTai === 'orders' ? '#dc2626' : '#334155' ?>; background: <?= $tabHienTai === 'orders' ? '#fef2f2' : 'transparent' ?>;">
          <span>📦</span> Orders (<?= count($dsDonHang) ?>)
        </a>
        <a href="quan-tri.php?tab=payments" style="display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.92rem; color: <?= $tabHienTai === 'payments' ? '#dc2626' : '#334155' ?>; background: <?= $tabHienTai === 'payments' ? '#fef2f2' : 'transparent' ?>;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span>💳</span> Payments &amp; Gateway
          </div>
          <?php if (($payMetrics['verifying_count'] ?? 0) > 0): ?>
            <span style="background: #dc2626; color: #ffffff; padding: 2px 7px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;"><?= $payMetrics['verifying_count'] ?></span>
          <?php endif; ?>
        </a>
        <a href="quan-tri.php?tab=sellers" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.92rem; color: <?= $tabHienTai === 'sellers' ? '#dc2626' : '#334155' ?>; background: <?= $tabHienTai === 'sellers' ? '#fef2f2' : 'transparent' ?>;">
          <span>🏪</span> Sellers (<?= count($dsSellers) ?>)
        </a>
        <a href="quan-tri.php?tab=reports" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.92rem; color: <?= $tabHienTai === 'reports' ? '#dc2626' : '#334155' ?>; background: <?= $tabHienTai === 'reports' ? '#fef2f2' : 'transparent' ?>;">
          <span>📈</span> Reports &amp; Revenue
        </a>
        <a href="quan-tri.php?tab=settings" style="display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.92rem; color: <?= $tabHienTai === 'settings' ? '#dc2626' : '#334155' ?>; background: <?= $tabHienTai === 'settings' ? '#fef2f2' : 'transparent' ?>;">
          <span>⚙️</span> System Settings
        </a>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 28px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">

      <?php if ($tabHienTai === 'dashboard'): ?>
        <!-- TAB 1: DASHBOARD & CHARTS -->
        <h2 style="margin: 0 0 16px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Platform Sales &amp; Growth Analytics
        </h2>

        <!-- SVG Platform Revenue Chart -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 28px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.05rem; color: #1e3b6d;">Platform Monthly Gross Volume (2026)</h3>
            <span style="font-size: 0.85rem; color: #059669; font-weight: 700;">● Active Monitoring</span>
          </div>

          <svg viewBox="0 0 600 200" style="width: 100%; height: auto; display: block;" aria-label="Platform Chart">
            <line x1="50" y1="20" x2="580" y2="20" stroke="#e2e8f0" stroke-dasharray="4" />
            <line x1="50" y1="70" x2="580" y2="70" stroke="#e2e8f0" stroke-dasharray="4" />
            <line x1="50" y1="120" x2="580" y2="120" stroke="#e2e8f0" stroke-dasharray="4" />
            <line x1="50" y1="170" x2="580" y2="170" stroke="#cbd5e1" stroke-width="2" />

            <text x="40" y="24" font-size="10" fill="#64748b" text-anchor="end">3.0M</text>
            <text x="40" y="74" font-size="10" fill="#64748b" text-anchor="end">2.0M</text>
            <text x="40" y="124" font-size="10" fill="#64748b" text-anchor="end">1.0M</text>
            <text x="40" y="174" font-size="10" fill="#64748b" text-anchor="end">0</text>

            <rect x="80" y="110" width="36" height="60" rx="4" fill="#cbd5e1" />
            <text x="98" y="188" font-size="11" fill="#64748b" text-anchor="middle">Jan</text>
            <rect x="145" y="80" width="36" height="90" rx="4" fill="#93c5fd" />
            <text x="163" y="188" font-size="11" fill="#64748b" text-anchor="middle">Feb</text>
            <rect x="210" y="55" width="36" height="115" rx="4" fill="#3b82f6" />
            <text x="228" y="188" font-size="11" fill="#64748b" text-anchor="middle">Mar</text>
            <rect x="275" y="45" width="36" height="125" rx="4" fill="#1d4ed8" />
            <text x="293" y="188" font-size="11" fill="#64748b" text-anchor="middle">Apr</text>
            <rect x="340" y="25" width="36" height="145" rx="4" fill="#dc2626" />
            <text x="358" y="188" font-size="11" fill="#dc2626" font-weight="700" text-anchor="middle">May (Current)</text>
            <rect x="405" y="60" width="36" height="110" rx="4" fill="#fca5a5" />
            <text x="423" y="188" font-size="11" fill="#64748b" text-anchor="middle">Jun</text>
            <rect x="470" y="85" width="36" height="85" rx="4" fill="#e2e8f0" />
            <text x="488" y="188" font-size="11" fill="#64748b" text-anchor="middle">Jul</text>
          </svg>
        </div>

        <h3 style="margin: 0 0 14px 0; font-size: 1.15rem; color: #0f172a;">
          Top 6 Best-Selling Textbooks Across All Sellers
        </h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
          <thead>
            <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1;">
              <th style="padding: 10px 12px;">Cover</th>
              <th style="padding: 10px 12px;">Title</th>
              <th style="padding: 10px 12px;">Category</th>
              <th style="padding: 10px 12px; text-align: right;">Price</th>
              <th style="padding: 10px 12px; text-align: center;">Sold</th>
              <th style="padding: 10px 12px; text-align: right;">Gross Revenue</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dsTopPlatform as $tp): ?>
              <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px 12px; width: 45px;">
                  <img src="<?= e(anhSach($tp['anh'])) ?>" alt="Cover" width="36" height="48" style="object-fit: cover; border-radius: 4px;">
                </td>
                <td style="padding: 10px 12px;">
                  <strong><a href="chi-tiet.php?id=<?= $tp['id'] ?>" target="_blank" style="color: #1e3b6d; text-decoration: none;"><?= e($tp['ten']) ?></a></strong>
                </td>
                <td style="padding: 10px 12px; color: #64748b;"><?= e($tp['ten_danh_muc']) ?></td>
                <td style="padding: 10px 12px; text-align: right;"><?= vnd($tp['gia']) ?></td>
                <td style="padding: 10px 12px; text-align: center; font-weight: 800; color: #ea580c;"><?= $tp['units_sold'] ?></td>
                <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #059669;"><?= vnd($tp['revenue']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      <?php elseif ($tabHienTai === 'users'): ?>
        <!-- TAB 2: USER MANAGEMENT -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h2 style="margin: 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
              User Account Management (<?= count($dsNguoiDung) ?>)
            </h2>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">
              Manage customer and seller accounts, activate/suspend access, and assign roles.
            </p>
          </div>
        </div>

        <!-- Single Admin Policy Banner -->
        <div style="background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1e40af; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 1.25rem;">🛡️</span>
          <div>
            <strong>Single Administrator Policy Active:</strong> BookNest strictly operates with exactly 1 Super Administrator account (ID #1: <code>admin@ued.vn</code>). All newly created or modified accounts are restricted to Customer (Student) or Seller (Bookstore).
          </div>
        </div>

        <!-- Add New User Form -->
        <details style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 16px; margin-bottom: 24px;">
          <summary style="font-weight: 700; color: #1e3b6d; cursor: pointer;">
            ➕ Add New User Account (Customer / Seller)
          </summary>
          <form method="POST" action="quan-tri.php?tab=users" style="margin-top: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 130px; gap: 12px; align-items: flex-end;">
            <?= csrfField() ?>
            <input type="hidden" name="action_them_user" value="1">
            <div>
              <label style="font-size: 0.82rem; font-weight: 700; color: #334155;">Full Name:</label>
              <input type="text" name="ho_ten" required placeholder="e.g. Alice Smith" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
              <label style="font-size: 0.82rem; font-weight: 700; color: #334155;">Email Address:</label>
              <input type="email" name="email" required placeholder="alice@ued.vn" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
              <label style="font-size: 0.82rem; font-weight: 700; color: #334155;">Initial Password:</label>
              <input type="password" name="mat_khau" required placeholder="Min 6 chars" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
              <label style="font-size: 0.82rem; font-weight: 700; color: #334155;">Role:</label>
              <select name="vai_tro" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-weight: 600;">
                <option value="khach_hang">👤 Customer (Student)</option>
                <option value="chu_sach">🏪 Seller (Bookstore)</option>
              </select>
            </div>
            <button type="submit" class="nut nut--chinh" style="padding: 9px 14px; font-size: 0.88rem; font-weight: 700; border-radius: 6px;">
              Create User
            </button>
          </form>
        </details>

        <!-- Users Table -->
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
              <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1; color: #475569;">
                <th style="padding: 10px 12px; width: 50px;">ID</th>
                <th style="padding: 10px 12px;">User / Identity</th>
                <th style="padding: 10px 12px; width: 170px;">Role</th>
                <th style="padding: 10px 12px; text-align: center; width: 100px;">Status</th>
                <th style="padding: 10px 12px; text-align: center; width: 110px;">Activity</th>
                <th style="padding: 10px 12px; text-align: center; width: 190px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dsNguoiDung as $nd): 
                $isSelf = ((int)$nd['id'] === (int)$u['id']);
                $isActive = (isset($nd['kich_hoat']) ? (int)$nd['kich_hoat'] === 1 : true);
                $isAdmin = ($nd['vai_tro'] === 'quan_tri');
              ?>
                <tr style="border-bottom: 1px solid #e2e8f0; background: <?= $isActive ? 'transparent' : '#fff1f2' ?>;">
                  <td style="padding: 10px 12px; font-weight: 700; color: #64748b;">#<?= $nd['id'] ?></td>
                  <td style="padding: 10px 12px;">
                    <strong><?= e($nd['ho_ten']) ?></strong> <?= $isSelf ? '<span style="color:#2563eb;font-size:0.75rem;">(You)</span>' : '' ?><br>
                    <small style="color: #64748b;">📧 <?= e($nd['email']) ?></small>
                  </td>
                  <td style="padding: 10px 12px;">
                    <?php if ($isAdmin): ?>
                      <span style="background: #fef3c7; color: #92400e; font-weight: 800; font-size: 0.78rem; padding: 4px 10px; border-radius: 6px; border: 1px solid #fde68a; display: inline-flex; align-items: center; gap: 4px;">
                        👑 Super Admin (Sole Admin)
                      </span>
                    <?php else: ?>
                      <form method="POST" action="quan-tri.php?tab=users" style="margin: 0; display: inline-flex; align-items: center; gap: 4px;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action_doi_vai_tro" value="1">
                        <input type="hidden" name="user_id" value="<?= $nd['id'] ?>">
                        <select name="vai_tro" onchange="this.form.submit()" style="padding: 4px 6px; border-radius: 4px; font-size: 0.8rem; border: 1px solid #cbd5e1; font-weight: 600;">
                          <option value="khach_hang" <?= $nd['vai_tro'] === 'khach_hang' ? 'selected' : '' ?>>👤 Customer</option>
                          <option value="chu_sach" <?= $nd['vai_tro'] === 'chu_sach' ? 'selected' : '' ?>>🏪 Seller</option>
                        </select>
                      </form>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: center;">
                    <?php if ($isActive): ?>
                      <span style="background: #ecfdf5; color: #047857; font-weight: 700; font-size: 0.78rem; padding: 3px 8px; border-radius: 4px;">Active</span>
                    <?php else: ?>
                      <span style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.78rem; padding: 3px 8px; border-radius: 4px;">Suspended</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: center; font-size: 0.82rem; color: #64748b;">
                    <?= $nd['so_sach'] ?> books &bull; <?= $nd['so_don'] ?> orders
                  </td>
                  <td style="padding: 10px 12px; text-align: center;">
                    <div style="display: flex; gap: 6px; justify-content: center;">
                      <?php if ($isAdmin): ?>
                        <span style="color: #64748b; font-size: 0.8rem; font-weight: 700; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">Protected Admin</span>
                      <?php else: ?>
                        <!-- Activate / Deactivate Toggle -->
                        <form method="POST" action="quan-tri.php?tab=users" style="margin: 0;">
                          <?= csrfField() ?>
                          <input type="hidden" name="action_kich_hoat_user" value="1">
                          <input type="hidden" name="user_id" value="<?= $nd['id'] ?>">
                          <input type="hidden" name="trang_thai_moi" value="<?= $isActive ? '0' : '1' ?>">
                          <button type="submit" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.78rem; color: <?= $isActive ? '#dc2626' : '#16a34a' ?>;">
                            <?= $isActive ? 'Suspend' : 'Activate' ?>
                          </button>
                        </form>

                        <!-- Delete User Account -->
                        <form method="POST" action="quan-tri.php?tab=users" style="margin: 0;" onsubmit="return confirm('Delete account <?= e($nd['email']) ?>? All related session and cart data will be wiped.');">
                          <?= csrfField() ?>
                          <input type="hidden" name="action_xoa_user" value="1">
                          <input type="hidden" name="user_id" value="<?= $nd['id'] ?>">
                          <button type="submit" class="nut nut--nguy-hiem" style="padding: 4px 8px; font-size: 0.78rem;">
                            🗑️
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      <?php elseif ($tabHienTai === 'books'): ?>
        <!-- TAB 3: BOOK SUPERVISION & CATEGORY MANAGEMENT -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Book Supervision &amp; Category Management
        </h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 20px 0;">
          Approve or reject seller listings, remove violating items, and manage platform textbook categories.
        </p>

        <!-- Category Management Section (Admin Can Add Categories) -->
        <details open id="categories" style="background: #ffffff; border: 2px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 28px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
          <summary style="font-weight: 800; color: #0f172a; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <span>📂 Manage &amp; Add Textbook Categories (<?= count($dsDanhMuc) ?> Categories)</span>
          </summary>
          <div style="margin-top: 16px;">
            <!-- Add New Category Form -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
              <h4 style="margin: 0 0 10px 0; font-size: 0.95rem; color: #1e3b6d; font-weight: 700;">
                ➕ Add New Category
              </h4>
              <form method="POST" action="quan-tri.php?tab=books#categories" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?= csrfField() ?>
                <input type="hidden" name="action_them_danh_muc" value="1">
                <input type="text" name="ten_danh_muc" required placeholder="Category Name (e.g. Data Science & Artificial Intelligence)" style="padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 6px; flex: 2; min-width: 250px; font-size: 0.95rem;">
                <input type="text" name="mo_ta" placeholder="Short description for students & sellers" style="padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 6px; flex: 3; min-width: 280px; font-size: 0.95rem;">
                <button type="submit" class="nut nut--chinh" style="padding: 10px 20px; font-size: 0.95rem; font-weight: 700; border-radius: 6px;">
                  + Add Category
                </button>
              </form>
            </div>

            <!-- Categories Table -->
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
              <thead>
                <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1; color: #475569;">
                  <th style="padding: 10px 12px; width: 50px;">ID</th>
                  <th style="padding: 10px 12px;">Category Name</th>
                  <th style="padding: 10px 12px; width: 180px;">URL Slug</th>
                  <th style="padding: 10px 12px;">Description</th>
                  <th style="padding: 10px 12px; text-align: center; width: 90px;">Books</th>
                  <th style="padding: 10px 12px; text-align: center; width: 80px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dsDanhMuc as $dm): ?>
                  <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 12px; font-weight: 700; color: #64748b;">#<?= $dm['id'] ?></td>
                    <td style="padding: 10px 12px; font-weight: 700; color: #1e3b6d;">
                      <?= e(layTenDanhMucEn($dm['id'], $dm['ten'])) ?>
                    </td>
                    <td style="padding: 10px 12px;">
                      <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; color: #475569;"><?= e($dm['slug']) ?></code>
                    </td>
                    <td style="padding: 10px 12px; color: #64748b;"><?= e($dm['mo_ta'] ?? '') ?></td>
                    <td style="padding: 10px 12px; text-align: center; font-weight: 700;">
                      <span style="background: #eff6ff; color: #1e40af; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem;">
                        <?= $dm['so_sach'] ?> books
                      </span>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <?php if ((int)$dm['so_sach'] === 0): ?>
                        <form method="POST" action="quan-tri.php?tab=books#categories" style="margin: 0;" onsubmit="return confirm('Delete this empty category #<?= $dm['id'] ?>?');">
                          <?= csrfField() ?>
                          <input type="hidden" name="action_xoa_danh_muc" value="1">
                          <input type="hidden" name="danh_muc_id" value="<?= $dm['id'] ?>">
                          <button type="submit" class="nut nut--nguy-hiem" style="padding: 4px 10px; font-size: 0.78rem;">Delete</button>
                        </form>
                      <?php else: ?>
                        <span style="color: #94a3b8; font-size: 0.78rem; font-weight: 600;">In use</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </details>

        <!-- Books Table -->
        <h3 style="margin: 0 0 12px 0; font-size: 1.15rem; color: #0f172a;">All Platform Books (<?= count($dsTatCaSach) ?>)</h3>
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
              <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1; color: #475569;">
                <th style="padding: 10px 12px; width: 45px;">ID</th>
                <th style="padding: 10px 12px; width: 60px;">Cover</th>
                <th style="padding: 10px 12px;">Title &amp; Author</th>
                <th style="padding: 10px 12px; width: 140px;">Seller</th>
                <th style="padding: 10px 12px; text-align: right; width: 100px;">Price</th>
                <th style="padding: 10px 12px; text-align: center; width: 70px;">Stock</th>
                <th style="padding: 10px 12px; text-align: center; width: 110px;">Approval</th>
                <th style="padding: 10px 12px; text-align: center; width: 140px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dsTatCaSach as $s): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <td style="padding: 10px 12px; font-weight: 700; color: #64748b;">#<?= $s['id'] ?></td>
                  <td style="padding: 10px 12px;">
                    <img src="<?= e(anhSach($s['anh'])) ?>" alt="Cover" width="40" height="54" style="object-fit: cover; border-radius: 4px;">
                  </td>
                  <td style="padding: 10px 12px;">
                    <strong><a href="chi-tiet.php?id=<?= $s['id'] ?>" target="_blank" style="color: #1e3b6d; text-decoration: none;"><?= e($s['ten']) ?></a></strong>
                    <div style="font-size: 0.78rem; color: #64748b;">
                      <?= e($s['tac_gia'] ?: 'UED Faculty') ?> &bull; <code><?= e($s['isbn'] ?: 'N/A') ?></code>
                    </div>
                  </td>
                  <td style="padding: 10px 12px;">
                    <span style="font-size: 0.82rem; font-weight: 600; color: #b45309;">
                      🏪 <?= e($s['ten_gian_hang'] ?: ($s['ten_chu_sach'] ?: 'UED Official')) ?>
                    </span>
                  </td>
                  <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #dc2626;">
                    <?= vnd($s['gia']) ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: center; font-weight: 700;">
                    <?= $s['ton_kho'] ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: center;">
                    <form method="POST" action="quan-tri.php?tab=books" style="margin: 0;">
                      <?= csrfField() ?>
                      <input type="hidden" name="action_duyet_sach" value="1">
                      <input type="hidden" name="book_id" value="<?= $s['id'] ?>">
                      <select name="status" onchange="this.form.submit()" style="font-size: 0.78rem; padding: 3px 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-weight: 700; color: <?= ($s['trang_thai_duyet'] ?? 'approved') === 'approved' ? '#047857' : '#991b1b' ?>;">
                        <option value="approved" <?= ($s['trang_thai_duyet'] ?? 'approved') === 'approved' ? 'selected' : '' ?>>✓ Approved</option>
                        <option value="pending" <?= ($s['trang_thai_duyet'] ?? '') === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="rejected" <?= ($s['trang_thai_duyet'] ?? '') === 'rejected' ? 'selected' : '' ?>>✕ Rejected</option>
                      </select>
                    </form>
                  </td>
                  <td style="padding: 10px 12px; text-align: center;">
                    <div style="display: flex; gap: 6px; justify-content: center;">
                      <a href="chi-tiet.php?id=<?= $s['id'] ?>" target="_blank" class="nut nut--vien" style="padding: 3px 8px; font-size: 0.78rem;">View</a>
                      <form method="POST" action="xoa-san-pham.php" style="margin: 0;" onsubmit="return confirm('Remove violating book #<?= $s['id'] ?> from the platform?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <button type="submit" class="nut nut--nguy-hiem" style="padding: 3px 8px; font-size: 0.78rem;" title="Delete violation">Remove</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      <?php elseif ($tabHienTai === 'orders'): ?>
        <!-- TAB 4: ORDER MANAGEMENT -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          All Customer Orders (<?= count($dsDonHang) ?>)
        </h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 20px 0;">
          View customer purchase orders, update status, and manage refunds/cancellations.
        </p>

        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
              <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1; color: #475569;">
                <th style="padding: 10px 12px; width: 60px;">ID</th>
                <th style="padding: 10px 12px; width: 140px;">Date</th>
                <th style="padding: 10px 12px;">Customer</th>
                <th style="padding: 10px 12px;">Shipping Address</th>
                <th style="padding: 10px 12px; width: 120px;">Payment</th>
                <th style="padding: 10px 12px; text-align: right; width: 110px;">Total</th>
                <th style="padding: 10px 12px; text-align: center; width: 140px;">Status</th>
                <th style="padding: 10px 12px; text-align: center; width: 90px;">Details</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dsDonHang as $dh): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <td style="padding: 10px 12px; font-weight: 700; color: #1e3b6d;">#<?= $dh['id'] ?></td>
                  <td style="padding: 10px 12px; color: #64748b; font-size: 0.82rem;"><?= date('d/m/Y H:i', strtotime($dh['ngay_tao'])) ?></td>
                  <td style="padding: 10px 12px;">
                    <strong><?= e($dh['ho_ten']) ?></strong><br>
                    <small style="color: #64748b;"><?= e($dh['email']) ?></small>
                  </td>
                  <td style="padding: 10px 12px; font-size: 0.82rem; color: #475569;">
                    <?= e($dh['dia_chi']) ?><br>
                    📞 <?= e($dh['so_dien_thoai']) ?>
                  </td>
                  <td style="padding: 10px 12px; font-size: 0.82rem;">
                    <strong><?= e($dh['phuong_thuc_tt'] ?? 'COD') ?></strong><br>
                    <small style="color: <?= $dh['trang_thai_tt'] === 'da_thanh_toan' ? '#059669' : '#d97706' ?>; font-weight: 700;">
                      <?= $dh['trang_thai_tt'] === 'da_thanh_toan' ? '✓ Paid' : '⏳ Pending' ?>
                    </small>
                  </td>
                  <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #dc2626;">
                    <?= vnd($dh['tong_tien']) ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: center;">
                    <form method="POST" action="quan-tri.php?tab=orders" style="margin: 0;">
                      <?= csrfField() ?>
                      <input type="hidden" name="action_cap_nhat_don_admin" value="1">
                      <input type="hidden" name="don_id" value="<?= $dh['id'] ?>">
                      <select name="trang_thai" onchange="this.form.submit()" style="font-size: 0.8rem; padding: 4px 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-weight: 700;">
                        <option value="cho_xu_ly" <?= $dh['trang_thai'] === 'cho_xu_ly' ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="dang_giao" <?= $dh['trang_thai'] === 'dang_giao' ? 'selected' : '' ?>>⚡ Shipping</option>
                        <option value="da_giao" <?= $dh['trang_thai'] === 'da_giao' ? 'selected' : '' ?>>✓ Delivered</option>
                        <option value="da_huy" <?= $dh['trang_thai'] === 'da_huy' ? 'selected' : '' ?>>✕ Cancelled / Refund</option>
                      </select>
                    </form>
                  </td>
                  <td style="padding: 10px 12px; text-align: center;">
                    <a href="don-hang.php?id=<?= $dh['id'] ?>" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.8rem;">View</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      <?php elseif ($tabHienTai === 'payments'): ?>
        <!-- TAB: PAYMENTS & GATEWAY MANAGEMENT -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
          <div>
            <h2 style="margin: 0 0 6px 0; font-size: 1.4rem; color: #0f172a; font-weight: 800;">
              💳 Platform Payments &amp; Gateway Management
            </h2>
            <p style="color: #64748b; font-size: 0.92rem; margin: 0;">
              Supervise all transactions, verify bank receipts, process refunds, audit suspicious payments, and manage payment methods.
            </p>
          </div>
          <div style="display: flex; gap: 10px;">
            <a href="quan-tri.php?export_payments_csv=1" class="nut nut--chinh" style="padding: 9px 18px; font-size: 0.88rem; background: #059669; border-color: #059669;">
              📥 Export CSV Report
            </a>
            <button onclick="window.print()" class="nut nut--vien" style="padding: 9px 16px; font-size: 0.88rem;">
              🖨️ Print Summary
            </button>
          </div>
        </div>

        <!-- 1. Financial KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 25px;">
          <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Gross Transaction Volume</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #1e3b6d; margin-top: 4px;"><?= vnd((int)$payMetrics['total_volume']) ?></div>
            <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">Total order payment value</div>
          </div>

          <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 18px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #166534; text-transform: uppercase;">Cleared Paid Revenue</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #059669; margin-top: 4px;"><?= vnd((int)$payMetrics['paid_volume']) ?></div>
            <div style="font-size: 0.78rem; color: #15803d; margin-top: 2px;">Settled &amp; verified funds</div>
          </div>

          <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 18px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #1e40af; text-transform: uppercase;">Pending Verification</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #2563eb; margin-top: 4px;"><?= vnd((int)$payMetrics['verifying_volume']) ?></div>
            <div style="font-size: 0.78rem; color: #3b82f6; margin-top: 2px;"><?= (int)$payMetrics['verifying_count'] ?> receipts submitted</div>
          </div>

          <div style="background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 10px; padding: 18px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #9d174d; text-transform: uppercase;">Total Refunded</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #be185d; margin-top: 4px;"><?= vnd((int)$payMetrics['refunded_volume']) ?></div>
            <div style="font-size: 0.78rem; color: #db2777; margin-top: 2px;">Reversed payments</div>
          </div>
        </div>

        <!-- 2. Suspicious Transactions / Fraud Monitor Panel -->
        <div style="margin-bottom: 25px;">
          <?php if (!empty($dsNghiVan)): ?>
            <div style="background: #fff7ed; border: 1.5px solid #fdba74; border-radius: 10px; padding: 18px 20px;">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                <span style="font-size: 1.3rem;">⚠️</span>
                <strong style="color: #9a3412; font-size: 1rem;">Payment Security Alert: <?= count($dsNghiVan) ?> Suspicious <?= count($dsNghiVan) > 1 ? 'Anomalies' : 'Anomaly' ?> Detected</strong>
              </div>
              <ul style="margin: 0; padding-left: 20px; font-size: 0.88rem; color: #7c2d12;">
                <?php foreach ($dsNghiVan as $nv): ?>
                  <li style="margin-bottom: 6px;">
                    <strong><?= e($nv['title']) ?>:</strong> <?= e($nv['detail']) ?>
                    <?php if (!empty($nv['payment_ids'])): ?>
                      <span style="margin-left: 6px;">
                        (Review IDs: 
                        <?php foreach ($nv['payment_ids'] as $pid): ?>
                          <a href="quan-tri.php?tab=payments&pay_q=<?= urlencode($pid) ?>" style="color: #c2410c; font-weight: bold; text-decoration: underline;">#<?= e($pid) ?></a>
                        <?php endforeach; ?>
                        )
                      </span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php else: ?>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 18px; display: flex; align-items: center; gap: 10px; color: #166534; font-size: 0.88rem;">
              <span style="font-size: 1.2rem;">🛡️</span>
              <span><strong>Security Audit Passed:</strong> No duplicate transaction references, amount discrepancies, or stale anomalies detected.</span>
            </div>
          <?php endif; ?>
        </div>

        <!-- 3. Filter & Search Bar -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
          <form method="GET" action="quan-tri.php" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="tab" value="payments">

            <div style="flex: 2; min-width: 200px;">
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Search Transactions</label>
              <input type="text" name="pay_q" value="<?= e($searchQ) ?>" placeholder="Transaction ID, Order #, Customer..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; box-sizing: border-box;">
            </div>

            <div style="flex: 1; min-width: 150px;">
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Payment Status</label>
              <select name="pay_status" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem;">
                <option value="all" <?= $locStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="verifying" <?= $locStatus === 'verifying' ? 'selected' : '' ?>>🔍 Verifying (Pending Check)</option>
                <option value="pending" <?= $locStatus === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                <option value="paid" <?= $locStatus === 'paid' ? 'selected' : '' ?>>✓ Paid</option>
                <option value="rejected" <?= $locStatus === 'rejected' ? 'selected' : '' ?>>✗ Rejected</option>
                <option value="refunded" <?= $locStatus === 'refunded' ? 'selected' : '' ?>>↩ Refunded</option>
                <option value="failed" <?= $locStatus === 'failed' ? 'selected' : '' ?>>⚠ Failed</option>
              </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Payment Method</label>
              <select name="pay_method" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem;">
                <option value="all" <?= $locMethod === 'all' ? 'selected' : '' ?>>All Methods</option>
                <option value="cod" <?= $locMethod === 'cod' ? 'selected' : '' ?>>💵 Cash on Delivery (COD)</option>
                <option value="bank_transfer" <?= $locMethod === 'bank_transfer' ? 'selected' : '' ?>>🏦 Bank Transfer</option>
                <option value="qr_code" <?= $locMethod === 'qr_code' ? 'selected' : '' ?>>📱 QR Code</option>
                <option value="e_wallet" <?= $locMethod === 'e_wallet' ? 'selected' : '' ?>>👛 E-Wallet (MoMo)</option>
              </select>
            </div>

            <div>
              <button type="submit" class="nut nut--chinh" style="padding: 8px 18px; font-size: 0.88rem;">
                Filter
              </button>
              <?php if ($searchQ !== '' || $locStatus !== 'all' || $locMethod !== 'all'): ?>
                <a href="quan-tri.php?tab=payments" class="nut nut--vien" style="padding: 8px 12px; font-size: 0.88rem; margin-left: 4px;">Reset</a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <!-- 4. Transactions Ledger Table -->
        <div style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; overflow-x: auto; margin-bottom: 35px;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left;">
            <thead>
              <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; color: #475569;">
                <th style="padding: 10px 12px; width: 60px;">ID</th>
                <th style="padding: 10px 12px;">Transaction Ref</th>
                <th style="padding: 10px 12px; width: 70px;">Order</th>
                <th style="padding: 10px 12px;">Customer</th>
                <th style="padding: 10px 12px; width: 130px;">Method</th>
                <th style="padding: 10px 12px; width: 110px; text-align: right;">Amount</th>
                <th style="padding: 10px 12px; width: 110px;">Status</th>
                <th style="padding: 10px 12px; width: 100px;">Receipt Proof</th>
                <th style="padding: 10px 12px; text-align: center; width: 180px;">Admin Operations</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($dsAllPayments)): ?>
                <tr>
                  <td colspan="9" style="padding: 30px; text-align: center; color: #64748b;">
                    No payment transactions found matching the filter criteria.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($dsAllPayments as $p): ?>
                  <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 12px; color: #64748b; font-weight: 700;">
                      #<?= $p['payment_id'] ?>
                    </td>
                    <td style="padding: 10px 12px;">
                      <code style="font-weight: 700; color: #1e40af;"><?= e($p['transaction_id'] ?: 'TXN-BN-' . $p['payment_id']) ?></code>
                      <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                        <?= $p['payment_date'] ? date('d/m/Y H:i', strtotime($p['payment_date'])) : date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                      </div>
                    </td>
                    <td style="padding: 10px 12px; font-weight: 700;">
                      <a href="don-hang.php?id=<?= $p['order_id'] ?>" style="color: #0284c7; text-decoration: none;">
                        #<?= $p['order_id'] ?>
                      </a>
                    </td>
                    <td style="padding: 10px 12px;">
                      <strong style="color: #0f172a;"><?= e($p['customer_name']) ?></strong><br>
                      <small style="color: #64748b;"><?= e($p['customer_email']) ?></small>
                    </td>
                    <td style="padding: 10px 12px;">
                      <?= nhanPhuongThucTT($p['payment_method']) ?>
                    </td>
                    <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #dc2626;">
                      <?= vnd($p['amount']) ?>
                    </td>
                    <td style="padding: 10px 12px;">
                      <?= huyHieuTrangThaiTT($p['payment_status']) ?>
                      <?php if (!empty($p['admin_note'])): ?>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px; max-width: 140px; word-break: break-word;">
                          <?= e($p['admin_note']) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px;">
                      <?php if (!empty($p['payment_proof'])): ?>
                        <a href="uploads/receipts/<?= e($p['payment_proof']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; color: #1d4ed8; font-weight: 600; text-decoration: underline; font-size: 0.82rem;">
                          📄 Proof
                        </a>
                      <?php else: ?>
                        <span style="color: #94a3b8; font-size: 0.8rem;">—</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                        <?php if ($p['payment_status'] === 'verifying' || ($p['payment_status'] === 'pending' && !empty($p['payment_proof']))): ?>
                          <!-- Approve Bank Transfer -->
                          <form method="POST" action="quan-tri.php?tab=payments" style="margin: 0;" onsubmit="return confirm('Approve payment for Order #<?= $p['order_id'] ?>?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action_admin_duyet_tt" value="1">
                            <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                            <button type="submit" class="nut nut--chinh" style="padding: 4px 8px; font-size: 0.78rem; background: #059669; border-color: #059669;" title="Approve Payment">
                              ✓ Approve
                            </button>
                          </form>

                          <!-- Reject Receipt with Reason -->
                          <form method="POST" action="quan-tri.php?tab=payments" style="margin: 0;" onsubmit="var r = prompt('Reason for rejecting receipt:'); if(!r) return false; this.rejection_reason.value = r; return true;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action_admin_tuchoi_tt" value="1">
                            <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                            <input type="hidden" name="rejection_reason" value="">
                            <button type="submit" class="nut" style="padding: 4px 8px; font-size: 0.78rem; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;" title="Reject Receipt">
                              ✗ Reject
                            </button>
                          </form>
                        <?php elseif ($p['payment_status'] === 'paid'): ?>
                          <a href="thanh-toan.php?action=receipt&order_id=<?= $p['order_id'] ?>" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.78rem;" title="View Digital Receipt">
                            🧾 Receipt
                          </a>

                          <!-- Refund Form -->
                          <form method="POST" action="quan-tri.php?tab=payments" style="margin: 0;" onsubmit="var r = prompt('Reason for refund:'); if(!r) return false; this.refund_reason.value = r; return true;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action_admin_hoan_tien" value="1">
                            <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                            <input type="hidden" name="refund_reason" value="">
                            <button type="submit" class="nut" style="padding: 4px 8px; font-size: 0.78rem; background: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe;" title="Process Refund">
                              ↩ Refund
                            </button>
                          </form>
                        <?php else: ?>
                          <a href="don-hang.php?id=<?= $p['order_id'] ?>" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.78rem;">
                            Order
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- 4. Bank Account, PromptPay & QR Code Configurations -->
        <div style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 24px; margin-bottom: 25px;">
          <div style="margin-bottom: 18px;">
            <h3 style="margin: 0 0 4px 0; font-size: 1.2rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
              <span>🏦</span> Cấu hình Tài khoản Ngân hàng, PromptPay &amp; QR Code (Payment Gateway Details)
            </h3>
            <p style="margin: 0; color: #64748b; font-size: 0.88rem;">
              Chỉnh sửa thông tin tài khoản thụ hưởng, số tài khoản, tên ngân hàng, số điện thoại PromptPay/MoMo và ảnh mã QR hiển thị cho khách hàng khi thanh toán.
            </p>
          </div>

          <form method="POST" action="quan-tri.php?tab=payments" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action_luu_cai_dat_ngan_hang" value="1">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 20px;">
              <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 5px;">
                  Tên Ngân hàng / Bank Name:
                </label>
                <input type="text" name="bank_name" value="<?= e($caiDatNganHang['bank_name']) ?>" placeholder="e.g. Vietcombank / Kasikornbank / SCB" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
              </div>

              <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 5px;">
                  Chi nhánh / Branch:
                </label>
                <input type="text" name="bank_branch" value="<?= e($caiDatNganHang['bank_branch']) ?>" placeholder="e.g. Danang City Branch / Bangkok" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
              </div>

              <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 5px;">
                  Số tài khoản / Account Number:
                </label>
                <input type="text" name="bank_account_number" value="<?= e($caiDatNganHang['bank_account_number']) ?>" placeholder="e.g. 02363841323" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; font-weight: 700; color: #1e40af; box-sizing: border-box;">
              </div>

              <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 5px;">
                  Tên Chủ tài khoản / Account Holder:
                </label>
                <input type="text" name="bank_account_name" value="<?= e($caiDatNganHang['bank_account_name']) ?>" placeholder="e.g. BOOKNEST STORE" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
              </div>

              <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 5px;">
                  Số PromptPay / Hotline chuyển khoản:
                </label>
                <input type="text" name="promptpay_number" value="<?= e($caiDatNganHang['promptpay_number']) ?>" placeholder="e.g. 0905123456" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
              </div>

              <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 5px;">
                  Nhà cung cấp Ví điện tử / Wallet Provider:
                </label>
                <input type="text" name="wallet_provider" value="<?= e($caiDatNganHang['wallet_provider']) ?>" placeholder="e.g. MoMo E-Wallet / TrueMoney" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
              </div>

              <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 5px;">
                  Thông tin người nhận Ví / Wallet Receiver:
                </label>
                <input type="text" name="wallet_receiver" value="<?= e($caiDatNganHang['wallet_receiver']) ?>" placeholder="e.g. 0905123456 (BookNest Admin)" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
              </div>

              <div>
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 5px;">
                  Tải lên ảnh Mã QR Code (JPG, PNG, WebP):
                </label>
                <input type="file" name="qr_code_file" accept="image/jpeg,image/png,image/webp" style="width: 100%; padding: 6px; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 0.85rem; box-sizing: border-box;">
                <?php if (!empty($caiDatNganHang['qr_code_image'])): ?>
                  <div style="margin-top: 6px; font-size: 0.8rem; color: #059669; display: flex; align-items: center; gap: 8px;">
                    <span>✓ Current QR:</span>
                    <a href="<?= e($caiDatNganHang['qr_code_image']) ?>" target="_blank" style="text-decoration: underline; font-weight: bold;">View Image</a>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <button type="submit" class="nut nut--chinh" style="padding: 10px 24px; font-size: 0.92rem; background: #1e40af; border-color: #1e40af;">
              💾 Lưu Cập nhật Thông tin Thanh toán (Save Bank &amp; QR Settings)
            </button>
          </form>
        </div>

        <!-- 5. Manage Available Payment Methods Section -->
        <div style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
              <h3 style="margin: 0; font-size: 1.15rem; color: #0f172a;">⚙️ Manage Available Payment Methods</h3>
              <p style="margin: 3px 0 0 0; color: #64748b; font-size: 0.85rem;">Enable or disable payment options and update account instructions displayed to customers.</p>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            <?php foreach ($dsAllMethods as $pm): ?>
              <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px;">
                <form method="POST" action="quan-tri.php?tab=payments">
                  <?= csrfField() ?>
                  <input type="hidden" name="action_luu_phuong_thuc" value="1">
                  <input type="hidden" name="method_id" value="<?= $pm['id'] ?>">

                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <span style="font-size: 1.4rem;"><?= e($pm['icon']) ?></span>
                      <strong style="color: #0f172a; font-size: 0.95rem;"><?= e($pm['name']) ?></strong>
                    </div>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: <?= $pm['is_active'] ? '#059669' : '#94a3b8' ?>; cursor: pointer;">
                      <input type="checkbox" name="is_active" value="1" <?= $pm['is_active'] ? 'checked' : '' ?>>
                      <?= $pm['is_active'] ? 'Enabled' : 'Disabled' ?>
                    </label>
                  </div>

                  <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #64748b; margin-bottom: 3px;">Description / Subtitle</label>
                    <input type="text" name="description" value="<?= e($pm['description']) ?>" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; box-sizing: border-box;">
                  </div>

                  <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #64748b; margin-bottom: 3px;">Payment Instructions / Account Details</label>
                    <textarea name="instructions" rows="3" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; box-sizing: border-box;"><?= e($pm['instructions']) ?></textarea>
                  </div>

                  <button type="submit" class="nut nut--vien" style="width: 100%; padding: 6px 0; font-size: 0.82rem; font-weight: 700;">
                    Save Configuration
                  </button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      <?php elseif ($tabHienTai === 'sellers'): ?>
        <!-- TAB 5: SELLER MANAGEMENT -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Seller Management &amp; Approvals (<?= count($dsSellers) ?>)
        </h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 20px 0;">
          Monitor partner bookstores, approve registrations, track performance, and suspend violating sellers.
        </p>

        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
              <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1; color: #475569;">
                <th style="padding: 10px 12px; width: 50px;">ID</th>
                <th style="padding: 10px 12px;">Store Name &amp; Owner</th>
                <th style="padding: 10px 12px;">Contact</th>
                <th style="padding: 10px 12px; text-align: center; width: 90px;">Books</th>
                <th style="padding: 10px 12px; text-align: right; width: 130px;">Total Sales</th>
                <th style="padding: 10px 12px; text-align: center; width: 140px;">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dsSellers as $sl): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <td style="padding: 10px 12px; font-weight: 700; color: #64748b;">#<?= $sl['id'] ?></td>
                  <td style="padding: 10px 12px;">
                    <strong style="color: #b45309; font-size: 0.95rem;">🏪 <?= e($sl['ten_gian_hang'] ?: ($sl['ho_ten'] . "'s Store")) ?></strong><br>
                    <small style="color: #64748b;">Owner: <?= e($sl['ho_ten']) ?></small>
                  </td>
                  <td style="padding: 10px 12px; font-size: 0.85rem; color: #475569;">
                    📧 <?= e($sl['email']) ?><br>
                    📞 <?= e($sl['so_dien_thoai'] ?: 'N/A') ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: center; font-weight: 700;"><?= $sl['so_sach'] ?></td>
                  <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #059669;"><?= vnd((int)$sl['doanh_thu']) ?></td>
                  <td style="padding: 10px 12px; text-align: center;">
                    <form method="POST" action="quan-tri.php?tab=sellers" style="margin: 0;">
                      <?= csrfField() ?>
                      <input type="hidden" name="action_duyet_seller" value="1">
                      <input type="hidden" name="seller_id" value="<?= $sl['id'] ?>">
                      <select name="seller_status" onchange="this.form.submit()" style="font-size: 0.8rem; padding: 4px 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-weight: 700;">
                        <option value="active" <?= ($sl['trang_thai_seller'] ?? 'active') === 'active' ? 'selected' : '' ?>>✓ Active</option>
                        <option value="pending" <?= ($sl['trang_thai_seller'] ?? '') === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="suspended" <?= ($sl['trang_thai_seller'] ?? '') === 'suspended' ? 'selected' : '' ?>>⛔ Suspended</option>
                        <option value="rejected" <?= ($sl['trang_thai_seller'] ?? '') === 'rejected' ? 'selected' : '' ?>>✕ Rejected</option>
                      </select>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      <?php elseif ($tabHienTai === 'reports'): ?>
        <!-- TAB 6: REPORTS & ANALYTICS -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          Reports &amp; Revenue Analytics
        </h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 20px 0;">
          Financial reports, revenue breakdown by category, and platform performance metrics.
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
            <h3 style="margin: 0 0 10px 0; font-size: 1.05rem; color: #1e3b6d;">Financial Summary</h3>
            <p style="margin: 4px 0;">Total Orders Placed: <strong><?= $tongDonHang ?></strong></p>
            <p style="margin: 4px 0;">Total Gross Sales: <strong style="color: #059669;"><?= vnd($tongDoanhThu) ?></strong></p>
            <p style="margin: 4px 0;">Total Physical Copies in Stock: <strong><?= number_format((int)$db->query('SELECT COALESCE(SUM(ton_kho), 0) FROM san_pham')->fetchColumn()) ?></strong></p>
          </div>

          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
            <h3 style="margin: 0 0 10px 0; font-size: 1.05rem; color: #1e3b6d;">Platform Capacity</h3>
            <p style="margin: 4px 0;">Total Partner Sellers: <strong><?= $tongSeller ?></strong></p>
            <p style="margin: 4px 0;">Active Customer Accounts: <strong><?= $tongKhachHang ?></strong></p>
            <p style="margin: 4px 0;">Total Available Titles: <strong><?= $tongSach ?></strong></p>
          </div>
        </div>

        <h3 style="margin: 20px 0 12px 0; font-size: 1.15rem; color: #0f172a;">Sales Volume by Category</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
          <thead>
            <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #cbd5e1;">
              <th style="padding: 10px 12px;">Category</th>
              <th style="padding: 10px 12px; text-align: center;">Total Titles</th>
              <th style="padding: 10px 12px; text-align: right;">Estimated Revenue</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dsDanhMuc as $dm): 
              $stRevDm = $db->prepare('SELECT COALESCE(SUM(ct.so_luong * ct.don_gia), 0) FROM chi_tiet_don ct JOIN san_pham sp ON ct.san_pham_id = sp.id WHERE sp.danh_muc_id = ?');
              $stRevDm->execute([$dm['id']]);
              $revDm = (int)$stRevDm->fetchColumn();
            ?>
              <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px 12px; font-weight: 700; color: #1e3b6d;"><?= e(layTenDanhMucEn($dm['id'], $dm['ten'])) ?></td>
                <td style="padding: 10px 12px; text-align: center;"><?= $dm['so_sach'] ?></td>
                <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #059669;"><?= vnd($revDm) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      <?php elseif ($tabHienTai === 'settings'): ?>
        <!-- TAB 7: SYSTEM SETTINGS -->
        <h2 style="margin: 0 0 8px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">
          System Settings &amp; Site Configuration
        </h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 20px 0;">
          Configure platform information, announcements, hotline, and promotional campaigns.
        </p>

        <form method="POST" action="quan-tri.php?tab=settings" style="max-width: 600px;">
          <?= csrfField() ?>
          <input type="hidden" name="action_luu_cai_dat" value="1">

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Platform Name:</label>
            <input type="text" name="site_name" value="<?= e($caiDatMap['site_name'] ?? 'BookNest - UED Student Bookstore') ?>" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Support Hotline:</label>
            <input type="text" name="hotline" value="<?= e($caiDatMap['hotline'] ?? '0236.3841.323') ?>" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Contact Email:</label>
            <input type="email" name="email_lien_he" value="<?= e($caiDatMap['email_lien_he'] ?? 'hotro@ued.vn') ?>" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Campus Address:</label>
            <input type="text" name="dia_chi" value="<?= e($caiDatMap['dia_chi'] ?? '459 Ton Duc Thang, Lien Chieu, Da Nang') ?>" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
          </div>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">Top Banner Announcement:</label>
            <textarea name="banner_thong_bao" rows="2" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?= e($caiDatMap['banner_thong_bao'] ?? 'New Semester Book Fair 2026: Up to 25% OFF all textbooks!') ?></textarea>
          </div>

          <div style="margin-bottom: 24px;">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #334155; cursor: pointer;">
              <input type="checkbox" name="khuyen_mai_active" value="1" <?= (!empty($caiDatMap['khuyen_mai_active']) && $caiDatMap['khuyen_mai_active'] === '1') ? 'checked' : '' ?>>
              <span>Enable 25% Student Discount Campaign Across All Textbooks</span>
            </label>
          </div>

          <button type="submit" class="nut nut--chinh" style="padding: 11px 24px; font-weight: 700; font-size: 0.95rem; border-radius: 8px;">
            Save Platform Settings
          </button>
        </form>

      <?php endif; ?>

    </div>
  </div>

</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
