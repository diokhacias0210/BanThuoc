<!--
  VIEW trang Thanh toán & Đặt hàng — chỉ phần main content
  (topbar/drawer/footer dùng chung khachHangLayout, không lặp lại ở đây).
  $cartItems và $tongTien do ThanhToanDatHangController::index() truyền xuống.
  ĐÃ SỬA: ảnh dùng $item['hinhAnhUrl'] (xử lý chuẩn từ Controller) thay vì tự ghép
  đường dẫn thủ công gây sai ảnh. Thêm hidden field selectedIds để giữ đúng danh sách
  sản phẩm đã chọn khi submit sang bước xacNhan().
-->
<style>
    .addr-select-option {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 10px;
        padding: 12px 14px;
        cursor: pointer;
        transition: all .15s;
    }
    .addr-select-option.selected {
        border-color: var(--green, #16a34a);
        background: var(--green-light, #f0fdf4);
    }
    .addr-select-option input[type="radio"] {
        margin-top: 3px;
    }
    .addr-select-option .addr-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .addr-select-option .badge-default {
        font-size: 11px;
        font-weight: 600;
        color: var(--green-dark, #15803d);
        background: var(--green-light, #dcfce7);
        padding: 2px 8px;
        border-radius: 20px;
    }
    .addr-select-option .addr-recipient,
    .addr-select-option .addr-detail {
        font-size: 13px;
        color: var(--muted, #6b7280);
    }
</style>
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

        <!-- Giữ lại đúng danh sách sản phẩm đã chọn từ giỏ hàng để bước xacNhan() xử lý đúng -->
        <input type="hidden" name="selectedIds" value="<?php echo htmlspecialchars($selectedIdsStr ?? ''); ?>">

        <div class="checkout-grid">
            <!-- CỘT TRÁI -->
            <div>
                <div class="card">
                    <div class="sec-head">
                        <div class="sec-num">1</div>
                        <div class="sec-title">Địa chỉ nhận hàng</div>
                    </div>

                    <?php
                        // Xác định địa chỉ mặc định (nếu có) để chọn sẵn khi vào trang
                        $diaChiMacDinh = null;
                        foreach ($diaChiList as $dc) {
                            if ($dc['laMacDinh']) { $diaChiMacDinh = $dc; break; }
                        }
                        if (!$diaChiMacDinh && !empty($diaChiList)) {
                            $diaChiMacDinh = $diaChiList[0];
                        }
                    ?>

                    <?php if (!empty($diaChiList)): ?>
                        <!-- Danh sách địa chỉ đã lưu (bảng DiaChiGiaoHang) để chọn nhanh -->
                        <div id="savedAddrList" style="display:grid; gap:10px; margin-bottom:16px;">
                            <?php foreach ($diaChiList as $dc): ?>
                                <label class="addr-select-option<?php echo $dc['laMacDinh'] ? ' selected' : ''; ?>">
                                    <input type="radio" name="diaChiChon" value="<?php echo $dc['idDiaChi']; ?>"
                                        <?php echo $dc['laMacDinh'] ? 'checked' : ''; ?>
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

                            <label class="addr-select-option" id="optNewAddr">
                                <input type="radio" name="diaChiChon" value="new">
                                <div class="addr-icon"><i class="fa-solid fa-plus"></i></div>
                                <div class="addr-body">
                                    <div class="addr-title-row"><span class="addr-name">Nhập địa chỉ khác</span></div>
                                </div>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="addr-item-checkout" id="manualAddrBox" style="<?php echo !empty($diaChiList) ? 'display:none;' : ''; ?>">
                        <div class="addr-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="addr-body">
                            <div class="addr-title-row">
                                <span class="addr-name">Người nhận</span>
                            </div>
                            <div class="addr-grid">
                                <input class="addr-input" type="text" name="hoTenNguoiNhan" id="f_hoTenNguoiNhan"
                                    placeholder="Họ và tên người nhận"
                                    value="<?php echo htmlspecialchars($diaChiMacDinh['tenNguoiNhan'] ?? ($_SESSION['user']['hoTen'] ?? '')); ?>" required>
                                <input class="addr-input" type="text" name="soDienThoaiNhan" id="f_soDienThoaiNhan"
                                    placeholder="Số điện thoại"
                                    value="<?php echo htmlspecialchars($diaChiMacDinh['soDienThoaiNhan'] ?? ($_SESSION['user']['soDienThoai'] ?? '')); ?>" required>
                                <input class="addr-input span-2" type="text" name="diaChiGiaoHang" id="f_diaChiGiaoHang"
                                    placeholder="Địa chỉ giao hàng cụ thể (số nhà, đường, phường/xã, tỉnh/thành)"
                                    value="<?php echo htmlspecialchars($diaChiMacDinh['diaChiChiTiet'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="sec-head">
                        <div class="sec-num">2</div>
                        <div class="sec-title">Sản phẩm trong đơn (<?php echo count($cartItems); ?>)</div>
                    </div>
                    <div id="productList">
                        <?php foreach ($cartItems as $item): ?>
                            <?php $thanhTien = $item['donGia'] * $item['soLuong']; ?>
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

<script>
    // Chọn địa chỉ có sẵn -> tự điền vào các ô input (vẫn giữ name để form submit đúng)
    // Chọn "Nhập địa chỉ khác" -> xoá trắng để người dùng tự nhập
    const savedAddrList = document.getElementById('savedAddrList');
    const manualAddrBox = document.getElementById('manualAddrBox');
    const fHoTen = document.getElementById('f_hoTenNguoiNhan');
    const fSdt = document.getElementById('f_soDienThoaiNhan');
    const fDiaChi = document.getElementById('f_diaChiGiaoHang');

    if (savedAddrList) {
        savedAddrList.querySelectorAll('input[name="diaChiChon"]').forEach(radio => {
            radio.addEventListener('change', () => {
                savedAddrList.querySelectorAll('.addr-select-option').forEach(el => el.classList.remove('selected'));
                radio.closest('.addr-select-option').classList.add('selected');

                if (radio.value === 'new') {
                    manualAddrBox.style.display = '';
                    fHoTen.value = '';
                    fSdt.value = '';
                    fDiaChi.value = '';
                    fHoTen.focus();
                } else {
                    manualAddrBox.style.display = 'none';
                    fHoTen.value = radio.dataset.ten;
                    fSdt.value = radio.dataset.sdt;
                    fDiaChi.value = radio.dataset.diachi;
                }
            });
        });
    }

    // Đổi trạng thái "selected" khi chọn phương thức thanh toán khác
    document.querySelectorAll('.pay-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.pay-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            opt.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Xác nhận trước khi đặt hàng
    document.getElementById('checkoutForm').addEventListener('submit', (e) => {
        if (!confirm('Xác nhận đặt hàng với các sản phẩm và địa chỉ trên?')) {
            e.preventDefault();
        }
    });
</script>
