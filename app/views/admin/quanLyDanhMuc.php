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
        <div class="icon icon-search" style="transform: scale(2.5); margin-bottom: 12px;"></div>
        <div class="t1">Không tìm thấy danh mục thuốc</div>
    </div>
</div>

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

<script>
    let searchTimeout;

    // Điều khiển Modal
    const modalForm = document.getElementById('modalForm');

    function openModal(el) {
        el.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(el) {
        el.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Gán sự kiện click để đóng modal khi click nút Close / Hủy
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(document.getElementById(btn.dataset.close)));
    });

    function setFieldError(id, hasError) {
        const field = document.getElementById(id).closest('.form-field');
        if (field) field.classList.toggle('has-error', hasError);
    }

    // Reset Form chuẩn bị thêm mới
    document.getElementById('btnAddCategory').addEventListener('click', () => {
        setFieldError('f_tenDanhMuc', false);
        document.getElementById('formModalTitle').textContent = 'Thêm danh mục thuốc mới';
        document.getElementById('categoryForm').reset();
        document.getElementById('f_idDanhMuc').value = '';
        openModal(modalForm);
    });

    // ===== KẾT NỐI API DỮ LIỆU ĐỘNG =====

    // 1. Tải và hiển thị danh sách (Sửa ASSETROOT thành URLROOT và quanlydanhmuc thành quanLyDanhMuc)
    function fetchAndRenderTable(searchKeyword = '') {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyDanhMuc/getList?search=${encodeURIComponent(searchKeyword)}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    renderTable(res.data);
                }
            })
            .catch(err => console.error("Lỗi lấy danh sách:", err));
    }

    function renderTable(danhMucList) {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');
        document.getElementById('resultCount').textContent = danhMucList.length;

        if (danhMucList.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = danhMucList.map(item => {
            const isSystem = item.idDanhMuc <= 3;
            const badgeHTML = isSystem ?
                `<span class="badge badge-system">Mặc định</span>` :
                `<span class="badge badge-product">Tùy biến</span>`;

            return `
                <tr>
                    <td class="cell-mono cell-strong">CAT-${String(item.idDanhMuc).padStart(4, '0')}</td>
                    <td class="cell-strong">${item.tenDanhMuc}</td>
                    <td class="desc-cell">${item.moTa || '—'}</td>
                    <td style="text-align: center;">${badgeHTML}</td>
                    <td class="actions-cell">
                        <button class="action-btn edit" onclick="openEditForm(${item.idDanhMuc})" title="Chỉnh sửa">
                            <div class="icon icon-pencil"></div>
                        </button>
                        <button class="action-btn delete" onclick="deleteCategory(${item.idDanhMuc})" title="Xóa danh mục">
                            <div class="icon icon-trash"><div class="icon-trash-body"></div></div>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // 2. Điền thông tin vào biểu mẫu sửa danh mục (Sửa ASSETROOT thành URLROOT)
    function openEditForm(id) {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyDanhMuc/detail/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    setFieldError('f_tenDanhMuc', false);
                    document.getElementById('formModalTitle').textContent = `Sửa danh mục — CAT-${String(id).padStart(4, '0')}`;
                    document.getElementById('f_idDanhMuc').value = res.data.idDanhMuc;
                    document.getElementById('f_tenDanhMuc').value = res.data.tenDanhMuc;
                    document.getElementById('f_moTa').value = res.data.moTa || '';
                    openModal(modalForm);
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lấy chi tiết:", err));
    }

    // 3. Xử lý sự kiện nút Lưu (Sửa ASSETROOT thành URLROOT)
    document.getElementById('btnSaveCategory').addEventListener('click', () => {
        const tenInput = document.getElementById('f_tenDanhMuc');
        if (!tenInput.value.trim()) {
            setFieldError('f_tenDanhMuc', true);
            return;
        }

        const formData = new FormData(document.getElementById('categoryForm'));

        fetch('<?php echo URLROOT; ?>/admin/quanLyDanhMuc/save', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    closeModal(modalForm);
                    showToast(res.message);
                    fetchAndRenderTable(document.getElementById('searchInput').value);
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lưu danh mục:", err));
    });

    // 4. Xử lý hành động xóa danh mục thuốc (Sửa ASSETROOT thành URLROOT)
    function deleteCategory(id) {
        if (id <= 3) {
            alert("Đây là danh mục cốt lõi mặc định của hệ thống, không được phép xóa.");
            return;
        }

        if (confirm(`Bạn có chắc chắn muốn xóa danh mục này?\n\nLưu ý: Toàn bộ sản phẩm thuốc hiện có trong danh mục này sẽ tự động được đưa về nhóm "Chưa phân loại".`)) {
            fetch(`<?php echo URLROOT; ?>/admin/quanLyDanhMuc/delete/${id}`, {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        showToast(res.message);
                        fetchAndRenderTable(document.getElementById('searchInput').value);
                    } else {
                        alert(res.message);
                    }
                })
                .catch(err => console.error("Lỗi xóa danh mục:", err));
        }
    }

    // ===== KHỞI CHẠY VÀ LẮNG NGHE SỰ KIỆN TÌM KIẾM =====
    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchAndRenderTable(e.target.value.trim());
        }, 300);
    });

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        fetchAndRenderTable();
    });

    // Khởi chạy nạp danh sách ngay khi tải trang xong
    fetchAndRenderTable();
</script>