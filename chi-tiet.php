<?php
/**
 * chi-tiet.php - Trang chi tiết sản phẩm / sách theo tham số URL ?id=
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web (CSRF Protection, HttpOnly Cookie, Input Validation)
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$db = db();
$kho = new KhoSanPham($db);
$u = nguoiDung();

// Kiểm tra tham số id: ép kiểm tra số nguyên nghiêm ngặt bằng filter_var
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$sp = $id ? $kho->timTheoId($id) : null;

// Nếu id thiếu, không phải số nguyên hoặc sản phẩm không tồn tại -> Trả về mã 404
if ($sp === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Handle Submit Review POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_danh_gia'])) {
    if (!$u) {
        $_SESSION['flash_error'] = 'Please sign in to write a review for this book.';
        header('Location: dang-nhap.php?yeu_cau=chi-tiet.php?id=' . $sp->id);
        exit;
    }

    $soSao = filter_var($_POST['so_sao'] ?? 5, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5, 'default' => 5]]);
    $noiDung = trim(chuoi($_POST['noi_dung'] ?? ''));

    if (mb_strlen($noiDung) < 5) {
        $_SESSION['flash_loi'] = 'Please write a review comment of at least 5 characters.';
    } else {
        // Check if user bought this book
        $stCheckBought = $db->prepare('SELECT COUNT(*) FROM chi_tiet_don ct JOIN don_hang dh ON ct.don_hang_id = dh.id WHERE dh.nguoi_dung_id = ? AND ct.san_pham_id = ? AND dh.trang_thai = "da_giao"');
        $stCheckBought->execute([$u['id'], $sp->id]);
        $daMuaHang = ((int)$stCheckBought->fetchColumn() > 0) ? 1 : 0;

        $stReview = $db->prepare('INSERT INTO danh_gia (nguoi_dung_id, san_pham_id, so_sao, noi_dung, da_mua_hang, ngay_tao) VALUES (?, ?, ?, ?, ?, NOW())');
        $stReview->execute([$u['id'], $sp->id, $soSao, $noiDung, $daMuaHang]);
        $_SESSION['flash'] = 'Thank you! Your book review and rating have been posted.';
    }
    header('Location: chi-tiet.php?id=' . $sp->id . '#reviews');
    exit;
}

// Fetch reviews for this book
$stRev = $db->prepare(
    'SELECT dg.*, nd.ho_ten 
     FROM danh_gia dg 
     JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id 
     WHERE dg.san_pham_id = ? 
     ORDER BY dg.ngay_tao DESC'
);
$stRev->execute([$sp->id]);
$dsDanhGia = $stRev->fetchAll();
$tongDanhGia = count($dsDanhGia);
$diemTrungBinh = $tongDanhGia > 0 ? round(array_sum(array_column($dsDanhGia, 'so_sao')) / $tongDanhGia, 1) : 5.0;

// Ghi nhớ mục đã xem vào Cookie 'da_xem' (Tối đa 4 ID mới nhất đứng đầu)
$cu  = array_map('intval', explode(',', $_COOKIE['da_xem'] ?? ''));
$moi = array_unique([$sp->id, ...array_filter($cu)]);
setcookie('da_xem', implode(',', array_slice($moi, 0, 4)), [
    'expires'  => time() + 30 * 24 * 3600, // 30 ngày
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

$tieuDe = $sp->ten;
$trang = 'danh-sach';
$moTaTrang = 'View detailed information of textbook ' . $sp->ten . ' at BookNest UED.';

// Lấy 4 đầu sách liên quan cùng danh mục qua KhoSanPham
$sachLienQuan = $kho->sanPhamLienQuan($sp->danhMucId, $sp->id, 4);

$thongBao = $_SESSION['flash'] ?? '';
$thongBaoLoi = $_SESSION['flash_loi'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_loi']);

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <!-- Điều hướng đường dẫn (Breadcrumbs) -->
  <nav aria-label="Breadcrumbs" style="margin-bottom: 24px; font-size: 0.92rem; color: #64748b; display: flex; align-items: center; gap: 8px;">
    <a href="index.php" style="color: #64748b; text-decoration: none;">🏠 Home</a>
    <span>&rsaquo;</span>
    <a href="danh-sach.php" style="color: #64748b; text-decoration: none;">Textbook Catalog</a>
    <span>&rsaquo;</span>
    <span style="color: #0f172a; font-weight: 700;"><?= e($sp->ten) ?></span>
  </nav>

  <!-- Chi tiết sách -->
  <article class="chi-tiet-container">
    
    <!-- Ảnh bìa sách -->
    <div class="chi-tiet__cot-anh">
      <figure style="margin: 0; text-align: center;">
        <img src="<?= e(anhSach($sp->anh)) ?>" alt="Cover <?= e($sp->ten) ?>" style="border-radius:12px;box-shadow:var(--shadow-card);">
      </figure>
      <div style="margin-top: 18px; text-align: center;">
        <span class="the__the-loai-badge" style="position: static; font-size: 0.85rem; padding: 6px 16px; background:#1a56db; color:#fff; border-radius:9999px;">
          📂 <?= e(layTenDanhMucEn($sp->danhMucId, $sp->tenDanhMuc ?? 'Textbook')) ?>
        </span>
      </div>
    </div>

    <!-- Thông tin và Đặt mua -->
    <div class="chi-tiet__cot-thong-tin">
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #111827; margin: 0 0 12px 0;"><?= e($sp->ten) ?></h1>
      
      <div class="chi-tiet__gia" style="font-size: 1.85rem; font-weight: 800; color: #e02424; margin-bottom: 16px;">
        <?= vnd($sp->gia) ?>
        <span style="font-size: 1.05rem; color: #9ca3af; text-decoration: line-through; font-weight: 500; margin-left: 10px;">
          <?= vnd(round($sp->gia * 1.25, -3)) ?>
        </span>
        <span style="background: #fdf2f2; color: #e02424; font-size: 0.8rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #f8b4b4; margin-left: 8px;">-25% Student</span>
      </div>

      <!-- Khối thông tin Người bán / Chủ sách (Book Owner Card) -->
      <div style="display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 44px; height: 44px; border-radius: 50%; background: <?= $sp->chuSachId ? '#fef3c7' : '#e0e7ff' ?>; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; border: 1.5px solid <?= $sp->chuSachId ? '#fcd34d' : '#c7d2fe' ?>; flex-shrink: 0;">
            <?= $sp->chuSachId ? '🏪' : '👑' ?>
          </div>
          <div>
            <div style="font-size: 0.76rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; color: <?= $sp->chuSachId ? '#b45309' : '#4338ca' ?>;">
              <?= $sp->chuSachId ? 'Partner Book Seller' : 'Official Bookstore' ?>
            </div>
            <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">
              <?= e($sp->tenChuSach ?: 'BookNest UED Student Bookstore') ?>
            </div>
            <div style="font-size: 0.82rem; color: #64748b;">
              <span>⭐ 4.9/5 (120+ orders)</span> &bull; 
              <span style="color: #10b981; font-weight: 600;">✓ Verified</span>
            </div>
          </div>
        </div>
        <div>
          <?php if ($sp->chuSachId): ?>
            <a href="danh-sach.php?chu_sach=<?= $sp->chuSachId ?>" style="background: #ffffff; color: #b45309; border: 1px solid #fcd34d; font-weight: 700; font-size: 0.82rem; padding: 7px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
              <span>🏪</span> View Seller Store
            </a>
          <?php else: ?>
            <span style="background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 0.82rem; padding: 7px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
              <span>🛡️</span> UED Standard Store
            </span>
          <?php endif; ?>
        </div>
      </div>

      <table class="bang-thong-so" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tbody>
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600; width: 140px;">Author:</th>
            <td style="padding: 8px 0; color: #111827; font-weight: 700;"><?= e($sp->tacGia ?: 'UED Editorial Board') ?></td>
          </tr>
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600;">Category:</th>
            <td style="padding: 8px 0; color: #111827;"><?= e(layTenDanhMucEn($sp->danhMucId, $sp->tenDanhMuc ?? 'Textbook')) ?></td>
          </tr>
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600;">ISBN / Catalog:</th>
            <td style="padding: 8px 0; color: #111827;"><code><?= e($sp->isbn ?: '978-604-0-12345-6') ?></code></td>
          </tr>
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600;">Publication Year:</th>
            <td style="padding: 8px 0; color: #111827;"><?= e((string)($sp->namXuatBan ?: 2024)) ?></td>
          </tr>
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600;">Stock Status:</th>
            <td style="padding: 8px 0;">
              <?php if ($sp->tonKho > 0): ?>
                <span style="color: #0e9f6e; font-weight: 700;">● In Stock (<?= $sp->tonKho ?> copies available)</span>
              <?php else: ?>
                <span style="color: #e02424; font-weight: 700;">○ Temporarily out of stock</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th style="text-align: left; padding: 8px 0; color: #64748b; font-weight: 600;">Ratings:</th>
            <td style="padding: 8px 0; color: #b45309; font-weight: 700;">
              ⭐ <?= number_format($diemTrungBinh, 1) ?> / 5.0 (<?= $tongDanhGia ?> customer <?= $tongDanhGia === 1 ? 'review' : 'reviews' ?>)
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Biểu mẫu Mua ngay, Thêm vào giỏ hàng và Nút Yêu thích (CSRF Protected) -->
      <div class="chi-tiet__hop-mua" style="background: #f9fafb; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 25px;">
        <?php if ($sp->tonKho > 0): ?>
          <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 18px; flex-wrap: wrap;">
            <label for="so-luong-nhap" style="font-weight: 700; color: #374151;">Quantity:</label>
            <input type="number" id="so-luong-nhap" name="soLuong" value="1" min="1" max="<?= min(99, $sp->tonKho) ?>" style="width: 80px; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; text-align: center; font-size: 1.05rem; font-weight: 800; color: #111827;">
            <span style="color: #6b7280; font-size: 0.88rem;">(Max <?= min(99, $sp->tonKho) ?> copies/order)</span>
          </div>

          <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <!-- 1. Mua ngay -->
            <form method="POST" action="gio-hang.php" id="form-mua-ngay" style="margin: 0;">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="action" value="mua_ngay">
              <input type="hidden" name="id" value="<?= $sp->id ?>">
              <input type="hidden" name="soLuong" id="so-luong-mua-ngay" value="1">
              <button type="submit" class="nut btn-mua-ngay" style="padding: 12px 24px; font-size: 1rem; border-radius: 10px;">
                ⚡ Buy Now
              </button>
            </form>

            <!-- 2. Thêm vào giỏ -->
            <form method="POST" action="gio-hang.php" id="form-them-gio" class="form-them-gio" style="margin: 0; display: inline-flex; align-items: center; gap: 10px;">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="action" value="them">
              <input type="hidden" name="id" value="<?= $sp->id ?>">
              <input type="hidden" name="soLuong" id="so-luong-them-gio" value="1">
              <button type="submit" id="btn-them-gio" class="nut btn-gio-hang" style="padding: 12px 22px; font-size: 1rem; border-radius: 10px;">
                🛒 Add to Cart
              </button>
              <span id="thong-bao-gio" style="color: #0e9f6e; font-weight: 700; font-size: 0.92rem;"></span>
            </form>

            <!-- 3. Yêu thích -->
            <?php $daThich = laYeuThich((int)$sp->id); ?>
            <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'chi-tiet.php?id=' . $sp->id) ?>" 
               class="nut nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" 
               data-id="<?= $sp->id ?>" 
               data-dang-chi-tiet="1" 
               role="button" 
               aria-label="<?= $daThich ? 'Remove from wishlist' : 'Save to wishlist' ?> <?= e($sp->ten) ?>" 
               style="padding: 11px 20px; font-size: 0.95rem; text-decoration: none; border-radius: 10px;">
              <?= $daThich ? '❤️ Saved to Wishlist' : '🤍 Save to Wishlist' ?>
            </a>
          </div>
        <?php else: ?>
          <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <p style="color: #e02424; font-weight: 600; margin: 0;">
              * This textbook is currently out of stock at UED warehouse. You can save it to your wishlist to receive restock notifications.
            </p>
            <?php $daThich = laYeuThich((int)$sp->id); ?>
            <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'chi-tiet.php?id=' . $sp->id) ?>" 
               class="nut nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" 
               data-id="<?= $sp->id ?>" 
               data-dang-chi-tiet="1" 
               role="button" 
               style="padding: 10px 18px; font-size: 0.92rem; text-decoration: none;">
              <?= $daThich ? '❤️ Saved to Wishlist' : '🤍 Save to Wishlist for Restock' ?>
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Nội dung giới thiệu tóm tắt giáo trình -->
      <h2 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin: 24px 0 10px 0;">📖 Textbook Description</h2>
      <p style="line-height: 1.75; color: #4b5563; font-size: 1rem; margin: 0;">
        <?= nl2br(e($sp->moTa ?? 'No detailed description available for this textbook.')) ?>
      </p>
    </div>
  </article>

  <!-- Customer Reviews & Ratings Section -->
  <section id="reviews" style="margin-top: 45px; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 28px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
      <div>
        <h2 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">
          ⭐ Customer Reviews &amp; Ratings
        </h2>
        <span style="font-size: 0.9rem; color: #64748b;">
          Based on <?= $tongDanhGia ?> verified student feedback
        </span>
      </div>

      <div style="display: flex; align-items: center; gap: 12px; background: #fffbeb; border: 1px solid #fde68a; padding: 10px 18px; border-radius: 10px;">
        <span style="font-size: 2rem; font-weight: 800; color: #b45309; line-height: 1;">
          <?= number_format($diemTrungBinh, 1) ?>
        </span>
        <div>
          <div style="color: #f59e0b; font-size: 1.15rem; line-height: 1;">
            <?= str_repeat('★', (int)round($diemTrungBinh)) . str_repeat('☆', 5 - (int)round($diemTrungBinh)) ?>
          </div>
          <span style="font-size: 0.78rem; font-weight: 700; color: #92400e;">Overall Score</span>
        </div>
      </div>
    </div>

    <!-- Flash message for reviews -->
    <?php if ($thongBao): ?>
      <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
        ✓ <?= e($thongBao) ?>
      </div>
    <?php endif; ?>

    <?php if ($thongBaoLoi): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
        ⚠ <?= e($thongBaoLoi) ?>
      </div>
    <?php endif; ?>

    <!-- Two-column reviews area: Reviews List (left) + Write Review Form (right) -->
    <div style="display: grid; grid-template-columns: 3fr 2fr; gap: 30px; align-items: flex-start;">
      
      <!-- Left: Existing Reviews List -->
      <div>
        <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #1e293b;">
          Reviews from Students (<?= $tongDanhGia ?>)
        </h3>

        <?php if (empty($dsDanhGia)): ?>
          <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 25px; text-align: center;">
            <p style="font-size: 1.8rem; margin: 0 0 6px 0;">✍️</p>
            <p style="color: #64748b; margin: 0 0 10px 0;">No reviews written for this textbook yet. Be the first to share your thoughts!</p>
          </div>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($dsDanhGia as $dg): ?>
              <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; justify-content: center;">
                      <?= mb_strtoupper(mb_substr($dg['ho_ten'], 0, 1)) ?>
                    </div>
                    <div>
                      <strong style="color: #0f172a; font-size: 0.95rem;"><?= e($dg['ho_ten']) ?></strong>
                      <?php if (!empty($dg['da_mua_hang'])): ?>
                        <span style="background: #ecfdf5; color: #047857; font-size: 0.72rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px;">✓ Verified Buyer</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div style="font-size: 0.8rem; color: #94a3b8;">
                    <?= e(date('M d, Y', strtotime($dg['ngay_tao']))) ?>
                  </div>
                </div>

                <div style="color: #f59e0b; font-size: 0.92rem; margin-bottom: 4px;">
                  <?= str_repeat('★', (int)$dg['so_sao']) . str_repeat('☆', 5 - (int)$dg['so_sao']) ?>
                </div>

                <p style="margin: 0; color: #334155; font-size: 0.92rem; line-height: 1.5;">
                  <?= nl2br(e($dg['noi_dung'])) ?>
                </p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right: Write a Review Form -->
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px;">
        <h3 style="margin: 0 0 10px 0; font-size: 1.1rem; color: #0f172a;">
          Write a Review
        </h3>
        <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 16px 0;">
          Share your feedback on the content quality, print clarity, and textbook usefulness.
        </p>

        <?php if ($u): ?>
          <form method="POST" action="chi-tiet.php?id=<?= $sp->id ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action_danh_gia" value="1">

            <div style="margin-bottom: 14px;">
              <label for="so_sao" style="display: block; font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 4px;">
                Your Rating:
              </label>
              <select id="so_sao" name="so_sao" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; font-weight: 600; background: #ffffff;">
                <option value="5">⭐⭐⭐⭐⭐ (5/5 Excellent)</option>
                <option value="4">⭐⭐⭐⭐ (4/5 Very Good)</option>
                <option value="3">⭐⭐⭐ (3/5 Average)</option>
                <option value="2">⭐⭐ (2/5 Below Average)</option>
                <option value="1">⭐ (1/5 Poor)</option>
              </select>
            </div>

            <div style="margin-bottom: 16px;">
              <label for="noi_dung" style="display: block; font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 4px;">
                Review Details:
              </label>
              <textarea id="noi_dung" name="noi_dung" rows="4" required placeholder="Write your textbook review..." style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;"></textarea>
            </div>

            <button type="submit" class="nut nut--chinh" style="width: 100%; padding: 10px; font-size: 0.92rem; font-weight: 700; border-radius: 6px;">
              Submit Review &rarr;
            </button>
          </form>
        <?php else: ?>
          <div style="text-align: center; padding: 15px 0;">
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 12px;">
              Please sign in with your customer account to rate and review this book.
            </p>
            <a href="dang-nhap.php?yeu_cau=chi-tiet.php?id=<?= $sp->id ?>" class="nut nut--chinh" style="padding: 8px 18px; font-size: 0.88rem; border-radius: 6px;">
              Sign In to Review
            </a>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </section>

  <!-- Khối sách liên quan cùng chuyên ngành (Tối đa 4 cuốn) -->
  <?php if (!empty($sachLienQuan)): ?>
    <section style="margin-top: 50px;">
      <div class="section-tmdt-head">
        <h2 class="section-tmdt-head__title">
          <span>📚</span> Related Textbooks in this Category
        </h2>
      </div>

      <div class="grid-books-tmdt">
        <?php foreach ($sachLienQuan as $lq): 
          $daThichLq = laYeuThich((int)$lq->id);
          $giaGocLq = round($lq->gia * 1.25, -3);
        ?>
          <article class="the" data-id="<?= $lq->id ?>">
            <figure class="the__hinh-anh">
              <span class="the__the-loai-badge" style="background:#1a56db;"><?= e(layTenDanhMucEn($lq->danhMucId, $lq->tenDanhMuc ?? 'Textbook')) ?></span>
              <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThichLq ? 'da-thich' : '' ?>" data-id="<?= $lq->id ?>" role="button">
                <?= $daThichLq ? '❤️' : '🤍' ?>
              </button>
              <img src="<?= e(anhSach($lq->anh)) ?>" alt="Cover <?= e($lq->ten) ?>" width="175" height="225" loading="lazy">
            </figure>
            <div class="the__than">
              <h3 class="the__tieu-de"><a href="chi-tiet.php?id=<?= $lq->id ?>"><?= e($lq->ten) ?></a></h3>
              <div class="the__gia-row">
                <span class="the__gia"><?= vnd($lq->gia) ?></span>
                <span class="the__gia-goc"><?= vnd($giaGocLq) ?></span>
              </div>
              <div class="the__chan">
                <div class="the__chan-hang1">
                  <form method="POST" action="gio-hang.php" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="mua_ngay">
                    <input type="hidden" name="id" value="<?= $lq->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-mua-ngay">⚡ Buy Now</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $lq->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Cart</button>
                  </form>
                </div>
                <div class="the__chan-hang2">
                  <a href="chi-tiet.php?id=<?= $lq->id ?>" class="btn-chi-tiet">🔍 Details</a>
                  <a href="yeu-thich.php?action=toggle&id=<?= $lq->id ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'chi-tiet.php?id=' . $sp->id) ?>" class="nut-bam-yeu-thich <?= $daThichLq ? 'da-thich' : '' ?>" data-id="<?= $lq->id ?>" role="button">
                    <?= $daThichLq ? '❤️ Saved' : '🤍 Wishlist' ?>
                  </a>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>

<script>
  // Đồng bộ số lượng nhập vào form Mua ngay và form Thêm vào giỏ
  const oNhap = document.getElementById('so-luong-nhap');
  const oMuaNgay = document.getElementById('so-luong-mua-ngay');
  const oThemGio = document.getElementById('so-luong-them-gio');

  if (oNhap && oMuaNgay && oThemGio) {
    oNhap.addEventListener('input', function() {
      const val = parseInt(this.value, 10) || 1;
      oMuaNgay.value = val;
      oThemGio.value = val;
    });
  }
</script>

<script src="js/gio-hang.js?v=<?= filemtime(__DIR__ . '/js/gio-hang.js') ?>"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
