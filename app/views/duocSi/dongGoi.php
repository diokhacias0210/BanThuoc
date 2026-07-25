<div class="toolbar">
    <div class="toolbar-search">
        <div class="icon icon-search"></div>
        <input type="text" id="searchInput" placeholder="Tìm theo mã đơn hàng hoặc tên khách hàng...">
    </div>
    <select class="filter-select" id="filterStatus">
        <option value="all">Tất cả đơn đóng gói</option>
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

<div class="modal-overlay hidden" id="modalPacking">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2>Phiếu chuẩn bị thuốc <span id="ship_idDonHang">—</span></h2>
            </div>
            <button class="modal-close" data-close="modalPacking"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="meta-shipping">
                <div><strong>Khách hàng:</strong> <span id="ship_hoTen">—</span></div>
                <div><strong>Số điện thoại:</strong> <span id="ship_sdt">—</span></div>
                <div class="span-2"><strong>Địa chỉ giao hàng:</strong> <span id="ship_diaChi">—</span></div>
            </div>
            <table class="pack-table">
                <thead>
                    <tr>
                        <th>Tên thuốc</th>
                        <th>Số lượng</th>
                        <th>Lô gợi ý (FEFO)</th>
                        <th>Hạn dùng</th>
                        <th style="text-align:center;">Đã nhặt</th>
                    </tr>
                </thead>
                <tbody id="packTableBody"></tbody>
            </table>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalPacking">Đóng</button>
            <button class="btn btn-primary" id="btnConfirmPackComplete" disabled>
                <i class="fa-solid fa-check"></i> Xác nhận đóng gói xong
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
        return trangThai === 'DANG_GIAO'
            ? { text: 'Đã đóng gói (Đang giao)', cls: 'st-dang-giao' }
            : { text: 'Chờ đóng gói', cls: 'st-cho-dong-goi' };
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

        const pendingCount = allOrders.filter(o => o.trangThai === 'DA_XAC_NHAN').length;
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
            return `
                <tr>
                    <td class="order-code">#ORD-${o.idDonHang}</td>
                    <td>${o.hoTen}</td>
                    <td>${fmtDate(o.ngayDat)}</td>
                    <td class="amount">${fmtMoney(o.tongTien)}</td>
                    <td>${o.tongSoThuoc} viên/hộp (${o.soLoaiThuoc} loại)</td>
                    <td><span class="status-badge ${st.cls}">${st.text}</span></td>
                    <td style="text-align:right;">
                        <button class="btn btn-ghost" onclick="openPackingModalById(${o.idDonHang})">
                            <i class="fa-solid fa-box-open"></i> ${o.trangThai === 'DANG_GIAO' ? 'Xuất phiếu chuẩn bị' : 'Đóng gói'}
                        </button>
                    </td>
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

    // 2. Mở popup hiển thị phiếu chuẩn bị thuốc & danh sách chi tiết (Nhặt kho FEFO)
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
        document.getElementById('ship_sdt').textContent = donHang.soDienThoaiNhan || '—';
        document.getElementById('ship_diaChi').textContent = donHang.diaChiGiaoHang;

        let packTbody = document.getElementById('packTableBody');
        packTbody.innerHTML = chiTiet.map(item => {
            const lo = item.loGoiY;
            const heetDate = lo ? new Date(lo.hanSuDung).toLocaleDateString('vi-VN') : '—';
            return `
                <tr>
                    <td>${item.tenThuoc}</td>
                    <td>${item.soLuong} ${item.donViTinh || ''}</td>
                    <td>${lo ? `<span class="batch-suggestion"><i class="fa-solid fa-layer-group"></i> ${lo.maLo}</span>` : '<span style="color:var(--red);">Hết hàng trong kho</span>'}</td>
                    <td>${heetDate}</td>
                    <td style="text-align:center;">
                        <input type="checkbox" class="item-check check-box-large" onchange="evaluateChecklistStatus()" ${lo ? '' : 'disabled'}>
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('btnConfirmPackComplete').disabled = true;
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
