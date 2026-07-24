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
        <div class="result-count">Tìm thấy <b id="resultCount">0</b> sản phẩm phù hợp</div>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px; text-align:center;">Hình ảnh</th>
                    <th>Tên thương mại</th>
                    <th>Danh mục thuốc</th>
                    <th>Phân loại</th>
                    <th>Giá niêm yết</th>
                    <th>Tồn kho khả dụng</th>
                    <th>Trạng thái</th>
                    <th style="text-align:right; width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fa-solid fa-box-open" style="font-size:40px; color:var(--gray-300); margin-bottom:14px; display:block;"></i>
        <div class="t1">Không tìm thấy sản phẩm thuốc phù hợp</div>
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

<script>
    const PLACEHOLDER_IMG = 'https://placehold.co/80x80/e2e8f0/64748b?text=No+Image';
    let searchTimeout;
    const modalForm = document.getElementById('modalForm');

    function openModal(el) {
        el.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(el) {
        el.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(document.getElementById(btn.dataset.close)));
    });

    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    // Logic toggle phân loại dược
    function setKedonToggle(value) {
        document.querySelectorAll('.kedon-option').forEach(opt => {
            const isMatch = opt.dataset.value === value;
            opt.classList.toggle('selected', isMatch);
            opt.querySelector('input').checked = isMatch;
        });
    }
    document.querySelectorAll('.kedon-option').forEach(opt => {
        opt.addEventListener('click', () => setKedonToggle(opt.dataset.value));
    });

    document.getElementById('f_khongGioiHan').addEventListener('change', (e) => {
        document.getElementById('f_gioiHanMua').disabled = e.target.checked;
        if (e.target.checked) document.getElementById('f_gioiHanMua').value = '';
    });
    document.getElementById('f_trangThai').addEventListener('change', (e) => {
        document.getElementById('trangThaiLabel').textContent = e.target.checked ? 'Đang bán' : 'Tạm ngưng';
    });

    // Preview nhiều ảnh khi chọn file
    document.getElementById('f_hinhAnh').addEventListener('change', (e) => {
        const previewsContainer = document.getElementById('f_hinhAnhPreviews');
        // Chỉ xóa previews cũ (không xóa ảnh đang có từ edit)
        const newPreviews = previewsContainer.querySelectorAll('.preview-new');
        newPreviews.forEach(el => el.remove());

        const files = e.target.files;
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file) {
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
    function fetchThuocList() {
        const search = document.getElementById('searchInput').value.trim();
        const idDanhMuc = document.getElementById('filterDanhMuc').value;
        const phanLoai = document.getElementById('filterPhanLoai').value;
        const trangThai = document.getElementById('filterTrangThai').value;

        fetch(`<?php echo URLROOT; ?>/admin/quanLyThuoc/getList?search=${encodeURIComponent(search)}&idDanhMuc=${idDanhMuc}&phanLoai=${phanLoai}&trangThai=${trangThai}&_=${Date.now()}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    renderTable(res.data);
                    renderCategoryFilter(res.categories);
                }
            });
    }

    function renderCategoryFilter(categories) {
        const select = document.getElementById('filterDanhMuc');
        const formSelect = document.getElementById('f_idDanhMuc');
        const currentFilterVal = select.value;

        const opts = categories.map(c => `<option value="${c.idDanhMuc}">${c.tenDanhMuc}</option>`).join('');
        select.innerHTML = '<option value="all">Tất cả danh mục</option>' + opts;
        formSelect.innerHTML = '<option value="">— Chọn danh mục —</option>' + opts;
        select.value = currentFilterVal;
    }

    function renderTable(list) {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');
        document.getElementById('resultCount').textContent = list.length;

        if (list.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }
        emptyState.style.display = 'none';

        tbody.innerHTML = list.map(item => {
            const badgeClass = item.yeuCauKeDon === 'Kê đơn' ? 'badge-rx' : 'badge-otc';
            const trangThai = item.trangThai == 1 || item.trangThai === '1' || item.trangThai === true;
            const statusClass = trangThai ? 'badge-active' : 'badge-inactive';
            const statusLabel = trangThai ? 'Còn bán' : 'Tạm ngưng';
            const lowStockHTML = item.tongTon <= 10 ? `<br><span class="badge badge-lowstock" style="margin-top:4px;">Sắp hết hàng</span>` : '';

            return `
                <tr class="${trangThai ? '' : 'row-inactive'}">
                    <td style="text-align:center;"><img class="thumb" src="${item.hinhAnh || PLACEHOLDER_IMG}" alt=""></td>
                    <td>
                        <div class="cell-strong">${item.tenThuoc}</div>
                        <div class="cell-sub">HC: ${item.thanhPhan} ${item.hamLuong ? '- ' + item.hamLuong : ''}</div>
                    </td>
                    <td class="cell-strong">${item.tenDanhMuc || 'Chưa phân loại'}</td>
                    <td><span class="badge ${badgeClass}">${item.yeuCauKeDon}</span></td>
                    <td class="cell-strong" style="color:var(--green-700);">${fmtMoney(item.giaBan)}</td>
                    <td class="cell-strong">${Number(item.tongTon).toLocaleString('vi-VN')} ${item.donViTinh}${lowStockHTML}</td>
                    <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                    <td>
                        <div class="actions-cell">
                            <button
                                class="action-btn view"
                                onclick="openDetail(${item.idThuoc})"
                                title="Chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            
                            <button class="action-btn edit" onclick="openEditForm(${item.idThuoc})" title="Sửa thông tin">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="action-btn delete" onclick="toggleStatus(${item.idThuoc})" title="Đổi trạng thái kinh doanh">
                                <i class="fa-solid fa-toggle-on"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function openAddForm() {
        document.getElementById('formModalTitle').textContent = 'Thêm ';
        document.getElementById('thuocForm').reset();
        document.getElementById('f_idThuoc').value = '';
        // Xóa toàn bộ previews
        document.getElementById('f_hinhAnhPreviews').innerHTML = '';
        document.getElementById('f_gioiHanMua').disabled = true;
        document.getElementById('f_trangThai').checked = true;
        document.getElementById('trangThaiLabel').textContent = 'Đang bán';
        setKedonToggle('Không kê đơn');
        openModal(modalForm);
    }

    function openEditForm(id) {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyThuoc/getDetailData/${id}`)
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

                    setKedonToggle(t.yeuCauKeDon);

                    const noLimit = t.gioiHanMua == -1;
                    document.getElementById('f_khongGioiHan').checked = noLimit;
                    document.getElementById('f_gioiHanMua').disabled = noLimit;
                    document.getElementById('f_gioiHanMua').value = noLimit ? '' : t.gioiHanMua;

                    document.getElementById('f_trangThai').checked = t.trangThai == 1;
                    document.getElementById('trangThaiLabel').textContent = t.trangThai == 1 ? 'Đang bán' : 'Tạm ngưng';

                    // Hiển thị danh sách ảnh hiện có kèm nút xóa
                    const previewsContainer = document.getElementById('f_hinhAnhPreviews');
                    previewsContainer.innerHTML = '';
                    if (res.images && res.images.length > 0) {
                        res.images.forEach(img => {
                            const div = document.createElement('div');
                            div.className = 'preview-item preview-existing';
                            div.innerHTML = `
                                <img class="preview-thumb" src="${img.duongDan}" alt="">
                                <button class="preview-delete-btn" type="button" title="Xóa ảnh" data-img="${img.duongDan}">&times;</button>
                            `;
                            previewsContainer.appendChild(div);
                        });

                        // Gắn sự kiện xóa cho từng nút
                        previewsContainer.querySelectorAll('.preview-delete-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                // Thêm đường dẫn ảnh cần xóa vào hidden input
                                const imgPath = this.dataset.img;
                                let deleteInput = document.getElementById('f_deleteImages');
                                if (!deleteInput) {
                                    deleteInput = document.createElement('div');
                                    deleteInput.id = 'f_deleteImages';
                                    document.getElementById('thuocForm').appendChild(deleteInput);
                                }
                                // Tạo hidden input cho mỗi ảnh cần xóa
                                const hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = 'deleteImages[]';
                                hidden.value = imgPath;
                                deleteInput.appendChild(hidden);
                                // Ẩn item preview
                                this.closest('.preview-item').style.display = 'none';
                            });
                        });
                    }

                    openModal(modalForm);
                }
            });
    }

    document.getElementById('btnSaveThuoc').addEventListener('click', function () {
        var form = document.getElementById('thuocForm');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);

        fetch('<?php echo URLROOT; ?>/admin/quanLyThuoc/save', {
            method: 'POST',
            body: formData
        })
        .then(function (res) {
            return res.text(); // Lấy dạng chuỗi thô để tránh crash khi PHP có Warning
        })
        .then(function (text) {
            var res;
            try {
                res = JSON.parse(text);
            } catch (e) {
                console.error("Server Response Error:", text);
                alert("Lỗi phản hồi từ máy chủ! Chi tiết: " + text.substring(0, 200));
                return;
            }

            if (res.status) {
                // Đóng Modal ngay lập tức
                var modal = document.getElementById('modalForm');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
                alert(res.message);
                
                // Cập nhật lại danh sách trực tiếp không cần reload toàn bộ trang
                fetchThuocList();
            } else {
                alert(res.message || 'Có lỗi xảy ra, không thể lưu dữ liệu!');
            }
        })
        .catch(function (err) {
            console.error(err);
            alert('Lỗi kết nối máy chủ!');
        });
    });

    function toggleStatus(id) {
        if (confirm('Xác nhận thay đổi trạng thái mở bán / tạm ngưng của mặt hàng thuốc này?')) {
            fetch(`<?php echo URLROOT; ?>/admin/quanLyThuoc/toggleStatus/${id}`, {
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
                    if (typeof showToast === 'function') {
                        showToast(res.message);
                    } else {
                        alert(res.message);
                    }
                    
                    // Chuyển bộ lọc về "Tất cả trạng thái" để thấy thay đổi ngay trên màn hình
                    document.getElementById('filterTrangThai').value = 'all';
                    
                    // Cập nhật lại danh sách
                    fetchThuocList();
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

    document.getElementById('searchInput').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchThuocList, 300);
    });
    document.getElementById('filterDanhMuc').addEventListener('change', fetchThuocList);
    document.getElementById('filterPhanLoai').addEventListener('change', fetchThuocList);
    document.getElementById('filterTrangThai').addEventListener('change', fetchThuocList);
    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterDanhMuc').value = 'all';
        document.getElementById('filterPhanLoai').value = 'all';
        document.getElementById('filterTrangThai').value = 'all';
        fetchThuocList();
    });

    document.getElementById('btnAddThuoc').addEventListener('click', openAddForm);

    fetchThuocList();

    function openDetail(id) {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyThuoc/getDetailData/${id}`)
            .then(res => res.json())
            .then(res => {

                if (!res.status) {
                    alert(res.message);
                    return;
                }

                //đổ dữ liệu vào popup
                document.getElementById("detailContent").innerHTML = `

                <p><b>Tên thuốc:</b> ${res.thuoc.tenThuoc}</p>

                <p><b>Danh mục:</b> ${res.thuoc.tenDanhMuc}</p>

                <p><b>Hoạt chất:</b> ${res.thuoc.thanhPhan}</p>

                <p><b>Hàm lượng:</b> ${res.thuoc.hamLuong}</p>

                <p><b>Công dụng:</b> ${res.thuoc.congDung}</p>

                <p><b>Đơn vị:</b> ${res.thuoc.donViTinh}</p>

                <p><b>Giá:</b> ${fmtMoney(res.thuoc.giaBan)}</p>

                <p><b>Yêu cầu kê đơn:</b> ${res.thuoc.yeuCauKeDon}</p>

                <p><b>Giới hạn mua:</b> ${res.thuoc.gioiHanMua}</p>

                <hr>

                <h4>Danh sách lô thuốc</h4>

                <table class="table">

                <thead>

                <tr>

                <th>Mã lô</th>

                <th>HSD</th>

                <th>Tồn</th>

                <th>Giá nhập</th>

                </tr>

                </thead>

                <tbody>

                ${
                res.lots.map(l=>`

                <tr>

                <td>${l.maLo}</td>

                <td>${l.hanSuDung}</td>

                <td>${l.soLuongTon}</td>

                <td>${fmtMoney(l.giaNhap)}</td>

                </tr>

                `).join("")
                }

                </tbody>

                </table>

                `;

                openModal(document.getElementById("modalDetail"));
            });
    }
</script>