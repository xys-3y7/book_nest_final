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
            Online bookstore specializing in university textbooks, Computer Science, and Mathematics standardized in 3NF for students of University of Science and Education &ndash; The University of Da Nang.
          </p>
          <div style="font-size:0.85rem;color:#4b5563;display:flex;flex-direction:column;gap:5px;">
            <div>📍 <strong>Address:</strong> 459 Ton Duc Thang, Lien Chieu, Da Nang City</div>
            <div>📞 <strong>Hotline:</strong> 0236.3841.323 (Faculty of Math &amp; IT)</div>
            <div>📧 <strong>Email:</strong> nhom12.24cntt3@ued.vn</div>
          </div>
        </div>

        <!-- Cột 2: Danh mục giáo trình nổi bật -->
        <div class="footer-tmdt-col">
          <h4>Textbook Categories</h4>
          <ul>
            <li><a href="<?= $goc ?>danh-sach.php?dm=1">💻 Computer Science &amp; Programming</a></li>
            <li><a href="<?= $goc ?>danh-sach.php?dm=2">📊 Economics &amp; Entrepreneurship</a></li>
            <li><a href="<?= $goc ?>danh-sach.php?dm=3">🎨 Literature &amp; Arts</a></li>
            <li><a href="<?= $goc ?>danh-sach.php?dm=4">🌱 Life Skills &amp; Personal Development</a></li>
            <li><a href="<?= $goc ?>danh-sach.php">📚 All Textbooks on Shelves</a></li>
          </ul>
        </div>

        <!-- Cột 3: Chính sách & Hỗ trợ sinh viên -->
        <div class="footer-tmdt-col">
          <h4>Policies &amp; Support</h4>
          <ul>
            <li><a href="<?= $goc ?>lien-he.php">🚀 How to Order &amp; Dorm Delivery</a></li>
            <li><a href="<?= $goc ?>don-hang.php">📦 Track Order Progress</a></li>
            <li><a href="<?= $goc ?>gioi-thieu.php">👥 About Team 12 Project</a></li>
            <li><a href="<?= $goc ?>gio-hang.php">🛒 Manage My Cart</a></li>
            <li><a href="<?= $goc ?>yeu-thich.php">❤️ My Saved Wishlist</a></li>
          </ul>
        </div>

        <!-- Cột 4: Thành viên nhóm & Thanh toán & An ninh -->
        <div class="footer-tmdt-col">
          <h4>Team 12 (Class 24CNTT3)</h4>
          <p style="font-size:0.85rem;color:#6b7280;margin:0 0 10px 0;">
            Course: <strong>Web Design &amp; Programming</strong> (Chapter 7: Web Security)
          </p>
          <div style="font-size:0.85rem;color:#374151;line-height:1.6;margin-bottom:12px;">
            <div>• <strong>Xaiyasith Yoi</strong> (Team Leader &bull; GitHub: <code>xys-3y7</code>)</div>
            <div>• <strong>Phommaket Hatsady</strong> (Member &bull; GitHub: <code>daimobile33076-eng</code>)</div>
            <div>• <strong>Vongsena Sauphasith</strong> (Member &bull; GitHub: <code>happy2072005-prog</code>)</div>
            <div>• <strong>Nguyễn Thị Sinh</strong> (Member &bull; GitHub: <code>chingg2005</code>)</div>
          </div>
          
          <div style="font-size:0.8rem;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Accepted Payments</div>
          <div class="footer-payment-icons">
            <span class="pay-badge">💵 Cash COD</span>
            <span class="pay-badge">🏦 VietQR</span>
            <span class="pay-badge">📱 MoMo</span>
            <span class="pay-badge">💳 VNPAY</span>
          </div>

          <!-- Huy hiệu Bảo mật Chương 7 (Defense-in-Depth) -->
          <div class="phan-chan__security-box" style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:8px;">
            <div style="font-size:0.8rem;font-weight:700;color:#0e9f6e;display:flex;align-items:center;gap:5px;margin-bottom:4px;">
              <span>🛡️</span>
              <span>DEFENSE-IN-DEPTH SECURITY</span>
            </div>
            <div style="font-size:0.75rem;color:#64748b;line-height:1.4;">
              100% POST CSRF Protected &bull; Session HttpOnly/SameSite &bull; CSP &bull; Least-Privilege DB &bull; Rate Limit 429
            </div>
          </div>
        </div>

      </div>

      <div class="footer-bottom-bar">
        &copy; 2026 <strong>BookNest &ndash; Team 12</strong> (Class 24CNTT3 &bull; Faculty of Mathematics &amp; Informatics, University of Science and Education &ndash; The University of Da Nang). 3NF Normalized Database on MariaDB / MySQL.
      </div>
    </div>
  </footer>

  <!-- Script điều hướng chung & Tác vụ giỏ hàng / yêu thích -->
  <script type="module" src="<?= $goc ?>js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>
</html>
