<?php
/**
 * index.php - Trang chủ website Nhà sách trực tuyến BookNest
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 1 (Bảng 1): Đọc dữ liệu từ MySQL (mục mới nhất, khối đã xem gần đây gom vào 1 câu WHERE IN)
 */

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSanPham;

$kho = new KhoSanPham(db());

// Lấy 6 cuốn sách mới nhất từ MySQL qua KhoSanPham
$sachMoi = $kho->sanPhamMoi(6);

// Đọc cookie "da_xem" (ID các sách đã xem gần nhất, cách nhau bởi dấu phẩy)
// Tối ưu hóa: gom toàn bộ ID vào 1 câu SQL WHERE id IN (...) duy nhất, tránh N + 1 queries
$daXem = [];
if (!empty($_COOKIE['da_xem'])) {
    $rawIds = explode(',', $_COOKIE['da_xem']);
    $ids = array_filter(array_map('intval', $rawIds));
    if (!empty($ids)) {
        // Chỉ lấy tối đa 4 cuốn đã xem gần nhất
        $daXem = $kho->layTheoDanhSachId(array_slice($ids, 0, 4));
    }
}

$tieuDe = 'Trang chủ';
$trang = 'index';
$moTaTrang = 'BookNest - Nhà sách trực tuyến chuyên cung cấp giáo trình đại học, sách chuyên ngành CNTT, Toán học cho sinh viên Trường ĐH Sư phạm - ĐH Đà Nẵng.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">

  <!-- 1. KHU VỰC HERO HIỆN ĐẠI (SPLIT 2 CỘT CAO CẤP) -->
  <section class="hero-modern">
    <div class="hero-modern__content">
      <div class="hero-badge">
        <span class="hero-badge__dot"></span>
        <span>✨ Nhà sách trực tuyến sinh viên UED Đà Nẵng</span>
      </div>
      <h1 class="hero-title">
        Tri Thức Nâng Bước, <br>
        <span class="text-gradient">Đồng Hành Tương Lai</span>
      </h1>
      <p class="hero-desc">
        Hệ thống cung cấp hơn 1.000+ đầu sách giáo trình chuẩn, tài liệu chuyên ngành Công nghệ thông tin, Toán học &amp; Kỹ năng sống với chính sách trợ giá độc quyền từ 10% &ndash; 25% dành riêng cho sinh viên UED.
      </p>

      <!-- Ô tìm kiếm thông minh trong Hero -->
      <form class="hero-search" action="danh-sach.php" method="GET">
        <div class="hero-search__inner">
          <span class="hero-search__icon">🔍</span>
          <input type="text" name="q" placeholder="Tìm kiếm giáo trình, tên tác giả, môn học..." aria-label="Tìm kiếm sách">
          <button type="submit" class="hero-search__btn">
            <span>Tìm sách</span>
            <span aria-hidden="true">&rarr;</span>
          </button>
        </div>
      </form>

      <!-- Các nút hành động chính -->
      <div class="hero-actions">
        <a href="danh-sach.php" class="hero-btn-primary">
          <span>Khám phá kho sách</span>
          <span aria-hidden="true">&rarr;</span>
        </a>
        <a href="gioi-thieu.php" class="hero-btn-secondary">
          <span>Hồ sơ Nhóm 12</span>
        </a>
      </div>

      <!-- Thống kê cam kết uy tín -->
      <div class="hero-stats">
        <div class="hero-stat-item">
          <div class="hero-stat-num">1.000+</div>
          <div class="hero-stat-label">Giáo trình &amp; Sách</div>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat-item">
          <div class="hero-stat-num">25%</div>
          <div class="hero-stat-label">Trợ giá sinh viên</div>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat-item">
          <div class="hero-stat-num">24h</div>
          <div class="hero-stat-label">Giao tận phòng KTX</div>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat-item">
          <div class="hero-stat-num">100%</div>
          <div class="hero-stat-label">Chuẩn OWASP BMTT</div>
        </div>
      </div>
    </div>

    <!-- Cột hiển thị hình ảnh minh họa nổi bật (3D Book Showcase) -->
    <div class="hero-modern__visual">
      <div class="hero-showcase-card">
        <div class="hero-showcase-glow"></div>
        <div class="hero-showcase-badge">🔥 BÁN CHẠY NHẤT HỌC KỲ</div>
        
        <div class="hero-book-stage">
          <div class="hero-3d-book">
            <img src="images/sach-laptrinh-web.png" alt="Bìa sách Lập trình Web hiện đại" class="hero-3d-book__cover" width="135" height="185">
          </div>
          
          <div class="hero-showcase-details">
            <span class="hero-book-chuyen-nganh">KHOA TOÁN - TIN</span>
            <h3 class="hero-book-title">Lập trình Web hiện đại với HTML5, CSS3 &amp; PHP</h3>
            <p class="hero-book-author">✍️ ThS. Nguyễn Văn A &bull; ĐH Sư phạm</p>
            <div class="hero-book-rating">
              <span class="stars">★★★★★</span>
              <span class="rating-text">5.0 (48 SV đánh giá)</span>
            </div>
            <div class="hero-book-price-row">
              <span class="hero-book-price">96.000 VNĐ</span>
              <span class="hero-book-old">120.000 VNĐ</span>
              <span class="hero-book-badge-sale">-20% Trợ giá</span>
            </div>
            <div class="hero-book-cta-row">
              <a href="dat-hang.php?id=1" class="hero-book-btn-buy">
                <span>⚡ Mua ngay</span>
              </a>
              <a href="chi-tiet.php?id=1" class="hero-book-btn-detail">
                <span>🔍 Chi tiết</span>
              </a>
            </div>
          </div>
        </div>

        <!-- 2 huy hiệu nổi bật góc -->
        <div class="hero-float-badge hero-float-badge--top">
          <span class="hero-float-icon">🎓</span>
          <div>
            <strong>Chính hãng UED</strong>
            <small>Giảng viên thẩm định</small>
          </div>
        </div>
        <div class="hero-float-badge hero-float-badge--bottom">
          <span class="hero-float-icon">⚡</span>
          <div>
            <strong>Giao nhanh 24h</strong>
            <small>Miễn phí tại KTX</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Khối thời tiết động từ Open-Meteo REST API -->
  <section id="khoi-thoi-tiet-dong" class="khoi-thoi-tiet-container" aria-live="polite"></section>

  <!-- 2. HÀNG 4 THẺ CAM KẾT VÀ GIÁ TRỊ CỐT LÕI -->
  <section class="tinh-nang-section">
    <div class="tinh-nang-grid">
      <div class="tinh-nang-card">
        <div class="tinh-nang-icon tinh-nang-icon--blue">📚</div>
        <div class="tinh-nang-content">
          <h4>Giáo trình chuẩn hóa</h4>
          <p>100% tài liệu được thẩm định bởi giảng viên ĐH Sư phạm &ndash; ĐH Đà Nẵng.</p>
        </div>
      </div>
      <div class="tinh-nang-card">
        <div class="tinh-nang-icon tinh-nang-icon--orange">🏷️</div>
        <div class="tinh-nang-content">
          <h4>Trợ giá sinh viên</h4>
          <p>Giảm từ 10% đến 25% so với giá bìa giúp tiết kiệm chi phí học tập.</p>
        </div>
      </div>
      <div class="tinh-nang-card">
        <div class="tinh-nang-icon tinh-nang-icon--green">🚀</div>
        <div class="tinh-nang-content">
          <h4>Giao nhận 24 giờ</h4>
          <p>Nhận sách trực tiếp tại sảnh Khoa hoặc giao tận phòng ký túc xá.</p>
        </div>
      </div>
      <div class="tinh-nang-card">
        <div class="tinh-nang-icon tinh-nang-icon--purple">🛡️</div>
        <div class="tinh-nang-content">
          <h4>Bảo mật tuyệt đối</h4>
          <p>Đáp ứng 7 biện pháp an ninh mạng OWASP: CSRF, CSP, HttpOnly, .env.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. KHÁM PHÁ NHANH THEO CHUYÊN NGÀNH (CATEGORY CHIPS) -->
  <div class="danh-muc-chips-wrap">
    <div class="danh-muc-chips-header">
      <h3>Khám phá theo chuyên ngành:</h3>
    </div>
    <div class="danh-muc-chips">
      <a href="danh-sach.php" class="chip active">✨ Tất cả sách</a>
      <a href="danh-sach.php?dm=1" class="chip">💻 Công nghệ thông tin</a>
      <a href="danh-sach.php?dm=2" class="chip">📊 Kinh tế &amp; Khởi nghiệp</a>
      <a href="danh-sach.php?dm=3" class="chip">📖 Văn học &amp; Nghệ thuật</a>
      <a href="danh-sach.php?dm=4" class="chip">🌱 Kỹ năng sống &amp; Phát triển</a>
    </div>
  </div>

  <!-- 4. DANH MỤC SÁCH MỚI NHẤT (TRUY VẤN MYSQL) -->
  <section>
    <div class="phan-doan-header">
      <div class="phan-doan-header__title">
        <span class="phan-doan-badge">🔥 Nổi Bật Tuần Này</span>
        <h2>Sách mới cập nhật trên kệ BookNest</h2>
      </div>
      <a href="danh-sach.php" class="phan-doan-link">
        <span>Xem tất cả 33+ cuốn sách</span>
        <span aria-hidden="true">&rarr;</span>
      </a>
    </div>
    
    <div class="luoi-the">
      <?php foreach ($sachMoi as $idx => $sp): ?>
        <?php 
          $giaGoc = round($sp->gia * 1.25, -3); // Giá bìa gốc trước khi trợ giá sinh viên
          $soSao = 4.8 + (($sp->id % 3) * 0.1);
          $soDanhGia = 15 + ($sp->id * 4);
        ?>
        <article class="the">
          <figure class="the__hinh-anh">
            <span class="the__tag-noi-bat"><?= $idx < 2 ? '🔥 BÁN CHẠY' : '⭐ MỚI' ?></span>
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>
          <div class="the__than">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <span class="the__the-loai"><?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?></span>
              <div class="the__danh-gia">
                <span>★</span> <?= number_format($soSao, 1) ?> (<?= $soDanhGia ?>)
              </div>
            </div>

            <h3 class="the__tieu-de" title="<?= e($sp->ten) ?>"><?= e($sp->ten) ?></h3>

            <div style="display: flex; align-items: baseline; gap: 8px; margin: 4px 0 6px 0;">
              <span class="the__gia" style="margin: 0;"><?= vnd($sp->gia) ?></span>
              <span class="the__gia-goc"><?= vnd($giaGoc) ?></span>
            </div>

            <p class="the__ton-kho" style="color:<?= $sp->tonKho > 0 ? '#16a34a' : '#dc2626' ?>;">
              <?= $sp->tonKho > 0 ? 'Còn hàng (' . $sp->tonKho . ' cuốn)' : 'Hết hàng tạm thời' ?>
            </p>

            <!-- 4 NÚT THAO TÁC: ĐẶT MUA, THÊM GIỎ, YÊU THÍCH, CHI TIẾT (2x2 GRID ĐỐI XỨNG) -->
            <div class="the__chan">
              <!-- 1. Nút Đặt mua ngay (ปุ่มกดสั่งชื้อ) -->
              <?php if ($sp->tonKho > 0): ?>
                <form method="POST" action="gio-hang.php">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="mua_ngay">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="soLuong" value="1">
                  <button type="submit" class="nut nut--nhan btn-mua-ngay" title="Đặt mua sách ngay">⚡ Mua ngay</button>
                </form>
              <?php else: ?>
                <button type="button" disabled class="nut nut--vien" style="opacity:0.6;cursor:not-allowed;" title="Sách tạm thời hết hàng">⚡ Hết hàng</button>
              <?php endif; ?>

              <!-- 2. Nút Thêm vào giỏ hàng (ปุ่มตะกร้า) -->
              <?php if ($sp->tonKho > 0): ?>
                <form method="POST" action="gio-hang.php" class="form-them-nhanh">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="them">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="soLuong" value="1">
                  <button type="submit" class="nut nut--chinh btn-them-nhanh" title="Thêm vào giỏ hàng">🛒 Thêm giỏ</button>
                </form>
              <?php else: ?>
                <button type="button" disabled class="nut nut--vien" style="opacity:0.6;cursor:not-allowed;" title="Sách tạm thời hết hàng">🛒 Hết hàng</button>
              <?php endif; ?>

              <!-- 3. Nút Danh sách yêu thích (ปุ่มรายการโปรด) -->
              <form method="POST" action="yeu-thich.php" class="form-yeu-thich">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $sp->id ?>">
                <button type="submit" class="nut nut--vien btn-yeu-thich <?= daYeuThich($sp->id) ? 'da-thich' : '' ?>" title="<?= daYeuThich($sp->id) ? 'Đã yêu thích (Bấm để bỏ)' : 'Thêm vào yêu thích' ?>">
                  <?= daYeuThich($sp->id) ? '❤️ Đã thích' : '🤍 Yêu thích' ?>
                </button>
              </form>

              <!-- 4. Nút Xem chi tiết (ปุ่มดูรายละเอียด) -->
              <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut nut--vien" title="Xem chi tiết nội dung cuốn sách">🔍 Chi tiết</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 5. BANNER KHUYẾN MÃI HỌC KỲ VỚI MÃ GIẢM GIÁ -->
  <section class="banner-khuyen-mai-ngang">
    <div class="banner-km-text">
      <h3>🎁 Chương trình Tri ân Sinh viên UED 2026</h3>
      <p>Nhận ngay ưu đãi giảm thêm 10% khi nhập mã voucher trong quá trình thanh toán đơn hàng trực tuyến.</p>
    </div>
    <div class="banner-km-cta">
      <div class="banner-ma-giam">MÃ: SINHVIEN2026</div>
      <a href="danh-sach.php" class="nut nut--nhan" style="border-radius: 10px; font-weight: 700;">Áp dụng ngay &rarr;</a>
    </div>
  </section>

  <!-- 6. KHỐI SÁCH ĐÃ XEM GẦN ĐÂY (LẤY BẰNG 1 CÂU WHERE IN) -->
  <?php if (!empty($daXem)): ?>
    <section style="margin-top: 40px; padding-top: 25px; border-top: 2px dashed #cbd5e1;">
      <div class="phan-doan-header">
        <div class="phan-doan-header__title">
          <span class="phan-doan-badge">🕒 Lịch Sử Xem</span>
          <h2>Sách bạn đã xem gần đây (Cookie Session)</h2>
        </div>
      </div>
      <div class="luoi-the">
        <?php foreach ($daXem as $sp): ?>
          <article class="the">
            <figure class="the__hinh-anh">
              <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
            </figure>
            <div class="the__than">
              <span class="the__the-loai"><?= e($sp->tenDanhMuc ?? 'Chuyên ngành') ?></span>
              <h3 class="the__tieu-de" title="<?= e($sp->ten) ?>"><?= e($sp->ten) ?></h3>
              <p class="the__gia"><?= vnd($sp->gia) ?></p>
              <div class="the__chan">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut nut--chinh" style="grid-column: span 2; width: 100%;">Xem lại chi tiết &rarr;</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- 7. GÓC ĐÁNH GIÁ CỦA CỘNG ĐỒNG SINH VIÊN SƯ PHẠM -->
  <section class="danh-gia-section">
    <div class="phan-doan-header" style="justify-content: center; text-align: center;">
      <div class="phan-doan-header__title" style="align-items: center;">
        <span class="phan-doan-badge">💬 Cảm Nhận Độc Giả</span>
        <h2>Sinh viên UED nói gì về BookNest?</h2>
      </div>
    </div>

    <div class="danh-gia-grid">
      <div class="danh-gia-card">
        <div class="danh-gia-sao">★★★★★</div>
        <p class="danh-gia-text">
          "Giáo trình Lập trình Web và CSDL của BookNest chuẩn khung chương trình trường mình, giá lại rẻ hơn tiệm sách ngoài cổng nhiều. Đặt buổi sáng, chiều đã nhận được ở sảnh Khoa Toán - Tin!"
        </p>
        <div class="danh-gia-author">
          <div class="author-avatar">TN</div>
          <div class="author-info">
            <h5>Trần Nhật Nam</h5>
            <span>Sinh viên K24 CNTT, ĐH Sư phạm</span>
          </div>
        </div>
      </div>

      <div class="danh-gia-card">
        <div class="danh-gia-sao">★★★★★</div>
        <p class="danh-gia-text">
          "Trang web chạy rất mượt, giao diện đẹp và tính năng thêm vào giỏ hàng hoặc yêu thích không hề bị tải lại trang. Rất thích mục lời khuyên chọn sách theo thời tiết Đà Nẵng!"
        </p>
        <div class="danh-gia-author">
          <div class="author-avatar" style="background:#fce7f3; color:#be185d;">ML</div>
          <div class="author-info">
            <h5>Mai Thùy Linh</h5>
            <span>Sinh viên Khoa Giáo dục Tiểu học</span>
          </div>
        </div>
      </div>

      <div class="danh-gia-card">
        <div class="danh-gia-sao">★★★★★</div>
        <p class="danh-gia-text">
          "Chính sách bảo mật của web rất nghiêm ngặt, có chống CSRF và bảo vệ tài khoản tốt. Đặt sách ở KTX trường được giao tận phòng chỉ trong vài giờ rất tiện lợi."
        </p>
        <div class="danh-gia-author">
          <div class="author-avatar" style="background:#fef3c7; color:#b45309;">HQ</div>
          <div class="author-info">
            <h5>Hoàng Đăng Quang</h5>
            <span>Sinh viên K23 Sư phạm Toán</span>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

<script src="js/gio-hang.js"></script>
<script type="module" src="js/trang-chu.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
