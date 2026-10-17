<?php
/**
 * xoa-san-pham.php - Xóa sách khỏi CSDL MySQL (Dành riêng cho Quản trị viên)
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 3 (Bảng 1): Chỉ nhận POST (GET trả 405), yeuCauQuyen('quan_tri'), bắt lỗi 1451 FK constraint, PRG + Flash
 */

require_once __DIR__ . '/inc/config.php';

// Kiểm tra quyền Quản trị viên
yeuCauQuyen('quan_tri');

// Kiểm tra phương thức HTTP: Chỉ chấp nhận POST (Chống xóa do bấm nhầm link GET hoặc bot cào)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo "<!DOCTYPE html><html lang='vi'><head><meta charset='utf-8'><title>405 Method Not Allowed</title></head>";
    echo "<body style='font-family:sans-serif;padding:40px;text-align:center;'>";
    echo "<h1 style='color:#dc2626;'>405 - Phương thức không được phép</h1>";
    echo "<p>Thao tác xóa dữ liệu chỉ chấp nhận phương thức POST an toàn.</p>";
    echo "<p><a href='quan-tri.php'>&larr; Về trang quản trị</a></p>";
    echo "</body></html>";
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_loi'] = 'Mã sách cần xóa không hợp lệ.';
    header('Location: quan-tri.php');
    exit;
}

$db = db();

try {
    $st = $db->prepare('DELETE FROM san_pham WHERE id = ?');
    $st->execute([$id]);

    if ($st->rowCount() > 0) {
        $_SESSION['flash'] = "Đã xóa thành công cuốn sách #$id khỏi hệ thống.";
    } else {
        $_SESSION['flash_loi'] = "Không tìm thấy cuốn sách #$id để xóa.";
    }
} catch (PDOException $e) {
    // Bắt lỗi ràng buộc khóa ngoại MariaDB/MySQL 1451 (Cannot delete or update a parent row: a foreign key constraint fails)
    if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1451) {
        $_SESSION['flash_loi'] = "Không thể xóa cuốn sách #$id vì sách này đã có trong lịch sử đơn hàng của khách hàng (Ràng buộc toàn vẹn khóa ngoại 1451).";
    } else {
        throw $e;
    }
}

header('Location: quan-tri.php');
exit;
