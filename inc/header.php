<?php
/**
 * inc/header.php - Phần đầu trang dùng chung cho mọi trang website BookNest
 * Nhóm 12 - Lớp 24CNTT3 - Chương 7: Bảo mật ứng dụng web
 */

$goc   ??= '';
$trang ??= '';
$tieuDe ??= 'Trang chủ';
$moTaTrang ??= 'BookNest - Nhà sách trực tuyến chuyên cung cấp giáo trình đại học, sách chuyên ngành CNTT, Toán học cho sinh viên Trường ĐH Sư phạm - ĐH Đà Nẵng.';

$gio = new \App\Services\GioHang();
$u = nguoiDung();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($moTaTrang) ?>">
  <meta name="csrf-token" content="<?= csrfToken() ?>">
  <title><?= e($tieuDe) ?> | BookNest - Nhà sách sinh viên UED</title>
  <link rel="stylesheet" href="<?= $goc ?>css/style.css">
</head>
<body class="trang-grid">

  <!-- Header chính BookNest -->
  <header class="phan-dau">
    <!-- 1. Thanh thông báo ưu đãi sinh viên đầu trang -->
    <div class="thanh-thong-bao-top">
      <div class="khung-chua thanh-thong-bao-top__khung">
        <div class="thong-bao-item">
          <span class="thong-bao-badge">🔥 ƯU ĐÃI HỌC KỲ</span>
          <span>Trợ giá sinh viên UED đến <strong>25%</strong> cho toàn bộ giáo trình chuyên ngành</span>
        </div>
        <div class="thong-bao-item thong-bao-item--phu">
          <span>📍 459 Tôn Đức Thắng, Đà Nẵng</span>
          <span class="phoi-phan-cach">&bull;</span>
          <span>⚡ Giao nhanh 24h tại Ký túc xá</span>
        </div>
      </div>
    </div>

    <div class="khung-chua phan-dau__khung">
      <!-- Logo thương hiệu -->
      <div class="phan-dau__logo-nhom">
        <a href="<?= $goc ?>index.php" class="logo-link">
          <img src="<?= $goc ?>images/logo-booknest.png" alt="Logo BookNest - Nhà sách trực tuyến UED" width="210" height="65">
        </a>
        <div class="phan-dau__sub-badge">
          <span class="sub-badge-truong">ĐH SƯ PHẠM ĐÀ NẴNG</span>
          <span class="sub-badge-khoa">KHOA TOÁN - TIN</span>
        </div>
      </div>

      <!-- Ô tìm kiếm thông minh -->
      <form class="phan-dau__tim-kiem" action="<?= $goc ?>danh-sach.php" method="GET" role="search">
        <div class="tim-kiem-hop">
          <span class="icon-tim-kiem" aria-hidden="true">🔍</span>
          <input type="search" name="q" placeholder="Tìm kiếm giáo trình, môn học, tác giả..." value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>" autocomplete="off" aria-label="Tìm kiếm sách">
          <button type="submit" class="nut-tim-kiem-header">
            <span>Tìm sách</span>
          </button>
        </div>
      </form>

      <!-- Cụm tiện ích: Yêu thích, Giỏ hàng, Tài khoản -->
      <div class="phan-dau__tien-ich">
        <a href="<?= $goc ?>yeu-thich.php" class="tien-ich-pill <?= $trang === 'yeu-thich' ? 'active' : '' ?>" title="Danh sách sách yêu thích">
          <span class="tien-ich-icon">❤️</span>
          <span class="tien-ich-label">Yêu thích</span>
          <span class="so-luong-yeu-thich"><?= soLuongYeuThich() ?></span>
        </a>

        <a href="<?= $goc ?>gio-hang.php" class="tien-ich-pill tien-ich-pill--gio <?= $trang === 'gio-hang' ? 'active' : '' ?>" title="Giỏ hàng của bạn">
          <span class="tien-ich-icon">🛒</span>
          <span class="tien-ich-label">Giỏ hàng</span>
          <span class="so-luong-gio"><?= $gio->soMon() ?></span>
        </a>

        <?php if ($u): ?>
          <div class="tien-ich-user">
            <span class="user-greeting">👤 <strong><?= e($u['ho_ten']) ?></strong></span>
            <?php if ($u['vai_tro'] === 'quan_tri'): ?>
              <a href="<?= $goc ?>quan-tri.php" class="user-badge-admin">⚙️ Quản trị</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="tien-ich-auth">
            <a href="<?= $goc ?>dang-nhap.php" class="nut nut--vien nut-auth-sm">Đăng nhập</a>
            <a href="<?= $goc ?>dang-ky.php" class="nut nut--chinh nut-auth-sm">Đăng ký</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- 3. Thanh điều hướng chính Sticky -->
  <nav class="thanh-dieu-huong" aria-label="Điều hướng chính">
    <div class="khung-chua thanh-dieu-huong__khung">
      <button class="nut-menu" id="nut-menu" aria-label="Mở menu điều hướng" aria-expanded="false">&#9776; Menu</button>
      <ul>
        <li><a href="<?= $goc ?>index.php" <?= $trang === 'index' ? 'class="hien-tai" aria-current="page"' : '' ?>><span>🏠</span> Trang chủ</a></li>
        <li><a href="<?= $goc ?>danh-sach.php" <?= $trang === 'danh-sach' ? 'class="hien-tai" aria-current="page"' : '' ?>><span>📚</span> Danh mục sách</a></li>
        <li><a href="<?= $goc ?>gioi-thieu.php" <?= $trang === 'gioi-thieu' ? 'class="hien-tai" aria-current="page"' : '' ?>><span>👥</span> Giới thiệu nhóm</a></li>
        <li><a href="<?= $goc ?>lien-he.php" <?= $trang === 'lien-he' ? 'class="hien-tai" aria-current="page"' : '' ?>><span>✉️</span> Liên hệ</a></li>
        <li><a href="<?= $goc ?>yeu-thich.php" <?= $trang === 'yeu-thich' ? 'class="hien-tai" aria-current="page"' : '' ?>><span>❤️</span> Yêu thích <span class="so-luong-yeu-thich"><?= soLuongYeuThich() ?></span></a></li>
        <li><a href="<?= $goc ?>gio-hang.php" <?= $trang === 'gio-hang' ? 'class="hien-tai" aria-current="page"' : '' ?>><span>🛒</span> Giỏ hàng <span class="so-luong-gio"><?= $gio->soMon() ?></span></a></li>
        <?php if ($u): ?>
          <li><a href="<?= $goc ?>don-hang.php" <?= $trang === 'don-hang' ? 'class="hien-tai" aria-current="page"' : '' ?>><span>📦</span> Đơn hàng</a></li>
          <?php if ($u['vai_tro'] === 'quan_tri'): ?>
            <li><a href="<?= $goc ?>quan-tri.php" <?= $trang === 'quan-tri' ? 'class="hien-tai" aria-current="page"' : '' ?>><span>⚙️</span> Quản trị</a></li>
          <?php endif; ?>
          <li style="display:inline-flex;align-items:center;">
            <form method="POST" action="<?= $goc ?>dang-xuat.php" style="display:inline;margin:0;padding:0;">
              <?= csrfField() ?>
              <button type="submit" class="nut-dang-xuat-header">🚪 Đăng xuất</button>
            </form>
          </li>
        <?php else: ?>
          <li class="nav-auth-mobile"><a href="<?= $goc ?>dang-nhap.php">Đăng nhập</a></li>
          <li class="nav-auth-mobile"><a href="<?= $goc ?>dang-ky.php">Đăng ký</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
