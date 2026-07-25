<div class="toolbar">
    <div class="toolbar-search">
        <div class="icon icon-search"></div>
        <input type="text" id="searchInput" placeholder="Tìm theo mã đơn hàng hoặc tên khách hàng...">
    </div>
    <select class="filter-select" id="filterStatus">
        <option value="all">Tất cả đơn đóng gói</option>
        <option value="CHO_XAC_NHAN">Chờ xử lý (Mới đặt)</option>
        <option value="DA_XAC_NHAN">Chờ đóng gói (Đã xác nhận)</option>
        <option value="DANG_GIAO">Đã đóng gói hoàn tất (Đang giao)</option>
    </select>
    <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt hàng</th>
                    <th>Giá trị đơn</th>
                    <th>Tổng số thuốc</th>
                    <th>Trạng thái kho</th>
                    <th style="text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <div style="font-weight: 700; margin-top: 8px;">Không tìm thấy đơn hàng đóng gói yêu cầu</div>
    </div>
    <div class="pagination-bar">
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<style>
.pack-info-banner { background:#f8fafc; border-radius:12px; padding:16px 20px; margin-bottom:22px; }
.pack-info-row { display:flex; justify-content:space-between; gap:24px; flex-wrap:wrap; margin-bottom:8px; }
.pack-info-row:last-child { margin-bottom:0; }
.pack-info-label { font-weight:700; color:#0f172a; margin-right:4px; }
.pack-info-value { color:#334155; }
.pack-info-value.mono { font-family:'Courier New',monospace; }
.pack-section-label { font-size:12px; font-weight:700; letter-spacing:.04em; color:#64748b; text-transform:uppercase; margin-bottom:10px; }
.pack-table { width:100%; border-collapse:collapse; }
.pack-table thead th { text-align:left; font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:.03em; padding:8px 10px; border-bottom:1px solid #e2e8f0; }
.pack-table tbody td { padding:12px 10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.pack-item-img { width:44px; height:44px; border-radius:8px; object-fit:cover; background:#f1f5f9; display:block; }
.pack-item-name { font-weight:700; color:#0f172a; }
.pack-item-sub { font-size:12px; color:#94a3b8; margin-top:2px; }
.batch-suggestion-pill { display:inline-block; background:#dcfce7; color:#15803d; font-weight:700; font-size:13px; padding:4px 12px; border-radius:999px; white-space:nowrap; }
.pack-out-of-stock { color:#dc2626; font-weight:600; font-size:13px; }
.btn-danger { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; font-weight:700; }
.btn-danger:hover { background:#fee2e2; }
</style>

<div class="modal-overlay hidden" id="modalXemXacNhan">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2>Xem chi tiết &amp; Xác nhận đơn hàng</h2>
            </div>
            <button class="modal-close" data-close="modalXemXacNhan"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="pack-info-banner">
                <div class="pack-info-row">
                    <div><span class="pack-info-label">Mã đơn hàng:</span> <span class="pack-info-value mono" id="view_idDonHang">—</span></div>
                    <div><span class="pack-info-label">Tên khách hàng:</span> <span class="pack-info-value" id="view_hoTen">—</span></div>
                </div>
                <div class="pack-info-row">
                    <div><span class="pack-info-label">Địa chỉ giao hàng:</span> <span class="pack-info-value" id="view_diaChi">—</span></div>
                </div>
            </div>

            <div class="pack-section-label">Danh sách thuốc trong đơn</div>

            <table class="pack-table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên hoạt chất / Hàm lượng</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th style="text-align:center;">Tình trạng kho</th>
                    </tr>
                </thead>
                <tbody id="viewTableBody"></tbody>
            </table>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalXemXacNhan">Đóng lại</button>
            <button class="btn btn-danger" id="btnTuChoiDon">
                <i class="fa-solid fa-xmark"></i> Từ chối đơn
            </button>
            <button class="btn btn-primary" id="btnConfirmDon">
                <i class="fa-solid fa-check"></i> Xác nhận đơn hàng
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay hidden" id="modalPacking">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2>Phiếu chuẩn bị thuốc &amp; Đóng gói sản phẩm</h2>
            </div>
            <button class="modal-close" data-close="modalPacking"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="pack-info-banner">
                <div class="pack-info-row">
                    <div><span class="pack-info-label">Mã đơn hàng:</span> <span class="pack-info-value mono" id="ship_idDonHang">—</span></div>
                    <div><span class="pack-info-label">Tên khách hàng:</span> <span class="pack-info-value" id="ship_hoTen">—</span></div>
                </div>
                <div class="pack-info-row">
                    <div><span class="pack-info-label">Địa chỉ giao hàng:</span> <span class="pack-info-value" id="ship_diaChi">—</span></div>
                </div>
            </div>

            <div class="pack-section-label">Danh sách thuốc cần nhặt kho</div>

            <table class="pack-table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên hoạt chất / Hàm lượng</th>
                        <th>Số lượng</th>
                        <th>Gợi ý mã lô (FEFO)</th>
                        <th>Hạn dùng</th>
                        <th style="text-align:center;">Đã nhặt</th>
                    </tr>
                </thead>
                <tbody id="packTableBody"></tbody>
            </table>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalPacking">Hủy bỏ phiếu</button>
            <button class="btn btn-primary" id="btnConfirmPackComplete" disabled>
                <i class="fa-solid fa-truck"></i> Xác nhận đóng gói - Chọn giao hàng
            </button>
        </div>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fa-solid fa-circle-check"></i>
    <span>Đã xác nhận đóng gói và bàn giao vận chuyển!</span>
</div>

<script>
    // Dữ liệu đơn hàng cần đóng gói lấy THẬT từ CSDL qua DongGoiController::index()
    let allOrders = <?php echo json_encode($donHangKhoList, JSON_UNESCAPED_UNICODE); ?>;

    let state = {
        search: '',
        status: 'all',
        page: 1,
        pageSize: 5,
        activeId: null
    };

    function statusLabel(trangThai) {
        if (trangThai === 'DANG_GIAO') {
            return { text: 'Đã đóng gói (Đang giao)', cls: 'st-dang-giao' };
        }
        if (trangThai === 'CHO_XAC_NHAN') {
            return { text: 'Chờ xử lý (Mới đặt)', cls: 'st-cho-dong-goi' };
        }
        return { text: 'Chờ đóng gói', cls: 'st-cho-dong-goi' };
    }

    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    function fmtDate(str) {
        if (!str) return '—';
        const d = new Date(str.replace(' ', 'T'));
        if (isNaN(d)) return str;
        return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }

    function getFilteredOrders() {
        const search = state.search.trim().toLowerCase();
        return allOrders.filter(o => {
            const matchesStatus = state.status === 'all' || o.trangThai === state.status;
            const matchesSearch = !search
                || String(o.idDonHang).includes(search)
                || (o.hoTen && o.hoTen.toLowerCase().includes(search));
            return matchesStatus && matchesSearch;
        });
    }

    // 1. Hàm render danh sách đơn đóng gói chính lên bảng dữ liệu
    function renderTable() {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');
        const badge = document.getElementById('sidebarBadge');

        const filtered = getFilteredOrders();

        const pendingCount = allOrders.filter(o => o.trangThai === 'CHO_XAC_NHAN' || o.trangThai === 'DA_XAC_NHAN').length;
        if (badge) {
            badge.textContent = pendingCount;
            badge.style.display = pendingCount > 0 ? 'inline-flex' : 'none';
        }

        if (filtered.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            renderPagination(0);
            return;
        }
        emptyState.style.display = 'none';

        const startIndex = (state.page - 1) * state.pageSize;
        const paginated = filtered.slice(startIndex, startIndex + state.pageSize);

        tbody.innerHTML = paginated.map(o => {
            const st = statusLabel(o.trangThai);
            let actionBtn = '';
            if (o.trangThai === 'CHO_XAC_NHAN') {
                actionBtn = `<button class="btn btn-ghost" onclick="openViewConfirmModalById(${o.idDonHang})">
                                <i class="fa-solid fa-eye"></i> Xem &amp; Xác nhận
                             </button>`;
            } else if (o.trangThai === 'DA_XAC_NHAN') {
                actionBtn = `<button class="btn btn-ghost" onclick="openPackingModalById(${o.idDonHang})">
                                <i class="fa-solid fa-box-open"></i> Đóng gói - Chọn giao hàng
                             </button>`;
            } else {
                actionBtn = `<button class="btn btn-ghost" onclick="openPackingModalById(${o.idDonHang})">
                                <i class="fa-solid fa-box-open"></i> Xuất phiếu chuẩn bị
                             </button>`;
            }
            return `
                <tr>
                    <td class="order-code">#ORD-${o.idDonHang}</td>
                    <td>${o.hoTen}</td>
                    <td>${fmtDate(o.ngayDat)}</td>
                    <td class="amount">${fmtMoney(o.tongTien)}</td>
                    <td>${o.tongSoThuoc} viên/hộp (${o.soLoaiThuoc} loại)</td>
                    <td><span class="status-badge ${st.cls}">${st.text}</span></td>
                    <td style="text-align:right;">${actionBtn}</td>
                </tr>
            `;
        }).join('');

        renderPagination(filtered.length);
    }

    // Phân trang
    function renderPagination(totalItems) {
        let totalPages = Math.ceil(totalItems / state.pageSize);
        let box = document.getElementById('pagination');
        if (totalPages <= 1) {
            box.innerHTML = '';
            return;
        }

        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${i === state.page ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        }
        box.innerHTML = html;
    }

    function changePage(page) {
        state.page = page;
        renderTable();
    }

    // 2. Mở popup Xem & Xác nhận đơn (BƯỚC 1 - dành cho đơn CHO_XAC_NHAN)
    //    Gọi API thật DongGoiController::layChiTietDon($idDonHang)
    function openViewConfirmModalById(idDonHang) {
        state.activeId = idDonHang;

        fetch(`<?php echo URLROOT; ?>/duocSi/dongGoi/layChiTietDon/${idDonHang}`)
            .then(res => res.json())
            .then(res => {
                if (!res.status) {
                    alert(res.message || 'Không tải được chi tiết đơn hàng.');
                    return;
                }
                renderViewConfirmModal(res.donHang, res.chiTiet);
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    }

    function renderViewConfirmModal(donHang, chiTiet) {
        document.getElementById('view_idDonHang').textContent = `#ORD-${donHang.idDonHang}`;
        document.getElementById('view_hoTen').textContent = donHang.tenNguoiNhan || donHang.hoTen;
        document.getElementById('view_diaChi').textContent = donHang.diaChiGiaoHang;

        let viewTbody = document.getElementById('viewTableBody');
        viewTbody.innerHTML = chiTiet.map(item => {
            const tenHoatChat = item.thanhPhan
                ? `${item.thanhPhan}${item.hamLuong ? ' ' + item.hamLuong : ''}`
                : item.tenThuoc;

            const imgSrc = item.hinhAnh
                ? `<?php echo URLROOT; ?>/${item.hinhAnh}`
                : `<?php echo URLROOT; ?>/assets/images/no-image.png`;

            const conKho = !!item.loGoiY;

            return `
                <tr>
                    <td><img class="pack-item-img" src="${imgSrc}" alt="${item.tenThuoc}" onerror="this.style.visibility='hidden'"></td>
                    <td>
                        <div class="pack-item-name">${tenHoatChat}</div>
                        <div class="pack-item-sub">${item.tenThuoc}</div>
                    </td>
                    <td>${item.soLuong} ${item.donViTinh || ''}</td>
                    <td>${fmtMoney(item.donGia)}</td>
                    <td style="text-align:center;">
                        ${conKho
                            ? '<span class="batch-suggestion-pill"><i class="fa-solid fa-check"></i> Còn hàng</span>'
                            : '<span class="pack-out-of-stock">Hết hàng trong kho</span>'}
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('modalXemXacNhan').classList.remove('hidden');
    }

    // Xử lý sự kiện bấm nút "Xác nhận đơn hàng" (BƯỚC 1)
    // Gọi API thật DongGoiController::xacNhanDon($idDonHang)
    document.getElementById('btnConfirmDon').addEventListener('click', () => {
        if (!state.activeId) return;

        fetch(`<?php echo URLROOT; ?>/duocSi/dongGoi/xacNhanDon/${state.activeId}`, {
            method: 'POST'
        })
            .then(res => res.json())
            .then(res => {
                if (!res.status) {
                    alert(res.message || 'Xác nhận đơn hàng thất bại.');
                    return;
                }

                document.getElementById('modalXemXacNhan').classList.add('hidden');

                // Cập nhật trạng thái ngay trên danh sách hiện có, không cần tải lại trang
                const order = allOrders.find(o => o.idDonHang === state.activeId);
                if (order) order.trangThai = 'DA_XAC_NHAN';

                let toast = document.getElementById('toast');
                toast.querySelector('span').textContent = 'Đã xác nhận đơn hàng! Chuyển sang bước đóng gói.';
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                    toast.querySelector('span').textContent = 'Đã xác nhận đóng gói và bàn giao vận chuyển!';
                }, 2500);

                renderTable();
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    });

    // Xử lý sự kiện bấm nút "Từ chối đơn" (BƯỚC 1b)
    // Gọi API thật DongGoiController::tuChoiDon($idDonHang)
    document.getElementById('btnTuChoiDon').addEventListener('click', () => {
        if (!state.activeId) return;

        const lyDo = prompt('Nhập lý do từ chối đơn hàng:');
        if (lyDo === null) return; // người dùng bấm Cancel
        if (!lyDo.trim()) {
            alert('Vui lòng nhập lý do từ chối.');
            return;
        }

        fetch(`<?php echo URLROOT; ?>/duocSi/dongGoi/tuChoiDon/${state.activeId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lyDo: lyDo.trim() })
        })
            .then(res => res.json())
            .then(res => {
                if (!res.status) {
                    alert(res.message || 'Từ chối đơn hàng thất bại.');
                    return;
                }

                document.getElementById('modalXemXacNhan').classList.add('hidden');

                // Loại bỏ đơn khỏi danh sách hiển thị (đơn đã huỷ không thuộc luồng đóng gói nữa)
                allOrders = allOrders.filter(o => o.idDonHang !== state.activeId);

                let toast = document.getElementById('toast');
                toast.querySelector('span').textContent = 'Đã từ chối đơn hàng!';
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                    toast.querySelector('span').textContent = 'Đã xác nhận đóng gói và bàn giao vận chuyển!';
                }, 2500);

                renderTable();
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    });

    // 3. Mở popup hiển thị phiếu chuẩn bị thuốc & danh sách chi tiết (Nhặt kho FEFO) - BƯỚC 2
    //    Gọi API thật DongGoiController::layChiTietDon($idDonHang)
    function openPackingModalById(idDonHang) {
        state.activeId = idDonHang;

        fetch(`<?php echo URLROOT; ?>/duocSi/dongGoi/layChiTietDon/${idDonHang}`)
            .then(res => res.json())
            .then(res => {
                if (!res.status) {
                    alert(res.message || 'Không tải được chi tiết đơn hàng.');
                    return;
                }
                renderPackingModal(res.donHang, res.chiTiet);
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    }

    function renderPackingModal(donHang, chiTiet) {
        document.getElementById('ship_idDonHang').textContent = `#ORD-${donHang.idDonHang}`;
        document.getElementById('ship_hoTen').textContent = donHang.tenNguoiNhan || donHang.hoTen;
        document.getElementById('ship_diaChi').textContent = donHang.diaChiGiaoHang;

        const daHoanTat = donHang.trangThai === 'DANG_GIAO';

        let packTbody = document.getElementById('packTableBody');
        packTbody.innerHTML = chiTiet.map(item => {
            const lo = item.loGoiY;
            const heetDate = lo ? new Date(lo.hanSuDung).toLocaleDateString('vi-VN') : '—';

            // Tên hoạt chất / hàm lượng: ưu tiên hiển thị thanhPhan + hamLuong (giống mẫu),
            // nếu thiếu dữ liệu thì fallback về tên thương mại của thuốc
            const tenHoatChat = item.thanhPhan
                ? `${item.thanhPhan}${item.hamLuong ? ' ' + item.hamLuong : ''}`
                : item.tenThuoc;

            const imgSrc = item.hinhAnh
                ? `<?php echo URLROOT; ?>/${item.hinhAnh}`
                : `<?php echo URLROOT; ?>/assets/images/no-image.png`;

            return `
                <tr>
                    <td><img class="pack-item-img" src="${imgSrc}" alt="${item.tenThuoc}" onerror="this.style.visibility='hidden'"></td>
                    <td>
                        <div class="pack-item-name">${tenHoatChat}</div>
                        <div class="pack-item-sub">${item.tenThuoc}</div>
                    </td>
                    <td>${item.soLuong} ${item.donViTinh || ''}</td>
                    <td>${lo ? `<span class="batch-suggestion-pill"><i class="fa-solid fa-layer-group"></i> ${lo.maLo}</span>` : '<span class="pack-out-of-stock">Hết hàng trong kho</span>'}</td>
                    <td>${heetDate}</td>
                    <td style="text-align:center;">
                        <input type="checkbox" class="item-check check-box-large" onchange="evaluateChecklistStatus()" ${daHoanTat ? 'checked disabled' : (lo ? '' : 'disabled')}>
                    </td>
                </tr>
            `;
        }).join('');

        const btnConfirm = document.getElementById('btnConfirmPackComplete');
        if (daHoanTat) {
            btnConfirm.style.display = 'none';
        } else {
            btnConfirm.style.display = '';
            btnConfirm.disabled = true;
        }

        document.getElementById('modalPacking').classList.remove('hidden');
    }

    // 3. Kiểm tra điều kiện: Tất cả các dòng thuốc trong phiếu đều được tích chọn nhặt kho đầy đủ mới mở khóa nút "Xác nhận"
    function evaluateChecklistStatus() {
        let checkboxes = document.querySelectorAll('.item-check');
        let allChecked = Array.from(checkboxes).every(cb => cb.checked);
        document.getElementById('btnConfirmPackComplete').disabled = !allChecked;
    }

    // Xử lý sự kiện bấm nút hoàn tất đóng gói bàn giao xe vận chuyển
    // Gọi API thật DongGoiController::xacNhanDongGoi($idDonHang)
    document.getElementById('btnConfirmPackComplete').addEventListener('click', () => {
        if (!state.activeId) return;

        fetch(`<?php echo URLROOT; ?>/duocSi/dongGoi/xacNhanDongGoi/${state.activeId}`, {
            method: 'POST'
        })
            .then(res => res.json())
            .then(res => {
                if (!res.status) {
                    alert(res.message || 'Xác nhận đóng gói thất bại.');
                    return;
                }

                document.getElementById('modalPacking').classList.add('hidden');

                // Cập nhật trạng thái ngay trên danh sách hiện có, không cần tải lại trang
                const order = allOrders.find(o => o.idDonHang === state.activeId);
                if (order) order.trangThai = 'DANG_GIAO';

                let toast = document.getElementById('toast');
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2500);

                renderTable();
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    });

    // Đóng cửa sổ modal phiếu
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.close).classList.add('hidden');
        });
    });

    // ===== ĐĂNG KÝ SỰ KIỆN TÌM KIẾM & BỘ LỌC =====
    document.getElementById('searchInput').addEventListener('input', (e) => {
        state.search = e.target.value;
        state.page = 1;
        renderTable();
    });

    document.getElementById('filterStatus').addEventListener('change', (e) => {
        state.status = e.target.value;
        state.page = 1;
        renderTable();
    });

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        state.search = '';
        state.status = 'all';
        state.page = 1;
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatus').value = 'all';
        renderTable();
    });

    // Khởi tạo lần đầu
    renderTable();
</script>
