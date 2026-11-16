<?php
/**
 * thanh-toan.php - Customer Payment Gateway, Proof Upload & Digital Receipt
 * BookNest - UED Student Online Bookstore
 * Module 10: Complete Payment System
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/don-hang.php';
require_once __DIR__ . '/inc/thanh-toan.php';

// Authentication required
yeuCauDangNhap();

$u = nguoiDung();
$db = db();
$action = $_GET['action'] ?? 'pay';
$orderId = filter_var($_GET['order_id'] ?? $_POST['order_id'] ?? '', FILTER_VALIDATE_INT);

// -----------------------------------------------------------------------------
// POST HANDLERS: Upload Receipt & Simulate Gateway
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Upload Payment Receipt
    if (isset($_POST['action_upload_proof'])) {
        $paymentId = filter_var($_POST['payment_id'] ?? '', FILTER_VALIDATE_INT);
        if (!$paymentId || !isset($_FILES['payment_proof'])) {
            $_SESSION['flash_loi'] = 'Invalid upload request. Please choose an image file.';
        } else {
            $res = xuLyTaiBienLai($paymentId, (int)$u['id'], $_FILES['payment_proof']);
            if ($res['success']) {
                $_SESSION['flash'] = $res['message'];
            } else {
                $_SESSION['flash_loi'] = $res['error'];
            }
        }
        header("Location: thanh-toan.php?order_id=$orderId");
        exit;
    }

    // 2. Instant E-Wallet Gateway Simulation
    if (isset($_POST['action_simulate_gateway'])) {
        $paymentId = filter_var($_POST['payment_id'] ?? '', FILTER_VALIDATE_INT);
        if (!$paymentId) {
            $_SESSION['flash_loi'] = 'Invalid transaction reference.';
        } else {
            $res = moPhongThanhToanCongTrucTuyen($paymentId, (int)$u['id']);
            if ($res['success']) {
                $_SESSION['flash'] = '✓ ' . $res['message'];
            } else {
                $_SESSION['flash_loi'] = $res['error'];
            }
        }
        header("Location: thanh-toan.php?order_id=$orderId");
        exit;
    }
}

// -----------------------------------------------------------------------------
// CASE A: VIEW DIGITAL RECEIPT (INVOICE)
// -----------------------------------------------------------------------------
if ($action === 'receipt' && $orderId) {
    // Fetch order & payment
    $stOrder = $db->prepare('SELECT dh.*, nd.ho_ten, nd.email FROM don_hang dh JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id WHERE dh.id = ?');
    $stOrder->execute([$orderId]);
    $don = $stOrder->fetch();

    if (!$don) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }

    // Permission check: Customer owner, Admin, or Seller who has books in this order
    $coQuyen = ($u['vai_tro'] === 'quan_tri') || ((int)$don['nguoi_dung_id'] === (int)$u['id']);
    if (!$coQuyen && $u['vai_tro'] === 'chu_sach') {
        $stCheck = $db->prepare('SELECT COUNT(*) FROM chi_tiet_don ctd JOIN san_pham sp ON ctd.san_pham_id = sp.id WHERE ctd.don_hang_id = ? AND sp.chu_sach_id = ?');
        $stCheck->execute([$orderId, $u['id']]);
        if ($stCheck->fetchColumn() > 0) {
            $coQuyen = true;
        }
    }

    if (!$coQuyen) {
        http_response_code(403);
        require __DIR__ . '/403.php';
        exit;
    }

    $stPay = $db->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY payment_id DESC LIMIT 1');
    $stPay->execute([$orderId]);
    $pay = $stPay->fetch();

    $stItems = $db->prepare('SELECT ctd.*, sp.ten, sp.ten_en, sp.anh FROM chi_tiet_don ctd JOIN san_pham sp ON ctd.san_pham_id = sp.id WHERE ctd.don_hang_id = ?');
    $stItems->execute([$orderId]);
    $items = $stItems->fetchAll();

    $receiptNo = 'REC-' . date('Ymd', strtotime($don['ngay_tao'])) . '-' . str_pad((string)$don['id'], 5, '0', STR_PAD_LEFT);

    $tieuDe = 'Digital Receipt #' . $receiptNo;
    $trang = 'don-hang';
    require __DIR__ . '/inc/header.php';
    ?>
    <main class="noi-dung-chinh khung-chua" style="max-width: 820px; margin: 30px auto;">
      <div class="chi-tren-man-hinh" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="thanh-toan.php?order_id=<?= $orderId ?>" class="nut nut--vien">&larr; Back to Payment Details</a>
        <div style="display: flex; gap: 10px;">
          <button onclick="window.print()" class="nut nut--chinh" style="padding: 8px 18px; display: inline-flex; align-items: center; gap: 6px;">
            🖨️ Print Digital Receipt
          </button>
          <a href="don-hang.php" class="nut nut--vien">All Orders</a>
        </div>
      </div>

      <!-- DIGITAL RECEIPT CARD -->
      <div id="digital-receipt-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); position: relative; overflow: hidden;">
        
        <!-- Official Paid Stamp Watermark -->
        <?php if ($pay && $pay['payment_status'] === 'paid'): ?>
          <div style="position: absolute; right: 40px; top: 120px; border: 3px solid #059669; color: #059669; padding: 8px 24px; border-radius: 8px; font-size: 1.4rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; transform: rotate(-12deg); opacity: 0.85; pointer-events: none;">
            ✓ OFFICIAL PAID
          </div>
        <?php endif; ?>

        <!-- Receipt Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 25px; margin-bottom: 25px;">
          <div>
            <div style="display: flex; align-items: center; gap: 10px;">
              <span style="font-size: 2rem;">📚</span>
              <div>
                <h1 style="margin: 0; font-size: 1.6rem; color: #1e3b6d;">BookNest Official Receipt</h1>
                <div style="font-size: 0.85rem; color: #64748b;">UED Student Bookstore &bull; University of Danang</div>
              </div>
            </div>
            <div style="margin-top: 10px; font-size: 0.85rem; color: #475569;">
              Hotline: 0236.3841.323 &bull; Email: booknest.ued@gmail.com<br>
              Campus: 459 Ton Duc Thang, Lien Chieu, Da Nang
            </div>
          </div>

          <div style="text-align: right;">
            <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: #64748b;">Receipt Number</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #0f172a;"><?= e($receiptNo) ?></div>
            <div style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">
              Order ID: <strong>#<?= $don['id'] ?></strong><br>
              Date: <?= date('d/m/Y H:i', strtotime($don['ngay_tao'])) ?>
            </div>
          </div>
        </div>

        <!-- Billing & Shipping Information -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; font-size: 0.9rem;">
          <div style="background: #f8fafc; padding: 15px 18px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <div style="font-weight: 700; color: #1e3b6d; margin-bottom: 6px; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Billed To (Student Customer)</div>
            <div style="font-weight: 700; font-size: 1rem; color: #0f172a;"><?= e($don['ho_ten']) ?></div>
            <div style="color: #475569;">Email: <?= e($don['email']) ?></div>
            <div style="color: #475569;">Phone: <?= e($don['so_dien_thoai']) ?></div>
            <div style="color: #475569; margin-top: 4px;"><strong>Delivery:</strong> <?= e($don['dia_chi']) ?></div>
          </div>

          <div style="background: #f8fafc; padding: 15px 18px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <div style="font-weight: 700; color: #1e3b6d; margin-bottom: 6px; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Payment Information</div>
            <div><strong>Method:</strong> <?= nhanPhuongThucTT($pay['payment_method'] ?? $don['phuong_thuc_tt']) ?></div>
            <div><strong>Status:</strong> <?= huyHieuTrangThaiTT($pay['payment_status'] ?? 'pending') ?></div>
            <div><strong>Transaction ID:</strong> <code style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px;"><?= e($pay['transaction_id'] ?? 'N/A') ?></code></div>
            <div><strong>Payment Date:</strong> <?= $pay && $pay['payment_date'] ? date('d/m/Y H:i', strtotime($pay['payment_date'])) : 'Pending settlement' ?></div>
          </div>
        </div>

        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 0.92rem;">
          <thead>
            <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; text-align: left;">
              <th style="padding: 10px 12px; width: 40px; text-align: center;">#</th>
              <th style="padding: 10px 12px;">Textbook Item Description</th>
              <th style="padding: 10px 12px; width: 110px; text-align: right;">Unit Price</th>
              <th style="padding: 10px 12px; width: 60px; text-align: center;">Qty</th>
              <th style="padding: 10px 12px; width: 130px; text-align: right;">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $stt = 1;
            foreach ($items as $item): 
            ?>
              <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px 12px; text-align: center; color: #64748b;"><?= $stt++ ?></td>
                <td style="padding: 10px 12px; font-weight: 600; color: #0f172a;">
                  <?= e(tieuDeSach($item)) ?>
                </td>
                <td style="padding: 10px 12px; text-align: right; color: #475569;"><?= vnd($item['don_gia']) ?></td>
                <td style="padding: 10px 12px; text-align: center; font-weight: bold;"><?= $item['so_luong'] ?></td>
                <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #0f172a;">
                  <?= vnd($item['don_gia'] * $item['so_luong']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Receipt Totals -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 30px;">
          <div style="width: 320px;">
            <div style="display: flex; justify-content: space-between; padding: 6px 0; color: #475569; font-size: 0.9rem;">
              <span>Subtotal:</span>
              <span><?= vnd($don['tong_tien']) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 6px 0; color: #475569; font-size: 0.9rem;">
              <span>Student Subsidy / Discount:</span>
              <span style="color: #059669;">-0 VNĐ</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 6px 0; color: #475569; font-size: 0.9rem;">
              <span>Shipping Fee (Campus Delivery):</span>
              <span style="color: #059669;">Free (0 VNĐ)</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 12px 0 0 0; border-top: 2px solid #0f172a; font-size: 1.25rem; font-weight: 800; color: #dc2626;">
              <span>Total Paid:</span>
              <span><?= vnd($don['tong_tien']) ?></span>
            </div>
          </div>
        </div>

        <!-- Receipt Footer Notice -->
        <div style="border-top: 1px dashed #cbd5e1; padding-top: 20px; text-align: center; color: #64748b; font-size: 0.82rem;">
          <p style="margin: 0 0 6px 0;">This is a computer-generated digital receipt issued by BookNest Student Bookstore.</p>
          <p style="margin: 0;">For textbook exchanges or student support inquiries, please present this receipt within 7 days of delivery.</p>
        </div>
      </div>
    </main>

    <style>
    @media print {
      body * { visibility: hidden; }
      #digital-receipt-card, #digital-receipt-card * { visibility: visible; }
      #digital-receipt-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        box-shadow: none !important;
        padding: 20px !important;
      }
      .chi-tren-man-hinh, .header-tmdt, .navbar-tmdt, .top-bar-tmdt, footer {
        display: none !important;
      }
    }
    </style>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

// -----------------------------------------------------------------------------
// CASE B: PAYMENT HISTORY
// -----------------------------------------------------------------------------
if ($action === 'history' || (!$orderId && $action !== 'pay')) {
    $stHist = $db->prepare('
        SELECT p.*, dh.tong_tien, dh.ngay_tao as order_date, dh.trang_thai as order_status
        FROM payments p
        JOIN don_hang dh ON p.order_id = dh.id
        WHERE p.user_id = ?
        ORDER BY p.payment_id DESC
    ');
    $stHist->execute([$u['id']]);
    $history = $stHist->fetchAll();

    $tieuDe = 'My Payment History';
    $trang = 'tai-khoan';
    require __DIR__ . '/inc/header.php';
    ?>
    <main class="noi-dung-chinh khung-chua" style="max-width: 950px; margin: 30px auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
          <h1 style="margin: 0; color: #1e3b6d;">Payment History</h1>
          <p style="margin: 4px 0 0 0; color: #64748b;">Review all payment transactions and download digital receipts.</p>
        </div>
        <a href="don-hang.php" class="nut nut--vien">&larr; Back to Orders</a>
      </div>

      <?php if (empty($history)): ?>
        <div style="text-align: center; padding: 50px 20px; background: #ffffff; border-radius: 8px; border: 1px dashed #cbd5e1;">
          <p style="font-size: 3rem; margin: 0 0 10px 0;">💳</p>
          <h2>No payment transactions found</h2>
          <p style="color: #64748b;">You have not made any payments yet.</p>
          <a href="danh-sach.php" class="nut nut--chinh">Shop Textbooks</a>
        </div>
      <?php else: ?>
        <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
              <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                <th style="padding: 12px 15px;">Transaction ID</th>
                <th style="padding: 12px 15px;">Order</th>
                <th style="padding: 12px 15px;">Method</th>
                <th style="padding: 12px 15px; text-align: right;">Amount</th>
                <th style="padding: 12px 15px;">Status</th>
                <th style="padding: 12px 15px;">Date</th>
                <th style="padding: 12px 15px; text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($history as $h): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <td style="padding: 12px 15px; font-weight: 700; color: #1e40af;">
                    <code><?= e($h['transaction_id'] ?: 'TXN-BN-' . $h['payment_id']) ?></code>
                  </td>
                  <td style="padding: 12px 15px;">
                    <a href="don-hang.php?id=<?= $h['order_id'] ?>" style="font-weight: 600; color: #0284c7; text-decoration: none;">
                      #<?= $h['order_id'] ?>
                    </a>
                  </td>
                  <td style="padding: 12px 15px;">
                    <?= nhanPhuongThucTT($h['payment_method']) ?>
                  </td>
                  <td style="padding: 12px 15px; text-align: right; font-weight: 700; color: #dc2626;">
                    <?= vnd($h['amount']) ?>
                  </td>
                  <td style="padding: 12px 15px;">
                    <?= huyHieuTrangThaiTT($h['payment_status']) ?>
                  </td>
                  <td style="padding: 12px 15px; color: #64748b; font-size: 0.85rem;">
                    <?= $h['payment_date'] ? date('d/m/Y H:i', strtotime($h['payment_date'])) : date('d/m/Y H:i', strtotime($h['created_at'])) ?>
                  </td>
                  <td style="padding: 12px 15px; text-align: center;">
                    <div style="display: inline-flex; gap: 6px;">
                      <?php if ($h['payment_status'] === 'paid'): ?>
                        <a href="thanh-toan.php?action=receipt&order_id=<?= $h['order_id'] ?>" class="nut nut--vien" style="padding: 4px 10px; font-size: 0.82rem;" title="View Digital Receipt">
                          🧾 Receipt
                        </a>
                      <?php else: ?>
                        <a href="thanh-toan.php?order_id=<?= $h['order_id'] ?>" class="nut nut--chinh" style="padding: 4px 10px; font-size: 0.82rem;" title="Complete Payment">
                          Pay / Upload &rarr;
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </main>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

// -----------------------------------------------------------------------------
// CASE C: ORDER PAYMENT SCREEN & RECEIPT UPLOAD
// -----------------------------------------------------------------------------
if (!$orderId) {
    header('Location: thanh-toan.php?action=history');
    exit;
}

// Fetch order details
$stOrder = $db->prepare('SELECT dh.*, nd.ho_ten, nd.email FROM don_hang dh JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id WHERE dh.id = ?');
$stOrder->execute([$orderId]);
$don = $stOrder->fetch();

if (!$don) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Ownership check
if ($u['vai_tro'] !== 'quan_tri' && (int)$don['nguoi_dung_id'] !== (int)$u['id']) {
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

// Ensure payment record exists
$pay = taoHoacLayThanhToan($orderId, (int)$don['nguoi_dung_id'], $don['phuong_thuc_tt'], (int)$don['tong_tien']);

// Fetch items
$stItems = $db->prepare('SELECT ctd.*, sp.ten, sp.anh FROM chi_tiet_don ctd JOIN san_pham sp ON ctd.san_pham_id = sp.id WHERE ctd.don_hang_id = ?');
$stItems->execute([$orderId]);
$items = $stItems->fetchAll();

// Payment method info
$methodCode = chuanHoaMaPhuongThuc($pay['payment_method']);
$pmInfo = layPhuongThucTheoCode($methodCode);
$caiDatTT = layCaiDatThanhToan();

$tieuDe = 'Payment for Order #' . $orderId;
$trang = 'don-hang';
require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 900px; margin: 30px auto;">

  <!-- Breadcrumbs & Status Bar -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d; font-size: 1.6rem;">Payment &bull; Order #<?= $orderId ?></h1>
      <p style="margin: 4px 0 0 0; color: #64748b;">Complete your payment securely for textbooks at BookNest.</p>
    </div>
    <div style="display: flex; gap: 8px;">
      <a href="don-hang.php?id=<?= $orderId ?>" class="nut nut--vien">&larr; Order Details</a>
      <a href="thanh-toan.php?action=history" class="nut nut--vien">Payment History</a>
    </div>
  </div>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;" aria-live="polite">
      <strong>✓ Success:</strong> <?= e($_SESSION['flash']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_loi'])): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;" aria-live="assertive">
      <strong>⚠ Error:</strong> <?= e($_SESSION['flash_loi']) ?>
    </div>
    <?php unset($_SESSION['flash_loi']); ?>
  <?php endif; ?>

  <!-- Payment Status Highlight Banner -->
  <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
      <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: #64748b;">Current Payment Status</div>
      <div style="margin-top: 6px; font-size: 1.15rem; font-weight: 800;">
        <?= huyHieuTrangThaiTT($pay['payment_status']) ?>
      </div>
      <div style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">
        Transaction Reference: <code><?= e($pay['transaction_id']) ?></code>
      </div>
    </div>

    <div style="text-align: right;">
      <div style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: #64748b;">Total Amount Due</div>
      <div style="font-size: 1.6rem; font-weight: 800; color: #dc2626;">
        <?= vnd($don['tong_tien']) ?>
      </div>
    </div>
  </div>

  <div style="display: flex; gap: 25px; flex-wrap: wrap;">

    <!-- Left Column: Payment Method Actions & Receipt Upload -->
    <div style="flex: 3; min-width: 320px;">
      
      <!-- PAID STATE -->
      <?php if ($pay['payment_status'] === 'paid'): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 25px; text-align: center; margin-bottom: 25px;">
          <div style="font-size: 3rem; margin-bottom: 10px;">🎉</div>
          <h2 style="margin: 0 0 8px 0; color: #065f46;">Payment Successfully Completed!</h2>
          <p style="color: #047857; margin-bottom: 20px; font-size: 0.95rem;">
            Thank you! Your payment of <strong><?= vnd($don['tong_tien']) ?></strong> was confirmed on <?= date('d/m/Y H:i', strtotime($pay['payment_date'] ?? $don['ngay_tao'])) ?>. Your textbooks are being prepared for delivery.
          </p>
          <div style="display: inline-flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
            <a href="thanh-toan.php?action=receipt&order_id=<?= $orderId ?>" class="nut nut--chinh" style="padding: 10px 22px; font-size: 1rem;">
              🧾 View &amp; Print Digital Receipt
            </a>
            <a href="don-hang.php?id=<?= $orderId ?>" class="nut nut--vien" style="padding: 10px 20px;">
              View Order Tracking
            </a>
          </div>
        </div>

      <!-- REFUNDED STATE -->
      <?php elseif ($pay['payment_status'] === 'refunded'): ?>
        <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 10px; padding: 25px; text-align: center; margin-bottom: 25px;">
          <div style="font-size: 3rem; margin-bottom: 10px;">↩️</div>
          <h2 style="margin: 0 0 8px 0; color: #5b21b6;">Payment Refunded</h2>
          <p style="color: #6d28d9; margin-bottom: 10px;">
            This order payment of <strong><?= vnd($don['tong_tien']) ?></strong> has been refunded.
          </p>
          <?php if (!empty($pay['admin_note'])): ?>
            <p style="background: #ffffff; padding: 10px; border-radius: 6px; border: 1px solid #e9d5ff; color: #4c1d95; font-size: 0.9rem; margin-bottom: 15px;">
              <strong>Note:</strong> <?= e($pay['admin_note']) ?>
            </p>
          <?php endif; ?>
        </div>

      <!-- VERIFYING STATE -->
      <?php elseif ($pay['payment_status'] === 'verifying'): ?>
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
          <div style="display: flex; gap: 15px; align-items: flex-start;">
            <div style="font-size: 2.2rem;">⏳</div>
            <div>
              <h3 style="margin: 0 0 6px 0; color: #1e40af;">Payment Receipt Under Verification</h3>
              <p style="margin: 0 0 12px 0; color: #3b82f6; font-size: 0.92rem;">
                We have received your payment proof and our administrator or bookstore seller is currently reviewing it. Your order will be approved as soon as the funds are matched.
              </p>
              <?php if (!empty($pay['payment_proof'])): ?>
                <div style="margin-top: 12px;">
                  <span style="font-size: 0.85rem; font-weight: 700; color: #1e40af;">Uploaded Receipt File:</span><br>
                  <a href="uploads/receipts/<?= e($pay['payment_proof']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 6px; color: #1d4ed8; font-weight: 600; text-decoration: underline;">
                    📄 View Uploaded Receipt (<?= e($pay['payment_proof']) ?>)
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

      <!-- REJECTED STATE -->
      <?php elseif ($pay['payment_status'] === 'rejected'): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
          <h3 style="margin: 0 0 6px 0; color: #991b1b;">❌ Payment Receipt Was Rejected</h3>
          <p style="margin: 0 0 10px 0; color: #b91c1c; font-size: 0.92rem;">
            The previous receipt you submitted could not be verified.
          </p>
          <?php if (!empty($pay['admin_note'])): ?>
            <div style="background: #ffffff; padding: 10px 14px; border-radius: 6px; border: 1px solid #fca5a5; color: #7f1d1d; font-size: 0.88rem; margin-bottom: 15px;">
              <strong>Rejection Reason:</strong> <?= e($pay['admin_note']) ?>
            </div>
          <?php endif; ?>
          <p style="margin: 0; font-size: 0.9rem; color: #991b1b;">
            Please check your bank transaction or re-upload a clear receipt image below.
          </p>
        </div>
      <?php endif; ?>

      <!-- PAYMENT METHOD SPECIFIC INSTRUCTIONS -->
      <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h2 style="margin-top: 0; color: #1e3b6d; font-size: 1.25rem;">
          <?= nhanPhuongThucTT($methodCode) ?>
        </h2>

        <!-- METHOD: COD -->
        <?php if ($methodCode === 'cod'): ?>
          <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.92rem; color: #334155;">
            <p style="margin: 0 0 10px 0;">
              📦 <strong>Cash on Delivery (COD) Instructions:</strong>
            </p>
            <ul style="margin: 0; padding-left: 20px;">
              <li>Please prepare the exact cash amount of <strong><?= vnd($don['tong_tien']) ?></strong> upon delivery.</li>
              <li>Our student courier will deliver textbooks directly to your dormitory or address: <strong><?= e($don['dia_chi']) ?></strong>.</li>
              <li>Payment status will automatically update to <strong>Paid</strong> once delivered.</li>
            </ul>
          </div>

        <!-- METHOD: BANK TRANSFER -->
        <?php elseif ($methodCode === 'bank_transfer'): ?>
          <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.92rem; color: #334155; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
              <div>
                <small style="color: #64748b; font-weight: bold; text-transform: uppercase;">Bank Name / ธนาคาร</small>
                <div style="font-weight: 700; color: #0f172a; font-size: 1rem;"><?= e($caiDatTT['bank_name']) ?></div>
                <small style="color: #64748b;"><?= e($caiDatTT['bank_branch']) ?></small>
              </div>

              <div>
                <small style="color: #64748b; font-weight: bold; text-transform: uppercase;">Account Number / เลขบัญชี</small>
                <div style="font-weight: 800; color: #1e40af; font-size: 1.15rem; letter-spacing: 1px;">
                  <?= e($caiDatTT['bank_account_number']) ?>
                  <button type="button" onclick="navigator.clipboard.writeText('<?= e($caiDatTT['bank_account_number']) ?>');alert('Account number copied!');" style="background:#e0f2fe;border:none;color:#0369a1;padding:2px 8px;border-radius:4px;font-size:0.75rem;cursor:pointer;margin-left:6px;">Copy</button>
                </div>
              </div>

              <div>
                <small style="color: #64748b; font-weight: bold; text-transform: uppercase;">Account Holder / ชื่อบัญชี</small>
                <div style="font-weight: 700; color: #0f172a;"><?= e($caiDatTT['bank_account_name']) ?></div>
              </div>

              <div>
                <small style="color: #64748b; font-weight: bold; text-transform: uppercase;">Transfer Memo / บันทึกช่วยจำ</small>
                <div style="font-weight: 800; color: #b91c1c; font-size: 1.05rem;">
                  BN<?= $orderId ?>
                  <button type="button" onclick="navigator.clipboard.writeText('BN<?= $orderId ?>');alert('Transfer memo copied!');" style="background:#fee2e2;border:none;color:#991b1b;padding:2px 8px;border-radius:4px;font-size:0.75rem;cursor:pointer;margin-left:6px;">Copy</button>
                </div>
                <small style="color: #dc2626;">* Please include exactly this memo</small>
              </div>
            </div>
          </div>

        <!-- METHOD: QR CODE PAYMENT -->
        <?php elseif ($methodCode === 'qr_code'): ?>
          <div style="text-align: center; background: #f8fafc; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
            <p style="margin: 0 0 15px 0; color: #334155; font-size: 0.95rem;">
              Scan this QR Code using any Banking app or PromptPay/VietQR scanner:
            </p>
            
            <div style="display: inline-block; background: #ffffff; padding: 15px; border-radius: 12px; border: 2px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
              <?php if (!empty($caiDatTT['qr_code_image']) && file_exists(__DIR__ . '/' . $caiDatTT['qr_code_image'])): ?>
                <img src="<?= e($caiDatTT['qr_code_image']) ?>" alt="Payment QR Code" style="max-width: 240px; height: auto; border-radius: 6px; display: block;">
              <?php else: ?>
                <!-- Default Dynamic SVG QR Code -->
                <svg width="200" height="200" viewBox="0 0 200 200" style="display: block;">
                  <rect width="200" height="200" fill="#ffffff" />
                  <rect x="20" y="20" width="45" height="45" fill="#1e3b6d" rx="4" />
                  <rect x="27" y="27" width="31" height="31" fill="#ffffff" rx="2" />
                  <rect x="34" y="34" width="17" height="17" fill="#1e3b6d" rx="2" />
                  <rect x="135" y="20" width="45" height="45" fill="#1e3b6d" rx="4" />
                  <rect x="142" y="27" width="31" height="31" fill="#ffffff" rx="2" />
                  <rect x="149" y="34" width="17" height="17" fill="#1e3b6d" rx="2" />
                  <rect x="20" y="135" width="45" height="45" fill="#1e3b6d" rx="4" />
                  <rect x="27" y="142" width="31" height="31" fill="#ffffff" rx="2" />
                  <rect x="34" y="149" width="17" height="17" fill="#1e3b6d" rx="2" />
                  <rect x="75" y="25" width="10" height="10" fill="#0f172a" />
                  <rect x="95" y="25" width="10" height="10" fill="#0f172a" />
                  <rect x="115" y="25" width="10" height="10" fill="#0f172a" />
                  <rect x="75" y="45" width="20" height="10" fill="#0f172a" />
                  <rect x="105" y="45" width="10" height="10" fill="#0f172a" />
                  <rect x="25" y="75" width="10" height="20" fill="#0f172a" />
                  <rect x="45" y="75" width="20" height="10" fill="#0f172a" />
                  <rect x="75" y="75" width="50" height="50" fill="#1e3b6d" rx="8" />
                  <text x="100" y="105" font-size="20" text-anchor="middle" fill="#ffffff">💳</text>
                  <rect x="135" y="75" width="20" height="10" fill="#0f172a" />
                  <rect x="165" y="75" width="15" height="20" fill="#0f172a" />
                  <rect x="25" y="105" width="20" height="10" fill="#0f172a" />
                  <rect x="55" y="105" width="10" height="20" fill="#0f172a" />
                  <rect x="135" y="95" width="10" height="20" fill="#0f172a" />
                  <rect x="155" y="105" width="25" height="10" fill="#0f172a" />
                  <rect x="75" y="135" width="15" height="10" fill="#0f172a" />
                  <rect x="100" y="135" width="20" height="10" fill="#0f172a" />
                  <rect x="135" y="135" width="10" height="25" fill="#0f172a" />
                  <rect x="155" y="135" width="25" height="10" fill="#0f172a" />
                  <rect x="75" y="155" width="30" height="10" fill="#0f172a" />
                  <rect x="115" y="155" width="10" height="25" fill="#0f172a" />
                  <rect x="135" y="170" width="25" height="10" fill="#0f172a" />
                  <rect x="170" y="155" width="10" height="25" fill="#0f172a" />
                </svg>
              <?php endif; ?>
            </div>

            <div style="margin-top: 15px; font-size: 0.9rem; color: #475569;">
              PromptPay / Ref: <strong><?= e($caiDatTT['promptpay_number']) ?></strong> &bull; Amount: <strong style="color: #dc2626; font-size: 1.1rem;"><?= vnd($don['tong_tien']) ?></strong> &bull; Memo: <strong>BN<?= $orderId ?></strong>
            </div>
          </div>

        <!-- METHOD: E-WALLET (MOMO / TRUEMONEY) -->
        <?php elseif ($methodCode === 'e_wallet'): ?>
          <div style="background: #fff1f2; padding: 20px; border-radius: 8px; border: 1px solid #fecdd3; margin-bottom: 20px;">
            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
              <span style="font-size: 2.2rem;">👛</span>
              <div>
                <strong style="color: #9f1239; font-size: 1.05rem;"><?= e($caiDatTT['wallet_provider']) ?></strong>
                <div style="font-size: 0.85rem; color: #be123c;">Pay directly using your student e-wallet application or instant sandbox simulator.</div>
              </div>
            </div>

            <div style="background: #ffffff; padding: 15px; border-radius: 6px; border: 1px solid #fecdd3; margin-bottom: 15px; font-size: 0.9rem;">
              <div><strong>Wallet Provider:</strong> <?= e($caiDatTT['wallet_provider']) ?></div>
              <div><strong>Receiver:</strong> <?= e($caiDatTT['wallet_receiver']) ?>
                <button type="button" onclick="navigator.clipboard.writeText('<?= e($caiDatTT['wallet_receiver']) ?>');alert('Receiver info copied!');" style="background:#fee2e2;border:none;color:#991b1b;padding:2px 8px;border-radius:4px;font-size:0.75rem;cursor:pointer;margin-left:6px;">Copy</button>
              </div>
              <div><strong>Amount:</strong> <strong style="color: #dc2626;"><?= vnd($don['tong_tien']) ?></strong></div>
              <div><strong>Message:</strong> BN<?= $orderId ?></div>
            </div>

            <?php if ($pay['payment_status'] !== 'paid'): ?>
              <!-- Instant Gateway Simulator Button -->
              <form method="POST" action="thanh-toan.php" style="margin: 0;">
                <?= csrfField() ?>
                <input type="hidden" name="action_simulate_gateway" value="1">
                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                <input type="hidden" name="payment_id" value="<?= $pay['payment_id'] ?>">
                <button type="submit" class="nut" style="width: 100%; background: #be185d; color: #ffffff; padding: 12px; font-size: 1rem; font-weight: 700; border: none; border-radius: 6px; cursor: pointer;">
                  ⚡ Simulate Instant E-Wallet Gateway Payment (<?= vnd($don['tong_tien']) ?>)
                </button>
                <div style="font-size: 0.78rem; color: #9f1239; text-align: center; margin-top: 6px;">
                  * Simulates payment gateway callback with token verification and automated order settlement.
                </div>
              </form>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- RECEIPT UPLOAD FORM (for Bank Transfer, QR Code, or re-upload) -->
        <?php if ($pay['payment_status'] !== 'paid'): ?>
          <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px;">
            <h3 style="margin: 0 0 8px 0; color: #1e3b6d; font-size: 1.1rem;">
              📤 Upload Payment Receipt / Proof of Transfer
            </h3>
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 15px;">
              After transferring, please upload a screenshot or photo of your payment receipt (JPG, PNG, WebP, or PDF, max 5MB).
            </p>

            <form method="POST" action="thanh-toan.php" enctype="multipart/form-data">
              <?= csrfField() ?>
              <input type="hidden" name="action_upload_proof" value="1">
              <input type="hidden" name="order_id" value="<?= $orderId ?>">
              <input type="hidden" name="payment_id" value="<?= $pay['payment_id'] ?>">

              <div style="margin-bottom: 15px;">
                <label for="payment_proof" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.9rem;">
                  Select Receipt Image / Document:
                </label>
                <input type="file" id="payment_proof" name="payment_proof" accept="image/jpeg,image/png,image/webp,application/pdf" required style="width: 100%; padding: 10px; border: 1.5px dashed #cbd5e1; border-radius: 6px; background: #f8fafc; font-size: 0.9rem;">
              </div>

              <button type="submit" class="nut nut--chinh" style="padding: 10px 24px; font-size: 0.95rem;">
                ✓ Submit Receipt for Verification
              </button>
            </form>
          </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Right Column: Order Summary & Item Breakdown -->
    <div style="flex: 2; min-width: 260px;">
      <div style="background: #f8fafc; padding: 22px; border-radius: 10px; border: 1px solid #e2e8f0;">
        <h3 style="margin-top: 0; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px;">
          Order Summary (<?= count($items) ?> <?= count($items) > 1 ? 'items' : 'item' ?>)
        </h3>

        <ul style="list-style: none; padding: 0; margin: 0 0 15px 0;">
          <?php foreach ($items as $it): ?>
            <li style="display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px dashed #e2e8f0; font-size: 0.88rem;">
              <img src="<?= e(anhSach($it['anh'])) ?>" alt="Cover" width="40" height="50" style="border-radius: 4px; object-fit: cover;">
              <div style="flex: 1;">
                <div style="font-weight: 600; color: #1e3b6d;"><?= e($it['ten']) ?></div>
                <div style="color: #64748b; font-size: 0.8rem;">Qty: <?= $it['so_luong'] ?> x <?= vnd($it['don_gia']) ?></div>
              </div>
              <div style="font-weight: 700; color: #dc2626;">
                <?= vnd($it['don_gia'] * $it['so_luong']) ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>

        <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 800; color: #0f172a; padding-top: 8px;">
          <span>Total Payment:</span>
          <span style="color: #dc2626;"><?= vnd($don['tong_tien']) ?></span>
        </div>

        <div style="margin-top: 20px; padding: 12px; background: #ffffff; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.82rem; color: #475569;">
          <div><strong>Recipient:</strong> <?= e($don['ho_ten']) ?></div>
          <div><strong>Phone:</strong> <?= e($don['so_dien_thoai']) ?></div>
          <div><strong>Delivery:</strong> <?= e($don['dia_chi']) ?></div>
        </div>

        <?php if ($pay['payment_status'] === 'paid'): ?>
          <div style="margin-top: 15px;">
            <a href="thanh-toan.php?action=receipt&order_id=<?= $orderId ?>" class="nut nut--chinh" style="width: 100%; text-align: center; display: block; box-sizing: border-box; padding: 10px 0;">
              🧾 View Digital Receipt
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
