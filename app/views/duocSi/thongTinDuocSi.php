<!--
  VIEW trang Thông tin dược sĩ — chỉ phần main content (không navbar/topbar/drawer/footer,
  bạn tự làm phần đó cho khu vực Dược sĩ).
  Nhúng CSDL đầy đủ cho bảng NguoiDung (hoTen, email, soDienThoai, trangThai, vaiTro)
  và DuocSi (chungChiHanhNghe, trinhDo, noiCap).
  Nhãn vai trò ("Dược sĩ trưởng"...) không có trong DB nên map tĩnh ở Controller, không đúng 100% ý nghĩa gốc.
-->
<div class="profile-card">
    <div class="profile-header">
        <div class="profile-avatar-large" id="view_avatar">—</div>
        <div class="profile-summary">
            <h2 id="view_hoTen">—</h2>
            <span class="badge-role" id="view_vaiTro">—</span>
        </div>
    </div>

    <div class="profile-body">
        <h3 class="section-title">
            <div class="icon icon-user-small"></div>
            Thông tin tài khoản hệ thống
        </h3>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Mã định danh (idNguoiDung)</span>
                <div class="info-value mono" id="db_idNguoiDung">—</div>
            </div>
            <div class="info-item">
                <span class="info-label">Trạng thái hoạt động</span>
                <div class="info-value" id="db_trangThai">—</div>
            </div>
            <div class="info-item">
                <span class="info-label">Địa chỉ Email</span>
                <div class="info-value" id="db_email">—</div>
            </div>
            <div class="info-item">
                <span class="info-label">Số điện thoại kết nối</span>
                <div class="info-value mono" id="db_soDienThoai">—</div>
            </div>
        </div>

        <div class="divider"></div>

        <h3 class="section-title">
            <div class="icon icon-doc-small"></div>
            Hồ sơ năng lực pháp lý &amp; Chuyên môn
        </h3>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Số chứng chỉ hành nghề</span>
                <div class="info-value mono" id="db_chungChiHanhNghe">—</div>
            </div>
            <div class="info-item">
                <span class="info-label">Trình độ chuyên môn (trinhDo)</span>
                <div class="info-value" id="db_trinhDo">—</div>
            </div>
            <div class="info-item span-2">
                <span class="info-label">Cơ quan / Nơi cấp bằng cấp chuyên môn (noiCap)</span>
                <div class="info-value" id="db_noiCap">—</div>
            </div>
        </div>

        <div style="margin-top: 40px; display: flex; justify-content: flex-end;">
            <button class="btn btn-primary" id="btnEditProfile">
                <div class="icon icon-edit-small"></div>
                Chỉnh sửa hồ sơ
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay hidden" id="modalEditProfile">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2>Chỉnh sửa hồ sơ dược sĩ</h2>
            </div>
            <button class="modal-close" id="btnModalClose">
                <div class="icon icon-close-modal"></div>
            </button>
        </div>
        <div class="modal-body">
            <form id="editProfileForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-field span-2">
                        <label>Họ và tên</label>
                        <input type="text" id="f_hoTen">
                    </div>
                    <div class="form-field">
                        <label>Địa chỉ Email</label>
                        <input type="email" id="f_email">
                    </div>
                    <div class="form-field">
                        <label>Số điện thoại</label>
                        <input type="text" id="f_soDienThoai">
                    </div>
                    <div class="form-field">
                        <label>Số chứng chỉ hành nghề</label>
                        <input type="text" id="f_chungChi">
                    </div>
                    <div class="form-field">
                        <label>Trình độ chuyên môn</label>
                        <input type="text" id="f_trinhDo">
                    </div>
                    <div class="form-field span-2">
                        <label>Nơi cấp chứng chỉ</label>
                        <input type="text" id="f_noiCap">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn btn-ghost" id="btnModalCancel">Hủy</button>
            <button class="btn btn-primary" id="btnSaveProfile">Lưu thay đổi</button>
        </div>
    </div>
</div>

<script>
    const modalEl = document.getElementById('modalEditProfile');

    function generateInitials(name) {
        if (!name) return '—';
        let cleanName = name.replace(/^DS\.\s*/i, '');
        let parts = cleanName.trim().split(' ');
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function showModal() {
        modalEl.classList.remove('hidden');
    }

    function hideModal() {
        modalEl.classList.add('hidden');
    }

    document.getElementById('btnModalClose').addEventListener('click', hideModal);
    document.getElementById('btnModalCancel').addEventListener('click', hideModal);

    document.getElementById('btnEditProfile').addEventListener('click', () => {
        document.getElementById('f_hoTen').value = document.getElementById('view_hoTen').textContent.replace(/^DS\.\s*/i, '');
        document.getElementById('f_email').value = document.getElementById('db_email').textContent;
        document.getElementById('f_soDienThoai').value = document.getElementById('db_soDienThoai').textContent;
        document.getElementById('f_chungChi').value = document.getElementById('db_chungChiHanhNghe').textContent;
        document.getElementById('f_trinhDo').value = document.getElementById('db_trinhDo').textContent;
        document.getElementById('f_noiCap').value = document.getElementById('db_noiCap').textContent;
        showModal();
    });

    function displayPharmacistProfile(pharmacistData) {
        if (!pharmacistData) return;
        document.getElementById('view_hoTen').textContent = "DS. " + pharmacistData.hoTen;
        document.getElementById('view_vaiTro').textContent = pharmacistData.vaiTroLabel;
        document.getElementById('view_avatar').textContent = generateInitials(pharmacistData.hoTen);

        document.getElementById('db_idNguoiDung').textContent = "USR-" + String(pharmacistData.idNguoiDung).padStart(6, '0');
        document.getElementById('db_trangThai').textContent = pharmacistData.trangThai == 1 ? 'Đang hoạt động' : 'Đã khóa';
        document.getElementById('db_email').textContent = pharmacistData.email;
        document.getElementById('db_soDienThoai').textContent = pharmacistData.soDienThoai || '—';
        document.getElementById('db_chungChiHanhNghe').textContent = pharmacistData.chungChiHanhNghe || '—';
        document.getElementById('db_trinhDo').textContent = pharmacistData.trinhDo || '—';
        document.getElementById('db_noiCap').textContent = pharmacistData.noiCap || '—';
    }

    // Lưu thông tin thật xuống CSDL (bảng NguoiDung + DuocSi) qua Controller
    document.getElementById('btnSaveProfile').addEventListener('click', () => {
        const updatedData = {
            hoTen: document.getElementById('f_hoTen').value.trim(),
            email: document.getElementById('f_email').value.trim(),
            soDienThoai: document.getElementById('f_soDienThoai').value.trim(),
            chungChiHanhNghe: document.getElementById('f_chungChi').value.trim(),
            trinhDo: document.getElementById('f_trinhDo').value.trim(),
            noiCap: document.getElementById('f_noiCap').value.trim()
        };

        fetch(`<?php echo URLROOT; ?>/duocSi/thongTinDuocSi/capNhatThongTin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(updatedData).toString()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    document.getElementById('view_hoTen').textContent = "DS. " + updatedData.hoTen;
                    document.getElementById('view_avatar').textContent = generateInitials(updatedData.hoTen);
                    document.getElementById('db_email').textContent = updatedData.email;
                    document.getElementById('db_soDienThoai').textContent = updatedData.soDienThoai;
                    document.getElementById('db_chungChiHanhNghe').textContent = updatedData.chungChiHanhNghe;
                    document.getElementById('db_trinhDo').textContent = updatedData.trinhDo;
                    document.getElementById('db_noiCap').textContent = updatedData.noiCap;

                    hideModal();
                    if (typeof showToast === 'function') {
                        showToast('Cập nhật thông tin hồ sơ thành công!');
                    } else {
                        alert('Cập nhật thông tin hồ sơ thành công!');
                    }
                } else {
                    alert(res.message || 'Cập nhật thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    });

    // Dữ liệu thật lấy từ CSDL, do Controller PHP truyền xuống (thay cho initialData giả lập)
    const initialData = <?php echo $thongTin ? json_encode([
                            'idNguoiDung' => $thongTin['idNguoiDung'],
                            'hoTen' => $thongTin['hoTen'],
                            'vaiTroLabel' => $nhanVaiTro[isset($thongTin['vaiTro']) ? $thongTin['vaiTro'] : null] ?? $thongTin['vaiTro'],
                            'trangThai' => $thongTin['trangThai'],
                            'email' => $thongTin['email'],
                            'soDienThoai' => $thongTin['soDienThoai'],
                            'chungChiHanhNghe' => $thongTin['chungChiHanhNghe'],
                            'trinhDo' => $thongTin['trinhDo'],
                            'noiCap' => $thongTin['noiCap']
                        ], JSON_UNESCAPED_UNICODE) : 'null'; ?>;
    displayPharmacistProfile(initialData);
</script>