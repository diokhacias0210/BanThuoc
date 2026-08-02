<div class="toolbar-card">
    <div class="toolbar">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Tìm nhanh theo tên danh mục...">
        </div>
        <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
        <button class="btn btn-primary" id="btnAddCategory" style="margin-left:auto;">
            <i class="fa-solid fa-plus"></i>
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
                    <th style="text-align: center; width: 140px;">Loại hệ thống</th>
                    <th style="width: 120px; text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fa-solid fa-folder-open" style="font-size:40px; color:var(--gray-300); margin-bottom:14px; display:block;"></i>
        <div class="t1">Không tìm thấy danh mục thuốc</div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal-overlay hidden" id="modalForm">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2 id="formModalTitle">Thêm danh mục thuốc mới</h2>
            </div>
            <button class="modal-close" data-close="modalForm">&times;</button>
        </div>
        <div class="modal-body">
            <form id="categoryForm">
                <input type="hidden" id="f_idDanhMuc" name="idDanhMuc">
                <div class="form-grid">
                    <div class="form-field">
                        <label>Tên danh mục thuốc <span class="req">*</span></label>
                        <input type="text" id="f_tenDanhMuc" name="tenDanhMuc" placeholder="VD: Thuốc nhỏ mắt, Dung dịch súc miệng" required>
                        <div class="error-msg">Vui lòng nhập tên danh mục thuốc.</div>
                    </div>
                    <div class="form-field">
                        <label>Mô tả đặc tả phân loại</label>
                        <textarea id="f_moTa" name="moTa" placeholder="Nhập mô tả tác dụng hoặc đặc trưng của nhóm thuốc này..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalForm">Hủy bỏ</button>
            <button class="btn btn-primary" id="btnSaveCategory">Lưu danh mục</button>
        </div>
    </div>
</div>

<div class="toast" id="localToast">
    <i class="fa-solid fa-circle-check" style="color:#1fae63;"></i>
    <span id="localToastMsg">Thao tác thành công</span>
</div>

<script>
    let searchTimeout; // Biến lưu timer debounce cho ô tìm kiếm, tránh gọi API liên tục khi gõ
    let toastTimer;    // Biến lưu timer tự ẩn thông báo toast sau vài giây

    const modalForm = document.getElementById('modalForm'); // Modal dùng chung cho cả Thêm mới và Sửa danh mục

    /**
     * Mở một modal (thêm class hiển thị) và khoá cuộn trang nền.
     * @param {HTMLElement} el Phần tử modal cần mở (overlay)
     */
    function moModal(el) {
        el.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Đóng một modal (ẩn đi) và trả lại trạng thái cuộn trang bình thường.
     * @param {HTMLElement} el Phần tử modal cần đóng (overlay)
     */
    function dongModal(el) {
        el.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Gắn sự kiện đóng modal cho tất cả nút có thuộc tính data-close (VD: nút X, nút Hủy bỏ)
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => dongModal(document.getElementById(btn.dataset.close)));
    });

    /**
     * Bật/tắt trạng thái báo lỗi (viền đỏ + thông báo lỗi) cho một trường trong form.
     * @param {string} id id của input/textarea cần đánh dấu lỗi
     * @param {boolean} hasError true = hiển thị lỗi, false = xoá lỗi
     */
    function datLoiTruong(id, hasError) {
        const field = document.getElementById(id).closest('.form-field'); // Tìm khối .form-field cha chứa input
        if (field) field.classList.toggle('has-error', hasError);
    }

    // Sự kiện bấm "Thêm danh mục mới": reset form, xoá lỗi cũ, đổi tiêu đề modal rồi mở modal
    document.getElementById('btnAddCategory').addEventListener('click', () => {
        datLoiTruong('f_tenDanhMuc', false);
        document.getElementById('formModalTitle').textContent = 'Thêm danh mục thuốc mới';
        document.getElementById('categoryForm').reset();
        document.getElementById('f_idDanhMuc').value = ''; // Đảm bảo id rỗng để backend hiểu đây là thêm mới (không phải sửa)
        moModal(modalForm);
    });

    /**
     * Hiển thị thông báo dạng toast ở góc màn hình trong 3 giây.
     * @param {string} msg Nội dung thông báo cần hiển thị
     */
    function hienThongBao(msg) {
        const toast = document.getElementById('localToast');
        document.getElementById('localToastMsg').textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer); // Huỷ timer cũ nếu toast đang hiển thị, tránh ẩn sớm hơn dự kiến
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    /**
     * Gọi API lấy danh sách danh mục (có thể lọc theo từ khoá tìm kiếm) rồi render ra bảng.
     * @param {string} searchKeyword Từ khoá tìm kiếm theo tên danh mục (mặc định rỗng = lấy tất cả)
     */
    function taiVaHienThiBang(searchKeyword = '') {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyDanhMuc/layDanhSach?search=${encodeURIComponent(searchKeyword)}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) hienThiBang(res.data);
            })
            .catch(err => console.error("Lỗi lấy danh sách:", err));
    }

    /**
     * Render danh sách danh mục ra bảng #tableBody. Nếu danh sách rỗng thì hiển thị
     * trạng thái "empty state" thay cho bảng.
     * @param {Array<Object>} danhMucList Mảng danh mục trả về từ API (idDanhMuc, tenDanhMuc, moTa...)
     */
    function hienThiBang(danhMucList) {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');
        document.getElementById('resultCount').textContent = danhMucList.length; // Cập nhật số lượng kết quả tìm thấy

        if (danhMucList.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = danhMucList.map(item => {
            // Danh mục "Chưa phân loại" là danh mục mặc định của hệ thống, không cho sửa tên/xoá
            const isSystem = item.tenDanhMuc === 'Chưa phân loại';
            const badgeHTML = isSystem ? `<span class="badge badge-system">Mặc định</span>` : `<span class="badge badge-product">Tùy biến</span>`;

            return `
                <tr>
                    <td class="cell-mono cell-strong">CAT-${String(item.idDanhMuc).padStart(4, '0')}</td>
                    <td class="cell-strong">${item.tenDanhMuc}</td>
                    <td class="desc-cell">${item.moTa || '—'}</td>
                    <td style="text-align: center;">${badgeHTML}</td>
                    <td class="actions-cell">
                        <button class="action-btn edit" onclick="moFormSua(${item.idDanhMuc})" title="Chỉnh sửa">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="action-btn delete" onclick="xoaDanhMuc(${item.idDanhMuc}, '${item.tenDanhMuc}')" title="Xóa danh mục">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    /**
     * Mở modal ở chế độ Sửa: lấy chi tiết 1 danh mục theo id, đổ dữ liệu vào form rồi mở modal.
     * @param {number} id id của danh mục cần sửa
     */
    function moFormSua(id) {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyDanhMuc/chiTiet/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    datLoiTruong('f_tenDanhMuc', false); // Xoá trạng thái lỗi cũ (nếu có) trước khi mở form sửa
                    document.getElementById('formModalTitle').textContent = `Sửa danh mục — CAT-${String(id).padStart(4, '0')}`;
                    document.getElementById('f_idDanhMuc').value = res.data.idDanhMuc; // id ẩn để backend biết đang cập nhật bản ghi nào
                    document.getElementById('f_tenDanhMuc').value = res.data.tenDanhMuc;
                    document.getElementById('f_moTa').value = res.data.moTa || '';
                    moModal(modalForm);
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lấy chi tiết:", err));
    }

    /**
     * Sự kiện nút "Lưu danh mục": validate tên danh mục bắt buộc, sau đó gửi form
     * (thêm mới hoặc cập nhật, tuỳ f_idDanhMuc có giá trị hay không) lên server.
     */
    document.getElementById('btnSaveCategory').addEventListener('click', () => {
        const tenInput = document.getElementById('f_tenDanhMuc');
        if (!tenInput.value.trim()) {
            datLoiTruong('f_tenDanhMuc', true); // Đánh dấu lỗi nếu chưa nhập tên danh mục
            return;
        }

        const formData = new FormData(document.getElementById('categoryForm')); // Gom toàn bộ input trong form (kể cả id ẩn)
        fetch('<?php echo URLROOT; ?>/admin/quanLyDanhMuc/luu', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    dongModal(modalForm);
                    hienThongBao(res.message);
                    taiVaHienThiBang(document.getElementById('searchInput').value); // Tải lại bảng, giữ nguyên từ khoá tìm kiếm hiện tại
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lưu danh mục:", err));
    });

    /**
     * Xoá một danh mục thuốc sau khi xác nhận. Danh mục mặc định "Chưa phân loại"
     * được bảo vệ, không cho phép xoá.
     * @param {number} id id của danh mục cần xoá
     * @param {string} name Tên danh mục (dùng để hiển thị cảnh báo và kiểm tra danh mục bảo vệ)
     */
    function xoaDanhMuc(id, name) {
        if (name === 'Chưa phân loại') {
            alert("Đây là danh mục mặc định bảo vệ của hệ thống, không được phép xóa.");
            return;
        }
        // Cảnh báo rõ hệ quả: sản phẩm thuộc danh mục bị xoá sẽ chuyển về "Chưa phân loại"
        if (confirm(`Bạn có chắc chắn muốn xóa danh mục "${name}"?\n\nToàn bộ sản phẩm thuốc thuộc danh mục này sẽ tự động chuyển sang nhóm "Chưa phân loại".`)) {
            fetch(`<?php echo URLROOT; ?>/admin/quanLyDanhMuc/xoa/${id}`, {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        hienThongBao(res.message);
                        taiVaHienThiBang(document.getElementById('searchInput').value); // Tải lại bảng, giữ nguyên từ khoá tìm kiếm hiện tại
                    } else {
                        alert(res.message);
                    }
                })
                .catch(err => console.error("Lỗi xóa danh mục:", err));
        }
    }

    // Tìm kiếm theo tên danh mục: debounce 300ms để tránh gọi API dồn dập khi người dùng đang gõ
    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            taiVaHienThiBang(e.target.value.trim());
        }, 300);
    });

    // Nút "Đặt lại": xoá từ khoá tìm kiếm và tải lại toàn bộ danh sách
    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        taiVaHienThiBang();
    });

    // Tải danh sách danh mục ngay khi trang vừa load
    taiVaHienThiBang();
</script>
