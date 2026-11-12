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
    'SELECT ct.don_hang_id, ct.san_pham_id, ct.so_luong, ct.don_gia, 
            sp.ten AS ten_sach, sp.anh AS anh_sach,
            dh.id AS don_hang_id, dh.ngay_tao, dh.trang_thai,
            nd.ho_ten AS ten_khach, nd.email AS email_khach
     FROM chi_tiet_don ct
     JOIN san_pham sp ON ct.san_pham_id = sp.id
     JOIN don_hang dh ON ct.don_hang_id = dh.id
     JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id
     WHERE sp.chu_sach_id = ?
     ORDER BY dh.ngay_tao DESC, dh.id DESC
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
  <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); color: #ffffff; padding: 28px 32px; border-radius: 16px; margin-bottom: 28px; box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.3); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 18px; border: 1px solid rgba(255,255,255,0.08); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -20px; top: -20px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(234, 88, 12, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
    <div style="position: relative; z-index: 1;">
      <span style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: #ffffff; font-size: 0.75rem; font-weight: 800; padding: 5px 14px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35); display: inline-flex; align-items: center; gap: 6px;">
        <span>🏪</span> BOOK OWNER PORTAL &bull; KÊNH NGƯỜI BÁN
      </span>
      <h1 style="color: #ffffff; margin: 10px 0 6px 0; font-size: 1.85rem; font-weight: 800; font-family: 'Prompt', var(--font-family);">
        Xin chào, <?= e($u['ho_ten']) ?>!
      </h1>
      <p style="margin: 0; color: #94a3b8; font-size: 0.96rem;">
        Bảng điều khiển quản lý quầy sách sinh viên, tồn kho và theo dõi doanh thu bán hàng của bạn.
      </p>
    </div>
    <div style="position: relative; z-index: 1;">
      <a href="them-san-pham.php" class="nut nut--chinh" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); font-weight: 700; padding: 12px 22px; border-radius: 10px; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 20px rgba(22, 163, 74, 0.35); border: none;">
        <span style="font-size: 1.1rem;">➕</span> Đăng sách mới lên quầy
      </a>
    </div>
  </div>

  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 10px; margin-bottom: 22px; box-shadow: 0 2px 6px rgba(16,185,129,0.08);" aria-live="polite">
      <strong>✓ Thông báo:</strong> <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <?php if ($thongBaoLoi !== ''): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: 10px; margin-bottom: 22px; box-shadow: 0 2px 6px rgba(220,38,38,0.08);" aria-live="polite">
      <strong>⚠ Cảnh báo:</strong> <?= e($thongBaoLoi) ?>
    </div>
  <?php endif; ?>

  <!-- 4 THẺ THỐNG KÊ KPI CHO BOOK OWNER (Modern Cards) -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 18px; margin-bottom: 32px;">
    
    <!-- Thẻ 1: Sách trên kệ -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px 20px; box-shadow: 0 4px 15px -2px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
      <div style="width: 52px; height: 52px; border-radius: 12px; background: #eff6ff; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #1d4ed8; flex-shrink: 0;">📚</div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">Sách của tôi trên kệ</div>
        <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-top: 2px; font-family: 'Prompt', var(--font-family);"><?= number_format($tongSach) ?> <span style="font-size: 0.88rem; font-weight: 500; color: #64748b;">đầu sách</span></div>
      </div>
    </div>

    <!-- Thẻ 2: Tồn kho hiện có -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px 20px; box-shadow: 0 4px 15px -2px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
      <div style="width: 52px; height: 52px; border-radius: 12px; background: #ecfdf5; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #059669; flex-shrink: 0;">📦</div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">Tổng tồn kho hiện có</div>
        <div style="font-size: 1.85rem; font-weight: 800; color: #065f46; margin-top: 2px; font-family: 'Prompt', var(--font-family);"><?= number_format($tongTonKho) ?> <span style="font-size: 0.88rem; font-weight: 500; color: #64748b;">cuốn</span></div>
      </div>
    </div>

    <!-- Thẻ 3: Số lượng đã bán -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px 20px; box-shadow: 0 4px 15px -2px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
      <div style="width: 52px; height: 52px; border-radius: 12px; background: #fffbeb; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #d97706; flex-shrink: 0;">🛍️</div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">Số lượng đã bán</div>
        <div style="font-size: 1.85rem; font-weight: 800; color: #92400e; margin-top: 2px; font-family: 'Prompt', var(--font-family);"><?= number_format($thongKeBan['tong_ban']) ?> <span style="font-size: 0.88rem; font-weight: 500; color: #64748b;">cuốn</span></div>
      </div>
    </div>

    <!-- Thẻ 4: Doanh thu của tôi -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px 20px; box-shadow: 0 4px 15px -2px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
      <div style="width: 52px; height: 52px; border-radius: 12px; background: #fff7ed; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #ea580c; flex-shrink: 0;">💰</div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600;">Doanh thu của tôi</div>
        <div style="font-size: 1.7rem; font-weight: 800; color: #c2410c; margin-top: 2px; font-family: 'Prompt', var(--font-family);"><?= vnd((int)$thongKeBan['tong_doanh_thu']) ?></div>
      </div>
    </div>

  </div>

  <!-- BẢNG 1: QUẢN LÝ SÁCH CỦA BOOK OWNER -->
  <section style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px 26px; margin-bottom: 35px; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.04);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
      <div>
        <h2 style="margin: 0; color: #0f172a; font-size: 1.35rem; font-weight: 800; font-family: 'Prompt', var(--font-family);">
          📚 Danh sách sách bạn đang đăng bán
        </h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.88rem;">Bạn có toàn quyền chỉnh sửa và cập nhật giá hoặc số lượng tồn kho sách của mình.</p>
      </div>
      <a href="them-san-pham.php" class="nut nut--vien" style="font-size: 0.85rem; padding: 8px 16px; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
        <span>+</span> Đăng thêm sách mới
      </a>
    </div>

    <?php if (empty($dsSach)): ?>
      <div style="text-align: center; padding: 45px 20px; color: #64748b;">
        <p style="font-size: 1.1rem; margin-bottom: 14px;">Bạn chưa có cuốn sách nào được đăng bán.</p>
        <a href="them-san-pham.php" class="nut nut--chinh" style="border-radius: 8px; padding: 10px 20px;">Đăng cuốn sách đầu tiên của bạn &rarr;</a>
      </div>
    <?php else: ?>
      <div style="overflow-x: auto;">
        <table class="bang" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
          <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left; color: #475569;">
              <th style="padding: 12px 14px; width: 60px;">Mã</th>
              <th style="padding: 12px 14px; width: 75px;">Ảnh</th>
              <th style="padding: 12px 14px;">Tên sách</th>
              <th style="padding: 12px 14px; width: 160px;">Danh mục</th>
              <th style="padding: 12px 14px; text-align: right; width: 130px;">Giá bán</th>
              <th style="padding: 12px 14px; text-align: center; width: 90px;">Tồn kho</th>
              <th style="padding: 12px 14px; text-align: center; width: 160px;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dsSach as $s): ?>
              <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                <td style="padding: 12px 14px; font-weight: 700; color: #64748b;">#<?= $s['id'] ?></td>
                <td style="padding: 12px 14px;">
                  <img src="<?= e(anhSach($s['anh'])) ?>" alt="<?= e($s['ten']) ?>" width="48" height="64" style="object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.06);">
                </td>
                <td style="padding: 12px 14px;">
                  <strong><a href="chi-tiet.php?id=<?= $s['id'] ?>" target="_blank" style="color: #0f2b5c; text-decoration: none; font-size: 0.95rem;"><?= e($s['ten']) ?></a></strong>
                </td>
                <td style="padding: 12px 14px;"><span class="huy-hieu" style="font-size: 0.78rem; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 600;"><?= e($s['ten_danh_muc']) ?></span></td>
                <td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #dc2626; font-size: 1rem;"><?= vnd($s['gia']) ?></td>
                <td style="padding: 12px 14px; text-align: center;">
                  <span style="font-weight: 800; color: <?= $s['ton_kho'] > 0 ? '#16a34a' : '#dc2626' ?>; background: <?= $s['ton_kho'] > 0 ? '#f0fdf4' : '#fef2f2' ?>; padding: 3px 10px; border-radius: 999px; font-size: 0.85rem;">
                    <?= $s['ton_kho'] ?>
                  </span>
                </td>
                <td style="padding: 12px 14px; text-align: center;">
                  <div style="display: flex; gap: 8px; justify-content: center;">
                    <a href="sua-san-pham.php?id=<?= $s['id'] ?>" class="nut nut--vien" style="padding: 6px 12px; font-size: 0.82rem; border-radius: 6px; font-weight: 700; color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff;" title="Chỉnh sửa thông tin sách">✏️ Sửa</a>
                    <form method="POST" action="xoa-san-pham.php" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cuốn sách này khỏi kho của mình?');">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $s['id'] ?>">
                      <button type="submit" class="nut nut--nguy-hiem" style="padding: 6px 12px; font-size: 0.82rem; border-radius: 6px; font-weight: 700;" title="Xóa sách">🗑️ Xóa</button>
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
  <section style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px 26px; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.04);">
    <div style="margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
      <h2 style="margin: 0; color: #0f172a; font-size: 1.35rem; font-weight: 800; font-family: 'Prompt', var(--font-family);">
        📦 Danh sách khách đặt mua sách của bạn (<?= count($dsDonHang) ?> lượt đặt)
      </h2>
      <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.88rem;">Theo dõi đơn hàng và tình trạng vận chuyển sách đến tay khách hàng.</p>
    </div>

    <?php if (empty($dsDonHang)): ?>
      <div style="text-align: center; padding: 35px; color: #64748b;">
        <p style="margin: 0; font-size: 1rem;">Chưa có khách hàng nào đặt mua sách từ gian hàng của bạn.</p>
      </div>
    <?php else: ?>
      <div style="overflow-x: auto;">
        <table class="bang" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
          <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left; color: #475569;">
              <th style="padding: 12px 14px;">Mã đơn</th>
              <th style="padding: 12px 14px;">Ngày đặt</th>
              <th style="padding: 12px 14px;">Khách hàng</th>
              <th style="padding: 12px 14px;">Tên cuốn sách</th>
              <th style="padding: 12px 14px; text-align: center;">SL</th>
              <th style="padding: 12px 14px; text-align: right;">Đơn giá</th>
              <th style="padding: 12px 14px; text-align: right;">Thành tiền</th>
              <th style="padding: 12px 14px; text-align: center;">Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dsDonHang as $dh): 
              $thanhTien = $dh['so_luong'] * $dh['don_gia'];
            ?>
              <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                <td style="padding: 12px 14px; font-weight: 800; color: #1e40af;">#<?= $dh['don_hang_id'] ?></td>
                <td style="padding: 12px 14px; color: #64748b; font-size: 0.85rem;"><?= date('d/m/Y H:i', strtotime($dh['ngay_tao'])) ?></td>
                <td style="padding: 12px 14px;">
                  <strong style="color: #1e293b;"><?= e($dh['ten_khach']) ?></strong><br>
                  <small style="color: #64748b;">📧 <?= e($dh['email_khach']) ?></small>
                </td>
                <td style="padding: 12px 14px;">
                  <span style="font-weight: 600; color: #0f172a;"><?= e($dh['ten_sach']) ?></span>
                </td>
                <td style="padding: 12px 14px; text-align: center; font-weight: 800;"><?= $dh['so_luong'] ?></td>
                <td style="padding: 12px 14px; text-align: right; color: #475569;"><?= vnd($dh['don_gia']) ?></td>
                <td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #16a34a;"><?= vnd($thanhTien) ?></td>
                <td style="padding: 12px 14px; text-align: center;">
                  <?php if ($dh['trang_thai'] === 'da_giao'): ?>
                    <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.78rem; padding: 4px 10px; border-radius: 999px;">✓ Đã giao</span>
                  <?php elseif ($dh['trang_thai'] === 'dang_giao'): ?>
                    <span class="huy-hieu" style="background:#e0f2fe;color:#0284c7;font-size:0.78rem; padding: 4px 10px; border-radius: 999px;">⚡ Đang giao</span>
                  <?php elseif ($dh['trang_thai'] === 'da_huy'): ?>
                    <span class="huy-hieu huy-hieu--nguy-hiem" style="font-size: 0.78rem; padding: 4px 10px; border-radius: 999px;">✕ Đã hủy</span>
                  <?php else: ?>
                    <span class="huy-hieu" style="background:#fef3c7;color:#b45309;font-size:0.78rem; padding: 4px 10px; border-radius: 999px;">⏳ Chờ xử lý</span>
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
