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

<div class="modal-overlay hidden" id="modalDetail">
    <div class="modal-box wide">
        <div class="modal-head">
            <h2>Chi tiết đơn thuốc kê đơn</h2>
            <button class="modal-close" data-close="modalDetail" type="button">×</button>
        </div>
        <div class="modal-body">
            <div class="prescription-flow">
                <div class="prescription-meta">
                    <h4>Thông tin yêu cầu</h4>
                    <div class="meta-list">
                        <div><strong>Mã đơn:</strong> <span id="view_maDon"></span></div>
                        <div><strong>Khách hàng:</strong> <span id="view_tenKhach"></span></div>
                        <div><strong>Ngày gửi:</strong> <span id="view_ngayGui"></span></div>
                        <div class="span-2"><strong>Ghi chú:</strong> <span id="view_ghiChu"></span></div>
                    </div>
                </div>

                <div class="prescription-meta">
                    <h4>Danh sách thuốc</h4>
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th style="width:70px;">Ảnh</th>
                                <th>Tên thuốc</th>
                                <th>Liều dùng</th>
                                <th style="width:80px; text-align:center;">SL</th>
                            </tr>
                        </thead>
                        <tbody id="medTableBody"></tbody>
                    </table>
                </div>

                <div class="prescription-meta">
                    <h4>Ảnh toa thuốc</h4>
                    <div class="panel-image">
                        <div class="title-hint">Nhấn vào ảnh để phóng to</div>
                        <img id="view_hinhAnhToa" class="prescription-img" src="" alt="Ảnh toa thuốc">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot" id="modalDetailFoot"></div>
    </div>
</div>

<div class="modal-overlay hidden" id="modalRejectReason">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Từ chối đơn thuốc</h2>
            <button class="modal-close" data-close="modalRejectReason" type="button">×</button>
        </div>
        <div class="modal-body">
            <label for="txtRejectReason" style="display:block; margin-bottom:8px; font-weight:600;">Lý do từ chối</label>
            <textarea id="txtRejectReason" class="reason-textarea" placeholder="Nhập lý do từ chối..."></textarea>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalRejectReason" type="button">Hủy</button>
            <button class="btn btn-primary" id="btnConfirmReject" type="button">Xác nhận từ chối</button>
        </div>
    </div>
</div>

<div class="lightbox-overlay" id="lightboxOverlay">
    <div class="lightbox-box">
        <button class="lightbox-close" id="btnLightboxClose" type="button">×</button>
        <img id="lightboxImg" class="lightbox-img" src="" alt="Ảnh phóng to">
    </div>
</div>

<div class="toast" id="toast"><span></span></div>

<script>
    let donThuocList = [];
    let currentDetail = null;
    let state = {
        search: '',
        status: 'all',
        page: 1,
        pageSize: 8,
        totalItems: 0,
        activeId: null
    };

    const baseUrl = '<?php echo URLROOT; ?>/duocSi/duyetDon';

    function formatDate(value) {
        if (!value) return '—';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function renderTable() {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');

        if (!donThuocList.length) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = donThuocList.map(item => {
            let statusBadge = '';
            let actionButtons = '';

            if (item.trangThai === 'CHO_DUYET') {
                statusBadge = '<span class="badge badge-pending">Chờ duyệt</span>';
                actionButtons = `
                    <button class="action-btn approve" onclick="approveSingle(${item.idDonThuoc})">Duyệt đơn</button>
                    <button class="action-btn reject" onclick="openRejectReasonModal(${item.idDonThuoc})">Từ chối</button>
                `;
            } else if (item.trangThai === 'DA_DUYET') {
                statusBadge = '<span class="badge badge-approved">Đã duyệt</span>';
            } else {
                statusBadge = '<span class="badge badge-rejected">Từ chối</span>';
            }

            return `
                <tr>
                    <td class="cell-strong cell-mono">REQ-${item.idDonThuoc}</td>
                    <td><div class="cell-strong">${item.tenKhachHang || '—'}</div></td>
                    <td>${formatDate(item.ngayGui)}</td>
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

        renderPagination(state.totalItems);
    }

    function renderPagination(totalItems) {
        const totalPages = Math.ceil(totalItems / state.pageSize);
        const box = document.getElementById('pagination');
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

    function goToPage(page) {
        state.page = page;
        loadData();
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

    function updatePendingBadge(count) {
        const badge = document.getElementById('sidebarBadge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-flex' : 'none';
        }
    }

    function loadData() {
        const params = new URLSearchParams({
            search: state.search,
            status: state.status,
            page: state.page
        });

        fetch(`${baseUrl}/getList?${params.toString()}`)
            .then(response => response.json())
            .then(result => {
                if (!result.status) {
                    showToast(result.message || 'Không thể tải danh sách đơn thuốc.');
                    return;
                }

                donThuocList = result.data || [];
                state.totalItems = result.total || 0;
                updatePendingBadge(result.pendingCount || 0);
                renderTable();
            })
            .catch(() => {
                showToast('Không thể kết nối máy chủ.');
            });
    }

    function populateDetailModal(item) {
        currentDetail = item;
        state.activeId = item.idDonThuoc;

        document.getElementById('view_maDon').textContent = `REQ-${item.idDonThuoc}`;
        document.getElementById('view_tenKhach').textContent = item.tenKhachHang || '—';
        document.getElementById('view_ngayGui').textContent = formatDate(item.ngayGui);
        document.getElementById('view_ghiChu').textContent = item.ghiChu || 'Không có';

        const imageUrl = item.hinhAnhDonThuoc || 'https://placehold.co/600x480/e8f5ee/2d7a4f?text=No+Image';
        const img = document.getElementById('view_hinhAnhToa');
        img.src = imageUrl;
        document.getElementById('lightboxImg').src = imageUrl;

        const medTbody = document.getElementById('medTableBody');
        if (item.chiTiet && item.chiTiet.length) {
            medTbody.innerHTML = item.chiTiet.map(med => `
                <tr>
                    <td style="text-align:center;"><img src="https://placehold.co/64x64/e8f5ee/2d7a4f?text=💊" class="med-thumb" alt="${med.tenThuoc}"></td>
                    <td><strong>${med.tenThuoc}</strong></td>
                    <td><span class="cell-sub">${med.lieuDung || '—'}</span></td>
                    <td style="text-align:center;" class="cell-strong">${med.soLuong}</td>
                </tr>
            `).join('');
        } else {
            medTbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#7c869a;">Không có thuốc trong đơn.</td></tr>';
        }

        const foot = document.getElementById('modalDetailFoot');
        if (item.trangThai === 'CHO_DUYET') {
            foot.innerHTML = `
                <button class="btn btn-ghost" onclick="closeModal('modalDetail')">Đóng</button>
                <button class="btn btn-primary" onclick="approveFromDetail()">Duyệt yêu cầu này</button>
                <button class="btn btn-primary" style="background:var(--red-600); border-color:var(--red-700);" onclick="rejectFromDetail()">Từ chối</button>
            `;
        } else {
            foot.innerHTML = '<button class="btn btn-ghost" onclick="closeModal(\'modalDetail\')">Đóng</button>';
        }
    }

    function openDetailModal(id) {
        fetch(`${baseUrl}/detail/${id}`)
            .then(response => response.json())
            .then(result => {
                if (!result.status) {
                    showToast(result.message || 'Không thể mở chi tiết đơn.');
                    return;
                }
                populateDetailModal(result.data);
                openModal('modalDetail');
            })
            .catch(() => showToast('Không thể tải chi tiết đơn.'));
    }

    function approveSingle(id) {
        if (!confirm('Xác nhận duyệt đơn thuốc này?')) return;
        fetch(`${baseUrl}/approve/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    showToast(result.message || 'Đã duyệt đơn thuốc.');
                    loadData();
                } else {
                    showToast(result.message || 'Không thể duyệt đơn thuốc.');
                }
            })
            .catch(() => showToast('Không thể duyệt đơn thuốc.'));
    }

    function approveFromDetail() {
        approveSingle(state.activeId);
        closeModal('modalDetail');
    }

    document.getElementById('btnApproveAll').addEventListener('click', () => {
        const pendingUnits = donThuocList.filter(d => d.trangThai === 'CHO_DUYET');
        if (!pendingUnits.length) {
            alert('Không có yêu cầu nào đang chờ duyệt.');
            return;
        }
        if (confirm(`Xác nhận duyệt nhanh toàn bộ ${pendingUnits.length} yêu cầu đang chờ?`)) {
            fetch(`${baseUrl}/approveAll`, { method: 'POST' })
                .then(response => response.json())
                .then(result => {
                    if (result.status) {
                        showToast(result.message || 'Đã duyệt toàn bộ đơn thuốc.');
                        loadData();
                    } else {
                        showToast(result.message || 'Không thể duyệt tất cả.');
                    }
                })
                .catch(() => showToast('Không thể duyệt tất cả.'));
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
        const reason = document.getElementById('txtRejectReason').value.trim();
        if (!reason) {
            alert('Vui lòng nhập lý do từ chối cụ thể.');
            return;
        }

        fetch(`${baseUrl}/reject/${state.activeId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body: `reason=${encodeURIComponent(reason)}`
        })
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    closeModal('modalRejectReason');
                    showToast(result.message || 'Đã từ chối đơn thuốc.');
                    loadData();
                } else {
                    showToast(result.message || 'Không thể từ chối đơn thuốc.');
                }
            })
            .catch(() => showToast('Không thể từ chối đơn thuốc.'));
    });

    function showToast(msg) {
        const toast = document.getElementById('toast');
        toast.querySelector('span').textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    document.getElementById('searchInput').addEventListener('input', (e) => {
        state.search = e.target.value;
        state.page = 1;
        loadData();
    });

    document.getElementById('filterStatus').addEventListener('change', (e) => {
        state.status = e.target.value;
        state.page = 1;
        loadData();
    });

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatus').value = 'all';
        state.search = '';
        state.status = 'all';
        state.page = 1;
        loadData();
    });

    const logoutButton = document.getElementById('btnLogout');
    if (logoutButton) {
        logoutButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Xác nhận đăng xuất khỏi hệ thống PharmaCare?')) {
                window.location.href = '<?php echo URLROOT; ?>/khachHang/xacThuc/dangXuat';
            }
        });
    }

    const lightboxOverlay = document.getElementById('lightboxOverlay');
    const lightboxImg = document.getElementById('lightboxImg');

    document.getElementById('view_hinhAnhToa').addEventListener('click', function() {
        lightboxImg.src = this.src;
        lightboxOverlay.classList.add('show');
    });

    function closeLightbox() {
        lightboxOverlay.classList.remove('show');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('modalDetail');
            closeModal('modalRejectReason');
            closeLightbox();
        }
    });

    document.getElementById('btnLightboxClose').addEventListener('click', closeLightbox);
    lightboxOverlay.addEventListener('click', function(e) {
        if (e.target === lightboxOverlay) {
            closeLightbox();
        }
    });

    loadData();
</script>