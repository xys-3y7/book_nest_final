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

<main class="noi-dung-chinh khung-chua" style="max-width: 1150px; margin: 30px auto;">
  
  <!-- Hero Banner Giới thiệu nhóm -->
  <section style="background: linear-gradient(135deg, #0f2b5c 0%, #1e4b8a 60%, #0369a1 100%); color: #ffffff; padding: 35px 30px; border-radius: 18px; box-shadow: 0 10px 25px -5px rgba(15, 43, 92, 0.3); margin-bottom: 35px; position: relative; overflow: hidden;">
    <div style="position: relative; z-index: 2; max-width: 800px;">
      <span style="display: inline-block; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px); color: #bae6fd; font-size: 0.8rem; font-weight: 700; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
        🎓 Học phần: Thiết kế và Lập trình Web (HK1 2026–2027)
      </span>
      <h1 style="color: #ffffff; margin: 0 0 12px 0; font-size: 2.1rem; font-weight: 800; letter-spacing: -0.5px;">
        Đồ án BookNest &ndash; Nhóm 12 (Lớp 24CNTT3)
      </h1>
      <p style="margin: 0; font-size: 1.05rem; line-height: 1.6; color: #e0f2fe;">
        Nhà sách trực tuyến chuyên cung cấp giáo trình đại học, sách chuyên ngành CNTT và tài liệu học thuật cho sinh viên Trường Đại học Sư phạm &ndash; Đại học Đà Nẵng.
      </p>
    </div>
  </section>

  <!-- 1. Tổng quan đồ án môn học -->
  <section style="background: #ffffff; padding: 28px 30px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 35px; box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);">
    <h2 style="color: #0f2b5c; margin-top: 0; font-size: 1.4rem; display: flex; align-items: center; gap: 8px;">
      <span>📚</span> Tổng quan về đồ án &amp; Kiến trúc hệ thống
    </h2>
    <p class="doan-van-dai" style="line-height: 1.75; color: #334155; margin-bottom: 15px;">
      Dự án website <strong>BookNest</strong> được thiết kế và hiện thực bởi Nhóm 12 theo mô hình phân tầng hướng đối tượng trên nền tảng <strong>PHP 8</strong> kết hợp hệ quản trị cơ sở dữ liệu quan hệ <strong>MySQL / MariaDB</strong> thông qua lớp trừu tượng PDO an toàn.
    </p>
    <p class="doan-van-dai" style="line-height: 1.75; color: #334155;">
      Tại <strong>Bài tập nhóm số 7 (Chương 7: Bảo mật ứng dụng web)</strong>, hệ thống đã được gia cố toàn diện theo mô hình <strong>Phòng thủ theo chiều sâu (Defense-in-Depth)</strong>: ngăn chặn CSRF trên 100% biểu mẫu POST, bảo mật phiên làm việc HttpOnly/SameSite, bộ tiêu đề CSP chặt chẽ, kiểm soát truy cập máy chủ Apache <code>.htaccess</code>, bảo vệ biến môi trường <code>.env</code> và cấp quyền CSDL tối thiểu.
    </p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-top: 25px;">
      <div style="background: #f8fafc; padding: 18px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 4px solid #0284c7;">
        <h4 style="margin: 0 0 8px 0; color: #0369a1; font-size: 1.05rem;">🎯 Sứ mệnh &amp; Mục tiêu</h4>
        <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
          Số hóa quy trình đặt mua, mượn và phân phối giáo trình chuẩn hóa cho sinh viên UED; trợ giá giáo trình từ 10% đến 25% giúp giảm thiểu chi phí học tập.
        </p>
      </div>
      <div style="background: #f8fafc; padding: 18px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 4px solid #ea580c;">
        <h4 style="margin: 0 0 8px 0; color: #ea580c; font-size: 1.05rem;">🛡️ An toàn thông tin chuẩn OWASP</h4>
        <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
          Đạt 14/14 ca kiểm thử bảo mật phủ định (Negative Tests), chống Brute-force 429 sau 5 lần sai, nhật ký an ninh độc lập và mã nguồn sạch 100% không rò rỉ bí mật.
        </p>
      </div>
    </div>
  </section>

  <!-- 2. Danh sách thành viên Nhóm 12 và Phân công làm báo cáo -->
  <section>
    <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
      <div>
        <h2 style="color: #0f2b5c; margin: 0 0 6px 0; font-size: 1.45rem;">
          👥 Danh sách thành viên Nhóm 12 (Lớp 24CNTT3)
        </h2>
        <p style="color: #64748b; margin: 0; font-size: 0.95rem;">
          Bấm vào hồ sơ từng thành viên để xem chi tiết quá trình phát triển, chức năng cá nhân và nhiệm vụ viết báo cáo:
        </p>
      </div>
      <span style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.85rem; padding: 6px 14px; border-radius: 9999px;">
        Tổng số: 4 thành viên (100% Hoàn thành)
      </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
      
      <!-- Thành viên 1: Xaiyasith Yoi -->
      <article class="the-thanh-vien">
        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
          <img src="images/yoi-avatar.png" alt="Ảnh Xaiyasith Yoi" width="90" height="90" loading="lazy">
          <div>
            <h3 style="margin: 0; color: #0f2b5c; font-size: 1.25rem;">Xaiyasith Yoi</h3>
            <p style="margin: 3px 0; font-size: 0.85rem; color: #64748b;">MSSV: <strong>3120224189</strong></p>
            <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.72rem;">Nhóm trưởng</span>
          </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.9rem;">
          <div style="margin: 6px 0;">
            <a href="https://github.com/xys-3y7" target="_blank" rel="noopener noreferrer" class="github-pill">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
              <span>xys-3y7</span>
            </a>
          </div>
          <p style="margin: 6px 0; color: #475569; font-size: 0.85rem;">
            <strong>Vai trò:</strong> Kiến trúc khung web PHP, trang chủ, giỏ hàng Session, yêu thích và quản trị đơn hàng.
          </p>
        </div>

        <!-- Khối nhiệm vụ làm báo cáo -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 10px; font-size: 0.85rem; line-height: 1.5; color: #334155; margin-bottom: 18px; flex-grow: 1;">
          <strong style="color: #0f2b5c; display: block; margin-bottom: 6px; font-size: 0.9rem;">📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul style="margin: 0; padding-left: 18px;">
            <li>Chủ trì tổng hợp &amp; định dạng báo cáo (Word &amp; PDF).</li>
            <li><strong>Biện pháp 1:</strong> CSRF Token trên 19 form POST &amp; API fetch.</li>
            <li><strong>Biện pháp 7:</strong> Rate limiting 429 &amp; ghi nhật ký bảo mật độc lập.</li>
            <li>Xây dựng bộ kiểm thử tự động 14 ca (<code>tests/bao-mat.sh</code>).</li>
            <li>Báo cáo Phần B (Lưu bút &amp; xóa bảo mật) &amp; Thảo luận C.1.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224189_yoi/gioithieu.php" class="nut nut--chinh" style="width: 100%; text-align: center; justify-content: center; font-size: 0.9rem; padding: 9px 14px; border-radius: 8px;">
          Xem hồ sơ cá nhân chi tiết &rarr;
        </a>
      </article>

      <!-- Thành viên 2: Phommaket Hatsady -->
      <article class="the-thanh-vien">
        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
          <img src="images/hatsady-avatar.png" alt="Ảnh Phommaket Hatsady" width="90" height="90" loading="lazy">
          <div>
            <h3 style="margin: 0; color: #0f2b5c; font-size: 1.25rem;">Phommaket Hatsady</h3>
            <p style="margin: 3px 0; font-size: 0.85rem; color: #64748b;">MSSV: <strong>3120224181</strong></p>
            <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.72rem;">Thành viên</span>
          </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.9rem;">
          <div style="margin: 6px 0;">
            <a href="https://github.com/daimobile33076-eng" target="_blank" rel="noopener noreferrer" class="github-pill">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
              <span>daimobile33076-eng</span>
            </a>
          </div>
          <p style="margin: 6px 0; color: #475569; font-size: 0.85rem;">
            <strong>Vai trò:</strong> Danh mục sách danh-sach.php (tìm kiếm an toàn, lọc chuyên ngành, phân trang) &amp; dịch vụ Giỏ hàng.
          </p>
        </div>

        <!-- Khối nhiệm vụ làm báo cáo -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 10px; font-size: 0.85rem; line-height: 1.5; color: #334155; margin-bottom: 18px; flex-grow: 1;">
          <strong style="color: #0f2b5c; display: block; margin-bottom: 6px; font-size: 0.9rem;">📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul style="margin: 0; padding-left: 18px;">
            <li><strong>Biện pháp 5:</strong> Bảo vệ máy chủ Apache qua tập tin <code>.htaccess</code>.</li>
            <li>Chặn truy cập tệp nội bộ: <code>.env</code>, <code>.git</code>, <code>.sql</code>, <code>.log</code>, <code>composer.json</code> (HTTP 404/403).</li>
            <li>Chặn thực thi PHP trong <code>uploads/</code> với <code>SetHandler None</code>.</li>
            <li>Báo cáo Phần B (Quản lý dự án công nghệ) &amp; Thảo luận C.3.</li>
            <li>Chụp ảnh minh chứng Hình 7 kiểm thử Apache.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224181_hatsady/gioithieu.php" class="nut nut--chinh" style="width: 100%; text-align: center; justify-content: center; font-size: 0.9rem; padding: 9px 14px; border-radius: 8px;">
          Xem hồ sơ cá nhân chi tiết &rarr;
        </a>
      </article>

      <!-- Thành viên 3: Vongsena Sauphasith -->
      <article class="the-thanh-vien">
        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
          <img src="images/sauphasith-avatar.png" alt="Ảnh Vongsena Sauphasith" width="90" height="90" loading="lazy">
          <div>
            <h3 style="margin: 0; color: #0f2b5c; font-size: 1.25rem;">Vongsena Sauphasith</h3>
            <p style="margin: 3px 0; font-size: 0.85rem; color: #64748b;">MSSV: <strong>3120224186</strong></p>
            <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.72rem;">Thành viên</span>
          </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.9rem;">
          <div style="margin: 6px 0;">
            <a href="https://github.com/happy2072005-prog" target="_blank" rel="noopener noreferrer" class="github-pill">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
              <span>happy2072005-prog</span>
            </a>
          </div>
          <p style="margin: 6px 0; color: #475569; font-size: 0.85rem;">
            <strong>Vai trò:</strong> Biểu mẫu liên hệ lien-he.php, kiểm tra upload ảnh an toàn MIME finfo và xử lý lỗi hệ thống.
          </p>
        </div>

        <!-- Khối nhiệm vụ làm báo cáo -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 10px; font-size: 0.85rem; line-height: 1.5; color: #334155; margin-bottom: 18px; flex-grow: 1;">
          <strong style="color: #0f2b5c; display: block; margin-bottom: 6px; font-size: 0.9rem;">📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul style="margin: 0; padding-left: 18px;">
            <li><strong>Biện pháp 4:</strong> Quản lý biến môi trường <code>.env</code> với thư viện <code>vlucas/phpdotenv</code> v5.7.</li>
            <li>Thiết lập <code>.gitignore</code>, <code>.env.example</code>, kiểm tra <code>composer audit</code> (0 lỗ hổng).</li>
            <li><strong>Biện pháp 6:</strong> Phân quyền CSDL tối thiểu <code>nhom12_app</code> (chỉ DML, cấm DDL báo lỗi 1142).</li>
            <li>Báo cáo Phần B (Lưu lượng truy cập) &amp; Thảo luận C.4 (.env leak).</li>
            <li>Chụp ảnh minh chứng Hình 8 phân quyền CSDL.</li>
          </ul>
        </div>

        <a href="thanhvien/3120224186_sauphasith/gioithieu.php" class="nut nut--chinh" style="width: 100%; text-align: center; justify-content: center; font-size: 0.9rem; padding: 9px 14px; border-radius: 8px;">
          Xem hồ sơ cá nhân chi tiết &rarr;
        </a>
      </article>

      <!-- Thành viên 4: Nguyễn Thị Sinh -->
      <article class="the-thanh-vien">
        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
          <img src="images/sinh-avatar.png" alt="Ảnh Nguyễn Thị Sinh" width="90" height="90" loading="lazy">
          <div>
            <h3 style="margin: 0; color: #0f2b5c; font-size: 1.25rem;">Nguyễn Thị Sinh</h3>
            <p style="margin: 3px 0; font-size: 0.85rem; color: #64748b;">MSSV: <strong>3120223169</strong></p>
            <span class="huy-hieu huy-hieu--thanh-cong" style="font-size: 0.72rem;">Thành viên</span>
          </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.9rem;">
          <div style="margin: 6px 0;">
            <a href="https://github.com/chingg2005" target="_blank" rel="noopener noreferrer" class="github-pill">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
              <span>chingg2005</span>
            </a>
          </div>
          <p style="margin: 6px 0; color: #475569; font-size: 0.85rem;">
            <strong>Vai trò:</strong> Trang chi tiết chi-tiet.php, cookie da_xem, đăng ký/đăng nhập và phân quyền quản trị.
          </p>
        </div>

        <!-- Khối nhiệm vụ làm báo cáo -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 10px; font-size: 0.85rem; line-height: 1.5; color: #334155; margin-bottom: 18px; flex-grow: 1;">
          <strong style="color: #0f2b5c; display: block; margin-bottom: 6px; font-size: 0.9rem;">📋 Nhiệm vụ làm báo cáo Bài tập 7:</strong>
          <ul style="margin: 0; padding-left: 18px;">
            <li><strong>Biện pháp 2:</strong> Bảo vệ phiên làm việc (Session Security): HttpOnly, SameSite=Lax, hủy 30 phút, chống Fixation.</li>
            <li><strong>Biện pháp 3:</strong> Bộ tiêu đề bảo mật HTTP (CSP script-src 'self', frame-ancestors 'none', nosniff, gỡ bỏ X-Powered-By).</li>
            <li>Báo cáo Phần B (Sách yêu thích &amp; quyền 403) &amp; Thảo luận C.2 (HttpOnly vs XSS).</li>
            <li>Chụp ảnh minh chứng Hình 2 (Headers) và Hình 3 (Cookies).</li>
          </ul>
        </div>

        <a href="thanhvien/3120223169_sinh/gioithieu.php" class="nut nut--chinh" style="width: 100%; text-align: center; justify-content: center; font-size: 0.9rem; padding: 9px 14px; border-radius: 8px;">
          Xem hồ sơ cá nhân chi tiết &rarr;
        </a>
      </article>

    </div>
  </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
