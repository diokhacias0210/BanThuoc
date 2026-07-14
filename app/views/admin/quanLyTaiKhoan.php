<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="width: 120px;">Mã ND</th>
                    <th>Người dùng</th>
                    <th>Thông tin liên hệ</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th style="text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</div>

<div class="modal-overlay hidden" id="modalDetail">...</div>
<div class="modal-overlay hidden" id="modalRole">...</div>

<script>
    // ===== HÀM ĐIỀU KHIỂN UI (MODAL & TOAST) =====
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.close));
    });

    let toastTimer;

    function showToast(msg) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMsg').textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
    }

    // ===== CÁC KHUNG LOGIC XỬ LÝ DỮ LIỆU ĐỘNG (KẾT NỐI API SAU NÀY) =====

    // 1. Render danh sách tài khoản lên bảng
    function renderTable(nguoiDungList) {
        const tbody = document.getElementById('tableBody');
        // TODO: Viết hàm đọc mảng data từ API và tạo chuỗi HTML template tương tự cấu trúc cũ
    }

    // 2. Mở popup hiển thị thông tin chi tiết người dùng
    function openDetail(userId) {
        // TODO: Call API lấy thông tin chi tiết theo mã ID hoặc tìm trong mảng cục bộ
        // Sau đó build HTML gán vào innerHTML của element '#detailBody'
        openModal('modalDetail');
    }

    // 3. Khóa / Mở khóa tài khoản
    function toggleStatus(userId) {
        // TODO: Gọi API xử lý cập nhật trạng thái hoạt động tài khoản (trangThai = true/false)
    }

    // 4. Mở popup chuẩn bị form phân quyền vai trò mới
    function openRoleForm(userId) {
        // TODO: Đọc dữ liệu tài khoản được chọn, gán thông tin vào form
        openModal('modalRole');
    }

    // Sự kiện click lưu phân quyền vai trò mới
    document.getElementById('btnSaveRole').addEventListener('click', () => {
        // TODO: Lấy dữ liệu từ input combo select gửi lên server/API xử lý lưu vai trò mới
        showToast('Đã cập nhật quyền hạn tài khoản!');
        closeModal('modalRole');
        // Gọi lại renderTable() hoặc fetch dữ liệu mới sau khi lưu thành công
    });

    // Xử lý sự kiện đăng xuất hệ thống
    document.querySelector('.logout-link').addEventListener('click', (e) => {
        e.preventDefault();
        // TODO: Xóa session/token đăng nhập tại đây
        alert('Đang đăng xuất khỏi hệ thống quản trị...');
    });
</script>