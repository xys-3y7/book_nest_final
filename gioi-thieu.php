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

  <!-- 2. Mô hình Kiến trúc Phân quyền 3 Roles (Multi-vendor Bookstore) -->
  <section style="margin: 40px 0;">
    <h2>Kiến trúc Hệ thống &amp; Phân quyền 3 Vai trò (Multi-vendor Bookstore)</h2>
    <p class="doan-van-dai">
      Hệ thống BookNest được thiết kế theo mô hình sàn thương mại điện tử đa người bán (Multi-vendor Bookstore), tách biệt rõ ràng 3 vai trò: <strong>Customer (Khách hàng)</strong>, <strong>Admin (Quản trị viên)</strong>, và <strong>Book Owner (Chủ sách / Đối tác bán hàng)</strong>. Khách hàng mua sách từ các Chủ sách thông qua nền tảng, Chủ sách sở hữu Dashboard quản trị độc lập (<code>chu-sach.php</code>) và chỉ kiểm soát sách cùng doanh thu của chính mình, trong khi Admin kiểm soát toàn bộ vận hành hệ thống.
    </p>

    <!-- Khung diagram giống ảnh đính kèm của người dùng -->
    <div style="background: #090d16; border: 1px solid #1e293b; border-radius: 12px; padding: 28px 20px; margin: 25px 0; color: #f8fafc; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
      
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; border-bottom: 1px solid #1e293b; padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
        <h3 style="margin: 0; font-size: 1.35rem; color: #ffffff; font-weight: 700; display: flex; align-items: center; gap: 8px;">
          <span>ความสัมพันธ์ของระบบ</span>
          <span style="font-size: 0.9rem; color: #94a3b8; font-weight: 400;">(Mối quan hệ trong hệ thống)</span>
        </h3>
        <span style="background: #1e293b; color: #38bdf8; font-size: 0.75rem; padding: 4px 10px; border-radius: 999px; font-weight: 600;">3-Role Multi-vendor Architecture</span>
      </div>

      <!-- Sơ đồ quan hệ 3 hộp -->
      <div style="max-width: 800px; margin: 0 auto;">
        
        <!-- Hộp Admin ở trên -->
        <div style="background: #0d1527; border: 1px solid #1e293b; border-radius: 8px; padding: 18px 24px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
          <div style="font-size: 1.35rem; font-weight: 800; color: #ffffff; margin-bottom: 4px; letter-spacing: 0.5px;">Admin</div>
          <div style="color: #94a3b8; font-size: 0.95rem;">ควบคุมและจัดการระบบทั้งหมด (Quản trị &amp; Kiểm soát toàn bộ hệ thống)</div>
        </div>

        <!-- Mũi tên chỉ xuống -->
        <div style="text-align: center; color: #94a3b8; font-size: 1.6rem; line-height: 1; margin: 12px 0;">↓</div>

        <!-- Hai hộp bên dưới: Customer & Book Owner -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          
          <!-- Hộp Customer (xanh lá trầm) -->
          <div style="background: #061a12; border: 1px solid #064e3b; border-radius: 8px; padding: 18px 20px; text-align: center;">
            <div style="font-size: 1.25rem; font-weight: 800; color: #ffffff; margin-bottom: 4px;">Customer</div>
            <div style="color: #6ee7b7; font-size: 0.95rem;">เลือกซื้อหนังสือ (Chọn mua giáo trình)</div>
          </div>

          <!-- Hộp Book Owner (nâu hổ phách trầm) -->
          <div style="background: #1c1306; border: 1px solid #78350f; border-radius: 8px; padding: 18px 20px; text-align: center;">
            <div style="font-size: 1.25rem; font-weight: 800; color: #ffffff; margin-bottom: 4px;">Book Owner</div>
            <div style="color: #fcd34d; font-size: 0.95rem;">นำหนังสือมาขาย (Đăng ký mở quầy &amp; bán sách)</div>
          </div>

        </div>

        <!-- Chú thích dưới sơ đồ -->
        <div style="text-align: center; margin-top: 18px; color: #cbd5e1; font-size: 0.92rem; font-weight: 500;">
          Customer ซื้อหนังสือจาก Book Owner ผ่านระบบ (Khách hàng mua sách từ Chủ sách qua sàn BookNest)
        </div>

      </div>

      <!-- Bảng so sánh phân quyền -->
      <div style="margin-top: 36px; border-top: 1px solid #1e293b; padding-top: 24px;">
        <h3 style="margin: 0 0 16px 0; font-size: 1.25rem; color: #ffffff; font-weight: 700; display: flex; align-items: center; gap: 8px;">
          <span>ตารางเปรียบเทียบสิทธิ์</span>
          <span style="font-size: 0.88rem; color: #94a3b8; font-weight: 400;">(Bảng ma trận so sánh phân quyền 3 vai trò)</span>
        </h3>

        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.92rem; color: #e2e8f0;">
            <thead>
              <tr style="border-bottom: 1px solid #334155; color: #94a3b8; font-weight: 600;">
                <th style="padding: 12px 14px; text-align: left;">ฟังก์ชัน (Chức năng)</th>
                <th style="padding: 12px 14px; text-align: center; width: 18%;">Customer</th>
                <th style="padding: 12px 14px; text-align: center; width: 18%;">Admin</th>
                <th style="padding: 12px 14px; text-align: center; width: 22%;">Book Owner</th>
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">ดูหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Xem thông tin sách)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
              </tr>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">ซื้อหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Đặt hàng &amp; Thanh toán)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
              </tr>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">จัดการตะกร้า <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Thêm/xóa giỏ hàng)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
              </tr>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">เพิ่มหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Đăng bán sách mới)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
              </tr>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">แก้ไขหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Cập nhật thông tin &amp; giá)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #f59e0b; font-weight: 600;">เฉพาะของตน</td>
              </tr>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">ลบหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Gỡ sản phẩm khỏi sàn)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #f59e0b; font-weight: 600;">เฉพาะของตน</td>
              </tr>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">จัดการผู้ใช้ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Khóa/mở tài khoản)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
              </tr>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">จัดการเจ้าของหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Kiểm soát đối tác)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
              </tr>
              <tr style="border-bottom: 1px solid #1e293b;">
                <td style="padding: 12px 14px; font-weight: 600;">ดูยอดขาย <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Báo cáo doanh thu)</span></td>
                <td style="padding: 12px 14px; text-align: center; color: #64748b;">-</td>
                <td style="padding: 12px 14px; text-align: center; color: #34d399; font-weight: bold; font-size: 1.1rem;">✓</td>
                <td style="padding: 12px 14px; text-align: center; color: #f59e0b; font-weight: 600;">เฉพาะของตน</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 3 Khối chi tiết chức năng của 3 Roles -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-top: 28px;">
        
        <!-- Khối 1: Customer -->
        <div style="background: #0d1527; border: 1px solid #1e293b; border-radius: 8px; padding: 16px 18px;">
          <h4 style="margin: 0 0 10px 0; color: #38bdf8; font-size: 1rem; display: flex; align-items: center; gap: 6px;">
            <span>👤</span>
            <span>1. Customer (ลูกค้า)</span>
          </h4>
          <ul style="margin: 0; padding-left: 18px; font-size: 0.85rem; color: #cbd5e1; line-height: 1.6;">
            <li>สมัครสมาชิก và เข้าสู่ระบบ</li>
            <li>ค้นหา và เลือกซื้อหนังสือ</li>
            <li>เพิ่มหนังสือลงตะกร้า (Cart)</li>
            <li>สั่งซื้อ và ชำระเงิน an toàn</li>
            <li>ติดตามสถานะคำสั่งซื้อ</li>
            <li>รีวิว và ให้คะแนนหนังสือ</li>
          </ul>
        </div>

        <!-- Khối 2: Admin -->
        <div style="background: #0d1527; border: 1px solid #1e293b; border-radius: 8px; padding: 16px 18px;">
          <h4 style="margin: 0 0 10px 0; color: #f43f5e; font-size: 1rem; display: flex; align-items: center; gap: 6px;">
            <span>👑</span>
            <span>2. Admin (ผู้ดูแลระบบ)</span>
          </h4>
          <ul style="margin: 0; padding-left: 18px; font-size: 0.85rem; color: #cbd5e1; line-height: 1.6;">
            <li>จัดการบัญชีผู้ใช้ (Users)</li>
            <li>จัดการข้อมูลหนังสือ và หมวดหมู่</li>
            <li>จัดการเจ้าของหนังสือ (Sellers)</li>
            <li>ตรวจสอบ và จัดการคำสั่งซื้อ</li>
            <li>ตรวจสอบรายงานยอดขาย toàn sàn</li>
            <li>จัดการสิทธิ์ของผู้ใช้งาน (RBAC)</li>
          </ul>
        </div>

        <!-- Khối 3: Book Owner -->
        <div style="background: #0d1527; border: 1px solid #1e293b; border-radius: 8px; padding: 16px 18px;">
          <h4 style="margin: 0 0 10px 0; color: #f59e0b; font-size: 1rem; display: flex; align-items: center; gap: 6px;">
            <span>🏪</span>
            <span>3. Book Owner (เจ้าของหนังสือ)</span>
          </h4>
          <ul style="margin: 0; padding-left: 18px; font-size: 0.85rem; color: #cbd5e1; line-height: 1.6;">
            <li>สมัครเป็นเจ้าของหนังสือ (Quầy bán)</li>
            <li>เพิ่มข้อมูลหนังสือที่ต้องการขาย</li>
            <li>แก้ไข và ลบ ข้อมูลหนังสือของตนเอง</li>
            <li>จัดการจำนวนหนังสือในสต็อก</li>
            <li>ดูรายการหนังสือที่ลูกค้าสั่งซื้อ</li>
            <li>ตรวจสอบยอดขาย &amp; รายได้ของตนเอง</li>
          </ul>
        </div>

      </div>

    </div>
  </section>

  <!-- 3. Danh sách thành viên Nhóm 12 -->
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
