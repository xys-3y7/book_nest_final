<?php
/**
 * inc/don-hang.php - Xử lý đặt hàng trong giao dịch CSDL ACID
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

use RuntimeException;

/**
 * Lưu đơn hàng và chi tiết đơn hàng trong một Transaction CSDL an toàn
 * Cập nhật số lượng tồn kho với điều kiện ton_kho >= ? (chống overselling do race condition)
 *
 * @param int $nguoiDungId ID tài khoản người đặt hàng
 * @param array $gio Mảng giỏ hàng dạng [san_pham_id => so_luong]
 * @param string $diaChi Địa chỉ nhận sách
 * @param string $soDienThoai Số điện thoại liên hệ
 * @param string|null $ghiChu Ghi chú thêm
 * @return int ID đơn hàng vừa tạo thành công
 */
function luuDonHang(int $nguoiDungId, array $gio, string $diaChi, string $soDienThoai, ?string $ghiChu = null): int
{
    if (empty($gio)) {
        throw new RuntimeException('Giỏ hàng đang trống, không thể đặt hàng.');
    }

    $db = db();
    $db->beginTransaction();

    try {
        // 1. Đọc thông tin và đơn giá hiện tại của toàn bộ sản phẩm trong giỏ qua một câu WHERE IN
        $ids = array_map('intval', array_keys($gio));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $st = $db->prepare("SELECT id, ten, gia, ton_kho FROM san_pham WHERE id IN ($placeholders)");
        $st->execute($ids);
        $sanPhams = $st->fetchAll();

        $bangGia = [];
        foreach ($sanPhams as $sp) {
            $bangGia[$sp['id']] = $sp;
        }

        // 2. Kiểm tra và trừ tồn kho từng sản phẩm với điều kiện ton_kho >= so_luong
        $tongTien = 0;
        $stUpdateTonKho = $db->prepare('UPDATE san_pham SET ton_kho = ton_kho - ? WHERE id = ? AND ton_kho >= ?');

        foreach ($gio as $spId => $sl) {
            $sl = (int)$sl;
            if ($sl <= 0) {
                continue;
            }
            if (!isset($bangGia[$spId])) {
                throw new RuntimeException("Sản phẩm mang mã #{$spId} không tồn tại trong hệ thống.");
            }

            $sp = $bangGia[$spId];

            // Cập nhật nguyên tử (Atomic Update) - nếu ton_kho < sl thì rowCount() sẽ bằng 0
            $stUpdateTonKho->execute([$sl, $spId, $sl]);
            if ($stUpdateTonKho->rowCount() === 0) {
                throw new RuntimeException("Rất tiếc! Sách \"{$sp['ten']}\" không đủ số lượng trong kho (còn {$sp['ton_kho']} cuốn).");
            }

            $tongTien += $sp['gia'] * $sl;
        }

        // 3. Tạo bản ghi đơn hàng chính (don_hang)
        $stDonHang = $db->prepare('INSERT INTO don_hang (nguoi_dung_id, tong_tien, dia_chi, so_dien_thoai, ghi_chu) VALUES (?, ?, ?, ?, ?)');
        $stDonHang->execute([$nguoiDungId, $tongTien, $diaChi, $soDienThoai, $ghiChu]);
        $donHangId = (int)$db->lastInsertId();

        // 4. Lưu từng dòng chi tiết đơn hàng (chi_tiet_don) với đơn giá tại thời điểm đặt
        $stChiTiet = $db->prepare('INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia) VALUES (?, ?, ?, ?)');
        foreach ($gio as $spId => $sl) {
            $sl = (int)$sl;
            if ($sl <= 0) continue;
            $donGia = $bangGia[$spId]['gia'];
            $stChiTiet->execute([$donHangId, $spId, $sl, $donGia]);
        }

        // 5. Xác nhận giao dịch thành công toàn vẹn
        $db->commit();
        return $donHangId;

    } catch (Throwable $e) {
        // Nếu có bất kỳ lỗi nào xảy ra, hoàn tác toàn bộ thao tác trong giao dịch
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
