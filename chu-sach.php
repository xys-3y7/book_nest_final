<?php
/**
 * chu-sach.php - Bảng điều khiển dành riêng cho Chủ sách (Book Owner Dashboard)
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Mô hình 3 Roles: Admin - Customer - Book Owner
 * Quyền hạn Book Owner:
 *   - Quản lý sách của chính mình (Thêm, Sửa, Xóa chỉ sách do mình đăng)
 *   - Quản lý tồn kho sách của mình
 *   - Xem danh sách đơn hàng khách mua sách của mình & theo dõi doanh thu cá nhân
 */

require_once __DIR__ . '/inc/config.php';

// Yêu cầu đăng nhập và có quyền 'chu_sach' hoặc 'quan_tri'
$u = nguoiDung();
if (!$u || ($u['vai_tro'] !== 'chu_sach' && $u['vai_tro'] !== 'quan_tri')) {
    ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI'], 'vai_tro' => $u['vai_tro'] ?? 'khach']);
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

$db = db();
$chuSachId = (int)$u['id'];

// 1. Thống kê số liệu cá nhân của Book Owner
// Tổng số sách của chủ sách
$stDemSach = $db->prepare('SELECT COUNT(*) FROM san_pham WHERE chu_sach_id = ?');
$stDemSach->execute([$chuSachId]);
$tongSach = (int)$stDemSach->fetchColumn();

// Tổng số lượng tồn kho
$stTonKho = $db->prepare('SELECT COALESCE(SUM(ton_kho), 0) FROM san_pham WHERE chu_sach_id = ?');
$stTonKho->execute([$chuSachId]);
$tongTonKho = (int)$stTonKho->fetchColumn();

// Tổng số lượt mua và doanh thu từ các sách của Book Owner này
$stDoanhThu = $db->prepare(
    'SELECT COALESCE(SUM(ct.so_luong), 0) AS tong_ban,
            COALESCE(SUM(ct.so_luong * ct.don_gia), 0) AS tong_doanh_thu
     FROM chi_tiet_don ct
     JOIN san_pham sp ON ct.san_pham_id = sp.id
     JOIN don_hang dh ON ct.don_hang_id = dh.id
     WHERE sp.chu_sach_id = ? AND dh.trang_thai != "da_huy"'
);
$stDoanhThu->execute([$chuSachId]);
$thongKeBan = $stDoanhThu->fetch() ?: ['tong_ban' => 0, 'tong_doanh_thu' => 0];

// 2. Danh sách sách của Book Owner
$stSach = $db->prepare(
    'SELECT sp.*, dm.ten AS ten_danh_muc 
     FROM san_pham sp
     JOIN danh_muc dm ON sp.danh_muc_id = dm.id
     WHERE sp.chu_sach_id = ?
     ORDER BY sp.id DESC'
);
$stSach->execute([$chuSachId]);
$dsSach = $stSach->fetchAll();

// 3. Danh sách đơn hàng mà khách đã mua sách của Book Owner này
$stDonHang = $db->prepare(
    'SELECT ct.id AS chi_tiet_id, ct.so_luong, ct.don_gia, 
            sp.ten AS ten_sach, sp.anh AS anh_sach,
            dh.id AS don_hang_id, dh.ngay_tao, dh.trang_thai,
            nd.ho_ten AS ten_khach, nd.email AS email_khach
     FROM chi_tiet_don ct
     JOIN san_pham sp ON ct.san_pham_id = sp.id
     JOIN don_hang dh ON ct.don_hang_id = dh.id
     JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id
     WHERE sp.chu_sach_id = ?
     ORDER BY dh.ngay_tao DESC, ct.id DESC
     LIMIT 20'
);
$stDonHang->execute([$chuSachId]);
$dsDonHang = $stDonHang->fetchAll();

$thongBao = $_SESSION['flash'] ?? '';
$thongBaoLoi = $_SESSION['flash_loi'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_loi']);

$tieuDe = 'Bảng điều khiển Chủ sách (Book Owner Dashboard)';
$trang = 'chu-sach';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="padding-top: 25px; padding-bottom: 50px;">
  
  <!-- Banner chào mừng Book Owner -->
  <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; padding: 25px 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
      <span style="background: #ea580c; color: #ffffff; font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase;">
        🏪 Book Owner Portal &bull; Kênh Người Bán
      </span>
      <h1 style="color: #ffffff; margin: 8px 0 4px 0; font-size: 1.7rem; font-weight: 800;">
        Xin chào, <?= e($u['ho_ten']) ?>!
      </h1>
      <p style="margin: 0; color: #94a3b8; font-size: 0.95rem;">
        Quản lý kho sách, theo dõi lượng đặt mua và doanh số bán sách trực tiếp trên hệ thống BookNest.
      </p>
    </div>
    <div>
      <a href="them-san-pham.php" class="nut nut--chinh" style="background: #16a34a; font-weight: 700; padding: 10px 18px; border-radius: 8px; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 6px;">
        <span>➕</span> Đăng sách mới lên sàn
      </a>
    </div>
  </div>

  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;" aria-live="polite">
      <strong>✓ Thành công:</strong> <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <?php if ($thongBaoLoi !== ''): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;" aria-live="polite">
      <strong>⚠ Lỗi:</strong> <?= e($thongBaoLoi) ?>
    </div>
  <?php endif; ?>

  <!-- 4 THẺ THỐNG KÊ KPI CHO BOOK OWNER -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 30px;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid #0284c7;">
      <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">📚 Sách của tôi trên kệ</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #0f2b5c; margin-top: 6px;"><?= number_format($tongSach) ?> <span style="font-size: 0.9rem; font-weight: normal; color: #64748b;">đầu sách</span></div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid #16a34a;">
      <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">📦 Tổng tồn kho hiện có</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #166534; margin-top: 6px;"><?= number_format($tongTonKho) ?> <span style="font-size: 0.9rem; font-weight: normal; color: #64748b;">cuốn</span></div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid #d97706;">
      <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">📈 Số lượng đã bán</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #b45309; margin-top: 6px;"><?= number_format($thongKeBan['tong_ban']) ?> <span style="font-size: 0.9rem; font-weight: normal; color: #64748b;">cuốn</span></div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid #ea580c;">
      <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">💰 Doanh thu của tôi</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #c2410c; margin-top: 6px;"><?= vnd((int)$thongKeBan['tong_doanh_thu']) ?></div>
    </div>
  </div>

  <!-- BẢNG 1: QUẢN LÝ SÁCH CỦA BOOK OWNER -->
  <section style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 35px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h2 style="margin: 0; color: #0f2b5c; font-size: 1.3rem;">
        📚 Danh sách sách bạn đang đăng bán (<?= count($dsSach) ?>)
      </h2>
      <a href="them-san-pham.php" class="nut nut--vien" style="font-size: 0.85rem; padding: 6px 12px;">+ Đăng thêm sách mới</a>
    </div>

    <?php if (empty($dsSach)): ?>
      <div style="text-align: center; padding: 40px 20px; color: #64748b;">
        <p style="font-size: 1.1rem; margin-bottom: 12px;">Bạn chưa có cuốn sách nào được đăng bán.</p>
        <a href="them-san-pham.php" class="nut nut--chinh">Đăng cuốn sách đầu tiên của bạn &rarr;</a>
      </div>
    <?php else: ?>
      <div style="overflow-x: auto;">
        <table class="bang" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
          <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
              <th style="padding: 10px; width: 60px;">Mã</th>
              <th style="padding: 10px; width: 70px;">Ảnh</th>
              <th style="padding: 10px;">Tên sách</th>
              <th style="padding: 10px;">Danh mục</th>
              <th style="padding: 10px; text-align: right;">Giá bán</th>
              <th style="padding: 10px; text-align: center;">Tồn kho</th>
              <th style="padding: 10px; text-align: center; width: 140px;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dsSach as $s): ?>
              <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px; color: #64748b;">#<?= $s['id'] ?></td>
                <td style="padding: 10px;">
                  <img src="<?= e(anhSach($s['anh'])) ?>" alt="<?= e($s['ten']) ?>" width="45" height="60" style="object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                </td>
                <td style="padding: 10px;">
                  <strong><a href="chi-tiet.php?id=<?= $s['id'] ?>" target="_blank" style="color: #0f2b5c; text-decoration: none;"><?= e($s['ten']) ?></a></strong>
                </td>
                <td style="padding: 10px;"><span class="huy-hieu" style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1;"><?= e($s['ten_danh_muc']) ?></span></td>
                <td style="padding: 10px; text-align: right; font-weight: 700; color: #dc2626;"><?= vnd($s['gia']) ?></td>
                <td style="padding: 10px; text-align: center;">
                  <span style="font-weight: 700; color: <?= $s['ton_kho'] > 0 ? '#16a34a' : '#dc2626' ?>;">
                    <?= $s['ton_kho'] ?>
                  </span>
                </td>
                <td style="padding: 10px; text-align: center;">
                  <div style="display: flex; gap: 6px; justify-content: center;">
                    <a href="sua-san-pham.php?id=<?= $s['id'] ?>" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.8rem;" title="Chỉnh sửa thông tin sách">Sửa</a>
                    <form method="POST" action="xoa-san-pham.php" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cuốn sách này khỏi kho của mình?');">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $s['id'] ?>">
                      <button type="submit" class="nut nut--nguy-hiem" style="padding: 4px 8px; font-size: 0.8rem;" title="Xóa sách">Xóa</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <!-- BẢNG 2: ĐƠN HÀNG KHÁCH MUA SÁCH CỦA BOOK OWNER -->
  <section style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <h2 style="margin: 0 0 18px 0; color: #0f2b5c; font-size: 1.3rem;">
      📦 Danh sách khách đặt mua sách của bạn (<?= count($dsDonHang) ?> lượt đặt)
    </h2>

    <?php if (empty($dsDonHang)): ?>
      <div style="text-align: center; padding: 30px; color: #64748b;">
        <p style="margin: 0;">Chưa có khách hàng nào đặt mua sách từ gian hàng của bạn.</p>
      </div>
    <?php else: ?>
      <div style="overflow-x: auto;">
        <table class="bang" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
          <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
              <th style="padding: 10px;">Mã đơn</th>
              <th style="padding: 10px;">Ngày đặt</th>
              <th style="padding: 10px;">Khách hàng</th>
              <th style="padding: 10px;">Tên cuốn sách</th>
              <th style="padding: 10px; text-align: center;">SL</th>
              <th style="padding: 10px; text-align: right;">Đơn giá</th>
              <th style="padding: 10px; text-align: right;">Thành tiền</th>
              <th style="padding: 10px; text-align: center;">Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dsDonHang as $dh): 
              $thanhTien = $dh['so_luong'] * $dh['don_gia'];
            ?>
              <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px; font-weight: 700; color: #1e40af;">#<?= $dh['don_hang_id'] ?></td>
                <td style="padding: 10px; color: #64748b; font-size: 0.85rem;"><?= date('d/m/Y H:i', strtotime($dh['ngay_tao'])) ?></td>
                <td style="padding: 10px;">
                  <strong><?= e($dh['ten_khach']) ?></strong><br>
                  <small style="color: #64748b;"><?= e($dh['email_khach']) ?></small>
                </td>
                <td style="padding: 10px;">
                  <span><?= e($dh['ten_sach']) ?></span>
                </td>
                <td style="padding: 10px; text-align: center; font-weight: bold;"><?= $dh['so_luong'] ?></td>
                <td style="padding: 10px; text-align: right; color: #475569;"><?= vnd($dh['don_gia']) ?></td>
                <td style="padding: 10px; text-align: right; font-weight: 700; color: #16a34a;"><?= vnd($thanhTien) ?></td>
                <td style="padding: 10px; text-align: center;">
                  <?php if ($dh['trang_thai'] === 'da_giao'): ?>
                    <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.75rem;">✓ Đã giao</span>
                  <?php elseif ($dh['trang_thai'] === 'dang_giao'): ?>
                    <span class="huy-hieu" style="background:#e0f2fe;color:#0284c7;font-size:0.75rem;">⚡ Đang giao</span>
                  <?php elseif ($dh['trang_thai'] === 'da_huy'): ?>
                    <span class="huy-hieu huy-hieu--nguy-hiem" style="font-size: 0.75rem;">✕ Đã hủy</span>
                  <?php else: ?>
                    <span class="huy-hieu" style="background:#fef3c7;color:#b45309;font-size:0.75rem;">⏳ Chờ xử lý</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
