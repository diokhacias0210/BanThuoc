<?php
/**
 * Hàm phụ: lấy 2 chữ cái đầu (họ + tên) từ họ tên đầy đủ để hiển thị lên avatar.
 * Chỉ xử lý chuỗi thuần, không truy vấn CSDL.
 * @param string $name Họ tên đầy đủ của người dùng (VD: "Nguyễn Văn An")
 * @return string Chuỗi 2 ký tự viết hoa (VD: "NA"), trả về "" nếu $name rỗng
 */
if (!function_exists("layChuCaiDau")) {
    function layChuCaiDau($name)
    {
        // Tách chuỗi theo khoảng trắng, loại bỏ phần tử rỗng (trường hợp nhiều dấu cách liền nhau)
        $parts = array_filter(explode(" ", trim($name)));
        if (empty($parts)) return ""; // Không có từ nào -> trả về chuỗi rỗng

        $parts = array_values($parts); // Đánh lại chỉ số mảng liên tục từ 0
        $first = mb_substr($parts[0], 0, 1);   // Chữ cái đầu của từ đầu tiên (họ)
        $last = mb_substr(end($parts), 0, 1);  // Chữ cái đầu của từ cuối cùng (tên)
        return mb_strtoupper($first . $last);  // Ghép và viết hoa 2 chữ cái
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
            <div class="avatar" id="avatarInitials"><?php echo htmlspecialchars(layChuCaiDau(isset($thongTin['hoTen']) ? $thongTin['hoTen'] : '')); ?></div>
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
            <button class="btn btn-outline" id="btnEdit" onclick="chuyenCheDoSua(true)"><i class="fa-solid fa-pen"></i>
                Sửa thông tin</button>
            <button class="btn btn-primary" id="btnSave" onclick="luuThongTin()" disabled><i
                    class="fa-solid fa-check"></i> Lưu thay đổi</button>
            <button class="btn btn-ghost" id="btnCancel" onclick="chuyenCheDoSua(false)" disabled>Hủy</button>
        </div>
    </div>

    <div class="card">
        <div class="addr-header">
            <h2 class="section-title" style="margin:0;">Địa chỉ giao hàng</h2>
            <button class="btn-add" onclick="moModalDiaChi()"><i class="fa-solid fa-plus"></i> Thêm địa
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
            <button class="modal-close" onclick="dongModalDiaChi()">
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
            <button type="button" class="btn btn-ghost" onclick="dongModalDiaChi()">Hủy</button>
            <button type="button" class="btn btn-primary" onclick="guiDiaChi()"><i
                    class="fa-solid fa-check"></i> Lưu địa chỉ</button>
        </div>
    </div>
</div>

<script>
    // Danh sách id của các input được phép bật/tắt chỉnh sửa (chế độ Sửa thông tin)
    const editableIds = ['hoVaTen', 'emailChinh', 'diaChi'];
    // Lưu ý: "diaChi" (Địa chỉ thường trú) vẫn cho sửa trên giao diện như bản gốc,
    // nhưng KHÔNG được gửi lên server / lưu CSDL vì chưa có cột tương ứng.

    /**
     * Chuyển đổi giao diện giữa 2 chế độ: xem (readonly) và sửa (editable).
     * Bật/tắt thuộc tính disabled của các input trong editableIds và các nút hành động.
     * @param {boolean} editing true = đang ở chế độ sửa, false = chế độ xem
     */
    function chuyenCheDoSua(editing) {
        // Duyệt qua từng input được phép sửa, bật/tắt disabled theo trạng thái editing
        editableIds.forEach(id => {
            document.getElementById(id).disabled = !editing;
        });
        document.getElementById('btnEdit').disabled = editing;    // Khi đang sửa thì khoá nút "Sửa thông tin"
        document.getElementById('btnSave').disabled = !editing;   // Chỉ cho bấm "Lưu" khi đang sửa
        document.getElementById('btnCancel').disabled = !editing; // Chỉ cho bấm "Hủy" khi đang sửa
    }

    /**
     * Lấy 2 chữ cái đầu (họ + tên) từ họ tên đầy đủ để cập nhật avatar phía client.
     * Bản JS song song với hàm PHP layChuCaiDau ở đầu file (dùng khi render lại DOM
     * mà không cần load lại trang).
     * @param {string} name Họ tên đầy đủ
     * @returns {string} Chuỗi 2 ký tự viết hoa, hoặc '' nếu không có từ nào
     */
    function layChuCaiDau(name) {
        const parts = name.split(' ').filter(Boolean); // Tách theo khoảng trắng, bỏ phần tử rỗng
        if (parts.length === 0) return '';
        const last = parts[parts.length - 1][0] || ''; // Chữ cái đầu của từ cuối (tên)
        const first = parts[0][0] || '';                // Chữ cái đầu của từ đầu (họ)
        return (first + last).toUpperCase();
    }

    /**
     * Gửi yêu cầu cập nhật Họ tên + Email lên server để lưu xuống CSDL (bảng NguoiDung).
     * Nếu thành công thì cập nhật lại giao diện (tên hiển thị, avatar) và thoát chế độ sửa.
     */
    function luuThongTin() {
        const fullName = document.getElementById('hoVaTen').value.trim();   // Họ tên mới nhập
        const email = document.getElementById('emailChinh').value.trim();  // Email mới nhập

        fetch(`<?php echo URLROOT; ?>/khachHang/thongTinCaNhan/capNhatThongTin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                // Encode dữ liệu dạng x-www-form-urlencoded để gửi lên server
                body: `hoTen=${encodeURIComponent(fullName)}&email=${encodeURIComponent(email)}`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    // Cập nhật lại tên hiển thị và chữ cái avatar ngay trên giao diện, không cần reload
                    document.getElementById('displayName').textContent = fullName;
                    document.getElementById('avatarInitials').textContent = layChuCaiDau(fullName);
                    chuyenCheDoSua(false); // Thoát chế độ sửa sau khi lưu thành công
                } else {
                    alert(res.message || 'Cập nhật thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    }

    // ══ MODAL ĐỊA CHỈ ══
    const addrModalOverlay = document.getElementById('addrModalOverlay'); // Lớp phủ (overlay) chứa modal thêm địa chỉ

    /**
     * Mở modal "Thêm địa chỉ giao hàng".
     * Reset form về rỗng, rồi tự điền sẵn tên người nhận + số điện thoại
     * theo thông tin cá nhân hiện tại để tiện cho người dùng.
     */
    function moModalDiaChi() {
        document.getElementById('addrForm').reset(); // Xoá dữ liệu form cũ (nếu có) trước khi mở
        document.getElementById('mRecipient').value = document.getElementById('hoVaTen').value;   // Gợi ý tên người nhận = họ tên tài khoản
        document.getElementById('mPhone').value = document.getElementById('soDienThoai').value;   // Gợi ý SĐT = SĐT tài khoản
        addrModalOverlay.classList.add('open'); // Hiển thị modal
        document.body.style.overflow = 'hidden'; // Khoá cuộn trang nền khi modal đang mở
    }

    /**
     * Đóng modal "Thêm địa chỉ giao hàng" và trả lại trạng thái cuộn trang bình thường.
     */
    function dongModalDiaChi() {
        addrModalOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    // Cho phép đóng modal khi bấm ra vùng nền tối bên ngoài modal-box
    addrModalOverlay.addEventListener('click', (e) => {
        if (e.target === addrModalOverlay) dongModalDiaChi();
    });

    /**
     * Thu thập dữ liệu từ form modal, validate rồi gửi lên server để thêm
     * địa chỉ giao hàng mới xuống CSDL (bảng DiaChiGiaoHang).
     * "Nhãn tên địa chỉ" và "Ghi chú giao hàng" vẫn bắt buộc nhập trên form như cũ,
     * nhưng KHÔNG gửi lên server vì bảng chưa có cột lưu 2 trường này.
     */
    function guiDiaChi() {
        const addrLabel = document.getElementById('mLabel').value.trim();   // Nhãn địa chỉ (chỉ hiển thị UI, không lưu CSDL)
        const recipient = document.getElementById('mRecipient').value.trim(); // Tên người nhận hàng
        const phone = document.getElementById('mPhone').value.trim();       // SĐT người nhận hàng
        const detail = document.getElementById('mDetail').value.trim();     // Địa chỉ chi tiết đầy đủ
        const isDefault = document.getElementById('mDefault').checked;      // Có đặt làm địa chỉ mặc định hay không

        // Kiểm tra các trường bắt buộc trước khi gửi request
        if (!addrLabel || !recipient || !phone || !detail) {
            alert('Vui lòng điền đầy đủ các trường bắt buộc (*)');
            return;
        }

        fetch(`<?php echo URLROOT; ?>/khachHang/thongTinCaNhan/themDiaChi`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                // Chỉ gửi các trường có cột tương ứng trong bảng DiaChiGiaoHang
                body: `tenNguoiNhan=${encodeURIComponent(recipient)}&soDienThoaiNhan=${encodeURIComponent(phone)}&diaChiChiTiet=${encodeURIComponent(detail)}&laMacDinh=${isDefault ? 1 : 0}`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    dongModalDiaChi();
                    window.location.reload(); // Tải lại trang để hiển thị địa chỉ vừa thêm
                } else {
                    alert(res.message || 'Thêm địa chỉ thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    }

    /**
     * Sự kiện xoá địa chỉ giao hàng (uỷ quyền sự kiện - event delegation trên #addressList).
     * Khi bấm nút "Xoá" trong 1 addr-item, gửi request xoá xuống server (bảng DiaChiGiaoHang)
     * và xoá luôn phần tử đó khỏi DOM nếu thành công.
     */
    document.getElementById('addressList').addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-link'); // Tìm nút xoá gần nhất từ vị trí click
        if (!deleteBtn) return; // Click không nhằm vào nút xoá thì bỏ qua
        const item = deleteBtn.closest('.addr-item'); // Khối địa chỉ chứa nút vừa bấm
        const idDiaChi = item.dataset.id; // id địa chỉ lấy từ thuộc tính data-id

        fetch(`<?php echo URLROOT; ?>/khachHang/thongTinCaNhan/xoaDiaChi/${idDiaChi}`, {
                method: 'POST'
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    item.remove(); // Xoá phần tử khỏi giao diện, không cần reload
                } else {
                    alert(res.message || 'Xoá địa chỉ thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    });

    /**
     * Sự kiện đặt địa chỉ giao hàng làm mặc định (uỷ quyền sự kiện trên #addressList).
     * Gửi id địa chỉ được chọn lên server để cập nhật cột laMacDinh trong bảng DiaChiGiaoHang,
     * sau đó tải lại trang để đồng bộ badge "Mặc định" trên toàn bộ danh sách.
     */
    document.getElementById('addressList').addEventListener('click', function(e) {
        const defaultBtn = e.target.closest('.setdefault-link'); // Tìm nút "Đặt mặc định" gần nhất từ vị trí click
        if (!defaultBtn) return; // Click không nhằm vào nút này thì bỏ qua
        const idDiaChi = defaultBtn.closest('.addr-item').dataset.id; // id địa chỉ tương ứng

        fetch(`<?php echo URLROOT; ?>/khachHang/thongTinCaNhan/datMacDinh/${idDiaChi}`, {
                method: 'POST'
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    window.location.reload(); // Reload để cập nhật lại badge mặc định cho toàn danh sách
                } else {
                    alert(res.message || 'Đặt mặc định thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Lỗi kết nối máy chủ.'));
    });
</script>