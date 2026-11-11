<?php
/**
 * inc/footer.php - Phần chân trang dùng chung cho mọi trang website BookNest
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

$goc ??= '';
?>
  <footer class="phan-chan">
    <div class="khung-chua phan-chan__grid">
      <!-- Cột 1: Thông tin thương hiệu -->
      <div class="phan-chan__col phan-chan__col--brand">
        <div class="phan-chan__logo" style="margin-bottom: 12px;">
          <img src="<?= $goc ?>images/logo-booknest.png" alt="Logo BookNest" width="180" height="55" class="footer-logo-img">
        </div>
        <p class="phan-chan__desc">
          <strong>BookNest</strong> là hệ sinh thái nhà sách trực tuyến trợ giá giáo trình chuẩn đại học dành riêng cho sinh viên Trường Đại học Sư phạm &ndash; Đại học Đà Nẵng.
        </p>
        <div class="phan-chan__badges">
          <span class="badge-chip">🎓 UED Đà Nẵng</span>
          <span class="badge-chip">🔒 OWASP Security</span>
          <span class="badge-chip">⚡ 24h KTX Delivery</span>
        </div>
      </div>

      <!-- Cột 2: Điều hướng nhanh -->
      <div class="phan-chan__col">
        <h4 class="phan-chan__tieu-de">Khám phá sách</h4>
        <ul class="phan-chan__links">
          <li><a href="<?= $goc ?>index.php">&rarr; Trang chủ</a></li>
          <li><a href="<?= $goc ?>danh-sach.php">&rarr; Danh mục toàn bộ giáo trình</a></li>
          <li><a href="<?= $goc ?>danh-sach.php?dm=1">&rarr; Sách Công nghệ thông tin</a></li>
          <li><a href="<?= $goc ?>danh-sach.php?dm=2">&rarr; Sách Kinh tế &amp; Khởi nghiệp</a></li>
          <li><a href="<?= $goc ?>yeu-thich.php">&rarr; Danh sách sách yêu thích</a></li>
        </ul>
      </div>

      <!-- Cột 3: Hỗ trợ sinh viên -->
      <div class="phan-chan__col">
        <h4 class="phan-chan__tieu-de">Hỗ trợ sinh viên</h4>
        <ul class="phan-chan__links">
          <li><a href="<?= $goc ?>lien-he.php">&rarr; Hướng dẫn đặt mua giáo trình</a></li>
          <li><a href="<?= $goc ?>lien-he.php">&rarr; Điểm giao nhận tại Ký túc xá</a></li>
          <li><a href="<?= $goc ?>lien-he.php">&rarr; Chính sách đổi trả trong 7 ngày</a></li>
          <li><a href="<?= $goc ?>gioi-thieu.php">&rarr; Đồ án môn học Nhóm 12</a></li>
          <li><a href="<?= $goc ?>lien-he.php">&rarr; Liên hệ &amp; Góp ý</a></li>
        </ul>
      </div>

      <!-- Cột 4: Thông tin đồ án & Bảo mật -->
      <div class="phan-chan__col">
        <h4 class="phan-chan__tieu-de">Bảo mật &amp; Nhóm tác giả</h4>
        <div class="phan-chan__security-box">
          <div class="security-status">
            <span class="status-indicator"></span>
            <strong>Hệ thống bảo vệ Chapter 7</strong>
          </div>
          <p class="security-note">
            Đạt chuẩn 7 biện pháp an toàn ứng dụng web: CSRF Token 100% biểu mẫu, HttpOnly/SameSite Session, CSP Script-src 'self', Apache .htaccess, Least Privilege CSDL.
          </p>
          <div class="phan-chan__author-info">
            <span>Thực hiện: <strong>Nhóm 12 (Lớp 24CNTT3)</strong></span><br>
            <span>Khoa Toán &ndash; Tin, Trường ĐH Sư phạm &ndash; ĐH Đà Nẵng</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Thanh bản quyền đáy trang -->
    <div class="phan-chan__day">
      <div class="khung-chua phan-chan__day-khung">
        <p>&copy; 2026 <strong>BookNest &ndash; Nhóm 12</strong> (Lớp 24CNTT3, Khoa Toán &ndash; Tin, Trường ĐH Sư phạm &ndash; ĐH Đà Nẵng). All rights reserved.</p>
        <p>Học phần Thiết kế và Lập trình Web &bull; Địa chỉ: 459 Tôn Đức Thắng, Q. Liên Chiểu, TP. Đà Nẵng.</p>
      </div>
    </div>
  </footer>

  <!-- Nạp script điều khiển tương tác menu mobile và phím Esc -->
  <script type="module" src="<?= $goc ?>js/main.js"></script>
</body>
</html>
