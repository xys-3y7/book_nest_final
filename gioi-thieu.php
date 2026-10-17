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

<main class="noi-dung-chinh khung-chua" style="max-width: 1100px; margin: 30px auto;">
  <h1 style="color: #1e3b6d; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 25px;">
    Giới thiệu Nhóm 12 (Lớp 24CNTT3) &amp; Đồ án BookNest
  </h1>

  <!-- 1. Tổng quan đồ án môn học -->
  <section style="background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 35px;">
    <h2 style="color: #1e3b6d; margin-top: 0;">📚 Tổng quan về đồ án môn học</h2>
    <p class="doan-van-dai" style="line-height: 1.7; color: #334155;">
      Dự án website <strong>BookNest</strong> được xây dựng trong khuôn khổ học phần <em>Thiết kế và Lập trình web</em> (Web Design and Programming), Học kỳ 1 năm học 2026 - 2027 tại Khoa Toán - Tin, Trường Đại học Sư phạm - Đại học Đà Nẵng.
    </p>
    <p class="doan-van-dai" style="line-height: 1.7; color: #334155;">
      Tại <strong>Bài tập nhóm số 7 (Chương 7: Bảo mật ứng dụng web)</strong>, Nhóm 12 đã gia cố toàn diện website với <strong>7 biện pháp bảo mật cốt lõi</strong> phòng chống các mối đe dọa OWASP Top 10 (CSRF, Brute Force, Session Hijacking, Clickjacking, Rò rỉ cấu hình máy chủ, Khai thác đặc quyền CSDL và quản lý biến môi trường an toàn).
    </p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 20px;">
      <div style="background: #f8fafc; padding: 16px; border-radius: 6px; border-left: 4px solid #1e3b6d;">
        <h4 style="margin: 0 0 8px 0; color: #1e3b6d;">🎯 Tầm nhìn dự án</h4>
        <p style="margin: 0; font-size: 0.9rem; color: #475569; line-height: 1.6;">
          Trở thành cổng thông tin tra cứu và phân phối giáo trình, văn phòng phẩm trực tuyến hàng đầu cho sinh viên Trường Đại học Sư phạm - ĐH Đà Nẵng.
        </p>
      </div>
      <div style="background: #f8fafc; padding: 16px; border-radius: 6px; border-left: 4px solid #ea580c;">
        <h4 style="margin: 0 0 8px 0; color: #ea580c;">🛡️ Tiêu chuẩn bảo mật</h4>
        <p style="margin: 0; font-size: 0.9rem; color: #475569; line-height: 1.6;">
          Phòng thủ theo chiều sâu (Defense-in-Depth): 100% POST forms có token CSRF, bảo vệ Session Strict Mode, CSP headers, Apache .htaccess và dotenv.
        </p>
      </div>
    </div>
  </section>

  <!-- 2. Danh sách thành viên Nhóm 12 và Phân công làm báo cáo -->
  <section>
    <div style="margin-bottom: 25px;">
      <h2 style="color: #1e3b6d; margin-bottom: 8px;">👥 Danh sách thành viên Nhóm 12 (Lớp 24CNTT3)</h2>
      <p style="color: #64748b; margin: 0;">
        Nhóm 12 gồm 4 sinh viên chuyên ngành Công nghệ thông tin cùng hợp tác và phân chia nhiệm vụ chuyên trách trong việc phát triển đồ án và thực hiện Báo cáo Bài tập số 7:
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
      
      <!-- Thành viên 1: Xaiyasith Yoi -->
      <article class="the-thanh-vien" style="display: flex; flex-direction: column; text-align: left; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
          <img src="images/yoi-avatar.png" alt="Ảnh Xaiyasith Yoi" width="90" height="90" style="border-radius: 50%; object-fit: cover; border: 3px solid #1e3b6d; flex-shrink: 0;">
          <div>
            <h3 style="margin: 0; color: #1e3b6d; font-size: 1.25rem;">Xaiyasith Yoi</h3>
            <p style="margin: 3px 0; font-size: 0.85rem; color: #64748b;">MSSV: <strong>3120224189</strong></p>
            <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.75rem;">Nhóm trưởng</span>
          </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.9rem;">
          <p style="margin: 4px 0;"><strong>GitHub:</strong> <a href="https://github.com/xys-3y7" target="_blank" rel="noopener noreferrer" style="color: #0369a1; font-weight: bold;">@xys-3y7</a></p>
          <p style="margin: 4px 0; color: #475569;"><strong>Vai trò website:</strong> Kiến trúc khung PHP chung, trang chủ, giỏ hàng Session, yêu thích và quản trị đơn hàng.</p>
        </div>

        <!-- Khối nhiệm vụ làm báo cáo -->
        <div style="background: #f1f5f9; padding: 12px; border-radius: 6px; font-size: 0.85rem; line-height: 1.5; color: #334155; margin-bottom: 15px; flex-grow: 1;">
          <strong style="color: #1e3b6d; display: block; margin-bottom: 4px;">📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul style="margin: 0; padding-left: 18px;">
            <li>Chủ trì tổng hợp &amp; định dạng báo cáo (Word &amp; PDF).</li>
            <li><strong>Biện pháp 1:</strong> CSRF Token trên 19 form POST &amp; API fetch.</li>
            <li><strong>Biện pháp 7:</strong> Rate limiting 429 &amp; ghi nhật ký bảo mật độc lập.</li>
            <li>Xây dựng bộ kiểm thử tự động 14 ca (<code>tests/bao-mat.sh</code>).</li>
            <li>Báo cáo Phần B (Lưu bút &amp; xóa bảo mật) &amp; Thảo luận C.1.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224189_yoi/gioithieu.php" class="nut nut--chinh" style="width: 100%; text-align: center; justify-content: center; font-size: 0.9rem; padding: 8px 12px;">
          Xem hồ sơ cá nhân chi tiết &rarr;
        </a>
      </article>

      <!-- Thành viên 2: Phommaket Hatsady -->
      <article class="the-thanh-vien" style="display: flex; flex-direction: column; text-align: left; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
          <img src="images/hatsady-avatar.png" alt="Ảnh Phommaket Hatsady" width="90" height="90" style="border-radius: 50%; object-fit: cover; border: 3px solid #1e3b6d; flex-shrink: 0;">
          <div>
            <h3 style="margin: 0; color: #1e3b6d; font-size: 1.25rem;">Phommaket Hatsady</h3>
            <p style="margin: 3px 0; font-size: 0.85rem; color: #64748b;">MSSV: <strong>3120224181</strong></p>
            <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.75rem;">Thành viên</span>
          </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.9rem;">
          <p style="margin: 4px 0;"><strong>GitHub:</strong> <a href="https://github.com/daimobile33076-eng" target="_blank" rel="noopener noreferrer" style="color: #0369a1; font-weight: bold;">@daimobile33076-eng</a></p>
          <p style="margin: 4px 0; color: #475569;"><strong>Vai trò website:</strong> Danh mục sách danh-sach.php (tìm kiếm LIKE an toàn, lọc chuyên ngành, phân trang) &amp; dịch vụ GioHang.</p>
        </div>

        <!-- Khối nhiệm vụ làm báo cáo -->
        <div style="background: #f1f5f9; padding: 12px; border-radius: 6px; font-size: 0.85rem; line-height: 1.5; color: #334155; margin-bottom: 15px; flex-grow: 1;">
          <strong style="color: #1e3b6d; display: block; margin-bottom: 4px;">📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul style="margin: 0; padding-left: 18px;">
            <li><strong>Biện pháp 5:</strong> Bảo vệ máy chủ Apache qua tập tin <code>.htaccess</code>.</li>
            <li>Chặn truy cập tệp nội bộ: <code>.env</code>, <code>.git</code>, <code>.sql</code>, <code>.log</code>, <code>composer.json</code> (HTTP 404/403).</li>
            <li>Chặn thực thi PHP trong <code>uploads/</code> với <code>SetHandler None</code>.</li>
            <li>Báo cáo Phần B (Quản lý dự án công nghệ) &amp; Thảo luận C.3.</li>
            <li>Chụp ảnh minh chứng Hình 7 kiểm thử Apache.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224181_hatsady/gioithieu.php" class="nut nut--chinh" style="width: 100%; text-align: center; justify-content: center; font-size: 0.9rem; padding: 8px 12px;">
          Xem hồ sơ cá nhân chi tiết &rarr;
        </a>
      </article>

      <!-- Thành viên 3: Vongsena Sauphasith -->
      <article class="the-thanh-vien" style="display: flex; flex-direction: column; text-align: left; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
          <img src="images/sauphasith-avatar.png" alt="Ảnh Vongsena Sauphasith" width="90" height="90" style="border-radius: 50%; object-fit: cover; border: 3px solid #1e3b6d; flex-shrink: 0;">
          <div>
            <h3 style="margin: 0; color: #1e3b6d; font-size: 1.25rem;">Vongsena Sauphasith</h3>
            <p style="margin: 3px 0; font-size: 0.85rem; color: #64748b;">MSSV: <strong>3120224186</strong></p>
            <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.75rem;">Thành viên</span>
          </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.9rem;">
          <p style="margin: 4px 0;"><strong>GitHub:</strong> <a href="https://github.com/happy2072005-prog" target="_blank" rel="noopener noreferrer" style="color: #0369a1; font-weight: bold;">@happy2072005-prog</a></p>
          <p style="margin: 4px 0; color: #475569;"><strong>Vai trò website:</strong> Biểu mẫu liên hệ lien-he.php, kiểm tra upload ảnh an toàn finfo và xử lý lỗi hệ thống.</p>
        </div>

        <!-- Khối nhiệm vụ làm báo cáo -->
        <div style="background: #f1f5f9; padding: 12px; border-radius: 6px; font-size: 0.85rem; line-height: 1.5; color: #334155; margin-bottom: 15px; flex-grow: 1;">
          <strong style="color: #1e3b6d; display: block; margin-bottom: 4px;">📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul style="margin: 0; padding-left: 18px;">
            <li><strong>Biện pháp 4:</strong> Biến môi trường bảo mật <code>.env</code> với thư viện <code>vlucas/phpdotenv</code> v5.7.</li>
            <li>Thiết lập <code>.gitignore</code>, <code>.env.example</code>, kiểm tra <code>composer audit</code>.</li>
            <li><strong>Biện pháp 6:</strong> Phân quyền CSDL tối thiểu <code>nhom12_app</code> (chỉ DML, cấm DDL báo lỗi 1142).</li>
            <li>Báo cáo Phần B (Lưu lượng truy cập) &amp; Thảo luận C.4 (.env leak).</li>
            <li>Chụp ảnh minh chứng Hình 8 phân quyền CSDL.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224186_sauphasith/gioithieu.php" class="nut nut--chinh" style="width: 100%; text-align: center; justify-content: center; font-size: 0.9rem; padding: 8px 12px;">
          Xem hồ sơ cá nhân chi tiết &rarr;
        </a>
      </article>

      <!-- Thành viên 4: Nguyễn Thị Sinh -->
      <article class="the-thanh-vien" style="display: flex; flex-direction: column; text-align: left; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
          <img src="images/sinh-avatar.png" alt="Ảnh Nguyễn Thị Sinh" width="90" height="90" style="border-radius: 50%; object-fit: cover; border: 3px solid #1e3b6d; flex-shrink: 0;">
          <div>
            <h3 style="margin: 0; color: #1e3b6d; font-size: 1.25rem;">Nguyễn Thị Sinh</h3>
            <p style="margin: 3px 0; font-size: 0.85rem; color: #64748b;">MSSV: <strong>3120223169</strong></p>
            <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.75rem;">Thành viên</span>
          </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.9rem;">
          <p style="margin: 4px 0;"><strong>GitHub:</strong> <a href="https://github.com/chingg2005" target="_blank" rel="noopener noreferrer" style="color: #0369a1; font-weight: bold;">@chingg2005</a></p>
          <p style="margin: 4px 0; color: #475569;"><strong>Vai trò website:</strong> Trang chi tiết chi-tiet.php, cookie da_xem, đăng ký/đăng nhập và phân quyền quản trị.</p>
        </div>

        <!-- Khối nhiệm vụ làm báo cáo -->
        <div style="background: #f1f5f9; padding: 12px; border-radius: 6px; font-size: 0.85rem; line-height: 1.5; color: #334155; margin-bottom: 15px; flex-grow: 1;">
          <strong style="color: #1e3b6d; display: block; margin-bottom: 4px;">📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul style="margin: 0; padding-left: 18px;">
            <li><strong>Biện pháp 2:</strong> Bảo vệ phiên (Session Security): HttpOnly, SameSite=Lax, hủy phiên 30 phút, chống Fixation.</li>
            <li><strong>Biện pháp 3:</strong> Bộ tiêu đề bảo mật HTTP (CSP, X-Frame-Options, X-Content-Type-Options, xóa X-Powered-By).</li>
            <li>Báo cáo Phần B (Sách yêu thích &amp; quyền 403) &amp; Thảo luận C.2 (HttpOnly vs XSS).</li>
            <li>Chụp ảnh minh chứng Hình 2 (Headers) và Hình 3 (Cookies).</li>
          </ul>
        </div>

        <a href="thanhvien/3120223169_sinh/gioithieu.php" class="nut nut--chinh" style="width: 100%; text-align: center; justify-content: center; font-size: 0.9rem; padding: 8px 12px;">
          Xem hồ sơ cá nhân chi tiết &rarr;
        </a>
      </article>

    </div>
  </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
