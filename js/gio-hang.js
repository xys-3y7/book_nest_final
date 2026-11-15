/**
 * js/gio-hang.js - Add textbooks to cart & toggle wishlist via Fetch API
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Detail page form (chi-tiet.php)
  const formThemChiTiet = document.getElementById('form-them-gio');
  if (formThemChiTiet) {
    formThemChiTiet.addEventListener('submit', (e) => {
      e.preventDefault();

      if (!window.BOOKNEST_UID || window.BOOKNEST_UID <= 0) {
        window.location.href = 'dang-nhap.php?yeu_cau=gio_hang';
        return;
      }

      const id = parseInt(formThemChiTiet.querySelector('input[name="id"]').value, 10);
      const slInput = formThemChiTiet.querySelector('input[name="soLuong"]');
      const sl = slInput ? parseInt(slInput.value, 10) : 1;
      const thongBaoSpan = document.getElementById('thong-bao-gio');
      const btnThem = document.getElementById('btn-them-gio');

      btnThem.disabled = true;
      if (thongBaoSpan) thongBaoSpan.textContent = 'Adding...';

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
            alert('Error: ' + err.message);
          }
        })
        .finally(() => {
          btnThem.disabled = false;
        });
    });
  }

  // 2. Quick add buttons on catalog cards
  document.querySelectorAll('.form-them-nhanh').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      if (!window.BOOKNEST_UID || window.BOOKNEST_UID <= 0) {
        window.location.href = 'dang-nhap.php?yeu_cau=gio_hang';
        return;
      }

      const id = parseInt(form.querySelector('input[name="id"]').value, 10);
      const btn = form.querySelector('.btn-them-nhanh');
      const banDau = btn.textContent;

      btn.disabled = true;
      btn.textContent = '...';

      guiYeuCauThemGio(id, 1)
        .then((data) => {
          capNhatSoLuongHeader(data.so_mon);
          btn.textContent = '✓ Added';
          setTimeout(() => {
            btn.textContent = banDau;
            btn.disabled = false;
          }, 1500);
        })
        .catch((err) => {
          alert('Could not add to cart: ' + err.message);
          btn.textContent = banDau;
          btn.disabled = false;
        });
    });
  });

  // 2.5. Buy Now buttons (require login)
  document.querySelectorAll('#form-mua-ngay, form[action="gio-hang.php"]:has(button[name="action"][value="mua_ngay"]), form:has(.btn-mua-ngay)').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (!window.BOOKNEST_UID || window.BOOKNEST_UID <= 0) {
        e.preventDefault();
        window.location.href = 'dang-nhap.php?yeu_cau=dat_hang';
      }
    });
  });

  // 3. Wishlist form submit
  document.querySelectorAll('.form-yeu-thich').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      if (!window.BOOKNEST_UID || window.BOOKNEST_UID <= 0) {
        window.location.href = 'dang-nhap.php?yeu_cau=yeu_thich';
        return;
      }

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
      .then((res) => {
        if (res.status === 401) {
          window.location.href = 'dang-nhap.php';
          return null;
        }
        return res.json();
      })
      .then((data) => {
        if (!data) return;
        if (data.thanh_cong) {
          document.querySelectorAll('.so-luong-yeu-thich').forEach((span) => {
            span.textContent = data.so_luong;
          });
          if (btn) {
            if (data.da_thich) {
              btn.classList.add('da-thich');
              btn.textContent = '❤️ Saved';
              btn.style.background = '#fee2e2';
              btn.style.color = '#dc2626';
              btn.style.borderColor = '#fca5a5';
            } else {
              btn.classList.remove('da-thich');
              btn.textContent = '🤍 Wishlist';
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

  // 4. Click event for .nut-bam-yeu-thich
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.nut-bam-yeu-thich');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (!id) return;
    e.preventDefault();

    if (!window.BOOKNEST_UID || window.BOOKNEST_UID <= 0) {
      window.location.href = 'dang-nhap.php?yeu_cau=yeu_thich';
      return;
    }

    fetch(`yeu-thich.php?action=toggle&id=${id}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then((res) => {
      if (res.status === 401) {
        window.location.href = 'dang-nhap.php?yeu_cau=yeu_thich';
        return null;
      }
      return res.json();
    })
    .then((data) => {
      if (!data) return;
      if (data.thanh_cong) {
        document.querySelectorAll('.so-luong-yeu-thich').forEach((span) => {
          span.textContent = data.so_luong;
        });
        document.querySelectorAll(`.nut-bam-yeu-thich[data-id="${id}"]`).forEach((b) => {
          if (data.da_thich) {
            b.classList.add('da-thich');
            if (b.classList.contains('btn-fav-corner')) {
              b.textContent = '❤️';
            } else {
              b.textContent = '❤️ Saved';
            }
          } else {
            b.classList.remove('da-thich');
            if (b.classList.contains('btn-fav-corner')) {
              b.textContent = '🤍';
            } else {
              b.textContent = '🤍 Wishlist';
            }
          }
        });
      }
    })
    .catch(() => {
      window.location.href = `yeu-thich.php?action=toggle&id=${id}&redirect=${encodeURIComponent(window.location.href)}`;
    });
  });

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
    if (res.status === 401 || data.require_login) {
      window.location.href = 'dang-nhap.php?yeu_cau=gio_hang';
      throw new Error(data.loi || 'Sign in required');
    }
    if (!res.ok) {
      throw new Error(data.loi || 'Error adding book to cart');
    }
    return data;
  }

  function capNhatSoLuongHeader(soMon) {
    document.querySelectorAll('.so-luong-gio').forEach((span) => {
      span.textContent = soMon;
    });
  }
});
