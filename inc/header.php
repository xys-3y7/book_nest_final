<?php
/**
 * inc/header.php - Phần đầu trang chuẩn Sàn Thương mại Điện tử thực thụ
 * Nhóm 12 - Lớp 24CNTT3 - Khoa Toán & Tin học, Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web (CSRF Protection, CSP, Session Security)
 */

$goc   ??= '';
$trang ??= '';
$tieuDe ??= 'Trang chủ';
$moTaTrang ??= 'BookNest - Nhà sách trực tuyến chuyên cung cấp giáo trình đại học, sách chuyên ngành CNTT, Toán học cho sinh viên Trường ĐH Sư phạm - ĐH Đà Nẵng.';

$u = nguoiDung();
$gio = new \App\Services\GioHang();
$khoDungChung = new \App\Data\KhoSanPham(db());
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($moTaTrang) ?>">
  <meta name="csrf-token" content="<?= csrfToken() ?>">
  <title><?= e($tieuDe) ?> | BookNest - Nhà sách sinh viên UED</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Cache-busting URL để trình duyệt luôn tải CSS mới nhất -->
  <link rel="stylesheet" href="<?= $goc ?>css/style.css?v=<?= time() ?>">
  <script>window.BOOKNEST_UID = <?= $u ? (int)$u['id'] : 0 ?>;</script>
</head>
<body class="trang-grid">

  <!-- 1. THANH TOP BAR TMĐT TRÊN CÙNG -->
  <div class="top-bar-tmdt">
    <div class="khung-chua top-bar-tmdt__inner">
      <div class="top-bar-tmdt__left">
        <span class="top-bar-tmdt__tag">Ưu đãi SV</span>
        <span>Hội sách học kỳ mới 2026: Giảm đến <strong>25%</strong> toàn bộ giáo trình!</span>
      </div>
      <div class="top-bar-tmdt__right chi-hien-tren-may-tinh">
        <?php if ($u): ?>
          <a href="<?= $goc ?>don-hang.php" class="top-bar-tmdt__link" style="color:#1d4ed8;font-weight:700;">📦 Lịch sử đơn mua</a>
        <?php else: ?>
          <a href="<?= $goc ?>don-hang.php" class="top-bar-tmdt__link">📦 Tra cứu đơn hàng</a>
        <?php endif; ?>
        <span>|</span>
        <a href="<?= $goc ?>lien-he.php" class="top-bar-tmdt__link">💬 Hỗ trợ sinh viên</a>
        <span>|</span>
        <span class="top-bar-tmdt__link">📍 ĐH Sư Phạm - ĐH Đà Nẵng</span>
      </div>
    </div>
  </div>

  <!-- 2. HEADER CHÍNH SÀN THƯƠNG MẠI ĐIỆN TỬ -->
  <header class="header-tmdt">
    <div class="khung-chua header-tmdt__inner">
      
      <!-- Logo Thương hiệu BookNest -->
      <a href="<?= $goc ?>index.php" class="header-tmdt__logo">
        <div class="header-tmdt__logo-badge">📚</div>
        <div class="header-tmdt__logo-text">
          <div class="header-tmdt__logo-brand">Book<span>Nest</span></div>
          <span class="header-tmdt__logo-sub">Nhà sách sinh viên UED</span>
        </div>
      </a>

      <!-- Thanh tìm kiếm kiểu Shopee/Tiki/Fahasa -->
      <div class="header-tmdt__search">
        <form action="<?= $goc ?>danh-sach.php" method="GET" class="header-tmdt__search-form">
          <select name="dm" class="header-tmdt__search-cat chi-hien-tren-may-tinh">
            <option value="">Tất cả danh mục</option>
            <option value="1" <?= (isset($_GET['dm']) && $_GET['dm'] == '1') ? 'selected' : '' ?>>CNTT & Lập trình</option>
            <option value="2" <?= (isset($_GET['dm']) && $_GET['dm'] == '2') ? 'selected' : '' ?>>Kinh tế & Khởi nghiệp</option>
            <option value="3" <?= (isset($_GET['dm']) && $_GET['dm'] == '3') ? 'selected' : '' ?>>Văn học & Nghệ thuật</option>
            <option value="4" <?= (isset($_GET['dm']) && $_GET['dm'] == '4') ? 'selected' : '' ?>>Kỹ năng sống</option>
          </select>
          <input type="text" name="q" value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>" class="header-tmdt__search-input" placeholder="Tìm kiếm tên sách, tác giả, giáo trình CNTT, Toán học..." autocomplete="off">
          <button type="submit" class="header-tmdt__search-btn">
            <span>🔍</span>
            <span class="chi-hien-tren-may-tinh">Tìm kiếm</span>
          </button>
        </form>

        <div class="header-tmdt__search-tags chi-hien-tren-may-tinh">
          <span>Gợi ý:</span>
          <a href="<?= $goc ?>danh-sach.php?q=PHP">Lập trình PHP</a>
          <a href="<?= $goc ?>danh-sach.php?q=MySQL">Cơ sở dữ liệu</a>
          <a href="<?= $goc ?>danh-sach.php?q=Toán">Toán cao cấp</a>
          <a href="<?= $goc ?>danh-sach.php?q=Web">Thiết kế Web</a>
        </div>
      </div>

      <!-- Cụm tác vụ người dùng: Yêu thích, Giỏ hàng, Tài khoản -->
      <div class="header-tmdt__actions">
        
        <!-- Yêu thích (Wishlist) -->
        <a href="<?= $goc ?>yeu-thich.php" class="action-pill-tmdt <?= $trang === 'yeu-thich' ? 'active' : '' ?>" title="Danh sách yêu thích">
          <div class="action-pill-tmdt__icon">
            <span>❤️</span>
            <span class="action-pill-tmdt__badge so-luong-yeu-thich"><?= soLuongYeuThich() ?></span>
          </div>
          <div class="action-pill-tmdt__text chi-hien-tren-may-tinh">
            <span class="action-pill-tmdt__title">Quan tâm</span>
            <span class="action-pill-tmdt__val">Yêu thích</span>
          </div>
        </a>

        <!-- Giỏ hàng (Cart) -->
        <a href="<?= $goc ?>gio-hang.php" class="action-pill-tmdt <?= $trang === 'gio-hang' ? 'active' : '' ?>" title="Giỏ hàng của bạn">
          <div class="action-pill-tmdt__icon">
            <span>🛒</span>
            <span class="action-pill-tmdt__badge so-luong-gio"><?= $gio->soMon() ?></span>
          </div>
          <div class="action-pill-tmdt__text chi-hien-tren-may-tinh">
            <span class="action-pill-tmdt__title">Giỏ hàng</span>
            <span class="action-pill-tmdt__val"><?= vnd($gio->tongTien($khoDungChung)) ?></span>
          </div>
        </a>

        <!-- Lịch sử đơn mua (Dành cho tài khoản đã đăng nhập) -->
        <?php if ($u): ?>
          <a href="<?= $goc ?>don-hang.php" class="action-pill-tmdt <?= $trang === 'don-hang' ? 'active' : '' ?>" title="Xem lịch sử mua hàng của bạn">
            <div class="action-pill-tmdt__icon">
              <span>📦</span>
            </div>
            <div class="action-pill-tmdt__text chi-hien-tren-may-tinh">
              <span class="action-pill-tmdt__title">Lịch sử</span>
              <span class="action-pill-tmdt__val">Đơn mua</span>
            </div>
          </a>
        <?php endif; ?>

        <!-- Tài khoản cá nhân -->
        <div class="header-tmdt__auth">
          <?php if ($u): ?>
            <div class="header-user-badge">
              <div class="header-user-avatar"><?= mb_strtoupper(mb_substr($u['ho_ten'], 0, 1)) ?></div>
              <span style="font-weight:700;font-size:0.88rem;" class="chi-hien-tren-may-tinh"><?= e($u['ho_ten']) ?></span>
              <?php if ($u['vai_tro'] === 'quan_tri'): ?>
                <a href="<?= $goc ?>quan-tri.php" style="background:#fef3c7;color:#b45309;font-size:0.75rem;font-weight:700;padding:3px 7px;border-radius:6px;border:1px solid #fde68a;text-decoration:none;display:inline-flex;align-items:center;gap:2px;" title="Quản trị hệ thống">⚙️ Quản trị</a>
              <?php elseif ($u['vai_tro'] === 'chu_sach'): ?>
                <a href="<?= $goc ?>chu-sach.php" style="background:#fef3c7;color:#b45309;font-size:0.75rem;font-weight:700;padding:3px 7px;border-radius:6px;border:1px solid #fde68a;text-decoration:none;display:inline-flex;align-items:center;gap:2px;" title="Kênh người bán">🏪 Quầy sách</a>
              <?php else: ?>
                <a href="<?= $goc ?>don-hang.php" style="background:#eff6ff;color:#1d4ed8;font-size:0.75rem;font-weight:700;padding:3px 7px;border-radius:6px;border:1px solid #bfdbfe;text-decoration:none;display:inline-flex;align-items:center;gap:2px;" title="Xem lịch sử đơn mua">📦 Đơn</a>
              <?php endif; ?>
              <!-- Đăng xuất an toàn bằng POST + CSRF token theo chuẩn Chương 7 -->
              <form action="<?= $goc ?>dang-xuat.php" method="POST" style="margin:0;display:inline;">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <button type="submit" class="header-user-logout" title="Đăng xuất khỏi hệ thống" style="background:none;border:none;cursor:pointer;font-family:inherit;font-size:0.82rem;font-weight:700;color:#e02424;padding:0 4px;">Thoát</button>
              </form>
            </div>
          <?php else: ?>
            <a href="<?= $goc ?>dang-nhap.php" class="btn-auth-tmdt btn-auth-tmdt--login">Đăng nhập</a>
            <a href="<?= $goc ?>dang-ky.php" class="btn-auth-tmdt btn-auth-tmdt--reg chi-hien-tren-may-tinh">Đăng ký</a>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </header>

  <!-- 3. THANH ĐIỀU HƯỚNG DANH MỤC SÀN TMĐT -->
  <nav class="navbar-tmdt" aria-label="Danh mục chính">
    <div class="khung-chua navbar-tmdt__inner">
      <div class="navbar-tmdt__left">
        <a href="<?= $goc ?>danh-sach.php" class="btn-danhmuc-all">
          <span>☰</span>
          <span>Danh Mục Sách</span>
        </a>

        <ul class="navbar-tmdt__menu">
          <li><a href="<?= $goc ?>index.php" <?= $trang === 'index' ? 'class="active"' : '' ?>>Trang chủ</a></li>
          <li><a href="<?= $goc ?>danh-sach.php" <?= $trang === 'danh-sach' ? 'class="active"' : '' ?>>Tất cả giáo trình</a></li>
          <li><a href="<?= $goc ?>gioi-thieu.php" <?= $trang === 'gioi-thieu' ? 'class="active"' : '' ?>>Nhóm 12 (UED)</a></li>
          <li><a href="<?= $goc ?>lien-he.php" <?= $trang === 'lien-he' ? 'class="active"' : '' ?>>Liên hệ &amp; Hỗ trợ</a></li>
          <?php if ($u && $u['vai_tro'] === 'khach_hang'): ?>
            <li><a href="<?= $goc ?>don-hang.php" style="color:#1d4ed8;font-weight:700;" <?= $trang === 'don-hang' ? 'class="active"' : '' ?>>📦 Lịch sử đơn mua</a></li>
          <?php endif; ?>
          <?php if ($u && $u['vai_tro'] === 'chu_sach'): ?>
            <li><a href="<?= $goc ?>chu-sach.php" style="color:#d97706;font-weight:800;" <?= $trang === 'chu-sach' ? 'class="active"' : '' ?>>🏪 Kênh người bán (Book Owner)</a></li>
          <?php endif; ?>
          <?php if ($u && $u['vai_tro'] === 'quan_tri'): ?>
            <li><a href="<?= $goc ?>quan-tri.php" style="color:#d97706;font-weight:800;">⚙️ Quản trị hệ thống</a></li>
            <li><a href="<?= $goc ?>don-hang.php" style="color:#2563eb;font-weight:700;" <?= $trang === 'don-hang' ? 'class="active"' : '' ?>>📦 Quản lý mọi đơn</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="navbar-tmdt__right chi-hien-tren-may-tinh">
        <span class="badge-flash-hot">HOT</span>
        <span>⚡ Giao nhanh KTX trong 24h &bull; Hotline: <strong>0236.3841.323</strong></span>
      </div>
    </div>
  </nav>
