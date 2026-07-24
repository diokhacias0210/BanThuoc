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
            <div class="stat-label">Sắp hết hạn (< 90 ngày)</div>
        </div>
        <div class="stat-card" data-quickfilter="disabled">
            <div class="stat-icon red">
                <div class="icon icon-box"></div>
            </div>
            <div class="stat-value" id="statDisabled">0</div>
            <div class="stat-label">Tự động vô hiệu hóa (< 30 ngày)</div>
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
            <option value="warn">Sắp hết hạn (<90 ngày)</option>
            <option value="disabled">Đã vô hiệu hóa (<30 ngày)</option>
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

<!-- Modal Form Thêm/Sửa lô thuốc -->
<div class="modal-overlay hidden" id="modalForm">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2 id="formModalTitle">Thêm lô thuốc mới</h2>
                <div class="desc">Nhập đầy đủ thông tin lô thuốc theo dữ liệu hệ thống</div>
            </div>
            <button class="modal-close" data-close="modalForm">&times;</button>
        </div>
        <div class="modal-body">
            <form id="loThuocForm" onsubmit="return false;">
                <input type="hidden" id="f_idLo" name="idLo">
                <div class="form-grid">
                    <div class="form-field">
                        <label>Thuốc <span class="req">*</span></label>
                        <select id="f_idThuoc" name="idThuoc" required>
                            <option value="">— Chọn thuốc —</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Mã lô <span class="req">*</span></label>
                        <input type="text" id="f_maLo" name="maLo" placeholder="VD: LOT-2024-001" required>
                    </div>
                    <div class="form-field">
                        <label>Ngày sản xuất</label>
                        <input type="date" id="f_ngaySanXuat" name="ngaySanXuat">
                    </div>
                    <div class="form-field">
                        <label>Hạn sử dụng <span class="req">*</span></label>
                        <input type="date" id="f_hanSuDung" name="hanSuDung" required>
                    </div>
                    <div class="form-field">
                        <label>Số lượng tồn <span class="req">*</span></label>
                        <input type="number" id="f_soLuongTon" name="soLuongTon" min="0" required>
                    </div>
                    <div class="form-field">
                        <label>Giá nhập (đ) <span class="req">*</span></label>
                        <input type="number" id="f_giaNhap" name="giaNhap" min="0" required>
                    </div>
                    <div class="form-field">
                        <label>Thành tiền</label>
                        <input type="text" id="f_thanhTien" readonly style="background:#f1f5f9; font-weight:600; color:var(--green-700);">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalForm">Hủy bỏ</button>
            <button class="btn btn-primary" id="btnSaveLo"><i class="fa-solid fa-floppy-disk"></i> Lưu dữ liệu</button>
        </div>
    </div>
</div>

<!-- Modal Xem chi tiết lô thuốc -->
<div class="modal-overlay hidden" id="modalDetail">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2>Chi tiết lô thuốc</h2>
            </div>
            <button class="modal-close" data-close="modalDetail">&times;</button>
        </div>
        <div class="modal-body" id="detailBody">
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalDetail">Đóng</button>
            <button class="btn btn-primary" id="btnEditFromDetail"><i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa</button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast" id="toast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="toastMsg">Thao tác thành công</span>
</div>

<script>
// ===== HÀM TIỆN ÍCH =====
function fmtMoney(n) {
    return Number(n || 0).toLocaleString('vi-VN') + 'đ';
}

function fmtDateVN(str) {
    if (!str) return '—';
    const parts = str.split('-');
    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
    const d = new Date(str);
    return d.toLocaleDateString('vi-VN');
}

const TODAY = new Date();
TODAY.setHours(0, 0, 0, 0);

function tinhTrangThaiHan(soNgayConLai) {
    const daysLeft = Number(soNgayConLai);
    if (daysLeft < 0) return { code: 'expired', label: 'Đã hết hạn', class: 'badge-expired' };
    if (daysLeft < 30) return { code: 'disabled', label: 'Vô hiệu hóa (tự động)', class: 'badge-disabled' };
    if (daysLeft < 90) return { code: 'warn', label: 'Sắp hết hạn', class: 'badge-warn' };
    return { code: 'active', label: 'Còn hạn', class: 'badge-active' };
}

// ===== STATE =====
let state = {
    search: '',
    status: 'all',
    danhMuc: 'all',
    page: 1,
    pageSize: 8,
    editingId: null,
    detailId: null,
    total: 0
};

let searchTimeout;

const modalForm = document.getElementById('modalForm');
const modalDetail = document.getElementById('modalDetail');

// ===== MODAL CONTROLS =====
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

// ===== FORM VALIDATION =====
function clearFormErrors() {
    document.querySelectorAll('#loThuocForm .form-field').forEach(f => f.classList.remove('has-error'));
}

function setFieldError(id, hasError) {
    const field = document.getElementById(id);
    if (!field) return;
    const formField = field.closest('.form-field');
    if (formField) formField.classList.toggle('has-error', hasError);
}

// Tự động tính thành tiền
function updateThanhTien() {
    const sl = Number(document.getElementById('f_soLuongTon').value) || 0;
    const gia = Number(document.getElementById('f_giaNhap').value) || 0;
    document.getElementById('f_thanhTien').value = fmtMoney(sl * gia);
}
document.getElementById('f_soLuongTon').addEventListener('input', updateThanhTien);
document.getElementById('f_giaNhap').addEventListener('input', updateThanhTien);

// ===== TOAST =====
let toastTimer;

function showToast(msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
}

// ===== FETCH DATA FROM API =====
function fetchData() {
    const params = new URLSearchParams({
        search: state.search,
        status: state.status,
        idDanhMuc: state.danhMuc,
        page: state.page,
        pageSize: state.pageSize,
        _: Date.now()
    });

    fetch('<?php echo URLROOT; ?>/duocsi/quanLyLo/getList?' + params.toString())
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                renderTable(res.data, res.total);
                renderStats(res.stats);
                renderCategoryFilter(res.categories);
                renderPagination(res.total);
            }
        })
        .catch(err => console.error('Lỗi tải dữ liệu:', err));
}

// ===== RENDER CATEGORY FILTER =====
function renderCategoryFilter(categories) {
    const filterSelect = document.getElementById('filterDanhMuc');
    const currentVal = filterSelect.value;
    let opts = '<option value="all">Tất cả danh mục</option>';
    opts += categories.map(c => '<option value="' + c.idDanhMuc + '">' + c.tenDanhMuc + '</option>').join('');
    filterSelect.innerHTML = opts;
    filterSelect.value = currentVal;
}

// ===== RENDER STATS =====
function renderStats(stats) {
    if (!stats) return;
    document.getElementById('statTotal').textContent = Number(stats.tongSo || 0).toLocaleString('vi-VN');
    document.getElementById('statWarn').textContent = Number(stats.sapHetHan || 0).toLocaleString('vi-VN');
    document.getElementById('statDisabled').textContent = Number(stats.voHieuHoa || 0).toLocaleString('vi-VN');
}

// ===== RENDER TABLE =====
function renderTable(list, total) {
    const tbody = document.getElementById('tableBody');
    const emptyState = document.getElementById('emptyState');
    state.total = total || list.length;

    if (list.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        document.getElementById('pagination').innerHTML = '';
        return;
    }
    emptyState.style.display = 'none';

    tbody.innerHTML = list.map(function(item) {
        var tt = tinhTrangThaiHan(item.soNgayConLai);
        var sxDate = item.ngaySanXuat ? fmtDateVN(item.ngaySanXuat) : '—';
        return '<tr>' +
            '<td class="cell-mono cell-strong">' + item.maLo + '</td>' +
            '<td class="cell-strong">' + (item.tenThuoc || '—') + '</td>' +
            '<td>' + sxDate + '</td>' +
            '<td>' + fmtDateVN(item.hanSuDung) + '</td>' +
            '<td class="cell-strong">' + Number(item.soLuongTon).toLocaleString('vi-VN') + '</td>' +
            '<td class="cell-strong" style="color:var(--green-700);">' + fmtMoney(item.giaNhap) + '</td>' +
            '<td><span class="badge ' + tt.class + '">' + tt.label + '</span></td>' +
            '<td>' +
                '<div class="actions-cell">' +
                    '<button class="action-btn view" data-view="' + item.idLo + '" title="Chi tiết"><i class="fa-solid fa-eye"></i></button>' +
                    '<button class="action-btn edit" data-edit="' + item.idLo + '" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></button>' +
                    '<button class="action-btn delete" data-delete="' + item.idLo + '" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }).join('');
}

// ===== RENDER PAGINATION =====
function renderPagination(total) {
    var paginationEl = document.getElementById('pagination');
    var totalPages = Math.ceil(total / state.pageSize);
    if (totalPages <= 1) {
        paginationEl.innerHTML = '';
        return;
    }

    var html = '';
    html += '<button class="page-btn" onclick="goToPage(' + (state.page - 1) + ')" ' + (state.page <= 1 ? 'disabled' : '') + '><i class="fa-solid fa-chevron-left"></i></button>';

    var range = 2;
    var startPage = Math.max(1, state.page - range);
    var endPage = Math.min(totalPages, state.page + range);

    if (startPage > 1) {
        html += '<button class="page-btn" onclick="goToPage(1)">1</button>';
        if (startPage > 2) html += '<span class="page-dots">...</span>';
    }

    for (var i = startPage; i <= endPage; i++) {
        html += '<button class="page-btn ' + (i === state.page ? 'active' : '') + '" onclick="goToPage(' + i + ')">' + i + '</button>';
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span class="page-dots">...</span>';
        html += '<button class="page-btn" onclick="goToPage(' + totalPages + ')">' + totalPages + '</button>';
    }

    html += '<button class="page-btn" onclick="goToPage(' + (state.page + 1) + ')" ' + (state.page >= totalPages ? 'disabled' : '') + '><i class="fa-solid fa-chevron-right"></i></button>';

    paginationEl.innerHTML = html;
}

function goToPage(page) {
    var totalPages = Math.ceil(state.total / state.pageSize);
    if (page < 1 || page > totalPages) return;
    state.page = page;
    fetchData();
}

// ===== LOAD THUOC LIST FOR FORM SELECT =====
function loadThuocList() {
    fetch('<?php echo URLROOT; ?>/duocsi/quanLyLo/getListThuoc?_=' + Date.now())
        .then(res => res.json())
        .then(res => {
            if (res.status && res.data) {
                var select = document.getElementById('f_idThuoc');
                var opts = '<option value="">— Chọn thuốc —</option>';
                opts += res.data.map(function(t) {
                    return '<option value="' + t.idThuoc + '">' + t.tenThuoc + ' (' + (t.donViTinh || 'N/A') + ')</option>';
                }).join('');
                select.innerHTML = opts;
            }
        })
        .catch(err => console.error('Lỗi tải danh sách thuốc:', err));
}

// ===== OPEN ADD FORM =====
document.getElementById('btnAddLo').addEventListener('click', function() {
    state.editingId = null;
    clearFormErrors();
    document.getElementById('loThuocForm').reset();
    document.getElementById('f_idLo').value = '';
    document.getElementById('formModalTitle').textContent = 'Thêm lô thuốc mới';
    document.getElementById('f_thanhTien').value = '';
    loadThuocList();
    openModal(modalForm);
});

// ===== OPEN EDIT FORM =====
function openEditForm(idLo) {
    state.editingId = idLo;
    clearFormErrors();
    document.getElementById('formModalTitle').textContent = 'Sửa lô thuốc';

    fetch('<?php echo URLROOT; ?>/duocsi/quanLyLo/detail/' + idLo)
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                var d = res.data;
                loadThuocList();
                setTimeout(function() {
                    document.getElementById('f_idLo').value = d.idLo;
                    document.getElementById('f_idThuoc').value = d.idThuoc;
                    document.getElementById('f_maLo').value = d.maLo;
                    document.getElementById('f_ngaySanXuat').value = d.ngaySanXuat || '';
                    document.getElementById('f_hanSuDung').value = d.hanSuDung;
                    document.getElementById('f_soLuongTon').value = d.soLuongTon;
                    document.getElementById('f_giaNhap').value = d.giaNhap;
                    updateThanhTien();
                    openModal(modalForm);
                }, 300);
            } else {
                alert(res.message);
            }
        })
        .catch(err => console.error('Lỗi tải chi tiết:', err));
}

// ===== OPEN DETAIL MODAL =====
function openDetailModal(idLo) {
    state.detailId = idLo;
    fetch('<?php echo URLROOT; ?>/duocsi/quanLyLo/detail/' + idLo)
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                var d = res.data;
                var tt = tinhTrangThaiHan(d.soNgayConLai);
                document.getElementById('detailBody').innerHTML =
                    '<div class="detail-grid">' +
                        '<div class="detail-row"><span class="detail-label">Mã lô:</span><span class="detail-value cell-mono">' + d.maLo + '</span></div>' +
                        '<div class="detail-row"><span class="detail-label">Thuốc:</span><span class="detail-value">' + (d.tenThuoc || '—') + '</span></div>' +
                        '<div class="detail-row"><span class="detail-label">Danh mục:</span><span class="detail-value">' + (d.tenDanhMuc || '—') + '</span></div>' +
                        '<div class="detail-row"><span class="detail-label">Ngày sản xuất:</span><span class="detail-value">' + fmtDateVN(d.ngaySanXuat) + '</span></div>' +
                        '<div class="detail-row"><span class="detail-label">Hạn sử dụng:</span><span class="detail-value">' + fmtDateVN(d.hanSuDung) + '</span></div>' +
                        '<div class="detail-row"><span class="detail-label">Số lượng tồn:</span><span class="detail-value">' + Number(d.soLuongTon).toLocaleString('vi-VN') + '</span></div>' +
                        '<div class="detail-row"><span class="detail-label">Giá nhập:</span><span class="detail-value" style="color:var(--green-700);">' + fmtMoney(d.giaNhap) + '</span></div>' +
                        '<div class="detail-row"><span class="detail-label">Trạng thái:</span><span class="detail-value"><span class="badge ' + tt.class + '">' + tt.label + '</span></span></div>' +
                        '<div class="detail-row"><span class="detail-label">Số ngày còn lại:</span><span class="detail-value">' + (d.soNgayConLai >= 0 ? d.soNgayConLai + ' ngày' : 'Đã quá hạn ' + Math.abs(d.soNgayConLai) + ' ngày') + '</span></div>' +
                    '</div>';
                openModal(modalDetail);
            } else {
                alert(res.message);
            }
        })
        .catch(err => console.error('Lỗi tải chi tiết:', err));
}

// Edit from detail
document.getElementById('btnEditFromDetail').addEventListener('click', function() {
    closeModal(modalDetail);
    if (state.detailId) openEditForm(state.detailId);
});

// ===== SAVE FORM =====
document.getElementById('btnSaveLo').addEventListener('click', function() {
    var ok = true;
    var idThuoc = document.getElementById('f_idThuoc').value;
    var maLo = document.getElementById('f_maLo').value.trim();
    var hsd = document.getElementById('f_hanSuDung').value;
    var sl = document.getElementById('f_soLuongTon').value;
    var gia = document.getElementById('f_giaNhap').value;

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

    var formData = new FormData(document.getElementById('loThuocForm'));

    fetch('<?php echo URLROOT; ?>/duocsi/quanLyLo/save', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(res) {
        if (res.status) {
            showToast(res.message);
            closeModal(modalForm);
            state.page = 1;
            fetchData();
        } else {
            alert(res.message || 'Lỗi lưu dữ liệu!');
        }
    })
    .catch(function(err) {
        console.error('Lỗi:', err);
        alert('Lỗi kết nối máy chủ!');
    });
});

// ===== DELETE =====
function deleteLo(idLo) {
    if (!confirm('Bạn có chắc chắn muốn xóa lô thuốc này?')) return;

    fetch('<?php echo URLROOT; ?>/duocsi/quanLyLo/delete/' + idLo, {
        method: 'POST'
    })
    .then(function(res) { return res.json(); })
    .then(function(res) {
        if (res.status) {
            showToast(res.message);
            fetchData();
        } else {
            alert(res.message);
        }
    })
    .catch(function(err) {
        console.error('Lỗi:', err);
        alert('Lỗi kết nối máy chủ!');
    });
}

// ===== EVENT DELEGATION FOR TABLE ACTIONS =====
document.getElementById('tableBody').addEventListener('click', function(e) {
    var editBtn = e.target.closest('[data-edit]');
    var viewBtn = e.target.closest('[data-view]');
    var deleteBtn = e.target.closest('[data-delete]');
    if (editBtn) openEditForm(Number(editBtn.dataset.edit));
    if (viewBtn) openDetailModal(Number(viewBtn.dataset.view));
    if (deleteBtn) deleteLo(Number(deleteBtn.dataset.delete));
});

// ===== SEARCH & FILTER EVENTS =====
document.getElementById('searchInput').addEventListener('input', function(e) {
    state.search = e.target.value;
    state.page = 1;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchData, 300);
});

document.getElementById('filterStatus').addEventListener('change', function(e) {
    state.status = e.target.value;
    state.page = 1;
    fetchData();
});

document.getElementById('filterDanhMuc').addEventListener('change', function(e) {
    state.danhMuc = e.target.value;
    state.page = 1;
    fetchData();
});

document.getElementById('btnResetFilter').addEventListener('click', function() {
    state.search = '';
    state.status = 'all';
    state.danhMuc = 'all';
    state.page = 1;
    document.getElementById('searchInput').value = '';
    document.getElementById('filterStatus').value = 'all';
    document.getElementById('filterDanhMuc').value = 'all';
    document.querySelectorAll('.stat-card').forEach(function(c) { c.classList.remove('is-active'); });
    fetchData();
});

// Quick filter by stat cards
document.querySelectorAll('.stat-grid .stat-card[data-quickfilter]').forEach(function(card) {
    card.addEventListener('click', function() {
        document.querySelectorAll('.stat-grid .stat-card').forEach(function(c) { c.classList.remove('is-active'); });
        card.classList.add('is-active');
        state.status = card.dataset.quickfilter;
        document.getElementById('filterStatus').value = state.status;
        state.page = 1;
        fetchData();
    });
});

// ===== INIT =====
fetchData();
</script>
