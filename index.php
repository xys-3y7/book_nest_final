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
  <h1>Chào mừng bạn đến với Nhà sách trực tuyến BookNest</h1>

  <!-- Khối thời tiết động từ Open-Meteo REST API -->
  <section id="khoi-thoi-tiet-dong" class="khoi-thoi-tiet-container" aria-live="polite"></section>

  <!-- 1. Khu vực nổi bật Hero Banner -->
  <section class="khu-vuc-hero">
    <h2>Chương trình khuyến mãi giáo trình học kỳ mới</h2>
    <p>Nhân dịp khai giảng năm học mới 2026 - 2027, BookNest trân trọng gửi tới các bạn sinh viên chương trình trợ giá giáo trình đặc biệt:</p>
    <figure>
      <img src="images/banner-khuyenmai.png" alt="Banner chương trình trợ giá 20% giáo trình CNTT và Toán học cho sinh viên" width="800" height="250">
      <figcaption>Chương trình ưu đãi giảm 20% cho toàn bộ giáo trình chuyên ngành Toán và CNTT</figcaption>
    </figure>
    <p>Ưu đãi áp dụng từ ngày 15/09/2026 đến hết ngày 15/10/2026 tại khuôn viên Trường Đại học Sư phạm - Đại học Đà Nẵng.</p>
  </section>

  <!-- 2. Giới thiệu dự án và Hàng ba thẻ cam kết nổi bật -->
  <section>
    <h2>Về dự án BookNest</h2>
    <p>BookNest là dự án học phần <em>Thiết kế và Lập trình web</em> được phát triển bởi <strong>Nhóm 12 (Lớp 24CNTT3)</strong>, Khoa Toán - Tin, Trường Đại học Sư phạm - Đại học Đà Nẵng.</p>
    <p>Toàn bộ dữ liệu của website đã được đưa vào hệ quản trị CSDL <strong>MySQL / MariaDB</strong> thông qua tầng truy xuất dữ liệu PDO an toàn, hỗ trợ tìm kiếm phân trang tức thời, giao dịch ACID và REST API hiện đại.</p>
    
    <h3>Ba cam kết cốt lõi của BookNest</h3>
    <div class="hang-ba-the">
      <article class="the-tinh-nang">
        <h4>Giáo trình chuẩn hóa</h4>
        <p>100% sách và tài liệu được biên soạn hoặc thẩm định bởi các giảng viên uy tín thuộc các trường đại học hàng đầu.</p>
      </article>
      <article class="the-tinh-nang">
        <h4>Giá cả trợ giá</h4>
        <p>Trợ giá sinh viên từ 10% đến 25% so với giá bìa thị trường kèm nhiều ưu đãi trong suốt học kỳ.</p>
      </article>
      <article class="the-tinh-nang">
        <h4>Giao nhận nhanh chóng</h4>
        <p>Nhận sách trực tiếp tại sảnh Khoa Toán - Tin hoặc giao tận phòng ký túc xá trong vòng 24 giờ.</p>
      </article>
    </div>
  </section>

  <!-- 3. Danh mục sách mới nhất (Truy vấn từ MySQL qua KhoSanPham) -->
  <section>
    <h2>Sách mới cập nhật trên kệ BookNest</h2>
    <p>Dưới đây là các đầu sách chuyên ngành mới nhất vừa được bổ sung vào hệ thống CSDL BookNest:</p>
    
    <div class="luoi-the">
      <?php foreach ($sachMoi as $sp): ?>
        <article class="the">
          <figure class="the__hinh-anh">
            <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
          </figure>
          <div class="the__than">
            <span class="the__the-loai" style="font-size:0.8rem;color:#1e40af;font-weight:bold;"><?= e($sp->tenDanhMuc ?? 'Sách chuyên ngành') ?></span>
            <h3 class="the__tieu-de" style="font-size:1.05rem;margin:0.25rem 0;"><?= e($sp->ten) ?></h3>
            <p class="the__gia" style="margin:0.4rem 0;"><strong><?= vnd($sp->gia) ?></strong></p>
            <p class="the__ton-kho" style="font-size:0.85rem;color:<?= $sp->tonKho > 0 ? '#16a34a' : '#dc2626' ?>;">
              <?= $sp->tonKho > 0 ? 'Còn hàng (' . $sp->tonKho . ' cuốn)' : 'Hết hàng tạm thời' ?>
            </p>
            <!-- 4 NÚT THAO TÁC: ĐẶT MUA, THÊM GIỎ, YÊU THÍCH, CHI TIẾT -->
            <div class="the__chan" style="margin-top:0.6rem;display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
              <!-- 1. Nút Đặt mua ngay (ปุ่มกดสั่งชื้อ) -->
              <?php if ($sp->tonKho > 0): ?>
                <form method="POST" action="gio-hang.php" style="margin:0;display:inline;">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="mua_ngay">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="soLuong" value="1">
                  <button type="submit" class="nut nut--nhan btn-mua-ngay" title="Đặt mua sách ngay" style="font-size:0.8rem;padding:6px 10px;">⚡ Đặt mua</button>
                </form>
              <?php else: ?>
                <button type="button" disabled class="nut nut--vien" style="font-size:0.8rem;padding:6px 8px;opacity:0.6;cursor:not-allowed;">Hết hàng</button>
              <?php endif; ?>

              <!-- 2. Nút Thêm vào giỏ hàng (ปุ่มตะกร้า) -->
              <?php if ($sp->tonKho > 0): ?>
                <form method="POST" action="gio-hang.php" class="form-them-nhanh" style="margin:0;display:inline;">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="them">
                  <input type="hidden" name="id" value="<?= $sp->id ?>">
                  <input type="hidden" name="soLuong" value="1">
                  <button type="submit" class="nut nut--chinh btn-them-nhanh" title="Thêm vào giỏ hàng" style="font-size:0.8rem;padding:6px 10px;">🛒 Thêm giỏ</button>
                </form>
              <?php endif; ?>

              <!-- 3. Nút Danh sách yêu thích (ปุ่มรายการโปรด) -->
              <form method="POST" action="yeu-thich.php" class="form-yeu-thich" style="margin:0;display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $sp->id ?>">
                <button type="submit" class="nut nut--vien btn-yeu-thich <?= daYeuThich($sp->id) ? 'da-thich' : '' ?>" title="<?= daYeuThich($sp->id) ? 'Đã yêu thích (Bấm để bỏ)' : 'Thêm vào yêu thích' ?>" style="font-size:0.8rem;padding:6px 10px;<?= daYeuThich($sp->id) ? 'background:#fee2e2;color:#dc2626;border-color:#fca5a5;' : '' ?>">
                  <?= daYeuThich($sp->id) ? '❤️ Đã thích' : '🤍 Yêu thích' ?>
                </button>
              </form>

              <!-- 4. Nút Xem chi tiết (ปุ่มดูรายละเอียด) -->
              <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut nut--vien" title="Xem chi tiết nội dung cuốn sách" style="font-size:0.8rem;padding:6px 10px;">🔍 Chi tiết</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 4. Khối sách đã xem gần đây (Lấy bằng 1 câu WHERE IN) -->
  <?php if (!empty($daXem)): ?>
    <section style="margin-top: 40px; padding-top: 20px; border-top: 2px dashed #cbd5e1;">
      <h2>Sách bạn đã xem gần đây (Cookie Session)</h2>
      <div class="luoi-the">
        <?php foreach ($daXem as $sp): ?>
          <article class="the">
            <figure class="the__hinh-anh">
              <img src="<?= e(anhSach($sp->anh)) ?>" alt="Bìa sách <?= e($sp->ten) ?>" width="175" height="225" loading="lazy">
            </figure>
            <div class="the__than">
              <h3 class="the__tieu-de"><?= e($sp->ten) ?></h3>
              <p class="the__gia"><?= vnd($sp->gia) ?></p>
              <div class="the__chan">
                <a href="chi-tiet.php?id=<?= $sp->id ?>" class="nut nut--vien">Xem lại</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</main>

<script src="js/gio-hang.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
