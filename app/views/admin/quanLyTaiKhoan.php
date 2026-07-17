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
    let searchTimeout;
    let toastTimer;

    const LOGGED_IN_ADMIN_ID = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; ?>;

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.close));
    });

    function showLocalToast(msg) {
        const toast = document.getElementById('localToast');
        document.getElementById('localToastMsg').textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function getInitials(name) {
        return name.split(' ').map(w => w[0]).slice(-2).join('').toUpperCase();
    }

    function fetchUserList() {
        const search = document.getElementById('searchInput').value.trim();
        const vaiTro = document.getElementById('filterRole').value;
        const trangThai = document.getElementById('filterStatus').value;
        const url = `<?php echo URLROOT; ?>/admin/quanLyTaiKhoan/getList?search=${encodeURIComponent(search)}&vaiTro=${vaiTro}&trangThai=${trangThai}`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status) renderTable(res.data);
            })
            .catch(err => console.error("Lỗi lấy danh sách tài khoản:", err));
    }

    function renderTable(list) {
        const tbody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');

        if (list.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = list.map(user => {
            const roleClass = user.vaiTro === 'QUAN_TRI_VIEN' ? 'badge-role-admin' : (user.vaiTro === 'DUOC_SI' ? 'badge-role-pharmacist' : 'badge-role-customer');
            const roleLabel = user.vaiTro === 'QUAN_TRI_VIEN' ? 'Quản trị viên' : (user.vaiTro === 'DUOC_SI' ? 'Dược sĩ' : 'Khách hàng');
            const statusClass = user.trangThai ? 'badge-status-active' : 'badge-status-locked';
            const statusLabel = user.trangThai ? 'Hoạt động' : 'Đã khóa';

            const lockIcon = user.trangThai ? `<i class="fa-solid fa-lock"></i>` : `<i class="fa-solid fa-lock-open"></i>`;
            const isAdminRow = user.vaiTro === 'QUAN_TRI_VIEN';

            const disabledAttr = (isSelf || isAdminRow) ? 'disabled title="Bạn không được phép tự xử lý chính mình hoặc thao tác lên tài khoản quản trị viên khác!"' : '';

            return `
                <tr class="${user.trangThai ? '' : 'row-inactive'}">
                    <td class="cell-mono cell-strong">USR-${String(user.idNguoiDung).padStart(6, '0')}</td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">${getInitials(user.hoTen)}</div>
                            <div class="cell-strong">${user.hoTen} ${isSelf ? '<small style="color:var(--green-700); font-weight:700;">(Bạn)</small>' : ''}</div>
                        </div>
                    </td>
                    <td>${user.email}</td>
                    <td class="cell-mono">${user.soDienThoai || '—'}</td>
                    <td><span class="badge ${roleClass}">${roleLabel}</span></td>
                    <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                    <td>
                        <div class="actions-cell">
                            <button class="action-btn view" onclick="openDetailModal(${user.idNguoiDung})" title="Xem hồ sơ chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="action-btn edit" onclick="openRoleModal(${user.idNguoiDung}, '${user.hoTen}', '${user.vaiTro}')" ${disabledAttr}>
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                            <button class="action-btn lock" onclick="toggleAccountStatus(${user.idNguoiDung}, '${user.hoTen}')" ${disabledAttr}>
                                ${lockIcon}
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function openDetailModal(id) {
        fetch(`<?php echo URLROOT; ?>/admin/quanLyTaiKhoan/detail/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    const u = res.data;
                    let extHTML = '';

                    if (u.vaiTro === 'KHACH_HANG') {
                        extHTML = `
                            <div class="detail-item"><div class="k">Điểm tích lũy</div><div class="v" style="color:var(--green-700);">${u.diemTichLuy || 0} điểm</div></div>
                            <div class="detail-item"><div class="k">Ngày sinh</div><div class="v">${u.ngaySinh || '—'}</div></div>
                            <div class="detail-item span-2"><div class="k">Địa chỉ giao hàng mặc định</div><div class="v">${u.diaChiGiaoHang || '—'}</div></div>
                        `;
                    } else if (u.vaiTro === 'DUOC_SI') {
                        extHTML = `
                            <div class="detail-item"><div class="k">Số chứng chỉ hành nghề</div><div class="v">${u.chungChiHanhNghe || '—'}</div></div>
                            <div class="detail-item"><div class="k">Trình độ chuyên môn</div><div class="v">${u.trinhDo || '—'}</div></div>
                            <div class="detail-item span-2"><div class="k">Nơi cấp bằng / chứng chỉ</div><div class="v">${u.noiCap || '—'}</div></div>
                        `;
                    }

                    document.getElementById('detailBody').innerHTML = `
                        <div class="detail-item"><div class="k">Mã số tài khoản</div><div class="v cell-mono">USR-${String(u.idNguoiDung).padStart(6, '0')}</div></div>
                        <div class="detail-item"><div class="k">Họ và tên</div><div class="v">${u.hoTen}</div></div>
                        <div class="detail-item"><div class="k">Địa chỉ Email</div><div class="v">${u.email}</div></div>
                        <div class="detail-item"><div class="k">Số điện thoại</div><div class="v cell-mono">${u.soDienThoai || '—'}</div></div>
                        <div class="detail-item"><div class="k">Phân quyền hệ thống</div><div class="v"><b style="color:var(--blue-600);">${u.vaiTro}</b></div></div>
                        <div class="detail-item"><div class="k">Trạng thái đăng nhập</div><div class="v">${u.trangThai ? 'Đang hoạt động' : 'Đang bị khóa'}</div></div>
                        ${extHTML}
                    `;
                    openModal('modalDetail');
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lấy chi tiết người dùng:", err));
    }

    function openRoleModal(id, name, currentRole) {
        if (id == LOGGED_IN_ADMIN_ID) {
            alert("Hệ thống chặn: Bạn không thể tự thay đổi vai trò của chính mình!");
            return;
        }
        document.getElementById('f_role_id').value = id;
        document.getElementById('f_role_name').value = name;
        document.getElementById('f_role_select').value = currentRole;
        openModal('modalRole');
    }

    document.getElementById('btnSaveRole').addEventListener('click', () => {
        const formData = new FormData(document.getElementById('roleForm'));
        fetch(`<?php echo URLROOT; ?>/admin/quanLyTaiKhoan/saveRole`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    closeModal('modalRole');
                    showLocalToast(res.message);
                    fetchUserList();
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi lưu quyền hạn tài khoản:", err));
    });

    function toggleAccountStatus(id, name) {
        if (id == LOGGED_IN_ADMIN_ID) {
            alert("Quy tắc an toàn: Bạn không được phép tự khóa chính tài khoản Admin của mình!");
            return;
        }
        if (confirm(`Xác nhận chuyển đổi trạng thái hoạt động (Khóa/Mở khóa) của tài khoản "${name}"?`)) {
            fetch(`<?php echo URLROOT; ?>/admin/quanLyTaiKhoan/toggleStatus/${id}`, {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        showLocalToast(res.message);
                        fetchUserList();
                    } else {
                        alert(res.message);
                    }
                })
                .catch(err => console.error("Lỗi cập nhật trạng thái tài khoản:", err));
        }
    }

    document.getElementById('searchInput').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchUserList, 350);
    });
    document.getElementById('filterRole').addEventListener('change', fetchUserList);
    document.getElementById('filterStatus').addEventListener('change', fetchUserList);

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterRole').value = 'all';
        document.getElementById('filterStatus').value = 'all';
        fetchUserList();
    });

    fetchUserList();
</script>