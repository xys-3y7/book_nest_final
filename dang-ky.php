<?php
/**
 * dang-ky.php - Register new user account in MySQL nguoi_dung table
 * Team 12 - Class 24CNTT3 - Security & User Role Separation
 */

require_once __DIR__ . '/inc/config.php';

// Redirect if already logged in
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

    // Validate registration input
    if ($du['ho_ten'] === '' || mb_strlen($du['ho_ten']) < 2 || mb_strlen($du['ho_ten']) > 100) {
        $loi['ho_ten'] = 'Full name must be between 2 and 100 characters.';
    }

    if ($du['email'] === '') {
        $loi['email'] = 'Please enter your email address.';
    } elseif (!filter_var($du['email'], FILTER_VALIDATE_EMAIL)) {
        $loi['email'] = 'Invalid email address format.';
    }

    if ($du['mat_khau'] === '' || mb_strlen($du['mat_khau']) < 8) {
        $loi['mat_khau'] = 'Password must be at least 8 characters.';
    }

    // Security whitelist: allow only 'khach_hang' (Customer) or 'chu_sach' (Seller)
    $du['vai_tro'] = chuoi($_POST['vai_tro'] ?? 'khach_hang');
    $vaiTro = ($du['vai_tro'] === 'chu_sach') ? 'chu_sach' : 'khach_hang';

    if (empty($loi)) {
        $db = db();
        try {
            // Check for existing email (case-insensitive)
            $stCheck = $db->prepare('SELECT id FROM nguoi_dung WHERE LOWER(email) = LOWER(?)');
            $stCheck->execute([$du['email']]);
            if ($stCheck->fetch()) {
                $loi['email'] = 'This email address is already registered in our system.';
            } else {
                // Hash password using BCRYPT
                $hash = password_hash($du['mat_khau'], PASSWORD_DEFAULT);

                $st = $db->prepare('INSERT INTO nguoi_dung (email, ho_ten, mat_khau, vai_tro) VALUES (?, ?, ?, ?)');
                $st->execute([$du['email'], $du['ho_ten'], $hash, $vaiTro]);

                $_SESSION['flash'] = 'Account registered successfully! Please sign in to get started.';
                header('Location: dang-nhap.php');
                exit;
            }
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                $loi['email'] = 'This email address is already registered in our system.';
            } else {
                throw $e;
            }
        }
    }
}

$tieuDe = 'Create Account';
$trang = 'dang-ky';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 520px; margin: 40px auto;">
  <div style="background: #ffffff; padding: 35px 30px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <h1 style="text-align: center; margin-top: 0; color: #1e3b6d; font-size: 1.8rem;">Create BookNest Account</h1>
    <p style="text-align: center; color: #64748b; font-size: 0.95rem; margin-bottom: 25px;">
      Sign up to save items to your wishlist, manage your cart, and track orders easily.
    </p>

    <form method="POST" action="dang-ky.php" novalidate>
      <?= csrfField() ?>
      
      <!-- Full Name -->
      <div style="margin-bottom: 18px;">
        <label for="ho_ten" style="display: block; font-weight: bold; margin-bottom: 6px;">Full Name <span style="color:#dc2626;">*</span></label>
        <input type="text" id="ho_ten" name="ho_ten" value="<?= e($du['ho_ten']) ?>" placeholder="e.g. John Doe" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['ho_ten']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
        <?php if (isset($loi['ho_ten'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['ho_ten']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Email -->
      <div style="margin-bottom: 18px;">
        <label for="email" style="display: block; font-weight: bold; margin-bottom: 6px;">Email Address <span style="color:#dc2626;">*</span></label>
        <input type="email" id="email" name="email" value="<?= e($du['email']) ?>" placeholder="name@example.com" style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['email']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
        <?php if (isset($loi['email'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['email']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Password -->
      <div style="margin-bottom: 22px;">
        <label for="mat_khau" style="display: block; font-weight: bold; margin-bottom: 6px;">Password (At least 8 characters) <span style="color:#dc2626;">*</span></label>
        <input type="password" id="mat_khau" name="mat_khau" placeholder="Enter a secure password..." style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['mat_khau']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;">
        <?php if (isset($loi['mat_khau'])): ?>
          <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['mat_khau']) ?></p>
        <?php endif; ?>
      </div>

      <!-- User Role (Customer vs Seller) -->
      <div style="margin-bottom: 25px;">
        <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b; font-size: 0.95rem;">Which role would you like to register? <span style="color:#dc2626;">*</span></label>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <label style="display: block; cursor: pointer; border: 2px solid <?= ($du['vai_tro'] ?? 'khach_hang') === 'khach_hang' ? '#10b981' : '#e2e8f0' ?>; background: <?= ($du['vai_tro'] ?? 'khach_hang') === 'khach_hang' ? '#f0fdf4' : '#ffffff' ?>; border-radius: 10px; padding: 14px 12px; transition: all 0.2s ease;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
              <input type="radio" name="vai_tro" value="khach_hang" <?= ($du['vai_tro'] ?? 'khach_hang') === 'khach_hang' ? 'checked' : '' ?> onchange="this.form.querySelectorAll('input[name=vai_tro]').forEach(r => { r.closest('label').style.borderColor = r.checked ? (r.value === 'khach_hang' ? '#10b981' : '#f59e0b') : '#e2e8f0'; r.closest('label').style.background = r.checked ? (r.value === 'khach_hang' ? '#f0fdf4' : '#fffbeb') : '#ffffff'; });">
              <strong style="color: #065f46; font-size: 0.95rem;">👤 Customer</strong>
            </div>
            <div style="font-size: 0.8rem; color: #047857; margin-left: 22px;">Buyer &bull; Purchase textbooks</div>
          </label>
          <label style="display: block; cursor: pointer; border: 2px solid <?= ($du['vai_tro'] ?? '') === 'chu_sach' ? '#f59e0b' : '#e2e8f0' ?>; background: <?= ($du['vai_tro'] ?? '') === 'chu_sach' ? '#fffbeb' : '#ffffff' ?>; border-radius: 10px; padding: 14px 12px; transition: all 0.2s ease;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
              <input type="radio" name="vai_tro" value="chu_sach" <?= ($du['vai_tro'] ?? '') === 'chu_sach' ? 'checked' : '' ?> onchange="this.form.querySelectorAll('input[name=vai_tro]').forEach(r => { r.closest('label').style.borderColor = r.checked ? (r.value === 'khach_hang' ? '#10b981' : '#f59e0b') : '#e2e8f0'; r.closest('label').style.background = r.checked ? (r.value === 'khach_hang' ? '#f0fdf4' : '#fffbeb') : '#ffffff'; });">
              <strong style="color: #92400e; font-size: 0.95rem;">🏪 Seller</strong>
            </div>
            <div style="font-size: 0.8rem; color: #b45309; margin-left: 22px;">Book Owner &bull; List &amp; sell books</div>
          </label>
        </div>
      </div>

      <button type="submit" class="nut nut--chinh" style="width: 100%; padding: 12px; font-size: 1rem;">
        Register Now
      </button>

      <p style="text-align: center; margin-top: 20px; font-size: 0.95rem; color: #475569;">
        Already have an account? <a href="dang-nhap.php" style="color: #1e40af; font-weight: bold;">Sign in now &rarr;</a>
      </p>
    </form>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
