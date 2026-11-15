<?php
/**
 * xoa-san-pham.php - Delete textbook from MySQL database (Admin & Seller)
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';

$u = nguoiDung();
if (!$u || ($u['vai_tro'] !== 'quan_tri' && $u['vai_tro'] !== 'chu_sach')) {
    ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI']]);
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

$dichDen = ($u['vai_tro'] === 'chu_sach') ? 'chu-sach.php' : 'quan-tri.php';

// Method check: POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo "<!DOCTYPE html><html lang='en'><head><meta charset='utf-8'><title>405 Method Not Allowed</title></head>";
    echo "<body style='font-family:sans-serif;padding:40px;text-align:center;'>";
    echo "<h1 style='color:#dc2626;'>405 - Method Not Allowed</h1>";
    echo "<p>Data deletion operations only accept secure POST requests.</p>";
    echo "<p><a href='$dichDen'>&larr; Back to management</a></p>";
    echo "</body></html>";
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_loi'] = 'Invalid book ID for deletion.';
    header("Location: $dichDen");
    exit;
}

$db = db();

$stFind = $db->prepare('SELECT chu_sach_id FROM san_pham WHERE id = ?');
$stFind->execute([$id]);
$sp = $stFind->fetch();

if (!$sp) {
    $_SESSION['flash_loi'] = "Book #$id not found for deletion.";
    header("Location: $dichDen");
    exit;
}

if ($u['vai_tro'] === 'chu_sach' && (int)$sp['chu_sach_id'] !== (int)$u['id']) {
    ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI'], 'loi' => 'xoa_sach_khong_phai_cua_minh']);
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

try {
    // Delete relations in cart and wishlist first
    $db->prepare('DELETE FROM gio_hang WHERE san_pham_id = ?')->execute([$id]);
    $db->prepare('DELETE FROM yeu_thich WHERE san_pham_id = ?')->execute([$id]);
    $db->prepare('DELETE FROM tv_sach_yeu_thich WHERE san_pham_id = ?')->execute([$id]);

    $st = $db->prepare('DELETE FROM san_pham WHERE id = ?');
    $st->execute([$id]);

    if ($st->rowCount() > 0) {
        if ($u['vai_tro'] === 'quan_tri') {
            $_SESSION['flash'] = "Violating textbook #$id has been successfully removed from the platform.";
        } else {
            $_SESSION['flash'] = "Textbook #$id has been successfully removed from your stall.";
        }
    } else {
        $_SESSION['flash_loi'] = "Book #$id not found for deletion.";
    }
} catch (PDOException $e) {
    if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1451) {
        $_SESSION['flash_loi'] = "Cannot delete book #$id because it already exists in completed customer order history (Foreign key constraint 1451).";
    } else {
        throw $e;
    }
}

header("Location: $dichDen");
exit;
