<div class="toolbar-card">
    <div class="toolbar">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Tìm theo tên, email hoặc số điện thoại...">
        </div>
        <select class="filter-select" id="filterRole">
            <option value="all">Tất cả vai trò</option>
            <option value="QUAN_TRI_VIEN">Quản trị viên</option>
            <option value="DUOC_SI">Dược sĩ</option>
            <option value="KHACH_HANG">Khách hàng</option>
        </select>
        <select class="filter-select" id="filterStatus">
            <option value="all">Tất cả trạng thái</option>
            <option value="active">Đang hoạt động</option>
            <option value="locked">Đã khóa</option>
        </select>
        <button class="btn btn-ghost" id="btnResetFilter">Đặt lại</button>
    </div>
</div>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="width: 120px;">Mã người dùng</th>
                    <th>Họ và tên</th>
                    <th>Email liên hệ</th>
                    <th>Số điện thoại</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th style="text-align: right; width: 160px;">Thao tác xử lý</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fa-solid fa-user-slash" style="font-size:40px; color:var(--gray-300); margin-bottom:14px; display:block;"></i>
        <div class="t1">Không tìm thấy tài khoản người dùng nào</div>
    </div>
</div>

<!-- Modal Chi Tiết Tài Khoản -->
<div class="modal-overlay hidden" id="modalDetail">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Chi tiết thông tin tài khoản</h2>
            <button class="modal-close" data-close="modalDetail">&times;</button>
        </div>
        <div class="modal-body">
            <div class="detail-grid" id="detailBody"></div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalDetail">Đóng cửa sổ</button>
        </div>
    </div>
</div>

<!-- Modal Phân Quyền -->
<div class="modal-overlay hidden" id="modalRole">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-head">
            <h2>Phân quyền chức năng tài khoản</h2>
            <button class="modal-close" data-close="modalRole">&times;</button>
        </div>
        <div class="modal-body">
            <form id="roleForm" onsubmit="return false;">
                <input type="hidden" id="f_role_id" name="idNguoiDung">
                <div class="form-grid">
                    <div class="form-field">
                        <label>Họ tên người dùng</label>
                        <input type="text" id="f_role_name" readonly disabled>
                    </div>
                    <div class="form-field">
                        <label>Chọn cấu hình vai trò mới <span class="req">*</span></label>
                        <select id="f_role_select" name="vaiTro">
                            <option value="KHACH_HANG">Khách hàng (CUSTOMER)</option>
                            <option value="DUOC_SI">Dược sĩ (PHARMACIST)</option>
                            <option value="QUAN_TRI_VIEN">Quản trị viên (ADMIN)</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" data-close="modalRole">Hủy bỏ</button>
            <button class="btn btn-primary" id="btnSaveRole">Xác nhận cấp quyền</button>
        </div>
    </div>
</div>

<div class="toast" id="localToast">
    <i class="fa-solid fa-circle-check" style="color:#1fae63;"></i>
    <span id="localToastMsg">Thao tác thành công</span>
</div>

<script>
    // Biến dùng để debounce ô tìm kiếm (tránh gọi API liên tục khi gõ)
    let searchTimeout;
    // Biến lưu timer của toast, dùng để reset thời gian ẩn toast mỗi khi hiển thị mới
    let toastTimer;

    // idNguoiDung của admin đang đăng nhập, lấy từ session PHP (dùng để chặn tự thao tác lên chính mình)
    const LOGGED_IN_ADMIN_ID = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; ?>;
    // id của admin GỐC (tài khoản QUAN_TRI_VIEN có idNguoiDung nhỏ nhất hệ thống), lấy từ Controller.
    // Chỉ tài khoản này mới được phép phân quyền/khóa các admin khác.
    const ROOT_ADMIN_ID = <?php echo isset($data['root_admin_id']) ? (int) $data['root_admin_id'] : 0; ?>;
    // Có phải mình đang đăng nhập bằng chính tài khoản admin gốc hay không
    const IS_ROOT_ADMIN = LOGGED_IN_ADMIN_ID === ROOT_ADMIN_ID;

    /**
     * Mở một modal theo id bằng cách gỡ class "hidden".
     * @param {string} id - id của phần tử modal-overlay cần mở
     */
    function moModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    /**
     * Đóng một modal theo id bằng cách thêm lại class "hidden".
     * @param {string} id - id của phần tử modal-overlay cần đóng
     */
    function dongModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // Gắn sự kiện đóng modal cho tất cả các nút có thuộc tính data-close (nút X, nút Đóng/Hủy)
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => dongModal(btn.dataset.close));
    });

    /**
     * Hiển thị thông báo toast tạm thời ở góc màn hình trong 3 giây.
     * @param {string} msg - Nội dung thông báo cần hiển thị
     */
    function hienThongBao(msg) {
        const toast = document.getElementById('localToast');
        document.getElementById('localToastMsg').textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer); // Hủy timer cũ nếu toast đang hiển thị, tránh ẩn sai lúc
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    /**
     * Lấy chữ cái đầu đại diện cho họ tên (dùng để hiển thị avatar chữ trong bảng).
     * @param {string} name - Họ và tên đầy đủ của người dùng
     * @returns {string} Tối đa 2 chữ cái viết hoa lấy từ 2 từ cuối của tên
     */
    function layChuCaiDau(name) {
        // Tách theo khoảng trắng, lấy chữ cái đầu mỗi từ, giữ lại 2 từ cuối cùng
        return name.split(' ').map(w => w[0]).slice(-2).join('').toUpperCase();
    }

    /**
     * Gọi API lấy danh sách tài khoản theo bộ lọc hiện tại (từ khóa tìm kiếm, vai trò, trạng thái)
     * và render lại bảng dữ liệu.
     */
    function taiDanhSachTaiKhoan() {
        const search = document.getElementById('searchInput').value.trim(); // Từ khóa tìm kiếm (tên/email/sđt)
        const vaiTro = document.getElementById('filterRole').value; // Bộ lọc vai trò (all/admin/dược sĩ/khách hàng)
        const trangThai = document.getElementById('filterStatus').value; // Bộ lọc trạng thái (all/active/locked)
        const url = `<?php echo URLROOT; ?>/admin/quanLyTaiKhoan/layDanhSach?search=${encodeURIComponent(search)}&vaiTro=${vaiTro}&trangThai=${trangThai}`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status) hienThiBang(res.data);
            })
            .catch(err => console.error("Lỗi lấy danh sách tài khoản:", err));
    }

    /**
     * Render danh sách tài khoản ra bảng HTML (#tableBody), bao gồm badge vai trò,
     * badge trạng thái, và các nút thao tác (xem/phân quyền/khóa-mở khóa).
     * @param {Array<Object>} list - Danh sách tài khoản trả về từ API
     */
    function hienThiBang(list) {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');

        // Không có tài khoản nào khớp bộ lọc -> xóa bảng và hiện trạng thái rỗng
        if (list.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = list.map(user => {
            // Chuẩn hóa trangThai về boolean thực sự: dữ liệu trả về từ server qua JSON có thể là
            // number (0/1), string ("0"/"1") hoặc boolean tùy driver CSDL. Nếu dùng thẳng "user.trangThai"
            // làm điều kiện, chuỗi "0" sẽ bị JS coi là TRUTHY (vì "0" là chuỗi không rỗng) -> badge/trạng thái
            // luôn hiển thị "Hoạt động" dù tài khoản đã bị khóa thật sự trong CSDL. Phải so sánh tường minh.
            const isActive = user.trangThai == 1 || user.trangThai === true;

            // Xác định class/nhãn badge vai trò dựa trên giá trị vaiTro
            const roleClass = user.vaiTro === 'QUAN_TRI_VIEN' ? 'badge-role-admin' : (user.vaiTro === 'DUOC_SI' ? 'badge-role-pharmacist' : 'badge-role-customer');
            const roleLabel = user.vaiTro === 'QUAN_TRI_VIEN' ? 'Quản trị viên' : (user.vaiTro === 'DUOC_SI' ? 'Dược sĩ' : 'Khách hàng');
            // Xác định class/nhãn badge trạng thái hoạt động (true = đang hoạt động, false = đã khóa)
            const statusClass = isActive ? 'badge-status-active' : 'badge-status-locked';
            const statusLabel = isActive ? 'Hoạt động' : 'Đã khóa';


            // Icon khóa/mở khóa hiển thị trên nút thao tác, đổi theo trạng thái hiện tại
            const lockIcon = isActive ? `<i class="fa-solid fa-lock"></i>` : `<i class="fa-solid fa-lock-open"></i>`;
            // Kiểm tra dòng hiện tại có phải là chính admin đang đăng nhập không
            const isSelf = user.idNguoiDung == LOGGED_IN_ADMIN_ID;
            // Kiểm tra dòng hiện tại có phải là một tài khoản quản trị viên khác không
            const isAdminRow = user.vaiTro === 'QUAN_TRI_VIEN';
            // Chỉ admin GỐC (IS_ROOT_ADMIN) mới được phép sửa vai trò/khóa một admin khác.
            // Các admin được cấp quyền đều bình đẳng -> không ai trong số họ được đụng vào admin khác,
            // kể cả bản thân admin gốc cũng không tự sửa được chính mình (đã chặn riêng ở isSelf).
            const blockedAdminRow = isAdminRow && !isSelf && !IS_ROOT_ADMIN;

            // Vô hiệu hóa nút phân quyền/khóa nếu là chính mình, hoặc là admin khác mà mình không phải gốc
            const disabledAttr = (isSelf || blockedAdminRow) ? 'disabled title="Chỉ tài khoản quản trị viên gốc mới có quyền thao tác lên tài khoản quản trị viên khác!"' : '';

            return `
                <tr class="${isActive ? '' : 'row-inactive'}">
                    <td class="cell-mono cell-strong">USR-${String(user.idNguoiDung).padStart(6, '0')}</td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">${layChuCaiDau(user.hoTen)}</div>
                            <div class="cell-strong">${user.hoTen} ${isSelf ? '<small style="color:var(--green-700); font-weight:700;">(Bạn)</small>' : ''}</div>
                        </div>
                    </td>
                    <td>${user.email}</td>
                    <td class="cell-mono">${user.soDienThoai || '—'}</td>
                    <td><span class="badge ${roleClass}">${roleLabel}</span></td>
                    <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                    <td>
                        <div class="actions-cell">
                            <button class="action-btn view" onclick="moModalChiTiet(${user.idNguoiDung})" title="Xem hồ sơ chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="action-btn edit" onclick="moModalPhanQuyen(${user.idNguoiDung}, '${user.hoTen}', '${user.vaiTro}')" ${disabledAttr}>
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                            <button class="action-btn lock" onclick="doiTrangThaiTaiKhoan(${user.idNguoiDung}, '${user.hoTen}')" ${disabledAttr}>
                                ${lockIcon}
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    /**
     * Mở modal xem chi tiết một tài khoản, gọi API lấy dữ liệu và render các trường
     * thông tin mở rộng tùy theo vai trò (khách hàng / dược sĩ).
     * @param {number} id - idNguoiDung cần xem chi tiết
     */
    function moModalChiTiet(id) {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyTaiKhoan/chiTiet/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    const u = res.data;
                    let extHTML = ''; // Phần thông tin mở rộng, khác nhau theo vai trò

                    if (u.vaiTro === 'KHACH_HANG') {
                        // Khách hàng: hiển thị điểm tích lũy, ngày sinh, địa chỉ giao hàng mặc định
                        extHTML = `
                            <div class="detail-item"><div class="k">Điểm tích lũy</div><div class="v" style="color:var(--green-700);">${u.diemTichLuy || 0} điểm</div></div>
                            <div class="detail-item"><div class="k">Ngày sinh</div><div class="v">${u.ngaySinh || '—'}</div></div>
                            <div class="detail-item span-2"><div class="k">Địa chỉ giao hàng mặc định</div><div class="v">${u.diaChiGiaoHang || '—'}</div></div>
                        `;
                    } else if (u.vaiTro === 'DUOC_SI') {
                        // Dược sĩ: hiển thị số chứng chỉ hành nghề, trình độ chuyên môn, nơi cấp bằng
                        extHTML = `
                            <div class="detail-item"><div class="k">Số chứng chỉ hành nghề</div><div class="v">${u.chungChiHanhNghe || '—'}</div></div>
                            <div class="detail-item"><div class="k">Trình độ chuyên môn</div><div class="v">${u.trinhDo || '—'}</div></div>
                            <div class="detail-item span-2"><div class="k">Nơi cấp bằng / chứng chỉ</div><div class="v">${u.noiCap || '—'}</div></div>
                        `;
                    }

                    // Render thông tin chung + phần mở rộng vào modal chi tiết
                    document.getElementById('detailBody').innerHTML = `
                        <div class="detail-item"><div class="k">Mã số tài khoản</div><div class="v cell-mono">USR-${String(u.idNguoiDung).padStart(6, '0')}</div></div>
                        <div class="detail-item"><div class="k">Họ và tên</div><div class="v">${u.hoTen}</div></div>
                        <div class="detail-item"><div class="k">Địa chỉ Email</div><div class="v">${u.email}</div></div>
                        <div class="detail-item"><div class="k">Số điện thoại</div><div class="v cell-mono">${u.soDienThoai || '—'}</div></div>
                        <div class="detail-item"><div class="k">Phân quyền hệ thống</div><div class="v"><b style="color:var(--blue-600);">${u.vaiTro}</b></div></div>
                        <div class="detail-item"><div class="k">Trạng thái đăng nhập</div><div class="v">${(u.trangThai == 1 || u.trangThai === true) ? 'Đang hoạt động' : 'Đang bị khóa'}</div></div>
                        ${extHTML}
                    `;
                    moModal('modalDetail');
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lấy chi tiết người dùng:", err));
    }

    /**
     * Mở modal phân quyền cho một tài khoản, điền sẵn thông tin hiện tại vào form.
     * Chặn thao tác nếu người dùng đang cố tự đổi vai trò của chính mình.
     * @param {number} id - idNguoiDung cần phân quyền
     * @param {string} name - Họ tên hiển thị (readonly) trong form
     * @param {string} currentRole - Vai trò hiện tại, dùng để chọn sẵn trong dropdown
     */
    function moModalPhanQuyen(id, name, currentRole) {
        // Chặn admin tự đổi vai trò của chính mình để tránh tự khóa quyền truy cập
        if (id == LOGGED_IN_ADMIN_ID) {
            alert("Hệ thống chặn: Bạn không thể tự thay đổi vai trò của chính mình!");
            return;
        }
        document.getElementById('f_role_id').value = id;
        document.getElementById('f_role_name').value = name;
        document.getElementById('f_role_select').value = currentRole;

        // Nếu người đang thao tác không phải admin gốc: khóa lựa chọn "Quản trị viên (ADMIN)"
        // để không ai ngoài admin gốc có thể tự cấp quyền admin cho tài khoản khác.
        const adminOption = document.querySelector('#f_role_select option[value="QUAN_TRI_VIEN"]');
        if (adminOption) {
            adminOption.disabled = !IS_ROOT_ADMIN;
            adminOption.title = IS_ROOT_ADMIN ? '' : 'Chỉ admin gốc mới được cấp quyền quản trị viên';
        }

        moModal('modalRole');
    }

    // Sự kiện khi bấm nút "Xác nhận cấp quyền": gửi form phân quyền lên server
    document.getElementById('btnSaveRole').addEventListener('click', () => {
        const formData = new FormData(document.getElementById('roleForm'));
        fetch(`<?php echo URLROOT; ?>/admin/quanLyTaiKhoan/luuVaiTro`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    dongModal('modalRole');
                    hienThongBao(res.message);
                    taiDanhSachTaiKhoan(); // Tải lại danh sách để cập nhật badge vai trò mới
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lưu quyền hạn tài khoản:", err));
    });

    /**
     * Đổi trạng thái hoạt động (khóa/mở khóa) của một tài khoản, sau khi xác nhận.
     * Chặn admin tự khóa chính tài khoản của mình.
     * @param {number} id - idNguoiDung cần đổi trạng thái
     * @param {string} name - Họ tên, dùng để hiển thị trong hộp thoại xác nhận
     */
    function doiTrangThaiTaiKhoan(id, name) {
        // Chặn admin tự khóa tài khoản của chính mình
        if (id == LOGGED_IN_ADMIN_ID) {
            alert("Quy tắc an toàn: Bạn không được phép tự khóa chính tài khoản Admin của mình!");
            return;
        }
        if (confirm(`Xác nhận chuyển đổi trạng thái hoạt động (Khóa/Mở khóa) của tài khoản "${name}"?`)) {
            fetch(`<?php echo URLROOT; ?>/admin/quanLyTaiKhoan/doiTrangThai/${id}`, {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        hienThongBao(res.message);
                        taiDanhSachTaiKhoan(); // Tải lại danh sách để cập nhật badge trạng thái mới
                    } else {
                        alert(res.message);
                    }
                })
                .catch(err => console.error("Lỗi cập nhật trạng thái tài khoản:", err));
        }
    }

    // Gõ vào ô tìm kiếm -> debounce 350ms trước khi gọi lại API (tránh spam request)
    document.getElementById('searchInput').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(taiDanhSachTaiKhoan, 350);
    });
    // Đổi bộ lọc vai trò -> tải lại danh sách ngay
    document.getElementById('filterRole').addEventListener('change', taiDanhSachTaiKhoan);
    // Đổi bộ lọc trạng thái -> tải lại danh sách ngay
    document.getElementById('filterStatus').addEventListener('change', taiDanhSachTaiKhoan);

    // Nút "Đặt lại": xóa từ khóa tìm kiếm, reset các bộ lọc về mặc định rồi tải lại danh sách
    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterRole').value = 'all';
        document.getElementById('filterStatus').value = 'all';
        taiDanhSachTaiKhoan();
    });

    // Tải danh sách tài khoản ngay khi trang load lần đầu
    taiDanhSachTaiKhoan();
</script>
