<div class="filter-card">
    <div class="filter-row">
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <div class="date-picker-group">
                <span>Từ ngày:</span>
                <input type="date" id="startDate">
                <span style="color: var(--gray-400);">|</span>
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
                <div class="icon icon-search"></div>
                Lọc dữ liệu
            </button>
        </div>
        <button class="btn btn-export" id="btnExportCSV">
            <div class="icon icon-export">
                <div class="line"></div>
            </div>
            Xuất báo cáo (CSV)
        </button>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon green">
            <div class="icon icon-dollar"></div>
        </div>
        <div>
            <div class="stat-label">Tổng doanh thu</div>
            <div class="stat-value" id="valRevenue">0đ</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <div class="icon icon-sheet"></div>
        </div>
        <div>
            <div class="stat-label">Đơn hàng hoàn tất</div>
            <div class="stat-value" id="valOrders">0</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <div class="icon icon-bag"></div>
        </div>
        <div>
            <div class="stat-label">Sản phẩm bán ra</div>
            <div class="stat-value" id="valItems">0</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <div class="icon icon-x-circle"></div>
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
    // --- UTILS ---
    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    function formatDateInput(date) {
        let d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    }

    function showToast(msg) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMsg').textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

    // --- LOGIC XỬ LÝ NGÀY THÁNG ---
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    function setDateRange(type) {
        let start = new Date();
        let end = new Date();

        if (type === 'today') {
            // Giữ nguyên ngày hiện tại
        } else if (type === 'week') {
            let day = start.getDay();
            let diff = start.getDate() - day + (day === 0 ? -6 : 1); // Đầu tuần là Thứ 2
            start = new Date(start.setDate(diff));
        } else if (type === 'month') {
            start = new Date(start.getFullYear(), start.getMonth(), 1);
        } else if (type === 'year') {
            start = new Date(start.getFullYear(), 0, 1);
        }

        startDateInput.value = formatDateInput(start);
        endDateInput.value = formatDateInput(end);

        // Cập nhật trạng thái Active trên giao diện nút bấm nhanh
        document.querySelectorAll('.btn-quick').forEach(b => b.classList.remove('active'));
        const quickBtn = document.querySelector(`.btn-quick[data-range="${type}"]`);
        if (quickBtn) quickBtn.classList.add('active');
    }

    // Đăng ký sự kiện chọn khoảng ngày nhanh
    document.querySelectorAll('.btn-quick').forEach(btn => {
        btn.addEventListener('click', (e) => {
            setDateRange(e.target.dataset.range);
            processData();
        });
    });

    // --- LOGIC LỌC VÀ TÍNH TOÁN (KẾT NỐI API SAU NÀY) ---
    function processData() {
        let startStr = startDateInput.value;
        let endStr = endDateInput.value;

        if (!startStr || !endStr) {
            alert("Vui lòng chọn đầy đủ khoảng thời gian (Từ ngày - Đến ngày).");
            return;
        }

        let startObj = new Date(startStr);
        startObj.setHours(0, 0, 0, 0);
        let endObj = new Date(endStr);
        endObj.setHours(23, 59, 59, 999);

        // ==========================================
        // TODO: Viết API Call hoặc truy vấn cơ sở dữ liệu thực tế tại đây
        // ==========================================

        // Ví dụ cấu trúc cập nhật DOM sau khi lấy được dữ liệu:
        /*
        let totalRev = 0;
        let totalOrd = 0;
        let totalCancel = 0;
        let totalItems = 0;

        document.getElementById('valRevenue').textContent = fmtMoney(totalRev);
        document.getElementById('valOrders').textContent = totalOrd.toLocaleString('vi-VN');
        document.getElementById('valItems').textContent = totalItems.toLocaleString('vi-VN');
        document.getElementById('valCanceled').textContent = totalCancel.toLocaleString('vi-VN');

        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:30px; color:var(--gray-500);">Không có dữ liệu bán hàng trong thời gian này.</td></tr>`;
        */
    }

    // --- LOGIC XUẤT CSV (Đọc trực tiếp từ DOM Table hiện tại để xuất file) ---
    document.getElementById('btnExportCSV').addEventListener('click', () => {
        let table = document.getElementById('reportTable');
        let rows = Array.from(table.querySelectorAll('tr'));

        let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; // Thêm UTF-8 BOM hỗ trợ hiển thị Tiếng Việt trong Excel

        rows.forEach(row => {
            let cols = Array.from(row.querySelectorAll('th, td'));
            let data = cols.map(c => {
                let text = c.innerText.replace(/\"/g, '\"\"'); // Xử lý ký tự nháy kép
                return `\"${text}\"`; // Bao bọc giá trị bằng dấu nháy kép để tránh lệch cột khi có dấu phẩy
            }).join(",");
            csvContent += data + "\r\n";
        });

        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `BaoCaoDoanhThu_${formatDateInput(new Date())}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showToast("Đã tải xuống báo cáo thành công!");
    });

    // Xử lý sự kiện đăng xuất
    document.querySelector('.logout-link').addEventListener('click', (e) => {
        e.preventDefault();
        // Xử lý logic xóa session/token đăng xuất tại đây
        alert('Đang đăng xuất khỏi hệ thống quản trị...');
    });

    // --- KHỞI CHẠY MẶC ĐỊNH (INIT) ---
    document.getElementById('btnFilterData').addEventListener('click', processData);

    // Mặc định thiết lập khoảng thời gian là "Tháng này" khi mới tải trang
    setDateRange('month');
    processData();
</script>