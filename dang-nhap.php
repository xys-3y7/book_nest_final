<?php
/**
 * dang-nhap.php - Đăng nhập tài khoản với bảo vệ CSRF và chống dò mật khẩu (Rate limiting)
 * Nhóm 12 - Lớp 24CNTT3 - Chương 7: Bảo mật ứng dụng web
 */

require_once __DIR__ . '/inc/config.php';

// Nếu đã đăng nhập -> Chuyển hướng
if (nguoiDung()) {
    header('Location: index.php');
    exit;
}

$loi = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(chuoi($_POST['email'] ?? ''));
    $password = chuoi($_POST['password'] ?? '');

    // Biện pháp 7: Giới hạn đoán mật khẩu (5 lần sai trong 15 phút)
    if (soLanSai($email) >= 5) {
        http_response_code(429); // 429 Too Many Requests
        $loi = 'Sai quá nhiều lần, hãy thử lại sau 15 phút';
    } else {
        $db = db();
        $st = $db->prepare('SELECT id, email, ho_ten, mat_khau, vai_tro FROM nguoi_dung WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();

        if ($u && password_verify($password, $u['mat_khau'])) {
            // Đăng nhập thành công: xóa lịch sử đăng nhập sai của email này
            $db->prepare('DELETE FROM dang_nhap_sai WHERE email = ?')->execute([$email]);

            // Đổi Session ID và thiết lập phiên an toàn
            dangNhap($u['id']);

            // Kiểm tra và băm lại mật khẩu nếu cần
            if (password_needs_rehash($u['mat_khau'], PASSWORD_DEFAULT)) {
                $moiHash = password_hash($password, PASSWORD_DEFAULT);
                $stUp = $db->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?');
                $stUp->execute([$moiHash, $u['id']]);
            }

            $dichDen = $_SESSION['redirect_sau_dang_nhap'] ?? ($u['vai_tro'] === 'quan_tri' ? 'quan-tri.php' : 'index.php');
            unset($_SESSION['redirect_sau_dang_nhap']);

            header('Location: ' . $dichDen);
            exit;
        } else {
            // Ghi nhận lần sai vào CSDL và nhật ký bảo mật logs/bao-mat.log
            ghiLanSai($email);

            // Thông báo lỗi chung không phân biệt sai email hay sai mật khẩu
            $loi = 'Sai email hoặc mật khẩu';
        }
    }
}

$thongBao = $_SESSION['flash'] ?? '';
$thongBaoLoi = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_error']);

$tieuDe = 'Đăng nhập tài khoản';
$trang = 'dang-nhap';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 500px; margin: 40px auto;">
  <div style="background: #ffffff; padding: 35px 30px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <h1 style="text-align: center; margin-top: 0; color: #1e3b6d; font-size: 1.8rem;">Đăng nhập BookNest</h1>
    <p style="text-align: center; color: #64748b; font-size: 0.95rem; margin-bottom: 25px;">
      Đăng nhập để xem lịch sử đơn hàng và trải nghiệm mua sắm an toàn.
    </p>

    <?php if ($thongBao !== ''): ?>
      <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px;" aria-live="polite">
        <?= e($thongBao) ?>
      </div>
    <?php endif; ?>

    <?php if ($thongBaoLoi !== ''): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px;" aria-live="polite">
        <?= e($thongBaoLoi) ?>
      </div>
    <?php endif; ?>

    <?php if ($loi !== ''): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px;" aria-live="assertive">
        <strong>⚠ Lỗi:</strong> <?= e($loi) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="dang-nhap.php" novalidate>
      <?= csrfField() ?>
      
      <!-- Email -->
      <div style="margin-bottom: 18px;">
        <label for="email" style="display: block; font-weight: bold; margin-bottom: 6px;">Địa chỉ Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="admin@ued.vn hoặc lan@ued.vn" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;" required autofocus>
      </div>

      <!-- Mật khẩu -->
      <div style="margin-bottom: 25px;">
        <label for="password" style="display: block; font-weight: bold; margin-bottom: 6px;">Mật khẩu</label>
        <input type="password" id="password" name="password" placeholder="Mật khẩu thử nghiệm: matkhau123" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;" required>
      </div>

      <button type="submit" class="nut nut--chinh" style="width: 100%; padding: 12px; font-size: 1rem;">
        Đăng nhập
      </button>

      <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #f1f5f9; font-size: 0.85rem; color: #64748b; line-height: 1.6;">
        <p style="margin: 0 0 5px 0;"><strong>Tài khoản thử nghiệm sẵn có (Mật khẩu: <code>matkhau123</code>):</strong></p>
        <ul style="margin: 0; padding-left: 20px;">
          <li>Quản trị viên: <code>admin@ued.vn</code> (vào được quan-tri.php)</li>
          <li>Khách hàng: <code>lan@ued.vn</code> hoặc <code>an@ued.vn</code></li>
        </ul>
      </div>

      <p style="text-align: center; margin-top: 20px; font-size: 0.95rem; color: #475569;">
        Chưa có tài khoản? <a href="dang-ky.php" style="color: #1e40af; font-weight: bold;">Đăng ký ngay &rarr;</a>
      </p>
    </form>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
