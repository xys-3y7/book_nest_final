<?php
/**
 * thanhvien/3120224181_hatsady/gioithieu.php - Trang cá nhân của Phommaket Hatsady
 * Mã sinh viên: 3120224181 - Lớp: 24CNTT3
 * Chức năng Phần B:
 *   1. Danh sách dự án CNTT cá nhân từ bảng tv_du_an: tìm kiếm từ khóa an toàn, lọc theo công nghệ qua prepared statements.
 *   2. Biểu mẫu thêm dự án mới và nút xóa dự án vào CSDL MySQL (chỉ chủ trang đăng nhập mới được thực hiện).
 * Cách thử: Gõ từ khóa tìm kiếm dự án; đăng nhập hatsady@ued.vn / matkhau123 để thêm hoặc xóa dự án.
 */

require_once __DIR__ . '/../../inc/config.php';

const MSSV = '3120224181';
$goc = '../../';
$db = db();

$st = $db->prepare('SELECT ho_ten, nguoi_dung_id FROM thanh_vien WHERE mssv = ?');
$st->execute([MSSV]);
$tv = $st->fetch();

$u = nguoiDung();
$laChuTrang = ($u['id'] ?? 0) === ($tv['nguoi_dung_id'] ?? -1);

$loi = [];

// Xử lý thêm hoặc xóa dự án (chỉ chủ trang có quyền)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$laChuTrang) {
        ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        exit('403 Forbidden — Bạn không có quyền thao tác trên trang này.');
    }

    $xoaId = filter_var($_POST['xoa_id'] ?? '', FILTER_VALIDATE_INT);
    if ($xoaId) {
        $stDel = $db->prepare('DELETE FROM tv_du_an WHERE id = ? AND mssv = ?');
        $stDel->execute([$xoaId, MSSV]);
        $_SESSION['flash'] = 'Đã xóa dự án thành công khỏi CSDL!';
        header('Location: gioithieu.php');
        exit;
    }

    $tenDuAn = trim(chuoi($_POST['ten_du_an'] ?? ''));
    $congNghe = trim(chuoi($_POST['cong_nghe'] ?? ''));
    $moTa = trim(chuoi($_POST['mo_ta'] ?? ''));
    $link = trim(chuoi($_POST['link_github'] ?? ''));

    if ($tenDuAn === '' || mb_strlen($tenDuAn) > 100) {
        $loi['ten_du_an'] = 'Tên dự án dài từ 1 đến 100 ký tự';
    }
    if ($congNghe === '' || mb_strlen($congNghe) > 100) {
        $loi['cong_nghe'] = 'Công nghệ sử dụng dài từ 1 đến 100 ký tự';
    }
    if ($moTa === '') {
        $loi['mo_ta'] = 'Vui lòng nhập mô tả tóm tắt dự án';
    }

    if (empty($loi)) {
        $stAdd = $db->prepare('INSERT INTO tv_du_an (mssv, ten_du_an, cong_nghe, mo_ta, link_github) VALUES (?, ?, ?, ?, ?)');
        $stAdd->execute([MSSV, $tenDuAn, $congNghe, $moTa, $link ?: null]);
        $_SESSION['flash'] = 'Đã thêm dự án công nghệ mới thành công!';
        header('Location: gioithieu.php');
        exit;
    }
}

// Tìm kiếm và lọc dự án an toàn với chuoi()
$q = trim(chuoi($_GET['q'] ?? ''));
$sql = 'SELECT * FROM tv_du_an WHERE mssv = ?';
$params = [MSSV];

if ($q !== '') {
    $sql .= ' AND (ten_du_an LIKE ? OR cong_nghe LIKE ?)';
    $params[] = '%' . addcslashes($q, '%_\\') . '%';
    $params[] = '%' . addcslashes($q, '%_\\') . '%';
}
$sql .= ' ORDER BY id DESC';

$stDs = $db->prepare($sql);
$stDs->execute($params);
$dsDuAn = $stDs->fetchAll();

$tongDuAn = (int)$db->query("SELECT COUNT(*) FROM tv_du_an WHERE mssv = '" . MSSV . "'")->fetchColumn();

$thongBao = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$tieuDe = 'Trang cá nhân - Phommaket Hatsady | Dự án công nghệ';
$trang = 'gioi-thieu';

require __DIR__ . '/../../inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 960px; margin: 32px auto; padding: 0 16px;">
  <!-- Thẻ thông tin cá nhân (Profile Hero) -->
  <section style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 28px; overflow: hidden; position: relative;">
    <div style="height: 100px; background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 50%, #0d9488 100%);"></div>
    <div style="padding: 0 28px 24px; position: relative; display: flex; gap: 24px; align-items: flex-end; flex-wrap: wrap; margin-top: -50px;">
      <img src="<?= $goc ?>images/hatsady-avatar.png" alt="Ảnh Phommaket Hatsady" width="120" height="120" style="border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15); background: #ffffff;">
      <div style="flex: 1; min-width: 280px; padding-top: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
          <h1 style="margin: 0; color: #0f2b5c; font-size: 1.85rem; font-weight: 800;">Phommaket Hatsady</h1>
          <span style="background: #e0f2fe; color: #0369a1; font-size: 0.8rem; font-weight: 700; padding: 4px 12px; border-radius: 9999px;">💻 Thành viên &bull; MSSV: 3120224181</span>
          <span style="background: #ecfdf5; color: #065f46; font-size: 0.8rem; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">Lớp: 24CNTT3</span>
        </div>
        <p style="margin: 6px 0 10px; color: #475569; font-size: 0.95rem; line-height: 1.5;">
          <strong>Trách nhiệm chính:</strong> Trang danh mục sách <code>danh-sach.php</code>, bộ lọc an toàn, phân trang, cấu hình máy chủ Apache <code>.htaccess</code>.
        </p>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <a href="https://github.com/daimobile33076-eng" target="_blank" rel="noopener noreferrer" class="github-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #0f172a; color: #ffffff; border-radius: 20px; text-decoration: none; font-size: 0.85rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: daimobile33076-eng</span>
          </a>
          <a href="../../gioi-thieu.php" class="nut nut--vien" style="padding: 5px 14px; font-size: 0.85rem; border-radius: 9999px;">&larr; Trang giới thiệu Nhóm 12</a>
          
          <?php if ($laChuTrang): ?>
            <span style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-size: 0.85rem; font-weight: 700; padding: 5px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;">
              ✓ Đang đăng nhập: Chủ trang (hatsady@ued.vn)
            </span>
          <?php else: ?>
            <a href="../../dang-nhap.php?email=hatsady@ued.vn" style="background: #fef3c7; border: 1px solid #fde68a; color: #b45309; font-size: 0.85rem; font-weight: 600; padding: 5px 12px; border-radius: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
              🔑 Đăng nhập tài khoản Hatsady để thêm / xóa dự án &rarr;
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Nhiệm vụ báo cáo đồ án chi tiết (Theo Báo cáo Bài tập 7) -->
  <section style="background: #ffffff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h2 style="margin: 0; color: #0f2b5c; font-size: 1.3rem;">📋 Bảng phân công nhiệm vụ chi tiết của Phommaket Hatsady</h2>
      <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-size: 0.82rem; font-weight: 700;">Đóng góp: 100% &bull; Hoàn thành xuất sắc</span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #1e3a8a;">
        <strong style="color: #1e3a8a; font-size: 1rem; display: block; margin-bottom: 8px;">1. Vai trò Lập trình Giao diện &amp; Danh mục:</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li>Phát triển trang danh mục sách <code>danh-sach.php</code> với bộ lọc chuyên ngành và tìm kiếm từ khóa an toàn.</li>
          <li>Xây dựng thuật toán phân trang dữ liệu theo từng trang trên CSDL MySQL.</li>
          <li>Đồng phát triển logic dịch vụ Giỏ hàng (thêm, cập nhật số lượng, xóa sản phẩm).</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #0284c7;">
        <strong style="color: #0284c7; font-size: 1rem; display: block; margin-bottom: 8px;">2. Biện pháp bảo mật đảm nhiệm (Chương 7):</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Biện pháp 3 (Tiêu đề bảo mật &amp; CSP):</strong> Thiết lập Content-Security-Policy <code>script-src 'self'</code>, loại bỏ <code>X-Powered-By</code>, thêm <code>nosniff</code>, Console 0 lỗi vi phạm.</li>
          <li><strong>Biện pháp 5 (Bảo vệ Apache .htaccess):</strong> Cấu hình <code>.htaccess</code> chặn liệt kê thư mục (<code>-Indexes</code>), chặn tải file nhạy cảm (<code>.env</code>, <code>.git</code>, <code>.sql</code>, <code>.log</code>); cấm chạy mã PHP trong <code>uploads/</code>.</li>
          <li><strong>Thảo luận C.3:</strong> Phân tích nguy cơ lộ file cấu hình máy chủ web và so sánh bảo vệ tầng ứng dụng vs tầng máy chủ web.</li>
        </ul>
      </div>

      <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border-left: 4px solid #0d9488;">
        <strong style="color: #0d9488; font-size: 1rem; display: block; margin-bottom: 8px;">3. Chức năng CSDL riêng Phần B:</strong>
        <ul style="margin: 0; padding-left: 18px; font-size: 0.9rem; color: #334155; line-height: 1.6;">
          <li><strong>Quản lý dự án công nghệ:</strong> Bảng <code>tv_du_an</code> hiển thị portfolio dự án CNTT cá nhân.</li>
          <li><strong>Tìm kiếm đa tiêu chí:</strong> Lọc theo tên dự án hoặc công nghệ sử dụng, chống SQL Injection qua Prepared Statements.</li>
          <li><strong>Phân quyền chủ trang:</strong> Chỉ tài khoản Hatsady mới được thêm dự án mới và xóa dự án trên CSDL (403 Forbidden nếu trái quyền).</li>
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

  <!-- Chức năng CSDL 2: Biểu mẫu thêm dự án công nghệ mới (chỉ dành cho chủ trang) -->
  <?php if ($laChuTrang): ?>
    <section style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 24px; border-radius: 12px; margin-bottom: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
      <h2 style="margin: 0 0 6px; color: #166534; font-size: 1.25rem; display: flex; align-items: center; gap: 8px;">
        <span>🚀</span> Thêm dự án công nghệ mới vào CSDL (Quyền Chủ trang)
      </h2>
      <p style="margin: 0 0 16px; color: #15803d; font-size: 0.9rem;">
        Dữ liệu sẽ được lưu an toàn vào bảng <code>tv_du_an</code> qua Prepared Statement.
      </p>

      <form method="POST" action="gioithieu.php" style="background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #bbf7d0;">
        <?= csrfField() ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 14px;">
          <div>
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
              Tên dự án <span style="color: #ef4444;">*</span>
            </label>
            <input type="text" name="ten_du_an" placeholder="Ví dụ: Cổng Quản lý Đồ án Tốt nghiệp" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            <?php if (isset($loi['ten_du_an'])): ?>
              <p style="color: #ef4444; font-size: 0.85rem; margin: 4px 0 0;"><?= e($loi['ten_du_an']) ?></p>
            <?php endif; ?>
          </div>
          <div>
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
              Công nghệ sử dụng <span style="color: #ef4444;">*</span>
            </label>
            <input type="text" name="cong_nghe" placeholder="Ví dụ: React, Node.js, PostgreSQL" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            <?php if (isset($loi['cong_nghe'])): ?>
              <p style="color: #ef4444; font-size: 0.85rem; margin: 4px 0 0;"><?= e($loi['cong_nghe']) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
            Mô tả tóm tắt dự án <span style="color: #ef4444;">*</span>
          </label>
          <textarea name="mo_ta" rows="2" placeholder="Tóm tắt chức năng, kiến trúc hoặc giải thuật nổi bật..." required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; line-height: 1.5;"></textarea>
          <?php if (isset($loi['mo_ta'])): ?>
            <p style="color: #ef4444; font-size: 0.85rem; margin: 4px 0 0;"><?= e($loi['mo_ta']) ?></p>
          <?php endif; ?>
        </div>

        <div style="margin-bottom: 18px;">
          <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 0.9rem;">
            Đường dẫn GitHub Repository (Tùy chọn)
          </label>
          <input type="url" name="link_github" placeholder="https://github.com/daimobile33076-eng/..." style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
        </div>

        <button type="submit" class="nut nut--chinh" style="padding: 10px 22px; font-size: 0.95rem; border-radius: 6px; background: #166534; border-color: #166534;">
          💾 Lưu dự án vào CSDL
        </button>
      </form>
    </section>
  <?php endif; ?>

  <!-- Chức năng CSDL 1: Danh mục dự án công nghệ kèm tìm kiếm -->
  <section style="background: #ffffff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 22px;">
      <div>
        <h2 style="margin: 0; color: #0f2b5c; font-size: 1.35rem; display: flex; align-items: center; gap: 8px;">
          <span>💻</span> Danh mục dự án công nghệ (Bảng CSDL: <code>tv_du_an</code>)
        </h2>
        <p style="margin: 4px 0 0; color: #64748b; font-size: 0.9rem;">
          Hiển thị các sản phẩm phần mềm đã thực hiện. Đã có <?= $tongDuAn ?> dự án trong CSDL.
        </p>
      </div>

      <!-- Khung tìm kiếm an toàn -->
      <form method="GET" action="gioithieu.php" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Tìm tên hoặc công nghệ..." style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; min-width: 220px;">
        <button type="submit" class="nut nut--chinh" style="padding: 8px 16px; font-size: 0.9rem; border-radius: 6px;">
          🔍 Tìm
        </button>
        <?php if ($q !== ''): ?>
          <a href="gioithieu.php" class="nut nut--vien" style="padding: 8px 14px; font-size: 0.9rem; border-radius: 6px; text-decoration: none;">
            Làm mới
          </a>
        <?php endif; ?>
      </form>
    </div>

    <?php if ($q !== ''): ?>
      <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 8px 14px; border-radius: 6px; font-size: 0.9rem; margin-bottom: 20px;">
        Đang lọc theo từ khóa: <strong>"<?= e($q) ?>"</strong> &bull; Tìm thấy <strong><?= count($dsDuAn) ?></strong> kết quả.
      </div>
    <?php endif; ?>

    <?php if (empty($dsDuAn)): ?>
      <div style="text-align: center; padding: 36px 20px; background: #f8fafc; border-radius: 8px; color: #64748b;">
        <p style="margin: 0; font-style: italic;">Không tìm thấy dự án nào phù hợp với yêu cầu tìm kiếm.</p>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <?php foreach ($dsDuAn as $da): ?>
          <article style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s; position: relative;">
            <div>
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                <h3 style="margin: 0; color: #1e40af; font-size: 1.15rem; font-weight: 700; line-height: 1.4;">
                  <?= e($da['ten_du_an']) ?>
                </h3>
                <?php if ($laChuTrang): ?>
                  <form method="POST" action="gioithieu.php" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dự án này khỏi CSDL?');">
                    <?= csrfField() ?>
                    <input type="hidden" name="xoa_id" value="<?= $da['id'] ?>">
                    <button type="submit" title="Xóa dự án" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; cursor: pointer; font-weight: 600;">
                      🗑️ Xóa
                    </button>
                  </form>
                <?php endif; ?>
              </div>

              <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                <?php 
                  $tags = array_map('trim', explode(',', $da['cong_nghe']));
                  foreach ($tags as $tag):
                ?>
                  <span style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                    <?= e($tag) ?>
                  </span>
                <?php endforeach; ?>
              </div>

              <p style="margin: 0 0 16px; color: #334155; font-size: 0.92rem; line-height: 1.55;">
                <?= nl2br(e($da['mo_ta'])) ?>
              </p>
            </div>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 8px;">
              <?php if (!empty($da['link_github'])): ?>
                <a href="<?= e($da['link_github']) ?>" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 6px; color: #1e40af; font-weight: 600; font-size: 0.85rem; text-decoration: none;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                  <span>Xem mã nguồn trên GitHub &rarr;</span>
                </a>
              <?php else: ?>
                <span style="color: #94a3b8; font-size: 0.82rem; font-style: italic;">Mã nguồn nội bộ</span>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
