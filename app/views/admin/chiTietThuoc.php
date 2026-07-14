<div id="contentRoot">
</div>

<script>
    const PLACEHOLDER_IMG = 'data:image/svg+xml;utf8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="14" fill="%23e9edf2"/><g fill="none" stroke="%237c869a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><rect x="30" y="22" width="40" height="56" rx="8"/><path d="M42 40h16M42 50h16M42 60h10"/></g></svg>`);

    // ===== HÀM TIỆN ÍCH HỖ TRỢ ĐỊNH DẠNG =====
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
            daysLeft
        };
        if (daysLeft < 30) return {
            code: 'disabled',
            label: 'Vô hiệu hóa',
            daysLeft
        };
        if (daysLeft < 90) return {
            code: 'warn',
            label: 'Sắp hết hạn',
            daysLeft
        };
        return {
            code: 'active',
            label: 'Còn hạn',
            daysLeft
        };
    }

    function initials(name) {
        return name.replace(/^DS\.\s*/, '').split(' ').map(w => w[0]).slice(-2).join('').toUpperCase();
    }

    // ===== KHUNG LOGIC XỬ LÝ DỮ LIỆU ĐỘNG (KẾT NỐI API SAU NÀY) =====
    const params = new URLSearchParams(window.location.search);
    const idThuoc = Number(params.get('id'));
    const root = document.getElementById('contentRoot');

    // 1. Hàm render chi tiết sản phẩm và danh sách các lô thuốc liên quan
    function renderDetail(thuoc, danhMuc, loCuaThuoc = []) {
        if (!thuoc) {
            root.innerHTML = `
      <div class="not-found">
        <div class="icon icon-search-notfound"></div>
        <div style="font-weight:700;color:var(--gray-700);margin-bottom:6px;">Không tìm thấy thuốc</div>
        <div>Thuốc bạn đang tìm có thể đã bị xóa hoặc mã ID không hợp lệ.</div>
        <div style="margin-top:18px;"><a class="btn btn-primary" href="quanLyThuoc.html">Quay lại danh sách thuốc</a></div>
      </div>`;
            return;
        }

        const tongTon = loCuaThuoc.reduce((s, l) => s + l.soLuongTon, 0);
        const gioiHanText = thuoc.gioiHanMua === -1 ? 'Không giới hạn' : `Tối đa ${thuoc.gioiHanMua} ${thuoc.donViTinh} / đơn hàng`;

        root.innerHTML = `
    <div class="detail-layout">
      <div class="gallery-card">
        <div class="gallery-main"><img id="mainImg" src="${thuoc.hinhAnh || PLACEHOLDER_IMG}" alt="${thuoc.tenThuoc}"></div>
        <div class="gallery-thumbs">
          <img src="${thuoc.hinhAnh || PLACEHOLDER_IMG}" class="active" onclick="document.getElementById('mainImg').src=this.src;document.querySelectorAll('.gallery-thumbs img').forEach(i=>i.classList.remove('active'));this.classList.add('active');">
          <img src="${PLACEHOLDER_IMG}" onclick="document.getElementById('mainImg').src=this.src;document.querySelectorAll('.gallery-thumbs img').forEach(i=>i.classList.remove('active'));this.classList.add('active');">
        </div>
      </div>

      <div class="info-card">
        <span class="idthuoc-tag">Mã thuốc: TH${String(thuoc.idThuoc).padStart(4, '0')}</span>
        <h1>${thuoc.tenThuoc}</h1>
        <div class="price-row">${fmtMoney(thuoc.giaBan)} <span class="unit">/ ${thuoc.donViTinh}</span></div>

        <div class="spec-grid">
          <div class="spec-item"><div class="k">Danh mục</div><div class="v">${danhMuc ? danhMuc.tenDanhMuc : '—'}</div></div>
          <div class="spec-item"><div class="k">Phân loại</div><div class="v">${thuoc.yeuCauKeDon}</div></div>
          <div class="spec-item"><div class="k">Trạng thái</div><div class="v">${thuoc.trangThai ? 'Đang bán' : 'Tạm ngưng'}</div></div>
          <div class="spec-item"><div class="k">Thành phần</div><div class="v">${thuoc.thanhPhan}</div></div>
          <div class="spec-item"><div class="k">Hàm lượng</div><div class="v">${thuoc.hamLuong || '—'}</div></div>
          <div class="spec-item"><div class="k">Đơn vị tính</div><div class="v">${thuoc.donViTinh}</div></div>
          <div class="spec-item"><div class="k">Tồn kho hiện tại</div><div class="v">${tongTon.toLocaleString('vi-VN')} ${thuoc.donViTinh}</div></div>
          <div class="spec-item"><div class="k">Giới hạn mua</div><div class="v">${gioiHanText}</div></div>
          <div class="spec-item" style="grid-column: span 2;"><div class="k">Số lô đang theo dõi</div><div class="v">${loCuaThuoc.length} lô</div></div>
        </div>

        <div class="actions-row">
          <button class="btn btn-primary" onclick="alert('Mở form chỉnh sửa thuốc TH${String(thuoc.idThuoc).padStart(4, '0')}')">
            <div class="icon icon-pencil"></div>
            Chỉnh sửa thuốc
          </button>
          <button class="btn btn-ghost" onclick="alert('${thuoc.trangThai ? 'Đã tạm ngưng bán' : 'Đã mở bán lại'} (demo)')">
            ${thuoc.trangThai ? '<div class="icon icon-eye-off"></div>' : '<div class="icon icon-eye"></div>'}
            ${thuoc.trangThai ? 'Tạm ngưng bán' : 'Mở bán lại'}
          </button>
          <button class="btn btn-danger" onclick="if(confirm('Bạn có chắc muốn xóa thuốc này?')) alert('Đã xóa (demo)')">
            <div class="icon icon-trash"><div class="icon-trash-body"></div></div>
            Xóa thuốc
          </button>
        </div>
      </div>
    </div>

    <div class="section-card">
      <h2>
        <div class="icon icon-book"></div>
        Mô tả &amp; công dụng
      </h2>
      <div class="desc-text">${thuoc.congDung}</div>
    </div>

    <div class="section-card">
      <h2>
        <div class="icon icon-box"></div>
        Danh sách lô thuốc (${loCuaThuoc.length})
      </h2>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Mã lô</th>
              <th>Ngày SX</th>
              <th>Hạn sử dụng</th>
              <th>SL tồn</th>
              <th>Giá nhập</th>
              <th>Trạng thái</th>
              <th>Dược sĩ thêm lô</th>
            </tr>
          </thead>
          <tbody>
            ${loCuaThuoc.length === 0 ? `<tr><td colspan="7" style="text-align:center;color:var(--gray-500);padding:30px;">Chưa có lô thuốc nào được nhập cho sản phẩm này.</td></tr>` :
          loCuaThuoc.map(lo => {
            const st = tinhTrangThaiHan(lo.hanSuDung);
            const ds = lo.duocSi; // Thông tin thực thể dược sĩ đính kèm từ API
            let pillCls = 'hsd-ok';
            if (st.code === 'warn') pillCls = 'hsd-warn'; else if (st.code === 'disabled') pillCls = 'hsd-danger'; else if (st.code === 'expired') pillCls = 'hsd-expired';
            const badgeMap = { active: ['badge-active', 'Còn hạn'], warn: ['badge-warn', 'Sắp hết hạn'], disabled: ['badge-danger', 'Vô hiệu hóa'], expired: ['badge-expired', 'Đã hết hạn'] };
            const bd = badgeMap[st.code];
            return ` <
            tr >
            <
            td class = "cell-mono cell-strong" > $ {
                lo.maLo
            } < /td> <
        td > $ {
            fmtDateVN(lo.ngaySanXuat)
        } < /td> <
        td > < span class = "hsd-pill ${pillCls}" > $ {
                fmtDateVN(lo.hanSuDung)
            } < /span></td >
            <
            td class = "cell-strong" > $ {
                lo.soLuongTon.toLocaleString('vi-VN')
            } < /td> <
        td > $ {
            fmtMoney(lo.giaNhap)
        } < /td> <
        td > < span class = "badge ${bd[0]}" > $ {
                bd[1]
            } < /span></td >
            <
            td >
            <
            div class = "ds-chip" >
            <
            div class = "ds-avatar" > $ {
                ds ? initials(ds.hoTen) : '?'
            } < /div> <
        div >
            <
            div class = "cell-strong"
        style = "font-size:12.8px;" > $ {
            ds ? ds.hoTen : 'Không rõ'
        } < /div> <
        div class = "cell-sub" > $ {
            ds ? ds.chungChiHanhNghe : ''
        } < /div> < /
        div > <
            /div> < /
            td > <
            /tr>`;
    }).join('')
    } <
    /tbody> < /
    table > <
        /div> < /
        div >
        `;
    }
</script>