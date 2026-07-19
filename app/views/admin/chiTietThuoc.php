<div id="detailContainer">
  <!-- Toàn bộ khung thông tin chi tiết được kết xuất động qua Fetch JSON API tránh gãy bóp méo hình ảnh -->
</div>

<script>
  const TARGET_THUOC_ID = <?php echo $idThuoc; ?>;

  function fmtMoney(n) {
    return Number(n || 0).toLocaleString('vi-VN') + 'đ';
  }

  function fmtDateVN(str) {
    if (!str) return '—';
    return new Date(str).toLocaleDateString('vi-VN');
  }

  function tinhTrangThaiHan(hanSuDungStr) {
    const TODAY = new Date();
    TODAY.setHours(0, 0, 0, 0);
    const hsd = new Date(hanSuDungStr);
    hsd.setHours(0, 0, 0, 0);
    const daysLeft = Math.round((hsd - TODAY) / 86400000);
    if (daysLeft < 0) return {
      code: 'expired',
      label: 'Đã hết hạn',
      cls: 'hsd-expired',
      bd: ['badge-inactive', 'Hết hạn']
    };
    if (daysLeft < 30) return {
      code: 'disabled',
      label: 'Vô hiệu hóa',
      cls: 'hsd-danger',
      bd: ['badge-danger', 'Vô hiệu hóa']
    };
    if (daysLeft < 90) return {
      code: 'warn',
      label: 'Sắp hết hạn',
      cls: 'hsd-warn',
      bd: ['badge-warn', 'Sắp hết hạn']
    };
    return {
      code: 'active',
      label: 'Còn hạn sử dụng',
      cls: 'hsd-ok',
      bd: ['badge-active', 'Còn hạn']
    };
  }

  function getInitials(name) {
    return name.split(' ').map(w => w[0]).slice(-2).join('').toUpperCase();
  }

  function fetchDetailData() {
    fetch(`<?php echo URLROOT; ?>/admin/quanLyThuoc/getDetailData/${TARGET_THUOC_ID}`)
      .then(res => res.json())
      .then(res => {
        if (res.status) {
          renderDetailHtml(res.thuoc, res.lots);
        } else {
          document.getElementById('detailContainer').innerHTML = `<div class="not-found"><h3>Không tìm thấy sản phẩm thuốc yêu cầu.</h3></div>`;
        }
      });
  }

  function renderDetailHtml(thuoc, lots) {
    const totalStock = lots.reduce((sum, item) => sum + (new Date(item.hanSuDung) >= new Date() ? item.soLuongTon : 0), 0);
    const limitText = thuoc.gioiHanMua == -1 ? 'Không giới hạn mua' : `Tối đa ${thuoc.gioiHanMua} ${thuoc.donViTinh} / đơn hàng`;

    let tableRowsHTML = `<tr><td colspan="7" style="text-align:center;color:var(--gray-500);padding:30px;"><i class="fa-solid fa-box-open"></i> Chưa nhập bất kỳ lô kho vận nào cho sản phẩm thuốc này.</td></tr>`;

    if (lots.length > 0) {
      tableRowsHTML = lots.map(lo => {
        const st = tinhTrangThaiHan(lo.hanSuDung);
        return `
                    <tr>
                        <td class="cell-mono cell-strong">${lo.maLo}</td>
                        <td>${fmtDateVN(lo.ngaySanXuat)}</td>
                        <td><span class="hsd-pill ${st.cls}"><i class="fa-regular fa-clock"></i> ${fmtDateVN(lo.hanSuDung)}</span></td>
                        <td class="cell-strong">${Number(lo.soLuongTon).toLocaleString('vi-VN')}</td>
                        <td class="cell-strong" style="color:var(--green-700);">${fmtMoney(lo.giaNhap)}</td>
                        <td><span class="badge ${st.bd[0]}">${st.bd[1]}</span></td>
                        <td>
                            <div class="ds-chip">
                                <div class="ds-avatar">${lo.hoTen ? getInitials(lo.hoTen) : '?'}</div>
                                <div>
                                    <div class="cell-strong" style="font-size:12.8px;">${lo.hoTen || 'Hệ thống'}</div>
                                    <div class="cell-sub" style="font-size:11px;">${lo.email || 'admin@pharmacare.vn'}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
      }).join('');
    }

    document.getElementById('detailContainer').innerHTML = `
            <div class="detail-layout">
                <div class="gallery-card">
                    <div class="gallery-main"><img src="${thuoc.hinhAnh || PLACEHOLDER_IMG}" alt=""></div>
                </div>

                <div class="info-card">
                    <span class="idthuoc-tag">Mã số thuốc liên kết: TH-${String(thuoc.idThuoc).padStart(4, '0')}</span>
                    <h1>${thuoc.tenThuoc}</h1>
                    <div class="price-row">${fmtMoney(thuoc.giaBan)} <span class="unit">/ ${thuoc.donViTinh}</span></div>

                    <div class="spec-grid">
                        <div class="spec-item"><div class="k">Danh mục đặc trị</div><div class="v">${thuoc.tenDanhMuc || 'Chưa phân loại'}</div></div>
                        <div class="spec-item"><div class="k">Yêu cầu kê đơn</div><div class="v"><span class="badge ${thuoc.yeuCauKeDon === 'Kê đơn' ? 'badge-rx' : 'badge-otc'}">${thuoc.yeuCauKeDon}</span></div></div>
                        <div class="spec-item"><div class="k">Thành phần dược chất</div><div class="v">${thuoc.thanhPhan}</div></div>
                        <div class="spec-item"><div class="k">Hàm lượng biệt dược</div><div class="v">${thuoc.hamLuong || '—'}</div></div>
                        <div class="spec-item"><div class="k">Tổng tồn kho khả dụng</div><div class="v">${totalStock.toLocaleString('vi-VN')} ${thuoc.donViTinh}</div></div>
                        <div class="spec-item"><div class="k">Giới hạn giỏ hàng</div><div class="v">${limitText}</div></div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h2><i class="fa-solid fa-file-medical"></i> Chỉ định lâm sàng & Công dụng đặc trị</h2>
                <div class="desc-text">${thuoc.congDung}</div>
            </div>

            <div class="section-card">
                <h2><i class="fa-solid fa-boxes-stacked"></i> Nhật ký danh sách các lô hàng nhập kho vận (${lots.length})</h2>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Mã số lô</th>
                                <th>Ngày sản xuất</th>
                                <th>Hạn sử dụng (HSD)</th>
                                <th>Số lượng tồn</th>
                                <th>Giá vốn nhập</th>
                                <th>Trạng thái hạn</th>
                                <th>Dược sĩ nhập kho</th>
                            </tr>
                        </thead>
                        <tbody>${tableRowsHTML}</tbody>
                    </table>
                </div>
            </div>
        `;
  }

  fetchDetailData();
</script>