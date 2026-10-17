/**
 * js/tim-kiem.js - Tìm kiếm tức thời bằng Fetch API kèm kỹ thuật Debounce
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 * Chức năng 6 (Bảng 1): fetch + debounce, textContent chống XSS, tắt JS thì form vẫn hoạt động bình thường
 */

document.addEventListener('DOMContentLoaded', () => {
  const oTimKiem = document.getElementById('o-tim-kiem');
  const ketQuaBox = document.getElementById('ket-qua-tuc-thoi');

  if (!oTimKiem || !ketQuaBox) return;

  let timeoutId = null;

  // Lắng nghe sự kiện gõ phím với kỹ thuật Debounce (trì hoãn 300ms chống nghẽn máy chủ)
  oTimKiem.addEventListener('input', (e) => {
    const tuKhoa = e.target.value.trim();

    clearTimeout(timeoutId);

    if (tuKhoa.length < 2) {
      ketQuaBox.style.display = 'none';
      ketQuaBox.textContent = '';
      return;
    }

    timeoutId = setTimeout(() => {
      fetch(`api/san-pham.php?q=${encodeURIComponent(tuKhoa)}`)
        .then((res) => {
          if (!res.ok) throw new Error('Lỗi máy chủ khi tìm kiếm');
          return res.json();
        })
        .then((data) => {
          // Xóa kết quả cũ
          ketQuaBox.textContent = '';

          const ds = data.du_lieu || [];
          if (ds.length === 0) {
            const p = document.createElement('div');
            p.style.padding = '12px 16px';
            p.style.color = '#64748b';
            p.style.fontSize = '0.9rem';
            p.textContent = 'Không tìm thấy cuốn sách nào khớp với từ khóa.';
            ketQuaBox.appendChild(p);
          } else {
            ds.forEach((sp) => {
              const item = document.createElement('a');
              item.href = `chi-tiet.php?id=${sp.id}`;
              item.style.display = 'flex';
              item.style.alignItems = 'center';
              item.style.justifyContent = 'space-between';
              item.style.padding = '10px 14px';
              item.style.borderBottom = '1px solid #f1f5f9';
              item.style.textDecoration = 'none';
              item.style.color = '#0f172a';
              item.style.fontSize = '0.9rem';

              // Tên sách - Gán hoàn toàn qua textContent chống XSS
              const tenDiv = document.createElement('div');
              tenDiv.style.fontWeight = '500';
              tenDiv.textContent = sp.ten;

              // Giá sách
              const giaSpan = document.createElement('span');
              giaSpan.style.color = '#dc2626';
              giaSpan.style.fontWeight = 'bold';
              giaSpan.style.fontSize = '0.85rem';
              giaSpan.style.marginLeft = '12px';
              giaSpan.style.whiteSpace = 'nowrap';
              giaSpan.textContent = Number(sp.gia).toLocaleString('vi-VN') + ' đ';

              item.appendChild(tenDiv);
              item.appendChild(giaSpan);

              item.addEventListener('mouseenter', () => {
                item.style.background = '#f8fafc';
              });
              item.addEventListener('mouseleave', () => {
                item.style.background = '#ffffff';
              });

              ketQuaBox.appendChild(item);
            });
          }
          ketQuaBox.style.display = 'block';
        })
        .catch((err) => {
          console.error('Lỗi tìm kiếm nhanh:', err);
        });
    }, 300);
  });

  // Tự động đóng hộp gợi ý khi click ra ngoài
  document.addEventListener('click', (e) => {
    if (!oTimKiem.contains(e.target) && !ketQuaBox.contains(e.target)) {
      ketQuaBox.style.display = 'none';
    }
  });
});
