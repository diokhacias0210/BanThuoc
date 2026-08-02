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
    const modalEl = document.getElementById('modalEditProfile'); // Modal "Chỉnh sửa hồ sơ dược sĩ"

    /**
     * Lấy chữ cái đầu (họ + tên) từ họ tên đầy đủ để hiển thị lên avatar tròn.
     * Tự động bỏ tiền tố "DS." (Dược sĩ) nếu có trước khi tách chữ cái.
     * @param {string} name Họ tên đầy đủ, có thể kèm tiền tố "DS. " (VD: "DS. Nguyễn Văn An")
     * @returns {string} Chữ cái đầu viết hoa (1 hoặc 2 ký tự), hoặc "—" nếu không có tên
     */
    function layChuCaiDau(name) {
        if (!name) return '—';
        let cleanName = name.replace(/^DS\.\s*/i, ''); // Bỏ tiền tố "DS." nếu tên đã có sẵn (không phân biệt hoa/thường)
        let parts = cleanName.trim().split(' ');
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase(); // Chỉ có 1 từ -> lấy 1 chữ cái đầu
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase(); // Nhiều từ -> ghép chữ đầu (họ) + chữ đầu (tên)
    }

    /**
     * Hiện modal chỉnh sửa hồ sơ (bỏ class 'hidden').
     */
    function hienModal() {
        modalEl.classList.remove('hidden');
    }

    /**
     * Ẩn modal chỉnh sửa hồ sơ (thêm class 'hidden').
     */
    function anModal() {
        modalEl.classList.add('hidden');
    }

    // Đóng modal khi bấm nút X hoặc nút "Hủy"
    document.getElementById('btnModalClose').addEventListener('click', anModal);
    document.getElementById('btnModalCancel').addEventListener('click', anModal);

    // Sự kiện bấm "Chỉnh sửa hồ sơ": đổ dữ liệu đang hiển thị vào form rồi mở modal
    document.getElementById('btnEditProfile').addEventListener('click', () => {
        document.getElementById('f_hoTen').value = document.getElementById('view_hoTen').textContent.replace(/^DS\.\s*/i, ''); // Bỏ tiền tố "DS." khi đổ vào ô nhập
        document.getElementById('f_email').value = document.getElementById('db_email').textContent;
        document.getElementById('f_soDienThoai').value = document.getElementById('db_soDienThoai').textContent;
        document.getElementById('f_chungChi').value = document.getElementById('db_chungChiHanhNghe').textContent;
        document.getElementById('f_trinhDo').value = document.getElementById('db_trinhDo').textContent;
        document.getElementById('f_noiCap').value = document.getElementById('db_noiCap').textContent;
        hienModal();
    });

    /**
     * Render toàn bộ thông tin hồ sơ dược sĩ lên giao diện (phần xem, không phải form sửa).
     * @param {Object|null} pharmacistData Dữ liệu hồ sơ dược sĩ lấy từ CSDL (bảng NguoiDung + DuocSi),
     *                                     hoặc null nếu chưa có dữ liệu (không làm gì cả)
     */
    function hienThiHoSoDuocSi(pharmacistData) {
        if (!pharmacistData) return; // Không có dữ liệu (VD: lỗi truy vấn) -> giữ nguyên giao diện mặc định "—"
        document.getElementById('view_hoTen').textContent = "DS. " + pharmacistData.hoTen; // Thêm tiền tố "DS." khi hiển thị
        document.getElementById('view_vaiTro').textContent = pharmacistData.vaiTroLabel;   // Nhãn vai trò đã map sẵn ở Controller
        document.getElementById('view_avatar').textContent = layChuCaiDau(pharmacistData.hoTen);

        document.getElementById('db_idNguoiDung').textContent = "USR-" + String(pharmacistData.idNguoiDung).padStart(6, '0'); // Format mã định danh dạng USR-000123
        document.getElementById('db_trangThai').textContent = pharmacistData.trangThai == 1 ? 'Đang hoạt động' : 'Đã khóa'; // trangThai = 1 -> tài khoản còn hoạt động
        document.getElementById('db_email').textContent = pharmacistData.email;
        document.getElementById('db_soDienThoai').textContent = pharmacistData.soDienThoai || '—';
        document.getElementById('db_chungChiHanhNghe').textContent = pharmacistData.chungChiHanhNghe || '—';
        document.getElementById('db_trinhDo').textContent = pharmacistData.trinhDo || '—';
        document.getElementById('db_noiCap').textContent = pharmacistData.noiCap || '—';
    }

    /**
     * Lưu thông tin thật xuống CSDL (bảng NguoiDung + DuocSi) qua Controller.
     * Thu thập dữ liệu từ form chỉnh sửa, gửi lên server, và nếu thành công thì
     * cập nhật lại giao diện xem (không cần reload trang).
     */
    document.getElementById('btnSaveProfile').addEventListener('click', () => {
        // Gom toàn bộ giá trị form thành object để gửi lên server
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
                body: new URLSearchParams(updatedData).toString() // Encode object thành chuỗi x-www-form-urlencoded
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    // Cập nhật lại phần xem trên giao diện với dữ liệu vừa lưu thành công
                    document.getElementById('view_hoTen').textContent = "DS. " + updatedData.hoTen;
                    document.getElementById('view_avatar').textContent = layChuCaiDau(updatedData.hoTen);
                    document.getElementById('db_email').textContent = updatedData.email;
                    document.getElementById('db_soDienThoai').textContent = updatedData.soDienThoai;
                    document.getElementById('db_chungChiHanhNghe').textContent = updatedData.chungChiHanhNghe;
                    document.getElementById('db_trinhDo').textContent = updatedData.trinhDo;
                    document.getElementById('db_noiCap').textContent = updatedData.noiCap;

                    anModal();
                    // Ưu tiên dùng showToast nếu có sẵn trên trang, không thì fallback alert mặc định
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
    // vaiTroLabel: ánh xạ mã vaiTro sang nhãn hiển thị qua mảng $nhanVaiTro; nếu không map được thì
    // giữ nguyên mã vaiTro gốc, còn nếu hoàn toàn không có vaiTro thì hiển thị "Không xác định"
     const initialData = <?php echo $thongTin ? json_encode(array(
                            'idNguoiDung' => $thongTin['idNguoiDung'],
                            'hoTen' => $thongTin['hoTen'],
                            'vaiTroLabel' => (isset($thongTin['vaiTro']) && isset($nhanVaiTro[$thongTin['vaiTro']])) ? $nhanVaiTro[$thongTin['vaiTro']] : (isset($thongTin['vaiTro']) ? $thongTin['vaiTro'] : 'Không xác định'),
                            'trangThai' => $thongTin['trangThai'],
                            'email' => $thongTin['email'],
                            'soDienThoai' => $thongTin['soDienThoai'],
                            'chungChiHanhNghe' => $thongTin['chungChiHanhNghe'],
                            'trinhDo' => $thongTin['trinhDo'],
                            'noiCap' => $thongTin['noiCap']
                        ), JSON_UNESCAPED_UNICODE) : 'null'; ?>;
    hienThiHoSoDuocSi(initialData); // Render hồ sơ ngay khi trang vừa load
</script>
