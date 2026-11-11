<?php
/**
 * gioi-thieu.php - Giới thiệu đề tài BookNest và danh sách thành viên Nhóm 12
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chương 7: Bảo mật ứng dụng web
 */

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'Giới thiệu Nhóm 12 & Đồ án BookNest';
$trang = 'gioi-thieu';
$moTaTrang = 'Giới thiệu về Nhóm 12 lớp 24CNTT3 và dự án đồ án website BookNest - Nhà sách trực tuyến phục vụ sinh viên UED.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <h1>Giới thiệu Nhóm phát triển 12 và Đồ án BookNest</h1>

  <!-- 1. Văn bản dài giới thiệu đồ án -->
  <section>
    <h2>Tổng quan về đồ án môn học</h2>
    <p class="doan-van-dai">Dự án website <strong>BookNest</strong> được xây dựng trong khuôn khổ học phần <em>Thiết kế và Lập trình web</em> (Web Design and Programming), Học kỳ 1 năm học 2026 - 2027 tại Khoa Toán - Tin, Trường Đại học Sư phạm - Đại học Đà Nẵng.</p>
    <p class="doan-van-dai">Ở Chương 7, nhóm thực hiện chuyên đề <strong>Bảo mật ứng dụng web</strong> theo mô hình <em>Phòng thủ theo chiều sâu (Defense-in-Depth)</em>: tích hợp CSRF Token cho toàn bộ 19 biểu mẫu POST và fetch API, bảo vệ phiên làm việc (HttpOnly, SameSite=Lax, TTL 30 phút), thiết lập bộ tiêu đề bảo mật HTTP (CSP script-src 'self', frame-ancestors 'none', nosniff, gỡ bỏ X-Powered-By), cấu hình máy chủ Apache <code>.htaccess</code> chặn tệp nội bộ nhạy cảm và bảo vệ biến môi trường <code>.env</code> cùng phân quyền CSDL người dùng ứng dụng tối thiểu.</p>
    
    <h3>Tầm nhìn và sứ mệnh</h3>
    <ul class="doan-van-dai">
      <li><strong>Tầm nhìn:</strong> Trở thành cổng thông tin tra cứu và phân phối giáo trình, văn phòng phẩm trực tuyến số một cho sinh viên Trường Đại học Sư phạm - Đại học Đà Nẵng.</li>
      <li><strong>Sứ mệnh:</strong> Số hóa quy trình mượn, mua và chia sẻ tài liệu học tập, góp phần giảm thiểu chi phí và nâng cao chất lượng học tập của sinh viên sư phạm và công nghệ.</li>
    </ul>
  </section>

  <!-- 2. Danh sách thành viên Nhóm 12 -->
  <section>
    <h2>Danh sách thành viên Nhóm 12 (Lớp 24CNTT3)</h2>
    <p>Nhóm 12 gồm 4 sinh viên chuyên ngành Công nghệ thông tin cùng hợp tác và phân chia nhiệm vụ chuyên trách:</p>

    <div class="danh-sach-thanh-vien">
      
      <!-- Thành viên 1: Xaiyasith Yoi -->
      <article class="the-thanh-vien">
        <figure>
          <img src="images/yoi-avatar.png" alt="Ảnh chân dung sinh viên Xaiyasith Yoi" width="120" height="120" loading="lazy">
          <figcaption><strong>Xaiyasith Yoi</strong><br><small>MSV: 3120224189</small></figcaption>
        </figure>
        <p><span class="huy-hieu huy-hieu--thanh-cong">Nhóm trưởng &bull; Phụ trách PHẦN A</span></p>
        <p>
          <a href="https://github.com/xys-3y7" target="_blank" rel="noopener noreferrer" class="github-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: xys-3y7</span>
          </a>
        </p>
        <p class="the__mo-ta">Kiến trúc khung web PHP, trang chủ, giỏ hàng Session, yêu thích và quản trị đơn hàng.</p>
        
        <div class="duty-box">
          <strong>📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul>
            <li>Chủ trì tổng hợp &amp; định dạng báo cáo (Word &amp; PDF).</li>
            <li>Biện pháp 1: CSRF Token trên 19 form POST &amp; API fetch.</li>
            <li>Biện pháp 7: Rate limiting 429 &amp; ghi nhật ký bảo mật độc lập.</li>
            <li>Xây dựng bộ kiểm thử tự động 14 ca (<code>tests/bao-mat.sh</code>).</li>
            <li>Báo cáo Phần B (Lưu bút &amp; xóa bảo mật) &amp; Thảo luận C.1.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224189_yoi/gioithieu.php" class="nut nut--chinh">Xem hồ sơ cá nhân &rarr;</a>
      </article>

      <!-- Thành viên 2: Phommaket Hatsady -->
      <article class="the-thanh-vien">
        <figure>
          <img src="images/hatsady-avatar.png" alt="Ảnh chân dung sinh viên Phommaket Hatsady" width="120" height="120" loading="lazy">
          <figcaption><strong>Phommaket Hatsady</strong><br><small>MSV: 3120224181</small></figcaption>
        </figure>
        <p><span class="huy-hieu huy-hieu--thanh-cong">Thành viên &bull; Phụ trách PHẦN B</span></p>
        <p>
          <a href="https://github.com/daimobile33076-eng" target="_blank" rel="noopener noreferrer" class="github-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: daimobile33076-eng</span>
          </a>
        </p>
        <p class="the__mo-ta">Danh mục sách danh-sach.php (tìm kiếm an toàn, lọc chuyên ngành, phân trang) &amp; dịch vụ Giỏ hàng.</p>
        
        <div class="duty-box">
          <strong>📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul>
            <li>Biện pháp 5: Cấu hình bảo vệ Apache qua tập tin <code>.htaccess</code>.</li>
            <li>Chặn truy cập tệp nội bộ: <code>.env</code>, <code>.git</code>, <code>.sql</code>, <code>.log</code> (HTTP 404/403).</li>
            <li>Chặn thực thi PHP trong thư mục tải lên <code>uploads/</code>.</li>
            <li>Báo cáo Phần B (Quản lý dự án công nghệ) &amp; Thảo luận C.3.</li>
            <li>Chụp ảnh minh chứng Hình 7 kiểm thử Apache.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224181_hatsady/gioithieu.php" class="nut nut--chinh">Xem hồ sơ cá nhân &rarr;</a>
      </article>

      <!-- Thành viên 3: Vongsena Sauphasith -->
      <article class="the-thanh-vien">
        <figure>
          <img src="images/sauphasith-avatar.png" alt="Ảnh chân dung sinh viên Vongsena Sauphasith" width="120" height="120" loading="lazy">
          <figcaption><strong>Vongsena Sauphasith</strong><br><small>MSV: 3120224186</small></figcaption>
        </figure>
        <p><span class="huy-hieu huy-hieu--thanh-cong">Thành viên &bull; Phụ trách PHẦN C</span></p>
        <p>
          <a href="https://github.com/happy2072005-prog" target="_blank" rel="noopener noreferrer" class="github-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: happy2072005-prog</span>
          </a>
        </p>
        <p class="the__mo-ta">Biểu mẫu liên hệ lien-he.php, kiểm tra upload ảnh an toàn MIME finfo và xử lý lỗi hệ thống.</p>
        
        <div class="duty-box">
          <strong>📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul>
            <li>Biện pháp 4: Quản lý biến môi trường <code>.env</code> với thư viện <code>vlucas/phpdotenv</code>.</li>
            <li>Thiết lập <code>.gitignore</code>, <code>.env.example</code>, kiểm tra <code>composer audit</code>.</li>
            <li>Biện pháp 6: Phân quyền CSDL tối thiểu <code>nhom12_app</code> (chỉ DML, cấm DDL).</li>
            <li>Báo cáo Phần B (Lưu lượng truy cập) &amp; Thảo luận C.4 (.env leak).</li>
            <li>Chụp ảnh minh chứng Hình 8 phân quyền CSDL.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224186_sauphasith/gioithieu.php" class="nut nut--chinh">Xem hồ sơ cá nhân &rarr;</a>
      </article>

      <!-- Thành viên 4: Nguyễn Thị Sinh -->
      <article class="the-thanh-vien">
        <figure>
          <img src="images/sinh-avatar.png" alt="Ảnh chân dung sinh viên Nguyễn Thị Sinh" width="120" height="120" loading="lazy">
          <figcaption><strong>Nguyễn Thị Sinh</strong><br><small>MSV: 3120223169</small></figcaption>
        </figure>
        <p><span class="huy-hieu huy-hieu--thanh-cong">Thành viên &bull; Phụ trách PHẦN D</span></p>
        <p>
          <a href="https://github.com/chingg2005" target="_blank" rel="noopener noreferrer" class="github-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: chingg2005</span>
          </a>
        </p>
        <p class="the__mo-ta">Trang chi tiết chi-tiet.php, cookie da_xem, đăng ký/đăng nhập và phân quyền quản trị.</p>
        
        <div class="duty-box">
          <strong>📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul>
            <li>Biện pháp 2: Bảo vệ phiên làm việc (Session Security): HttpOnly, SameSite=Lax, hủy 30 phút.</li>
            <li>Biện pháp 3: Bộ tiêu đề bảo mật HTTP (CSP script-src 'self', frame-ancestors 'none', nosniff).</li>
            <li>Báo cáo Phần B (Sách yêu thích &amp; quyền 403) &amp; Thảo luận C.2.</li>
            <li>Chụp ảnh minh chứng Hình 2 (Headers) và Hình 3 (Cookies).</li>
          </ul>
        </div>

        <a href="thanhvien/3120223169_sinh/gioithieu.php" class="nut nut--chinh">Xem hồ sơ cá nhân &rarr;</a>
      </article>

    </div>
  </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
