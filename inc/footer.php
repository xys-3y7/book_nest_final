<?php
/**
 * inc/footer.php - Phần chân trang chuẩn Sàn Thương mại Điện tử thực thụ
 * Nhóm 12 - Lớp 24CNTT3 - Khoa Toán & Tin học, Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web (OWASP Defense-in-Depth, CSRF, CSP, Least Privilege)
 */

$goc ??= '';
?>
  <!-- CHÂN TRANG SÀN THƯƠNG MẠI ĐIỆN TỬ BOOKNEST -->
  <footer class="phan-chan">
    <div class="khung-chua">
      <div class="footer-tmdt-grid">
        
        <!-- Cột 1: Thông tin thương hiệu & Liên hệ trường -->
        <div class="footer-tmdt-col">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="font-size:1.6rem;">📚</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1a56db;">Book<span style="color:#e02424;">Nest</span></div>
          </div>
          <p style="color:#6b7280;line-height:1.6;margin:0 0 12px 0;">
            Nhà sách trực tuyến chuyên cung cấp giáo trình đại học, sách chuyên ngành CNTT và Toán học chuẩn hóa 3NF cho sinh viên Trường Đại học Sư phạm &ndash; Đại học Đà Nẵng.
          </p>
          <div style="font-size:0.85rem;color:#4b5563;display:flex;flex-direction:column;gap:5px;">
            <div>📍 <strong>Địa chỉ:</strong> 459 Tôn Đức Thắng, Liên Chiểu, TP. Đà Nẵng</div>
            <div>📞 <strong>Hotline:</strong> 0236.3841.323 (Khoa Toán &ndash; Tin)</div>
            <div>📧 <strong>Email:</strong> nhom12.24cntt3@ued.vn</div>
          </div>
        </div>

        <!-- Cột 2: Danh mục giáo trình nổi bật -->
        <div class="footer-tmdt-col">
          <h4>Danh mục giáo trình</h4>
          <ul>
            <li><a href="<?= $goc ?>danh-sach.php?dm=1">💻 Công nghệ thông tin & Lập trình</a></li>
            <li><a href="<?= $goc ?>danh-sach.php?dm=2">📊 Kinh tế & Kỹ năng khởi nghiệp</a></li>
            <li><a href="<?= $goc ?>danh-sach.php?dm=3">🎨 Văn học, Nghệ thuật & Triết học</a></li>
            <li><a href="<?= $goc ?>danh-sach.php?dm=4">🌱 Kỹ năng sống & Phát triển bản thân</a></li>
            <li><a href="<?= $goc ?>danh-sach.php">📚 Toàn bộ giáo trình trên kệ</a></li>
          </ul>
        </div>

        <!-- Cột 3: Chính sách & Hỗ trợ sinh viên -->
        <div class="footer-tmdt-col">
          <h4>Chính sách & Hỗ trợ</h4>
          <ul>
            <li><a href="<?= $goc ?>lien-he.php">🚀 Hướng dẫn đặt sách & Nhận tại KTX</a></li>
            <li><a href="<?= $goc ?>don-hang.php">📦 Kiểm tra tiến độ đơn hàng</a></li>
            <li><a href="<?= $goc ?>gioi-thieu.php">👥 Giới thiệu đồ án Nhóm 12</a></li>
            <li><a href="<?= $goc ?>gio-hang.php">🛒 Quản lý giỏ hàng cá nhân</a></li>
            <li><a href="<?= $goc ?>yeu-thich.php">❤️ Danh sách sách yêu thích</a></li>
          </ul>
        </div>

        <!-- Cột 4: Thành viên nhóm & Thanh toán & An ninh -->
        <div class="footer-tmdt-col">
          <h4>Đồ án Nhóm 12 (24CNTT3)</h4>
          <p style="font-size:0.85rem;color:#6b7280;margin:0 0 10px 0;">
            Học phần: <strong>Thiết kế & Lập trình Web</strong> (Chương 7: Bảo mật ứng dụng Web)
          </p>
          <div style="font-size:0.85rem;color:#374151;line-height:1.6;margin-bottom:12px;">
            <div>• <strong>Xaiyasith Yoi</strong> (Nhóm trưởng &bull; GitHub: <code>xys-3y7</code>)</div>
            <div>• <strong>Phommaket Hatsady</strong> (Thành viên &bull; GitHub: <code>daimobile33076-eng</code>)</div>
            <div>• <strong>Vongsena Sauphasith</strong> (Thành viên &bull; GitHub: <code>happy2072005-prog</code>)</div>
            <div>• <strong>Nguyễn Thị Sinh</strong> (Thành viên &bull; GitHub: <code>chingg2005</code>)</div>
          </div>
          
          <div style="font-size:0.8rem;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Chấp nhận thanh toán</div>
          <div class="footer-payment-icons">
            <span class="pay-badge">💵 Tiền mặt COD</span>
            <span class="pay-badge">🏦 VietQR</span>
            <span class="pay-badge">📱 MoMo</span>
            <span class="pay-badge">💳 VNPAY</span>
          </div>

          <!-- Huy hiệu Bảo mật Chương 7 (Defense-in-Depth) -->
          <div class="phan-chan__security-box" style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:8px;">
            <div style="font-size:0.8rem;font-weight:700;color:#0e9f6e;display:flex;align-items:center;gap:5px;margin-bottom:4px;">
              <span>🛡️</span>
              <span>BẢO MẬT PHÒNG THỦ CHIỀU SÂU</span>
            </div>
            <div style="font-size:0.75rem;color:#64748b;line-height:1.4;">
              100% POST CSRF Protected &bull; Session HttpOnly/SameSite &bull; CSP &bull; Least-Privilege DB &bull; Rate Limit 429
            </div>
          </div>
        </div>

      </div>

      <div class="footer-bottom-bar">
        © 2026 <strong>BookNest &ndash; Nhóm 12</strong> (Lớp 24CNTT3 &bull; Khoa Toán &ndash; Tin, Trường ĐH Sư phạm &ndash; ĐH Đà Nẵng). Dữ liệu CSDL chuẩn hóa 3NF trên hệ quản trị MariaDB / MySQL.
      </div>
    </div>
  </footer>

  <!-- Script điều hướng chung & Tác vụ giỏ hàng / yêu thích -->
  <script type="module" src="<?= $goc ?>js/main.js"></script>
</body>
</html>
