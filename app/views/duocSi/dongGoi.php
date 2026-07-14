<div class="toolbar">
    <div class="toolbar-search">
        <div class="icon icon-search"></div>
        <input type="text" id="searchInput" placeholder="Tìm theo mã đơn hàng hoặc tên khách hàng...">
    </div>
    <select class="filter-select" id="filterStatus">
        <option value="all">Tất cả đơn đóng gói</option>
        <option value="CHO_DONG_GOI">Chờ đóng gói (Đã xác nhận)</option>
        <option value="DANG_GIAO">Đã đóng gói hoàn tất (Đang giao)</option>
    </select>
    <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt hàng</th>
                    <th>Giá trị đơn</th>
                    <th>Tổng số thuốc</th>
                    <th>Trạng thái kho</th>
                    <th style="text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <div style="font-weight: 700; margin-top: 8px;">Không tìm thấy đơn hàng đóng gói yêu cầu</div>
    </div>
    <div class="pagination-bar">
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<div class="modal-overlay hidden" id="modalPacking">...</div>

<script>
    // TRẠNG THÁI HỆ THỐNG LƯU TRỮ TẠM THỜI
    let state = {
        search: '',
        status: 'all',
        page: 1,
        pageSize: 5,
        activeId: null
    };

    // ===== HÀM ĐIỀU KHIỂN UI (RENDER HOÀN TOÀN ĐỘNG THEO API) =====

    // 1. Hàm render danh sách đơn đóng gói chính lên bảng dữ liệu
    function renderTable(donHangKhoList = []) {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');
        const badge = document.getElementById('sidebarPackingBadge');

        // TODO: Thiết lập bộ lọc dữ liệu thực tế dựa trên state.search và state.status
        // Gán số lượng đơn hàng "Chờ đóng gói" thật từ API lên Sidebar Badge thông báo:
        // badge.textContent = pendingCount;
        // badge.style.display = pendingCount > 0 ? 'inline-flex' : 'none';
    }

    // Phân trang
    function renderPagination(totalItems) {
        let totalPages = Math.ceil(totalItems / state.pageSize);
        let box = document.getElementById('pagination');
        if (totalPages <= 1) {
            box.innerHTML = '';
            return;
        }

        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${i === state.page ? 'active' : ''}">${i}</button>`;
        }
        box.innerHTML = html;
    }

    // 2. Mở popup hiển thị phiếu chuẩn bị thuốc & danh sách chi tiết (Nhặt kho FEFO)
    function openPackingModal(order) {
        if (!order) return;
        state.activeId = order.idDonHang;

        document.getElementById('ship_idDonHang').textContent = `#ORD-${order.idDonHang}`;
        document.getElementById('ship_hoTen').textContent = order.hoTen;
        document.getElementById('ship_diaChi').textContent = order.diaChiGiaoHang;

        let packTbody = document.getElementById('packTableBody');

        // TODO: Render mảng chi tiết thuốc của đơn hàng vào bảng packTbody bằng chuỗi Template String.
        // Mỗi dòng input checkbox kiểm đếm cần thêm class="item-check" và thuộc tính onchange="evaluateChecklistStatus()"

        document.getElementById('btnConfirmPackComplete').disabled = true; // Khóa nút lúc ban đầu
        document.getElementById('modalPacking').classList.remove('hidden');
    }

    // 3. Kiểm tra điều kiện: Tất cả các dòng thuốc trong phiếu đều được tích chọn nhặt kho đầy đủ mới mở khóa nút "Xác nhận"
    function evaluateChecklistStatus() {
        let checkboxes = document.querySelectorAll('.item-check');
        let allChecked = Array.from(checkboxes).every(cb => cb.checked);
        document.getElementById('btnConfirmPackComplete').disabled = !allChecked;
    }

    // Xử lý sự kiện bấm nút hoàn tất đóng gói bàn giao xe vận chuyển
    document.getElementById('btnConfirmPackComplete').addEventListener('click', () => {
        // TODO: Gọi API xử lý cập nhật trạng thái đơn hàng thành "DANG_GIAO" (Đang giao hàng) lên server

        document.getElementById('modalPacking').classList.add('hidden');

        // Hiện toast thông báo thành công
        let toast = document.getElementById('toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);

        // Tải lại bảng dữ liệu sau khi cập nhật thành công
        // renderTable();
    });

    // Đóng cửa sổ modal phiếu
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.close).classList.add('hidden');
        });
    });

    // ===== ĐĂNG KÝ SỰ KIỆN TÌM KIẾM & BỘ LỌC =====
    document.getElementById('searchInput').addEventListener('input', (e) => {
        state.search = e.target.value;
        // TODO: Gọi hàm filter và render lại danh sách
    });

    document.getElementById('filterStatus').addEventListener('change', (e) => {
        state.status = e.target.value;
        // TODO: Gọi hàm filter và render lại danh sách
    });

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        state.search = '';
        state.status = 'all';
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatus').value = 'all';
        // TODO: Gọi hàm thiết lập lại dữ liệu bảng nguyên bản ban đầu
    });

    // Xử lý đăng xuất hệ thống
    document.getElementById('btnLogout').addEventListener('click', () => {
        // TODO: Thực hiện xóa thông tin session/token đăng nhập
        alert('Đang đăng xuất khỏi hệ thống PharmaCare...');
    });
</script>