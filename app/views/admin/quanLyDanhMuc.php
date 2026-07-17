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
    let searchTimeout;
    let toastTimer;

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
        const field = document.getElementById(id).closest('.form-field');
        if (field) field.classList.toggle('has-error', hasError);
    }

    document.getElementById('btnAddCategory').addEventListener('click', () => {
        setFieldError('f_tenDanhMuc', false);
        document.getElementById('formModalTitle').textContent = 'Thêm danh mục thuốc mới';
        document.getElementById('categoryForm').reset();
        document.getElementById('f_idDanhMuc').value = '';
        openModal(modalForm);
    });

    function showLocalToast(msg) {
        const toast = document.getElementById('localToast');
        document.getElementById('localToastMsg').textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function fetchAndRenderTable(searchKeyword = '') {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyDanhMuc/getList?search=${encodeURIComponent(searchKeyword)}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) renderTable(res.data);
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
            const isSystem = item.tenDanhMuc === 'Chưa phân loại';
            const badgeHTML = isSystem ? `<span class="badge badge-system">Mặc định</span>` : `<span class="badge badge-product">Tùy biến</span>`;

            return `
                <tr>
                    <td class="cell-mono cell-strong">CAT-${String(item.idDanhMuc).padStart(4, '0')}</td>
                    <td class="cell-strong">${item.tenDanhMuc}</td>
                    <td class="desc-cell">${item.moTa || '—'}</td>
                    <td style="text-align: center;">${badgeHTML}</td>
                    <td class="actions-cell">
                        <button class="action-btn edit" onclick="openEditForm(${item.idDanhMuc})" title="Chỉnh sửa">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="action-btn delete" onclick="deleteCategory(${item.idDanhMuc}, '${item.tenDanhMuc}')" title="Xóa danh mục">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

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
                    showLocalToast(res.message);
                    fetchAndRenderTable(document.getElementById('searchInput').value);
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lưu danh mục:", err));
    });

    function deleteCategory(id, name) {
        if (name === 'Chưa phân loại') {
            alert("Đây là danh mục mặc định bảo vệ của hệ thống, không được phép xóa.");
            return;
        }
        if (confirm(`Bạn có chắc chắn muốn xóa danh mục "${name}"?\n\nToàn bộ sản phẩm thuốc thuộc danh mục này sẽ tự động chuyển sang nhóm "Chưa phân loại".`)) {
            fetch(`<?php echo URLROOT; ?>/admin/quanLyDanhMuc/delete/${id}`, {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        showLocalToast(res.message);
                        fetchAndRenderTable(document.getElementById('searchInput').value);
                    } else {
                        alert(res.message);
                    }
                })
                .catch(err => console.error("Lỗi xóa danh mục:", err));
        }
    }

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

    fetchAndRenderTable();
</script>