<?php
/**
 * inc/kiem-tra.php - Kiểm tra tính hợp lệ dữ liệu sản phẩm cho trang thêm và sửa
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

function kiemTraSanPham(array $vao, array $idDanhMuc): array
{
    $du = [
        'ten'         => trim($vao['ten'] ?? ''),
        'gia'         => filter_var($vao['gia'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000]]),
        'ton_kho'     => filter_var($vao['ton_kho'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]),
        'danh_muc_id' => filter_var($vao['danh_muc_id'] ?? '', FILTER_VALIDATE_INT),
        'mo_ta'       => trim($vao['mo_ta'] ?? ''),
    ];

    $loi = [];
    $n = mb_strlen($du['ten']);
    if ($n < 2 || $n > 200) {
        $loi['ten'] = 'Tên sách phải dài từ 2 đến 200 ký tự';
    }
    if ($du['gia'] === false) {
        $loi['gia'] = 'Giá sách phải là số nguyên từ 1.000 VNĐ trở lên';
    }
    if ($du['ton_kho'] === false) {
        $loi['ton_kho'] = 'Tồn kho phải là số nguyên từ 0 đến 9.999';
    }
    if ($du['danh_muc_id'] === false || !in_array((int)$du['danh_muc_id'], $idDanhMuc, true)) {
        $loi['danh_muc_id'] = 'Vui lòng chọn một danh mục sách có sẵn';
    }
    if (mb_strlen($du['mo_ta']) > 2000) {
        $loi['mo_ta'] = 'Mô tả sách không được vượt quá 2.000 ký tự';
    }

    return [$du, $loi];
}
