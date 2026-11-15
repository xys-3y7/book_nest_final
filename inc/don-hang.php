<?php
/**
 * inc/don-hang.php - Order processing within ACID database transactions
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

/**
 * Save order and order items in a secure database transaction.
 * Updates stock with condition ton_kho >= ? (prevents race condition overselling)
 *
 * @param int $nguoiDungId User ID placing the order
 * @param array $gio Shopping cart array [san_pham_id => so_luong]
 * @param string $diaChi Shipping address
 * @param string $soDienThoai Contact phone number
 * @param string|null $ghiChu Additional notes
 * @param string $phuongThucTT Payment method (COD, Bank Transfer, MoMo)
 * @return int Newly created order ID
 */
function luuDonHang(int $nguoiDungId, array $gio, string $diaChi, string $soDienThoai, ?string $ghiChu = null, string $phuongThucTT = 'COD'): int
{
    if (empty($gio)) {
        throw new RuntimeException('Your shopping cart is empty, cannot place an order.');
    }

    $db = db();
    $db->beginTransaction();

    try {
        // 1. Fetch current info and prices of all books in cart in a single WHERE IN query
        $ids = array_map('intval', array_keys($gio));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $st = $db->prepare("SELECT id, ten, gia, ton_kho FROM san_pham WHERE id IN ($placeholders)");
        $st->execute($ids);
        $sanPhams = $st->fetchAll();

        $bangGia = [];
        foreach ($sanPhams as $sp) {
            $bangGia[$sp['id']] = $sp;
        }

        // 2. Validate and decrement stock atomically with condition ton_kho >= so_luong
        $tongTien = 0;
        $stUpdateTonKho = $db->prepare('UPDATE san_pham SET ton_kho = ton_kho - ? WHERE id = ? AND ton_kho >= ?');

        foreach ($gio as $spId => $sl) {
            $sl = (int)$sl;
            if ($sl <= 0) {
                continue;
            }
            if (!isset($bangGia[$spId])) {
                throw new RuntimeException("Textbook #{$spId} does not exist in the catalog.");
            }

            $sp = $bangGia[$spId];

            // Atomic update: if ton_kho < sl then rowCount() is 0
            $stUpdateTonKho->execute([$sl, $spId, $sl]);
            if ($stUpdateTonKho->rowCount() === 0) {
                throw new RuntimeException("Sorry! Textbook \"{$sp['ten']}\" does not have enough stock available (only {$sp['ton_kho']} left).");
            }

            $tongTien += $sp['gia'] * $sl;
        }

        // 3. Insert order record (don_hang)
        $trangThaiTT = 'chua_thanh_toan';
        $stDonHang = $db->prepare('INSERT INTO don_hang (nguoi_dung_id, tong_tien, dia_chi, so_dien_thoai, ghi_chu, phuong_thuc_tt, trang_thai_tt) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stDonHang->execute([$nguoiDungId, $tongTien, $diaChi, $soDienThoai, $ghiChu, $phuongThucTT, $trangThaiTT]);
        $donHangId = (int)$db->lastInsertId();

        // 4. Insert order details (chi_tiet_don) with locked unit price
        $stChiTiet = $db->prepare('INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia) VALUES (?, ?, ?, ?)');
        foreach ($gio as $spId => $sl) {
            $sl = (int)$sl;
            if ($sl <= 0) continue;
            $donGia = $bangGia[$spId]['gia'];
            $stChiTiet->execute([$donHangId, $spId, $sl, $donGia]);
        }

        // 5. Record payment in payments table
        $maGiaoDich = 'TXN-BN-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $stPayment = $db->prepare('INSERT INTO payments (order_id, user_id, payment_method, transaction_id, amount, payment_status, created_at) VALUES (?, ?, ?, ?, ?, "pending", NOW())');
        $stPayment->execute([
            $donHangId,
            $nguoiDungId,
            $phuongThucTT,
            $maGiaoDich,
            $tongTien
        ]);

        // Keep legacy thanh_toan in sync if table exists
        try {
            $db->prepare('INSERT INTO thanh_toan (don_hang_id, phuong_thuc, so_tien, trang_thai, ma_giao_dich) VALUES (?, ?, ?, "dang_cho", ?)')
               ->execute([$donHangId, $phuongThucTT, $tongTien, $maGiaoDich]);
        } catch (Throwable) {
            // Ignore legacy table errors
        }

        // 6. Commit transaction
        $db->commit();
        return $donHangId;

    } catch (Throwable $e) {
        // Rollback on any failure
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Cancel an order and restore stock quantities for each book item
 */
function huyDonHang(int $donHangId, int $nguoiDungId, bool $laAdmin = false): bool
{
    $db = db();
    $db->beginTransaction();

    try {
        // Check order existence and permission
        $st = $db->prepare('SELECT * FROM don_hang WHERE id = ? FOR UPDATE');
        $st->execute([$donHangId]);
        $don = $st->fetch();

        if (!$don) {
            throw new RuntimeException('Order not found.');
        }

        if (!$laAdmin && (int)$don['nguoi_dung_id'] !== $nguoiDungId) {
            throw new RuntimeException('You do not have permission to cancel this order.');
        }

        if ($don['trang_thai'] === 'da_huy') {
            throw new RuntimeException('This order is already cancelled.');
        }

        if (!$laAdmin && $don['trang_thai'] !== 'cho_xu_ly') {
            throw new RuntimeException('Only orders with status "Pending" can be cancelled.');
        }

        // Restore stock
        $stItems = $db->prepare('SELECT san_pham_id, so_luong FROM chi_tiet_don WHERE don_hang_id = ?');
        $stItems->execute([$donHangId]);
        $items = $stItems->fetchAll();

        $stRestore = $db->prepare('UPDATE san_pham SET ton_kho = ton_kho + ? WHERE id = ?');
        foreach ($items as $item) {
            $stRestore->execute([(int)$item['so_luong'], (int)$item['san_pham_id']]);
        }

        // Update status to da_huy
        $stUpdate = $db->prepare('UPDATE don_hang SET trang_thai = "da_huy" WHERE id = ?');
        $stUpdate->execute([$donHangId]);

        // Update payment status if exists
        $db->prepare('UPDATE payments SET payment_status = "refunded", admin_note = "Order cancelled" WHERE order_id = ?')->execute([$donHangId]);
        try {
            $db->prepare('UPDATE thanh_toan SET trang_thai = "refunded" WHERE don_hang_id = ?')->execute([$donHangId]);
        } catch (Throwable) {}

        $db->commit();
        return true;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
