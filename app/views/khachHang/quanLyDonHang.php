<?php
/**
 * View: Quản lý đơn hàng
 * Chức năng: Hiển thị danh sách đơn hàng của khách hàng dưới dạng bảng có thể
 * lọc theo trạng thái (tab trạng thái), tìm kiếm theo mã đơn hàng, phân trang;
 * cho phép xem chi tiết đơn hàng qua popup (tải dữ liệu bằng AJAX, không reload
 * trang) và hủy đơn hàng (kèm chọn lý do hủy) khi đơn còn ở trạng thái chờ xác nhận.
 * Dữ liệu đầu vào: $donHangList (mảng danh sách đơn hàng của khách hàng, gồm
 * idDonHang, ngayDat, tongTien, trangThai, lyDoHuy...) truyền từ Controller xuống View.
 */
?>
<!-- 
  VIEW trang Quản lý đơn hàng — chỉ phần main content (không navbar/topbar/drawer/footer,
  bạn đã tự làm phần đó).
  Có nhúng CSDL cho bảng DonHang (idDonHang, ngayDat, tongTien, trangThai, lyDoHuy).
  Ô tìm kiếm mã đơn hàng đã chuyển vào trong card nội dung (không phụ thuộc navbar nữa).
-->
    <div class="wrap">
        <div class="card">
            <!-- Ô tìm kiếm riêng cho trang Quản lý đơn hàng (theo mã đơn hàng),
                 KHÔNG dùng chung ô tìm kiếm sản phẩm ở navbar để tránh phụ thuộc/lỗi -->
            <div class="content-search-bar" style="margin-bottom:16px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Tìm kiếm theo mã đơn hàng...">
            </div>

            <!-- Các tab lọc theo trạng thái đơn hàng (Tất cả, Chờ xác nhận...), được render động bằng hàm hienThiTabTrangThai() -->
            <div class="status-tabs" id="statusTabs"></div>

            <table style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="width: 18%;">Mã đơn hàng</th>
                        <th style="width: 27%;">Ngày đặt</th>
                        <th style="width: 20%;">Tổng tiền</th>
                        <th style="width: 20%;">Trạng thái</th>
                        <th style="width: 15%; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <!-- Nội dung danh sách đơn hàng, được render động bằng hàm hienThiBang() -->
                <tbody id="orderTableBody"></tbody>
            </table>

            <!-- Khu vực phân trang, được render động bằng hàm hienThiPhanTrang() -->
            <div class="pagination-container" id="paginationControls"></div>
        </div>
    </div>

    <!-- Modal huỷ đơn hàng -->
    <div class="modal-overlay" id="cancelModal">
        <div class="modal-box">
            <h3><i class="fa-solid fa-triangle-exclamation"></i> Huỷ đơn hàng</h3>
            <p class="modal-desc">Vui lòng chọn lý do huỷ đơn hàng này để hệ thống ghi nhận.</p>
            <!-- Danh sách các lý do hủy đơn để chọn, được render động bằng hàm hienThiLyDo() -->
            <div class="reason-options" id="reasonOptions"></div>
            <div class="modal-actions">
                <button class="btn-close" onclick="dongModalHuy()">Đóng</button>
                <button class="btn-confirm" onclick="xacNhanHuyDon()">Xác nhận huỷ</button>
            </div>
        </div>
    </div>

    <!-- Modal/Popup CHI TIẾT ĐƠN HÀNG - hiện đè lên trang, KHÔNG reload trang.
         Nội dung bên trong #dhdBody được JS render bằng dữ liệu lấy qua AJAX
         từ QuanLyDonHangController::chiTietAjax() -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal-box">
            <button class="dhd-close-x" onclick="dongModalChiTiet()" aria-label="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div id="dhdBody">
                <div class="dhd-loading"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải chi tiết đơn hàng...</div>
            </div>
        </div>
    </div>

<script>
        // statusMeta: bảng tra cứu nhãn hiển thị (label) và class CSS (cls) tương ứng
        // với từng mã trạng thái đơn hàng trong CSDL (bảng DonHang.trangThai)
        const statusMeta = {
            CHO_XAC_NHAN: { label: "Chờ xác nhận", cls: "st-cho_xac_nhan" },
            DA_XAC_NHAN: { label: "Đã xác nhận", cls: "st-dang_giao" },
            DANG_GIAO: { label: "Đang giao", cls: "st-dang_giao" },
            DA_GIAO: { label: "Đã giao", cls: "st-da_giao" },
            DA_HUY: { label: "Đã huỷ", cls: "st-da_huy" }
        };
        // Đã thêm trạng thái DA_XAC_NHAN cho khớp với ENUM trangThai trong bảng DonHang
        // (bản mock gốc thiếu trạng thái này). Đồng thời sửa lỗi tên class "st-da_huy"
        // (bản gốc bị lỗi gõ thành "st-da_實現_huy" không khớp với CSS).

        // Dữ liệu đơn hàng thật lấy từ CSDL (bảng DonHang) qua Controller
        let orders = <?php echo json_encode($donHangList, JSON_UNESCAPED_UNICODE); ?>;

        /**
         * Định dạng id đơn hàng (số) thành mã đơn hàng hiển thị dạng "DHxxxxx".
         * @param {number} id - Id đơn hàng (idDonHang) trong CSDL
         * @returns {string} Mã đơn hàng đã định dạng, ví dụ: DH00012
         */
        function dinhDangMaDon(id) {
            return 'DH' + String(id).padStart(5, '0');
        }

        // currentFilter: trạng thái đơn hàng đang được lọc trên tab ("all" = tất cả)
        let currentFilter = "all";
        // currentPage: trang hiện tại đang hiển thị trong bảng đơn hàng
        let currentPage = 1;
        // itemsPerPage: số đơn hàng hiển thị tối đa trên mỗi trang
        const itemsPerPage = 8;
        // cancelTargetId: id đơn hàng đang được thao tác hủy trong modal hủy đơn
        let cancelTargetId = null;
        // selectedReason: lý do hủy đơn đang được chọn trong modal hủy đơn
        let selectedReason = "";
        let currentDetailId = null; // id đơn hàng đang mở trong popup chi tiết (nếu có)

        // cancelReasons: danh sách các lý do hủy đơn hàng để người dùng lựa chọn
        const cancelReasons = [
            "Tôi muốn đổi sản phẩm khác / thêm thuốc",
            "Tìm thấy giá tốt hơn ở nhà thuốc khác",
            "Sai sót thông tin giao nhận hàng",
            "Không còn nhu cầu mua nữa"
        ];

        /**
         * Render các tab lọc theo trạng thái đơn hàng, kèm số lượng đơn hàng
         * tương ứng với mỗi trạng thái.
         */
        function hienThiTabTrangThai() {
            // counts: số lượng đơn hàng theo từng trạng thái (bao gồm "all" = tổng tất cả)
            const counts = { all: orders.length };
            Object.keys(statusMeta).forEach(k => counts[k] = orders.filter(o => o.status === k).length);
            const tabs = [
                { key: "all", label: "Tất cả" },
                { key: "CHO_XAC_NHAN", label: "Chờ xác nhận" },
                { key: "DA_XAC_NHAN", label: "Đã xác nhận" },
                { key: "DANG_GIAO", label: "Đang giao" },
                { key: "DA_GIAO", label: "Đã giao" },
                { key: "DA_HUY", label: "Đã huỷ" }
            ];
            document.getElementById('statusTabs').innerHTML = tabs.map(t => `
    <button class="status-tab ${currentFilter === t.key ? 'active' : ''}" onclick="locTheoTrangThai('${t.key}')">
      ${t.label} <span class="count">(${counts[t.key]})</span>
    </button>
  `).join('');
        }

        /**
         * Xử lý khi người dùng chọn 1 tab trạng thái: cập nhật bộ lọc, quay về trang 1
         * và render lại tab + bảng đơn hàng.
         * @param {string} status - Mã trạng thái được chọn ("all" hoặc 1 trong statusMeta)
         */
        function locTheoTrangThai(status) {
            currentFilter = status;
            currentPage = 1;
            hienThiTabTrangThai();
            hienThiBang();
        }

        /**
         * Lọc danh sách đơn hàng theo trạng thái đang chọn và từ khóa tìm kiếm mã đơn,
         * sau đó phân trang và render lại bảng cùng khu vực phân trang.
         */
        function hienThiBang() {
            // search: từ khóa tìm kiếm mã đơn hàng, đã chuẩn hóa chữ thường
            const search = document.getElementById('searchInput').value.trim().toLowerCase();
            // filtered: danh sách đơn hàng sau khi áp dụng lọc theo trạng thái + tìm kiếm
            let filtered = orders.filter(o => {
                const matchesStatus = currentFilter === "all" || o.status === currentFilter;
                const matchesSearch = !search || dinhDangMaDon(o.id).toLowerCase().includes(search);
                return matchesStatus && matchesSearch;
            });

            // totalPages: tổng số trang dựa trên số đơn hàng sau lọc
            const totalPages = Math.ceil(filtered.length / itemsPerPage);
            // startIndex: vị trí bắt đầu lấy đơn hàng cho trang hiện tại
            const startIndex = (currentPage - 1) * itemsPerPage;
            const paginated = filtered.slice(startIndex, startIndex + itemsPerPage);

            const tbody = document.getElementById('orderTableBody');
            if (paginated.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--muted2); padding: 40px 0;">Không có đơn hàng nào phù hợp.</td></tr>`;
                document.getElementById('paginationControls').innerHTML = '';
                return;
            }

            // Render từng dòng đơn hàng: mã đơn, ngày đặt, tổng tiền, trạng thái,
            // và nút hủy đơn (chỉ bật khi trạng thái là CHO_XAC_NHAN)
            tbody.innerHTML = paginated.map(o => `
    <tr onclick="chuyenDenChiTiet(${o.id})">
      <td class="order-code">#${dinhDangMaDon(o.id)}</td>
      <td>${o.date}</td>
      <td class="amount">${Number(o.total).toLocaleString('vi-VN')}đ</td>
      <td><span class="status-badge ${statusMeta[o.status].cls}"><span class="dot"></span>${statusMeta[o.status].label}</span></td>
      <td style="text-align: center;">
        <button class="btn-cancel-action" ${o.status !== 'CHO_XAC_NHAN' ? 'disabled' : ''} onclick="moModalHuy(${o.id}, event)">
          <i class="fa-solid fa-rectangle-xmark"></i> Hủy đơn
        </button>
      </td>
    </tr>
  `).join('');

            hienThiPhanTrang(totalPages, filtered.length);
        }

        /**
         * Render khu vực phân trang cho bảng đơn hàng dựa trên tổng số trang và tổng số đơn hàng.
         * @param {number} totalPages - Tổng số trang sau khi đã lọc dữ liệu
         * @param {number} totalItems - Tổng số đơn hàng sau khi đã lọc dữ liệu
         */
        function hienThiPhanTrang(totalPages, totalItems) {
            const controls = document.getElementById('paginationControls');
            if (totalItems <= 8) {
                controls.innerHTML = '';
                return;
            }
            let html = `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="chuyenTrang(${currentPage - 1})"><i class="fa-solid fa-angle-left"></i></button>`;
            for (let i = 1; i <= totalPages; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="chuyenTrang(${i})">${i}</button>`;
            }
            html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="chuyenTrang(${currentPage + 1})"><i class="fa-solid fa-angle-right"></i></button>`;
            controls.innerHTML = html;
        }

        /**
         * Chuyển sang trang đơn hàng được chỉ định trong bảng.
         * @param {number} page - Số trang cần chuyển đến
         */
        function chuyenTrang(page) { currentPage = page; hienThiBang(); }

        // ĐÃ SỬA THEO YÊU CẦU: không chuyển trang / không reload nữa.
        // Bấm vào 1 dòng đơn hàng -> mở POPUP chi tiết, lấy dữ liệu qua AJAX
        // từ QuanLyDonHangController::chiTietAjax($id) (trả JSON).
        /**
         * Xử lý khi người dùng bấm vào 1 dòng đơn hàng: mở popup chi tiết đơn hàng đó.
         * @param {number} id - Id đơn hàng cần xem chi tiết
         */
        function chuyenDenChiTiet(id) { moModalChiTiet(id); }

        /**
         * Mở popup chi tiết đơn hàng và tải dữ liệu chi tiết bằng AJAX
         * từ QuanLyDonHangController::chiTietAjax($id).
         * @param {number} id - Id đơn hàng cần xem chi tiết
         */
        function moModalChiTiet(id) {
            currentDetailId = id;
            document.getElementById('dhdBody').innerHTML = `<div class="dhd-loading"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải chi tiết đơn hàng...</div>`;
            document.getElementById('detailModal').classList.add('show');

            fetch(`<?php echo URLROOT; ?>/QuanLyDonHang/chiTietAjax/${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        // Lấy dữ liệu chi tiết thành công -> render nội dung popup
                        hienThiModalChiTiet(res.donHangInfo, res.sanPhamList);
                    } else {
                        // Server trả về lỗi nghiệp vụ (VD: đơn hàng không tồn tại)
                        document.getElementById('dhdBody').innerHTML =
                            `<div class="dhd-loading">${res.message || 'Không tải được chi tiết đơn hàng.'}</div>`;
                    }
                })
                .catch(() => {
                    document.getElementById('dhdBody').innerHTML =
                        `<div class="dhd-loading">Có lỗi xảy ra, vui lòng thử lại.</div>`;
                });
        }

        /**
         * Đóng popup chi tiết đơn hàng và reset id đơn hàng đang mở.
         */
        function dongModalChiTiet() {
            document.getElementById('detailModal').classList.remove('show');
            currentDetailId = null;
        }

        // Render nội dung popup chi tiết đơn hàng (tương đương views/khachHang/chiTietDonHang.php,
        // nhưng render bằng JS để không phải load lại trang)
        /**
         * Render toàn bộ nội dung popup chi tiết đơn hàng: tiến trình đơn hàng (timeline),
         * danh sách thuốc đã đặt, thông tin thanh toán và thông tin giao nhận hàng.
         * @param {Object} donHangInfo - Thông tin tổng quan của đơn hàng (idDonHang, trangThai, tongTien...)
         * @param {Array} sanPhamList - Danh sách các thuốc trong đơn hàng (name, qty, price, lineTotal)
         */
        function hienThiModalChiTiet(donHangInfo, sanPhamList) {
            // trangThai: trạng thái hiện tại của đơn hàng
            const trangThai = donHangInfo.trangThai;
            // meta: thông tin nhãn hiển thị + class CSS tương ứng với trạng thái đơn hàng
            const meta = statusMeta[trangThai] || { label: trangThai, cls: '' };
            // phiVanChuyen: phí vận chuyển của đơn hàng, mặc định 15.000đ nếu server không trả về
            const phiVanChuyen = donHangInfo.phiVanChuyen != null ? Number(donHangInfo.phiVanChuyen) : 15000;
            // tamTinh: tổng tiền thuốc (chưa gồm phí vận chuyển), cộng dồn từ thành tiền từng sản phẩm
            const tamTinh = sanPhamList.reduce((sum, sp) => sum + Number(sp.lineTotal), 0);
            // tongTien: tổng tiền thanh toán cuối cùng của đơn hàng
            const tongTien = donHangInfo.tongTien != null ? Number(donHangInfo.tongTien) : (tamTinh + phiVanChuyen);
            // money: hàm rút gọn để định dạng số tiền theo chuẩn VNĐ
            const money = n => Number(n).toLocaleString('vi-VN') + 'đ';

            // Timeline
            // timelineHtml: chuỗi HTML thể hiện tiến trình đơn hàng dạng các bước (mốc thời gian)
            let timelineHtml = '';
            if (trangThai === 'DA_HUY') {
                timelineHtml = `
                    <div class="tl-step done">
                        <div class="tl-line"></div>
                        <div class="tl-dot"><i class="fa-solid fa-file-invoice"></i></div>
                        <div class="tl-label">Tạo đơn</div>
                    </div>
                    <div class="tl-step cancelled">
                        <div class="tl-line"></div>
                        <div class="tl-dot"><i class="fa-solid fa-xmark"></i></div>
                        <div class="tl-label">Đã huỷ đơn</div>
                    </div>`;
            } else {
                // steps: danh sách các mốc tiến trình chuẩn của 1 đơn hàng chưa bị hủy
                const steps = [
                    { key: 'CHO_XAC_NHAN', label: 'Chờ xác nhận', icon: 'fa-clipboard-check' },
                    { key: 'DA_XAC_NHAN', label: 'Đã xác nhận', icon: 'fa-square-check' },
                    { key: 'DANG_GIAO', label: 'Đang giao', icon: 'fa-truck-fast' },
                    { key: 'DA_GIAO', label: 'Đã giao thuốc', icon: 'fa-circle-check' }
                ];
                // currentIndex: vị trí của trạng thái hiện tại trong danh sách steps
                const currentIndex = steps.findIndex(s => s.key === trangThai);
                timelineHtml = steps.map((s, i) => {
                    // cls: trạng thái hiển thị của từng mốc - đã qua (done), đang ở (current), hoặc sắp tới (upcoming)
                    let cls = 'upcoming';
                    if (currentIndex !== -1) {
                        if (i < currentIndex) cls = 'done';
                        else if (i === currentIndex) cls = 'current';
                    }
                    return `
                    <div class="tl-step ${cls}">
                        <div class="tl-line"></div>
                        <div class="tl-dot"><i class="fa-solid ${s.icon}"></i></div>
                        <div class="tl-label">${s.label}</div>
                    </div>`;
                }).join('');
            }

            // cancelledBanner: băng rôn cảnh báo hiển thị khi đơn hàng đã bị hủy, kèm lý do hủy (nếu có)
            const cancelledBanner = trangThai === 'DA_HUY'
                ? `<div class="cancelled-banner"><i class="fa-solid fa-circle-exclamation"></i>
                     <div><strong>Đơn hàng này đã bị huỷ${donHangInfo.lyDoHuy ? ' — Lý do: ' + donHangInfo.lyDoHuy : ''}</strong></div>
                   </div>`
                : '';

            // cancelBtn: nút hủy đơn hàng, chỉ hiển thị khi đơn còn ở trạng thái chờ xác nhận
            const cancelBtn = trangThai === 'CHO_XAC_NHAN'
                ? `<button class="btn-cancel-order" onclick="moModalHuy(${donHangInfo.idDonHang}, event)">
                       <i class="fa-solid fa-rectangle-xmark"></i> Huỷ đơn hàng
                   </button>`
                : '';

            // rowsHtml: chuỗi HTML các dòng thuốc trong bảng chi tiết đơn hàng
            const rowsHtml = sanPhamList.map(sp => `
                <tr>
                    <td><strong>${sp.name}</strong></td>
                    <td class="num">${sp.qty}</td>
                    <td class="num">${money(sp.price)}</td>
                    <td class="num"><strong>${money(sp.lineTotal)}</strong></td>
                </tr>`).join('');

            document.getElementById('dhdBody').innerHTML = `
                <div class="detail-header">
                    <h3>Chi tiết đơn hàng #${dinhDangMaDon(donHangInfo.idDonHang)}</h3>
                    ${cancelBtn}
                </div>
                ${cancelledBanner}

                <div class="detail-layout">
                    <div>
                        <div class="card">
                            <div class="section-title"><i class="fa-solid fa-circle-nodes"></i> Tiến trình đơn hàng</div>
                            <div class="timeline">${timelineHtml}</div>
                        </div>

                        <div class="card">
                            <div class="section-title"><i class="fa-solid fa-kit-medical"></i> Danh sách thuốc đã đặt</div>
                            <table class="prod-table">
                                <thead>
                                    <tr>
                                        <th>Tên thuốc / Dược phẩm</th>
                                        <th class="num">Số lượng</th>
                                        <th class="num">Đơn giá</th>
                                        <th class="num">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>${rowsHtml}</tbody>
                            </table>
                            <div class="totals-box">
                                <div class="totals-row"><span>Tạm tính tiền thuốc</span><span>${money(tamTinh)}</span></div>
                                <div class="totals-row"><span>Phí vận chuyển</span><span>${money(phiVanChuyen)}</span></div>
                                <div class="totals-row grand"><span>Tổng tiền thanh toán</span><span class="val">${money(tongTien)}</span></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card">
                            <div class="section-title"><i class="fa-solid fa-credit-card"></i> Trạng thái & Thanh toán</div>
                            <div class="info-stack">
                                <div class="info-group">
                                    <div class="info-item">
                                        <div class="label">Phương thức thanh toán</div>
                                        <div class="value">${donHangInfo.phuongThucThanhToan || 'Thanh toán khi nhận hàng (COD)'}</div>
                                    </div>
                                </div>
                                <div class="info-group">
                                    <div class="info-item">
                                        <div class="label">Trạng thái đơn hàng hiện tại</div>
                                        <div style="margin-top: 8px;">
                                            <span class="status-badge ${meta.cls}"><span class="dot"></span>${meta.label}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="section-title"><i class="fa-solid fa-address-card"></i> Thông tin giao nhận hàng</div>
                            <div class="info-stack">
                                <div class="info-group">
                                    <div class="info-item">
                                        <div class="label">Người nhận hàng</div>
                                        <div class="value">${donHangInfo.tenNguoiNhan || '—'}</div>
                                    </div>
                                </div>
                                <div class="info-group">
                                    <div class="info-item">
                                        <div class="label">Số điện thoại</div>
                                        <div class="value">${donHangInfo.soDienThoai || '—'}</div>
                                    </div>
                                </div>
                                <div class="info-group">
                                    <div class="info-item">
                                        <div class="label">Địa chỉ nhận hàng</div>
                                        <div class="value">${donHangInfo.diaChiGiaoHang || '—'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Mở modal hủy đơn hàng cho 1 đơn hàng cụ thể, mặc định chọn sẵn lý do đầu tiên.
         * @param {number} id - Id đơn hàng cần hủy
         * @param {Event} event - Sự kiện click, dùng để chặn nổi bọt (tránh kích hoạt onclick của dòng bảng)
         */
        function moModalHuy(id, event) {
            event.stopPropagation();
            cancelTargetId = id;
            selectedReason = cancelReasons[0];
            hienThiLyDo();
            document.getElementById('cancelModal').classList.add('show');
        }
        /**
         * Đóng modal hủy đơn hàng.
         */
        function dongModalHuy() { document.getElementById('cancelModal').classList.remove('show'); }

        /**
         * Render danh sách các lựa chọn lý do hủy đơn trong modal, đánh dấu lý do đang được chọn.
         */
        function hienThiLyDo() {
            document.getElementById('reasonOptions').innerHTML = cancelReasons.map(r => `
    <div class="reason-option ${selectedReason === r ? 'selected' : ''}" onclick="chonLyDo('${r}')">
      <div class="reason-radio"></div>
      <span>${r}</span>
    </div>
  `).join('');
        }
        /**
         * Chọn 1 lý do hủy đơn hàng và render lại danh sách lựa chọn.
         * @param {string} r - Nội dung lý do hủy đơn được chọn
         */
        function chonLyDo(r) { selectedReason = r; hienThiLyDo(); }

        // Gọi API huỷ đơn thật xuống CSDL (bảng DonHang: trangThai + lyDoHuy)
        // ĐÃ SỬA: route đúng là Controller "QuanLyDonHang", action "huyDonHang"
        /**
         * Xác nhận hủy đơn hàng: gửi lý do hủy lên server để cập nhật trạng thái đơn hàng
         * (bảng DonHang: trangThai + lyDoHuy), sau đó đồng bộ lại giao diện.
         */
        function xacNhanHuyDon() {
            fetch(`<?php echo URLROOT; ?>/QuanLyDonHang/huyDonHang/${cancelTargetId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `lyDoHuy=${encodeURIComponent(selectedReason)}`
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        // Hủy thành công -> cập nhật trạng thái đơn hàng trong danh sách orders ở phía client
                        const o = orders.find(x => x.id === cancelTargetId);
                        if (o) o.status = "DA_HUY";
                        alert(`Đã hủy thành công đơn hàng #${dinhDangMaDon(cancelTargetId)}`);
                        dongModalHuy();
                        hienThiTabTrangThai();
                        hienThiBang();
                        // Nếu popup chi tiết đang mở đúng đơn hàng vừa huỷ -> tải lại để hiện trạng thái "Đã huỷ"
                        if (currentDetailId === cancelTargetId) {
                            moModalChiTiet(cancelTargetId);
                        }
                    } else {
                        alert(res.message || 'Huỷ đơn hàng thất bại, vui lòng thử lại.');
                    }
                })
                .catch(() => alert('Lỗi kết nối máy chủ.'));
        }

        // Lắng nghe sự kiện nhập từ khóa vào ô tìm kiếm mã đơn hàng: quay về trang 1 và lọc lại
        document.getElementById('searchInput').addEventListener('input', () => { currentPage = 1; hienThiBang(); });

        // Khởi tạo hiển thị ban đầu khi tải trang: render tab trạng thái và bảng đơn hàng
        hienThiTabTrangThai();
        hienThiBang();
</script>
    