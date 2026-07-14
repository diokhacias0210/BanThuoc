<div class="toolbar-card">
    <div class="toolbar">
        <div class="toolbar-search">
            <div class="icon icon-search"></div>
            <input type="text" id="searchInput" placeholder="Tìm nhanh theo tên danh mục...">
        </div>
        <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
        <button class="btn btn-primary" id="btnAddCategory" style="margin-left:auto;">
            <div class="icon icon-plus"></div>
            Thêm danh mục mới
        </button>
    </div>
    <div class="toolbar-row2">
        <div class="result-count">Tìm thấy <b id="resultCount">0</b> danh mục hệ thống</div>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="width: 130px;">Mã danh mục</th>
                    <th style="width: 240px;">Tên danh mục</th>
                    <th>Mô tả chi tiết phân loại</th>
                    <th style="width: 140px; text-align: center;">Loại hệ thống</th>
                    <th style="width: 120px; text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <div class="t1">Không tìm thấy danh mục thuốc</div>
    </div>
</div>

<div class="modal-overlay hidden" id="modalForm">
</div>

<script>
    // TRẠNG THÁI HỆ THỐNG CỐ DỊNH LƯU TRỮ TẠM THỜI
    let state = {
        search: '',
        editingId: null
    };

    // ===== HÀM ĐIỀU KHIỂN UI (MODAL & TOAST) =====
    const modalForm = document.getElementById('modalForm');

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

    function setFieldError(id, hasError) {
        document.getElementById(id).closest('.form-field').classList.toggle('has-error', hasError);
    }

    let toastTimer;

    function showToast(msg) {
        const toast = document.getElementById('toast');
        toast.querySelector('span').textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
    }

    // Kích hoạt form chuẩn bị thêm mới danh mục
    document.getElementById('btnAddCategory').addEventListener('click', () => {
        state.editingId = null;
        setFieldError('f_tenDanhMuc', false);
        document.getElementById('formModalTitle').textContent = 'Thêm danh mục thuốc mới';
        document.getElementById('f_idDanhMuc').value = '';
        document.getElementById('f_tenDanhMuc').value = '';
        document.getElementById('f_moTa').value = '';
        openModal(modalForm);
    });

    // ===== CÁC KHUNG LOGIC XỬ LÝ DỮ LIỆU ĐỘNG (KẾT NỐI API SAU NÀY) =====

    // 1. Render danh sách danh mục lên bảng dữ liệu
    function renderTable(danhMucList) {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');

        // TODO: Viết hàm đọc mảng data từ API và tạo chuỗi HTML template tương tự cấu trúc cũ
        // Đối với phần tử mặc định hệ thống (idDanhMuc === 0), dùng `<span class="badge badge-system">Mặc định</span>`
        // Đối với các phần tử tùy chỉnh, dùng `<span class="badge badge-product">Tùy biến</span>`
    }

    // 2. Mở popup hiển thị form chỉnh sửa danh mục thuốc
    function openEditForm(id) {
        // TODO: Lấy thông tin chi tiết danh mục theo mã ID từ API/Array hệ thống
        setFieldError('f_tenDanhMuc', false);
        document.getElementById('formModalTitle').textContent = `Sửa danh mục — CAT-${String(id).padStart(4, '0')}`;
        // Gán dữ liệu vào form ví dụ:
        // document.getElementById('f_idDanhMuc').value = d.idDanhMuc;
        openModal(modalForm);
    }

    // Sự kiện xử lý bấm nút lưu danh mục (Thêm mới hoặc Cập nhật)
    document.getElementById('btnSaveCategory').addEventListener('click', () => {
        const ten = document.getElementById('f_tenDanhMuc').value.trim();
        const moTa = document.getElementById('f_moTa').value.trim();

        if (!ten) {
            setFieldError('f_tenDanhMuc', true);
            return;
        }

        // TODO: Gọi API xử lý lưu/cập nhật dữ liệu lên server backend
        if (state.editingId !== null) {
            showToast('Đã cập nhật danh mục thuốc thành công!');
        } else {
            showToast('Đã thêm danh mục mới thành công!');
        }

        closeModal(modalForm);
        // Gọi lại render dữ liệu sau khi cập nhật thành công
    });

    // 3. Xử lý xóa một danh mục thuốc
    function deleteCategory(id) {
        // Hệ thống bảo vệ mặc định không xóa danh mục ID: 0
        if (id === 0) return;

        // TODO: Gọi API xử lý gửi yêu cầu xóa lên server
        if (confirm(`Bạn có chắc chắn muốn xóa danh mục này?\n\nLưu ý: Toàn bộ sản phẩm thuốc hiện có trong danh mục này sẽ tự động được đưa về nhóm "Chưa phân loại".`)) {
            showToast('Đã xóa danh mục và điều chuyển dữ liệu thuốc liên quan!');
        }
    }

    // ===== ĐĂNG KÝ SỰ KIỆN TÌM KIẾM & BỘ LỌC =====
    document.getElementById('searchInput').addEventListener('input', (e) => {
        state.search = e.target.value;
        // TODO: Thực hiện gọi hàm lọc và renderTable dữ liệu dựa theo từ khóa search
    });

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        state.search = '';
        document.getElementById('searchInput').value = '';
        // TODO: Thiết lập lại bảng dữ liệu ban đầu
    });

    // Xử lý sự kiện đăng xuất hệ thống quản trị
    document.querySelector('.logout-link').addEventListener('click', (e) => {
        e.preventDefault();
        // TODO: Xóa session/token đăng nhập tại đây
        alert('Đang đăng xuất khỏi hệ thống quản trị...');
    });
</script>