<?php
/**
 * View: Thanh toán & Đặt hàng
 * Chức năng: Trang bước 2 trong quy trình đặt hàng, cho phép khách hàng chọn
 * địa chỉ nhận hàng đã lưu (hoặc thêm địa chỉ mới qua modal / nhập tạm thời),
 * xem lại danh sách sản phẩm đã chọn từ giỏ hàng, chọn phương thức thanh toán
 * (COD / chuyển khoản / ví điện tử), nhập ghi chú và xác nhận đặt hàng.
 * Dữ liệu đầu vào: $selectedIdsStr (danh sách id sản phẩm giỏ hàng đã chọn,
 * dạng chuỗi), $diaChiList (danh sách địa chỉ đã lưu của khách hàng),
 * $cartItems (danh sách sản phẩm trong đơn), $tongTien (tổng tiền thanh toán).
 */
?>
<!--
  VIEW trang Thanh toán & Đặt hàng
  Hỗ trợ: Chọn địa chỉ có sẵn, Thêm địa chỉ mới qua Modal, Chọn phương thức thanh toán.
-->
<link rel="stylesheet" href="<?= ASSETROOT ?>/css/khachHang/thanhToanDatHang.css">

<div class="page">

    <div class="stepper">
        <div class="step done">
            <div class="step-circle"><i class="fa-solid fa-check"></i></div>
            <div class="step-label">Giỏ hàng</div>
        </div>
        <div class="step-line done"></div>
        <div class="step current">
            <div class="step-circle">2</div>
            <div class="step-label">Thanh toán &amp; Đặt hàng</div>
        </div>
        <div class="step-line"></div>
        <div class="step upcoming">
            <div class="step-circle">3</div>
            <div class="step-label">Hoàn tất đơn hàng</div>
        </div>
    </div>

    <form id="checkoutForm" method="POST" action="<?php echo URLROOT; ?>/khachHang/thanhToanDatHang/xacNhan">

        <!-- Giữ lại đúng danh sách sản phẩm đã chọn từ giỏ hàng -->
        <!-- Trường ẩn lưu danh sách id sản phẩm giỏ hàng đã chọn, sẽ gửi kèm khi submit form đặt hàng -->
        <input type="hidden" name="selectedIds" value="<?php echo htmlspecialchars(isset($selectedIdsStr) ? $selectedIdsStr : ''); ?>">

        <div class="checkout-grid">
            <!-- CỘT TRÁI -->
            <div>
                <div class="card">
                    <div class="sec-head" style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="sec-num">1</div>
                            <div class="sec-title">Địa chỉ nhận hàng</div>
                        </div>
                        <!-- NÚT MỞ MODAL THÊM ĐỊA CHỈ MỚI -->
                        <button type="button" class="btn-add-addr-modal" onclick="moModalDiaChi()">
                            <i class="fa-solid fa-plus"></i> Thêm địa chỉ mới
                        </button>
                    </div>

                    <?php
                    // $diaChiMacDinh: địa chỉ được dùng làm mặc định để chọn sẵn khi vào trang
                    // (ưu tiên địa chỉ có laMacDinh = true, nếu không có thì lấy địa chỉ đầu tiên trong danh sách)
                    $diaChiMacDinh = null;
                    if (!empty($diaChiList)) {
                        foreach ($diaChiList as $dc) {
                            if ($dc['laMacDinh']) {
                                $diaChiMacDinh = $dc;
                                break;
                            }
                        }
                        if (!$diaChiMacDinh) {
                            $diaChiMacDinh = $diaChiList[0];
                        }
                    }
                    ?>

                    <?php if (!empty($diaChiList)): ?>
                        <!-- Danh sách địa chỉ đã lưu -->
                        <div id="savedAddrList" style="display:grid; gap:10px; margin-bottom:16px;">
                            <?php // Duyệt qua từng địa chỉ đã lưu để hiển thị dạng radio option chọn địa chỉ ?>
                            <?php foreach ($diaChiList as $dc): ?>
                                <label class="addr-select-option<?php echo ($diaChiMacDinh && $diaChiMacDinh['idDiaChi'] == $dc['idDiaChi']) ? ' selected' : ''; ?>">
                                    <input type="radio" name="diaChiChon" value="<?php echo $dc['idDiaChi']; ?>"
                                        <?php echo ($diaChiMacDinh && $diaChiMacDinh['idDiaChi'] == $dc['idDiaChi']) ? 'checked' : ''; ?>
                                        data-ten="<?php echo htmlspecialchars($dc['tenNguoiNhan']); ?>"
                                        data-sdt="<?php echo htmlspecialchars($dc['soDienThoaiNhan']); ?>"
                                        data-diachi="<?php echo htmlspecialchars($dc['diaChiChiTiet']); ?>">
                                    <div class="addr-icon"><i class="fa-solid fa-location-dot"></i></div>
                                    <div class="addr-body">
                                        <div class="addr-title-row">
                                            <span class="addr-name"><?php echo htmlspecialchars($dc['tenNguoiNhan']); ?></span>
                                            <?php if ($dc['laMacDinh']): ?><span class="badge-default">Mặc định</span><?php endif; ?>
                                        </div>
                                        <div class="addr-recipient"><?php echo htmlspecialchars($dc['soDienThoaiNhan']); ?></div>
                                        <div class="addr-detail"><?php echo htmlspecialchars($dc['diaChiChiTiet']); ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>

                            <!-- Lựa chọn nhập địa chỉ tạm thời khác (không lưu vào danh sách địa chỉ) -->
                            <label class="addr-select-option" id="optNewAddr">
                                <input type="radio" name="diaChiChon" value="new">
                                <div class="addr-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                                <div class="addr-body">
                                    <div class="addr-title-row"><span class="addr-name">Nhập địa chỉ tạm thời khác</span></div>
                                </div>
                            </label>
                        </div>
                    <?php endif; ?>

                    <!-- Khối nhập tay thông tin người nhận, ẩn mặc định nếu đã có địa chỉ đã lưu -->
                    <div class="addr-item-checkout" id="manualAddrBox" style="<?php echo !empty($diaChiList) ? 'display:none;' : ''; ?>">
                        <div class="addr-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="addr-body">
                            <div class="addr-title-row">
                                <span class="addr-name">Thông tin người nhận</span>
                            </div>
                            <div class="addr-grid">
                                <!-- Họ tên người nhận: ưu tiên lấy từ session user_name, nếu không có thì lấy từ địa chỉ mặc định -->
                                <input class="addr-input" type="text" name="hoTenNguoiNhan" id="f_hoTenNguoiNhan"
                                    placeholder="Họ và tên người nhận"
                                    value="<?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($diaChiMacDinh['tenNguoiNhan']) ? $diaChiMacDinh['tenNguoiNhan'] : (isset( $_SESSION['user_name'] ) ? $_SESSION['user_name'] : ''))); ?>" required>
                                <!-- Số điện thoại người nhận: lấy sẵn từ địa chỉ mặc định (nếu có) -->
                                <input class="addr-input" type="text" name="soDienThoaiNhan" id="f_soDienThoaiNhan"
                                    placeholder="Số điện thoại"
                                    value="<?php echo htmlspecialchars(isset($diaChiMacDinh['soDienThoaiNhan']) ? $diaChiMacDinh['soDienThoaiNhan'] : ''); ?>" required>
                                <!-- Địa chỉ giao hàng chi tiết: lấy sẵn từ địa chỉ mặc định (nếu có) -->
                                <input class="addr-input span-2" type="text" name="diaChiGiaoHang" id="f_diaChiGiaoHang"
                                    placeholder="Địa chỉ giao hàng cụ thể (số nhà, đường, phường/xã, tỉnh/thành)"
                                    value="<?php echo htmlspecialchars(isset($diaChiMacDinh['diaChiChiTiet']) ? $diaChiMacDinh['diaChiChiTiet'] : ''); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DỊCH VỤ / SẢN PHẨM TRONG ĐƠN -->
                <div class="card">
                    <div class="sec-head">
                        <div class="sec-num">2</div>
                        <div class="sec-title">Sản phẩm trong đơn (<?php echo count($cartItems); ?>)</div>
                    </div>
                    <div id="productList">
                        <?php // Duyệt qua từng sản phẩm trong giỏ hàng đã chọn để hiển thị lại trong đơn hàng ?>
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                            // $thanhTien: thành tiền của dòng sản phẩm này = đơn giá * số lượng
                            $thanhTien = $item['donGia'] * $item['soLuong'];
                            ?>
                            <div class="cart-item">
                                <div class="ci-img">
                                    <img src="<?php echo $item['hinhAnhUrl']; ?>" alt="<?php echo htmlspecialchars($item['tenThuoc']); ?>">
                                </div>
                                <div class="ci-info">
                                    <div class="ci-name"><?php echo htmlspecialchars($item['tenThuoc']); ?></div>
                                    <div class="ci-unit"><?php echo number_format($item['donGia'], 0, ',', '.'); ?>đ / <?php echo htmlspecialchars($item['donViTinh']); ?></div>
                                </div>
                                <div class="ci-qty-badge">x<?php echo $item['soLuong']; ?></div>
                                <div class="ci-price"><?php echo number_format($thanhTien, 0, ',', '.'); ?>đ</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI -->
            <div>
                <div class="card">
                    <div class="sec-head">
                        <div class="sec-num">3</div>
                        <div class="sec-title">Chọn phương thức thanh toán</div>
                    </div>
                    <div id="payOptions">
                        <label class="pay-option selected" data-id="cod">
                            <input type="radio" name="phuongThucThanhToan" value="COD" checked>
                            <div class="pay-icon cod"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                            <div class="pay-info">
                                <div class="pay-title">Thanh toán khi nhận hàng (COD)</div>
                                <div class="pay-sub">Thanh toán tiền mặt trực tiếp cho nhân viên giao hàng</div>
                            </div>
                        </label>
                        <label class="pay-option" data-id="bank">
                            <input type="radio" name="phuongThucThanhToan" value="CHUYEN_KHOAN">
                            <div class="pay-icon bank"><i class="fa-solid fa-qrcode"></i></div>
                            <div class="pay-info">
                                <div class="pay-title">Chuyển khoản ngân hàng qua mã QR</div>
                                <div class="pay-sub">Quét mã QR qua ứng dụng Internet Banking của bạn</div>
                            </div>
                        </label>
                        <label class="pay-option" data-id="wallet">
                            <input type="radio" name="phuongThucThanhToan" value="VI_DIEN_TU">
                            <div class="pay-icon wallet"><i class="fa-solid fa-wallet"></i></div>
                            <div class="pay-info">
                                <div class="pay-title">Ví điện tử trực tuyến</div>
                                <div class="pay-sub">Hỗ trợ thanh toán nhanh bằng MoMo, ZaloPay, VNPay</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="card note-box">
                    <div class="sec-head">
                        <div class="sec-num">4</div>
                        <div class="sec-title">Ghi chú đơn hàng</div>
                    </div>
                    <textarea name="ghiChu" placeholder="VD: Giao giờ hành chính, gọi trước khi giao 15 phút..."></textarea>
                </div>
            </div>
        </div>

        <div class="summary-bar">
            <div class="total-block">
                <span class="total-label">Tổng thanh toán:</span>
                <span class="total-value" id="totalValue"><?php echo number_format($tongTien, 0, ',', '.'); ?>đ</span>
            </div>
            <button type="submit" class="continue-btn" id="continueBtn" <?php echo empty($cartItems) ? 'disabled' : ''; ?>>Xác nhận đặt hàng</button>
        </div>
    </form>

</div>

<!-- ══ MODAL THÊM ĐỊA CHỈ MỚI ══ -->
<div class="modal-overlay" id="addrModalOverlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Thêm địa chỉ giao hàng mới</h3>
            <button type="button" class="modal-close" onclick="dongModalDiaChi()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addrForm" onsubmit="return false;">
                <div class="mf-grid">
                    <div class="mfield">
                        <label>Tên người nhận <span class="req">*</span></label>
                        <input type="text" id="mRecipient" placeholder="Ví dụ: Nguyễn Văn A" required>
                    </div>
                    <div class="mfield">
                        <label>Số điện thoại <span class="req">*</span></label>
                        <input type="tel" id="mPhone" placeholder="0912 345 678" required>
                    </div>
                </div>

                <div class="mf-grid full">
                    <div class="mfield">
                        <label>Địa chỉ giao hàng đầy đủ <span class="req">*</span></label>
                        <input type="text" id="mDetail" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành..." required>
                        <div class="hint">VD: 123 Đường Nguyễn Trãi, P. An Bình, Q. Ninh Kiều, TP. Cần Thơ</div>
                    </div>
                </div>

                <div class="check-row">
                    <input type="checkbox" id="mDefault" checked>
                    <label for="mDefault">Đặt làm địa chỉ mặc định</label>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer;" onclick="dongModalDiaChi()">Hủy</button>
            <button type="button" class="btn btn-primary" style="padding: 8px 16px; background: var(--green); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" onclick="guiDiaChi()">
                <i class="fa-solid fa-check"></i> Lưu & Chọn địa chỉ này
            </button>
        </div>
    </div>
</div>

<script>
    // savedAddrList: khu vực chứa danh sách các radio chọn địa chỉ đã lưu
    const savedAddrList = document.getElementById('savedAddrList');
    // manualAddrBox: khối nhập tay thông tin người nhận (hiện khi chọn "Nhập địa chỉ tạm thời khác")
    const manualAddrBox = document.getElementById('manualAddrBox');
    // fHoTen, fSdt, fDiaChi: các input ẩn dùng để gửi thông tin người nhận thực tế khi submit form
    const fHoTen = document.getElementById('f_hoTenNguoiNhan');
    const fSdt = document.getElementById('f_soDienThoaiNhan');
    const fDiaChi = document.getElementById('f_diaChiGiaoHang');

    if (savedAddrList) {
        // Lắng nghe sự kiện chọn địa chỉ (radio) trong danh sách địa chỉ đã lưu
        savedAddrList.querySelectorAll('input[name="diaChiChon"]').forEach(radio => {
            radio.addEventListener('change', () => {
                // Đánh dấu địa chỉ vừa chọn là đang active (selected)
                savedAddrList.querySelectorAll('.addr-select-option').forEach(el => el.classList.remove('selected'));
                radio.closest('.addr-select-option').classList.add('selected');

                if (radio.value === 'new') {
                    // Chọn "Nhập địa chỉ tạm thời khác" -> hiện khối nhập tay và xóa dữ liệu cũ
                    manualAddrBox.style.display = '';
                    fHoTen.value = '';
                    fSdt.value = '';
                    fDiaChi.value = '';
                    fHoTen.focus();
                } else {
                    // Chọn 1 địa chỉ đã lưu -> ẩn khối nhập tay và điền dữ liệu từ data attribute của radio
                    manualAddrBox.style.display = 'none';
                    fHoTen.value = radio.dataset.ten;
                    fSdt.value = radio.dataset.sdt;
                    fDiaChi.value = radio.dataset.diachi;
                }
            });
        });
    }

    // Modal địa chỉ
    const addrModalOverlay = document.getElementById('addrModalOverlay');

    /**
     * Mở modal thêm địa chỉ giao hàng mới, đồng thời điền sẵn tên/số điện thoại
     * người nhận hiện tại (nếu có) để tiện chỉnh sửa.
     */
    function moModalDiaChi() {
        document.getElementById('addrForm').reset();
        document.getElementById('mRecipient').value = fHoTen.value || '';
        document.getElementById('mPhone').value = fSdt.value || '';
        addrModalOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Đóng modal thêm địa chỉ giao hàng mới.
     */
    function dongModalDiaChi() {
        addrModalOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Đóng modal khi click ra ngoài vùng modal-box (click trực tiếp lên lớp overlay)
    addrModalOverlay.addEventListener('click', (e) => {
        if (e.target === addrModalOverlay) dongModalDiaChi();
    });

    // Thêm địa chỉ mới qua AJAX tới API thongTinCaNhan/themDiaChi
    /**
     * Gửi thông tin địa chỉ mới lên server để lưu vào danh mục địa chỉ của khách hàng.
     */
    function guiDiaChi() {
        // recipient, phone, detail: thông tin địa chỉ mới nhập trong modal, đã trim khoảng trắng thừa
        const recipient = document.getElementById('mRecipient').value.trim();
        const phone = document.getElementById('mPhone').value.trim();
        const detail = document.getElementById('mDetail').value.trim();
        // isDefault: có đặt địa chỉ này làm mặc định hay không
        const isDefault = document.getElementById('mDefault').checked;

        // Kiểm tra đầy đủ các trường bắt buộc trước khi gửi lên server
        if (!recipient || !phone || !detail) {
            alert('Vui lòng điền đầy đủ các trường thông tin địa chỉ (*)');
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
                    dongModalDiaChi();
                    // Tải lại trang để tự động cập nhật danh sách địa chỉ mới được thêm vào CSDL
                    window.location.reload();
                } else {
                    alert(res.message || 'Thêm địa chỉ thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Không thể kết nối đến máy chủ.'));
    }

    // Đổi trạng thái khi chọn phương thức thanh toán
    // Lắng nghe sự kiện click vào từng lựa chọn phương thức thanh toán để đánh dấu selected
    document.querySelectorAll('.pay-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.pay-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            opt.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Xác nhận đặt hàng
    // Yêu cầu xác nhận lần cuối trước khi submit form đặt hàng lên server
    document.getElementById('checkoutForm').addEventListener('submit', (e) => {
        if (!confirm('Xác nhận đặt hàng với các sản phẩm và địa chỉ trên?')) {
            e.preventDefault();
        }
    });
</script>