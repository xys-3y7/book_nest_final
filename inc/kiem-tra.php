<?php
/**
 * inc/kiem-tra.php - Kiểm tra tính hợp lệ dữ liệu sản phẩm cho trang thêm và sửa
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

function kiemTraSanPham(array $vao, array $idDanhMuc): array
{
    $du = [
        'ten'          => trim($vao['ten'] ?? ''),
        'ten_en'       => trim($vao['ten_en'] ?? ''),
        'tac_gia'      => trim($vao['tac_gia'] ?? ''),
        'tac_gia_en'   => trim($vao['tac_gia_en'] ?? ''),
        'isbn'         => trim($vao['isbn'] ?? ''),
        'nam_xuat_ban' => filter_var($vao['nam_xuat_ban'] ?? date('Y'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => 2099]]),
        'gia'          => filter_var($vao['gia'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000]]),
        'ton_kho'      => filter_var($vao['ton_kho'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]),
        'danh_muc_id'  => filter_var($vao['danh_muc_id'] ?? '', FILTER_VALIDATE_INT),
        'mo_ta'        => trim($vao['mo_ta'] ?? ''),
        'mo_ta_en'     => trim($vao['mo_ta_en'] ?? ''),
    ];

    $loi = [];
    $n = mb_strlen($du['ten']);
    if ($n < 2 || $n > 200) {
        $loi['ten'] = 'Book title must be between 2 and 200 characters.';
    }
    if ($du['ten_en'] !== '' && mb_strlen($du['ten_en']) > 200) {
        $loi['ten_en'] = 'English title must not exceed 200 characters.';
    }
    if ($du['tac_gia'] !== '' && mb_strlen($du['tac_gia']) > 150) {
        $loi['tac_gia'] = 'Author name must not exceed 150 characters.';
    }
    if ($du['tac_gia_en'] !== '' && mb_strlen($du['tac_gia_en']) > 150) {
        $loi['tac_gia_en'] = 'English author name must not exceed 150 characters.';
    }
    if ($du['gia'] === false) {
        $loi['gia'] = 'Book price must be an integer of at least 1,000 VNĐ.';
    }
    if ($du['ton_kho'] === false) {
        $loi['ton_kho'] = 'Stock quantity must be an integer between 0 and 9,999.';
    }
    if ($du['danh_muc_id'] === false || !in_array((int)$du['danh_muc_id'], $idDanhMuc, true)) {
        $loi['danh_muc_id'] = 'Please select a valid textbook category.';
    }
    if ($du['mo_ta'] !== '' && mb_strlen($du['mo_ta']) > 2000) {
        $loi['mo_ta'] = 'Book description must not exceed 2,000 characters.';
    }
    if ($du['mo_ta_en'] !== '' && mb_strlen($du['mo_ta_en']) > 2000) {
        $loi['mo_ta_en'] = 'English description must not exceed 2,000 characters.';
    }

    return [$du, $loi];
}
