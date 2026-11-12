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

    <!-- Khung diagram giống ảnh đính kèm của người dùng (Giao diện thẩm mỹ cao) -->
    <div style="background: radial-gradient(circle at 50% 0%, #1e293b 0%, #090d16 100%); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 16px; padding: 32px 28px; margin: 30px 0; color: #f8fafc; box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.5); backdrop-filter: blur(12px);">
      
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
          <h3 style="margin: 0; font-size: 1.5rem; font-weight: 800; font-family: 'Prompt', var(--font-family); background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: flex; align-items: center; gap: 10px;">
            <span>🌐</span>
            <span>ความสัมพันธ์ของระบบ</span>
          </h3>
          <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 0.92rem;">Mô hình Kiến trúc Phân quyền &amp; Luồng vận hành sàn TMĐT Multi-vendor</p>
        </div>
        <span style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: #bfdbfe; font-size: 0.8rem; padding: 6px 14px; border-radius: 9999px; font-weight: 700; border: 1px solid rgba(96, 165, 250, 0.4); box-shadow: 0 0 15px rgba(59, 130, 246, 0.25);">
          ⚡ 3-Role Multi-vendor Architecture
        </span>
      </div>

      <!-- Sơ đồ quan hệ 3 hộp chuẩn thẩm mỹ cao -->
      <div style="max-width: 820px; margin: 0 auto;">
        
        <!-- Hộp 1: Admin ở trên -->
        <div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); border: 1px solid rgba(99, 102, 241, 0.4); border-radius: 12px; padding: 22px 28px; text-align: center; box-shadow: 0 12px 28px -5px rgba(99, 102, 241, 0.25); position: relative; overflow: hidden;">
          <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #6366f1, #a855f7, #3b82f6);"></div>
          <span style="display: inline-block; background: rgba(99, 102, 241, 0.2); color: #c7d2fe; border: 1px solid rgba(99, 102, 241, 0.45); font-size: 0.72rem; font-weight: 800; padding: 3px 12px; border-radius: 999px; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 8px;">👑 System Administrator</span>
          <div style="font-size: 1.6rem; font-weight: 800; color: #ffffff; letter-spacing: 0.5px; font-family: 'Prompt', var(--font-family);">Admin</div>
          <div style="color: #cbd5e1; font-size: 1.05rem; font-weight: 500; font-family: 'Prompt', var(--font-family); margin-top: 4px;">
            ควบคุมและจัดการระบบทั้งหมด
          </div>
          <div style="color: #94a3b8; font-size: 0.85rem; margin-top: 2px;">(Toàn quyền kiểm soát hệ thống, quản lý người dùng, duyệt sản phẩm &amp; báo cáo tổng)</div>
        </div>

        <!-- Mũi tên kết nối thẩm mỹ -->
        <div style="display: flex; flex-direction: column; align-items: center; margin: 12px 0;">
          <div style="width: 2px; height: 16px; background: linear-gradient(to bottom, #6366f1, #38bdf8);"></div>
          <div style="width: 30px; height: 30px; border-radius: 50%; background: #0f172a; border: 2px solid #38bdf8; display: flex; align-items: center; justify-content: center; color: #38bdf8; font-weight: 800; font-size: 15px; box-shadow: 0 0 15px rgba(56, 189, 248, 0.5);">↓</div>
          <div style="width: 2px; height: 16px; background: linear-gradient(to bottom, #38bdf8, #10b981);"></div>
        </div>

        <!-- Hai hộp bên dưới: Customer & Book Owner -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          
          <!-- Hộp Customer -->
          <div style="background: linear-gradient(145deg, rgba(6, 36, 25, 0.92) 0%, rgba(3, 21, 14, 0.98) 100%); border: 1px solid rgba(16, 185, 129, 0.45); border-radius: 12px; padding: 22px 24px; text-align: center; box-shadow: 0 12px 28px -5px rgba(16, 185, 129, 0.2); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #10b981, #34d399);"></div>
            <span style="display: inline-block; background: rgba(16, 185, 129, 0.2); color: #a7f3d0; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.72rem; font-weight: 800; padding: 3px 12px; border-radius: 999px; text-transform: uppercase; margin-bottom: 8px;">👤 Buyer Role</span>
            <div style="font-size: 1.45rem; font-weight: 800; color: #ffffff; font-family: 'Prompt', var(--font-family);">Customer</div>
            <div style="color: #6ee7b7; font-size: 1.05rem; font-weight: 600; font-family: 'Prompt', var(--font-family); margin-top: 4px;">
              เลือกซื้อหนังสือ
            </div>
            <div style="color: #94a3b8; font-size: 0.85rem; margin-top: 2px;">(Tìm kiếm, giỏ hàng, đặt hàng &amp; theo dõi đơn mua)</div>
          </div>

          <!-- Hộp Book Owner -->
          <div style="background: linear-gradient(145deg, rgba(39, 22, 7, 0.92) 0%, rgba(21, 11, 2, 0.98) 100%); border: 1px solid rgba(245, 158, 11, 0.45); border-radius: 12px; padding: 22px 24px; text-align: center; box-shadow: 0 12px 28px -5px rgba(245, 158, 11, 0.2); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
            <span style="display: inline-block; background: rgba(245, 158, 11, 0.2); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.72rem; font-weight: 800; padding: 3px 12px; border-radius: 999px; text-transform: uppercase; margin-bottom: 8px;">🏪 Seller Role</span>
            <div style="font-size: 1.45rem; font-weight: 800; color: #ffffff; font-family: 'Prompt', var(--font-family);">Book Owner</div>
            <div style="color: #fcd34d; font-size: 1.05rem; font-weight: 600; font-family: 'Prompt', var(--font-family); margin-top: 4px;">
              นำหนังสือมาขาย
            </div>
            <div style="color: #94a3b8; font-size: 0.85rem; margin-top: 2px;">(Đăng ký mở quầy sách, đăng bán &amp; quản lý doanh thu)</div>
          </div>

        </div>

        <!-- Chú thích liên kết -->
        <div style="text-align: center; margin-top: 22px;">
          <span style="background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255,255,255,0.15); padding: 9px 22px; border-radius: 9999px; font-size: 0.95rem; font-weight: 600; color: #f8fafc; box-shadow: 0 6px 18px rgba(0,0,0,0.35); display: inline-flex; align-items: center; gap: 8px; font-family: 'Prompt', var(--font-family);">
            <span style="font-size: 1.1rem;">🤝</span>
            <span>Customer ซื้อหนังสือจาก Book Owner ผ่านระบบ</span>
            <span style="color: #94a3b8; font-size: 0.82rem; font-weight: 400;">(Khách mua trực tiếp từ Chủ sách qua sàn BookNest)</span>
          </span>
        </div>

      </div>

      <!-- Bảng so sánh phân quyền -->
      <div style="margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
          <h3 style="margin: 0; font-size: 1.35rem; color: #ffffff; font-weight: 800; font-family: 'Prompt', var(--font-family); display: flex; align-items: center; gap: 8px;">
            <span>📊</span>
            <span>ตารางเปรียบเทียบสิทธิ์</span>
            <span style="font-size: 0.88rem; color: #94a3b8; font-weight: 400;">(Bảng ma trận so sánh phân quyền 3 vai trò)</span>
          </h3>
          <span style="color: #94a3b8; font-size: 0.8rem; font-style: italic;">Role-Based Access Control (RBAC Matrix)</span>
        </div>

        <div style="overflow-x: auto; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.25);">
          <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.93rem; color: #e2e8f0;">
            <thead>
              <tr style="background: #1e293b; color: #cbd5e1; font-weight: 700; border-bottom: 2px solid rgba(255,255,255,0.1);">
                <th style="padding: 14px 18px; text-align: left;">ฟังก์ชัน (Chức năng)</th>
                <th style="padding: 14px 16px; text-align: center; width: 20%;">Customer (ลูกค้า)</th>
                <th style="padding: 14px 16px; text-align: center; width: 20%;">Admin (ผู้ดูแล)</th>
                <th style="padding: 14px 16px; text-align: center; width: 25%;">Book Owner (เจ้าของหนังสือ)</th>
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(15, 23, 42, 0.4);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">ดูหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Xem thông tin sách)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">ซื้อหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Đặt hàng &amp; Thanh toán)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(15, 23, 42, 0.4);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">จัดการตะกร้า <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Thêm/xóa giỏ hàng)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">เพิ่มหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Đăng bán sách mới)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(15, 23, 42, 0.4);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">แก้ไขหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Cập nhật thông tin &amp; giá)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.45);padding:4px 12px;border-radius:999px;font-size:0.82rem;font-weight:700;box-shadow:0 0 10px rgba(245,158,11,0.2);font-family:'Prompt',var(--font-family);">เฉพาะของตน</span></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">ลบหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Gỡ sản phẩm khỏi sàn)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.45);padding:4px 12px;border-radius:999px;font-size:0.82rem;font-weight:700;box-shadow:0 0 10px rgba(245,158,11,0.2);font-family:'Prompt',var(--font-family);">เฉพาะของตน</span></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(15, 23, 42, 0.4);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">จัดการผู้ใช้ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Khóa/mở &amp; phân quyền)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">จัดการเจ้าของหนังสือ <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Kiểm soát đối tác)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
              </tr>
              <tr style="background: rgba(15, 23, 42, 0.4);">
                <td style="padding: 13px 18px; font-weight: 600; font-family: 'Prompt', var(--font-family);">ดูยอดขาย <span style="color:#94a3b8;font-weight:400;font-size:0.85rem;">(Báo cáo doanh thu &amp; đơn)</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="color:#64748b;font-weight:700;font-size:1.1rem;">—</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;background:rgba(16,185,129,0.2);color:#34d399;font-weight:800;font-size:0.95rem;box-shadow:0 0 10px rgba(16,185,129,0.25);">✓</span></td>
                <td style="padding: 13px 16px; text-align: center;"><span style="background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.45);padding:4px 12px;border-radius:999px;font-size:0.82rem;font-weight:700;box-shadow:0 0 10px rgba(245,158,11,0.2);font-family:'Prompt',var(--font-family);">เฉพาะของตน</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 3 Khối chi tiết chức năng của 3 Roles -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 32px;">
        
        <!-- Khối 1: Customer -->
        <div style="background: linear-gradient(145deg, rgba(15, 23, 42, 0.95) 0%, rgba(6, 36, 25, 0.3) 100%); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 20px 22px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
          <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: #10b981;"></div>
          <h4 style="margin: 0 0 12px 0; color: #34d399; font-size: 1.1rem; font-weight: 700; font-family: 'Prompt', var(--font-family); display: flex; align-items: center; gap: 8px;">
            <span>👤</span>
            <span>1. Customer (ลูกค้า)</span>
          </h4>
          <ul style="margin: 0; padding-left: 18px; font-size: 0.88rem; color: #cbd5e1; line-height: 1.7; font-family: 'Prompt', var(--font-family);">
            <li>สมัครสมาชิก và เข้าสู่ระบบ</li>
            <li>ค้นหา và เลือกซื้อหนังสือ</li>
            <li>เพิ่มหนังสือลงตะกร้า (Cart)</li>
            <li>สั่งซื้อ và ชำระเงิน an toàn</li>
            <li>ติดตามสถานะคำสั่งซื้อ</li>
            <li>รีวิว và ให้คะแนนหนังสือ</li>
          </ul>
        </div>

        <!-- Khối 2: Admin -->
        <div style="background: linear-gradient(145deg, rgba(15, 23, 42, 0.95) 0%, rgba(99, 102, 241, 0.15) 100%); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 12px; padding: 20px 22px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
          <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: #6366f1;"></div>
          <h4 style="margin: 0 0 12px 0; color: #a5b4fc; font-size: 1.1rem; font-weight: 700; font-family: 'Prompt', var(--font-family); display: flex; align-items: center; gap: 8px;">
            <span>👑</span>
            <span>2. Admin (ผู้ดูแลระบบ)</span>
          </h4>
          <ul style="margin: 0; padding-left: 18px; font-size: 0.88rem; color: #cbd5e1; line-height: 1.7; font-family: 'Prompt', var(--font-family);">
            <li>จัดการบัญชีผู้ใช้ (Users)</li>
            <li>จัดการข้อมูลหนังสือ và หมวดหมู่</li>
            <li>จัดการเจ้าของหนังสือ (Sellers)</li>
            <li>ตรวจสอบ và จัดการคำสั่งซื้อ</li>
            <li>ตรวจสอบรายงานยอดขาย toàn sàn</li>
            <li>จัดการสิทธิ์ของผู้ใช้งาน (RBAC)</li>
          </ul>
        </div>

        <!-- Khối 3: Book Owner -->
        <div style="background: linear-gradient(145deg, rgba(15, 23, 42, 0.95) 0%, rgba(245, 158, 11, 0.15) 100%); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 20px 22px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
          <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: #f59e0b;"></div>
          <h4 style="margin: 0 0 12px 0; color: #fcd34d; font-size: 1.1rem; font-weight: 700; font-family: 'Prompt', var(--font-family); display: flex; align-items: center; gap: 8px;">
            <span>🏪</span>
            <span>3. Book Owner (เจ้าของหนังสือ)</span>
          </h4>
          <ul style="margin: 0; padding-left: 18px; font-size: 0.88rem; color: #cbd5e1; line-height: 1.7; font-family: 'Prompt', var(--font-family);">
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
