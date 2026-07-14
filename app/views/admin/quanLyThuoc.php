<div class="toolbar-card">
    <div class="toolbar">
        <div class="toolbar-search">
            <div class="icon icon-search"></div>
            <input type="text" id="searchInput" placeholder="Tìm theo tên thuốc...">
        </div>
        <select class="filter-select" id="filterDanhMuc">
            <option value="all">Tất cả danh mục</option>
        </select>
        <select class="filter-select" id="filterPhanLoai">
            <option value="all">Tất cả phân loại</option>
            <option value="Kê đơn">Kê đơn</option>
            <option value="Không kê đơn">Không kê đơn</option>
        </select>
        <select class="filter-select" id="filterTrangThai">
            <option value="all">Tất cả trạng thái</option>
            <option value="active">Còn bán</option>
            <option value="inactive">Tạm ngưng</option>
        </select>
        <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
        <button class="btn btn-primary" id="btnAddThuoc" style="margin-left:auto;">
            <div class="icon icon-plus"></div>
            Thêm thuốc mới
        </button>
    </div>
    <div class="toolbar-row2">
        <div class="result-count">Tìm thấy <b id="resultCount">0</b> thuốc phù hợp</div>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tên thuốc</th>
                    <th>Danh mục</th>
                    <th>Phân loại</th>
                    <th>Giá bán</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th style="text-align:right;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <div class="t1">Không tìm thấy thuốc</div>
    </div>
    <div class="pagination-bar">
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<div class="modal-overlay hidden" id="modalForm">
</div>

<script>
    // ===== HÀM TIỆN ÍCH HỖ TRỢ ĐỊNH DẠNG =====
    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }
    const PLACEHOLDER_IMG = 'data:image/svg+xml;utf8,' + encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="14" fill="%23e9edf2"/><g fill="none" stroke="%237c869a" stroke-width="4"><rect x="30" y="22" width="40" height="56" rx="8"/><path d="M42 40h16M42 50h16M42 60h10"/></g></svg>`);

    // ===== ĐIỀU HƯỚNG TABS SIDEBAR =====
    const tabMeta = {
        thuoc: ['Quản lý thuốc'],
        tongquan: ['Tổng quan'],
        danhmuc: ['Quản lý danh mục thuốc'],
        taikhoan: ['Quản lý tài khoản'],
    };
    document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.nav-item[data-tab]').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            const tab = item.dataset.tab;
            ['thuoc', 'tongquan', 'danhmuc', 'taikhoan'].forEach(t => {
                const el = document.getElementById('tab-' + t);
                if (el) el.style.display = (t === tab) ? 'block' : 'none';
            });
            document.querySelector('.page-title').textContent = tabMeta[tab][0];
        });
    });

    // ===== ĐÓNG/MỞ MODAL =====
    const modalForm = document.getElementById('modalForm');

    function openModal(el) {
        el.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(el) {
        el.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-close]').forEach(btn => btn.addEventListener('click', () => closeModal(document.getElementById(btn.dataset.close))));
    modalForm.addEventListener('click', (e) => {
        if (e.target === modalForm) closeModal(modalForm);
    });

    document.getElementById('btnAddThuoc').addEventListener('click', () => {
        document.getElementById('thuocForm').reset();
        document.getElementById('f_idThuoc').value = '';
        document.getElementById('f_hinhAnhPreview').src = PLACEHOLDER_IMG;
        document.getElementById('f_gioiHanMua').disabled = true;
        document.getElementById('trangThaiLabel').textContent = 'Đang bán';
        setKedon('Không kê đơn');
        openModal(modalForm);
    });

    // ===== LOGIC TƯƠNG TÁC TRÊN FORM MODAL =====
    function setKedon(value) {
        document.querySelectorAll('.kedon-option').forEach(opt => {
            const active = opt.dataset.value === value;
            opt.classList.toggle('selected', active);
            opt.querySelector('input').checked = active;
        });
    }
    document.querySelectorAll('.kedon-option').forEach(opt => {
        opt.addEventListener('click', () => setKedon(opt.dataset.value));
    });

    document.getElementById('f_khongGioiHan').addEventListener('change', (e) => {
        document.getElementById('f_gioiHanMua').disabled = e.target.checked;
        if (e.target.checked) document.getElementById('f_gioiHanMua').value = '';
    });

    document.getElementById('f_trangThai').addEventListener('change', (e) => {
        document.getElementById('trangThaiLabel').textContent = e.target.checked ? 'Đang bán' : 'Tạm ngưng';
    });

    // Preview ảnh tải lên từ máy tính
    document.getElementById('f_hinhAnh').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('f_hinhAnhPreview').src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('f_hinhAnhPreview').src = PLACEHOLDER_IMG;
        }
    });

    // ===== TOAST MESSAGE NOTIFICATION =====
    let toastTimer;

    function showToast(msg) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMsg').textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
    }
</script>