/**
 * js/gio-hang.js - Thêm sách vào giỏ hàng qua Fetch API không tải lại trang
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 6 (Bảng 1): Fetch API, cập nhật header tức thời, nếu tắt JS thì form vẫn hoạt động bình thường
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Xử lý form thêm vào giỏ tại trang chi tiết (chi-tiet.php)
  const formThemChiTiet = document.getElementById('form-them-gio');
  if (formThemChiTiet) {
    formThemChiTiet.addEventListener('submit', (e) => {
      e.preventDefault(); // Chặn tải lại trang khi bật JavaScript

      const id = parseInt(formThemChiTiet.querySelector('input[name="id"]').value, 10);
      const slInput = formThemChiTiet.querySelector('input[name="soLuong"]');
      const sl = slInput ? parseInt(slInput.value, 10) : 1;
      const thongBaoSpan = document.getElementById('thong-bao-gio');
      const btnThem = document.getElementById('btn-them-gio');

      btnThem.disabled = true;
      if (thongBaoSpan) thongBaoSpan.textContent = 'Đang thêm...';

      guiYeuCauThemGio(id, sl)
        .then((data) => {
          capNhatSoLuongHeader(data.so_mon);
          if (thongBaoSpan) {
            thongBaoSpan.style.color = '#16a34a';
            thongBaoSpan.textContent = '✓ ' + data.thong_bao;
            setTimeout(() => { thongBaoSpan.textContent = ''; }, 3500);
          }
        })
        .catch((err) => {
          if (thongBaoSpan) {
            thongBaoSpan.style.color = '#dc2626';
            thongBaoSpan.textContent = '⚠ ' + err.message;
          } else {
            alert('Lỗi: ' + err.message);
          }
        })
        .finally(() => {
          btnThem.disabled = false;
        });
    });
  }

  // 2. Xử lý các nút thêm nhanh ngoài trang danh mục (danh-sach.php)
  document.querySelectorAll('.form-them-nhanh').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const id = parseInt(form.querySelector('input[name="id"]').value, 10);
      const btn = form.querySelector('.btn-them-nhanh');
      const banDau = btn.textContent;

      btn.disabled = true;
      btn.textContent = '...';

      guiYeuCauThemGio(id, 1)
        .then((data) => {
          capNhatSoLuongHeader(data.so_mon);
          btn.textContent = '✓ Đã thêm';
          setTimeout(() => {
            btn.textContent = banDau;
            btn.disabled = false;
          }, 1500);
        })
        .catch((err) => {
          alert('Không thể thêm vào giỏ: ' + err.message);
          btn.textContent = banDau;
          btn.disabled = false;
        });
    });
  });

  // 3. Xử lý các nút yêu thích (Wishlist) qua Fetch API không tải lại trang
  document.querySelectorAll('.form-yeu-thich').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const btn = form.querySelector('.btn-yeu-thich');
      const formData = new FormData(form);
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      if (btn) btn.disabled = true;

      fetch('yeu-thich.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then((res) => res.json())
      .then((data) => {
        if (data.thanh_cong) {
          document.querySelectorAll('.so-luong-yeu-thich').forEach((span) => {
            span.textContent = data.so_luong;
          });
          if (btn) {
            if (data.da_thich) {
              btn.classList.add('da-thich');
              btn.textContent = '❤️ Đã thích';
              btn.style.background = '#fee2e2';
              btn.style.color = '#dc2626';
              btn.style.borderColor = '#fca5a5';
            } else {
              btn.classList.remove('da-thich');
              btn.textContent = '🤍 Yêu thích';
              btn.style.background = '';
              btn.style.color = '';
              btn.style.borderColor = '';
            }
          }
        }
      })
      .catch(() => {
        form.submit();
      })
      .finally(() => {
        if (btn) btn.disabled = false;
      });
    });
  });

  /**
   * Gửi HTTP POST request với thân JSON lên endpoint api/gio-hang.php
   */
  async function guiYeuCauThemGio(id, sl) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const res = await fetch('api/gio-hang.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json; charset=utf-8',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ id: id, sl: sl })
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.loi || 'Lỗi khi xử lý thêm sách vào giỏ');
    }
    return data;
  }

  /**
   * Cập nhật số món hiển thị trên thanh Header ngay lập tức
   */
  function capNhatSoLuongHeader(soMon) {
    document.querySelectorAll('.so-luong-gio').forEach((span) => {
      span.textContent = soMon;
    });
  }
});
