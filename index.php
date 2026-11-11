<?php
/**
 * index.php - Trang chủ website Nhà sách trực tuyến BookNest
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web (CSRF Token, Secure Session, CSP)
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham(db());

// Lấy 8 cuốn sách mới nhất từ MySQL qua KhoSanPham
$sachMoi = $kho->sanPhamMoi(8);

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

$tieuDe = 'Trang chủ | BookNest - Nhà sách sinh viên UED';
$trang = 'index';
$moTaTrang = 'BookNest - Nhà sách trực tuyến chuyên cung cấp giáo trình đại học, sách chuyên ngành CNTT, Toán học cho sinh viên Trường ĐH Sư phạm - ĐH Đà Nẵng.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">

  <!-- 1. HERO BANNER SÀN THƯƠNG MẠI ĐIỆN TỬ -->
  <section class="hero-tmdt-grid">
    <!-- Banner chính lớn bên trái -->
    <div class="banner-chinh-tmdt">
      <div class="banner-chinh-tmdt__tag">🔥 HỘI SÁCH SINH VIÊN 2026 &bull; TRỢ GIÁ ĐẾN 25%</div>
      <h1 class="banner-chinh-tmdt__title">
        Giáo Trình Chuẩn Hóa<br><span>Đại Học Sư Phạm Đà Nẵng</span>
      </h1>
      <p class="banner-chinh-tmdt__desc">
        Hơn 30+ đầu sách Công nghệ thông tin, Toán học &amp; Kỹ năng sống được thẩm định chính quy. Giao nhanh tận phòng ký túc xá Liên Chiểu trong vòng 24 giờ.
      </p>
      <div class="banner-chinh-tmdt__cta">
        <a href="danh-sach.php" class="btn-banner-mua">⚡ Mua giáo trình ngay &rarr;</a>
        <a href="gioi-thieu.php" class="btn-banner-nhom">👥 Đồ án Nhóm 12</a>
      </div>
    </div>

    <!-- Cột phụ bên phải: Voucher sinh viên & Smart Weather Assistant -->
    <div class="hero-tmdt-phu">
      <!-- Khối Voucher độc quyền sinh viên UED -->
      <div class="card-voucher-sv">
        <div class="card-voucher-sv__head">
          <strong style="color:#0f172a;font-size:0.95rem;">🎁 Voucher Ưu Đãi Sinh Viên</strong>
          <span class="card-voucher-sv__badge">HỌC KỲ MỚI</span>
        </div>
        <div class="card-voucher-sv__code">UED2026</div>
        <p class="card-voucher-sv__note">
          Giảm ngay <strong>25.000đ</strong> cho đơn đặt từ 2 cuốn giáo trình trở lên. Áp dụng cho toàn thể sinh viên Trường ĐH Sư phạm!
        </p>
      </div>

      <!-- Smart Weather Assistant từ Open-Meteo REST API -->
      <div class="card-weather-tmdt" id="khoi-thoi-tiet-dong" aria-live="polite">
        <div class="card-weather-tmdt__head">
          <span>🌤️</span>
          <span>Thời tiết Đà Nẵng hôm nay</span>
        </div>
        <div class="card-weather-tmdt__stats">
          <div>Nhiệt độ: <strong id="thoi-tiet-nhiet-do">26 °C</strong></div>
          <div>Độ ẩm: <strong id="thoi-tiet-do-am">85%</strong></div>
        </div>
        <p class="card-weather-tmdt__tip" id="thoi-tiet-loi-khuyen">
          💡 <em>Thời tiết Đà Nẵng mát mẻ, rất thích hợp để đọc sách chuyên ngành và luyện lập trình tại thư viện!</em>
        </p>
      </div>
    </div>
  </section>

  <!-- 2. THANH CAM KẾT SÀN (TRUST BADGES 4 TRỤ CỘT) -->
  <section class="trust-badges-bar">
    <div class="trust-item">
      <div class="trust-item__icon">🚚</div>
      <div>
        <p class="trust-item__title">Giao nhanh KTX trong 24h</p>
        <p class="trust-item__sub">Nhận sách tận phòng hoặc sảnh Khoa</p>
      </div>
    </div>
    <div class="trust-item">
      <div class="trust-item__icon">🛡️</div>
      <div>
        <p class="trust-item__title">100% Chuẩn Bản Quyền</p>
        <p class="trust-item__sub">Biên soạn bởi giảng viên uy tín ĐHĐN</p>
      </div>
    </div>
    <div class="trust-item">
      <div class="trust-item__icon">🔄</div>
      <div>
        <p class="trust-item__title">Đổi trả trong 7 ngày</p>
        <p class="trust-item__sub">Bảo hành nếu sách lỗi in, rách trang</p>
      </div>
    </div>
    <div class="trust-item">
      <div class="trust-item__icon">🏷️</div>
      <div>
        <p class="trust-item__title">Trợ giá sinh viên 25%</p>
        <p class="trust-item__sub">Giá ưu đãi đặc quyền cho sinh viên UED</p>
      </div>
    </div>
  </section>

  <!-- 3. KHỐI FLASH SALE GIÁ SỐC (REAL E-COMMERCE FLASH SALE) -->
  <section class="flash-sale-box">
    <div class="flash-sale-header">
      <div class="flash-sale-title">
        <span>⚡ FLASH SALE HÔM NAY</span>
      </div>
      <div class="flash-timer">
        <span>Kết thúc sau:</span>
        <span class="timer-pill" id="fs-gio">02</span> :
        <span class="timer-pill" id="fs-phut">45</span> :
        <span class="timer-pill" id="fs-giay">18</span>
      </div>
    </div>

    <!-- Lưới sách Flash Sale -->
    <div class="grid-books-tmdt">
      <?php foreach ($sachFlashSale as $sp): 
        $daThich = laYeuThich((int)$sp->id);
        $giaGoc = round($sp->gia * 1.25, -3); // Giá niêm yết trước giảm
      ?>
        <article class="the" data-id="<?= $sp->id ?>">
          <figure class="the__hinh-anh">
            <span class="the__the-loai-badge">-25%</span>
            <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button" aria-label="Yêu thích <?= e($sp->ten) ?>" title="<?= $daThich ? 'Bỏ thích' : 'Thêm vào yêu thích' ?>">
              <?= $daThich ? '❤️' : '🤍' ?>
            </button>
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>

          <div class="the__than">
            <span class="the__the-loai"><?= e($sp->tenDanhMuc ?? 'Giáo trình') ?></span>
            <h3 class="the__tieu-de">
              <a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a>
            </h3>

            <!-- Đánh giá sao & lượt mua -->
            <div class="rating-stars-tmdt">
              <span class="stars">★★★★★</span>
              <span>(4.9) &bull; Đã bán <?= rand(15, 80) ?></span>
            </div>

            <!-- Giá tiền TMĐT -->
            <div class="the__gia-row">
              <span class="the__gia"><?= vnd($sp->gia) ?></span>
              <span class="the__gia-goc"><?= vnd($giaGoc) ?></span>
            </div>

            <!-- Thanh tiến độ Flash Sale -->
            <div class="deal-progress-bar">
              <div class="deal-progress-bar__fill" style="width: <?= rand(60, 90) ?>%;"></div>
              <div class="deal-progress-bar__text">Đang bán chạy 🔥</div>
            </div>

            <p class="the__ton-kho <?= $sp->tonKho > 0 ? 'the__ton-kho--con' : 'the__ton-kho--het' ?>">
              <?= $sp->tonKho > 0 ? '✓ Còn ' . $sp->tonKho . ' cuốn' : '⚠ Hết hàng tạm thời' ?>
            </p>

            <!-- KHU VỰC 4 NÚT HÀNH ĐỘNG (CSRF PROTECTED) -->
            <div class="the__chan">
              <!-- Hàng 1: Nút Mua ngay (⚡ Mua ngay) & Nút Thêm vào giỏ (🛒 + Giỏ) -->
              <?php if ($sp->tonKho > 0): ?>
                <div class="the__chan-hang1">
                  <form method="POST" action="gio-hang.php" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="mua_ngay">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-mua-ngay">⚡ Mua ngay</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Giỏ</button>
                  </form>
                </div>
              <?php else: ?>
                <div style="color:#e02424;font-size:0.82rem;font-weight:700;text-align:center;padding:4px 0;background:#fdf2f2;border-radius:4px;">Hết hàng tạm thời</div>
              <?php endif; ?>

              <!-- Hàng 2: Chi tiết (🔍 Chi tiết) & Yêu thích (🤍 Yêu thích) -->
              <div class="the__chan-hang2">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" class="btn-chi-tiet">🔍 Chi tiết</a>
                <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=index.php" class="nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                  <?= $daThich ? '❤️ Đã thích' : '🤍 Yêu thích' ?>
                </a>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 4. KHỐI TẤT CẢ SÁCH MỚI CẬP NHẬT TRÊN KỆ -->
  <section>
    <div class="section-tmdt-head">
      <h2 class="section-tmdt-head__title">
        <span>📚</span> Giáo trình mới cập nhật trên kệ
      </h2>
      <a href="danh-sach.php" class="section-tmdt-head__link">
        <span>Xem tất cả hơn 30+ đầu sách</span>
        <span>&rarr;</span>
      </a>
    </div>

    <div class="grid-books-tmdt">
      <?php foreach ($sachMoi as $sp): 
        $daThich = laYeuThich((int)$sp->id);
        $giaGoc = round($sp->gia * 1.2, -3);
      ?>
        <article class="the" data-id="<?= $sp->id ?>">
          <figure class="the__hinh-anh">
            <span class="the__the-loai-badge" style="background:#1a56db;"><?= e($sp->tenDanhMuc ?? 'Giáo trình') ?></span>
            <button class="btn-fav-corner nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button" aria-label="Yêu thích <?= e($sp->ten) ?>" title="<?= $daThich ? 'Bỏ thích' : 'Thêm vào yêu thích' ?>">
              <?= $daThich ? '❤️' : '🤍' ?>
            </button>
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>

          <div class="the__than">
            <span class="the__the-loai"><?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?></span>
            <h3 class="the__tieu-de">
              <a href="chi-tiet.php?id=<?= $sp->id ?>"><?= e($sp->ten) ?></a>
            </h3>

            <div class="rating-stars-tmdt">
              <span class="stars">★★★★★</span>
              <span>(4.8) &bull; Đã bán <?= rand(10, 65) ?></span>
            </div>

            <div class="the__gia-row">
              <span class="the__gia"><?= vnd($sp->gia) ?></span>
              <span class="the__gia-goc"><?= vnd($giaGoc) ?></span>
            </div>

            <p class="the__ton-kho <?= $sp->tonKho > 0 ? 'the__ton-kho--con' : 'the__ton-kho--het' ?>">
              <?= $sp->tonKho > 0 ? '✓ Còn ' . $sp->tonKho . ' cuốn' : '⚠ Hết hàng tạm thời' ?>
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
                    <button type="submit" class="btn-mua-ngay">⚡ Mua ngay</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Giỏ</button>
                  </form>
                </div>
              <?php else: ?>
                <div style="color:#e02424;font-size:0.82rem;font-weight:700;text-align:center;padding:4px 0;background:#fdf2f2;border-radius:4px;">Hết hàng tạm thời</div>
              <?php endif; ?>

              <div class="the__chan-hang2">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" class="btn-chi-tiet">🔍 Chi tiết</a>
                <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=index.php" class="nut-bam-yeu-thich <?= $daThich ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                  <?= $daThich ? '❤️ Đã thích' : '🤍 Yêu thích' ?>
                </a>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 5. KHỐI SÁCH ĐÃ XEM GẦN ĐÂY -->
  <?php if (!empty($daXem)): ?>
    <section style="margin-top: 30px;">
      <div class="section-tmdt-head">
        <h2 class="section-tmdt-head__title">
          <span>🕒</span> Giáo trình bạn đã xem gần đây
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
              <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
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
                    <button type="submit" class="btn-mua-ngay">⚡ Mua ngay</button>
                  </form>
                  <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;flex:1;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="them">
                    <input type="hidden" name="id" value="<?= $sp->id ?>">
                    <input type="hidden" name="soLuong" value="1">
                    <button type="submit" class="btn-gio-hang btn-them-nhanh">🛒 + Giỏ</button>
                  </form>
                </div>
                <div class="the__chan-hang2">
                  <a href="chi-tiet.php?id=<?= $sp->id ?>" class="btn-chi-tiet">🔍 Chi tiết</a>
                  <a href="yeu-thich.php?action=toggle&id=<?= $sp->id ?>&redirect=index.php" class="nut-bam-yeu-thich <?= $daThichDx ? 'da-thich' : '' ?>" data-id="<?= $sp->id ?>" role="button">
                    <?= $daThichDx ? '❤️ Đã thích' : '🤍 Yêu thích' ?>
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
<script src="js/gio-hang.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
