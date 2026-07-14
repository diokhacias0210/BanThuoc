<div class="toolbar">
    <div class="toolbar-search">
        <div class="icon icon-search"></div>
        <input type="text" id="searchInput" placeholder="Tìm theo mã yêu cầu hoặc tên khách hàng...">
    </div>
    <select class="filter-select" id="filterStatus">
        <option value="all">Tất cả trạng thái</option>
        <option value="CHO_DUYET">Chờ duyệt</option>
        <option value="DA_DUYET">Đã duyệt</option>
        <option value="TU_CHOI">Từ chối</option>
    </select>
    <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
    <button class="btn btn-primary" id="btnApproveAll" style="margin-left:auto;">Duyệt tất cả yêu cầu chờ</button>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Mã yêu cầu</th>
                    <th>Khách hàng</th>
                    <th>Ngày gửi yêu cầu</th>
                    <th>Ghi chú khách hàng</th>
                    <th>Trạng thái</th>
                    <th style="text-align:right;">Thao tác phê duyệt</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <div class="t1">Không tìm thấy yêu cầu nào</div>
    </div>
    <div class="pagination-bar">
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<div class="modal-overlay hidden" id="modalDetail">...</div>
<div class="modal-overlay hidden" id="modalRejectReason">...</div>
<div class="lightbox-overlay" id="lightboxOverlay">...</div>

<script>
    // ===== DỮ LIỆU ĐƠN THUỐC (Sẽ được tải từ API/Database thực tế) =====
    let donThuocList = [];

    let state = {
        search: '',
        status: 'all',
        page: 1,
        pageSize: 5,
        activeId: null
    };

    function renderTable() {
        let filtered = donThuocList.filter(item => {
            let kw = state.search.toLowerCase().trim();
            if (kw && !item.tenKhachHang.toLowerCase().includes(kw) && !String(item.idDonThuoc).includes(kw)) return false;
            if (state.status !== 'all' && item.trangThai !== state.status) return false;
            return true;
        });

        let pendingCount = donThuocList.filter(d => d.trangThai === 'CHO_DUYET').length;
        document.getElementById('sidebarBadge').textContent = pendingCount;
        document.getElementById('sidebarBadge').style.display = pendingCount > 0 ? 'inline-flex' : 'none';

        let tbody = document.getElementById('tableBody');
        let emptyState = document.getElementById('emptyState');

        if (filtered.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            document.getElementById('pagination').innerHTML = '';
            return;
        }
        emptyState.style.display = 'none';

        let start = (state.page - 1) * state.pageSize;
        let pageItems = filtered.slice(start, start + state.pageSize);

        tbody.innerHTML = pageItems.map(item => {
            let statusBadge = '';
            let actionButtons = '';

            if (item.trangThai === 'CHO_DUYET') {
                statusBadge = `<span class="badge badge-pending">Chờ duyệt</span>`;
                actionButtons = `
            <button class="action-btn approve" onclick="approveSingle(${item.idDonThuoc})">Duyệt đơn</button>
            <button class="action-btn reject" onclick="openRejectReasonModal(${item.idDonThuoc})">Từ chối</button>
          `;
            } else if (item.trangThai === 'DA_DUYET') {
                statusBadge = `<span class="badge badge-approved">Đã duyệt</span>`;
            } else {
                statusBadge = `<span class="badge badge-rejected" title="${item.lyDoTuChoi || ''}">Từ chối</span>`;
            }

            return `
          <tr>
            <td class="cell-strong cell-mono">REQ-${item.idDonThuoc}</td>
            <td><div class="cell-strong">${item.tenKhachHang}</div></td>
            <td>${item.ngayGui}</td>
            <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${item.ghiChu || '—'}</td>
            <td>${statusBadge}</td>
            <td>
              <div class="actions-cell" style="justify-content:flex-end;">
                <button class="action-btn view" onclick="openDetailModal(${item.idDonThuoc})">Xem chi tiết đơn</button>
                ${actionButtons}
              </div>
            </td>
          </tr>
        `;
        }).join('');

        renderPagination(filtered.length);
    }

    function renderPagination(totalItems) {
        let totalPages = Math.ceil(totalItems / state.pageSize);
        let box = document.getElementById('pagination');
        if (totalPages <= 1) {
            box.innerHTML = '';
            return;
        }

        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${i === state.page ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        }
        box.innerHTML = html;
    }

    function goToPage(p) {
        state.page = p;
        renderTable();
    }

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.close));
    });

    /* HIỂN THỊ POPUP CHI TIẾT */
    function openDetailModal(id) {
        let item = donThuocList.find(d => d.idDonThuoc === id);
        if (!item) return;
        state.activeId = id;

        document.getElementById('view_hinhAnhToa').src = item.hinhAnhDonThuoc;
        document.getElementById('view_maDon').textContent = `REQ-${item.idDonThuoc}`;
        document.getElementById('view_tenKhach').textContent = item.tenKhachHang;
        document.getElementById('view_ngayGui').textContent = item.ngayGui;
        document.getElementById('view_ghiChu').textContent = item.ghiChu || 'Không có';

        let medTbody = document.getElementById('medTableBody');
        medTbody.innerHTML = item.chiTiet.map(med => `
        <tr>
          <td style="text-align: center;"><img src="${med.hinhAnhThuoc}" class="med-thumb" alt="${med.tenThuoc}"></td>
          <td><strong>${med.tenThuoc}</strong></td>
          <td><span class="cell-sub">${med.lieuDung}</span></td>
          <td style="text-align:center;" class="cell-strong">${med.soLuong}</td>
        </tr>
      `).join('');

        let foot = document.getElementById('modalDetailFoot');
        if (item.trangThai === 'CHO_DUYET') {
            foot.innerHTML = `
          <button class="btn btn-ghost" onclick="closeModal('modalDetail')">Đóng</button>
          <button class="btn btn-primary" onclick="approveFromDetail()">Duyệt yêu cầu này</button>
          <button class="btn btn-primary" style="background:var(--red-600); border-color:var(--red-700);" onclick="rejectFromDetail()">Từ chối</button>
        `;
        } else {
            foot.innerHTML = `<button class="btn btn-ghost" onclick="closeModal('modalDetail')">Đóng</button>`;
        }

        openModal('modalDetail');
    }

    /* ĐIỀU KHIỂN LOGIC PHÓNG TO ẢNH TOA THUỐC (LIGHTBOX) */
    const lightboxOverlay = document.getElementById('lightboxOverlay');
    const lightboxImg = document.getElementById('lightboxImg');

    document.getElementById('view_hinhAnhToa').addEventListener('click', function() {
        lightboxImg.src = this.src;
        lightboxOverlay.classList.add('show');
    });

    function closeLightbox() {
        lightboxOverlay.classList.remove('show');
    }
    document.getElementById('btnLightboxClose').addEventListener('click', closeLightbox);
    lightboxOverlay.addEventListener('click', function(e) {
        if (e.target === lightboxOverlay) closeLightbox();
    });

    // ===== KHUNG XỬ LÝ PHÊ DUYỆT (KẾT NỐI API THỰC TẾ SAU NÀY) =====
    function approveSingle(id) {
        let item = donThuocList.find(d => d.idDonThuoc === id);
        if (item) {
            // TODO: Gửi yêu cầu phê duyệt thông qua API lên server backend
            item.trangThai = 'DA_DUYET';
            showToast('Đã phê duyệt thuốc kê đơn thành công. Hệ thống đã mở khóa giỏ hàng cho khách!');
            renderTable();
        }
    }

    function approveFromDetail() {
        approveSingle(state.activeId);
        closeModal('modalDetail');
    }

    document.getElementById('btnApproveAll').addEventListener('click', () => {
        let pendingUnits = donThuocList.filter(d => d.trangThai === 'CHO_DUYET');
        if (pendingUnits.length === 0) {
            alert('Không có yêu cầu nào đang chờ duyệt.');
            return;
        }
        if (confirm(`Xác nhận duyệt nhanh toàn bộ ${pendingUnits.length} yêu cầu đang chờ?`)) {
            // TODO: Gửi yêu cầu duyệt hàng loạt thông qua API lên server backend
            pendingUnits.forEach(d => d.trangThai = 'DA_DUYET');
            showToast('Đã phê duyệt toàn bộ danh sách yêu cầu thành công!');
            renderTable();
        }
    });

    function openRejectReasonModal(id) {
        state.activeId = id;
        document.getElementById('txtRejectReason').value = '';
        openModal('modalRejectReason');
    }

    function rejectFromDetail() {
        closeModal('modalDetail');
        openRejectReasonModal(state.activeId);
    }

    document.getElementById('btnConfirmReject').addEventListener('click', () => {
        let reason = document.getElementById('txtRejectReason').value.trim();
        if (!reason) {
            alert('Vui lòng nhập lý do từ chối cụ thể.');
            return;
        }
        let item = donThuocList.find(d => d.idDonThuoc === state.activeId);
        if (item) {
            // TODO: Gửi yêu cầu từ chối kèm lý do thông qua API lên server backend
            item.trangThai = 'TU_CHOI';
            item.lyDoTuChoi = reason;
            closeModal('modalRejectReason');
            showToast('Đã từ chối yêu cầu đơn thuốc của khách hàng thành công.');
            renderTable();
        }
    });

    function showToast(msg) {
        let toast = document.getElementById('toast');
        toast.querySelector('span').textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    document.getElementById('searchInput').addEventListener('input', (e) => {
        state.search = e.target.value;
        state.page = 1;
        renderTable();
    });
    document.getElementById('filterStatus').addEventListener('change', (e) => {
        state.status = e.target.value;
        state.page = 1;
        renderTable();
    });
    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatus').value = 'all';
        state.search = '';
        state.status = 'all';
        state.page = 1;
        renderTable();
    });

    document.getElementById('btnLogout').addEventListener('click', () => {
        // TODO: Xóa session/token đăng nhập tại đây
        alert('Đang đăng xuất khỏi hệ thống PharmaCare...');
    });

    // ===== KHỞI CHẠY (INIT) =====
    // ==========================================
    // TODO: Viết lệnh gọi API tải danh sách đơn thuốc kê đơn ban đầu từ server backend
    // và gán kết quả vào mảng `donThuocList`, sau đó gọi renderTable()
    // ==========================================
    renderTable();
</script>