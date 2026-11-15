<?php
/**
 * index.php - Trang chủ website Nhà sách trực tuyến BookNest
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web (CSRF Token, Secure Session, CSP)
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham(db());

// Fetch books from MySQL via KhoSanPham
$sachMoi = $kho->sanPhamMoi(10);
$sachBanChay = $kho->sanPhamBanChay(5);
$sachGoiY = $kho->sanPhamGoiY(5);

// Lấy 4 cuốn sách cho khối Flash Sale
$sachFlashSale = array_slice($sachMoi, 0, 4);

// Đọc cookie "da_xem" (ID các sách đã xem gần nhất, cách nhau bởi dấu phẩy)
$daXem = [];
if (!empty($_COOKIE['da_xem'])) {
    $rawIds = explode(',', $_COOKIE['da_xem']);
    $ids = array_filter(array_map('intval', $rawIds));
    if (!empty($ids)) {
        $daXem = $kho->layTheoDanhSachId(array_slice($ids, 0, 4));
    }
}

$tieuDe = 'Home | BookNest - UED Student Bookstore';
$trang = 'index';
$moTaTrang = 'BookNest - Online bookstore providing academic textbooks, Computer Science, and Mathematics for students of University of Science and Education - The University of Da Nang.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">

  <!-- 1. HERO BANNER SÀN THƯƠNG MẠI ĐIỆN TỬ -->
  <section class="hero-tmdt-grid">
    <!-- Banner chính lớn bên trái -->
    <div class="banner-chinh-tmdt" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; gap: 30px; position: relative;">
      <div style="flex: 1; max-width: 540px; z-index: 2;">
        <div class="banner-chinh-tmdt__tag" style="display: inline-flex; align-items: center; gap: 8px;">
          <span class="pulsing-dot"></span>
          <span>🔥 STUDENT BOOK FAIR 2026 &bull; UP TO 25% OFF</span>
        </div>
        <h1 class="banner-chinh-tmdt__title">
          Standardized Textbooks<br><span>University of Science and Education</span>
        </h1>
        <p class="banner-chinh-tmdt__desc">
          Over 50+ officially vetted textbooks in Computer Science, Economics, and Life Skills. Fast delivery directly to Lien Chieu dormitories within 24 hours.
        </p>
        <div class="banner-chinh-tmdt__cta" style="margin-bottom: 20px;">
          <a href="danh-sach.php" class="btn-banner-mua">⚡ Shop Textbooks Now &rarr;</a>
          <a href="gioi-thieu.php" class="btn-banner-nhom">👥 Team 12 Project</a>
        </div>
        <div style="display: flex; gap: 14px; flex-wrap: wrap; font-size: 0.8rem; color: #cbd5e1;">
          <span>✓ 100% Authentic</span>
          <span>&bull;</span>
          <span>✓ 7-Day Returns</span>
          <span>&bull;</span>
          <span>✓ 24h Dorm Delivery</span>
        </div>
      </div>

      <!-- Khối bìa sách nổi bật 3D bên phải Hero (Chi-hien-tren-may-tinh) -->
      <div class="chi-hien-tren-may-tinh" style="position: relative; width: 220px; height: 260px; flex-shrink: 0; z-index: 2; align-items: center; justify-content: center;">
        <!-- Sách 1 nghiêng trái -->
        <img src="uploads/book_2b9e2c40fe3ad2b0.png" alt="Featured textbook" width="130" height="175" style="position: absolute; left: 0; top: 35px; border-radius: 8px; box-shadow: -10px 15px 30px rgba(0,0,0,0.4); transform: rotate(-8deg); border: 2px solid rgba(255,255,255,0.2); transition: all 0.3s ease; object-fit: cover;">
        <!-- Sách 2 nghiêng phải -->
        <img src="uploads/book_479148959ef0214f.png" alt="Featured textbook" width="130" height="175" style="position: absolute; right: 0; top: 45px; border-radius: 8px; box-shadow: 10px 15px 30px rgba(0,0,0,0.4); transform: rotate(10deg); border: 2px solid rgba(255,255,255,0.2); transition: all 0.3s ease; object-fit: cover;">
        <!-- Sách 3 chính giữa nổi bật nhất -->
        <img src="uploads/book_53ddc2905e87fd3f.png" alt="Featured textbook" width="145" height="200" style="position: absolute; left: 35px; top: 10px; border-radius: 10px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); z-index: 3; border: 2px solid #ffffff; transition: all 0.3s ease; object-fit: cover;">
      </div>
    </div>

    <!-- Cột phụ bên phải: Voucher sinh viên & Smart Weather Assistant -->
    <div class="hero-tmdt-phu">
      <!-- Khối Voucher độc quyền sinh viên UED -->
      <div class="card-voucher-sv">
        <div class="card-voucher-sv__head">
          <strong style="color:#0f172a;font-size:0.95rem;">🎁 Exclusive Student Voucher</strong>
          <span class="card-voucher-sv__badge">NEW SEMESTER</span>
        </div>
        <div class="card-voucher-sv__code">UED2026</div>
        <p class="card-voucher-sv__note">
          Instant <strong>25,000 VNĐ discount</strong> on orders of 2 or more textbooks. Valid for all UED students!
        </p>
      </div>

      <!-- Smart Weather Assistant từ Open-Meteo REST API -->
      <div class="card-weather-tmdt" id="khoi-thoi-tiet-dong" aria-live="polite">
        <div class="card-weather-tmdt__head">
          <span>🌤️</span>
          <span>Da Nang Weather Today</span>
        </div>
        <div class="card-weather-tmdt__stats">
          <div>Temperature: <strong id="thoi-tiet-nhiet-do">26 °C</strong></div>
          <div>Humidity: <strong id="thoi-tiet-do-am">85%</strong></div>
        </div>
        <p class="card-weather-tmdt__tip" id="thoi-tiet-loi-khuyen">
          💡 <em>Cool and pleasant weather in Da Nang, great for reading and practicing web programming at the library!</em>
        </p>
      </div>
    </div>
  </section>

  <!-- 2. THANH CAM KẾT SÀN (TRUST BADGES 4 TRỤ CỘT) -->
  <section class="trust-badges-bar">
    <div class="trust-item">
      <div class="trust-item__icon">🚚</div>
      <div>
        <p class="trust-item__title">Fast 24h Dorm Delivery</p>
        <p class="trust-item__sub">Delivered to room or faculty lobby</p>
      </div>
    </div>
    <div class="trust-item">
      <div class="trust-item__icon">🛡️</div>
      <div>
        <p class="trust-item__title">100% Copyright Standard</p>
        <p class="trust-item__sub">Vetted by experienced UED professors</p>
      </div>
    </div>
    <div class="trust-item">
      <div class="trust-item__icon">🔄</div>
      <div>
        <p class="trust-item__title">7-Day Free Returns</p>
        <p class="trust-item__sub">Instant replacement for defective pages</p>
      </div>
    </div>
    <div class="trust-item">
      <div class="trust-item__icon">🏷️</div>
      <div>
        <p class="trust-item__title">25% Student Subsidy</p>
        <p class="trust-item__sub">Special discounted prices for UED students</p>
      </div>
    </div>
  </section>

  <!-- 3. KHỐI FLASH SALE GIÁ SỐC (REAL E-COMMERCE FLASH SALE) -->
  <section class="flash-sale-box">
    <div class="flash-sale-header">
      <div class="flash-sale-title">
        <span>⚡ TODAY'S FLASH SALE</span>
      </div>
      <div class="flash-timer">
        <span>Ends in:</span>
        <span class="timer-pill" id="fs-gio">02</span> :
        <span class="timer-pill" id="fs-phut">45</span> :
        <span class="timer-pill" id="fs-giay">18</span>
      </div>
    </div>

    <!-- Lưới sách Flash Sale -->
    <?php if (empty($sachFlashSale)): ?>
      <div style="text-align: center; padding: 30px 20px; background: rgba(255,255,255,0.7); border-radius: 12px; margin: 10px 0;">
        <p style="font-size: 1.8rem; margin: 0 0 6px 0;">⚡</p>
        <p style="margin: 0; color: #64748b; font-weight: 600;">No products currently in Flash Sale. Please check back soon!</p>
      </div>
    <?php else: ?>
    <div class="grid-books-tmdt">
      <?php foreach ($sachFlashSale as $sp): 
        $daThich = laYeuThich((int)$sp->id);
        $giaGoc = round($sp->gia * 1.25, -3); // Giá niêm yết trước giảm
      ?>
        <article class="the" data-id="<?= $sp->id ?>">
          <figure class="the__hinh-anh">
            <span class="the__the-loai-badge">-25%</span>
            <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button" aria-label="Save <?= e($sp->ten) ?>" title="<?= $daThich ? 'Remove favorite' : 'Add to favorites' ?>">
              <?= $daThich ? '❤️' : '🤍' ?>
            </button>
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Cover <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>

          <div class="the__than">
            <span class="the__the-loai"><?= e(layTenDanhMucEn($sp->danhMucId ?? null, $sp->tenDanhMuc ?? 'Textbook')) ?></span>
            <h3 class="the__tieu-de">
              <a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a>
            </h3>

            <!-- Đánh giá sao & lượt mua -->
            <div class="rating-stars-tmdt">
              <span class="stars">★★★★★</span>
              <span>(4.9) &bull; <?= rand(15, 80) ?> sold</span>
            </div>

            <div style="font-size: 0.76rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
              <span><?= $sp->chuSachId ? '🏪' : '👑' ?></span>
              <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;" title="<?= e($sp->tenChuSach ?: 'BookNest UED') ?>">
                <?= e($sp->tenChuSach ?: 'BookNest UED') ?>
              </span>
            </div>

            <!-- Giá tiền TMĐT -->
            <div class="the__gia-row">
              <span class="the__gia"><?= vnd($sp->gia) ?></span>
              <span class="the__gia-goc"><?= vnd($giaGoc) ?></span>
            </div>

            <!-- Thanh tiến độ Flash Sale -->
            <div class="deal-progress-bar">
              <div class="deal-progress-bar__fill" style="width: <?= rand(60, 90) ?>%;"></div>
              <div class="deal-progress-bar__text">HOT SELLER 🔥</div>
            </div>

            <p class="the__ton-kho <?= $sp->tonKho > 0 ? 'the__ton-kho--con' : 'the__ton-kho--het' ?>">
              <?= $sp->tonKho > 0 ? '✓ ' . $sp->tonKho . ' copies left' : '⚠ Out of stock' ?>
            </p>

            <!-- KHU VỰC 4 NÚT HÀNH ĐỘNG (CSRF PROTECTED) -->
            <div class="the__chan">
              <!-- Hàng 1: Nút Mua ngay (⚡ Buy Now) & Nút Thêm vào giỏ (🛒 + Cart) -->
              <?php if ($sp->tonKho > 0): ?>
                <div class="the__chan-hang1">
                  <form method="POST" action="gio-hang.php" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="mua_ngay">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-mua-ngay">⚡ Buy Now</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Cart</button>
                  </form>
                </div>
              <?php else: ?>
                <div style="color:#e02424;font-size:0.82rem;font-weight:700;text-align:center;padding:4px 0;background:#fdf2f2;border-radius:4px;">Out of stock</div>
              <?php endif; ?>

              <!-- Hàng 2: Chi tiết (🔍 Details) & Yêu thích (🤍 Wishlist) -->
              <div class="the__chan-hang2">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" class="btn-chi-tiet">🔍 Details</a>
                <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=index.php" class="nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                  <?= $daThich ? '❤️ Saved' : '🤍 Wishlist' ?>
                </a>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <!-- 4. KHỐI TẤT CẢ SÁCH MỚI CẬP NHẬT TRÊN KỆ -->
  <section>
    <div class="section-tmdt-head">
      <h2 class="section-tmdt-head__title">
        <span>📚</span> Latest Textbooks on Shelves
      </h2>
      <a href="danh-sach.php" class="section-tmdt-head__link">
        <span>View all textbooks</span>
        <span>&rarr;</span>
      </a>
    </div>

    <?php if (empty($sachMoi)): ?>
      <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: 16px; border: 1.5px dashed #cbd5e1; box-shadow: var(--shadow-card); margin-bottom: 30px;">
        <p style="font-size: 3.5rem; margin: 0 0 12px 0;">📚</p>
        <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 1.3rem;">No textbooks currently on shelves</h3>
        <p style="color: #64748b; max-width: 520px; margin: 0 auto 24px auto; font-size: 0.95rem; line-height: 1.6;">
          All previous inventory has been cleared. Log in with a <strong>Seller</strong> account to list your first textbook on the store!
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
          <a href="dang-nhap.php" class="nut nut--chinh" style="padding: 10px 22px; text-decoration: none;">🔑 Sign In as Seller</a>
          <a href="dang-ky.php" class="nut nut--vien" style="padding: 10px 20px; text-decoration: none;">📝 Register New Seller Account</a>
        </div>
      </div>
    <?php else: ?>
    <div class="grid-books-tmdt">
      <?php foreach ($sachMoi as $sp): 
        $daThich = laYeuThich((int)$sp->id);
        $giaGoc = round($sp->gia * 1.2, -3);
      ?>
        <article class="the" data-id="<?= $sp->id ?>">
          <figure class="the__hinh-anh">
            <span class="the__the-loai-badge" style="background:#1a56db;"><?= e(layTenDanhMucEn($sp->danhMucId ?? null, $sp->tenDanhMuc ?? 'Textbook')) ?></span>
            <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button" aria-label="Save <?= e($sp->ten) ?>" title="<?= $daThich ? 'Remove favorite' : 'Add to favorites' ?>">
              <?= $daThich ? '❤️' : '🤍' ?>
            </button>
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Cover <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>

          <div class="the__than">
            <span class="the__the-loai"><?= e(layTenDanhMucEn($sp->danhMucId ?? null, $sp->tenDanhMuc ?? 'Category')) ?></span>
            <h3 class="the__tieu-de">
              <a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a>
            </h3>

            <div class="rating-stars-tmdt">
              <span class="stars">★★★★★</span>
              <span>(4.8) &bull; <?= rand(10, 65) ?> sold</span>
            </div>

            <div style="font-size: 0.76rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
              <span><?= $sp->chuSachId ? '🏪' : '👑' ?></span>
              <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;" title="<?= e($sp->tenChuSach ?: 'BookNest UED') ?>">
                <?= e($sp->tenChuSach ?: 'BookNest UED') ?>
              </span>
            </div>

            <div class="the__gia-row">
              <span class="the__gia"><?= vnd($sp->gia) ?></span>
              <span class="the__gia-goc"><?= vnd($giaGoc) ?></span>
            </div>

            <p class="the__ton-kho <?= $sp->tonKho > 0 ? 'the__ton-kho--con' : 'the__ton-kho--het' ?>">
              <?= $sp->tonKho > 0 ? '✓ ' . $sp->tonKho . ' copies left' : '⚠ Out of stock' ?>
            </p>

            <!-- 4 NÚT HÀNH ĐỘNG (CSRF PROTECTED) -->
            <div class="the__chan">
              <?php if ($sp->tonKho > 0): ?>
                <div class="the__chan-hang1">
                  <form method="POST" action="gio-hang.php" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="mua_ngay">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-mua-ngay">⚡ Buy Now</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Cart</button>
                  </form>
                </div>
              <?php else: ?>
                <div style="color:#e02424;font-size:0.82rem;font-weight:700;text-align:center;padding:4px 0;background:#fdf2f2;border-radius:4px;">Out of stock</div>
              <?php endif; ?>

              <div class="the__chan-hang2">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" class="btn-chi-tiet">🔍 Details</a>
                <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=index.php" class="nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                  <?= $daThich ? '❤️ Saved' : '🤍 Wishlist' ?>
                </a>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <!-- 5. BEST-SELLING TEXTBOOKS -->
  <?php if (!empty($sachBanChay)): ?>
    <section style="margin-top: 40px;">
      <div class="section-tmdt-head">
        <h2 class="section-tmdt-head__title">
          <span>🔥</span> Best-Selling Textbooks
        </h2>
        <a href="danh-sach.php?sort=ban-chay" class="section-tmdt-head__link">
          <span>View all bestsellers</span>
          <span>&rarr;</span>
        </a>
      </div>

      <div class="grid-books-tmdt">
        <?php foreach ($sachBanChay as $sp): 
          $daThichBc = laYeuThich((int)$sp->id);
          $giaGocBc = round($sp->gia * 1.25, -3);
        ?>
          <article class="the" data-id="<?= $sp->id ?>">
            <figure class="the__hinh-anh">
              <span class="the__the-loai-badge" style="background:#ea580c;">🔥 BESTSELLER</span>
              <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThichBc ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                <?= $daThichBc ? '❤️' : '🤍' ?>
              </button>
              <img src="<?= e(anhSach($sp->anh)) ?>" alt="Cover <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
            </figure>
            <div class="the__than">
              <span class="the__the-loai"><?= e(layTenDanhMucEn($sp->danhMucId ?? null, $sp->tenDanhMuc ?? 'Textbook')) ?></span>
              <h3 class="the__tieu-de"><a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a></h3>
              <div class="rating-stars-tmdt"><span class="stars">★★★★★</span><span>(4.9) &bull; Popular</span></div>
              <div class="the__gia-row">
                <span class="the__gia"><?= vnd($sp->gia) ?></span>
                <span class="the__gia-goc"><?= vnd($giaGocBc) ?></span>
              </div>
              <p class="the__ton-kho <?= $sp->tonKho > 0 ? 'the__ton-kho--con' : 'the__ton-kho--het' ?>">
                <?= $sp->tonKho > 0 ? '✓ ' . $sp->tonKho . ' copies left' : '⚠ Out of stock' ?>
              </p>
              <div class="the__chan">
                <?php if ($sp->tonKho > 0): ?>
                  <div class="the__chan-hang1">
                    <form method="POST" action="gio-hang.php" style="margin:0;flex:1;">
                      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                      <input type="hidden" name="action" value="mua_ngay">
                      <input type="hidden" name="id" value="<?= $sp->id ?>">
                      <input type="hidden" name="soLuong" value="1">
                      <button type="submit" class="btn-mua-ngay">⚡ Buy Now</button>
                    </form>
                    <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                      <input type="hidden" name="action" value="them">
                      <input type="hidden" name="id" value="<?= $sp->id ?>">
                      <input type="hidden" name="soLuong" value="1">
                      <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Cart</button>
                    </form>
                  </div>
                <?php endif; ?>
                <div class="the__chan-hang2">
                  <a href="chi-tiet.php?id=<?= $sp->id ?>" class="btn-chi-tiet">🔍 Details</a>
                  <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=index.php" class="nut-bam-yeu-thich <?= $daThichBc ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                    <?= $daThichBc ? '❤️ Saved' : '🤍 Wishlist' ?>
                  </a>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- 6. KHỐI SÁCH ĐÃ XEM GẦN ĐÂY -->
  <?php if (!empty($daXem)): ?>
    <section style="margin-top: 30px;">
      <div class="section-tmdt-head">
        <h2 class="section-tmdt-head__title">
          <span>🕒</span> Recently Viewed Textbooks
        </h2>
      </div>

      <div class="grid-books-tmdt">
        <?php foreach ($daXem as $sp): 
          $daThichDx = laYeuThich((int)$sp->id);
        ?>
          <article class="the" data-id="<?= $sp->id ?>">
            <figure class="the__hinh-anh">
              <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThichDx ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                <?= $daThichDx ? '❤️' : '🤍' ?>
              </button>
              <img src="<?= e(anhSach($sp->anh)) ?>" alt="Cover <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
            </figure>
            <div class="the__than">
              <h3 class="the__tieu-de">
                <a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a>
              </h3>
              
              <div class="the__gia-row">
                <span class="the__gia"><?= vnd($sp->gia) ?></span>
              </div>

              <div class="the__chan">
                <div class="the__chan-hang1">
                  <form method="POST" action="gio-hang.php" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="mua_ngay">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-mua-ngay">⚡ Buy Now</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Cart</button>
                  </form>
                </div>
                <div class="the__chan-hang2">
                  <a href="chi-tiet.php?id=<?= $sp->id ?>" class="btn-chi-tiet">🔍 Details</a>
                  <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=index.php" class="nut-bam-yeu-thich <?= $daThichDx ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                    <?= $daThichDx ? '❤️ Saved' : '🤍 Wishlist' ?>
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

<!-- Timer đếm ngược Flash Sale & Weather Script -->
<script>
  // Timer đếm ngược Flash Sale thời gian thực
  function capNhatCountdown() {
    const elGiay = document.getElementById('fs-giay');
    const elPhut = document.getElementById('fs-phut');
    const elGio  = document.getElementById('fs-gio');
    if (!elGiay || !elPhut || !elGio) return;

    let s = parseInt(elGiay.innerText, 10);
    let m = parseInt(elPhut.innerText, 10);
    let h = parseInt(elGio.innerText, 10);

    s--;
    if (s < 0) {
      s = 59;
      m--;
      if (m < 0) {
        m = 59;
        h = (h > 0) ? h - 1 : 2;
      }
    }

    elGiay.innerText = s < 10 ? '0' + s : s;
    elPhut.innerText = m < 10 ? '0' + m : m;
    elGio.innerText  = h < 10 ? '0' + h : h;
  }
  setInterval(capNhatCountdown, 1000);
</script>

<script type="module" src="js/trang-chu.js"></script>
<script src="js/gio-hang.js?v=<?= filemtime(__DIR__ . '/js/gio-hang.js') ?>"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
