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

    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    function setDateRange(type) {
        let start = new Date();
        let end = new Date();

        if (type === 'today') {
            // Ngày hiện tại
        } else if (type === 'week') {
            let day = start.getDay();
            let diff = start.getDate() - day + (day === 0 ? -6 : 1);
            start = new Date(start.setDate(diff));
        } else if (type === 'month') {
            start = new Date(start.getFullYear(), start.getMonth(), 1);
        } else if (type === 'year') {
            start = new Date(start.getFullYear(), 0, 1);
        }

        startDateInput.value = formatDateInput(start);
        endDateInput.value = formatDateInput(end);

        document.querySelectorAll('.btn-quick').forEach(b => b.classList.remove('active'));
        const quickBtn = document.querySelector(`.btn-quick[data-range="${type}"]`);
        if (quickBtn) quickBtn.classList.add('active');
    }

    document.querySelectorAll('.btn-quick').forEach(btn => {
        btn.addEventListener('click', (e) => {
            setDateRange(e.target.dataset.range);
            processData();
        });
    });

    // KẾT NỐI API TRUY VẤN DỮ LIỆU ĐỘNG
    function processData() {
        let startStr = startDateInput.value;
        let endStr = endDateInput.value;

        if (!startStr || !endStr) {
            alert("Vui lòng chọn đầy đủ khoảng thời gian (Từ ngày - Đến ngày).");
            return;
        }

        fetch(`<?php echo URLROOT; ?>/admin/baoCaoThongKe/getData?startDate=${startStr}&endDate=${endStr}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    // Cập nhật các thẻ chỉ số tổng quan
                    document.getElementById('valRevenue').textContent = fmtMoney(res.overview.totalRevenue);
                    document.getElementById('valOrders').textContent = res.overview.totalCompleted.toLocaleString('vi-VN');
                    document.getElementById('valItems').textContent = res.overview.totalItems.toLocaleString('vi-VN');
                    document.getElementById('valCanceled').textContent = res.overview.totalCanceled.toLocaleString('vi-VN');

                    // Cập nhật bảng dữ liệu thuốc bán
                    const tbody = document.getElementById('tableBody');
                    if (res.medicines.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:30px; color:var(--gray-500);"><i class="fa-solid fa-chart-line" style="font-size:24px; margin-bottom:8px; display:block;"></i> Không có dữ liệu bán hàng trong khoảng thời gian này.</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = res.medicines.map(m => `
                        <tr>
                            <td class="cell-mono cell-strong">TH-${String(m.idThuoc).padStart(4, '0')}</td>
                            <td>
                                <div class="cell-strong">${m.tenThuoc}</div>
                                <div class="cell-sub" style="font-size:12px; color:var(--gray-500);">${m.thanhPhan} ${m.hamLuong ? '- ' + m.hamLuong : ''}</div>
                            </td>
                            <td class="cell-strong">${m.tenDanhMuc || 'Chưa phân loại'}</td>
                            <td style="text-align: center;" class="cell-strong">${Number(m.luotBan).toLocaleString('vi-VN')}</td>
                            <td style="text-align: right;" class="cell-strong" style="color:var(--green-700);">${fmtMoney(m.doanhThu)}</td>
                        </tr>
                    `).join('');
                }
            })
            .catch(err => console.error("Lỗi lấy dữ liệu báo cáo:", err));
    }

    // TÍNH NĂNG XUẤT CSV CHUẨN MÃ UTF-8 BOM CỦA EXCEL
    document.getElementById('btnExportCSV').addEventListener('click', () => {
        let table = document.getElementById('reportTable');
        let rows = Array.from(table.querySelectorAll('tr'));

        let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; // Chuỗi \uFEFF giúp Excel hiển thị đúng Tiếng Việt

        rows.forEach(row => {
            let cols = Array.from(row.querySelectorAll('th, td'));
            let data = cols.map(c => {
                let text = c.innerText.replace(/"/g, '""');
                return `"${text}"`;
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

        if (typeof showToast === 'function') {
            showToast("Đã tải xuống tệp báo cáo CSV thành công!");
        } else {
            alert("Đã tải xuống tệp báo cáo CSV thành công!");
        }
    });

    document.getElementById('btnFilterData').addEventListener('click', processData);

    // Khởi tạo mặc định chọn "Tháng này"
    setDateRange('month');
    processData();
</script>