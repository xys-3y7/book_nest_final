<?php
/**
 * thanhvien/3120224189_yoi/gioithieu.php - Trang cá nhân của Xaiyasith Yoi (Nhóm trưởng)
 * Mã sinh viên: 3120224189 - Lớp: 24CNTT3
 * Chức năng Phần B: 
 *   1. Sổ lưu bút dùng CSDL MySQL (bảng tv_luu_but) có phân trang; chỉ chủ trang đăng nhập mới thấy nút Xóa.
 *   2. Thống kê tổng quan dữ liệu đồ án BookNest (tổng số sách, tổng danh mục, tổng đơn hàng).
 * Cách thử: Gửi lời nhắn mới; sang trang 2; đăng nhập tài khoản yoi@ued.vn (chủ trang) để thấy nút Xóa.
 */

require_once __DIR__ . '/../../inc/config.php';

const MSSV = '3120224189';
$goc = '../../';
$db = db();

// Kiểm tra quyền chủ trang từ bảng thanh_vien
$st = $db->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$st->execute([MSSV]);
$tv = $st->fetch();

$u = nguoiDung();
$laChuTrang = ($u['id'] ?? 0) === ($tv['nguoi_dung_id'] ?? -1);

$loi = [];

// Xử lý gửi lời nhắn hoặc xóa lời nhắn (POST - PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xoa = filter_var($_POST['xoa'] ?? '', FILTER_VALIDATE_INT);

    if ($xoa) {
        if (!$laChuTrang) {
            ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI']]);
            http_response_code(403);
            exit('403 Forbidden — Bạn không có quyền xóa lời nhắn này.');
        }
        // Chỉ chủ trang mới được xóa, và chỉ xóa trên trang của mình (AND mssv = ?)
        $stXoa = $db->prepare('DELETE FROM tv_luu_but WHERE id = ? AND mssv = ?');
        $stXoa->execute([$xoa, MSSV]);
        $_SESSION['flash'] = 'Đã xóa lời nhắn thành công!';
    } else {
        $tenKhach = trim(chuoi($_POST['ten_khach'] ?? ''));
        $loiNhan  = trim(chuoi($_POST['loi_nhan'] ?? ''));

        if ($tenKhach === '' || mb_strlen($tenKhach) > 50) {
            $loi['ten_khach'] = 'Họ tên phải dài từ 1 đến 50 ký tự';
        }
        if ($loiNhan === '' || mb_strlen($loiNhan) > 500) {
            $loi['loi_nhan'] = 'Lời nhắn phải dài từ 1 đến 500 ký tự';
        }

        if (empty($loi)) {
            $stThem = $db->prepare('INSERT INTO tv_luu_but (mssv, ten_khach, loi_nhan) VALUES (?, ?, ?)');
            $stThem->execute([MSSV, $tenKhach, $loiNhan]);
            $_SESSION['flash'] = 'Cảm ơn bạn đã để lại lời nhắn lưu bút!';
        }
    }

    if (empty($loi)) {
        header('Location: gioithieu.php');
        exit;
    }
}

// Phân trang sổ lưu bút: 5 lời nhắn mỗi trang
$stDem = $db->prepare('SELECT COUNT(*) FROM tv_luu_but WHERE mssv = ?');
$stDem->execute([MSSV]);
$tongLuuBut = (int)$stDem->fetchColumn();
$moiTrang = 5;
$soTrang = max(1, (int) ceil($tongLuuBut / $moiTrang));

$hienTai = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'default' => 1]]);
$hienTai = min($hienTai, $soTrang);

$stLb = $db->prepare(
    'SELECT id, ten_khach, loi_nhan, thoi_gian 
     FROM tv_luu_but 
     WHERE mssv = ? 
     ORDER BY thoi_gian DESC, id DESC 
     LIMIT 5 OFFSET ?'
);
$stLb->execute([MSSV, ($hienTai - 1) * $moiTrang]);
$dsLuuBut = $stLb->fetchAll();

// Chức năng 2: Thống kê số liệu đồ án từ CSDL
$tongSach = (int)$db->query('SELECT COUNT(*) FROM san_pham')->fetchColumn();
$tongDm = (int)$db->query('SELECT COUNT(*) FROM danh_muc')->fetchColumn();
$tongDon = (int)$db->query('SELECT COUNT(*) FROM don_hang')->fetchColumn();

$thongBao = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$tieuDe = 'Trang cá nhân - Xaiyasith Yoi';
$trang = 'gioi-thieu';

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 900px; margin: 30px auto;">
  <!-- Thông tin cá nhân -->
  <section style="display: flex; gap: 30px; align-items: center; background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px; flex-wrap: wrap;">
    <img src="<?= $goc ?>images/yoi-avatar.png" alt="Ảnh Xaiyasith Yoi" width="130" height="130" style="border-radius: 50%; object-fit: cover; border: 3px solid #1e3b6d;">
    <div>
      <h1 style="margin: 0; color: #1e3b6d; font-size: 1.8rem;">Xaiyasith Yoi</h1>
      <p style="margin: 5px 0; color: #64748b;">Mã sinh viên: <strong>3120224189</strong> | Lớp: <strong>24CNTT3</strong></p>
      <p style="margin: 5px 0;">GitHub: <a href="https://github.com/xys-3y7" target="_blank" rel="noopener noreferrer" style="color: #0369a1; font-weight: bold;">@xys-3y7 (https://github.com/xys-3y7)</a></p>
      <p style="margin: 5px 0;">Vai trò: <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Nhóm trưởng Nhóm 12</span></p>
      <p style="margin: 5px 0; color: #475569;">Trách nhiệm website: Quản trị CSDL MySQL, kiến trúc PDO, giỏ hàng Session, Wishlist và điều phối chung.</p>
      <div style="margin-top: 10px;">
        <a href="../../gioi-thieu.php" class="nut nut--vien" style="padding: 4px 12px; font-size: 0.85rem;">&larr; Quay lại trang Giới thiệu nhóm</a>
      </div>
      <?php if ($laChuTrang): ?>
        <p style="margin: 8px 0 0 0; color: #16a34a; font-weight: bold;">✓ Bạn đang đăng nhập với tư cách Chủ trang (yoi@ued.vn)</p>
      <?php else: ?>
        <p style="margin: 8px 0 0 0; font-size: 0.85rem; color: #64748b;">(Đăng nhập tài khoản <code>yoi@ued.vn</code> / <code>matkhau123</code> để kích hoạt quyền xóa lưu bút)</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- BẢNG NHIỆM VỤ CHI TIẾT TRONG VIỆC LÀM BÁO CÁO BÀI TẬP 7 (THEO YÊU CẦU) -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h2 style="margin: 0; color: #1e3b6d; font-size: 1.35rem;">📋 Nhiệm vụ &amp; Đóng góp trong Báo cáo Bài tập nhóm số 7 (Bảo mật Web)</h2>
      <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;">Đóng góp: 100%</span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-bottom: 18px;">
      <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #1e3b6d;">
        <h4 style="margin: 0 0 8px 0; color: #1e3b6d;">1. Vai trò chủ trì &amp; Xuất bản</h4>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li>Khởi tạo cấu trúc và phân công nhiệm vụ 4 thành viên.</li>
          <li>Tổng hợp và định dạng báo cáo bản Word (<code>Nhom12_Baitap7.docx</code>) và PDF (<code>Nhom12_Baitap7.pdf</code>).</li>
          <li>Đóng gói mã nguồn nộp bài đạt chuẩn (<code>LTW_BTN7_Nhom12_code.zip</code>).</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #ea580c;">
        <h4 style="margin: 0 0 8px 0; color: #ea580c;">2. Biện pháp bảo mật phụ trách</h4>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Biện pháp 1:</strong> CSRF Token trên 100% 19 form POST và fetch AJAX, chuyển đăng xuất sang POST 405.</li>
          <li><strong>Biện pháp 7:</strong> Giới hạn tần suất đăng nhập (5 lần sai/15 phút -> 429) và ghi nhật ký <code>logs/bao-mat.log</code>.</li>
        </ul>
      </div>
    </div>

    <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.9rem; color: #334155; line-height: 1.6;">
      <strong style="color: #1e3b6d;">3. Nội dung báo cáo biên soạn &amp; Thực thi kiểm thử:</strong>
      <ul style="margin: 6px 0 0 0; padding-left: 18px;">
        <li><strong>Phần A:</strong> Thiết kế Hình 1 (Kiến trúc phòng thủ theo chiều sâu), Bảng 1 (Ma trận bảo mật), Bảng 2 (Nhật ký commit Git), Bảng 4 (Static Audit) và Git Diff Stat.</li>
        <li><strong>Phần B:</strong> Báo cáo an toàn trang cá nhân <code>xaiyasith.php</code>, làm sạch <code>chuoi()</code>, bảo vệ quyền xóa lưu bút (403 Forbidden) và thực hiện 5 ca kiểm thử thực nghiệm.</li>
        <li><strong>Phần C:</strong> Biên soạn lời giải chuyên sâu <strong>Câu C.1</strong> (Phân tích so sánh <code>SameSite=Lax</code> vs CSRF Token và ranh giới với Authorization).</li>
        <li><strong>Kiểm thử tự động:</strong> Lập trình bộ kịch bản 14 ca phủ định tự động trong <code>tests/bao-mat.http</code> và <code>tests/bao-mat.sh</code>.</li>
      </ul>
    </div>
  </section>

  <!-- Thông báo trạng thái -->
  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 6px; margin-bottom: 25px;" aria-live="polite">
      <strong>✓ Thành công:</strong> <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <!-- Chức năng CSDL 2: Thống kê số liệu hệ thống đồ án -->
  <section style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 35px;">
    <h2 style="margin-top: 0; color: #1e3b6d; font-size: 1.3rem;">📊 Thống kê dữ liệu đồ án BookNest (CSDL MySQL)</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 15px;">
      <div style="background: #ffffff; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; text-align: center;">
        <span style="font-size: 2rem; font-weight: bold; color: #1e40af;"><?= $tongSach ?></span>
        <div style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">Tổng số đầu sách</div>
      </div>
      <div style="background: #ffffff; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; text-align: center;">
        <span style="font-size: 2rem; font-weight: bold; color: #059669;"><?= $tongDm ?></span>
        <div style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">Danh mục sách</div>
      </div>
      <div style="background: #ffffff; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; text-align: center;">
        <span style="font-size: 2rem; font-weight: bold; color: #d97706;"><?= $tongDon ?></span>
        <div style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">Tổng đơn đã đặt</div>
      </div>
    </div>
  </section>

  <!-- Chức năng CSDL 1: Sổ lưu bút cá nhân (bảng tv_luu_but) -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
    <h2 style="margin-top: 0; color: #1e3b6d;">✍️ Sổ lưu bút bạn bè (Bảng tv_luu_but - Phân trang)</h2>
    <p style="color: #64748b; font-size: 0.95rem;">Để lại lời chúc hoặc góp ý cho nhóm trưởng Yoi. Dữ liệu lưu trong bảng <code>tv_luu_but</code>.</p>

    <!-- Biểu mẫu gửi lưu bút -->
    <form method="POST" action="gioithieu.php" style="margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 6px; border: 1px solid #e2e8f0;">
      <?= csrfField() ?>
      <div style="margin-bottom: 15px;">
        <label for="ten_khach" style="display: block; font-weight: bold; margin-bottom: 5px;">Họ và tên của bạn <span style="color:#dc2626;">*</span></label>
        <input type="text" id="ten_khach" name="ten_khach" placeholder="Ví dụ: Nguyễn Thị Sinh" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
        <?php if (isset($loi['ten_khach'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['ten_khach']) ?></p>
        <?php endif; ?>
      </div>

      <div style="margin-bottom: 15px;">
        <label for="loi_nhan" style="display: block; font-weight: bold; margin-bottom: 5px;">Lời nhắn lưu bút <span style="color:#dc2626;">*</span></label>
        <textarea id="loi_nhan" name="loi_nhan" rows="3" placeholder="Viết vài dòng chia sẻ cùng Yoi..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;"></textarea>
        <?php if (isset($loi['loi_nhan'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['loi_nhan']) ?></p>
        <?php endif; ?>
      </div>

      <button type="submit" class="nut nut--chinh">Gửi lưu bút ngay</button>
    </form>

    <!-- Danh sách lưu bút -->
    <h3>Các lời nhắn đã nhận (Tổng: <?= $tongLuuBut ?>)</h3>
    <?php if (empty($dsLuuBut)): ?>
      <p style="color: #64748b; font-style: italic;">Chưa có lời nhắn nào. Hãy là người đầu tiên ghi lưu bút nhé!</p>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px;">
        <?php foreach ($dsLuuBut as $lb): ?>
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
            <div>
              <div style="font-weight: bold; color: #1e3b6d;"><?= e($lb['ten_khach']) ?></div>
              <p style="margin: 5px 0 6px 0; color: #334155; line-height: 1.5;"><?= nl2br(e($lb['loi_nhan'])) ?></p>
              <small style="color: #94a3b8;"><?= e($lb['thoi_gian']) ?></small>
            </div>
            <?php if ($laChuTrang): ?>
              <form method="POST" action="gioithieu.php" class="form-xoa-luubut" style="margin: 0;">
                <?= csrfField() ?>
                <input type="hidden" name="xoa" value="<?= $lb['id'] ?>">
                <button type="submit" class="nut nut--vien" style="color: #dc2626; border-color: #fca5a5; padding: 4px 10px; font-size: 0.8rem;">Xóa</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Phân trang lưu bút -->
      <?php if ($soTrang > 1): ?>
        <nav aria-label="Phân trang lưu bút" style="display: flex; gap: 6px; justify-content: center;">
          <?php for ($i = 1; $i <= $soTrang; $i++): ?>
            <a href="gioithieu.php?trang=<?= $i ?>" class="nut <?= $i === $hienTai ? 'nut--chinh' : 'nut--vien' ?>" style="padding: 4px 10px; font-size: 0.85rem;">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
