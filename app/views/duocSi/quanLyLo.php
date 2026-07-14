<div id="tab-lothuoc">
    <div class="stat-grid">
        <div class="stat-card" data-quickfilter="all">
            <div class="stat-icon green">
                <div class="icon icon-box"></div>
            </div>
            <div class="stat-value" id="statTotal">0</div>
            <div class="stat-label">Tổng số lô thuốc</div>
        </div>
        <div class="stat-card" data-quickfilter="warn">
            <div class="stat-icon orange">
                <div class="icon icon-box"></div>
            </div>
            <div class="stat-value" id="statWarn">0</div>
            <div class="stat-label">Sắp hết hạn (&lt; 90 ngày)</div>
        </div>
        <div class="stat-card" data-quickfilter="disabled">
            <div class="stat-icon red">
                <div class="icon icon-box"></div>
            </div>
            <div class="stat-value" id="statDisabled">0</div>
            <div class="stat-label">Tự động vô hiệu hóa (&lt; 30 ngày)</div>
        </div>
    </div>

    <div class="toolbar">
        <div class="toolbar-search">
            <div class="icon icon-search"></div>
            <input type="text" id="searchInput" placeholder="Tìm theo mã lô hoặc tên thuốc...">
        </div>
        <select class="filter-select" id="filterStatus">
            <option value="all">Tất cả trạng thái</option>
            <option value="active">Còn hạn</option>
            <option value="warn">Sắp hết hạn (&lt;90 ngày)</option>
            <option value="disabled">Đã vô hiệu hóa (&lt;30 ngày)</option>
            <option value="expired">Đã hết hạn</option>
        </select>
        <select class="filter-select" id="filterDanhMuc">
            <option value="all">Tất cả danh mục</option>
        </select>
        <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
        <button class="btn btn-primary" id="btnAddLo" style="margin-left:auto;">Thêm lô thuốc</button>
    </div>

    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Mã lô</th>
                        <th>Thuốc</th>
                        <th>Ngày SX</th>
                        <th>Hạn sử dụng</th>
                        <th>SL tồn</th>
                        <th>Giá nhập</th>
                        <th>Trạng thái</th>
                        <th style="text-align:right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
        <div id="emptyState" class="empty-state" style="display:none;">
            <div class="t1">Không tìm thấy lô thuốc nào</div>
        </div>
        <div class="pagination-bar">
            <div class="pagination" id="pagination"></div>
        </div>
    </div>
</div>

<div class="modal-overlay hidden" id="modalForm">
</div>
<script>
    // Đoạn mã JS xử lý Lọc dữ liệu, Thêm/Sửa/Xóa lô thuốc, Validation biểu mẫu từ trang gốc
    // Chú ý: Cần viết AJAX gọi đến /duocsi/quanlylo/... xử lý dữ liệu động thay vì mảng tĩnh.
    // ===== HÀM TIỆN ÍCH ĐỊNH DẠNG & THUẬT TOÁN HẠN DÙNG CỐT LÕI =====
    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    function fmtDateVN(str) {
        if (!str) return '—';
        const d = new Date(str);
        return d.toLocaleDateString('vi-VN');
    }

    function fmtDateInput(d) {
        return d.toISOString().slice(0, 10);
    }

    function addDays(base, days) {
        const d = new Date(base);
        d.setDate(d.getDate() + days);
        return d;
    }
    const TODAY = new Date();
    TODAY.setHours(0, 0, 0, 0);

    // Thuật toán kiểm tra trạng thái thời hạn tự động dựa vào ngày hiện hành
    function tinhTrangThaiHan(hanSuDungStr) {
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
            label: 'Vô hiệu hóa (tự động)',
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

    // Mảng lưu trữ trạng thái động của hệ thống (Nhận dữ liệu từ kết nối API thật sau này)
    let danhMucList = [];
    let thuocList = [];
    let loThuocList = [];

    let state = {
        search: '',
        status: 'all',
        danhMuc: 'all',
        page: 1,
        pageSize: 8,
        editingId: null,
        detailId: null
    };

    // Khởi tạo nạp dữ liệu thật lên các thẻ select combobox form
    function initSelects() {
        // TODO: Viết hàm duyệt qua danhMucList và thuocList lấy từ API để điền option vào các thẻ #filterDanhMuc và #f_idThuoc
    }

    // ===== BỘ LỌC VÀ RENDER PHÂN TRANG (KẾT NỐI DATABASE THỰC TẾ) =====
    function renderStats() {
        // TODO: Tính toán số lượng lô thuốc từ cơ sở dữ liệu thật để hiển thị lên 3 Card thống kê
        // document.getElementById('statTotal').textContent = loThuocList.length;
    }

    function renderTable() {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');

        // TODO: Đọc danh sách lô thuốc đã được filter, cắt mảng theo phân trang (state.page) rồi map ra HTML
    }

    // ===== ĐIỀU KHIỂN ĐÓNG/MỞ MODAL POPUP VÀ FORM BIỂU MẪU =====
    const modalForm = document.getElementById('modalForm');
    const modalDetail = document.getElementById('modalDetail');

    function openModal(el) {
        el.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(el) {
        el.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(document.getElementById(btn.dataset.close)));
    });

    function clearFormErrors() {
        document.querySelectorAll('#loThuocForm .form-field').forEach(f => f.classList.remove('has-error'));
    }

    function setFieldError(id, hasError) {
        document.getElementById(id).closest('.form-field').classList.toggle('has-error', hasError);
    }

    // Tự động tính toán thành tiền nhập lô ngay trên giao diện form
    function updateThanhTien() {
        const sl = Number(document.getElementById('f_soLuongTon').value) || 0;
        const gia = Number(document.getElementById('f_giaNhap').value) || 0;
        document.getElementById('f_thanhTien').value = fmtMoney(sl * gia);
    }
    document.getElementById('f_soLuongTon').addEventListener('input', updateThanhTien);
    document.getElementById('f_giaNhap').addEventListener('input', updateThanhTien);

    // Mở form chuẩn bị thêm mới lô thuốc
    document.getElementById('btnAddLo').addEventListener('click', () => {
        state.editingId = null;
        clearFormErrors();
        document.getElementById('loThuocForm').reset();
        document.getElementById('formModalTitle').textContent = 'Thêm lô thuốc mới';
        document.getElementById('f_thanhTien').value = '';
        openModal(modalForm);
    });

    // Mở form chỉnh sửa một lô thuốc cụ thể
    function openEditForm(idLo) {
        clearFormErrors();
        document.getElementById('formModalTitle').textContent = 'Sửa lô thuốc';
        // TODO: Tìm bản ghi thực tế theo idLo, đồng bộ dữ liệu vào form, gọi updateThanhTien() và kích hoạt openModal(modalForm);
    }

    // Mở popup xem chi tiết lô thuốc kèm cảnh báo hệ thống (FEFO/Hạn dùng)
    function openDetailModal(idLo) {
        state.detailId = idLo;
        // TODO: Gọi API lấy thông tin chi tiết lô thuốc kèm theo thực thể Thuoc và DanhMuc đính kèm, render innerHTML cho '#detailBody'
        openModal(modalDetail);
    }

    document.getElementById('btnEditFromDetail').addEventListener('click', () => {
        closeModal(modalDetail);
        if (state.detailId) openEditForm(state.detailId);
    });

    // Hành động xử lý bấm nút lưu thông tin form lên cơ sở dữ liệu
    document.getElementById('btnSaveLo').addEventListener('click', () => {
        // Logic kiểm tra Validation dữ liệu nhập vào ô form trước khi gửi đi
        let ok = true;
        const idThuoc = document.getElementById('f_idThuoc').value;
        const maLo = document.getElementById('f_maLo').value.trim();
        const hsd = document.getElementById('f_hanSuDung').value;
        const sl = document.getElementById('f_soLuongTon').value;
        const gia = document.getElementById('f_giaNhap').value;

        setFieldError('f_idThuoc', !idThuoc);
        if (!idThuoc) ok = false;
        setFieldError('f_maLo', !maLo);
        if (!maLo) ok = false;
        setFieldError('f_hanSuDung', !hsd);
        if (!hsd) ok = false;
        setFieldError('f_soLuongTon', sl === '' || Number(sl) < 0);
        if (sl === '' || Number(sl) < 0) ok = false;
        setFieldError('f_giaNhap', gia === '' || Number(gia) <= 0);
        if (gia === '' || Number(gia) <= 0) ok = false;

        if (!ok) return;

        // ==========================================
        // TODO: Thực hiện gửi dữ liệu (object data) thông qua HTTP POST/PUT API lên server backend
        // ==========================================
        showToast(state.editingId ? 'Đã cập nhật lô thuốc thành công' : 'Đã thêm lô thuốc mới thành công');
        closeModal(modalForm);
        // renderTable();
    });

    // Ủy quyền sự kiện click trên bảng (Xem chi tiết / Sửa nhanh)
    document.getElementById('tableBody').addEventListener('click', (e) => {
        const editBtn = e.target.closest('[data-edit]');
        const viewBtn = e.target.closest('[data-view]');
        if (editBtn) openEditForm(Number(editBtn.dataset.edit));
        if (viewBtn) openDetailModal(Number(viewBtn.dataset.view));
    });

    // ===== ĐĂNG KÝ CÁC SỰ KIỆN TÌM KIẾM, BỘ LỌC VÀ THAO TÁC NHANH =====
    document.getElementById('searchInput').addEventListener('input', (e) => {
        state.search = e.target.value;
        state.page = 1; /* renderTable(); */
    });
    document.getElementById('filterStatus').addEventListener('change', (e) => {
        state.status = e.target.value;
        state.page = 1; /* renderTable(); */
    });
    document.getElementById('filterDanhMuc').addEventListener('change', (e) => {
        state.danhMuc = e.target.value;
        state.page = 1; /* renderTable(); */
    });

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        state.search = '';
        state.status = 'all';
        state.danhMuc = 'all';
        state.page = 1;
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatus').value = 'all';
        document.getElementById('filterDanhMuc').value = 'all';
        document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('is-active'));
        // renderTable();
    });

    // Bấm vào các Card thống kê để lọc nhanh trạng thái kho thuốc
    document.querySelectorAll('.stat-grid .stat-card[data-quickfilter]').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.stat-grid .stat-card').forEach(c => c.classList.remove('is-active'));
            card.classList.add('is-active');
            state.status = card.dataset.quickfilter;
            document.getElementById('filterStatus').value = state.status;
            state.page = 1;
            // renderTable();
        });
    });

    // ===== CHUYỂN ĐỔI TAB GIAO DIỆN HỆ THỐNG =====
    const tabTitles = {
        lothuoc: 'Quản lý lô thuốc',
        thongtin: 'Thông tin dược sĩ',
        donthuoc: 'Phê duyệt đơn thuốc',
        dongoi: 'Xử lý & đóng gói',
    };
    document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.nav-item[data-tab]').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            const tab = item.dataset.tab;
            ['lothuoc', 'thongtin', 'donthuoc', 'dongoi'].forEach(t => {
                const targetEl = document.getElementById('tab-' + t);
                if (targetEl) targetEl.style.display = (t === tab) ? 'block' : 'none';
            });
            document.getElementById('pageTitle').textContent = tabTitles[tab];
        });
    });

    // ===== TOAST NOTIFICATION ALERTS =====
    let toastTimer;

    function showToast(msg) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMsg').textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
    }

    // Sự kiện đăng xuất tài khoản
    document.getElementById('btnLogout').addEventListener('click', () => {
        // TODO: Xóa session/token lưu trữ của phiên làm việc hiện tại
        alert('Đang đăng xuất khỏi hệ thống PharmaCare...');
    });
</script>