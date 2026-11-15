<?php
/**
 * inc/thanh-toan.php - Payment System Engine & Helpers
 * BookNest - UED Student Online Bookstore
 * Module 10: Complete and Secure Payment System
 */

require_once __DIR__ . '/config.php';

/**
 * Get list of available payment methods
 */
function layDanhSachPhuongThuc(bool $chiHoatDong = true): array
{
    $db = db();
    $sql = 'SELECT * FROM payment_methods';
    if ($chiHoatDong) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, id ASC';
    return $db->query($sql)->fetchAll();
}

/**
 * Get single payment method by code
 */
function layPhuongThucTheoCode(string $code): ?array
{
    $db = db();
    $st = $db->prepare('SELECT * FROM payment_methods WHERE code = ? LIMIT 1');
    $st->execute([$code]);
    $pt = $st->fetch();
    return $pt ?: null;
}

/**
 * Standardize payment method code
 */
function chuanHoaMaPhuongThuc(string $rawMethod): string
{
    $raw = strtolower(trim($rawMethod));
    if (str_contains($raw, 'bank') || str_contains($raw, 'chuyen_khoan')) {
        return 'bank_transfer';
    }
    if (str_contains($raw, 'qr') || str_contains($raw, 'vietqr') || str_contains($raw, 'promptpay')) {
        return 'qr_code';
    }
    if (str_contains($raw, 'momo') || str_contains($raw, 'wallet') || str_contains($raw, 'vi')) {
        return 'e_wallet';
    }
    return 'cod';
}

/**
 * Generate human-friendly label for payment method
 */
function nhanPhuongThucTT(string $code): string
{
    $code = chuanHoaMaPhuongThuc($code);
    return match ($code) {
        'bank_transfer' => '🏦 Bank Transfer / VietQR',
        'qr_code'       => '📱 QR Code Payment',
        'e_wallet'      => '👛 E-Wallet (MoMo)',
        default         => '💵 Cash on Delivery (COD)',
    };
}

/**
 * Generate HTML status badge for payment status
 */
function huyHieuTrangThaiTT(string $status): string
{
    return match (strtolower($status)) {
        'paid' => '<span style="display:inline-flex;align-items:center;gap:4px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;padding:3px 9px;border-radius:9999px;font-size:0.82rem;font-weight:700;">✓ Paid</span>',
        'verifying' => '<span style="display:inline-flex;align-items:center;gap:4px;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;padding:3px 9px;border-radius:9999px;font-size:0.82rem;font-weight:700;">🔍 Verifying</span>',
        'rejected' => '<span style="display:inline-flex;align-items:center;gap:4px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:3px 9px;border-radius:9999px;font-size:0.82rem;font-weight:700;">✗ Rejected</span>',
        'refunded' => '<span style="display:inline-flex;align-items:center;gap:4px;background:#f5f3ff;color:#5b21b6;border:1px solid #ddd6fe;padding:3px 9px;border-radius:9999px;font-size:0.82rem;font-weight:700;">↩ Refunded</span>',
        'failed' => '<span style="display:inline-flex;align-items:center;gap:4px;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;padding:3px 9px;border-radius:9999px;font-size:0.82rem;font-weight:700;">⚠ Failed</span>',
        default => '<span style="display:inline-flex;align-items:center;gap:4px;background:#fffbeb;color:#92400e;border:1px solid #fde68a;padding:3px 9px;border-radius:9999px;font-size:0.82rem;font-weight:700;">⏳ Pending</span>',
    };
}

/**
 * Create or ensure payment record for an order
 */
function taoHoacLayThanhToan(int $orderId, int $userId, string $method, int $amount): array
{
    $db = db();
    $st = $db->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY payment_id DESC LIMIT 1');
    $st->execute([$orderId]);
    $existing = $st->fetch();

    if ($existing) {
        return $existing;
    }

    $cleanMethod = chuanHoaMaPhuongThuc($method);
    $txnId = 'TXN-BN-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

    $stIns = $db->prepare('INSERT INTO payments (order_id, user_id, payment_method, transaction_id, amount, payment_status, created_at) VALUES (?, ?, ?, ?, ?, "pending", NOW())');
    $stIns->execute([$orderId, $userId, $cleanMethod, $txnId, $amount]);

    $paymentId = (int)$db->lastInsertId();
    $stFetch = $db->prepare('SELECT * FROM payments WHERE payment_id = ?');
    $stFetch->execute([$paymentId]);
    return $stFetch->fetch();
}

/**
 * Handle secure receipt file upload
 */
function xuLyTaiBienLai(int $paymentId, int $userId, array $file): array
{
    $db = db();

    // Check payment record and ownership
    $st = $db->prepare('SELECT p.*, dh.nguoi_dung_id FROM payments p JOIN don_hang dh ON p.order_id = dh.id WHERE p.payment_id = ?');
    $st->execute([$paymentId]);
    $pay = $st->fetch();

    if (!$pay) {
        return ['success' => false, 'error' => 'Payment record not found.'];
    }

    if ((int)$pay['nguoi_dung_id'] !== $userId) {
        return ['success' => false, 'error' => 'Permission denied. You do not own this order.'];
    }

    if ($pay['payment_status'] === 'paid') {
        return ['success' => false, 'error' => 'This payment is already confirmed as Paid.'];
    }

    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file upload parameters.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error (code: ' . $file['error'] . '). Please try again.'];
    }

    // Size limit: 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'Receipt file size exceeds the 5MB limit.'];
    }

    // MIME type check using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    if (!isset($allowedMimes[$mime])) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, WebP, and PDF files are allowed.'];
    }

    $ext = $allowedMimes[$mime];
    $uploadDir = dirname(__DIR__) . '/uploads/receipts';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Secure random file name
    $newFileName = 'proof_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Failed to save uploaded receipt. Please check folder permissions.'];
    }

    // Update payment record to 'verifying'
    $stUpdate = $db->prepare('UPDATE payments SET payment_proof = ?, payment_status = "verifying", admin_note = NULL WHERE payment_id = ?');
    $stUpdate->execute([$newFileName, $paymentId]);

    return [
        'success'  => true,
        'filename' => $newFileName,
        'message'  => 'Payment receipt uploaded successfully! Our team is verifying your payment.',
    ];
}

/**
 * Approve payment (Admin or authorized Seller)
 */
function duyetThanhToan(int $paymentId, int $verifierId, ?string $note = null): bool
{
    $db = db();
    $db->beginTransaction();

    try {
        $st = $db->prepare('SELECT * FROM payments WHERE payment_id = ? FOR UPDATE');
        $st->execute([$paymentId]);
        $pay = $st->fetch();

        if (!$pay) {
            throw new RuntimeException('Payment record not found.');
        }

        if ($pay['payment_status'] === 'paid') {
            $db->commit();
            return true;
        }

        // Update payment status
        $stUp = $db->prepare('UPDATE payments SET payment_status = "paid", payment_date = NOW(), verified_by = ?, admin_note = ? WHERE payment_id = ?');
        $stUp->execute([$verifierId, $note ?: 'Payment approved and verified.', $paymentId]);

        // Update order payment status
        $stOrder = $db->prepare('UPDATE don_hang SET trang_thai_tt = "da_thanh_toan" WHERE id = ?');
        $stOrder->execute([(int)$pay['order_id']]);

        $db->commit();
        return true;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Reject payment receipt with reason
 */
function tuChoiThanhToan(int $paymentId, int $verifierId, string $reason): bool
{
    $db = db();
    $db->beginTransaction();

    try {
        $st = $db->prepare('SELECT * FROM payments WHERE payment_id = ? FOR UPDATE');
        $st->execute([$paymentId]);
        $pay = $st->fetch();

        if (!$pay) {
            throw new RuntimeException('Payment record not found.');
        }

        $stUp = $db->prepare('UPDATE payments SET payment_status = "rejected", verified_by = ?, admin_note = ? WHERE payment_id = ?');
        $stUp->execute([$verifierId, $reason, $paymentId]);

        // Ensure order remains unpaid
        $db->prepare('UPDATE don_hang SET trang_thai_tt = "chua_thanh_toan" WHERE id = ?')->execute([(int)$pay['order_id']]);

        $db->commit();
        return true;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Process Refund
 */
function hoanTienThanhToan(int $paymentId, int $adminId, string $reason): bool
{
    $db = db();
    $db->beginTransaction();

    try {
        $st = $db->prepare('SELECT * FROM payments WHERE payment_id = ? FOR UPDATE');
        $st->execute([$paymentId]);
        $pay = $st->fetch();

        if (!$pay) {
            throw new RuntimeException('Payment record not found.');
        }

        $stUp = $db->prepare('UPDATE payments SET payment_status = "refunded", verified_by = ?, admin_note = ? WHERE payment_id = ?');
        $stUp->execute([$adminId, $reason, $paymentId]);

        // Update order status to refunded
        $db->prepare('UPDATE don_hang SET trang_thai_tt = "hoan_tien" WHERE id = ?')->execute([(int)$pay['order_id']]);

        $db->commit();
        return true;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Simulate trusted gateway / E-wallet instant payment
 */
function moPhongThanhToanCongTrucTuyen(int $paymentId, int $userId): array
{
    $db = db();
    $st = $db->prepare('SELECT p.*, dh.nguoi_dung_id, dh.tong_tien FROM payments p JOIN don_hang dh ON p.order_id = dh.id WHERE p.payment_id = ?');
    $st->execute([$paymentId]);
    $pay = $st->fetch();

    if (!$pay) {
        return ['success' => false, 'error' => 'Payment record not found.'];
    }

    if ((int)$pay['nguoi_dung_id'] !== $userId) {
        return ['success' => false, 'error' => 'Permission denied.'];
    }

    if ($pay['payment_status'] === 'paid') {
        return ['success' => true, 'message' => 'This transaction was already successfully paid.'];
    }

    // Backend verification simulation: generate payment provider gateway reference
    $gatewayRef = 'MOMO-GW-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    $db->beginTransaction();
    try {
        $stUp = $db->prepare('UPDATE payments SET payment_status = "paid", transaction_id = ?, payment_date = NOW(), admin_note = "Instant simulated E-Wallet gateway payment verified." WHERE payment_id = ?');
        $stUp->execute([$gatewayRef, $paymentId]);

        $db->prepare('UPDATE don_hang SET trang_thai_tt = "da_thanh_toan" WHERE id = ?')->execute([(int)$pay['order_id']]);

        $db->commit();
        return ['success' => true, 'message' => 'E-Wallet payment simulated and verified successfully!', 'txn' => $gatewayRef];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'error' => 'Gateway error: ' . $e->getMessage()];
    }
}

/**
 * Detect suspicious transactions
 */
function kiemTraGiaoDichNghiVan(): array
{
    $db = db();
    $danhSach = [];

    // 1. Duplicate transaction IDs
    $dupTxn = $db->query('SELECT transaction_id, COUNT(*) as cnt, GROUP_CONCAT(payment_id) as pids FROM payments WHERE transaction_id IS NOT NULL AND transaction_id != "" GROUP BY transaction_id HAVING cnt > 1')->fetchAll();
    foreach ($dupTxn as $d) {
        $danhSach[] = [
            'type'        => 'duplicate_txn',
            'severity'    => 'high',
            'title'       => 'Duplicate Transaction ID',
            'detail'      => "Transaction ID \"{$d['transaction_id']}\" appears in {$d['cnt']} payments (IDs: {$d['pids']}).",
            'payment_ids' => explode(',', $d['pids']),
        ];
    }

    // 2. Mismatched payment amount vs order total
    $mismatch = $db->query('SELECT p.payment_id, p.amount as p_amount, dh.tong_tien as o_amount, p.order_id FROM payments p JOIN don_hang dh ON p.order_id = dh.id WHERE p.amount != dh.tong_tien')->fetchAll();
    foreach ($mismatch as $m) {
        $danhSach[] = [
            'type'        => 'amount_mismatch',
            'severity'    => 'high',
            'title'       => 'Amount Discrepancy',
            'detail'      => "Payment #{$m['payment_id']} amount (" . vnd($m['p_amount']) . ") does not match Order #{$m['order_id']} total (" . vnd($m['o_amount']) . ").",
            'payment_ids' => [$m['payment_id']],
        ];
    }

    // 3. Stale pending payments older than 48 hours without receipt
    $stale = $db->query('SELECT payment_id, order_id, created_at, amount FROM payments WHERE payment_status = "pending" AND payment_proof IS NULL AND created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR) LIMIT 10')->fetchAll();
    foreach ($stale as $s) {
        $danhSach[] = [
            'type'        => 'stale_pending',
            'severity'    => 'medium',
            'title'       => 'Long-Pending Unpaid Order (>48h)',
            'detail'      => "Payment #{$s['payment_id']} (Order #{$s['order_id']}, " . vnd($s['amount']) . ") has been pending since {$s['created_at']}.",
            'payment_ids' => [$s['payment_id']],
        ];
    }

    return $danhSach;
}
