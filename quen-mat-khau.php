<?php
/**
 * quen-mat-khau.php - Forgot Password request handler
 * Team 12 - Class 24CNTT3 - Security & Authentication
 */

require_once __DIR__ . '/inc/config.php';

// Redirect if already logged in
if (nguoiDung()) {
    header('Location: index.php');
    exit;
}

$email = trim(chuoi($_POST['email'] ?? ''));
$thongBao = '';
$loi = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($email === '') {
        $loi = 'Please enter your registered email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loi = 'Invalid email address format.';
    } else {
        $db = db();
        $st = $db->prepare('SELECT id, ho_ten, email FROM nguoi_dung WHERE LOWER(email) = LOWER(?)');
        $st->execute([$email]);
        $u = $st->fetch();

        if ($u) {
            // Generate a secure 32-byte reset token
            $token = bin2hex(random_bytes(24));
            $_SESSION['reset_token_' . $u['id']] = [
                'token'   => $token,
                'email'   => $u['email'],
                'expires' => time() + 3600 // 1 hour validity
            ];
            $resetLink = 'dat-lai-mat-khau.php?uid=' . $u['id'] . '&token=' . $token;
            $thongBao = 'Password reset instructions have been generated for your account.';
        } else {
            // Even if user not found, show user-friendly message (protect against user enumeration) or helper
            $loi = 'We could not find an account with that email address.';
        }
    }
}

$tieuDe = 'Forgot Password';
$trang = 'dang-nhap';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 480px; margin: 40px auto;">
  <div style="background: #ffffff; padding: 35px 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    
    <div style="text-align: center; margin-bottom: 24px;">
      <span style="font-size: 2.8rem; display: inline-block; margin-bottom: 8px;">🔑</span>
      <h1 style="color: #0f172a; margin: 0 0 8px 0; font-size: 1.7rem; font-weight: 800;">Forgot Password</h1>
      <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
        Enter your registered email address and we will provide a link to reset your password.
      </p>
    </div>

    <?php if ($loi): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.92rem;">
        <strong>⚠ Notice:</strong> <?= e($loi) ?>
      </div>
    <?php endif; ?>

    <?php if ($thongBao && $resetLink): ?>
      <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 18px 20px; border-radius: 8px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 8px 0; font-size: 1.05rem; color: #15803d;">✓ Reset Link Ready</h3>
        <p style="margin: 0 0 14px 0; font-size: 0.92rem; line-height: 1.5;">
          A password reset link has been created for <strong><?= e($email) ?></strong>. Since this is an offline academic environment, click the button below to proceed:
        </p>
        <a href="<?= e($resetLink) ?>" class="nut nut--chinh" style="display: block; text-align: center; padding: 10px 16px; text-decoration: none; font-weight: 700; border-radius: 8px;">
          Proceed to Reset Password &rarr;
        </a>
      </div>
    <?php endif; ?>

    <form method="POST" action="quen-mat-khau.php" novalidate>
      <?= csrfField() ?>
      <div style="margin-bottom: 22px;">
        <label for="email" style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">
          Your Email Address <span style="color:#dc2626;">*</span>
        </label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="e.g. customer@ued.vn or seller@ued.vn" required style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 1rem; box-sizing: border-box;">
      </div>

      <button type="submit" class="nut nut--chinh" style="width: 100%; padding: 12px; font-size: 1rem; border-radius: 8px; font-weight: 700;">
        Send Reset Link
      </button>

      <div style="text-align: center; margin-top: 22px; font-size: 0.92rem;">
        <a href="dang-nhap.php" style="color: #2563eb; text-decoration: none; font-weight: 600;">&larr; Back to Sign In</a>
      </div>
    </form>

  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
