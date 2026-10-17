<?php
/**
 * don-hang.php - Quản lý và xem chi tiết đơn hàng của người dùng / quản trị
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 5 (Bảng 1): Đơn hàng của tôi, xem chi tiết, phân quyền chủ sở hữu dữ liệu (đơn người khác -> 404)
 */

require_once __DIR__ . '/inc/config.php';

// Yêu cầu bắt buộc phải đăng nhập
yeuCauDangNhap();

$u = nguoiDung();
$db = db();

$idDon = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

// Trường hợp 1: Xem chi tiết một đơn hàng cụ thể
if ($idDon) {
    $st = $db->prepare(
        'SELECT dh.*, nd.ho_ten, nd.email 
         FROM don_hang dh 
         JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
         WHERE dh.id = ?'
    );
    $st->execute([$idDon]);
    $don = $st->fetch();

    // BẢO MẬT PHÂN QUYỀN CHỦ SỞ HỮU DỮ LIỆU:
    // Nếu đơn hàng không tồn tại HOẶC (không phải quản trị VÀ không phải đơn của chính mình) -> Báo lỗi 404
    if (!$don || ($u['vai_tro'] !== 'quan_tri' && (int)$don['nguoi_dung_id'] !== (int)$u['id'])) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }

    // Lấy chi tiết các cuốn sách trong đơn kèm đơn giá tại thời điểm đặt
    $stItems = $db->prepare(
        'SELECT ctd.*, sp.ten, sp.anh 
         FROM chi_tiet_don ctd 
         JOIN san_pham sp ON ctd.san_pham_id = sp.id 
         WHERE ctd.don_hang_id = ?'
    );
    $stItems->execute([$idDon]);
    $items = $stItems->fetchAll();

    $tieuDe = 'Chi tiết đơn hàng #' . $idDon;
    $trang = 'don-hang';
    require __DIR__ . '/inc/header.php';
    ?>
    <main class="noi-dung-chinh khung-chua" style="max-width: 850px; margin: 30px auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0; color: #1e3b6d;">Chi tiết đơn hàng #<?= $idDon ?></h1>
        <a href="don-hang.php" class="nut nut--vien">&larr; Danh sách đơn hàng</a>
      </div>

      <?php if (!empty($_SESSION['flash'])): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;" aria-live="polite">
          <strong>✓ Thông báo:</strong> <?= e($_SESSION['flash']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
      <?php endif; ?>

      <div style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 15px;">
          <div>
            <p style="margin: 3px 0;"><strong>Ngày đặt:</strong> <?= e($don['ngay_tao']) ?></p>
            <p style="margin: 3px 0;"><strong>Khách hàng:</strong> <?= e($don['ho_ten']) ?> (<code><?= e($don['email']) ?></code>)</p>
            <p style="margin: 3px 0;"><strong>Số điện thoại:</strong> <?= e($don['so_dien_thoai']) ?></p>
            <p style="margin: 3px 0;"><strong>Địa chỉ giao:</strong> <?= e($don['dia_chi']) ?></p>
            <?php if (!empty($don['ghi_chu'])): ?>
              <p style="margin: 3px 0; color: #64748b;"><strong>Ghi chú:</strong> <?= e($don['ghi_chu']) ?></p>
            <?php endif; ?>
          </div>
          <div style="text-align: right;">
            <p style="margin: 3px 0;"><strong>Trạng thái:</strong>
              <?php
              $nhanTrangThai = [
                  'cho_xu_ly' => '<span style="background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:4px;font-weight:bold;">Chờ xử lý</span>',
                  'dang_giao' => '<span style="background:#e0f2fe;color:#0369a1;padding:4px 10px;border-radius:4px;font-weight:bold;">Đang giao</span>',
                  'da_giao'   => '<span style="background:#ecfdf5;color:#065f46;padding:4px 10px;border-radius:4px;font-weight:bold;">Đã giao</span>',
                  'da_huy'    => '<span style="background:#fee2e2;color:#991b1b;padding:4px 10px;border-radius:4px;font-weight:bold;">Đã hủy</span>',
              ];
              echo $nhanTrangThai[$don['trang_thai']] ?? e($don['trang_thai']);
              ?>
            </p>
            <p style="margin: 10px 0 0 0; font-size: 1.25rem; font-weight: bold; color: #dc2626;">
              Tổng cộng: <?= vnd($don['tong_tien']) ?>
            </p>
          </div>
        </div>

        <h3 style="margin: 0 0 12px 0;">Danh sách sách đã mua</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
          <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
              <th style="padding: 10px;">Ảnh</th>
              <th style="padding: 10px;">Tên sách</th>
              <th style="padding: 10px; width: 120px;">Đơn giá lúc đặt</th>
              <th style="padding: 10px; width: 80px; text-align: center;">Số lượng</th>
              <th style="padding: 10px; width: 130px; text-align: right;">Thành tiền</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px; width: 60px;">
                  <img src="<?= e(anhSach($item['anh'])) ?>" alt="Bìa" width="45" height="55" style="border-radius: 4px; object-fit: cover;">
                </td>
                <td style="padding: 10px;">
                  <a href="chi-tiet.php?id=<?= $item['san_pham_id'] ?>" style="font-weight: bold; color: #1e40af; text-decoration: none;">
                    <?= e($item['ten']) ?>
                  </a>
                </td>
                <td style="padding: 10px;"><?= vnd($item['don_gia']) ?></td>
                <td style="padding: 10px; text-align: center; font-weight: bold;"><?= $item['so_luong'] ?></td>
                <td style="padding: 10px; text-align: right; font-weight: bold; color: #dc2626;">
                  <?= vnd($item['don_gia'] * $item['so_luong']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

// Trường hợp 2: Xem danh sách các đơn hàng
$laQuanTri = $u['vai_tro'] === 'quan_tri';

if ($laQuanTri) {
    // Quản trị viên xem mọi đơn hàng
    $st = $db->query(
        'SELECT dh.*, nd.ho_ten, nd.email 
         FROM don_hang dh 
         JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
         ORDER BY dh.ngay_tao DESC, dh.id DESC'
    );
} else {
    // Khách hàng chỉ xem danh sách đơn hàng của chính mình
    $st = $db->prepare(
        'SELECT dh.*, nd.ho_ten, nd.email 
         FROM don_hang dh 
         JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
         WHERE dh.nguoi_dung_id = ? 
         ORDER BY dh.ngay_tao DESC, dh.id DESC'
    );
    $st->execute([$u['id']]);
}
$danhSachDon = $st->fetchAll();

$tieuDe = $laQuanTri ? 'Quản lý Đơn hàng hệ thống' : 'Đơn hàng của tôi';
$trang = 'don-hang';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d;"><?= $laQuanTri ? 'Quản lý toàn bộ Đơn hàng' : 'Đơn hàng của tôi' ?></h1>
      <p style="margin: 5px 0 0 0; color: #64748b;">
        <?= $laQuanTri ? 'Theo dõi và xử lý đơn đặt sách của toàn bộ sinh viên trên hệ thống.' : 'Xem danh sách và tiến độ các đơn đặt giáo trình của bạn.' ?>
      </p>
    </div>
    <a href="danh-sach.php" class="nut nut--chinh">+ Tiếp tục mua sách</a>
  </div>

  <?php if (empty($danhSachDon)): ?>
    <div style="text-align: center; padding: 50px 20px; background: #ffffff; border-radius: 8px; border: 1px dashed #cbd5e1;">
      <p style="font-size: 3rem; margin: 0 0 10px 0;">📦</p>
      <h2>Bạn chưa có đơn đặt hàng nào</h2>
      <p style="color: #64748b; margin-bottom: 20px;">Hãy dạo qua danh mục sách và đặt những cuốn giáo trình ưng ý nhé!</p>
      <a href="danh-sach.php" class="nut nut--chinh">Khám phá danh mục sách</a>
    </div>
  <?php else: ?>
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
            <th style="padding: 12px 15px; width: 80px;">Mã đơn</th>
            <th style="padding: 12px 15px; width: 160px;">Thời gian đặt</th>
            <?php if ($laQuanTri): ?>
              <th style="padding: 12px 15px;">Khách hàng</th>
            <?php endif; ?>
            <th style="padding: 12px 15px;">Địa chỉ giao sách</th>
            <th style="padding: 12px 15px; width: 130px;">Tổng tiền</th>
            <th style="padding: 12px 15px; width: 130px;">Trạng thái</th>
            <th style="padding: 12px 15px; width: 120px; text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($danhSachDon as $d): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 12px 15px; font-weight: bold; color: #1e40af;">
                #<?= $d['id'] ?>
              </td>
              <td style="padding: 12px 15px; font-size: 0.85rem; color: #64748b;">
                <?= e($d['ngay_tao']) ?>
              </td>
              <?php if ($laQuanTri): ?>
                <td style="padding: 12px 15px;">
                  <strong><?= e($d['ho_ten']) ?></strong><br>
                  <small style="color: #64748b;"><?= e($d['email']) ?></small>
                </td>
              <?php endif; ?>
              <td style="padding: 12px 15px; font-size: 0.9rem; color: #334155;">
                <?= e($d['dia_chi']) ?> (SĐT: <?= e($d['so_dien_thoai']) ?>)
              </td>
              <td style="padding: 12px 15px; font-weight: bold; color: #dc2626;">
                <?= vnd($d['tong_tien']) ?>
              </td>
              <td style="padding: 12px 15px;">
                <?php
                $nhanTrangThai = [
                    'cho_xu_ly' => '<span style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold;">Chờ xử lý</span>',
                    'dang_giao' => '<span style="background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold;">Đang giao</span>',
                    'da_giao'   => '<span style="background:#ecfdf5;color:#065f46;padding:3px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold;">Đã giao</span>',
                    'da_huy'    => '<span style="background:#fee2e2;color:#991b1b;padding:3px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold;">Đã hủy</span>',
                ];
                echo $nhanTrangThai[$d['trang_thai']] ?? e($d['trang_thai']);
                ?>
              </td>
              <td style="padding: 12px 15px; text-align: center;">
                <a href="don-hang.php?id=<?= $d['id'] ?>" class="nut nut--vien" style="padding: 5px 12px; font-size: 0.85rem;">
                  Chi tiết &rarr;
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
