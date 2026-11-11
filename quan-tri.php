<?php
/**
 * quan-tri.php - Bảng điều khiển Quản trị viên: Quản trị sách, Đơn hàng và Liên hệ
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 3 (Bảng 1): yeuCauQuyen('quan_tri'), danh sách phân trang, CRUD, xem liên hệ
 */

require_once __DIR__ . '/inc/config.php';

// Yêu cầu quyền Quản trị viên (quan_tri)
yeuCauQuyen('quan_tri');

use App\Data\KhoSanPham;
use App\Data\KhoLienHe;

$db = db();
$khoSp = new KhoSanPham($db);
$khoLh = new KhoLienHe($db);

// Xử lý cập nhật trạng thái đơn hàng nếu có POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cap_nhat_don'])) {
    $donId = filter_var($_POST['don_id'] ?? '', FILTER_VALIDATE_INT);
    $trangThaiMoi = trim($_POST['trang_thai'] ?? '');
    $hopLe = ['cho_xu_ly', 'dang_giao', 'da_giao', 'da_huy'];

    if ($donId && in_array($trangThaiMoi, $hopLe, true)) {
        $st = $db->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ?');
        $st->execute([$trangThaiMoi, $donId]);
        $_SESSION['flash'] = "Đã cập nhật trạng thái đơn hàng #$donId thành công!";
    }
    header('Location: quan-tri.php');
    exit;
}

// Xử lý đổi vai trò người dùng (Admin quản lý người dùng & chủ sách)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doi_vai_tro'])) {
    $uid = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);
    $vaiTroMoi = trim($_POST['vai_tro'] ?? '');
    $hopLe = ['khach_hang', 'chu_sach', 'quan_tri'];

    if ($uid && in_array($vaiTroMoi, $hopLe, true)) {
        $st = $db->prepare('UPDATE nguoi_dung SET vai_tro = ? WHERE id = ?');
        $st->execute([$vaiTroMoi, $uid]);
        $_SESSION['flash'] = "Đã cập nhật vai trò người dùng #$uid thành công!";
    }
    header('Location: quan-tri.php#nguoi-dung');
    exit;
}

// Phân trang danh sách sách quản trị
$trangSp = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'default' => 1]]);
$moiTrang = 10;
$tongSp = $khoSp->dem([]);
$soTrangSp = max(1, (int) ceil($tongSp / $moiTrang));
$trangSp = min($trangSp, $soTrangSp);
$dsSach = $khoSp->trang([], 'moi', $moiTrang, ($trangSp - 1) * $moiTrang);

// Đọc danh sách liên hệ mới nhất từ MySQL
$dsLienHe = $khoLh->tatCa();

// Đọc danh sách đơn hàng toàn hệ thống
$stDon = $db->query(
    'SELECT dh.*, nd.ho_ten, nd.email 
     FROM don_hang dh 
     JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
     ORDER BY dh.ngay_tao DESC, dh.id DESC 
     LIMIT 15'
);
$dsDonHang = $stDon->fetchAll();

// Đọc danh sách người dùng và chủ sách
$stUsers = $db->query(
    'SELECT nd.id, nd.email, nd.ho_ten, nd.vai_tro, nd.ngay_tao,
            COUNT(sp.id) AS so_sach
     FROM nguoi_dung nd
     LEFT JOIN san_pham sp ON sp.chu_sach_id = nd.id
     GROUP BY nd.id
     ORDER BY nd.id ASC'
);
$dsNguoiDung = $stUsers->fetchAll();

$thongBao = $_SESSION['flash'] ?? '';
$thongBaoLoi = $_SESSION['flash_loi'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_loi']);

$u = nguoiDung();
$tieuDe = 'Bảng điều khiển Quản trị viên';
$trang = 'quan-tri';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
    <div>
      <h1 style="margin: 0; font-size: 1.8rem; color: #0f172a;">Bảng điều khiển Quản trị viên</h1>
      <p style="margin: 5px 0 0 0; color: #64748b;">
        Xin chào, <strong><?= e($u['ho_ten']) ?></strong> (Vai trò: <code><?= e($u['vai_tro']) ?></code>)
      </p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="them-san-pham.php" class="nut nut--chinh">+ Thêm sách mới</a>
      <a href="dang-xuat.php" class="nut nut--vien" style="color: #dc2626; border-color: #fca5a5;">Đăng xuất</a>
    </div>
  </div>

  <!-- Thông báo Flash Message -->
  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;" aria-live="polite">
      <strong>✓ Hoàn tất:</strong> <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <?php if ($thongBaoLoi !== ''): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;" aria-live="assertive">
      <strong>⚠ Thông báo:</strong> <?= e($thongBaoLoi) ?>
    </div>
  <?php endif; ?>

  <!-- 1. QUẢN TRỊ DANH MỤC SÁCH / THỰC THỂ CHÍNH -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 35px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
      <h2 style="margin: 0; color: #1e3b6d;">Quản lý kho sách (Tổng: <?= $tongSp ?> cuốn)</h2>
      <a href="them-san-pham.php" class="nut nut--chinh" style="padding: 7px 16px; font-size: 0.9rem;">+ Thêm sản phẩm</a>
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
            <th style="padding: 10px 12px; width: 60px;">Mã ID</th>
            <th style="padding: 10px 12px; width: 70px;">Ảnh</th>
            <th style="padding: 10px 12px;">Tên giáo trình</th>
            <th style="padding: 10px 12px; width: 130px;">Danh mục</th>
            <th style="padding: 10px 12px; width: 140px;">Chủ sở hữu</th>
            <th style="padding: 10px 12px; width: 110px;">Đơn giá</th>
            <th style="padding: 10px 12px; width: 80px; text-align: center;">Tồn kho</th>
            <th style="padding: 10px 12px; width: 140px; text-align: center;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dsSach as $sp): ?>
            <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
              <td style="padding: 10px 12px; font-weight: bold; color: #64748b;">#<?= $sp->id ?></td>
              <td style="padding: 10px 12px;">
                <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa" width="45" height="55" style="border-radius: 4px; object-fit: cover;">
              </td>
              <td style="padding: 10px 12px;">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" target="_blank" style="font-weight: bold; color: #1e40af; text-decoration: none;">
                  <?= e($sp->ten) ?>
                </a>
              </td>
              <td style="padding: 10px 12px; font-size: 0.85rem; color: #475569;">
                <?= e($sp->tenDanhMuc ?? 'Chưa phân loại') ?>
              </td>
              <td style="padding: 10px 12px; font-size: 0.82rem;">
                <?php if ($sp->chuSachId): ?>
                  <span style="background: #fef3c7; color: #b45309; font-weight: 700; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                    🏪 <?= e($sp->tenChuSach ?: ('Chủ #' . $sp->chuSachId)) ?>
                  </span>
                <?php else: ?>
                  <span style="background: #eff6ff; color: #1d4ed8; font-weight: 700; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                    👑 Admin sàn
                  </span>
                <?php endif; ?>
              </td>
              <td style="padding: 10px 12px; font-weight: bold; color: #dc2626;">
                <?= vnd($sp->gia) ?>
              </td>
              <td style="padding: 10px 12px; text-align: center;">
                <span style="font-weight: bold; color: <?= $sp->tonKho > 0 ? '#16a34a' : '#dc2626' ?>;">
                  <?= $sp->tonKho ?>
                </span>
              </td>
              <td style="padding: 10px 12px; text-align: center;">
                <div style="display: flex; gap: 6px; justify-content: center;">
                  <a href="sua-san-pham.php?id=<?= $sp->id ?>" class="nut nut--vien" style="padding: 4px 10px; font-size: 0.8rem;">Sửa</a>
                  <form method="POST" action="xoa-san-pham.php" class="form-xoa-sp" style="margin: 0;">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <button type="submit" class="nut nut--vien" style="padding: 4px 10px; font-size: 0.8rem; color: #dc2626; border-color: #fca5a5;">Xóa</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Phân trang quản trị sách -->
    <?php if ($soTrangSp > 1): ?>
      <nav aria-label="Phân trang quản trị" style="margin-top: 20px; display: flex; justify-content: center; gap: 6px;">
        <?php for ($i = 1; $i <= $soTrangSp; $i++): ?>
          <a href="quan-tri.php?trang=<?= $i ?>" class="nut <?= $i === $trangSp ? 'nut--chinh' : 'nut--vien' ?>" style="padding: 5px 12px; font-size: 0.85rem;">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </section>

  <!-- 2. QUẢN LÝ ĐƠN ĐẶT HÀNG TOÀN HỆ THỐNG -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 35px;">
    <h2 style="margin: 0 0 15px 0; color: #1e3b6d;">Đơn đặt sách gần đây (Toàn hệ thống)</h2>
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
            <th style="padding: 10px 12px; width: 60px;">Mã đơn</th>
            <th style="padding: 10px 12px; width: 140px;">Ngày tạo</th>
            <th style="padding: 10px 12px;">Khách hàng</th>
            <th style="padding: 10px 12px; width: 120px;">Tổng tiền</th>
            <th style="padding: 10px 12px; width: 140px;">Trạng thái</th>
            <th style="padding: 10px 12px; width: 130px; text-align: center;">Cập nhật</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dsDonHang as $dh): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 10px 12px; font-weight: bold; color: #1e40af;">
                <a href="don-hang.php?id=<?= $dh['id'] ?>">#<?= $dh['id'] ?></a>
              </td>
              <td style="padding: 10px 12px; font-size: 0.85rem; color: #64748b;"><?= e($dh['ngay_tao']) ?></td>
              <td style="padding: 10px 12px;">
                <strong><?= e($dh['ho_ten']) ?></strong><br>
                <small style="color: #64748b;"><?= e($dh['email']) ?> | <?= e($dh['so_dien_thoai']) ?></small>
              </td>
              <td style="padding: 10px 12px; font-weight: bold; color: #dc2626;"><?= vnd($dh['tong_tien']) ?></td>
              <td style="padding: 10px 12px;">
                <form method="POST" action="quan-tri.php" style="display: flex; gap: 6px; align-items: center; margin: 0;">
                  <?= csrfField() ?>
                  <input type="hidden" name="cap_nhat_don" value="1">
                  <input type="hidden" name="don_id" value="<?= $dh['id'] ?>">
                  <select name="trang_thai" style="padding: 4px 8px; font-size: 0.85rem; border: 1px solid #cbd5e1; border-radius: 4px;">
                    <option value="cho_xu_ly" <?= $dh['trang_thai'] === 'cho_xu_ly' ? 'selected' : '' ?>>Chờ xử lý</option>
                    <option value="dang_giao" <?= $dh['trang_thai'] === 'dang_giao' ? 'selected' : '' ?>>Đang giao</option>
                    <option value="da_giao" <?= $dh['trang_thai'] === 'da_giao' ? 'selected' : '' ?>>Đã giao</option>
                    <option value="da_huy" <?= $dh['trang_thai'] === 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                  </select>
                  <button type="submit" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.8rem;">Lưu</button>
                </form>
              </td>
              <td style="padding: 10px 12px; text-align: center;">
                <a href="don-hang.php?id=<?= $dh['id'] ?>" class="nut nut--vien" style="padding: 4px 8px; font-size: 0.8rem;">Xem chi tiết</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- 3. DANH SÁCH LIÊN HỆ ĐÃ NHẬN TỪ BẢNG LIEN_HE -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
    <h2 style="margin: 0 0 15px 0; color: #1e3b6d;">Danh sách góp ý &amp; Liên hệ đã nhận (Tổng: <?= count($dsLienHe) ?>)</h2>
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
            <th style="padding: 10px 12px; width: 140px;">Thời gian</th>
            <th style="padding: 10px 12px; width: 180px;">Người gửi</th>
            <th style="padding: 10px 12px;">Chủ đề &amp; Nội dung liên hệ</th>
            <th style="padding: 10px 12px; width: 90px; text-align: center;">Ảnh</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dsLienHe as $lh): ?>
            <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: top;">
              <td style="padding: 10px 12px; font-size: 0.85rem; color: #64748b;"><?= e($lh['ngay_gui']) ?></td>
              <td style="padding: 10px 12px;">
                <strong><?= e($lh['ho_ten']) ?></strong><br>
                <small style="color: #64748b;">📧 <?= e($lh['email']) ?></small>
              </td>
              <td style="padding: 10px 12px;">
                <strong style="color: #1e40af;"><?= e($lh['chu_de']) ?></strong>
                <p style="margin: 4px 0 0 0; color: #334155; font-size: 0.9rem;"><?= nl2br(e($lh['noi_dung'])) ?></p>
              </td>
              <td style="padding: 10px 12px; text-align: center;">
                <?php if (!empty($lh['anh']) && file_exists(__DIR__ . '/uploads/' . $lh['anh'])): ?>
                  <a href="uploads/<?= e($lh['anh']) ?>" target="_blank">
                    <img src="uploads/<?= e($lh['anh']) ?>" alt="Ảnh" width="40" height="40" style="object-fit: cover; border-radius: 4px;">
                  </a>
                <?php else: ?>
                  <span style="color: #94a3b8; font-size: 0.8rem;">Không</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- 4. QUẢN LÝ NGƯỜI DÙNG & CHỦ SÁCH (USERS & BOOK OWNERS) -->
  <section id="nguoi-dung" style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 35px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
      <div>
        <h2 style="margin: 0; color: #1e3b6d;">Quản lý Người dùng &amp; Chủ sách (Book Owners)</h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Admin có toàn quyền kiểm soát danh sách người dùng, cấp quyền hoặc chuyển đổi vai trò Chủ sách (Book Owner).</p>
      </div>
      <span style="background: #f1f5f9; color: #475569; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 0.85rem;">Tổng: <?= count($dsNguoiDung) ?> tài khoản</span>
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
          <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 0.9rem;">
            <th style="padding: 10px 12px; width: 60px;">ID</th>
            <th style="padding: 10px 12px;">Họ và tên</th>
            <th style="padding: 10px 12px;">Email</th>
            <th style="padding: 10px 12px; width: 170px; text-align: center;">Vai trò hiện tại</th>
            <th style="padding: 10px 12px; width: 110px; text-align: center;">Sách đăng bán</th>
            <th style="padding: 10px 12px; width: 200px; text-align: center;">Phân quyền vai trò</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dsNguoiDung as $user): ?>
            <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
              <td style="padding: 10px 12px; font-weight: bold; color: #64748b;">#<?= $user['id'] ?></td>
              <td style="padding: 10px 12px; font-weight: 700; color: #1e293b;">
                <?= e($user['ho_ten']) ?>
                <?php if ((int)$user['id'] === (int)$u['id']): ?>
                  <span style="font-size: 0.72rem; background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-weight: 600; margin-left: 4px;">Bạn</span>
                <?php endif; ?>
              </td>
              <td style="padding: 10px 12px; font-size: 0.9rem; color: #475569;">
                <code><?= e($user['email']) ?></code>
              </td>
              <td style="padding: 10px 12px; text-align: center;">
                <?php if ($user['vai_tro'] === 'quan_tri'): ?>
                  <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">👑 Quản trị viên (Admin)</span>
                <?php elseif ($user['vai_tro'] === 'chu_sach'): ?>
                  <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">🏪 Chủ sách (Book Owner)</span>
                <?php else: ?>
                  <span style="background: #eff6ff; color: #1e40af; padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">👤 Khách hàng (Customer)</span>
                <?php endif; ?>
              </td>
              <td style="padding: 10px 12px; text-align: center; font-weight: bold; color: <?= $user['so_sach'] > 0 ? '#ea580c' : '#94a3b8' ?>;">
                <?= $user['so_sach'] ?> cuốn
              </td>
              <td style="padding: 10px 12px; text-align: center;">
                <?php if ((int)$user['id'] === (int)$u['id']): ?>
                  <span style="color: #94a3b8; font-size: 0.8rem; font-style: italic;">Đang đăng nhập</span>
                <?php else: ?>
                  <form method="POST" action="quan-tri.php" style="display: flex; gap: 6px; justify-content: center; align-items: center; margin: 0;">
                    <?= csrfField() ?>
                    <input type="hidden" name="doi_vai_tro" value="1">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="vai_tro" style="padding: 4px 6px; font-size: 0.82rem; border: 1px solid #cbd5e1; border-radius: 4px;">
                      <option value="khach_hang" <?= $user['vai_tro'] === 'khach_hang' ? 'selected' : '' ?>>Khách hàng</option>
                      <option value="chu_sach" <?= $user['vai_tro'] === 'chu_sach' ? 'selected' : '' ?>>Chủ sách</option>
                      <option value="quan_tri" <?= $user['vai_tro'] === 'quan_tri' ? 'selected' : '' ?>>Quản trị viên</option>
                    </select>
                    <button type="submit" class="nut nut--vien" style="padding: 3px 8px; font-size: 0.78rem;">Đổi</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
