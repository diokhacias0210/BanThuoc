<div class="filter-card">
    <div class="filter-row">
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <div class="date-picker-group">
                <span>Từ ngày:</span>
                <input type="date" id="startDate">
                <span style="color: var(--gray-300);">|</span>
                <span>Đến ngày:</span>
                <input type="date" id="endDate">
            </div>
            <div class="quick-btn-group" id="quickBtnGroup">
                <button class="btn-quick" data-range="today">Hôm nay</button>
                <button class="btn-quick" data-range="week">Tuần này</button>
                <button class="btn-quick active" data-range="month">Tháng này</button>
                <button class="btn-quick" data-range="year">Năm nay</button>
            </div>
            <button class="btn btn-primary" id="btnFilterData">
                <i class="fa-solid fa-magnifying-glass"></i> Lọc dữ liệu
            </button>
        </div>
        <button class="btn btn-export" id="btnExportCSV">
            <i class="fa-solid fa-file-export"></i> Xuất báo cáo (CSV)
        </button>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fa-solid fa-sack-dollar"></i>
        </div>
        <div>
            <div class="stat-label">Tổng doanh thu</div>
            <div class="stat-value" id="valRevenue">0đ</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="stat-label">Đơn hàng hoàn tất</div>
            <div class="stat-value" id="valOrders">0</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fa-solid fa-box-open"></i>
        </div>
        <div>
            <div class="stat-label">Sản phẩm bán ra</div>
            <div class="stat-value" id="valItems">0</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div>
            <div class="stat-label">Đơn hàng bị hủy</div>
            <div class="stat-value" id="valCanceled">0</div>
        </div>
    </div>
</div>

<div class="table-section">
    <div class="table-header">
        <h3>Thống kê thuốc bán (Xếp theo doanh thu)</h3>
    </div>
    <div class="table-scroll">
        <table id="reportTable">
            <thead>
                <tr>
                    <th style="width: 120px;">Mã thuốc</th>
                    <th>Tên thuốc / Hoạt chất</th>
                    <th>Danh mục</th>
                    <th style="text-align: center;">Lượt bán ra</th>
                    <th style="text-align: right;">Doanh thu (VND)</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</div>

<script>
    /**
     * Định dạng một số tiền thành chuỗi tiền tệ Việt Nam (phân cách hàng nghìn + hậu tố "đ").
     * @param {number} n Số tiền cần định dạng (có thể null/undefined -> coi như 0)
     * @returns {string} Chuỗi tiền đã định dạng, VD: "1.000.000đ"
     */
    function dinhDangTien(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    /**
     * Định dạng một đối tượng Date thành chuỗi "yyyy-MM-dd" để gán vào input[type=date].
     * @param {Date|string} date Ngày cần định dạng
     * @returns {string} Chuỗi ngày dạng yyyy-MM-dd
     */
    function dinhDangNgayNhap(date) {
        let d = new Date(date),
            month = '' + (d.getMonth() + 1), // Tháng (getMonth() trả về 0-11 nên +1)
            day = '' + d.getDate(),          // Ngày trong tháng
            year = d.getFullYear();          // Năm
        if (month.length < 2) month = '0' + month; // Thêm số 0 phía trước nếu tháng chỉ có 1 chữ số
        if (day.length < 2) day = '0' + day;       // Thêm số 0 phía trước nếu ngày chỉ có 1 chữ số
        return [year, month, day].join('-');
    }

    const startDateInput = document.getElementById('startDate'); // Input "Từ ngày"
    const endDateInput = document.getElementById('endDate');     // Input "Đến ngày"

    /**
     * Tính khoảng ngày (Từ ngày - Đến ngày) tương ứng với nút lọc nhanh được chọn,
     * rồi gán giá trị vào 2 input ngày và bật trạng thái active cho nút tương ứng.
     * @param {string} type Loại khoảng thời gian: 'today' | 'week' | 'month' | 'year'
     */
    function datKhoangNgay(type) {
        let start = new Date(); // Ngày bắt đầu, mặc định là hôm nay
        let end = new Date();   // Ngày kết thúc luôn là hôm nay

        if (type === 'today') {
            // Ngày hiện tại -> không cần chỉnh start, giữ nguyên = hôm nay
        } else if (type === 'week') {
            let day = start.getDay(); // Thứ trong tuần hiện tại (0 = Chủ nhật, 1-6 = Thứ Hai - Thứ Bảy)
            let diff = start.getDate() - day + (day === 0 ? -6 : 1); // Tính lùi về thứ Hai của tuần hiện tại
            start = new Date(start.setDate(diff));
        } else if (type === 'month') {
            start = new Date(start.getFullYear(), start.getMonth(), 1); // Ngày 1 của tháng hiện tại
        } else if (type === 'year') {
            start = new Date(start.getFullYear(), 0, 1); // Ngày 1 tháng 1 của năm hiện tại
        }

        startDateInput.value = dinhDangNgayNhap(start);
        endDateInput.value = dinhDangNgayNhap(end);

        // Bỏ trạng thái active của tất cả nút lọc nhanh, rồi gắn active cho nút vừa chọn
        document.querySelectorAll('.btn-quick').forEach(b => b.classList.remove('active'));
        const quickBtn = document.querySelector(`.btn-quick[data-range="${type}"]`);
        if (quickBtn) quickBtn.classList.add('active');
    }

    // Gắn sự kiện click cho từng nút lọc nhanh (Hôm nay / Tuần này / Tháng này / Năm nay)
    document.querySelectorAll('.btn-quick').forEach(btn => {
        btn.addEventListener('click', (e) => {
            datKhoangNgay(e.target.dataset.range); // Cập nhật khoảng ngày theo nút vừa bấm
            xuLyDuLieu();                          // Truy vấn lại dữ liệu theo khoảng ngày mới
        });
    });

    /**
     * KẾT NỐI API TRUY VẤN DỮ LIỆU ĐỘNG
     * Gọi API lấy dữ liệu báo cáo thống kê (doanh thu, đơn hàng, sản phẩm bán ra...)
     * theo khoảng thời gian đang chọn trên 2 input ngày, sau đó render lên các thẻ
     * chỉ số tổng quan và bảng thống kê thuốc bán.
     */
    function xuLyDuLieu() {
        let startStr = startDateInput.value; // Giá trị "Từ ngày" hiện tại trên input
        let endStr = endDateInput.value;      // Giá trị "Đến ngày" hiện tại trên input

        // Bắt buộc phải chọn đủ cả 2 mốc thời gian trước khi gọi API
        if (!startStr || !endStr) {
            alert("Vui lòng chọn đầy đủ khoảng thời gian (Từ ngày - Đến ngày).");
            return;
        }

        fetch(`<?php echo URLROOT; ?>/admin/baoCaoThongKe/layDuLieu?startDate=${startStr}&endDate=${endStr}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    // Cập nhật các thẻ chỉ số tổng quan
                    document.getElementById('valRevenue').textContent = dinhDangTien(res.overview.totalRevenue);       // Tổng doanh thu
                    document.getElementById('valOrders').textContent = res.overview.totalCompleted.toLocaleString('vi-VN'); // Số đơn hoàn tất
                    document.getElementById('valItems').textContent = res.overview.totalItems.toLocaleString('vi-VN');      // Số sản phẩm bán ra
                    document.getElementById('valCanceled').textContent = res.overview.totalCanceled.toLocaleString('vi-VN'); // Số đơn bị huỷ

                    // Cập nhật bảng dữ liệu thuốc bán
                    const tbody = document.getElementById('tableBody');
                    if (res.medicines.length === 0) {
                        // Không có dữ liệu trong khoảng thời gian -> hiển thị placeholder thay vì bảng rỗng
                        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:30px; color:var(--gray-500);"><i class="fa-solid fa-chart-line" style="font-size:24px; margin-bottom:8px; display:block;"></i> Không có dữ liệu bán hàng trong khoảng thời gian này.</td></tr>`;
                        return;
                    }

                    // Render từng dòng thuốc: mã thuốc, tên + thành phần, danh mục, lượt bán, doanh thu
                    tbody.innerHTML = res.medicines.map(m => `
                        <tr>
                            <td class="cell-mono cell-strong">TH-${String(m.idThuoc).padStart(4, '0')}</td>
                            <td>
                                <div class="cell-strong">${m.tenThuoc}</div>
                                <div class="cell-sub" style="font-size:12px; color:var(--gray-500);">${m.thanhPhan} ${m.hamLuong ? '- ' + m.hamLuong : ''}</div>
                            </td>
                            <td class="cell-strong">${m.tenDanhMuc || 'Chưa phân loại'}</td>
                            <td style="text-align: center;" class="cell-strong">${Number(m.luotBan).toLocaleString('vi-VN')}</td>
                            <td style="text-align: right;" class="cell-strong" style="color:var(--green-700);">${dinhDangTien(m.doanhThu)}</td>
                        </tr>
                    `).join('');
                }
            })
            .catch(err => console.error("Lỗi lấy dữ liệu báo cáo:", err));
    }

    /**
     * TÍNH NĂNG XUẤT CSV CHUẨN MÃ UTF-8 BOM CỦA EXCEL
     * Đọc toàn bộ dữ liệu đang hiển thị trên bảng #reportTable (kể cả header) và
     * xuất thành file CSV tải về máy, đảm bảo Excel hiển thị đúng tiếng Việt có dấu.
     */
    document.getElementById('btnExportCSV').addEventListener('click', () => {
        let table = document.getElementById('reportTable');
        let rows = Array.from(table.querySelectorAll('tr')); // Lấy tất cả các dòng (kể cả dòng tiêu đề)

        let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; // Chuỗi \uFEFF (BOM) giúp Excel hiển thị đúng Tiếng Việt

        // Duyệt từng dòng, từng ô -> escape dấu " rồi bọc lại thành chuỗi CSV hợp lệ
        rows.forEach(row => {
            let cols = Array.from(row.querySelectorAll('th, td'));
            let data = cols.map(c => {
                let text = c.innerText.replace(/"/g, '""'); // Escape dấu ngoặc kép theo chuẩn CSV
                return `"${text}"`;
            }).join(",");
            csvContent += data + "\r\n";
        });

        // Tạo link ẩn để trigger tải file, sau đó gỡ khỏi DOM
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `BaoCaoDoanhThu_${dinhDangNgayNhap(new Date())}.csv`); // Tên file kèm ngày xuất báo cáo
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Thông báo xuất file thành công (ưu tiên showToast nếu có, không thì dùng alert mặc định)
        if (typeof showToast === 'function') {
            showToast("Đã tải xuống tệp báo cáo CSV thành công!");
        } else {
            alert("Đã tải xuống tệp báo cáo CSV thành công!");
        }
    });

    document.getElementById('btnFilterData').addEventListener('click', xuLyDuLieu); // Nút "Lọc dữ liệu" -> gọi lại API

    // Khởi tạo mặc định chọn "Tháng này" khi trang vừa load
    datKhoangNgay('month');
    xuLyDuLieu();
</script>
