<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div id="tab-lothuoc">
  <!-- Khối 3 thẻ thống kê nhanh: tổng số lô, sắp hết hạn, đã vô hiệu hóa -->
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

  <!-- Thanh công cụ: ô tìm kiếm, bộ lọc trạng thái/danh mục, nút đặt lại và nút thêm mới -->
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

  <!-- Bảng dữ liệu chính hiển thị danh sách lô thuốc (được đổ động bằng JS qua hàm hienThiBang) -->
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
            <th>Giá bán</th>
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

<!-- Modal thêm mới / chỉnh sửa lô thuốc -->
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
            <label>Giá bán (đ/đơn vị) <span class="req">*</span></label>
            <input type="number" id="f_giaBan" name="giaBan" min="0" step="1000" placeholder="0" required>
            <div class="error-msg">Vui lòng nhập giá bán hợp lệ.</div>
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

<!-- Modal xem chi tiết lô thuốc (nội dung #detailBody được đổ động bằng JS) -->
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

<!-- Toast thông báo nổi (được điều khiển bằng hàm hienThongBao trong JS) -->
<div class="toast" id="toast">
  <i class="fa-solid fa-circle-check"></i>
  <span id="toastMsg">Đã lưu thành công</span>
</div>

<script>
/**
 * Định dạng một số thành chuỗi tiền tệ Việt Nam (VD: 1000 -> "1.000đ").
 * @param {number} n - Giá trị số cần định dạng (có thể null/undefined).
 * @returns {string} Chuỗi tiền tệ đã định dạng, kèm hậu tố "đ".
 */
function dinhDangTien(n) {
    // Ép về số, nếu n rỗng/null thì mặc định 0, rồi định dạng theo chuẩn vi-VN
    return Number(n || 0).toLocaleString('vi-VN') + 'đ';
}

/**
 * Chuyển chuỗi ngày định dạng "YYYY-MM-DD" (từ server) sang định dạng
 * hiển thị "DD/MM/YYYY" theo chuẩn Việt Nam.
 * @param {string} str - Chuỗi ngày đầu vào, có thể rỗng/null.
 * @returns {string} Chuỗi ngày đã định dạng, hoặc "—" nếu không có dữ liệu.
 */
function dinhDangNgayVN(str) {
    // Không có dữ liệu ngày thì hiển thị dấu gạch ngang
    if (!str) return '—';

    // Tách chuỗi theo dấu "-" để lấy năm/tháng/ngày
    const parts = str.split('-');
    // Nếu đúng 3 phần (năm-tháng-ngày) thì đảo lại thành ngày/tháng/năm
    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];

    // Trường hợp chuỗi không đúng định dạng trên, thử parse bằng Date rồi định dạng lại
    const d = new Date(str);
    return d.toLocaleDateString('vi-VN');
}

/**
 * Xác định trạng thái hạn sử dụng của lô thuốc dựa trên số ngày còn lại.
 * Quy tắc: < 0 ngày = hết hạn; < 30 ngày = tự động vô hiệu hóa;
 * < 90 ngày = sắp hết hạn; còn lại = còn hạn.
 * @param {number} soNgayConLai - Số ngày còn lại đến hạn sử dụng (có thể âm nếu đã quá hạn).
 * @returns {{code: string, label: string, class: string}} Object mô tả trạng thái (mã, nhãn hiển thị, class CSS).
 */
function tinhTrangThaiHan(soNgayConLai) {
    // Ép về số, mặc định 0 nếu không có giá trị
    const daysLeft = Number(soNgayConLai || 0);

    // Đã quá hạn sử dụng
    if (daysLeft < 0) return { code: 'expired', label: 'Đã hết hạn', class: 'badge-expired' };
    // Còn dưới 30 ngày -> tự động vô hiệu hóa
    if (daysLeft < 30) return { code: 'disabled', label: 'Vô hiệu hóa (tự động)', class: 'badge-disabled' };
    // Còn dưới 90 ngày -> cảnh báo sắp hết hạn
    if (daysLeft < 90) return { code: 'warn', label: 'Sắp hết hạn', class: 'badge-warn' };
    // Còn lại -> vẫn còn hạn sử dụng bình thường
    return { code: 'active', label: 'Còn hạn', class: 'badge-active' };
}

/**
 * State toàn cục lưu trạng thái hiện tại của bộ lọc, tìm kiếm, phân trang
 * và các ID đang được thao tác (sửa/xem chi tiết) trên màn hình quản lý lô thuốc.
 */
let state = {
    search: '',      // Từ khóa tìm kiếm hiện tại (mã lô / tên thuốc)
    status: 'all',    // Trạng thái đang lọc: all | active | warn | disabled | expired
    danhMuc: 'all',   // ID danh mục đang lọc, 'all' nghĩa là tất cả danh mục
    page: 1,          // Trang hiện tại đang hiển thị
    pageSize: 8,      // Số dòng hiển thị trên mỗi trang
    editingId: null,  // ID lô thuốc đang được sửa (null nếu đang ở chế độ thêm mới)
    detailId: null,   // ID lô thuốc đang được xem chi tiết
    total: 0          // Tổng số bản ghi trả về từ server (dùng để tính phân trang)
};

let searchTimeout; // Biến lưu timer debounce cho ô tìm kiếm, tránh gọi API liên tục khi gõ
const modalForm = document.getElementById('modalForm');     // Modal thêm/sửa lô thuốc
const modalDetail = document.getElementById('modalDetail'); // Modal xem chi tiết lô thuốc

/**
 * Mở (hiển thị) một modal và khóa cuộn trang nền để tránh cuộn ngoài ý muốn.
 * @param {HTMLElement} el - Phần tử modal cần mở.
 */
function moModal(el) {
    el.classList.remove('hidden'); // Bỏ class ẩn để modal hiện ra
    document.body.style.overflow = 'hidden'; // Khóa cuộn trang phía sau modal
}

/**
 * Đóng (ẩn) một modal và trả lại trạng thái cuộn trang bình thường.
 * @param {HTMLElement} el - Phần tử modal cần đóng.
 */
function dongModal(el) {
    el.classList.add('hidden'); // Thêm class ẩn để ẩn modal
    document.body.style.overflow = ''; // Mở lại cuộn trang
}

// Gán sự kiện đóng modal cho tất cả nút có thuộc tính data-close (nút X, nút Hủy/Đóng)
document.querySelectorAll('[data-close]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        dongModal(document.getElementById(btn.dataset.close));
    });
});
// Cho phép đóng modal khi click ra ngoài vùng nội dung (click vào lớp overlay)
[modalForm, modalDetail].forEach(function(m) {
    m.addEventListener('click', function(e) {
        if (e.target === m) dongModal(m); // Chỉ đóng nếu click trúng chính overlay, không phải nội dung bên trong
    });
});

/**
 * Xóa toàn bộ trạng thái lỗi (viền đỏ, thông báo lỗi) đang hiển thị trên form thêm/sửa lô thuốc.
 * Thường được gọi trước khi mở form hoặc trước khi validate lại.
 */
function xoaLoiForm() {
    // Duyệt qua tất cả field trong form, gỡ class báo lỗi khỏi từng field
    document.querySelectorAll('#loThuocForm .form-field').forEach(function(f) {
        f.classList.remove('has-error');
    });
}

/**
 * Bật/tắt trạng thái lỗi hiển thị cho một field cụ thể trong form.
 * @param {string} id - ID của input cần đánh dấu lỗi.
 * @param {boolean} hasError - true nếu field đang bị lỗi (thiếu/không hợp lệ), false nếu hợp lệ.
 */
function datLoiTruong(id, hasError) {
    const field = document.getElementById(id); // Lấy input theo ID
    if (!field) return; // Không tìm thấy input thì bỏ qua
    const formField = field.closest('.form-field'); // Tìm khối wrapper chứa input + nhãn + thông báo lỗi
    if (formField) formField.classList.toggle('has-error', hasError); // Bật/tắt class báo lỗi tương ứng
}

/**
 * Tự động tính và hiển thị "Thành tiền nhập lô" = Số lượng tồn x Giá nhập,
 * cập nhật vào ô input chỉ đọc trên form.
 */
function updateThanhTien() {
    const sl = Number(document.getElementById('f_soLuongTon').value) || 0;  // Số lượng tồn nhập vào form
    const gia = Number(document.getElementById('f_giaNhap').value) || 0;    // Giá nhập/đơn vị nhập vào form
    document.getElementById('f_thanhTien').value = dinhDangTien(sl * gia);  // Tính thành tiền và định dạng hiển thị
}
// Tự động tính lại thành tiền mỗi khi người dùng thay đổi số lượng tồn hoặc giá nhập
document.getElementById('f_soLuongTon').addEventListener('input', updateThanhTien);
document.getElementById('f_giaNhap').addEventListener('input', updateThanhTien);

let toastTimer; // Timer điều khiển thời gian hiển thị của toast thông báo

/**
 * Hiển thị thông báo dạng toast (thông báo nổi góc màn hình) với nội dung tùy chỉnh,
 * tự động ẩn sau một khoảng thời gian.
 * @param {string} msg - Nội dung thông báo cần hiển thị.
 */
function hienThongBao(msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg; // Gán nội dung thông báo
    toast.classList.add('show'); // Hiện toast
    clearTimeout(toastTimer); // Hủy timer cũ (nếu có) để tránh ẩn sai thời điểm khi gọi liên tiếp
    toastTimer = setTimeout(function() {
        toast.classList.remove('show'); // Tự động ẩn toast sau thời gian quy định
    }, 2600); // Thời gian hiển thị: 2.6 giây
}

/**
 * Gọi API lấy danh sách lô thuốc theo bộ lọc/tìm kiếm/phân trang hiện tại trong state,
 * sau đó cập nhật bảng dữ liệu, thống kê, bộ lọc danh mục và phân trang trên giao diện.
 */
function taiDuLieu() {
    // Xây dựng query string từ state hiện tại (tìm kiếm, trạng thái, danh mục, trang)
    const params = new URLSearchParams({
        search: state.search,       // Từ khóa tìm kiếm
        status: state.status,       // Trạng thái lọc
        idDanhMuc: state.danhMuc,   // Danh mục đang lọc
        page: state.page,           // Trang hiện tại
        pageSize: state.pageSize,   // Số dòng mỗi trang
        _: Date.now()               // Tham số chống cache trình duyệt
    });

    // Gọi API lấy danh sách lô thuốc từ server
    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/layDanhSach?' + params.toString())
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status) {
                hienThiBang(res.data, res.total);           // Đổ dữ liệu vào bảng
                hienThiThongKe(res.stats);                  // Cập nhật các thẻ thống kê
                hienThiBoLocDanhMuc(res.categories);         // Cập nhật danh sách danh mục trong bộ lọc
                hienThiPhanTrang(res.total);                 // Vẽ lại thanh phân trang
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải dữ liệu:', err);
        });
}

/**
 * Đổ danh sách danh mục thuốc vào dropdown lọc theo danh mục,
 * đồng thời giữ lại giá trị đang được chọn trước đó (nếu có).
 * @param {Array<{idDanhMuc: number, tenDanhMuc: string}>} categories - Danh sách danh mục lấy từ server.
 */
function hienThiBoLocDanhMuc(categories) {
    const filterSelect = document.getElementById('filterDanhMuc');
    const currentVal = filterSelect.value; // Lưu lại lựa chọn hiện tại trước khi render lại dropdown

    // Option mặc định "Tất cả danh mục"
    let opts = '<option value="all">Tất cả danh mục</option>';
    // Sinh thêm các option từ danh sách danh mục trả về
    opts += (categories || []).map(function(c) {
        return '<option value="' + c.idDanhMuc + '">' + c.tenDanhMuc + '</option>';
    }).join('');

    filterSelect.innerHTML = opts;
    if (currentVal) filterSelect.value = currentVal; // Khôi phục lại lựa chọn cũ sau khi render
}

/**
 * Cập nhật 3 thẻ thống kê ở đầu trang: tổng số lô, số lô sắp hết hạn,
 * và số lô đã tự động vô hiệu hóa.
 * @param {{tongSo: number, sapHetHan: number, voHieuHoa: number}} stats - Dữ liệu thống kê từ server.
 */
function hienThiThongKe(stats) {
    if (!stats) return; // Không có dữ liệu thống kê thì bỏ qua, giữ nguyên giá trị cũ

    // Cập nhật từng thẻ thống kê, định dạng số theo chuẩn Việt Nam
    document.getElementById('statTotal').textContent = Number(stats.tongSo || 0).toLocaleString('vi-VN');       // Tổng số lô thuốc
    document.getElementById('statWarn').textContent = Number(stats.sapHetHan || 0).toLocaleString('vi-VN');     // Số lô sắp hết hạn
    document.getElementById('statDisabled').textContent = Number(stats.voHieuHoa || 0).toLocaleString('vi-VN'); // Số lô đã vô hiệu hóa
}

/**
 * Render danh sách lô thuốc vào bảng dữ liệu chính.
 * Hiển thị trạng thái rỗng khi không có dữ liệu, ngược lại render từng dòng bảng
 * kèm badge trạng thái hạn sử dụng và các nút thao tác (xem/sửa).
 * @param {Array<Object>} list - Danh sách lô thuốc cần hiển thị (trang hiện tại).
 * @param {number} total - Tổng số bản ghi (dùng để cập nhật state phục vụ phân trang).
 */
function hienThiBang(list, total) {
    const tbody = document.getElementById('tableBody');
    const emptyState = document.getElementById('emptyState');
    state.total = total || list.length; // Cập nhật tổng số bản ghi vào state

    // Không có dữ liệu -> ẩn bảng, hiện trạng thái rỗng, xóa phân trang
    if (!list || list.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    emptyState.style.display = 'none'; // Có dữ liệu -> ẩn trạng thái rỗng
    // Duyệt qua từng lô thuốc, sinh ra chuỗi HTML cho từng dòng bảng
    tbody.innerHTML = list.map(function(item) {
        var tt = tinhTrangThaiHan(item.soNgayConLai); // Tính trạng thái hạn sử dụng (badge) cho từng lô
        return '<tr>' +
            '<td><div class="cell-strong cell-mono">' + (item.maLo || '—') + '</div><div class="cell-sub">ID lô: ' + item.idLo + '</div></td>' +
            '<td><div class="cell-strong">' + (item.tenThuoc || '—') + '</div></td>' +
            '<td>' + dinhDangNgayVN(item.ngaySanXuat) + '</td>' +
            '<td><div class="hsd-cell"><span class="hsd-pill">' + dinhDangNgayVN(item.hanSuDung) + '</span><div class="cell-sub">' + (Number(item.soNgayConLai || 0) >= 0 ? 'còn ' + (item.soNgayConLai || 0) + ' ngày' : 'quá hạn ' + Math.abs(item.soNgayConLai || 0) + ' ngày') + '</div></div></td>' +
            '<td class="cell-strong">' + Number(item.soLuongTon || 0).toLocaleString('vi-VN') + '</td>' +
            '<td class="cell-strong" style="color:var(--green-700);">' + dinhDangTien(item.giaNhap) + '</td>' +
            '<td class="cell-strong" style="color:var(--primary-color);">' + dinhDangTien(item.giaBan) + '</td>' +
            '<td><span class="badge ' + tt.class + '">' + tt.label + '</span></td>' +
            '<td><div class="actions-cell" style="justify-content:flex-end;">' +
                '<button class="action-btn view" data-view="' + item.idLo + '" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></button>' +
                '<button class="action-btn edit" data-edit="' + item.idLo + '" title="Sửa"><i class="fa-solid fa-pen-to-square"></i></button>' +
            '</div></td>' +
        '</tr>';
    }).join('');
}

/**
 * Vẽ thanh phân trang dựa trên tổng số bản ghi, gồm nút lùi/tiến và các số trang
 * (rút gọn bằng dấu "..." nếu số trang quá nhiều so với trang hiện tại).
 * @param {number} total - Tổng số bản ghi để tính tổng số trang.
 */
function hienThiPhanTrang(total) {
    var paginationEl = document.getElementById('pagination');
    var totalPages = Math.ceil(total / state.pageSize); // Tổng số trang = tổng bản ghi / số dòng mỗi trang (làm tròn lên)

    // Chỉ có 1 trang trở xuống thì không cần hiển thị phân trang
    if (totalPages <= 1) {
        paginationEl.innerHTML = '';
        return;
    }

    var html = '';
    // Nút "Trang trước" - bị disable nếu đang ở trang đầu tiên
    html += '<button class="page-btn" onclick="chuyenTrang(' + (state.page - 1) + ')" ' + (state.page <= 1 ? 'disabled' : '') + '><i class="fa-solid fa-chevron-left"></i></button>';

    var range = 2; // Số trang hiển thị mỗi bên của trang hiện tại
    var startPage = Math.max(1, state.page - range); // Trang bắt đầu của dải hiển thị
    var endPage = Math.min(totalPages, state.page + range); // Trang kết thúc của dải hiển thị

    // Nếu dải hiển thị không bắt đầu từ trang 1, hiện nút trang 1 + dấu "..." nếu cần
    if (startPage > 1) {
        html += '<button class="page-btn" onclick="chuyenTrang(1)">1</button>';
        if (startPage > 2) html += '<span class="page-dots">...</span>';
    }

    // Sinh các nút số trang trong dải [startPage, endPage], đánh dấu active cho trang hiện tại
    for (var i = startPage; i <= endPage; i++) {
        html += '<button class="page-btn ' + (i === state.page ? 'active' : '') + '" onclick="chuyenTrang(' + i + ')">' + i + '</button>';
    }

    // Nếu dải hiển thị không kết thúc ở trang cuối, hiện dấu "..." + nút trang cuối nếu cần
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span class="page-dots">...</span>';
        html += '<button class="page-btn" onclick="chuyenTrang(' + totalPages + ')">' + totalPages + '</button>';
    }

    // Nút "Trang sau" - bị disable nếu đang ở trang cuối cùng
    html += '<button class="page-btn" onclick="chuyenTrang(' + (state.page + 1) + ')" ' + (state.page >= totalPages ? 'disabled' : '') + '><i class="fa-solid fa-chevron-right"></i></button>';

    paginationEl.innerHTML = html;
}

/**
 * Chuyển sang một trang cụ thể trong bảng danh sách lô thuốc và tải lại dữ liệu.
 * @param {number} page - Số trang muốn chuyển tới (bỏ qua nếu ngoài phạm vi hợp lệ).
 */
function chuyenTrang(page) {
    var totalPages = Math.ceil(state.total / state.pageSize);
    if (page < 1 || page > totalPages) return; // Trang không hợp lệ (nhỏ hơn 1 hoặc lớn hơn tổng số trang) thì bỏ qua
    state.page = page; // Cập nhật trang hiện tại vào state
    taiDuLieu(); // Tải lại dữ liệu theo trang mới
}

/**
 * Gọi API lấy danh sách thuốc để đổ vào dropdown chọn thuốc trong form thêm/sửa lô.
 * @param {Function} [callback] - Hàm gọi lại sau khi đã đổ xong dữ liệu (dùng khi cần set giá trị đã chọn sẵn lúc sửa).
 */
function taiDanhSachThuoc(callback) {
    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/layDanhSachThuoc?_=' + Date.now())
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status && res.data) {
                var select = document.getElementById('f_idThuoc');
                // Option mặc định yêu cầu chọn thuốc
                var opts = '<option value="">— Chọn thuốc —</option>';
                // Sinh các option từ danh sách thuốc, kèm đơn vị tính
                opts += res.data.map(function(t) {
                    return '<option value="' + t.idThuoc + '">' + t.tenThuoc + ' (' + (t.donViTinh || 'N/A') + ')</option>';
                }).join('');
                select.innerHTML = opts;
                // Gọi callback (nếu có) sau khi dropdown đã được đổ dữ liệu xong
                if (typeof callback === 'function') callback();
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải danh sách thuốc:', err);
        });
}

// Xử lý khi bấm nút "Thêm lô thuốc": reset form về trạng thái thêm mới rồi mở modal
document.getElementById('btnAddLo').addEventListener('click', function() {
    state.editingId = null; // Không có ID đang sửa -> chế độ thêm mới
    xoaLoiForm(); // Xóa lỗi hiển thị từ lần mở form trước (nếu có)
    document.getElementById('loThuocForm').reset(); // Reset toàn bộ giá trị input về mặc định
    document.getElementById('f_idLo').value = '';
    document.getElementById('formModalTitle').textContent = 'Thêm lô thuốc mới';
    document.getElementById('f_thanhTien').value = '';
    taiDanhSachThuoc(); // Tải lại danh sách thuốc cho dropdown chọn
    moModal(modalForm);
});

/**
 * Mở form sửa lô thuốc: tải chi tiết lô theo ID, đổ dữ liệu vào các input tương ứng,
 * sau đó hiển thị modal form.
 * @param {number} idLo - ID của lô thuốc cần sửa.
 */
function moFormSua(idLo) {
    state.editingId = idLo; // Lưu ID đang sửa vào state
    xoaLoiForm();
    document.getElementById('formModalTitle').textContent = 'Sửa lô thuốc';

    // Gọi API lấy chi tiết lô thuốc theo ID
    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/layChiTiet/' + idLo)
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status) {
                var d = res.data; // Dữ liệu chi tiết lô thuốc trả về từ server
                // Tải danh sách thuốc trước, sau đó mới đổ dữ liệu vào form (đảm bảo dropdown đã có option)
                taiDanhSachThuoc(function() {
                    document.getElementById('f_idLo').value = d.idLo;
                    document.getElementById('f_idThuoc').value = d.idThuoc;
                    document.getElementById('f_maLo').value = d.maLo;
                    document.getElementById('f_ngaySanXuat').value = d.ngaySanXuat || '';
                    document.getElementById('f_hanSuDung').value = d.hanSuDung;
                    document.getElementById('f_soLuongTon').value = d.soLuongTon;
                    document.getElementById('f_giaNhap').value = d.giaNhap;
                    document.getElementById('f_giaBan').value = d.giaBan || 0;
                    updateThanhTien(); // Tính lại thành tiền dựa trên dữ liệu vừa đổ vào
                    moModal(modalForm);
                });
            } else {
                alert(res.message); // Hiển thị thông báo lỗi từ server nếu lấy chi tiết thất bại
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải chi tiết:', err);
        });
}

/**
 * Mở modal xem chi tiết một lô thuốc: tải dữ liệu chi tiết theo ID và render
 * toàn bộ thông tin (mã lô, thuốc, danh mục, hạn sử dụng, trạng thái...) ra modal.
 * @param {number} idLo - ID của lô thuốc cần xem chi tiết.
 */
function moModalChiTiet(idLo) {
    state.detailId = idLo; // Lưu ID đang xem chi tiết vào state (dùng khi bấm "Chỉnh sửa lô này")
    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/layChiTiet/' + idLo)
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status) {
                var d = res.data; // Dữ liệu chi tiết lô thuốc
                var tt = tinhTrangThaiHan(d.soNgayConLai); // Trạng thái hạn sử dụng để hiển thị badge
                // Xây dựng HTML hiển thị các dòng thông tin chi tiết
                var detailHtml = '<div class="detail-grid">' +
                    '<div class="detail-row"><span class="detail-label">Mã lô</span><span class="detail-value cell-mono">' + (d.maLo || '—') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Thuốc</span><span class="detail-value">' + (d.tenThuoc || '—') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Danh mục</span><span class="detail-value">' + (d.tenDanhMuc || '—') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Ngày sản xuất</span><span class="detail-value">' + dinhDangNgayVN(d.ngaySanXuat) + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Hạn sử dụng</span><span class="detail-value">' + dinhDangNgayVN(d.hanSuDung) + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Số lượng tồn</span><span class="detail-value">' + Number(d.soLuongTon || 0).toLocaleString('vi-VN') + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Giá nhập</span><span class="detail-value" style="color:var(--green-700);">' + dinhDangTien(d.giaNhap) + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Giá bán</span><span class="detail-value" style="color:var(--primary-color);">' + dinhDangTien(d.giaBan) + '</span></div>' +
                    '<div class="detail-row"><span class="detail-label">Trạng thái</span><span class="detail-value"><span class="badge ' + tt.class + '">' + tt.label + '</span></span></div>' +
                    '<div class="detail-row"><span class="detail-label">Số ngày còn lại</span><span class="detail-value">' + (Number(d.soNgayConLai || 0) >= 0 ? (d.soNgayConLai || 0) + ' ngày' : 'Đã quá hạn ' + Math.abs(d.soNgayConLai || 0) + ' ngày') + '</span></div>' +
                '</div>';
                document.getElementById('detailBody').innerHTML = detailHtml;
                moModal(modalDetail);
            } else {
                alert(res.message);
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải chi tiết:', err);
        });
}

// Khi bấm "Chỉnh sửa lô này" trong modal chi tiết: đóng modal chi tiết rồi mở form sửa
document.getElementById('btnEditFromDetail').addEventListener('click', function() {
    dongModal(modalDetail);
    if (state.detailId) moFormSua(state.detailId); // Chỉ mở form sửa nếu đang có ID chi tiết hợp lệ
});

// Xử lý khi bấm nút "Lưu lô thuốc": validate dữ liệu form, sau đó gửi lên server để lưu (thêm mới hoặc cập nhật)
document.getElementById('btnSaveLo').addEventListener('click', function() {
    var ok = true; // Cờ đánh dấu form có hợp lệ hay không

    // Lấy giá trị các trường cần validate
    var idThuoc = document.getElementById('f_idThuoc').value;
    var maLo = document.getElementById('f_maLo').value.trim();
    var hsd = document.getElementById('f_hanSuDung').value;
    var sl = document.getElementById('f_soLuongTon').value;
    var gia = document.getElementById('f_giaNhap').value;
    var giaBan = document.getElementById('f_giaBan').value;

    // Validate: phải chọn thuốc
    datLoiTruong('f_idThuoc', !idThuoc);
    if (!idThuoc) ok = false;
    // Validate: phải nhập mã lô
    datLoiTruong('f_maLo', !maLo);
    if (!maLo) ok = false;
    // Validate: phải chọn hạn sử dụng
    datLoiTruong('f_hanSuDung', !hsd);
    if (!hsd) ok = false;
    // Validate: số lượng tồn phải nhập và >= 0
    datLoiTruong('f_soLuongTon', sl === '' || Number(sl) < 0);
    if (sl === '' || Number(sl) < 0) ok = false;
    // Validate: giá nhập phải nhập và > 0
    datLoiTruong('f_giaNhap', gia === '' || Number(gia) <= 0);
    if (gia === '' || Number(gia) <= 0) ok = false;
    // Validate: giá bán phải nhập và > 0
    datLoiTruong('f_giaBan', giaBan === '' || Number(giaBan) <= 0);
    if (giaBan === '' || Number(giaBan) <= 0) ok = false;

    if (!ok) return; // Có lỗi -> dừng lại, không gửi dữ liệu

    // Đóng gói toàn bộ dữ liệu form (bao gồm cả f_idLo ẩn để server biết là thêm mới hay cập nhật)
    var formData = new FormData(document.getElementById('loThuocForm'));

    // Gửi dữ liệu lên server để lưu
    fetch('<?php echo URLROOT; ?>/duocSi/quanLyLo/luu', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(res) {
        if (res.status) {
            hienThongBao(res.message); // Hiện thông báo lưu thành công
            dongModal(modalForm);      // Đóng modal form
            state.page = 1;            // Quay về trang đầu tiên để thấy dữ liệu mới/cập nhật
            taiDuLieu();                // Tải lại danh sách
        } else {
            alert(res.message || 'Lỗi lưu dữ liệu!'); // Server trả về lỗi nghiệp vụ (VD: trùng mã lô)
        }
    })
    .catch(function(err) {
        console.error('Lỗi:', err);
        alert('Lỗi kết nối máy chủ!'); // Lỗi mạng/kết nối tới server
    });
});



// Sự kiện ủy quyền (event delegation) trên tbody: xử lý click vào nút Xem/Sửa của từng dòng
document.getElementById('tableBody').addEventListener('click', function(e) {
    var editBtn = e.target.closest('[data-edit]'); // Tìm nút sửa gần nhất chứa phần tử được click
    var viewBtn = e.target.closest('[data-view]');  // Tìm nút xem chi tiết gần nhất chứa phần tử được click
    if (editBtn) moFormSua(Number(editBtn.dataset.edit));
    if (viewBtn) moModalChiTiet(Number(viewBtn.dataset.view));
});

// Sự kiện gõ tìm kiếm: debounce 300ms để tránh gọi API liên tục khi người dùng đang gõ
document.getElementById('searchInput').addEventListener('input', function(e) {
    state.search = e.target.value;
    state.page = 1; // Reset về trang 1 khi tìm kiếm mới
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(taiDuLieu, 300);
});

// Sự kiện thay đổi bộ lọc trạng thái
document.getElementById('filterStatus').addEventListener('change', function(e) {
    state.status = e.target.value;
    state.page = 1; // Reset về trang 1 khi đổi bộ lọc
    taiDuLieu();
});

// Sự kiện thay đổi bộ lọc danh mục
document.getElementById('filterDanhMuc').addEventListener('change', function(e) {
    state.danhMuc = e.target.value;
    state.page = 1; // Reset về trang 1 khi đổi bộ lọc
    taiDuLieu();
});

// Sự kiện bấm nút "Đặt lại": xóa toàn bộ điều kiện tìm kiếm/lọc, đưa giao diện về trạng thái mặc định
document.getElementById('btnResetFilter').addEventListener('click', function() {
    state.search = '';
    state.status = 'all';
    state.danhMuc = 'all';
    state.page = 1;
    document.getElementById('searchInput').value = '';
    document.getElementById('filterStatus').value = 'all';
    document.getElementById('filterDanhMuc').value = 'all';
    document.querySelectorAll('.stat-card').forEach(function(c) { c.classList.remove('is-active'); }); // Bỏ trạng thái active của thẻ thống kê
    taiDuLieu();
});

// Sự kiện click vào thẻ thống kê (data-quickfilter) để lọc nhanh theo trạng thái tương ứng
document.querySelectorAll('.stat-grid .stat-card[data-quickfilter]').forEach(function(card) {
    card.addEventListener('click', function() {
        document.querySelectorAll('.stat-grid .stat-card').forEach(function(c) { c.classList.remove('is-active'); }); // Bỏ active tất cả thẻ
        card.classList.add('is-active'); // Đánh dấu thẻ vừa bấm là đang active
        state.status = card.dataset.quickfilter; // Lấy trạng thái lọc gắn trên thẻ (all/warn/disabled...)
        document.getElementById('filterStatus').value = state.status; // Đồng bộ lại dropdown lọc trạng thái
        state.page = 1;
        taiDuLieu();
    });
});

// Tải dữ liệu lần đầu khi trang được load
taiDuLieu();
</script>
