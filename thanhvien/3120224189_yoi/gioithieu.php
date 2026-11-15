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
        $_SESSION['flash'] = 'Đã xóa lời nhắn thành công khỏi CSDL!';
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

$tieuDe = 'Trang cá nhân - Xaiyasith Yoi | Sổ lưu bút sinh viên';
$trang = 'gioi-thieu';

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 960px; margin: 32px auto; padding: 0 16px;">
  <!-- Thẻ thông tin cá nhân (Profile Hero) -->
  <section style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 28px; overflow: hidden; position: relative;">
    <div style="height: 100px; background: linear-gradient(135deg, #0f2b5c 0%, #1e40af 50%, #0284c7 100%);"></div>
    <div style="padding: 0 28px 24px; position: relative; display: flex; gap: 24px; align-items: flex-end; flex-wrap: wrap; margin-top: -50px;">
      <img src="<?= $goc ?>images/yoi-avatar.png" alt="Ảnh Xaiyasith Yoi" width="120" height="120" style="border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15); background: #ffffff;">
      <div style="flex: 1; min-width: 280px; padding-top: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
          <h1 style="margin: 0; color: #0f2b5c; font-size: 1.85rem; font-weight: 800;">Xaiyasith Yoi</h1>
          <span style="background: #dbeafe; color: #1e40af; font-size: 0.8rem; font-weight: 700; padding: 4px 12px; border-radius: 9999px;">👑 Nhóm trưởng &bull; MSSV: 3120224189</span>
          <span style="background: #ecfdf5; color: #065f46; font-size: 0.8rem; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">Lớp: 24CNTT3</span>
        </div>
        <p style="margin: 6px 0 10px; color: #475569; font-size: 0.95rem; line-height: 1.5;">
          <strong>Trách nhiệm chính:</strong> Quản trị CSDL MySQL, kiến trúc PDO, giỏ hàng Session, Wishlist &amp; điều phối chung đồ án.
        </p>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <a href="https://github.com/xys-3y7" target="_blank" rel="noopener noreferrer" class="github-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #0f172a; color: #ffffff; border-radius: 20px; text-decoration: none; font-size: 0.85rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: xys-3y7</span>
          </a>
          <a href="../../gioi-thieu.php" class="nut nut--vien" style="padding: 5px 14px; font-size: 0.85rem; border-radius: 9999px;">&larr; Trang giới thiệu Nhóm 12</a>
          
          <?php if ($laChuTrang): ?>
            <span style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-size: 0.85rem; font-weight: 700; padding: 5px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;">
              ✓ Đang đăng nhập: Chủ trang (yoi@ued.vn)
            </span>
          <?php else: ?>
            <a href="../../dang-nhap.php?email=yoi@ued.vn" style="background: #fef3c7; border: 1px solid #fde68a; color: #b45309; font-size: 0.85rem; font-weight: 600; padding: 5px 12px; border-radius: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
              🔑 Đăng nhập tài khoản Yoi để thử xóa lưu bút &rarr;
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Nhiệm vụ báo cáo đồ án chi tiết (Theo Báo cáo Bài tập 7) -->
  <section style="background: #ffffff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h2 style="margin: 0; color: #0f2b5c; font-size: 1.3rem;">📋 Bảng phân công nhiệm vụ chi tiết của Xaiyasith Yoi</h2>
      <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-size: 0.82rem; font-weight: 700;">Đóng góp: 100% &bull; Hoàn thành xuất sắc</span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #0f2b5c;">
        <strong style="color: #0f2b5c; font-size: 1rem; display: block; margin-bottom: 8px;">1. Vai trò Chủ trì &amp; Xuất bản:</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li>Khởi tạo kiến trúc dự án BookNest, thiết lập kết nối PDO và quản trị CSDL MySQL.</li>
          <li>Điều phối phân công nhiệm vụ và quản lý kho lưu trữ Git của cả nhóm.</li>
          <li>Biên soạn bộ kịch bản kiểm thử an ninh 14 ca tự động trong <code>tests/bao-mat.*</code>.</li>
          <li>Chủ trì tổng hợp và định dạng báo cáo hoàn chỉnh bản Word (<code>.docx</code>) và PDF (<code>.pdf</code>).</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #0284c7;">
        <strong style="color: #0284c7; font-size: 1rem; display: block; margin-bottom: 8px;">2. Biện pháp bảo mật đảm nhiệm (Chương 7):</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Biện pháp 1 (CSRF Token):</strong> Triển khai mã CSRF Token tập trung qua <code>inc/csrf.php</code>, bảo vệ 100% 19 form POST và fetch AJAX, chuyển đăng xuất sang POST 405.</li>
          <li><strong>Biện pháp 7 (Chống Brute-force &amp; Log):</strong> Bảng CSDL <code>dang_nhap_sai</code>, giới hạn 5 lần sai/15 phút (mã 429), xây dựng hàm <code>ghiLogBaoMat()</code> vào <code>logs/bao-mat.log</code>.</li>
          <li><strong>Thảo luận C.1:</strong> Biên soạn phân tích so sánh chuyên sâu <code>SameSite=Lax</code> vs CSRF Token.</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #16a34a;">
        <strong style="color: #16a34a; font-size: 1rem; display: block; margin-bottom: 8px;">3. Chức năng CSDL riêng Phần B:</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Sổ lưu bút sinh viên:</strong> Bảng <code>tv_luu_but</code> có phân trang 5 mục/trang.</li>
          <li><strong>Kiểm soát đầu vào &amp; XSS:</strong> Làm sạch bằng <code>chuoi()</code> và thoát ký tự nguy hiểm với <code>e()</code>.</li>
          <li><strong>Phân quyền xóa chủ trang:</strong> Chỉ tài khoản Yoi mới xóa được lưu bút (ràng buộc <code>AND mssv = ?</code>, từ chối 403 Forbidden nếu trái quyền).</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- Thông báo trạng thái -->
  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 8px; margin-bottom: 24px; font-weight: 500;" aria-live="polite">
      ✓ <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <!-- Chức năng Thống kê CSDL tổng quan đồ án -->
  <section style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <h2 style="margin: 0 0 16px; color: #0f2b5c; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
      <span>📊</span> Thống kê dữ liệu CSDL BookNest (Truy vấn PDO trực tiếp)
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
      <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 18px; border-radius: 10px; text-align: center;">
        <span style="font-size: 2.2rem; font-weight: 800; color: #166534; line-height: 1;"><?= $tongSach ?></span>
        <div style="color: #15803d; font-size: 0.9rem; font-weight: 600; margin-top: 6px;">📚 Đầu sách trong kho</div>
      </div>
      <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 18px; border-radius: 10px; text-align: center;">
        <span style="font-size: 2.2rem; font-weight: 800; color: #1e40af; line-height: 1;"><?= $tongDm ?></span>
        <div style="color: #1d4ed8; font-size: 0.9rem; font-weight: 600; margin-top: 6px;">📁 Chuyên ngành / Danh mục</div>
      </div>
      <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 18px; border-radius: 10px; text-align: center;">
        <span style="font-size: 2.2rem; font-weight: 800; color: #b45309; line-height: 1;"><?= $tongDon ?></span>
        <div style="color: #d97706; font-size: 0.9rem; font-weight: 600; margin-top: 6px;">📦 Đơn hàng đã ghi nhận</div>
      </div>
    </div>
  </section>

  <!-- Chức năng CSDL 1: Sổ lưu bút sinh viên (Bảng tv_luu_but) -->
  <section style="background: #ffffff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
      <div>
        <h2 style="margin: 0; color: #0f2b5c; font-size: 1.35rem; display: flex; align-items: center; gap: 8px;">
          <span>✍️</span> Sổ lưu bút sinh viên (Bảng CSDL: <code>tv_luu_but</code>)
        </h2>
        <p style="margin: 4px 0 0; color: #64748b; font-size: 0.9rem;">
          Lưu trữ lời nhắn của bạn bè &amp; khách ghé thăm. Có phân trang 5 mục/trang, chống CSRF và chỉ chủ trang mới có quyền xóa.
        </p>
      </div>
      <span style="background: #f1f5f9; color: #334155; font-size: 0.85rem; font-weight: 700; padding: 4px 12px; border-radius: 20px;">
        Tổng: <?= $tongLuuBut ?> lời nhắn
      </span>
    </div>

    <!-- Biểu mẫu gửi lưu bút mới -->
    <form method="POST" action="gioithieu.php" style="background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 28px;">
      <?= csrfField() ?>
      <div style="margin-bottom: 14px;">
        <label for="ten_khach" style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
          Họ và tên của bạn <span style="color: #ef4444;">*</span>
        </label>
        <input type="text" id="ten_khach" name="ten_khach" placeholder="Ví dụ: Nguyễn Thị Sinh, Phommaket Hatsady..." required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem;">
        <?php if (isset($loi['ten_khach'])): ?>
          <p style="color: #ef4444; font-size: 0.85rem; margin: 4px 0 0;"><?= e($loi['ten_khach']) ?></p>
        <?php endif; ?>
      </div>

      <div style="margin-bottom: 16px;">
        <label for="loi_nhan" style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
          Lời nhắn lưu bút <span style="color: #ef4444;">*</span>
        </label>
        <textarea id="loi_nhan" name="loi_nhan" rows="3" placeholder="Viết vài dòng chia sẻ, động viên hoặc góp ý cho Yoi..." required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; line-height: 1.5;"></textarea>
        <?php if (isset($loi['loi_nhan'])): ?>
          <p style="color: #ef4444; font-size: 0.85rem; margin: 4px 0 0;"><?= e($loi['loi_nhan']) ?></p>
        <?php endif; ?>
      </div>

      <button type="submit" class="nut nut--chinh" style="padding: 10px 22px; font-size: 0.95rem; border-radius: 6px;">
        ✉️ Gửi lời nhắn lưu bút ngay
      </button>
    </form>

    <!-- Danh sách lời nhắn -->
    <h3 style="color: #1e293b; font-size: 1.15rem; margin: 0 0 16px;">
      Danh sách lời nhắn đã nhận (Trang <?= $hienTai ?> / <?= $soTrang ?>)
    </h3>

    <?php if (empty($dsLuuBut)): ?>
      <div style="text-align: center; padding: 30px; background: #f8fafc; border-radius: 8px; color: #64748b;">
        <p style="margin: 0; font-style: italic;">Chưa có lời nhắn nào trên trang này. Hãy là người đầu tiên để lại lời chúc nhé!</p>
      </div>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;">
        <?php foreach ($dsLuuBut as $lb): ?>
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
            <div style="display: flex; gap: 14px; align-items: flex-start; flex: 1;">
              <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #0284c7 0%, #1e40af 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; flex-shrink: 0;">
                <?= e(mb_strtoupper(mb_substr($lb['ten_khach'], 0, 1))) ?>
              </div>
              <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                  <strong style="color: #0f2b5c; font-size: 1rem;"><?= e($lb['ten_khach']) ?></strong>
                  <span style="color: #94a3b8; font-size: 0.8rem;">&bull; <?= e($lb['thoi_gian']) ?></span>
                </div>
                <p style="margin: 6px 0 0; color: #334155; font-size: 0.95rem; line-height: 1.55;">
                  <?= nl2br(e($lb['loi_nhan'])) ?>
                </p>
              </div>
            </div>

            <?php if ($laChuTrang): ?>
              <form method="POST" action="gioithieu.php" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lời nhắn này?');">
                <?= csrfField() ?>
                <input type="hidden" name="xoa" value="<?= $lb['id'] ?>">
                <button type="submit" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 5px 12px; border-radius: 6px; font-size: 0.8rem; cursor: pointer; font-weight: 600;">
                  🗑️ Xóa
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Phân trang -->
      <?php if ($soTrang > 1): ?>
        <nav aria-label="Phân trang lưu bút" style="display: flex; gap: 8px; justify-content: center; align-items: center; margin-top: 20px;">
          <?php for ($i = 1; $i <= $soTrang; $i++): ?>
            <a href="gioithieu.php?trang=<?= $i ?>" class="nut <?= $i === $hienTai ? 'nut--chinh' : 'nut--vien' ?>" style="min-width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-weight: 600; text-decoration: none;">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
