<?php
/**
 * dang-ky.php - Đăng ký tài khoản người dùng mới vào bảng nguoi_dung MySQL
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 4 (Bảng 1): password_hash, email UNIQUE + bắt lỗi 1062, không nhận vai_tro từ form
 */

require_once __DIR__ . '/inc/config.php';

// Nếu đã đăng nhập thì về trang chủ
if (nguoiDung()) {
    header('Location: index.php');
    exit;
}

$du = [
    'email'    => '',
    'ho_ten'   => '',
    'mat_khau' => '',
];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['email']    = trim(chuoi($_POST['email'] ?? ''));
    $du['ho_ten']   = trim(chuoi($_POST['ho_ten'] ?? ''));
    $du['mat_khau'] = chuoi($_POST['mat_khau'] ?? '');

    // Kiểm tra tính hợp lệ dữ liệu đăng ký
    if ($du['ho_ten'] === '' || mb_strlen($du['ho_ten']) < 2 || mb_strlen($du['ho_ten']) > 100) {
        $loi['ho_ten'] = 'Họ và tên phải có độ dài từ 2 đến 100 ký tự';
    }

    if ($du['email'] === '') {
        $loi['email'] = 'Vui lòng nhập địa chỉ email';
    } elseif (!filter_var($du['email'], FILTER_VALIDATE_EMAIL)) {
        $loi['email'] = 'Địa chỉ email không đúng định dạng';
    }

    if ($du['mat_khau'] === '' || mb_strlen($du['mat_khau']) < 8) {
        $loi['mat_khau'] = 'Mật khẩu phải chứa ít nhất 8 ký tự';
    }

    // BẢO MẬT: Bỏ qua hoàn toàn ô vai_tro nếu kẻ xấu cố tình truyền qua devtools/curl
    // Tất cả tài khoản tự đăng ký luôn có vai_tro = 'khach_hang'
    $vaiTro = 'khach_hang';

    if (empty($loi)) {
        $db = db();
        try {
            // Kiểm tra email trùng lặp (không phân biệt chữ hoa thường)
            $stCheck = $db->prepare('SELECT id FROM nguoi_dung WHERE LOWER(email) = LOWER(?)');
            $stCheck->execute([$du['email']]);
            if ($stCheck->fetch()) {
                $loi['email'] = 'Địa chỉ email này đã được đăng ký trên hệ thống';
            } else {
                // Băm mật khẩu bằng thuật toán BCRYPT mặc định của PHP 8
                $hash = password_hash($du['mat_khau'], PASSWORD_DEFAULT);

                $st = $db->prepare('INSERT INTO nguoi_dung (email, ho_ten, mat_khau, vai_tro) VALUES (?, ?, ?, ?)');
                $st->execute([$du['email'], $du['ho_ten'], $hash, $vaiTro]);

                $_SESSION['flash'] = 'Đăng ký tài khoản thành công! Vui lòng đăng nhập để bắt đầu.';
                header('Location: dang-nhap.php');
                exit;
            }
        } catch (PDOException $e) {
            // Bắt lỗi trùng lặp UNIQUE khóa chính/email 1062
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                $loi['email'] = 'Địa chỉ email này đã được đăng ký trên hệ thống (Mã lỗi 1062)';
            } else {
                throw $e;
            }
        }
    }
}

$tieuDe = 'Đăng ký tài khoản';
$trang = 'dang-ky';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 520px; margin: 40px auto;">
  <div style="background: #ffffff; padding: 35px 30px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <h1 style="text-align: center; margin-top: 0; color: #1e3b6d; font-size: 1.8rem;">Đăng ký tài khoản BookNest</h1>
    <p style="text-align: center; color: #64748b; font-size: 0.95rem; margin-bottom: 25px;">
      Tạo tài khoản để lưu giỏ hàng, đặt mua sách và theo dõi đơn hàng của bạn.
    </p>

    <form method="POST" action="dang-ky.php" novalidate>
      <?= csrfField() ?>
      
      <!-- Họ và tên -->
      <div style="margin-bottom: 18px;">
        <label for="ho_ten" style="display: block; font-weight: bold; margin-bottom: 6px;">Họ và tên của bạn <span style="color:#dc2626;">*</span></label>
        <input type="text" id="ho_ten" name="ho_ten" value="<?= e($du['ho_ten']) ?>" placeholder="Ví dụ: Trần Văn An" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['ho_ten']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
        <?php if (isset($loi['ho_ten'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['ho_ten']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Email -->
      <div style="margin-bottom: 18px;">
        <label for="email" style="display: block; font-weight: bold; margin-bottom: 6px;">Địa chỉ Email <span style="color:#dc2626;">*</span></label>
        <input type="email" id="email" name="email" value="<?= e($du['email']) ?>" placeholder="name@ued.vn" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['email']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
        <?php if (isset($loi['email'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['email']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Mật khẩu -->
      <div style="margin-bottom: 25px;">
        <label for="mat_khau" style="display: block; font-weight: bold; margin-bottom: 6px;">Mật khẩu (Tối thiểu 8 ký tự) <span style="color:#dc2626;">*</span></label>
        <input type="password" id="mat_khau" name="mat_khau" placeholder="Nhập mật khẩu an toàn..." style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['mat_khau']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
        <?php if (isset($loi['mat_khau'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['mat_khau']) ?></p>
        <?php endif; ?>
      </div>

      <button type="submit" class="nut nut--chinh" style="width: 100%; padding: 12px; font-size: 1rem;">
        Đăng ký tài khoản ngay
      </button>

      <p style="text-align: center; margin-top: 20px; font-size: 0.95rem; color: #475569;">
        Đã có tài khoản? <a href="dang-nhap.php" style="color: #1e40af; font-weight: bold;">Đăng nhập ngay &rarr;</a>
      </p>
    </form>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
