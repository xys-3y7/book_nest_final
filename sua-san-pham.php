<?php
/**
 * sua-san-pham.php - Edit textbook information in MySQL database
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/kiem-tra.php';

$u = nguoiDung();
if (!$u) {
    header('Location: dang-nhap.php?yeu_cau=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Administrators supervise and do not edit Seller books
if ($u['vai_tro'] === 'quan_tri') {
    $_SESSION['flash_loi'] = 'Administrators oversee the platform and cannot edit Seller products. If a product violates rules, you can delete it from the dashboard.';
    header('Location: quan-tri.php');
    exit;
}

// Seller role check
if ($u['vai_tro'] !== 'chu_sach') {
    ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI'], 'vai_tro' => $u['vai_tro']]);
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

$laChuSach = true;
$dichDen = 'chu-sach.php';

$db = db();
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

$st = $db->prepare('SELECT * FROM san_pham WHERE id = ?');
$st->execute([$id]);
$cu = $st->fetch();

if ($cu === false) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Sellers can only edit their own books
if ((int)$cu['chu_sach_id'] !== (int)$u['id']) {
    ghiLogBaoMat('cam_truy_cap', ['trang' => $_SERVER['REQUEST_URI'], 'loi' => 'sua_sach_khong_phai_cua_minh']);
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

$danhMucList = $db->query('SELECT id, ten FROM danh_muc ORDER BY id')->fetchAll();
$idDanhMuc = array_column($danhMucList, 'id');

$biaMauList = [
    ['file' => 'book_53ddc2905e87fd3f.png', 'ten' => 'Zero To Pro (AI Code)'],
    ['file' => 'book_a6eb1abe32481d65.png', 'ten' => 'OpenCode Mastery'],
    ['file' => 'book_e0a5fc3e123622fa.png', 'ten' => 'Rich Dad Poor Dad'],
    ['file' => 'book_c264f7e317d4a643.png', 'ten' => 'Fake: Fake Money'],
    ['file' => 'sach-laptrinh-web.png',    'ten' => 'Modern Web Dev'],
    ['file' => 'sach-csdl-sql.png',        'ten' => 'Databases & SQL'],
];

$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$du, $loi] = kiemTraSanPham($_POST, $idDanhMuc);

    $tenAnh = $cu['anh'];

    // 1. New file upload
    if (isset($_FILES['anh']) && $_FILES['anh']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['anh'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Error occurred during cover image upload (Code: ' . $file['error'] . ')';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $loi['anh'] = 'Book cover image must not exceed 5 MB';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $hopLe = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($hopLe[$mime])) {
                $loi['anh'] = 'Only valid JPG, PNG, or WebP images are accepted';
            } else {
                $ext = $hopLe[$mime];
                $tenAnh = 'book_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $tenAnh)) {
                    $loi['anh'] = 'Could not save image file to server uploads/ directory';
                    $tenAnh = $cu['anh'];
                }
            }
        }
    }

    // 2. Online Image URL
    $anhUrl = trim($_POST['anh_url'] ?? '');
    if ($anhUrl !== '' && $anhUrl !== $cu['anh']) {
        if (filter_var($anhUrl, FILTER_VALIDATE_URL) && (str_starts_with($anhUrl, 'http://') || str_starts_with($anhUrl, 'https://'))) {
            $tenAnh = $anhUrl;
        } else {
            $loi['anh'] = 'Invalid online image URL';
        }
    }

    // 3. Preset Cover
    $anhMau = trim($_POST['anh_mau'] ?? '');
    if ($anhMau !== '') {
        $safeMau = basename($anhMau);
        if (file_exists(__DIR__ . '/uploads/' . $safeMau) || file_exists(__DIR__ . '/images/' . $safeMau)) {
            $tenAnh = $safeMau;
        }
    }

    if (empty($loi)) {
        $st = $db->prepare(
            'UPDATE san_pham 
             SET ten = :ten, ten_en = :ten_en, tac_gia = :tac_gia, tac_gia_en = :tac_gia_en, isbn = :isbn, nam_xuat_ban = :nam_xuat_ban, gia = :gia, ton_kho = :ton_kho, danh_muc_id = :danh_muc_id, mo_ta = :mo_ta, mo_ta_en = :mo_ta_en, anh = :anh 
             WHERE id = :id'
        );
        $st->execute([
            'ten'          => $du['ten'],
            'ten_en'       => $du['ten_en'] ?: null,
            'tac_gia'      => $du['tac_gia'] ?: ($cu['tac_gia'] ?? 'UED Editorial Board'),
            'tac_gia_en'   => $du['tac_gia_en'] ?: null,
            'isbn'         => $du['isbn'] ?: ($cu['isbn'] ?? '978-604-0-12345-6'),
            'nam_xuat_ban' => $du['nam_xuat_ban'] ?: ($cu['nam_xuat_ban'] ?? (int)date('Y')),
            'gia'          => $du['gia'],
            'ton_kho'      => $du['ton_kho'],
            'danh_muc_id'  => $du['danh_muc_id'],
            'mo_ta'        => $du['mo_ta'],
            'mo_ta_en'     => $du['mo_ta_en'] ?: null,
            'anh'          => $tenAnh,
            'id'           => $id
        ]);

        $_SESSION['flash'] = "✓ Textbook #$id updated successfully! Cover image saved automatically to database.";
        header("Location: $dichDen");
        exit;
    }
}

$f = $_SERVER['REQUEST_METHOD'] === 'POST' ? array_merge($cu, $du) : $cu;

$tieuDe = 'Edit Textbook #' . $id;
$trang = 'chu-sach';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua" style="max-width: 1040px; margin: 0 auto; padding-top: 25px; padding-bottom: 50px;">

  <!-- BANNER -->
  <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); color: #ffffff; padding: 26px 30px; border-radius: 16px; margin-bottom: 28px; box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.35); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; border: 1px solid rgba(255,255,255,0.1);">
    <div>
      <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
        <span style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: #ffffff; font-size: 0.72rem; font-weight: 800; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px;">
          🏪 SELLER STALL MANAGEMENT • EDIT TEXTBOOK
        </span>
        <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 0.72rem; font-weight: 700; padding: 4px 10px; border-radius: 9999px; border: 1px solid rgba(16, 185, 129, 0.3);">
          ⚡ Auto-Update Database
        </span>
      </div>
      <h1 style="color: #ffffff; margin: 0 0 6px 0; font-size: 1.85rem; font-weight: 800;">
        Edit Textbook Details #<?= $id ?>
      </h1>
      <p style="margin: 0; color: #94a3b8; font-size: 0.94rem;">
        <?= e($cu['ten']) ?> &bull; Database Record ID: <code>#<?= $id ?></code>
      </p>
    </div>
    <a href="<?= $dichDen ?>" class="nut nut--vien" style="background: rgba(255,255,255,0.08); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); font-weight: 600; padding: 10px 18px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
      &larr; Back to List
    </a>
  </div>

  <form method="POST" action="sua-san-pham.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate id="form-sua-sach">
    <?= csrfField() ?>

    <div style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 24px; align-items: start;">
      
      <!-- COLUMN 1: INFORMATION -->
      <div style="background: #ffffff; padding: 28px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 18px -2px rgba(0,0,0,0.05);">
        <h3 style="margin: 0 0 20px 0; font-size: 1.25rem; font-weight: 800; color: #0f172a; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
          <span>📝</span> 1. Textbook Information
        </h3>

        <!-- Book Title -->
        <div style="margin-bottom: 20px;">
          <label for="ten" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
            Textbook Title <span style="color:#dc2626;">*</span>
          </label>
          <input type="text" id="ten" name="ten" value="<?= e($f['ten']) ?>" required style="width: 100%; padding: 11px 14px; border: 1.5px solid <?= isset($loi['ten']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 8px; box-sizing: border-box; font-size: 1rem;">
          <?php if (isset($loi['ten'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 5px 0 0 0; font-weight: 600;">⚠ <?= e($loi['ten']) ?></p>
          <?php endif; ?>
        </div>

        <!-- English Title (Optional) -->
        <div style="margin-bottom: 20px;">
          <label for="ten_en" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
            English Title <span style="color:#64748b; font-weight: 400; font-size: 0.85rem;">(Optional - for English Subsystem)</span>
          </label>
          <input type="text" id="ten_en" name="ten_en" value="<?= e($f['ten_en'] ?? '') ?>" placeholder="e.g. Advanced Python Programming in Practice" style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 0.95rem;">
          <?php if (isset($loi['ten_en'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 5px 0 0 0; font-weight: 600;">⚠ <?= e($loi['ten_en']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Author and ISBN row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
          <div>
            <label for="tac_gia" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
              Author / Editor
            </label>
            <input type="text" id="tac_gia" name="tac_gia" value="<?= e($f['tac_gia'] ?? '') ?>" placeholder="e.g. Robert T. Kiyosaki" style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 0.95rem;">
          </div>
          <div>
            <label for="tac_gia_en" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
              Author (English) <span style="color:#64748b; font-weight: 400; font-size: 0.82rem;">(Optional)</span>
            </label>
            <input type="text" id="tac_gia_en" name="tac_gia_en" value="<?= e($f['tac_gia_en'] ?? '') ?>" placeholder="e.g. Robert Kiyosaki" style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 0.95rem;">
          </div>
        </div>

        <!-- ISBN row -->
        <div style="margin-bottom: 20px;">
          <label for="isbn" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
            ISBN Number
          </label>
          <input type="text" id="isbn" name="isbn" value="<?= e($f['isbn'] ?? '') ?>" placeholder="e.g. 978-604-0-12345-6" style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 0.95rem;">
        </div>

        <!-- Category & Publication Year -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px;">
          <div>
            <label for="danh_muc_id" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
              Category <span style="color:#dc2626;">*</span>
            </label>
            <select id="danh_muc_id" name="danh_muc_id" style="width: 100%; padding: 11px 14px; border: 1.5px solid <?= isset($loi['danh_muc_id']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 8px; box-sizing: border-box; font-size: 0.98rem; background-color: #f8fafc;">
              <?php foreach ($danhMucList as $dm): ?>
                <option value="<?= $dm['id'] ?>" <?= (int)$f['danh_muc_id'] === (int)$dm['id'] ? 'selected' : '' ?>>
                  📂 <?= e(layTenDanhMucEn($dm['id'], $dm['ten'])) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($loi['danh_muc_id'])): ?>
              <p style="color: #dc2626; font-size: 0.85rem; margin: 5px 0 0 0; font-weight: 600;">⚠ <?= e($loi['danh_muc_id']) ?></p>
            <?php endif; ?>
          </div>

          <div>
            <label for="nam_xuat_ban" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
              Year
            </label>
            <input type="number" id="nam_xuat_ban" name="nam_xuat_ban" value="<?= e((string)($f['nam_xuat_ban'] ?? date('Y'))) ?>" min="1900" max="2099" style="width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 0.95rem;">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
          <!-- Price -->
          <div>
            <label for="gia" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
              Selling Price (VNĐ) <span style="color:#dc2626;">*</span>
            </label>
            <input type="number" id="gia" name="gia" value="<?= e($f['gia']) ?>" min="1000" step="1000" required style="width: 100%; padding: 11px 14px; border: 1.5px solid <?= isset($loi['gia']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 8px; box-sizing: border-box; font-size: 1rem; font-weight: 700; color: #dc2626;">
            <?php if (isset($loi['gia'])): ?>
              <p style="color: #dc2626; font-size: 0.85rem; margin: 5px 0 0 0; font-weight: 600;">⚠ <?= e($loi['gia']) ?></p>
            <?php endif; ?>
          </div>

          <!-- Stock -->
          <div>
            <label for="ton_kho" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
              Available Stock <span style="color:#dc2626;">*</span>
            </label>
            <input type="number" id="ton_kho" name="ton_kho" value="<?= e($f['ton_kho']) ?>" min="0" max="9999" required style="width: 100%; padding: 11px 14px; border: 1.5px solid <?= isset($loi['ton_kho']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 8px; box-sizing: border-box; font-size: 1rem; font-weight: 700; color: #16a34a;">
            <?php if (isset($loi['ton_kho'])): ?>
              <p style="color: #dc2626; font-size: 0.85rem; margin: 5px 0 0 0; font-weight: 600;">⚠ <?= e($loi['ton_kho']) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom: 20px;">
          <label for="mo_ta" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
            Textbook Description
          </label>
          <textarea id="mo_ta" name="mo_ta" rows="4" style="width: 100%; padding: 12px 14px; border: 1.5px solid <?= isset($loi['mo_ta']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 8px; box-sizing: border-box; font-size: 0.95rem; resize: vertical;"><?= e($f['mo_ta']) ?></textarea>
          <?php if (isset($loi['mo_ta'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 5px 0 0 0; font-weight: 600;">⚠ <?= e($loi['mo_ta']) ?></p>
          <?php endif; ?>
        </div>

        <!-- English Description (Optional) -->
        <div style="margin-bottom: 10px;">
          <label for="mo_ta_en" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.95rem;">
            English Description <span style="color:#64748b; font-weight: 400; font-size: 0.85rem;">(Optional - for English Subsystem)</span>
          </label>
          <textarea id="mo_ta_en" name="mo_ta_en" rows="4" placeholder="English overview of syllabus, topics, and course knowledge..." style="width: 100%; padding: 12px 14px; border: 1.5px solid <?= isset($loi['mo_ta_en']) ? '#dc2626' : '#cbd5e1' ?>; border-radius: 8px; box-sizing: border-box; font-size: 0.95rem; resize: vertical;"><?= e($f['mo_ta_en'] ?? '') ?></textarea>
          <?php if (isset($loi['mo_ta_en'])): ?>
            <p style="color: #dc2626; font-size: 0.85rem; margin: 5px 0 0 0; font-weight: 600;">⚠ <?= e($loi['mo_ta_en']) ?></p>
          <?php endif; ?>
        </div>

      </div>

      <!-- COLUMN 2: COVER IMAGE & UPDATE -->
      <div style="background: #ffffff; padding: 28px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 18px -2px rgba(0,0,0,0.05);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px;">
          <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span>📸</span> 2. Book Cover Image
          </h3>
          <span style="font-size: 0.72rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 3px 10px; border-radius: 999px; font-weight: 700;">
            ✓ Database Updated
          </span>
        </div>

        <!-- LIVE PREVIEW -->
        <div style="text-align: center; margin-bottom: 22px; background: #f8fafc; padding: 18px; border-radius: 12px; border: 1.5px dashed #cbd5e1;">
          <div style="font-size: 0.82rem; font-weight: 700; color: #64748b; margin-bottom: 10px; text-transform: uppercase;">
            🖼️ Current Cover / Live Preview
          </div>
          <div style="display: inline-block; position: relative; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 25px -3px rgba(0,0,0,0.2); border: 2px solid #ffffff;">
            <img id="img-preview" src="<?= e(anhSach($cu['anh'])) ?>" alt="Cover" width="160" height="215" style="object-fit: cover; display: block; background: #e2e8f0;">
            <span id="preview-badge" style="position: absolute; bottom: 6px; left: 6px; right: 6px; background: rgba(15, 23, 42, 0.85); color: #ffffff; font-size: 0.7rem; padding: 3px 6px; border-radius: 4px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              <?= e($cu['anh'] ? basename($cu['anh']) : 'Default Cover') ?>
            </span>
          </div>
          <div style="color: #059669; font-size: 0.8rem; font-weight: 600; margin-top: 10px;">
            ✓ Automatically updates <code>san_pham.anh</code> in MySQL
          </div>
        </div>

        <?php if (isset($loi['anh'])): ?>
          <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 0.88rem; font-weight: 600;">
            ⚠ <?= e($loi['anh']) ?>
          </div>
        <?php endif; ?>

        <!-- REPLACE FILE -->
        <div style="margin-bottom: 18px;">
          <label style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.92rem;">
            📁 Replace with New File:
          </label>
          <input type="file" id="input-file-anh" name="anh" accept="image/jpeg,image/png,image/webp" style="display: block; width: 100%; padding: 8px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; background: #ffffff;">
          <span style="font-size: 0.78rem; color: #64748b; margin-top: 4px; display: block;">
            Leave empty to keep the current cover image.
          </span>
        </div>

        <!-- NEW URL -->
        <div style="margin-bottom: 18px;">
          <label for="input-url-anh" style="display: block; font-weight: 700; margin-bottom: 6px; color: #1e293b; font-size: 0.92rem;">
            🌐 Or Enter New Image URL:
          </label>
          <input type="url" id="input-url-anh" name="anh_url" value="<?= str_starts_with((string)$cu['anh'], 'http') ? e($cu['anh']) : '' ?>" placeholder="https://..." style="width: 100%; padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 0.9rem;">
        </div>

        <!-- PRESETS -->
        <div style="margin-bottom: 24px;">
          <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b; font-size: 0.92rem;">
            ⚡ Or Choose from Presets:
          </label>
          <input type="hidden" id="input-anh-mau" name="anh_mau" value="">
          
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
            <?php foreach ($biaMauList as $bm): ?>
              <div class="preset-card" onclick="chonBiaMau('<?= e($bm['file']) ?>', '<?= e($bm['ten']) ?>', this)" style="cursor: pointer; border: 2px solid <?= ($cu['anh'] === $bm['file']) ? '#2563eb' : '#e2e8f0' ?>; border-radius: 8px; padding: 6px; text-align: center; background: <?= ($cu['anh'] === $bm['file']) ? '#eff6ff' : '#f8fafc' ?>; transition: all 0.2s ease;">
                <img src="<?= e(anhSach($bm['file'])) ?>" alt="<?= e($bm['ten']) ?>" width="45" height="60" style="object-fit: cover; border-radius: 4px; display: block; margin: 0 auto 4px auto;">
                <span style="font-size: 0.72rem; color: #475569; font-weight: 600; display: block; line-height: 1.2; height: 26px; overflow: hidden;"><?= e($bm['ten']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- UPDATE BUTTON -->
        <div style="border-top: 1.5px solid #f1f5f9; padding-top: 20px;">
          <button type="submit" class="nut nut--chinh" style="width: 100%; padding: 13px 20px; font-size: 1.05rem; font-weight: 800; border-radius: 10px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none; box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span>💾</span>
            <span>Update Textbook &amp; Cover in Database</span>
          </button>
          
          <div style="text-align: center; margin-top: 12px;">
            <a href="<?= $dichDen ?>" style="color: #64748b; font-size: 0.88rem; text-decoration: none;">
              ✕ Cancel and go back
            </a>
          </div>
        </div>

      </div>

    </div>
  </form>

</main>

<script>
const fileInput = document.getElementById('input-file-anh');
const urlInput = document.getElementById('input-url-anh');
const mauInput = document.getElementById('input-anh-mau');
const imgPreview = document.getElementById('img-preview');
const badge = document.getElementById('preview-badge');

if (fileInput) {
  fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const url = URL.createObjectURL(file);
      imgPreview.src = url;
      badge.textContent = 'New File: ' + file.name;
      badge.style.background = 'rgba(22, 163, 74, 0.9)';
      urlInput.value = '';
      mauInput.value = '';
      resetPresetHighlight();
    }
  });
}

if (urlInput) {
  urlInput.addEventListener('input', function() {
    const val = this.value.trim();
    if (val.startsWith('http://') || val.startsWith('https://')) {
      imgPreview.src = val;
      badge.textContent = 'Online Image URL';
      badge.style.background = 'rgba(37, 99, 235, 0.9)';
      if (fileInput) fileInput.value = '';
      mauInput.value = '';
      resetPresetHighlight();
    }
  });
}

function chonBiaMau(filename, title, el) {
  mauInput.value = filename;
  imgPreview.src = filename.includes('/') ? filename : ('uploads/' + filename);
  imgPreview.onerror = function() {
    this.src = 'images/' + filename;
  };
  badge.textContent = 'Preset: ' + title;
  badge.style.background = 'rgba(217, 119, 6, 0.9)';
  if (fileInput) fileInput.value = '';
  if (urlInput) urlInput.value = '';
  
  resetPresetHighlight();
  const card = el || (typeof event !== 'undefined' && event ? event.currentTarget : null);
  if (card && card.style) {
    card.style.borderColor = '#2563eb';
    card.style.background = '#eff6ff';
  }
}

function resetPresetHighlight() {
  document.querySelectorAll('.preset-card').forEach(el => {
    el.style.borderColor = '#e2e8f0';
    el.style.background = '#f8fafc';
  });
}
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
