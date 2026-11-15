<?php
/**
 * dat-lai-mat-khau.php - Reset password with token
 * Team 12 - Class 24CNTT3 - Security & Authentication
 */

require_once __DIR__ . '/inc/config.php';

// Redirect if already logged in
if (nguoiDung()) {
    header('Location: index.php');
    exit;
}

$uid = filter_var($_GET['uid'] ?? $_POST['uid'] ?? '', FILTER_VALIDATE_INT);
$token = trim(chuoi($_GET['token'] ?? $_POST['token'] ?? ''));

$loi = '';
$thanhCong = false;

// Verify token
$sessionTokenKey = 'reset_token_' . $uid;
$stored = $_SESSION[$sessionTokenKey] ?? null;

if (!$uid || !$token || !$stored || $stored['token'] !== $token || time() > $stored['expires']) {
    $loi = 'This password reset link is invalid or has expired. Please submit a new request.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$loi) {
    $matKhauMoi = chuoi($_POST['mat_khau_moi'] ?? '');
    $xacNhan = chuoi($_POST['xac_nhan'] ?? '');

    if (mb_strlen($matKhauMoi) < 8) {
        $loi = 'New password must be at least 8 characters long.';
    } elseif ($matKhauMoi !== $xacNhan) {
        $loi = 'Password confirmation does not match.';
    } else {
        $db = db();
        $hash = password_hash($matKhauMoi, PASSWORD_DEFAULT);
        $st = $db->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?');
        $st->execute([$hash, $uid]);

        // Invalidate token
        unset($_SESSION[$sessionTokenKey]);

        $_SESSION['flash'] = 'Your password has been successfully reset. Please sign in with your new password.';
        header('Location: dang-nhap.php');
        exit;
    }
}

$tieuDe = 'Set New Password';
$trang = 'dang-nhap';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 480px; margin: 40px auto;">
  <div style="background: #ffffff; padding: 35px 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    
    <div style="text-align: center; margin-bottom: 24px;">
      <span style="font-size: 2.8rem; display: inline-block; margin-bottom: 8px;">🔐</span>
      <h1 style="color: #0f172a; margin: 0 0 8px 0; font-size: 1.7rem; font-weight: 800;">Set New Password</h1>
      <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
        Create a new, strong password for your BookNest account.
      </p>
    </div>

    <?php if ($loi): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.92rem;">
        <strong>⚠ Notice:</strong> <?= e($loi) ?>
        <div style="margin-top: 8px;">
          <a href="quen-mat-khau.php" style="color: #dc2626; font-weight: 700;">Request a new link &rarr;</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!$loi || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <form method="POST" action="dat-lai-mat-khau.php" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="uid" value="<?= (int)$uid ?>">
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div style="margin-bottom: 18px;">
          <label for="mat_khau_moi" style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">
            New Password (Min 8 characters) <span style="color:#dc2626;">*</span>
          </label>
          <input type="password" id="mat_khau_moi" name="mat_khau_moi" required placeholder="Enter new password..." style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 1rem; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 22px;">
          <label for="xac_nhan" style="display: block; font-weight: 700; margin-bottom: 6px; color: #334155;">
            Confirm New Password <span style="color:#dc2626;">*</span>
          </label>
          <input type="password" id="xac_nhan" name="xac_nhan" required placeholder="Re-enter new password..." style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 1rem; box-sizing: border-box;">
        </div>

        <button type="submit" class="nut nut--chinh" style="width: 100%; padding: 12px; font-size: 1rem; border-radius: 8px; font-weight: 700;">
          Save New Password
        </button>

        <div style="text-align: center; margin-top: 20px; font-size: 0.92rem;">
          <a href="dang-nhap.php" style="color: #2563eb; text-decoration: none; font-weight: 600;">&larr; Back to Sign In</a>
        </div>
      </form>
    <?php endif; ?>

  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
