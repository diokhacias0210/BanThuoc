<?php
// Hàm phụ lấy chữ cái đầu tên hiển thị avatar (không liên quan CSDL, chỉ xử lý chuỗi)
if (!function_exists("getInitials")) {
    function getInitials($name)
    {
        $parts = array_filter(explode(" ", trim($name)));
        if (empty($parts)) return "";
        $parts = array_values($parts);
        $first = mb_substr($parts[0], 0, 1);
        $last = mb_substr(end($parts), 0, 1);
        return mb_strtoupper($first . $last);
    }
}
?>
<!-- 
  VIEW trang Thông tin cá nhân — chỉ phần main content (không navbar/footer, bạn đã tự làm).
  Có nhúng CSDL cho: NguoiDung.hoTen/soDienThoai/email và DiaChiGiaoHang (đầy đủ các cột).
  Các trường KHÔNG có cột trong CSDL (Địa chỉ thường trú, Nhãn địa chỉ, Ghi chú giao hàng,
  trạng thái "đã xác thực") được giữ NGUYÊN dạng tĩnh như file mẫu gốc.
-->
<div class="wrap">

    <div class="card">
        <div class="profile-row">
            <div class="avatar" id="avatarInitials"><?php echo htmlspecialchars(getInitials(isset($thongTin['hoTen']) ? $thongTin['hoTen'] : '')); ?></div>
            <div class="profile-info">
                <div class="name" id="displayName"><?php echo htmlspecialchars(isset($thongTin['hoTen']) ? $thongTin['hoTen'] : ''); ?></div>
                <div class="verified">
                    <i class="fa-solid fa-circle-check"></i>
                    Tài khoản đã xác thực
                </div>
                <!-- "Tài khoản đã xác thực" giữ nguyên tĩnh vì bảng NguoiDung không có cột trạng thái xác thực -->
            </div>
        </div>

        <h2 class="section-title">Hồ sơ hiện tại</h2>

        <div class="form-grid">
            <div class="field">
                <label for="hoVaTen">Họ và tên</label>
                <input type="text" id="hoVaTen" value="<?php echo htmlspecialchars(isset($thongTin['hoTen']) ? $thongTin['hoTen'] : ''); ?>" disabled>
            </div>
            <div class="field">
                <label for="soDienThoai">Số điện thoại (Tên đăng nhập)</label>
                <div class="input-lock-wrapper">
                    <input type="tel" id="soDienThoai" value="<?php echo htmlspecialchars(isset($thongTin['soDienThoai']) ? $thongTin['soDienThoai'] : ''); ?>" readonly>
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>
            <div class="field">
                <label for="emailChinh">Email chính</label>
                <input type="email" id="emailChinh" value="<?php echo htmlspecialchars(isset($thongTin['email']) ? $thongTin['email'] : ''); ?>" disabled>
            </div>
        </div>
        <div class="form-grid full">
            <div class="field">
                <!-- "Địa chỉ thường trú" giữ nguyên giá trị tĩnh: bảng NguoiDung/KhachHang không có cột này -->
                <label for="diaChi">Địa chỉ thường trú</label>
                <input type="text" id="diaChi" value="123 Đường Nguyễn Trãi, P. An Bình, Q. Ninh Kiều, TP. Cần Thơ"
                    disabled>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-outline" id="btnEdit" onclick="toggleEdit(true)"><i class="fa-solid fa-pen"></i>
                Sửa thông tin</button>
            <button class="btn btn-primary" id="btnSave" onclick="saveInfo()" disabled><i
                    class="fa-solid fa-check"></i> Lưu thay đổi</button>
            <button class="btn btn-ghost" id="btnCancel" onclick="toggleEdit(false)" disabled>Hủy</button>
        </div>
    </div>

    <div class="card">
        <div class="addr-header">
            <h2 class="section-title" style="margin:0;">Địa chỉ giao hàng</h2>
            <button class="btn-add" onclick="openAddressModal()"><i class="fa-solid fa-plus"></i> Thêm địa
                chỉ</button>
        </div>

        <!-- Danh sách địa chỉ lấy từ bảng DiaChiGiaoHang (idDiaChi, tenNguoiNhan, soDienThoaiNhan, diaChiChiTiet, laMacDinh) -->
        <div id="addressList">
            <?php if (!empty($diaChiList)): ?>
                <?php foreach ($diaChiList as $dc): ?>
                    <div class="addr-item<?php echo !empty($dc['laMacDinh']) ? ' is-default' : ''; ?>" data-id="<?php echo $dc['idDiaChi']; ?>">
                        <div class="addr-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="addr-body">
                            <div class="addr-title-row">
                                <!-- Bảng DiaChiGiaoHang không có cột "nhãn địa chỉ" (VD: Nhà riêng/Công ty)
                                         nên giữ nguyên nhãn tĩnh, không lấy từ CSDL -->
                                <span class="addr-name">Địa chỉ giao hàng</span>
                                <?php if (!empty($dc['laMacDinh'])): ?><span class="badge badge-default">Mặc định</span><?php endif; ?>
                            </div>
                            <div class="addr-recipient">
                                <span><?php echo htmlspecialchars($dc['tenNguoiNhan']); ?></span>
                                <span class="dot"></span>
                                <span><?php echo htmlspecialchars($dc['soDienThoaiNhan']); ?></span>
                            </div>
                            <div class="addr-detail"><?php echo htmlspecialchars($dc['diaChiChiTiet']); ?></div>
                        </div>
                        <div class="addr-actions">
                            <button class="edit-link"><i class="fa-solid fa-pen-to-square"></i>Sửa</button>
                            <button class="setdefault-link"><i class="fa-regular fa-circle-check"></i>Đặt mặc định</button>
                            <button class="delete-link"><i class="fa-solid fa-trash-can"></i>Xoá</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:20px 0; color:var(--muted2);">Chưa có địa chỉ giao hàng nào.</div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ══ MODAL ĐỊA CHỈ (ĐÃ BỎ TỈNH, QUẬN, PHƯỜNG) ══ -->
<div class="modal-overlay" id="addrModalOverlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Thêm địa chỉ giao hàng</h3>
            <button class="modal-close" onclick="closeAddressModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addrForm">
                <div class="mf-grid full">
                    <div class="mfield">
                        <!-- Nhãn tên địa chỉ: giữ nguyên trên giao diện, KHÔNG lưu xuống CSDL
                                 vì bảng DiaChiGiaoHang chưa có cột tương ứng -->
                        <label>Nhãn tên địa chỉ <span class="req">*</span></label>
                        <input type="text" id="mLabel" placeholder="VD: Nhà riêng, Cơ quan, Nhà nội, Kho hàng..."
                            required>
                    </div>
                </div>

                <div class="mf-grid">
                    <div class="mfield">
                        <label>Tên người nhận <span class="req">*</span></label>
                        <input type="text" id="mRecipient" placeholder="Nguyễn Văn An" required>
                    </div>
                    <div class="mfield">
                        <label>Số điện thoại <span class="req">*</span></label>
                        <input type="tel" id="mPhone" placeholder="0912 345 678" required>
                    </div>
                </div>

                <!-- Form được tinh gọn, dồn vào ô nhập địa chỉ toàn diện -->
                <div class="mf-grid full">
                    <div class="mfield">
                        <label>Địa chỉ giao hàng đầy đủ <span class="req">*</span></label>
                        <input type="text" id="mDetail" placeholder="Số nhà, tên đường, phường, quận, tỉnh thành..."
                            required>
                        <div class="hint">VD: 12 Trần Hưng Đạo, Phường 1, TP. Vĩnh Long</div>
                    </div>
                </div>

                <div class="mf-grid full">
                    <div class="mfield">
                        <!-- Ghi chú giao hàng: giữ nguyên trên giao diện, KHÔNG lưu xuống CSDL
                                 vì bảng DiaChiGiaoHang chưa có cột tương ứng -->
                        <label>Ghi chú giao hàng</label>
                        <textarea id="mNote"
                            placeholder="VD: Giao giờ hành chính, gọi trước 15 phút, liên hệ bảo vệ tại sảnh..."></textarea>
                    </div>
                </div>

                <div class="check-row">
                    <input type="checkbox" id="mDefault">
                    <label for="mDefault">Đặt làm địa chỉ mặc định ngay khi tạo</label>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" onclick="closeAddressModal()">Hủy</button>
            <button type="button" class="btn btn-primary" onclick="submitAddress()"><i
                    class="fa-solid fa-check"></i> Lưu địa chỉ</button>
        </div>
    </div>
</div>

<script>
    const editableIds = ['hoVaTen', 'emailChinh', 'diaChi'];
    // Lưu ý: "diaChi" (Địa chỉ thường trú) vẫn cho sửa trên giao diện như bản gốc,
    // nhưng KHÔNG được gửi lên server / lưu CSDL vì chưa có cột tương ứng.

    function toggleEdit(editing) {
        editableIds.forEach(id => {
            document.getElementById(id).disabled = !editing;
        });
        document.getElementById('btnEdit').disabled = editing;
        document.getElementById('btnSave').disabled = !editing;
        document.getElementById('btnCancel').disabled = !editing;
    }

    function getInitials(name) {
        const parts = name.split(' ').filter(Boolean);
        if (parts.length === 0) return '';
        const last = parts[parts.length - 1][0] || '';
        const first = parts[0][0] || '';
        return (first + last).toUpperCase();
    }

    // Lưu Họ tên + Email thật xuống CSDL (bảng NguoiDung)
    function saveInfo() {
        const fullName = document.getElementById('hoVaTen').value.trim();
        const email = document.getElementById('emailChinh').value.trim();

        fetch(`<?php echo URLROOT; ?>/khachHang/thongTinCaNhan/capNhatThongTin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `hoTen=${encodeURIComponent(fullName)}&email=${encodeURIComponent(email)}`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    document.getElementById('displayName').textContent = fullName;
                    document.getElementById('avatarInitials').textContent = getInitials(fullName);
                    toggleEdit(false);
                } else {
                    alert(res.message || 'Cập nhật thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    }

    // ══ MODAL ĐỊA CHỈ ══
    const addrModalOverlay = document.getElementById('addrModalOverlay');

    function openAddressModal() {
        document.getElementById('addrForm').reset();
        document.getElementById('mRecipient').value = document.getElementById('hoVaTen').value;
        document.getElementById('mPhone').value = document.getElementById('soDienThoai').value;
        addrModalOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeAddressModal() {
        addrModalOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    addrModalOverlay.addEventListener('click', (e) => {
        if (e.target === addrModalOverlay) closeAddressModal();
    });

    // Thêm địa chỉ giao hàng thật xuống CSDL (bảng DiaChiGiaoHang)
    // "Nhãn tên địa chỉ" và "Ghi chú giao hàng" vẫn bắt buộc nhập trên form như cũ,
    // nhưng KHÔNG gửi lên server vì bảng chưa có cột lưu 2 trường này.
    function submitAddress() {
        const addrLabel = document.getElementById('mLabel').value.trim();
        const recipient = document.getElementById('mRecipient').value.trim();
        const phone = document.getElementById('mPhone').value.trim();
        const detail = document.getElementById('mDetail').value.trim();
        const isDefault = document.getElementById('mDefault').checked;

        if (!addrLabel || !recipient || !phone || !detail) {
            alert('Vui lòng điền đầy đủ các trường bắt buộc (*)');
            return;
        }

        fetch(`<?php echo URLROOT; ?>/khachHang/thongTinCaNhan/themDiaChi`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `tenNguoiNhan=${encodeURIComponent(recipient)}&soDienThoaiNhan=${encodeURIComponent(phone)}&diaChiChiTiet=${encodeURIComponent(detail)}&laMacDinh=${isDefault ? 1 : 0}`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    closeAddressModal();
                    window.location.reload();
                } else {
                    alert(res.message || 'Thêm địa chỉ thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    }

    // Xoá địa chỉ (bảng DiaChiGiaoHang)
    document.getElementById('addressList').addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-link');
        if (!deleteBtn) return;
        const item = deleteBtn.closest('.addr-item');
        const idDiaChi = item.dataset.id;

        fetch(`<?php echo URLROOT; ?>/khachHang/thongTinCaNhan/xoaDiaChi/${idDiaChi}`, {
                method: 'POST'
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    item.remove();
                } else {
                    alert(res.message || 'Xoá địa chỉ thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    });

    // Đặt địa chỉ mặc định (bảng DiaChiGiaoHang)
    document.getElementById('addressList').addEventListener('click', function(e) {
        const defaultBtn = e.target.closest('.setdefault-link');
        if (!defaultBtn) return;
        const idDiaChi = defaultBtn.closest('.addr-item').dataset.id;

        fetch(`<?php echo URLROOT; ?>/khachHang/thongTinCaNhan/datMacDinh/${idDiaChi}`, {
                method: 'POST'
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    window.location.reload();
                } else {
                    alert(res.message || 'Đặt mặc định thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    });
</script>