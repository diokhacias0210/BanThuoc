<?php
/**
 * View: duyetDon.php
 * Trang "Duyệt đơn thuốc" dành cho Dược sĩ.
 * Chức năng tổng quan:
 *  - Hiển thị danh sách các đơn thuốc kê đơn (yêu cầu từ khách hàng) dạng bảng,
 *    có tìm kiếm, lọc theo trạng thái và phân trang.
 *  - Cho phép Dược sĩ xem chi tiết một đơn thuốc (thông tin khách hàng, danh sách
 *    thuốc, ảnh toa thuốc) thông qua modal.
 *  - Cho phép duyệt từng đơn, duyệt tất cả đơn đang chờ, hoặc từ chối đơn kèm lý do.
 *  - Toàn bộ dữ liệu được lấy/ghi thông qua các API (fetch) gọi tới controller
 *    duocSi/duyetDon (layDanhSach, layChiTiet, duyet, duyetTatCa, tuChoi).
 */
?>
<div class="toolbar">
    <div class="toolbar-search">
        <div class="icon icon-search"></div>
        <!-- Ô nhập liệu tìm kiếm theo mã yêu cầu hoặc tên khách hàng -->
        <input type="text" id="searchInput" placeholder="Tìm theo mã yêu cầu hoặc tên khách hàng...">
    </div>
    <!-- Bộ lọc theo trạng thái đơn: tất cả / chờ duyệt / đã duyệt / từ chối -->
    <select class="filter-select" id="filterStatus">
        <option value="all">Tất cả trạng thái</option>
        <option value="CHO_DUYET">Chờ duyệt</option>
        <option value="DA_DUYET">Đã duyệt</option>
        <option value="TU_CHOI">Từ chối</option>
    </select>
    <!-- Nút đặt lại bộ lọc và ô tìm kiếm về mặc định -->
    <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
            <!-- Nút duyệt nhanh toàn bộ các yêu cầu đang ở trạng thái chờ duyệt -->
            <button class="btn btn-primary" id="btnApproveAll" style="margin-left:auto;">
                <div class="icon icon-check-all"></div>
                Duyệt tất cả yêu cầu chờ
            </button>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Mã yêu cầu</th>
                    <th>Khách hàng</th>
                    <th>Ngày gửi yêu cầu</th>
                    <th>Ghi chú khách hàng</th>
                    <th>Trạng thái</th>
                    <th style="text-align:right;">Thao tác phê duyệt</th>
                </tr>
            </thead>
            <!-- Nội dung bảng được render động bằng JS (hàm hienThiBang) -->
            <tbody id="tableBody"></tbody>
        </table>
    </div>
            <!-- Thông báo hiển thị khi danh sách đơn thuốc rỗng -->
            <div id="emptyState" class="empty-state" style="display:none;">
                <div class="icon icon-empty-box"></div>
                <div class="t1">Không tìm thấy yêu cầu nào</div>
            </div>
    <div class="pagination-bar">
        <!-- Khu vực chứa các nút phân trang, render động bằng JS -->
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<!-- Modal xem chi tiết đơn thuốc kê đơn -->
<div class="modal-overlay hidden" id="modalDetail">
    <div class="modal-box wide">
        <div class="modal-head">
            <h2>Chi tiết đơn thuốc kê đơn</h2>
            <button class="modal-close" data-close="modalDetail" type="button">
                <div class="icon icon-close-modal"></div>
            </button>
        </div>
        <div class="modal-body">
            <div class="prescription-flow">
                <div class="prescription-meta">
                    <h4>Thông tin yêu cầu</h4>
                    <div class="meta-list">
                        <!-- Các span dưới đây được JS đổ dữ liệu khi mở modal chi tiết -->
                        <div><strong>Mã đơn:</strong> <span id="view_maDon"></span></div>
                        <div><strong>Khách hàng:</strong> <span id="view_tenKhach"></span></div>
                        <div><strong>Ngày gửi:</strong> <span id="view_ngayGui"></span></div>
                        <div class="span-2"><strong>Ghi chú:</strong> <span id="view_ghiChu"></span></div>
                    </div>
                </div>

                <div class="prescription-meta">
                    <h4>Danh sách thuốc</h4>
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th style="width:70px;">Ảnh</th>
                                <th>Tên thuốc</th>
                                <th>Liều dùng</th>
                                <th style="width:80px; text-align:center;">SL</th>
                            </tr>
                        </thead>
                        <!-- Danh sách thuốc trong đơn, render động bằng JS -->
                        <tbody id="medTableBody"></tbody>
                    </table>
                </div>

                <div class="prescription-meta">
                    <h4>Ảnh toa thuốc</h4>
                    <div class="panel-image">
                        <div class="title-hint">(Nhấn trực tiếp vào ảnh dưới đây để phóng to xem rõ nét)</div>
                        <!-- Ảnh toa thuốc, click vào sẽ mở lightbox phóng to -->
                        <img id="view_hinhAnhToa" class="prescription-img" src="" alt="Ảnh toa thuốc">
                    </div>
                </div>
            </div>
        </div>
        <!-- Vùng chân modal chứa các nút thao tác (Duyệt / Từ chối / Đóng), render động theo trạng thái đơn -->
        <div class="modal-foot" id="modalDetailFoot"></div>
    </div>
</div>

<!-- Modal nhập lý do khi từ chối một đơn thuốc -->
<div class="modal-overlay hidden" id="modalRejectReason">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Từ chối đơn thuốc</h2>
            <button class="modal-close" data-close="modalRejectReason" type="button">
                <div class="icon icon-close-modal"></div>
            </button>
        </div>
        <div class="modal-body">
            <label for="txtRejectReason" style="display:block; margin-bottom:8px; font-weight:600;">Lý do từ chối</label>
            <!-- Nội dung lý do từ chối do Dược sĩ nhập -->
            <textarea id="txtRejectReason" class="reason-textarea" placeholder="Nhập lý do từ chối..."></textarea>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalRejectReason" type="button">Hủy</button>
            <button class="btn btn-primary" id="btnConfirmReject" type="button">Xác nhận từ chối</button>
        </div>
    </div>
</div>

<!-- Lightbox phóng to ảnh toa thuốc -->
<div class="lightbox-overlay" id="lightboxOverlay">
    <div class="lightbox-box">
        <button class="lightbox-close" id="btnLightboxClose" type="button">×</button>
        <img id="lightboxImg" class="lightbox-img" src="" alt="Ảnh phóng to">
    </div>
</div>

<!-- Toast thông báo trạng thái thao tác (thành công / lỗi) -->
<div class="toast" id="toast">
    <div class="icon icon-toast-success"></div>
    <span id="toastMsg">Thao tác thành công</span>
</div>

<script>
    // Danh sách đơn thuốc hiện đang hiển thị trên bảng (dữ liệu của trang hiện tại)
    let donThuocList = [];
    // Lưu chi tiết đơn thuốc đang được xem trong modal (để dùng lại khi cần)
    let currentDetail = null;
    // Đối tượng lưu trạng thái bộ lọc / tìm kiếm / phân trang hiện tại của trang
    let state = {
        search: '',       // Từ khóa tìm kiếm hiện tại
        status: 'all',    // Trạng thái đang lọc: all / CHO_DUYET / DA_DUYET / TU_CHOI
        page: 1,          // Trang hiện tại
        pageSize: 8,      // Số dòng hiển thị mỗi trang
        totalItems: 0,    // Tổng số bản ghi trả về từ server (dùng để tính số trang)
        activeId: null    // ID đơn thuốc đang được thao tác (xem chi tiết / từ chối)
    };

    // Đường dẫn gốc tới controller duyệt đơn, dùng để build các URL gọi API
    const baseUrl = '<?php echo URLROOT; ?>/duocSi/duyetDon';

    /**
     * Định dạng một giá trị ngày giờ (chuỗi/ISO) sang định dạng hiển thị tiếng Việt.
     * @param value Giá trị ngày giờ đầu vào (có thể null/rỗng hoặc không hợp lệ)
     * @return Chuỗi ngày giờ đã định dạng, hoặc '—' nếu rỗng, hoặc giá trị gốc nếu không parse được
     */
    function dinhDangNgay(value) {
        if (!value) return '—';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    /**
     * Render toàn bộ bảng danh sách đơn thuốc (donThuocList) ra HTML,
     * bao gồm badge trạng thái và các nút thao tác tương ứng (duyệt/từ chối/xem),
     * đồng thời cập nhật khu vực trạng thái rỗng và phân trang.
     */
    function hienThiBang() {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');

        // Nếu không có đơn thuốc nào, hiển thị thông báo rỗng và xóa phân trang
        if (!donThuocList.length) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = donThuocList.map(item => {
            let statusBadge = '';   // HTML badge hiển thị trạng thái đơn
            let actionButtons = ''; // HTML các nút thao tác (chỉ có khi đang chờ duyệt)

            // Xác định badge và nút thao tác tương ứng theo trạng thái đơn thuốc
            if (item.trangThai === 'CHO_DUYET') {
                statusBadge = '<span class="badge badge-pending">Chờ duyệt</span>';
                actionButtons = `
                    <button class="action-btn approve" onclick="duyetDonLe(${item.idDonThuoc})">Duyệt đơn</button>
                    <button class="action-btn reject" onclick="moModalLyDoTuChoi(${item.idDonThuoc})">Từ chối</button>
                `;
            } else if (item.trangThai === 'DA_DUYET') {
                statusBadge = '<span class="badge badge-approved">Đã duyệt</span>';
            } else {
                statusBadge = '<span class="badge badge-rejected">Từ chối</span>';
            }

            return `
                <tr>
                    <td class="cell-strong cell-mono">REQ-${item.idDonThuoc}</td>
                    <td><div class="cell-strong">${item.tenKhachHang || '—'}</div></td>
                    <td>${dinhDangNgay(item.ngayGui)}</td>
                    <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${item.ghiChu || '—'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="actions-cell" style="justify-content:flex-end;">
                            <button class="action-btn view" onclick="moModalChiTiet(${item.idDonThuoc})">Xem chi tiết đơn</button>
                            ${actionButtons}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        hienThiPhanTrang(state.totalItems);
    }

    /**
     * Render các nút phân trang dựa trên tổng số bản ghi và pageSize hiện tại.
     * @param totalItems Tổng số bản ghi (đơn thuốc) khớp với bộ lọc hiện tại
     */
    function hienThiPhanTrang(totalItems) {
        const totalPages = Math.ceil(totalItems / state.pageSize); // Tổng số trang cần hiển thị
        const box = document.getElementById('pagination');
        // Không cần hiển thị phân trang nếu chỉ có 1 trang hoặc không có trang nào
        if (totalPages <= 1) {
            box.innerHTML = '';
            return;
        }

        let html = ''; // Chuỗi HTML tích lũy các nút số trang
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${i === state.page ? 'active' : ''}" onclick="chuyenTrang(${i})">${i}</button>`;
        }
        box.innerHTML = html;
    }

    /**
     * Chuyển sang trang được chọn và tải lại dữ liệu tương ứng.
     * @param page Số trang muốn chuyển tới
     */
    function chuyenTrang(page) {
        state.page = page;
        taiDuLieu();
    }

    /**
     * Hiển thị (mở) một modal theo id bằng cách bỏ class 'hidden'.
     * @param id ID phần tử modal cần mở
     */
    function moModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    /**
     * Ẩn (đóng) một modal theo id bằng cách thêm lại class 'hidden'.
     * @param id ID phần tử modal cần đóng
     */
    function dongModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // Gắn sự kiện đóng modal cho tất cả các nút có thuộc tính data-close
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => dongModal(btn.dataset.close));
    });

    /**
     * Cập nhật số lượng hiển thị trên badge (ví dụ ở sidebar) thể hiện
     * số đơn thuốc đang chờ xử lý.
     * @param count Số lượng đơn đang ở trạng thái chờ duyệt
     */
    function capNhatBadgeChoXuLy(count) {
        const badge = document.getElementById('sidebarBadge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-flex' : 'none'; // Ẩn badge khi count = 0
        }
    }

    /**
     * Gọi API lấy danh sách đơn thuốc theo bộ lọc/tìm kiếm/phân trang hiện tại (state),
     * sau đó cập nhật donThuocList, tổng số bản ghi, badge chờ xử lý và render lại bảng.
     */
    function taiDuLieu() {
        // Tham số truy vấn gửi kèm request: từ khóa tìm kiếm, trạng thái lọc, số trang
        const params = new URLSearchParams({
            search: state.search,
            status: state.status,
            page: state.page
        });

        fetch(`${baseUrl}/layDanhSach?${params.toString()}`)
            .then(response => response.json())
            .then(result => {
                if (!result.status) {
                    hienThongBao(result.message || 'Không thể tải danh sách đơn thuốc.');
                    return;
                }

                donThuocList = result.data || [];                       // Danh sách đơn thuốc của trang hiện tại
                state.totalItems = result.total || 0;                    // Tổng số bản ghi khớp bộ lọc (để tính phân trang)
                capNhatBadgeChoXuLy(result.pendingCount || 0);           // Số đơn đang chờ duyệt (cập nhật badge)
                hienThiBang();
            })
            .catch(() => {
                hienThongBao('Không thể kết nối máy chủ.');
            });
    }

    /**
     * Đổ dữ liệu chi tiết của một đơn thuốc (item) vào modal xem chi tiết,
     * bao gồm thông tin khách hàng, danh sách thuốc, ảnh toa thuốc và
     * các nút thao tác phù hợp với trạng thái đơn.
     * @param item Đối tượng dữ liệu chi tiết đơn thuốc trả về từ API layChiTiet
     */
    function dienDuLieuModalChiTiet(item) {
        currentDetail = item;          // Lưu lại chi tiết đơn đang xem để tái sử dụng nếu cần
        state.activeId = item.idDonThuoc; // Ghi nhận ID đơn đang thao tác (dùng khi duyệt/từ chối từ modal)

        document.getElementById('view_maDon').textContent = `REQ-${item.idDonThuoc}`;
        document.getElementById('view_tenKhach').textContent = item.tenKhachHang || '—';
        document.getElementById('view_ngayGui').textContent = dinhDangNgay(item.ngayGui);
        document.getElementById('view_ghiChu').textContent = item.ghiChu || 'Không có';

        // Xác định URL ảnh toa thuốc, dùng ảnh placeholder nếu đơn không có ảnh
        const imageUrl = item.hinhAnhDonThuoc
            ? `<?php echo URLROOT; ?>/${item.hinhAnhDonThuoc}`
            : 'https://placehold.co/600x480/e8f5ee/2d7a4f?text=No+Image';
        const img = document.getElementById('view_hinhAnhToa');
        img.src = imageUrl;
        document.getElementById('lightboxImg').src = imageUrl; // Đồng bộ ảnh cho lightbox phóng to

        const medTbody = document.getElementById('medTableBody');
        // Render danh sách thuốc trong đơn, hoặc thông báo nếu đơn không có thuốc nào
        if (item.chiTiet && item.chiTiet.length) {
            medTbody.innerHTML = item.chiTiet.map(med => `
                <tr>
                    <td style="text-align:center;"><img src="https://placehold.co/64x64/e8f5ee/2d7a4f?text=💊" class="med-thumb" alt="${med.tenThuoc}"></td>
                    <td><strong>${med.tenThuoc}</strong></td>
                    <td><span class="cell-sub">${med.lieuDung || '—'}</span></td>
                    <td style="text-align:center;" class="cell-strong">${med.soLuong}</td>
                </tr>
            `).join('');
        } else {
            medTbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#7c869a;">Không có thuốc trong đơn.</td></tr>';
        }

        // Chân modal: chỉ hiển thị nút Duyệt/Từ chối khi đơn đang ở trạng thái chờ duyệt
        const foot = document.getElementById('modalDetailFoot');
        if (item.trangThai === 'CHO_DUYET') {
            foot.innerHTML = `
                <button class="btn btn-ghost" onclick="dongModal('modalDetail')">Đóng</button>
                <button class="btn btn-primary" onclick="duyetTuChiTiet()">Duyệt yêu cầu này</button>
                <button class="btn btn-primary" style="background:var(--red-600); border-color:var(--red-700);" onclick="tuChoiTuChiTiet()">Từ chối</button>
            `;
        } else {
            foot.innerHTML = '<button class="btn btn-ghost" onclick="dongModal(\'modalDetail\')">Đóng</button>';
        }
    }

    /**
     * Mở modal xem chi tiết cho một đơn thuốc: gọi API lấy chi tiết theo id,
     * đổ dữ liệu vào modal rồi hiển thị modal.
     * @param id ID đơn thuốc cần xem chi tiết
     */
    function moModalChiTiet(id) {
        fetch(`${baseUrl}/layChiTiet/${id}`)
            .then(response => response.json())
            .then(result => {
                if (!result.status) {
                    hienThongBao(result.message || 'Không thể mở chi tiết đơn.');
                    return;
                }
                dienDuLieuModalChiTiet(result.data);
                moModal('modalDetail');
            })
            .catch(() => hienThongBao('Không thể tải chi tiết đơn.'));
    }

    /**
     * Duyệt một đơn thuốc lẻ (sau khi xác nhận), gọi API duyệt theo id
     * rồi tải lại danh sách nếu thành công.
     * @param id ID đơn thuốc cần duyệt
     */
    function duyetDonLe(id) {
        if (!confirm('Xác nhận duyệt đơn thuốc này?')) return; // Yêu cầu xác nhận trước khi duyệt
        fetch(`${baseUrl}/duyet/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    hienThongBao(result.message || 'Đã duyệt đơn thuốc.');
                    taiDuLieu();
                } else {
                    hienThongBao(result.message || 'Không thể duyệt đơn thuốc.');
                }
            })
            .catch(() => hienThongBao('Không thể duyệt đơn thuốc.'));
    }

    /**
     * Duyệt đơn thuốc đang được xem trong modal chi tiết (dùng state.activeId),
     * sau đó đóng modal chi tiết.
     */
    function duyetTuChiTiet() {
        duyetDonLe(state.activeId);
        dongModal('modalDetail');
    }

    // Sự kiện: Duyệt nhanh toàn bộ các đơn thuốc đang ở trạng thái chờ duyệt
    document.getElementById('btnApproveAll').addEventListener('click', () => {
        // Lọc ra các đơn đang chờ duyệt trong danh sách hiện tại
        const pendingUnits = donThuocList.filter(d => d.trangThai === 'CHO_DUYET');
        if (!pendingUnits.length) {
            alert('Không có yêu cầu nào đang chờ duyệt.');
            return;
        }
        if (confirm(`Xác nhận duyệt nhanh toàn bộ ${pendingUnits.length} yêu cầu đang chờ?`)) {
            fetch(`${baseUrl}/duyetTatCa`, { method: 'POST' })
                .then(response => response.json())
                .then(result => {
                    if (result.status) {
                        hienThongBao(result.message || 'Đã duyệt toàn bộ đơn thuốc.');
                        taiDuLieu();
                    } else {
                        hienThongBao(result.message || 'Không thể duyệt tất cả.');
                    }
                })
                .catch(() => hienThongBao('Không thể duyệt tất cả.'));
        }
    });

    /**
     * Mở modal nhập lý do từ chối cho một đơn thuốc cụ thể, reset nội dung
     * ô nhập lý do trước khi hiển thị.
     * @param id ID đơn thuốc cần từ chối
     */
    function moModalLyDoTuChoi(id) {
        state.activeId = id; // Ghi nhận đơn đang thao tác để dùng khi xác nhận từ chối
        document.getElementById('txtRejectReason').value = '';
        moModal('modalRejectReason');
    }

    /**
     * Chuyển từ modal xem chi tiết sang modal nhập lý do từ chối
     * cho đơn thuốc đang được xem (state.activeId).
     */
    function tuChoiTuChiTiet() {
        dongModal('modalDetail');
        moModalLyDoTuChoi(state.activeId);
    }

    // Sự kiện: Xác nhận từ chối đơn thuốc với lý do đã nhập
    document.getElementById('btnConfirmReject').addEventListener('click', () => {
        const reason = document.getElementById('txtRejectReason').value.trim(); // Lý do từ chối do Dược sĩ nhập
        if (!reason) {
            alert('Vui lòng nhập lý do từ chối cụ thể.');
            return;
        }

        fetch(`${baseUrl}/tuChoi/${state.activeId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body: `reason=${encodeURIComponent(reason)}`
        })
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    dongModal('modalRejectReason');
                    hienThongBao(result.message || 'Đã từ chối đơn thuốc.');
                    taiDuLieu();
                } else {
                    hienThongBao(result.message || 'Không thể từ chối đơn thuốc.');
                }
            })
            .catch(() => hienThongBao('Không thể từ chối đơn thuốc.'));
    });

    /**
     * Hiển thị thông báo dạng toast ở góc màn hình trong 3 giây.
     * @param msg Nội dung thông báo cần hiển thị
     */
    function hienThongBao(msg) {
        const toast = document.getElementById('toast');
        const msgSpan = document.getElementById('toastMsg');
        if (msgSpan) {
            msgSpan.textContent = msg;
        } else {
            toast.querySelector('span').textContent = msg;
        }
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000); // Tự động ẩn toast sau 3 giây
    }

    // Sự kiện: gõ từ khóa tìm kiếm -> cập nhật state và tải lại dữ liệu từ trang 1
    document.getElementById('searchInput').addEventListener('input', (e) => {
        state.search = e.target.value;
        state.page = 1;
        taiDuLieu();
    });

    // Sự kiện: đổi bộ lọc trạng thái -> cập nhật state và tải lại dữ liệu từ trang 1
    document.getElementById('filterStatus').addEventListener('change', (e) => {
        state.status = e.target.value;
        state.page = 1;
        taiDuLieu();
    });

    // Sự kiện: đặt lại bộ lọc/tìm kiếm về mặc định rồi tải lại dữ liệu
    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatus').value = 'all';
        state.search = '';
        state.status = 'all';
        state.page = 1;
        taiDuLieu();
    });

    // Nút đăng xuất (nếu tồn tại trên layout chứa trang này)
    const logoutButton = document.getElementById('btnLogout');
    if (logoutButton) {
        logoutButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Xác nhận đăng xuất khỏi hệ thống PharmaCare?')) {
                window.location.href = '<?php echo URLROOT; ?>/khachHang/xacThuc/dangXuat';
            }
        });
    }

    // Tham chiếu tới overlay và ảnh của lightbox phóng to ảnh toa thuốc
    const lightboxOverlay = document.getElementById('lightboxOverlay');
    const lightboxImg = document.getElementById('lightboxImg');

    // Sự kiện: click vào ảnh toa thuốc trong modal chi tiết -> mở lightbox phóng to
    document.getElementById('view_hinhAnhToa').addEventListener('click', function() {
        lightboxImg.src = this.src;
        lightboxOverlay.classList.add('show');
    });

    /**
     * Đóng lightbox phóng to ảnh.
     */
    function dongLightbox() {
        lightboxOverlay.classList.remove('show');
    }

    // Sự kiện: nhấn phím Escape -> đóng tất cả modal và lightbox đang mở
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dongModal('modalDetail');
            dongModal('modalRejectReason');
            dongLightbox();
        }
    });

    // Sự kiện: click nút đóng hoặc click ra ngoài vùng ảnh -> đóng lightbox
    document.getElementById('btnLightboxClose').addEventListener('click', dongLightbox);
    lightboxOverlay.addEventListener('click', function(e) {
        if (e.target === lightboxOverlay) {
            dongLightbox();
        }
    });

    // Tải dữ liệu danh sách đơn thuốc ngay khi trang được load
    taiDuLieu();
</script>
