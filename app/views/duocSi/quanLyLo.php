<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
      <div class="info-banner">
        <div class="icon icon-alert-circle"></div>
        Lô thuốc sẽ tự động được đánh dấu “Sắp hết hạn” khi còn dưới 90 ngày và tự động vô hiệu hóa khi còn dưới 30 ngày kể từ hạn sử dụng.
      </div>
      <form id="loThuocForm" onsubmit="return false;">
        <input type="hidden" id="f_idLo" name="idLo">
        <div class="form-grid">
          <div class="form-field span-2">
            <label>Thuốc <span class="req">*</span></label>
            <select id="f_idThuoc" name="idThuoc" required>
              <option value="">— Chọn thuốc —</option>
            </select>
            <div class="error-msg">Vui lòng chọn thuốc.</div>
          </div>

          <div class="form-field">
            <label>Mã lô <span class="req">*</span></label>
            <input type="text" id="f_maLo" name="maLo" placeholder="VD: LO2026-001" required>
            <div class="error-msg">Vui lòng nhập mã lô.</div>
          </div>
          <div class="form-field">
            <label>Số lượng tồn <span class="req">*</span></label>
            <input type="number" id="f_soLuongTon" name="soLuongTon" min="0" step="1" placeholder="0" required>
            <div class="error-msg">Số lượng tồn phải ≥ 0.</div>
          </div>

          <div class="form-field">
            <label>Ngày sản xuất</label>
            <input type="date" id="f_ngaySanXuat" name="ngaySanXuat">
            <div class="error-msg">Ngày sản xuất không hợp lệ.</div>
          </div>
          <div class="form-field">
            <label>Hạn sử dụng <span class="req">*</span></label>
            <input type="date" id="f_hanSuDung" name="hanSuDung" required>
            <div class="error-msg">Hạn sử dụng phải hợp lệ.</div>
          </div>

          <div class="form-field">
            <label>Giá nhập (đ/đơn vị) <span class="req">*</span></label>
            <input type="number" id="f_giaNhap" name="giaNhap" min="0" step="1000" placeholder="0" required>
            <div class="error-msg">Vui lòng nhập giá nhập hợp lệ.</div>
          </div>
          <div class="form-field">
            <label>Thành tiền nhập lô</label>
            <input type="text" id="f_thanhTien" class="field-readonly" readonly placeholder="Tự động tính">
          </div>
        </div>
      </form>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" data-close="modalForm">Hủy</button>
      <button class="btn btn-primary" id="btnSaveLo">Lưu lô thuốc</button>
    </div>
  </div>
</div>

<div class="modal-overlay hidden" id="modalDetail">
  <div class="modal-box wide">
    <div class="modal-head">
      <div>
        <h2>Chi tiết lô thuốc</h2>
        <div class="desc">Thông tin đầy đủ của lô và thuốc liên quan</div>
      </div>
      <button class="modal-close" data-close="modalDetail">&times;</button>
    </div>
    <div class="modal-body" id="detailBody"></div>
    <div class="modal-foot">
      <button class="btn btn-ghost" data-close="modalDetail">Đóng</button>
      <button class="btn btn-primary" id="btnEditFromDetail">Chỉnh sửa lô này</button>
    </div>
  </div>
</div>

<div class="toast" id="toast">
  <i class="fa-solid fa-circle-check"></i>
  <span id="toastMsg">Đã lưu thành công</span>
</div>

<script>
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

function tinhTrangThaiHan(soNgayConLai) {
    const daysLeft = Number(soNgayConLai || 0);
    if (daysLeft < 0) return { code: 'expired', label: 'Đã hết hạn', class: 'badge-expired' };
    if (daysLeft < 30) return { code: 'disabled', label: 'Vô hiệu hóa (tự động)', class: 'badge-disabled' };
    if (daysLeft < 90) return { code: 'warn', label: 'Sắp hết hạn', class: 'badge-warn' };
    return { code: 'active', label: 'Còn hạn', class: 'badge-active' };
}

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

function openModal(el) {
    el.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(el) {
    el.classList.add('hidden');
    document.body.style.overflow = '';
}

document.querySelectorAll('[data-close]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        closeModal(document.getElementById(btn.dataset.close));
    });
});
[modalForm, modalDetail].forEach(function(m) {
    m.addEventListener('click', function(e) {
        if (e.target === m) closeModal(m);
    });
});

function clearFormErrors() {
    document.querySelectorAll('#loThuocForm .form-field').forEach(function(f) {
        f.classList.remove('has-error');
    });
}

function setFieldError(id, hasError) {
    const field = document.getElementById(id);
    if (!field) return;
    const formField = field.closest('.form-field');
    if (formField) formField.classList.toggle('has-error', hasError);
}

function updateThanhTien() {
    const sl = Number(document.getElementById('f_soLuongTon').value) || 0;
    const gia = Number(document.getElementById('f_giaNhap').value) || 0;
    document.getElementById('f_thanhTien').value = fmtMoney(sl * gia);
}
document.getElementById('f_soLuongTon').addEventListener('input', updateThanhTien);
document.getElementById('f_giaNhap').addEventListener('input', updateThanhTien);

let toastTimer;
function showToast(msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function() {
        toast.classList.remove('show');
    }, 2600);
}

function fetchData() {
    const params = new URLSearchParams({
        search: state.search,
        status: state.status,
        idDanhMuc: state.danhMuc,
        page: state.page,
        pageSize: state.pageSize,
        _: Date.now()
    });

    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/getList?' + params.toString())
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status) {
                renderTable(res.data, res.total);
                renderStats(res.stats);
                renderCategoryFilter(res.categories);
                renderPagination(res.total);
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải dữ liệu:', err);
        });
}

function renderCategoryFilter(categories) {
    const filterSelect = document.getElementById('filterDanhMuc');
    const currentVal = filterSelect.value;
    let opts = '<option value="all">Tất cả danh mục</option>';
    opts += (categories || []).map(function(c) {
        return '<option value="' + c.idDanhMuc + '">' + c.tenDanhMuc + '</option>';
    }).join('');
    filterSelect.innerHTML = opts;
    if (currentVal) filterSelect.value = currentVal;
}

function renderStats(stats) {
    if (!stats) return;
    document.getElementById('statTotal').textContent = Number(stats.tongSo || 0).toLocaleString('vi-VN');
    document.getElementById('statWarn').textContent = Number(stats.sapHetHan || 0).toLocaleString('vi-VN');
    document.getElementById('statDisabled').textContent = Number(stats.voHieuHoa || 0).toLocaleString('vi-VN');
}

function renderTable(list, total) {
    const tbody = document.getElementById('tableBody');
    const emptyState = document.getElementById('emptyState');
    state.total = total || list.length;

    if (!list || list.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    emptyState.style.display = 'none';
    tbody.innerHTML = list.map(function(item) {
        var tt = tinhTrangThaiHan(item.soNgayConLai);
        return '<tr>' +
            '<td><div class="cell-strong cell-mono">' + (item.maLo || '—') + '</div><div class="cell-sub">ID lô: ' + item.idLo + '</div></td>' +
            '<td><div class="cell-strong">' + (item.tenThuoc || '—') + '</div></td>' +
            '<td>' + fmtDateVN(item.ngaySanXuat) + '</td>' +
            '<td><div class="hsd-cell"><span class="hsd-pill">' + fmtDateVN(item.hanSuDung) + '</span><div class="cell-sub">' + (Number(item.soNgayConLai || 0) >= 0 ? 'còn ' + (item.soNgayConLai || 0) + ' ngày' : 'quá hạn ' + Math.abs(item.soNgayConLai || 0) + ' ngày') + '</div></div></td>' +
            '<td class="cell-strong">' + Number(item.soLuongTon || 0).toLocaleString('vi-VN') + '</td>' +
            '<td class="cell-strong" style="color:var(--green-700);">' + fmtMoney(item.giaNhap) + '</td>' +
            '<td><span class="badge ' + tt.class + '">' + tt.label + '</span></td>' +
            '<td><div class="actions-cell" style="justify-content:flex-end;">' +
                '<button class="action-btn view" data-view="' + item.idLo + '" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></button>' +
                '<button class="action-btn edit" data-edit="' + item.idLo + '" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></button>' +
            '</div></td>' +
        '</tr>';
    }).join('');
}

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

function loadThuocList(callback) {
    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/getListThuoc?_=' + Date.now())
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status && res.data) {
                var select = document.getElementById('f_idThuoc');
                var opts = '<option value="">— Chọn thuốc —</option>';
                opts += res.data.map(function(t) {
                    return '<option value="' + t.idThuoc + '">' + t.tenThuoc + ' (' + (t.donViTinh || 'N/A') + ')</option>';
                }).join('');
                select.innerHTML = opts;
                if (typeof callback === 'function') callback();
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải danh sách thuốc:', err);
        });
}

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

function openEditForm(idLo) {
    state.editingId = idLo;
    clearFormErrors();
    document.getElementById('formModalTitle').textContent = 'Sửa lô thuốc';

    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/detail/' + idLo)
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status) {
                var d = res.data;
                loadThuocList(function() {
                    document.getElementById('f_idLo').value = d.idLo;
                    document.getElementById('f_idThuoc').value = d.idThuoc;
                    document.getElementById('f_maLo').value = d.maLo;
                    document.getElementById('f_ngaySanXuat').value = d.ngaySanXuat || '';
                    document.getElementById('f_hanSuDung').value = d.hanSuDung;
                    document.getElementById('f_soLuongTon').value = d.soLuongTon;
                    document.getElementById('f_giaNhap').value = d.giaNhap;
                    updateThanhTien();
                    openModal(modalForm);
                });
            } else {
                alert(res.message);
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải chi tiết:', err);
        });
}

function openDetailModal(idLo) {
    state.detailId = idLo;
    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/detail/' + idLo)
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status) {
                var d = res.data;
                var tt = tinhTrangThaiHan(d.soNgayConLai);
                var detailHtml = '<div class="detail-grid">' +
                    '<div class="detail-row"><span class="detail-label">Mã lô</span><span class="detail-value cell-mono">' + (d.maLo || '—') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Thuốc</span><span class="detail-value">' + (d.tenThuoc || '—') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Danh mục</span><span class="detail-value">' + (d.tenDanhMuc || '—') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Ngày sản xuất</span><span class="detail-value">' + fmtDateVN(d.ngaySanXuat) + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Hạn sử dụng</span><span class="detail-value">' + fmtDateVN(d.hanSuDung) + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Số lượng tồn</span><span class="detail-value">' + Number(d.soLuongTon || 0).toLocaleString('vi-VN') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Giá nhập</span><span class="detail-value" style="color:var(--green-700);">' + fmtMoney(d.giaNhap) + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Trạng thái</span><span class="detail-value"><span class="badge ' + tt.class + '">' + tt.label + '</span></span></div>' +
                    '<div class="detail-row"><span class="detail-label">Số ngày còn lại</span><span class="detail-value">' + (Number(d.soNgayConLai || 0) >= 0 ? (d.soNgayConLai || 0) + ' ngày' : 'Đã quá hạn ' + Math.abs(d.soNgayConLai || 0) + ' ngày') + '</span></div>' +
                '</div>';
                document.getElementById('detailBody').innerHTML = detailHtml;
                openModal(modalDetail);
            } else {
                alert(res.message);
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải chi tiết:', err);
        });
}

document.getElementById('btnEditFromDetail').addEventListener('click', function() {
    closeModal(modalDetail);
    if (state.detailId) openEditForm(state.detailId);
});

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

    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/save', {
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



document.getElementById('tableBody').addEventListener('click', function(e) {
    var editBtn = e.target.closest('[data-edit]');
    var viewBtn = e.target.closest('[data-view]');
    if (editBtn) openEditForm(Number(editBtn.dataset.edit));
    if (viewBtn) openDetailModal(Number(viewBtn.dataset.view));
});

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

fetchData();
</script>
