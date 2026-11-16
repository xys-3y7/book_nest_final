<?php
/**
 * tests/test_payment_system.php
 * Automated Test Suite for Module 10: Payment System (BookNest)
 */

require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/don-hang.php';
require_once __DIR__ . '/../inc/thanh-toan.php';

$db = db();
$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition, ?string $detail = null): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] $description\n";
    } else {
        $failed++;
        echo "  [FAIL] $description" . ($detail ? " ($detail)" : "") . "\n";
    }
}

echo "====================================================================\n";
echo "BookNest Module 10: Payment System Test Suite\n";
echo "====================================================================\n\n";

// -----------------------------------------------------------------
// TEST 1: Database Table Schema Verification
// -----------------------------------------------------------------
echo "[1] Testing Database Table Schema for 'payments':\n";

$cols = $db->query("DESCRIBE payments")->fetchAll(PDO::FETCH_COLUMN);
$requiredCols = [
    'payment_id',
    'order_id',
    'user_id',
    'payment_method',
    'transaction_id',
    'amount',
    'payment_status',
    'payment_proof',
    'payment_date',
    'created_at'
];

foreach ($requiredCols as $req) {
    assertTest("Column '$req' exists in payments table", in_array($req, $cols, true));
}

// Check ENUM statuses
$stStatus = $db->query("SHOW COLUMNS FROM payments LIKE 'payment_status'")->fetch();
$enumStr = $stStatus['Type'] ?? '';
$requiredStatuses = ['pending', 'paid', 'failed', 'verifying', 'rejected', 'refunded'];
$allStatusesPresent = true;
foreach ($requiredStatuses as $st) {
    if (!str_contains($enumStr, "'$st'")) {
        $allStatusesPresent = false;
        break;
    }
}
assertTest("Payment status supports all 6 required states: pending, paid, failed, verifying, rejected, refunded", $allStatusesPresent, "Type: $enumStr");

// Check payment_methods table
$pmCount = (int)$db->query("SELECT COUNT(*) FROM payment_methods WHERE is_active = 1")->fetchColumn();
assertTest("Active payment methods exist in database", $pmCount >= 4, "Found: $pmCount methods");

// -----------------------------------------------------------------
// TEST 2: Payment Creation & Methods Integration
// -----------------------------------------------------------------
echo "\n[2] Testing Payment Creation for Customer Checkout:\n";

// Get sample users: Admin, Customer, Seller
$admin = $db->query("SELECT id FROM nguoi_dung WHERE vai_tro = 'quan_tri' LIMIT 1")->fetch();
$seller = $db->query("SELECT id FROM nguoi_dung WHERE vai_tro = 'chu_sach' LIMIT 1")->fetch();
$customer = $db->query("SELECT id FROM nguoi_dung WHERE vai_tro = 'khach_hang' LIMIT 1")->fetch();

if (!$customer) {
    $customer = $db->query("SELECT id FROM nguoi_dung WHERE id != {$admin['id']} LIMIT 1")->fetch();
}

$sampleBook = $db->query("SELECT id, gia, ton_kho FROM san_pham WHERE ton_kho > 5 LIMIT 1")->fetch();

assertTest("Test prerequisites: Admin, Seller, and Book available", ($admin && $seller && $sampleBook));

$testOrderId = null;
$testPaymentId = null;

if ($customer && $sampleBook) {
    // Test creating an order with Bank Transfer
    $gio = [$sampleBook['id'] => 1];
    $testOrderId = luuDonHang(
        (int)$customer['id'],
        $gio,
        'Dormitory Block A, UED Campus, Danang',
        '0905123456',
        'Test automated payment order',
        'bank_transfer'
    );

    assertTest("Order #$testOrderId placed with bank_transfer method", $testOrderId > 0);

    // Verify payment record was created
    $stPay = $db->prepare("SELECT * FROM payments WHERE order_id = ?");
    $stPay->execute([$testOrderId]);
    $payRecord = $stPay->fetch();

    assertTest("Payment record automatically created for Order #$testOrderId", (bool)$payRecord);
    assertTest("Payment initial status is 'pending'", ($payRecord['payment_status'] ?? '') === 'pending');
    assertTest("Transaction ID is non-empty and formatted correctly", str_starts_with($payRecord['transaction_id'] ?? '', 'TXN-BN-'));
    assertTest("Payment amount matches book unit price", (int)($payRecord['amount'] ?? 0) === (int)$sampleBook['gia']);

    $testPaymentId = (int)($payRecord['payment_id'] ?? 0);
}

// -----------------------------------------------------------------
// TEST 3: Receipt Upload & Verification Workflow
// -----------------------------------------------------------------
echo "\n[3] Testing Receipt Upload and Verification Workflow:\n";

if ($testPaymentId && $customer) {
    // Simulate valid receipt upload
    $dummyProofName = 'proof_test_' . time() . '.png';
    $dummyProofPath = dirname(__DIR__) . '/uploads/receipts/' . $dummyProofName;
    file_put_contents($dummyProofPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

    $db->prepare("UPDATE payments SET payment_proof = ?, payment_status = 'verifying' WHERE payment_id = ?")
       ->execute([$dummyProofName, $testPaymentId]);

    $stCheckVerifying = $db->prepare("SELECT payment_status, payment_proof FROM payments WHERE payment_id = ?");
    $stCheckVerifying->execute([$testPaymentId]);
    $rowV = $stCheckVerifying->fetch();

    assertTest("Payment status transitions to 'verifying' after proof upload", $rowV['payment_status'] === 'verifying');
    assertTest("Payment proof file reference stored correctly", $rowV['payment_proof'] === $dummyProofName);

    // Test rejection with reason
    $rejectionReason = "Receipt amount does not match order total.";
    $rejected = tuChoiThanhToan($testPaymentId, (int)$admin['id'], $rejectionReason);
    assertTest("tuChoiThanhToan() rejects receipt successfully", $rejected);

    $stCheckRej = $db->prepare("SELECT payment_status, admin_note FROM payments WHERE payment_id = ?");
    $stCheckRej->execute([$testPaymentId]);
    $rowR = $stCheckRej->fetch();

    assertTest("Payment status is now 'rejected'", $rowR['payment_status'] === 'rejected');
    assertTest("Rejection reason note recorded properly", $rowR['admin_note'] === $rejectionReason);

    // Test approval
    $approved = duyetThanhToan($testPaymentId, (int)$admin['id'], 'Receipt verified by automated test');
    assertTest("duyetThanhToan() approves payment successfully", $approved);

    $stCheckPaid = $db->prepare("SELECT p.payment_status, p.payment_date, dh.trang_thai_tt FROM payments p JOIN don_hang dh ON p.order_id = dh.id WHERE p.payment_id = ?");
    $stCheckPaid->execute([$testPaymentId]);
    $rowP = $stCheckPaid->fetch();

    assertTest("Payment status is updated to 'paid'", $rowP['payment_status'] === 'paid');
    assertTest("Payment date timestamp is recorded", !empty($rowP['payment_date']));
    assertTest("Order status 'trang_thai_tt' is synchronized to 'da_thanh_toan'", $rowP['trang_thai_tt'] === 'da_thanh_toan');

    // Test Refund processing
    $refundReason = "Customer requested return within 7-day guarantee.";
    $refunded = hoanTienThanhToan($testPaymentId, (int)$admin['id'], $refundReason);
    assertTest("hoanTienThanhToan() refunds transaction successfully", $refunded);

    $stCheckRef = $db->prepare("SELECT p.payment_status, dh.trang_thai_tt FROM payments p JOIN don_hang dh ON p.order_id = dh.id WHERE p.payment_id = ?");
    $stCheckRef->execute([$testPaymentId]);
    $rowRef = $stCheckRef->fetch();

    assertTest("Payment status is updated to 'refunded'", $rowRef['payment_status'] === 'refunded');
    assertTest("Order payment status updated to 'hoan_tien'", $rowRef['trang_thai_tt'] === 'hoan_tien');
}

// -----------------------------------------------------------------
// TEST 4: E-Wallet Instant Gateway Simulation
// -----------------------------------------------------------------
echo "\n[4] Testing E-Wallet Instant Gateway Simulation:\n";

if ($customer && $sampleBook) {
    // Create new order for e-wallet test
    $ewalletOrderId = luuDonHang(
        (int)$customer['id'],
        [$sampleBook['id'] => 1],
        'UED Student Hall 3',
        '0905987654',
        'E-Wallet test order',
        'e_wallet'
    );

    $stPayEW = $db->prepare("SELECT payment_id FROM payments WHERE order_id = ?");
    $stPayEW->execute([$ewalletOrderId]);
    $ewPayId = (int)$stPayEW->fetchColumn();

    $simRes = moPhongThanhToanCongTrucTuyen($ewPayId, (int)$customer['id']);
    assertTest("moPhongThanhToanCongTrucTuyen() returns success", $simRes['success'] === true);

    $stCheckEW = $db->prepare("SELECT p.payment_status, p.transaction_id, dh.trang_thai_tt FROM payments p JOIN don_hang dh ON p.order_id = dh.id WHERE p.payment_id = ?");
    $stCheckEW->execute([$ewPayId]);
    $rowEW = $stCheckEW->fetch();

    assertTest("E-Wallet simulated payment is marked as 'paid'", $rowEW['payment_status'] === 'paid');
    assertTest("E-Wallet gateway reference ID generated", str_starts_with($rowEW['transaction_id'] ?? '', 'MOMO-GW-'));
    assertTest("Order payment status set to 'da_thanh_toan'", $rowEW['trang_thai_tt'] === 'da_thanh_toan');
}

// -----------------------------------------------------------------
// TEST 5: Suspicious Transaction & Fraud Detection
// -----------------------------------------------------------------
echo "\n[5] Testing Suspicious Transaction & Fraud Audit Engine:\n";

$auditResults = kiemTraGiaoDichNghiVan();
assertTest("kiemTraGiaoDichNghiVan() executes without errors and returns array", is_array($auditResults));

// -----------------------------------------------------------------
// TEST 6: Payment Methods Configuration Management
// -----------------------------------------------------------------
echo "\n[6] Testing Payment Methods Management:\n";

$methods = layDanhSachPhuongThuc(false);
assertTest("layDanhSachPhuongThuc(false) retrieves all methods", count($methods) >= 4);

$cod = layPhuongThucTheoCode('cod');
assertTest("layPhuongThucTheoCode('cod') returns COD config", ($cod && $cod['code'] === 'cod'));

$bank = layPhuongThucTheoCode('bank_transfer');
assertTest("layPhuongThucTheoCode('bank_transfer') returns bank details", ($bank && str_contains($bank['instructions'], 'Vietcombank')));

$qr = layPhuongThucTheoCode('qr_code');
assertTest("layPhuongThucTheoCode('qr_code') returns QR config", ($qr && $qr['code'] === 'qr_code'));

$wallet = layPhuongThucTheoCode('e_wallet');
assertTest("layPhuongThucTheoCode('e_wallet') returns E-Wallet config", ($wallet && $wallet['code'] === 'e_wallet'));

// -----------------------------------------------------------------
// TEST 7: Dynamic Bank Account & Payment Gateway Configurations
// -----------------------------------------------------------------
echo "\n[7] Testing Dynamic Bank Account & Gateway Configurations:\n";

$settings = layCaiDatThanhToan();
assertTest("layCaiDatThanhToan() returns bank_name", !empty($settings['bank_name']));
assertTest("layCaiDatThanhToan() returns bank_account_number", !empty($settings['bank_account_number']));
assertTest("layCaiDatThanhToan() returns bank_account_name", !empty($settings['bank_account_name']));
assertTest("layCaiDatThanhToan() returns promptpay_number", !empty($settings['promptpay_number']));
assertTest("layCaiDatThanhToan() returns wallet_provider", !empty($settings['wallet_provider']));

$resSave = luuCaiDatThanhToan([
    'bank_name'           => 'Kasikornbank (KBank)',
    'bank_branch'         => 'Siam Paragon Branch',
    'bank_account_number' => '123-4-56789-0',
    'bank_account_name'   => 'BOOKNEST OFFICIAL',
    'promptpay_number'    => '0812345678'
]);
assertTest("luuCaiDatThanhToan() executes successfully", $resSave['success'] === true);

$updatedSettings = layCaiDatThanhToan();
assertTest("Updated bank_name is saved", $updatedSettings['bank_name'] === 'Kasikornbank (KBank)');
assertTest("Updated bank_account_number is saved", $updatedSettings['bank_account_number'] === '123-4-56789-0');
assertTest("Updated promptpay_number is saved", $updatedSettings['promptpay_number'] === '0812345678');

// Restore original/default settings
luuCaiDatThanhToan([
    'bank_name'           => $settings['bank_name'],
    'bank_branch'         => $settings['bank_branch'],
    'bank_account_number' => $settings['bank_account_number'],
    'bank_account_name'   => $settings['bank_account_name'],
    'promptpay_number'    => $settings['promptpay_number']
]);

// -----------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------
echo "\n====================================================================\n";
echo "Test Results: Total $passed Passed, $failed Failed\n";
echo "====================================================================\n";

if ($failed === 0) {
    echo "🎉 ALL PAYMENT SYSTEM TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED!\n";
    exit(1);
}
