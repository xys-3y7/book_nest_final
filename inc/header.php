<?php
/**
 * inc/header.php - Phần đầu trang chuẩn Sàn Thương mại Điện tử thực thụ
 * Nhóm 12 - Lớp 24CNTT3 - Khoa Toán & Tin học, Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web (CSRF Protection, CSP, Session Security)
 */

$goc   ??= '';
$trang ??= '';
$tieuDe ??= 'Home';
$moTaTrang ??= 'BookNest - Online bookstore offering academic textbooks, Computer Science, and Mathematics for students of University of Science and Education - The University of Da Nang.';

$u = nguoiDung();
$gio = new \App\Services\GioHang();
$khoDungChung = new \App\Data\KhoSanPham(db());
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($moTaTrang) ?>">
  <meta name="csrf-token" content="<?= csrfToken() ?>">
  <title><?= e($tieuDe) ?> | BookNest - UED Student Bookstore</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Prompt:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Cache-busting URL to ensure fresh CSS -->
  <link rel="stylesheet" href="<?= $goc ?>css/style.css?v=<?= time() ?>">
  <script>window.BOOKNEST_UID = <?= $u ? (int)$u['id'] : 0 ?>;</script>
</head>
<body class="trang-grid">

  <!-- 1. TOP BAR -->
  <div class="top-bar-tmdt">
    <div class="khung-chua top-bar-tmdt__inner">
      <div class="top-bar-tmdt__left">
        <span class="top-bar-tmdt__tag">Student Deals</span>
        <span>New Semester Book Fair 2026: Up to <strong>25% OFF</strong> all textbooks!</span>
      </div>
      <div class="top-bar-tmdt__right chi-hien-tren-may-tinh">
        <?php if ($u): ?>
          <a href="<?= $goc ?>tai-khoan.php" class="top-bar-tmdt__link" style="color:#ea580c;font-weight:700;">👤 My Account</a>
          <span>|</span>
          <a href="<?= $goc ?>don-hang.php" class="top-bar-tmdt__link">📦 My Orders</a>
        <?php else: ?>
          <a href="<?= $goc ?>don-hang.php" class="top-bar-tmdt__link">📦 Track Order</a>
        <?php endif; ?>
        <span>|</span>
        <a href="<?= $goc ?>lien-he.php" class="top-bar-tmdt__link">💬 Student Support</a>
        <span>|</span>
        <span class="top-bar-tmdt__link">📍 UED - University of Da Nang</span>
        <span>|</span>
        <a href="?lang=en" class="top-bar-tmdt__link" style="font-weight: <?= laTiengAnh() ? '800;color:#ea580c' : '500' ?>;" title="Switch to English">🇬🇧 EN</a>
        <span>/</span>
        <a href="?lang=vi" class="top-bar-tmdt__link" style="font-weight: <?= !laTiengAnh() ? '800;color:#ea580c' : '500' ?>;" title="Chuyển sang Tiếng Việt">🇻🇳 VI</a>
      </div>
    </div>
  </div>

  <!-- 2. MAIN HEADER -->
  <header class="header-tmdt">
    <div class="khung-chua header-tmdt__inner">
      
      <!-- Logo Brand -->
      <a href="<?= $goc ?>index.php" class="header-tmdt__logo">
        <div class="header-tmdt__logo-badge">📚</div>
        <div class="header-tmdt__logo-text">
          <div class="header-tmdt__logo-brand">Book<span>Nest</span></div>
          <span class="header-tmdt__logo-sub">UED Student Bookstore</span>
        </div>
      </a>

      <!-- Search bar -->
      <div class="header-tmdt__search">
        <form action="<?= $goc ?>danh-sach.php" method="GET" class="header-tmdt__search-form">
          <span class="header-tmdt__search-icon">🔍</span>
          <input type="text" name="q" value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>" class="header-tmdt__search-input" placeholder="Search textbooks, titles, authors..." autocomplete="off">
          <button type="submit" class="header-tmdt__search-btn">Search</button>
        </form>
      </div>

      <!-- User actions -->
      <div class="header-tmdt__actions">
        
        <!-- Wishlist -->
        <a href="<?= $goc ?>yeu-thich.php" class="header-action-item <?= $trang === 'yeu-thich' ? 'active' : '' ?>" title="My Wishlist">
          <div class="header-action-item__icon-wrap">
            <span class="header-action-item__emoji">❤️</span>
            <span class="header-action-item__badge so-luong-yeu-thich" aria-label="<?= soLuongYeuThich() ?> saved items"><?= soLuongYeuThich() ?></span>
          </div>
          <span class="header-action-item__label chi-hien-tren-may-tinh">Wishlist</span>
        </a>

        <!-- Cart -->
        <a href="<?= $goc ?>gio-hang.php" class="header-action-item <?= $trang === 'gio-hang' ? 'active' : '' ?>" title="Shopping Cart">
          <div class="header-action-item__icon-wrap">
            <span class="header-action-item__emoji">🛒</span>
            <span class="header-action-item__badge so-luong-gio"><?= $gio->soMon() ?></span>
          </div>
          <div class="header-action-item__text chi-hien-tren-may-tinh">
            <span class="header-action-item__label">Cart</span>
            <span class="header-action-item__val"><?= $gio->soMon() > 0 ? vnd($gio->tongTien($khoDungChung)) : '0 VNĐ' ?></span>
          </div>
        </a>

        <!-- User account -->
        <div class="header-tmdt__auth">
          <?php if ($u): ?>
            <div class="header-user-card">
              <div class="header-user-card__avatar">
                <?= mb_strtoupper(mb_substr($u['ho_ten'], 0, 1)) ?>
              </div>
              <div class="header-user-card__info chi-hien-tren-may-tinh">
                <span class="header-user-card__name"><?= e($u['ho_ten']) ?></span>
                <span class="header-user-card__role">
                  <?= $u['vai_tro'] === 'quan_tri' ? 'Administrator' : ($u['vai_tro'] === 'chu_sach' ? 'Book Seller' : 'Student') ?>
                </span>
              </div>
              
              <div class="header-user-card__actions">
                <?php if ($u['vai_tro'] === 'quan_tri'): ?>
                  <a href="<?= $goc ?>quan-tri.php" class="btn-role-chip btn-role-chip--admin" title="Admin Dashboard">⚙️ Admin</a>
                <?php elseif ($u['vai_tro'] === 'chu_sach'): ?>
                  <a href="<?= $goc ?>chu-sach.php" class="btn-role-chip btn-role-chip--seller" title="Seller Hub">🏪 Seller</a>
                <?php else: ?>
                  <a href="<?= $goc ?>tai-khoan.php" class="btn-role-chip btn-role-chip--user" title="Customer Dashboard">👤 Account</a>
                <?php endif; ?>

                <form action="<?= $goc ?>dang-xuat.php" method="POST" style="margin:0;display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <button type="submit" class="btn-clean-logout" title="Sign out">Logout</button>
                </form>
              </div>
            </div>
          <?php else: ?>
            <a href="<?= $goc ?>dang-nhap.php" class="btn-clean-auth btn-clean-auth--login">Sign In</a>
            <a href="<?= $goc ?>dang-ky.php" class="btn-clean-auth btn-clean-auth--reg chi-hien-tren-may-tinh">Register</a>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </header>

  <!-- 3. NAVIGATION BAR -->
  <nav class="navbar-tmdt" aria-label="Main Navigation">
    <div class="khung-chua navbar-tmdt__inner">
      <div class="navbar-tmdt__left">
        <a href="<?= $goc ?>danh-sach.php" class="btn-danhmuc-all">
          <span>☰</span>
          <span>CATEGORIES</span>
        </a>

        <ul class="navbar-tmdt__menu">
          <li><a href="<?= $goc ?>index.php" <?= $trang === 'index' ? 'class="active"' : '' ?>>Home</a></li>
          <li><a href="<?= $goc ?>danh-sach.php" <?= $trang === 'danh-sach' ? 'class="active"' : '' ?>>All Textbooks</a></li>
          <li><a href="<?= $goc ?>gioi-thieu.php" <?= $trang === 'gioi-thieu' ? 'class="active"' : '' ?>>About Us</a></li>
          <li><a href="<?= $goc ?>lien-he.php" <?= $trang === 'lien-he' ? 'class="active"' : '' ?>>Contact &amp; Support</a></li>
          <?php if ($u && $u['vai_tro'] === 'quan_tri'): ?>
            <li><a href="<?= $goc ?>quan-tri.php" class="nav-role-link nav-role-link--admin">⚙️ Admin Dashboard</a></li>
          <?php elseif ($u && $u['vai_tro'] === 'chu_sach'): ?>
            <li><a href="<?= $goc ?>chu-sach.php" class="nav-role-link nav-role-link--seller">🏪 Seller Portal</a></li>
          <?php elseif ($u): ?>
            <li><a href="<?= $goc ?>tai-khoan.php" <?= $trang === 'tai-khoan' ? 'class="active"' : '' ?>>👤 Customer Hub</a></li>
            <li><a href="<?= $goc ?>don-hang.php" <?= $trang === 'don-hang' ? 'class="active"' : '' ?>>📦 Orders</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="navbar-tmdt__right chi-hien-tren-may-tinh">
        <div class="hotline-chip-clean">
          <span class="hotline-chip-dot"></span>
          <span>Dorm delivery in 24h &bull; Hotline: <strong>0236.3841.323</strong></span>
        </div>
      </div>
    </div>
  </nav>
