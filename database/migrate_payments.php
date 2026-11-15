<?php
/**
 * database/migrate_payments.php
 * Migration script for Module 10: Payment System
 */

require_once __DIR__ . '/../inc/config.php';

$db = db();

echo "Running Payment System Migration...\n";

// Fix any orphaned user_ids in don_hang
$defaultUserId = (int)$db->query("SELECT id FROM nguoi_dung ORDER BY id ASC LIMIT 1")->fetchColumn();
$db->exec("UPDATE don_hang SET nguoi_dung_id = $defaultUserId WHERE nguoi_dung_id NOT IN (SELECT id FROM nguoi_dung)");

// 1. Drop old view if exists
$db->exec('DROP VIEW IF EXISTS payments');

// 2. Create payment_methods table
$db->exec("CREATE TABLE IF NOT EXISTS payment_methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  instructions TEXT,
  icon VARCHAR(50) DEFAULT '💳',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Seed default payment methods
$db->exec("INSERT INTO payment_methods (code, name, description, instructions, icon, is_active, sort_order) VALUES
  ('cod', 'Cash on Delivery (COD)', 'Pay with cash upon textbook delivery at UED campus or dormitory', 'Please prepare the exact cash amount when receiving textbooks from our courier.', '💵', 1, 1),
  ('bank_transfer', 'Bank Transfer', 'Transfer directly to BookNest official student account via mobile banking', 'Bank: Vietcombank - Danang Branch\nAccount No: 02363841323\nAccount Name: BOOKNEST STORE UED\nTransfer memo: BN{ORDER_ID}', '🏦', 1, 2),
  ('qr_code', 'QR Code Payment', 'Scan dynamic QR code (PromptPay / VietQR standard) for instant settlement', 'Scan the QR code displayed on the checkout or order screen using any banking or payment app.', '📱', 1, 3),
  ('e_wallet', 'E-Wallet (MoMo / TrueMoney)', 'Fast and secure checkout via MoMo or digital student e-wallets', 'Scan MoMo QR code or transfer to 0905123456 (BookNest Admin).', '👛', 1, 4)
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), instructions=VALUES(instructions), icon=VALUES(icon)");

// 3. Create payments table
$db->exec("CREATE TABLE IF NOT EXISTS payments (
  payment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  transaction_id VARCHAR(100) NULL,
  amount INT UNSIGNED NOT NULL,
  payment_status ENUM('pending', 'paid', 'failed', 'verifying', 'rejected', 'refunded') NOT NULL DEFAULT 'pending',
  payment_proof VARCHAR(255) NULL,
  payment_date DATETIME NULL,
  admin_note TEXT NULL,
  verified_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order (order_id),
  INDEX idx_user (user_id),
  INDEX idx_status (payment_status),
  INDEX idx_txn (transaction_id),
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES don_hang(id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 4. Migrate existing orders if payments is empty
$count = (int)$db->query("SELECT COUNT(*) FROM payments")->fetchColumn();
if ($count === 0) {
    $orders = $db->query("SELECT * FROM don_hang")->fetchAll();
    $stIns = $db->prepare("INSERT INTO payments (order_id, user_id, payment_method, transaction_id, amount, payment_status, payment_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($orders as $o) {
        $method = strtolower($o['phuong_thuc_tt'] ?? 'cod');
        if (str_contains($method, 'bank')) {
            $method = 'bank_transfer';
        } elseif (str_contains($method, 'momo') || str_contains($method, 'wallet')) {
            $method = 'e_wallet';
        } elseif (str_contains($method, 'qr')) {
            $method = 'qr_code';
        } else {
            $method = 'cod';
        }

        $status = ($o['trang_thai_tt'] === 'da_thanh_toan') ? 'paid' : 'pending';
        $pDate = ($status === 'paid') ? $o['ngay_tao'] : null;
        $txn = 'TXN-BN-' . date('Ymd', strtotime($o['ngay_tao'])) . '-' . strtoupper(substr(md5((string)$o['id']), 0, 6));
        $stIns->execute([$o['id'], $o['nguoi_dung_id'], $method, $txn, $o['tong_tien'], $status, $pDate, $o['ngay_tao']]);
    }
    echo "Migrated " . count($orders) . " existing orders into payments table.\n";
} else {
    echo "Payments table already has $count records.\n";
}

echo "Payment System Migration Complete!\n";
