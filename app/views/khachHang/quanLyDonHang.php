<!-- 
  VIEW trang Quản lý đơn hàng — chỉ phần main content (không navbar/topbar/drawer/footer,
  bạn đã tự làm phần đó).
  Có nhúng CSDL cho bảng DonHang (idDonHang, ngayDat, tongTien, trangThai, lyDoHuy).
  Ô tìm kiếm mã đơn hàng đã chuyển vào trong card nội dung (không phụ thuộc navbar nữa).
-->
    <div class="wrap">
        <div class="card">
            <!-- Ô tìm kiếm riêng cho trang Quản lý đơn hàng (theo mã đơn hàng),
                 KHÔNG dùng chung ô tìm kiếm sản phẩm ở navbar để tránh phụ thuộc/lỗi -->
            <div class="content-search-bar" style="margin-bottom:16px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Tìm kiếm theo mã đơn hàng...">
            </div>

            <div class="status-tabs" id="statusTabs"></div>

            <table style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="width: 18%;">Mã đơn hàng</th>
                        <th style="width: 27%;">Ngày đặt</th>
                        <th style="width: 20%;">Tổng tiền</th>
                        <th style="width: 20%;">Trạng thái</th>
                        <th style="width: 15%; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody"></tbody>
            </table>

            <div class="pagination-container" id="paginationControls"></div>
        </div>
    </div>

    <!-- Modal huỷ đơn hàng -->
    <div class="modal-overlay" id="cancelModal">
        <div class="modal-box">
            <h3><i class="fa-solid fa-triangle-exclamation"></i> Huỷ đơn hàng</h3>
            <p class="modal-desc">Vui lòng chọn lý do huỷ đơn hàng này để hệ thống ghi nhận.</p>
            <div class="reason-options" id="reasonOptions"></div>
            <div class="modal-actions">
                <button class="btn-close" onclick="closeCancelModal()">Đóng</button>
                <button class="btn-confirm" onclick="confirmCancelOrder()">Xác nhận huỷ</button>
            </div>
        </div>
    </div>

<script>
        const statusMeta = {
            CHO_XAC_NHAN: { label: "Chờ xác nhận", cls: "st-cho_xac_nhan" },
            DA_XAC_NHAN: { label: "Đã xác nhận", cls: "st-dang_giao" },
            DANG_GIAO: { label: "Đang giao", cls: "st-dang_giao" },
            DA_GIAO: { label: "Đã giao", cls: "st-da_giao" },
            DA_HUY: { label: "Đã huỷ", cls: "st-da_huy" }
        };
        // Đã thêm trạng thái DA_XAC_NHAN cho khớp với ENUM trangThai trong bảng DonHang
        // (bản mock gốc thiếu trạng thái này). Đồng thời sửa lỗi tên class "st-da_huy"
        // (bản gốc bị lỗi gõ thành "st-da_實現_huy" không khớp với CSS).

        // Dữ liệu đơn hàng thật lấy từ CSDL (bảng DonHang) qua Controller
        let orders = <?php echo json_encode($donHangList, JSON_UNESCAPED_UNICODE); ?>;

        function formatMaDon(id) {
            return 'DH' + String(id).padStart(5, '0');
        }

        let currentFilter = "all";
        let currentPage = 1;
        const itemsPerPage = 8;
        let cancelTargetId = null;
        let selectedReason = "";

        const cancelReasons = [
            "Tôi muốn đổi sản phẩm khác / thêm thuốc",
            "Tìm thấy giá tốt hơn ở nhà thuốc khác",
            "Sai sót thông tin giao nhận hàng",
            "Không còn nhu cầu mua nữa"
        ];

        function renderStatusTabs() {
            const counts = { all: orders.length };
            Object.keys(statusMeta).forEach(k => counts[k] = orders.filter(o => o.status === k).length);
            const tabs = [
                { key: "all", label: "Tất cả" },
                { key: "CHO_XAC_NHAN", label: "Chờ xác nhận" },
                { key: "DA_XAC_NHAN", label: "Đã xác nhận" },
                { key: "DANG_GIAO", label: "Đang giao" },
                { key: "DA_GIAO", label: "Đã giao" },
                { key: "DA_HUY", label: "Đã huỷ" }
            ];
            document.getElementById('statusTabs').innerHTML = tabs.map(t => `
    <button class="status-tab ${currentFilter === t.key ? 'active' : ''}" onclick="filterStatus('${t.key}')">
      ${t.label} <span class="count">(${counts[t.key]})</span>
    </button>
  `).join('');
        }

        function filterStatus(status) {
            currentFilter = status;
            currentPage = 1;
            renderStatusTabs();
            renderTable();
        }

        function renderTable() {
            const search = document.getElementById('searchInput').value.trim().toLowerCase();
            let filtered = orders.filter(o => {
                const matchesStatus = currentFilter === "all" || o.status === currentFilter;
                const matchesSearch = !search || formatMaDon(o.id).toLowerCase().includes(search);
                return matchesStatus && matchesSearch;
            });

            const totalPages = Math.ceil(filtered.length / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const paginated = filtered.slice(startIndex, startIndex + itemsPerPage);

            const tbody = document.getElementById('orderTableBody');
            if (paginated.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--muted2); padding: 40px 0;">Không có đơn hàng nào phù hợp.</td></tr>`;
                document.getElementById('paginationControls').innerHTML = '';
                return;
            }

            tbody.innerHTML = paginated.map(o => `
    <tr onclick="navigateToDetail(${o.id})">
      <td class="order-code">#${formatMaDon(o.id)}</td>
      <td>${o.date}</td>
      <td class="amount">${Number(o.total).toLocaleString('vi-VN')}đ</td>
      <td><span class="status-badge ${statusMeta[o.status].cls}"><span class="dot"></span>${statusMeta[o.status].label}</span></td>
      <td style="text-align: center;">
        <button class="btn-cancel-action" ${o.status !== 'CHO_XAC_NHAN' ? 'disabled' : ''} onclick="openCancelModal(${o.id}, event)">
          <i class="fa-solid fa-rectangle-xmark"></i> Hủy đơn
        </button>
      </td>
    </tr>
  `).join('');

            renderPagination(totalPages, filtered.length);
        }

        function renderPagination(totalPages, totalItems) {
            const controls = document.getElementById('paginationControls');
            if (totalItems <= 8) {
                controls.innerHTML = '';
                return;
            }
            let html = `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})"><i class="fa-solid fa-angle-left"></i></button>`;
            for (let i = 1; i <= totalPages; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
            }
            html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})"><i class="fa-solid fa-angle-right"></i></button>`;
            controls.innerHTML = html;
        }

        function changePage(page) { currentPage = page; renderTable(); }

        // Điều hướng sang trang chi tiết đơn hàng - dùng idDonHang thật (số) thay vì mã "DH00128"
        function navigateToDetail(id) { window.location.href = `<?php echo URLROOT; ?>/khachHang/chiTietDonHang/${id}`; }

        function openCancelModal(id, event) {
            event.stopPropagation();
            cancelTargetId = id;
            selectedReason = cancelReasons[0];
            renderReasonOptions();
            document.getElementById('cancelModal').classList.add('show');
        }
        function closeCancelModal() { document.getElementById('cancelModal').classList.remove('show'); }

        function renderReasonOptions() {
            document.getElementById('reasonOptions').innerHTML = cancelReasons.map(r => `
    <div class="reason-option ${selectedReason === r ? 'selected' : ''}" onclick="selectReason('${r}')">
      <div class="reason-radio"></div>
      <span>${r}</span>
    </div>
  `).join('');
        }
        function selectReason(r) { selectedReason = r; renderReasonOptions(); }

        // Gọi API huỷ đơn thật xuống CSDL (bảng DonHang: trangThai + lyDoHuy)
        function confirmCancelOrder() {
            fetch(`<?php echo URLROOT; ?>/khachHang/quanLyDonHang/huyDonHang/${cancelTargetId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `lyDoHuy=${encodeURIComponent(selectedReason)}`
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        const o = orders.find(x => x.id === cancelTargetId);
                        if (o) o.status = "DA_HUY";
                        alert(`Đã hủy thành công đơn hàng #${formatMaDon(cancelTargetId)}`);
                        closeCancelModal();
                        renderStatusTabs();
                        renderTable();
                    } else {
                        alert(res.message || 'Huỷ đơn hàng thất bại, vui lòng thử lại.');
                    }
                })
                .catch(() => alert('Lỗi kết nối máy chủ.'));
        }

        document.getElementById('searchInput').addEventListener('input', () => { currentPage = 1; renderTable(); });

        renderStatusTabs();
        renderTable();
</script>
