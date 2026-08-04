<div class="toolbar-card">
    <div class="toolbar">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Tìm kiếm nhanh tên thuốc hoặc hoạt chất...">
        </div>
        <select class="filter-select" id="filterDanhMuc">
            <option value="all">Tất cả danh mục</option>
        </select>
        <select class="filter-select" id="filterPhanLoai">
            <option value="all">Tất cả phân loại</option>
            <option value="Kê đơn">Kê đơn (Rx)</option>
            <option value="Không kê đơn">Không kê đơn (OTC)</option>
        </select>
        <select class="filter-select" id="filterTrangThai">
            <option value="all">Tất cả trạng thái</option>
            <option value="active">Đang kinh doanh</option>
            <option value="inactive">Tạm ngưng bán</option>
        </select>
        <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
        <button class="btn btn-primary" id="btnAddThuoc" style="margin-left:auto;">
            <i class="fa-solid fa-plus"></i> Thêm thuốc mới
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
                    <th style="width: 80px; text-align:center;">Hình ảnh</th>
                    <th>Tên thuốc</th>
                    <th>Danh mục</th>
                    <th>Phân loại</th>
                    <th>Giá bán</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th style="text-align:right; width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fa-solid fa-box-open" style="font-size:40px; color:var(--gray-300); margin-bottom:14px; display:block;"></i>
        <div class="t1">Không tìm thấy thuốc</div>
        <div>Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc.</div>
    </div>
    <div class="pagination-bar" id="paginationBar">
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<!-- Modal Form Thêm/Sửa thuốc -->

<div class="modal-overlay hidden" id="modalDetail">

    <div class="modal-box">

        <div class="modal-head">

            <h2>Thông tin thuốc</h2>

            <button
                class="modal-close"
                data-close="modalDetail">

                &times;

            </button>

        </div>

        <div
            class="modal-body"
            id="detailContent">

        </div>

    </div>

</div>

<div class="modal-overlay hidden" id="modalForm">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2 id="formModalTitle">Thêm thuốc mới</h2>
                <div class="desc">Nhập đầy đủ thông tin thuốc theo dữ liệu hệ thống</div>
            </div>
            <button class="modal-close" data-close="modalForm">&times;</button>
        </div>
        <div class="modal-body">
            <form id="thuocForm" onsubmit="return false;">
                <input type="hidden" id="f_idThuoc" name="idThuoc">
                <input type="hidden" id="f_hinhAnhUrlHienTai" name="hinhAnhUrlHienTai">
                <div class="form-grid">
                    <div class="form-field span-2">
                        <label>Tên thương mại thuốc <span class="req">*</span></label>
                        <input type="text" id="f_tenThuoc" name="tenThuoc" required>
                    </div>
                    <div class="form-field">
                        <label>Danh mục phân nhóm <span class="req">*</span></label>
                        <select id="f_idDanhMuc" name="idDanhMuc" required></select>
                    </div>
                    <div class="form-field">
                        <label>Đơn vị tính <span class="req">*</span></label>
                        <input type="text" id="f_donViTinh" name="donViTinh" placeholder="VD: Viên, Hộp, Vỉ" required>
                    </div>
                    <div class="form-field">
                        <label>Hoạt chất chính <span class="req">*</span></label>
                        <input type="text" id="f_thanhPhan" name="thanhPhan" required>
                    </div>
                    <div class="form-field">
                        <label>Hàm lượng lượng chất</label>
                        <input type="text" id="f_hamLuong" name="hamLuong" placeholder="VD: 500mg, 10ml">
                    </div>
                    <div class="form-field span-2">
                        <label>Mô tả chỉ định & Công dụng thuốc <span class="req">*</span></label>
                        <textarea id="f_congDung" name="congDung" required></textarea>
                    </div>
                    <div class="form-field">
                        <label>Giá bán niêm yết (đ) <span class="req">*</span></label>
                        <input type="number" id="f_giaBan" name="giaBan" required>
                    </div>
                    <div class="form-field span-2">
                        <label>Hình ảnh sản phẩm (có thể chọn nhiều)</label>
                        <div class="file-input-wrapper" style="margin-bottom:8px;">
                            <button type="button" class="btn-upload-trigger">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Chọn ảnh từ máy
                            </button>
                            <input type="file" id="f_hinhAnh" name="hinhAnhFiles[]" accept="image/*" multiple>
                        </div>
                        <div id="f_hinhAnhPreviews" class="image-previews"></div>
                    </div>
                    <div class="form-field span-2">
                        <label>Phân loại quản lý dược</label>
                        <div class="kedon-toggle">
                            <label class="kedon-option otc selected" data-value="Không kê đơn">
                                <input type="radio" name="yeuCauKeDon" value="Không kê đơn" checked>
                                <div class="t"><i class="fa-solid fa-circle-check"></i> Không kê đơn (OTC)</div>
                            </label>
                            <label class="kedon-option rx" data-value="Kê đơn">
                                <input type="radio" name="yeuCauKeDon" value="Kê đơn">
                                <div class="t"><i class="fa-solid fa-circle-exclamation"></i> Bắt buộc kê đơn (Rx)</div>
                            </label>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Giới hạn giỏ hàng / đơn hàng</label>
                        <input type="number" id="f_gioiHanMua" name="gioiHanMua" disabled>
                        <div class="gioihan-row">
                            <input type="checkbox" id="f_khongGioiHan" name="khongGioiHan" checked>
                            <label for="f_khongGioiHan">Không giới hạn mua (-1)</label>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Kinh doanh</label>
                        <div class="switch-row">
                            <div class="label-txt" id="trangThaiLabel">Đang bán</div>
                            <label class="switch">
                                <input type="checkbox" id="f_trangThai" name="trangThai" checked>
                                <span class="slider-switch"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalForm">Hủy bỏ</button>
            <button class="btn btn-primary" id="btnSaveThuoc"><i class="fa-solid fa-floppy-disk"></i> Lưu dữ liệu</button>
        </div>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="toastMsg">Đã lưu thành công</span>
</div>

<script>
    // Ảnh mặc định hiển thị khi thuốc không có ảnh
    const PLACEHOLDER_IMG = 'https://placehold.co/80x80/e2e8f0/64748b?text=No+Image';
    // Số lượng bản ghi hiển thị trên mỗi trang của bảng
    const PAGE_SIZE = 8;
    // Trang hiện tại đang xem trong bảng phân trang
    let currentPage = 1;
    // Toàn bộ dữ liệu thuốc đã lọc từ server (chưa cắt theo trang), dùng để phân trang phía client
    let currentData = [];
    // Biến dùng để debounce ô tìm kiếm (tránh gọi API liên tục khi gõ)
    let searchTimeout;
    // Tham chiếu tới modal form Thêm/Sửa thuốc
    const modalForm = document.getElementById('modalForm');

    /**
     * Mở một modal (gỡ class "hidden") và khóa cuộn trang nền.
     * @param {HTMLElement} el - Phần tử modal-overlay cần mở
     */
    function moModal(el) {
        el.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Đóng một modal (thêm lại class "hidden") và mở lại cuộn trang nền.
     * @param {HTMLElement} el - Phần tử modal-overlay cần đóng
     */
    function dongModal(el) {
        el.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Gắn sự kiện đóng modal cho tất cả các nút có thuộc tính data-close (nút X, nút Hủy)
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => dongModal(document.getElementById(btn.dataset.close)));
    });

    /**
     * Định dạng một số tiền sang chuỗi tiền tệ Việt Nam (ví dụ: 15000 -> "15.000đ").
     * @param {number} n - Số tiền cần định dạng
     * @returns {string} Chuỗi tiền đã định dạng, có hậu tố "đ"
     */
    function dinhDangTien(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    // Chuẩn hóa đường dẫn ảnh từ CSDL (xử lý cả đường dẫn tương đối và tuyệt đối)
    function chuanHoaDuongDanAnh(path) {
        if (!path) return PLACEHOLDER_IMG;
        if (path.indexOf('http') === 0) return path;
        if (path.indexOf('assets/') === 0) return '<?php echo URLROOT; ?>/' + path;
        if (path.indexOf('/assets/') === 0) return '<?php echo URLROOT; ?>' + path;
        if (path.indexOf('/') === 0) return '<?php echo URLROOT; ?>' + path;
        return '<?php echo URLROOT; ?>/' + path;
    }

    // ===== TOAST NOTIFICATION =====
    /**
     * Hiển thị thông báo toast tạm thời ở góc màn hình trong 3.5 giây.
     * @param {string} msg - Nội dung thông báo cần hiển thị
     */
    function hienThongBao(msg) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');
        toastMsg.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toast._hideTimer); // Hủy timer cũ nếu toast đang hiển thị, tránh ẩn sai lúc
        toast._hideTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    // ===== FORM VALIDATION =====
    /**
     * Xóa toàn bộ trạng thái lỗi (class "has-error") đang hiển thị trên form.
     */
    function xoaLoiForm() {
        document.querySelectorAll('.form-field.has-error').forEach(el => el.classList.remove('has-error'));
    }

    /**
     * Đánh dấu lỗi cho một trường trong form và hiển thị thông báo lỗi bên dưới trường đó.
     * @param {string} fieldId - id của input/select/textarea bị lỗi
     * @param {string} message - Nội dung thông báo lỗi cần hiển thị
     */
    function datLoiTruong(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        const formField = field.closest('.form-field');
        if (!formField) return;
        formField.classList.add('has-error');
        // Tái sử dụng thẻ thông báo lỗi nếu đã tồn tại, nếu chưa thì tạo mới
        let errorEl = formField.querySelector('.error-msg');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'error-msg';
            formField.appendChild(errorEl);
        }
        errorEl.textContent = message;
    }

    /**
     * Kiểm tra tính hợp lệ của toàn bộ form Thêm/Sửa thuốc trước khi lưu.
     * Các trường bắt buộc: tên thuốc, danh mục, đơn vị tính, hoạt chất, công dụng,
     * giá bán (> 0), và giới hạn mua (> 0 nếu không chọn "không giới hạn").
     * @returns {boolean} true nếu form hợp lệ, false nếu có ít nhất 1 lỗi
     */
    function kiemTraForm() {
        xoaLoiForm();
        let isValid = true;

        // Tên thương mại thuốc - bắt buộc
        const tenThuoc = document.getElementById('f_tenThuoc').value.trim();
        if (!tenThuoc) {
            datLoiTruong('f_tenThuoc', 'Vui lòng nhập tên thương mại thuốc');
            isValid = false;
        }

        // Danh mục phân nhóm - bắt buộc chọn
        const idDanhMuc = document.getElementById('f_idDanhMuc').value;
        if (!idDanhMuc) {
            datLoiTruong('f_idDanhMuc', 'Vui lòng chọn danh mục phân nhóm');
            isValid = false;
        }

        // Đơn vị tính (Viên, Hộp, Vỉ...) - bắt buộc
        const donViTinh = document.getElementById('f_donViTinh').value.trim();
        if (!donViTinh) {
            datLoiTruong('f_donViTinh', 'Vui lòng nhập đơn vị tính');
            isValid = false;
        }

        // Hoạt chất chính - bắt buộc
        const thanhPhan = document.getElementById('f_thanhPhan').value.trim();
        if (!thanhPhan) {
            datLoiTruong('f_thanhPhan', 'Vui lòng nhập hoạt chất chính');
            isValid = false;
        }

        // Mô tả công dụng - bắt buộc
        const congDung = document.getElementById('f_congDung').value.trim();
        if (!congDung) {
            datLoiTruong('f_congDung', 'Vui lòng nhập mô tả công dụng thuốc');
            isValid = false;
        }

        // Giá bán - bắt buộc và phải lớn hơn 0
        const giaBan = document.getElementById('f_giaBan').value;
        if (!giaBan || Number(giaBan) <= 0) {
            datLoiTruong('f_giaBan', 'Giá bán phải lớn hơn 0');
            isValid = false;
        }

        // Giới hạn mua - chỉ bắt buộc kiểm tra khi KHÔNG chọn "không giới hạn"
        const khongGioiHan = document.getElementById('f_khongGioiHan').checked;
        if (!khongGioiHan) {
            const gioiHanMua = document.getElementById('f_gioiHanMua').value;
            if (!gioiHanMua || Number(gioiHanMua) <= 0) {
                datLoiTruong('f_gioiHanMua', 'Giới hạn mua phải lớn hơn 0');
                isValid = false;
            }
        }

        return isValid;
    }

    /**
     * Chuyển đổi giao diện lựa chọn phân loại kê đơn (OTC / Rx): đánh dấu option
     * đang chọn bằng class "selected" và check radio input tương ứng.
     * @param {string} value - Giá trị phân loại được chọn ("Kê đơn" hoặc "Không kê đơn")
     */
    function datCheDoKeDon(value) {
        document.querySelectorAll('.kedon-option').forEach(opt => {
            const isMatch = opt.dataset.value === value;
            opt.classList.toggle('selected', isMatch);
            opt.querySelector('input').checked = isMatch;
        });
    }
    // Cho phép click vào cả khối option để chọn phân loại kê đơn (không chỉ riêng radio input)
    document.querySelectorAll('.kedon-option').forEach(opt => {
        opt.addEventListener('click', () => datCheDoKeDon(opt.dataset.value));
    });

    // Khi tick "Không giới hạn mua" -> vô hiệu hóa và xóa trắng ô nhập giới hạn mua
    document.getElementById('f_khongGioiHan').addEventListener('change', (e) => {
        document.getElementById('f_gioiHanMua').disabled = e.target.checked;
        if (e.target.checked) document.getElementById('f_gioiHanMua').value = '';
    });
    // Đổi nhãn hiển thị trạng thái kinh doanh theo switch bật/tắt
    document.getElementById('f_trangThai').addEventListener('change', (e) => {
        document.getElementById('trangThaiLabel').textContent = e.target.checked ? 'Đang bán' : 'Tạm ngưng';
    });

    // Khi chọn ảnh mới từ máy -> xóa các preview ảnh mới cũ rồi render preview cho từng file vừa chọn
    document.getElementById('f_hinhAnh').addEventListener('change', (e) => {
        const previewsContainer = document.getElementById('f_hinhAnhPreviews');
        const newPreviews = previewsContainer.querySelectorAll('.preview-new');
        newPreviews.forEach(el => el.remove());

        const files = e.target.files;
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file) {
                // Đọc file dưới dạng base64 để hiển thị preview ngay trên trình duyệt (chưa upload lên server)
                const reader = new FileReader();
                reader.onload = (event) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item preview-new';
                    div.innerHTML = `<img class="preview-thumb" src="${event.target.result}" alt="preview"><span class="preview-label">Mới</span>`;
                    previewsContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        }
    });

    // ===== TRUY XUẤT DỮ LIỆU ĐỘNG =====
    /**
     * Gọi API lấy danh sách thuốc theo bộ lọc hiện tại (từ khóa, danh mục, phân loại, trạng thái)
     * và cập nhật lại bảng dữ liệu cùng danh sách danh mục cho bộ lọc.
     */
    function taiDanhSachThuoc() {
        const search = document.getElementById('searchInput').value.trim(); // Từ khóa tìm kiếm (tên thuốc/hoạt chất)
        const idDanhMuc = document.getElementById('filterDanhMuc').value; // Lọc theo danh mục
        const phanLoai = document.getElementById('filterPhanLoai').value; // Lọc theo phân loại kê đơn/OTC
        const trangThai = document.getElementById('filterTrangThai').value; // Lọc theo trạng thái kinh doanh

        // _=${Date.now()} dùng để chống cache trình duyệt cho request GET
        fetch(`<?php echo URLROOT; ?>/admin/quanLyThuoc/layDanhSach?search=${encodeURIComponent(search)}&idDanhMuc=${idDanhMuc}&phanLoai=${phanLoai}&trangThai=${trangThai}&_=${Date.now()}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    hienThiBang(res.data);
                    hienThiBoLocDanhMuc(res.categories);
                }
            });
    }

    /**
     * Đổ danh sách danh mục vào dropdown bộ lọc và dropdown chọn danh mục trong form thêm/sửa thuốc,
     * đồng thời giữ lại giá trị bộ lọc đang chọn (nếu có).
     * @param {Array<{idDanhMuc: number, tenDanhMuc: string}>} categories - Danh sách danh mục từ server
     */
    function hienThiBoLocDanhMuc(categories) {
        const select = document.getElementById('filterDanhMuc');
        const formSelect = document.getElementById('f_idDanhMuc');
        const currentFilterVal = select.value; // Lưu lại lựa chọn hiện tại để không bị reset sau khi render lại

        const opts = categories.map(c => `<option value="${c.idDanhMuc}">${c.tenDanhMuc}</option>`).join('');
        select.innerHTML = '<option value="all">Tất cả danh mục</option>' + opts;
        formSelect.innerHTML = '<option value="">— Chọn danh mục —</option>' + opts;
        select.value = currentFilterVal;
    }

    /**
     * Render thanh phân trang dựa trên tổng số trang, hiển thị tối đa 2 trang liền kề
     * mỗi bên trang hiện tại, kèm dấu "..." khi có khoảng trang bị ẩn.
     */
    function hienThiPhanTrang() {
        const paginationEl = document.getElementById('pagination');
        const totalPages = Math.ceil(currentData.length / PAGE_SIZE);
        if (totalPages <= 1) {
            paginationEl.innerHTML = '';
            return;
        }

        let html = '';
        // Nút "trang trước", disabled nếu đang ở trang đầu tiên
        html += `<button class="page-btn" onclick="chuyenTrang(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i></button>`;

        const range = 2; // Số trang hiển thị liền kề mỗi bên trang hiện tại
        const startPage = Math.max(1, currentPage - range);
        const endPage = Math.min(totalPages, currentPage + range);

        // Nếu trang bắt đầu không phải trang 1 -> luôn hiện nút trang 1 (và dấu "..." nếu có khoảng cách)
        if (startPage > 1) {
            html += `<button class="page-btn" onclick="chuyenTrang(1)">1</button>`;
            if (startPage > 2) html += `<span class="page-dots">...</span>`;
        }

        // Render các nút số trang trong khoảng [startPage, endPage], đánh dấu active cho trang hiện tại
        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="chuyenTrang(${i})">${i}</button>`;
        }

        // Nếu trang kết thúc không phải trang cuối -> luôn hiện nút trang cuối (và dấu "..." nếu có khoảng cách)
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span class="page-dots">...</span>`;
            html += `<button class="page-btn" onclick="chuyenTrang(${totalPages})">${totalPages}</button>`;
        }

        // Nút "trang sau", disabled nếu đang ở trang cuối cùng
        html += `<button class="page-btn" onclick="chuyenTrang(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}><i class="fa-solid fa-chevron-right"></i></button>`;

        paginationEl.innerHTML = html;
    }

    /**
     * Chuyển sang một trang cụ thể trong bảng (nếu hợp lệ) và render lại dữ liệu trang đó.
     * @param {number} page - Số trang muốn chuyển đến
     */
    function chuyenTrang(page) {
        const totalPages = Math.ceil(currentData.length / PAGE_SIZE);
        if (page < 1 || page > totalPages) return; // Bỏ qua nếu số trang không hợp lệ
        currentPage = page;
        hienThiTrangHienTai();
    }

    /**
     * Render dữ liệu của trang hiện tại (cắt ra từ currentData theo PAGE_SIZE) vào bảng,
     * bao gồm badge phân loại, badge trạng thái, cảnh báo sắp hết hàng, và các nút thao tác.
     */
    function hienThiTrangHienTai() {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');
        const start = (currentPage - 1) * PAGE_SIZE;
        const end = start + PAGE_SIZE;
        const pageData = currentData.slice(start, end); // Chỉ lấy dữ liệu của trang hiện tại

        tbody.innerHTML = pageData.map(item => {
            const badgeClass = item.yeuCauKeDon === 'Kê đơn' ? 'badge-rx' : 'badge-otc'; // Class badge phân loại kê đơn/OTC
            // Xử lý linh hoạt vì trangThai có thể là number, string hoặc boolean tùy nguồn dữ liệu
            const trangThai = item.trangThai == 1 || item.trangThai === '1' || item.trangThai === true;
            const statusClass = trangThai ? 'badge-active' : 'badge-inactive';
            const statusLabel = trangThai ? 'Còn bán' : 'Tạm ngưng';
            // Cảnh báo sắp hết hàng nếu tồn kho <= 10
            const lowStockHTML = item.tongTon <= 10 ? `<br><span class="badge badge-lowstock" style="margin-top:4px;">Sắp hết hàng</span>` : '';

            return `
                <tr class="${trangThai ? '' : 'row-inactive'}">
                    <td style="text-align:center;"><img class="thumb" src="${chuanHoaDuongDanAnh(item.hinhAnh)}" alt=""></td>
                    <td>
                        <div class="cell-strong">${item.tenThuoc}</div>
                    </td>
                    <td class="cell-strong">${item.tenDanhMuc || 'Chưa phân loại'}</td>
                    <td><span class="badge ${badgeClass}">${item.yeuCauKeDon}</span></td>
                    <td class="cell-strong" style="color:var(--green-700);">${dinhDangTien(item.giaBan)}</td>
                    <td class="cell-strong">${Number(item.tongTon).toLocaleString('vi-VN')} ${item.donViTinh}${lowStockHTML}</td>
                    <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                    <td>
                        <div class="actions-cell">
                            <button class="action-btn view" onclick="moChiTiet(${item.idThuoc})" title="Chi tiết"><i class="fa-solid fa-eye"></i></button>
                            <button class="action-btn edit" onclick="moFormSua(${item.idThuoc})" title="Sửa thông tin"><i class="fa-solid fa-pen-to-square"></i></button>
                            <button class="action-btn delete" onclick="doiTrangThai(${item.idThuoc})" title="Đổi trạng thái kinh doanh"><i class="fa-solid fa-toggle-on"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        hienThiPhanTrang();
    }

    /**
     * Nhận danh sách thuốc mới từ API, lưu vào currentData, reset về trang 1
     * và render lại bảng + phân trang. Hiển thị trạng thái rỗng nếu không có kết quả.
     * @param {Array<Object>} list - Danh sách thuốc đã lọc, trả về từ server
     */
    function hienThiBang(list) {
        currentData = list;
        currentPage = 1; // Luôn quay về trang 1 mỗi khi có bộ lọc/tìm kiếm mới
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');
        document.getElementById('resultCount').textContent = list.length;

        if (list.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            document.getElementById('pagination').innerHTML = '';
            return;
        }
        emptyState.style.display = 'none';

        hienThiTrangHienTai();
    }

    /**
     * Mở modal form ở chế độ "Thêm thuốc mới": xóa trắng toàn bộ form và đặt lại
     * các giá trị mặc định (không giới hạn mua, đang bán, không kê đơn).
     */
    function moFormThem() {
        document.getElementById('formModalTitle').textContent = 'Thêm ';
        document.getElementById('thuocForm').reset();
        document.getElementById('f_idThuoc').value = '';
        document.getElementById('f_hinhAnhPreviews').innerHTML = '';
        document.getElementById('f_gioiHanMua').disabled = true;
        document.getElementById('f_trangThai').checked = true;
        document.getElementById('trangThaiLabel').textContent = 'Đang bán';
        datCheDoKeDon('Không kê đơn');
        moModal(modalForm);
    }

    /**
     * Mở modal form ở chế độ "Chỉnh sửa": gọi API lấy chi tiết thuốc theo id
     * và điền toàn bộ dữ liệu (thông tin chung, phân loại, giới hạn mua, ảnh hiện có) vào form.
     * @param {number} id - idThuoc cần chỉnh sửa
     */
    function moFormSua(id) {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyThuoc/layChiTietDuLieu/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    const t = res.thuoc;
                    document.getElementById('formModalTitle').textContent = 'Chỉnh sửa thông tin thuốc';
                    document.getElementById('f_idThuoc').value = t.idThuoc;
                    document.getElementById('f_tenThuoc').value = t.tenThuoc;
                    document.getElementById('f_idDanhMuc').value = t.idDanhMuc || '';
                    document.getElementById('f_donViTinh').value = t.donViTinh;
                    document.getElementById('f_thanhPhan').value = t.thanhPhan;
                    document.getElementById('f_hamLuong').value = t.hamLuong || '';
                    document.getElementById('f_congDung').value = t.congDung;
                    document.getElementById('f_giaBan').value = t.giaBan;

                    datCheDoKeDon(t.yeuCauKeDon);

                    // gioiHanMua = -1 nghĩa là không giới hạn -> tick checkbox và khóa ô nhập số
                    const noLimit = t.gioiHanMua == -1;
                    document.getElementById('f_khongGioiHan').checked = noLimit;
                    document.getElementById('f_gioiHanMua').disabled = noLimit;
                    document.getElementById('f_gioiHanMua').value = noLimit ? '' : t.gioiHanMua;

                    document.getElementById('f_trangThai').checked = t.trangThai == 1;
                    document.getElementById('trangThaiLabel').textContent = t.trangThai == 1 ? 'Đang bán' : 'Tạm ngưng';

                    // Hiển thị các ảnh hiện có của thuốc kèm nút xóa cho từng ảnh
                    const previewsContainer = document.getElementById('f_hinhAnhPreviews');
                    previewsContainer.innerHTML = '';
                    if (res.images && res.images.length > 0) {
                        res.images.forEach(img => {
                            const div = document.createElement('div');
                            div.className = 'preview-item preview-existing';
                            div.innerHTML = `
                                <img class="preview-thumb" src="${chuanHoaDuongDanAnh(img.duongDan)}" alt="">
                                <button class="preview-delete-btn" type="button" title="Xóa ảnh" data-img="${img.duongDan}">&times;</button>
                            `;
                            previewsContainer.appendChild(div);
                        });

                        // Khi bấm nút xóa ảnh: thêm input ẩn deleteImages[] để báo server xóa ảnh này khi lưu,
                        // đồng thời ẩn preview ảnh đó khỏi giao diện ngay lập tức
                        previewsContainer.querySelectorAll('.preview-delete-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const imgPath = this.dataset.img;
                                let deleteInput = document.getElementById('f_deleteImages');
                                if (!deleteInput) {
                                    deleteInput = document.createElement('div');
                                    deleteInput.id = 'f_deleteImages';
                                    document.getElementById('thuocForm').appendChild(deleteInput);
                                }
                                const hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = 'deleteImages[]';
                                hidden.value = imgPath;
                                deleteInput.appendChild(hidden);
                                this.closest('.preview-item').style.display = 'none';
                            });
                        });
                    }

                    moModal(modalForm);
                }
            });
    }

    // Sự kiện khi bấm nút "Lưu dữ liệu": kiểm tra hợp lệ HTML5 rồi gửi form (thêm mới hoặc cập nhật) lên server
    document.getElementById('btnSaveThuoc').addEventListener('click', function () {
        var form = document.getElementById('thuocForm');

        if (!form.checkValidity()) {
            form.reportValidity(); // Hiển thị thông báo lỗi mặc định của trình duyệt cho các trường required
            return;
        }

        var formData = new FormData(form); // FormData hỗ trợ gửi kèm file ảnh (multipart/form-data)

        fetch('<?php echo URLROOT; ?>/admin/quanLyThuoc/luu', {
            method: 'POST',
            body: formData
        })
        .then(function (res) {
            return res.text(); // Lấy dạng text trước để tự parse JSON, tránh lỗi crash khi server trả về không phải JSON hợp lệ
        })
        .then(function (text) {
            var res;
            try {
                res = JSON.parse(text);
            } catch (e) {
                // Server trả về nội dung không phải JSON (ví dụ lỗi PHP) -> hiển thị 200 ký tự đầu để debug
                console.error("Server Response Error:", text);
                alert("Lỗi phản hồi từ máy chủ! Chi tiết: " + text.substring(0, 200));
                return;
            }

            if (res.status) {
                var modal = document.getElementById('modalForm');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
                alert(res.message);

                taiDanhSachThuoc(); // Tải lại danh sách để cập nhật dữ liệu vừa lưu
            } else {
                alert(res.message || 'Có lỗi xảy ra, không thể lưu dữ liệu!');
            }
        })
        .catch(function (err) {
            console.error(err);
            alert('Lỗi kết nối máy chủ!');
        });
    });

    /**
     * Đổi trạng thái kinh doanh (đang bán / tạm ngưng) của một thuốc, sau khi xác nhận.
     * @param {number} id - idThuoc cần đổi trạng thái
     */
    function doiTrangThai(id) {
        if (confirm('Xác nhận thay đổi trạng thái mở bán / tạm ngưng của mặt hàng thuốc này?')) {
            fetch(`<?php echo URLROOT; ?>/admin/quanLyThuoc/doiTrangThai/${id}`, {
                method: 'POST',
                headers: {
                    'Cache-Control': 'no-cache'
                }
            })
            .then(function (res) {
                return res.json();
            })
            .then(function (res) {
                if (res.status) {
                    // Ưu tiên dùng hàm hienThongBao nếu có (toast), fallback về alert() nếu không tồn tại
                    if (typeof hienThongBao === 'function') {
                        hienThongBao(res.message);
                    } else {
                        alert(res.message);
                    }

                    // Reset bộ lọc trạng thái về "Tất cả" để dễ thấy thuốc vừa đổi trạng thái trong danh sách
                    document.getElementById('filterTrangThai').value = 'all';

                    taiDanhSachThuoc();
                } else {
                    alert(res.message || 'Thay đổi trạng thái thất bại!');
                }
            })
            .catch(function (err) {
                console.error('Lỗi khi đổi trạng thái:', err);
                alert('Có lỗi kết nối khi đổi trạng thái!');
            });
        }
    }

    // Gõ vào ô tìm kiếm -> debounce 300ms trước khi gọi lại API (tránh spam request)
    document.getElementById('searchInput').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(taiDanhSachThuoc, 300);
    });
    // Đổi bộ lọc danh mục/phân loại/trạng thái -> tải lại danh sách ngay
    document.getElementById('filterDanhMuc').addEventListener('change', taiDanhSachThuoc);
    document.getElementById('filterPhanLoai').addEventListener('change', taiDanhSachThuoc);
    document.getElementById('filterTrangThai').addEventListener('change', taiDanhSachThuoc);
    // Nút "Đặt lại": xóa từ khóa tìm kiếm, reset các bộ lọc về mặc định rồi tải lại danh sách
    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterDanhMuc').value = 'all';
        document.getElementById('filterPhanLoai').value = 'all';
        document.getElementById('filterTrangThai').value = 'all';
        taiDanhSachThuoc();
    });

    // Nút "Thêm thuốc mới" -> mở form ở chế độ thêm mới
    document.getElementById('btnAddThuoc').addEventListener('click', moFormThem);

    // Tải danh sách thuốc ngay khi trang load lần đầu
    taiDanhSachThuoc();

    // Nếu có idThuoc được lưu tạm trong sessionStorage (được điều hướng từ trang chi tiết thuốc sang)
    // thì tự động mở modal chỉnh sửa đúng thuốc đó, sau đó xóa key này đi để không mở lại lần sau
    (function moFormSuaTuSessionNeuCo() {
        const idSua = sessionStorage.getItem('suaThuocId');
        if (idSua) {
            sessionStorage.removeItem('suaThuocId');
            moFormSua(idSua);
        }
    })();

    /**
     * Điều hướng sang trang chi tiết của một thuốc.
     * @param {number} id - idThuoc cần xem chi tiết
     */
    function moChiTiet(id) {
        window.location.href = '<?php echo URLROOT; ?>/admin/quanLyThuoc/chiTiet/' + id;
    }
</script>
