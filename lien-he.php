<?php
/**
 * lien-he.php - Contact form, textbook inquiry, and image attachments
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoLienHe;

$du = [
    'hoten'   => '',
    'email'   => '',
    'sdt'     => '',
    'noidung' => ''
];
$loi = [];
$tenAnhLuu = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['hoten']   = trim(chuoi($_POST['hoten'] ?? ''));
    $du['email']   = trim(chuoi($_POST['email'] ?? ''));
    $du['sdt']     = trim(chuoi($_POST['sdt'] ?? ''));
    $du['noidung'] = trim(chuoi($_POST['noidung'] ?? ''));

    if ($du['hoten'] === '') {
        $loi['hoten'] = 'Please enter your full name.';
    } elseif (mb_strlen($du['hoten']) < 2 || mb_strlen($du['hoten']) > 100) {
        $loi['hoten'] = 'Full name must be between 2 and 100 characters.';
    }

    if ($du['email'] === '') {
        $loi['email'] = 'Please provide an email address.';
    } elseif (!filter_var($du['email'], FILTER_VALIDATE_EMAIL)) {
        $loi['email'] = 'Invalid email address format (e.g. student@ued.udn.vn).';
    }

    if ($du['sdt'] === '') {
        $loi['sdt'] = 'Please enter your contact phone number.';
    } elseif (!preg_match('/^(0|\+84)[0-9]{9,10}$/', $du['sdt'])) {
        $loi['sdt'] = 'Invalid phone number (must be 10-11 digits, starting with 0 or +84).';
    }

    if ($du['noidung'] === '') {
        $loi['noidung'] = 'Please enter your message or textbook request details.';
    } elseif (mb_strlen($du['noidung']) < 10) {
        $loi['noidung'] = 'Message content must contain at least 10 characters.';
    }

    if (isset($_FILES['anh']) && $_FILES['anh']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['anh'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Error occurred during file upload (Code: ' . $file['error'] . ').';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $loi['anh'] = 'Attached file exceeds the 2 MB maximum allowed size.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $duoiHopLe = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!array_key_exists($mime, $duoiHopLe)) {
                $loi['anh'] = 'Invalid file type! Only JPG, PNG, or WebP images are allowed.';
            } else {
                $ext = $duoiHopLe[$mime];
                $tenNgauNhien = 'lh_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $duongDanDich = __DIR__ . '/uploads/' . $tenNgauNhien;

                if (!is_dir(__DIR__ . '/uploads')) {
                    mkdir(__DIR__ . '/uploads', 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $duongDanDich)) {
                    $tenAnhLuu = $tenNgauNhien;
                } else {
                    $loi['anh'] = 'Could not save uploaded image file on the server.';
                }
            }
        }
    }

    if (empty($loi)) {
        $khoLh = new KhoLienHe(db());
        $chuDe = 'Contact / Book Request [Phone: ' . $du['sdt'] . ']';
        $khoLh->luu($du['hoten'], $du['email'], $chuDe, $du['noidung'], $tenAnhLuu);

        $_SESSION['flash'] = 'Thank you! Your inquiry has been saved to the BookNest database successfully.';
        header('Location: lien-he.php');
        exit;
    }
}

$thongBao = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$tieuDe = 'Contact & Support';
$trang = 'lien-he';
$moTaTrang = 'Send feedback, textbook inquiry requests, and support for students at BookNest UED.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <h1>Contact &amp; Support</h1>
  <p>Need to order textbooks in bulk or have questions? Send a message to the BookNest team.</p>

  <?php if ($thongBao !== ''): ?>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 15px 20px; border-radius: 6px; margin-bottom: 25px;" aria-live="polite">
      <strong>✓ Done:</strong> <?= e($thongBao) ?>
    </div>
  <?php endif; ?>

  <div style="display: flex; gap: 40px; flex-wrap: wrap;">
    <div style="flex: 3; min-width: 320px; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0;">
      <form method="POST" action="lien-he.php" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        
        <!-- Full Name -->
        <div style="margin-bottom: 20px;">
          <label for="hoten" style="display: block; font-weight: bold; margin-bottom: 6px;">
            Full Name <span style="color: #dc2626;">*</span>
          </label>
          <input type="text" id="hoten" name="hoten" value="<?= e($du['hoten']) ?>" 
                 style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['hoten']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;"
                 placeholder="e.g. John Doe">
          <?php if (isset($loi['hoten'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['hoten']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Email & Phone -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
          <div style="flex: 1; min-width: 200px;">
            <label for="email" style="display: block; font-weight: bold; margin-bottom: 6px;">
              Email Address <span style="color: #dc2626;">*</span>
            </label>
            <input type="email" id="email" name="email" value="<?= e($du['email']) ?>" 
                   style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['email']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;"
                   placeholder="name@example.com">
            <?php if (isset($loi['email'])): ?>
              <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['email']) ?></p>
            <?php endif; ?>
          </div>

          <div style="flex: 1; min-width: 200px;">
            <label for="sdt" style="display: block; font-weight: bold; margin-bottom: 6px;">
              Phone Number <span style="color: #dc2626;">*</span>
            </label>
            <input type="tel" id="sdt" name="sdt" value="<?= e($du['sdt']) ?>" 
                   style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['sdt']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;"
                   placeholder="0905123456">
            <?php if (isset($loi['sdt'])): ?>
              <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['sdt']) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Message -->
        <div style="margin-bottom: 20px;">
          <label for="noidung" style="display: block; font-weight: bold; margin-bottom: 6px;">
            Message / Book Request <span style="color: #dc2626;">*</span>
          </label>
          <textarea id="noidung" name="noidung" rows="5" 
                    style="width: 100%; padding: 10px 12px; border: 1px solid <?= isset($loi['noidung']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 4px; box-sizing: border-box;"
                    placeholder="Describe specific textbook titles, quantity needed, or any feedback..."><?= e($du['noidung']) ?></textarea>
          <?php if (isset($loi['noidung'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['noidung']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Attachment -->
        <div style="margin-bottom: 25px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">
          <label for="anh" style="display: block; font-weight: bold; margin-bottom: 6px;">
            Image Attachment (Optional, max 2 MB, JPG/PNG/WebP):
          </label>
          <input type="file" id="anh" name="anh" accept="image/jpeg,image/png,image/webp" style="margin-top: 5px;">
          <?php if (isset($loi['anh'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 4px 0 0 0;"><?= e($loi['anh']) ?></p>
          <?php endif; ?>
        </div>

        <button type="submit" class="nut nut--chinh" style="padding: 12px 28px; font-size: 1.05rem;">
          ✉️ Send Message Now
        </button>
      </form>
    </div>

    <!-- Office Details -->
    <div style="flex: 2; min-width: 250px;">
      <div style="background: #f8fafc; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="margin-top: 0; color: #0f172a;">BookNest Office Information</h3>
        <p><strong>Address:</strong> Building A3, University of Science and Education - Danang University, 459 Ton Duc Thang, Danang.</p>
        <p><strong>Email:</strong> booknest.ued@gmail.com</p>
        <p><strong>Hotline:</strong> 0236.3841.323</p>
        <p><strong>Working Hours:</strong> Monday - Saturday (07:30 - 17:00)</p>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
