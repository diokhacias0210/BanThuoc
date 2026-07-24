<!--
  VIEW trang Thanh toán & Đặt hàng
  Hỗ trợ: Chọn địa chỉ có sẵn, Thêm địa chỉ mới qua Modal, Chọn phương thức thanh toán.
-->
<style>
    /* CSS Chọn địa chỉ */
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

    /* CSS Modal Thêm địa chỉ */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 300;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal-box {
        background: #fff;
        border-radius: 14px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border, #e5e7eb);
    }

    .modal-head h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--green-dark, #15803d);
        margin: 0;
    }

    .modal-close {
        border: none;
        background: none;
        font-size: 18px;
        cursor: pointer;
        color: var(--muted, #6b7280);
    }

    .modal-body {
        padding: 20px;
        max-height: calc(80vh - 120px);
        overflow-y: auto;
    }

    .mf-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    .mf-grid.full {
        grid-template-columns: 1fr;
    }

    .mfield label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        margin-bottom: 4px;
        color: var(--text, #1f2937);
    }

    .mfield label .req {
        color: var(--red, #dc2626);
    }

    .mfield input,
    .mfield textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        font-family: inherit;
    }

    .mfield input:focus,
    .mfield textarea:focus {
        border-color: var(--green, #16a34a);
    }

    .mfield .hint {
        font-size: 11px;
        color: var(--muted, #6b7280);
        margin-top: 3px;
    }

    .check-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        margin-top: 10px;
        cursor: pointer;
    }

    .modal-foot {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 20px;
        background: #f9fafb;
        border-top: 1px solid var(--border, #e5e7eb);
    }

    .btn-add-addr-modal {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--green, #16a34a);
        background: var(--green-light, #f0fdf4);
        border: 1px solid #bcded0;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-add-addr-modal:hover {
        background: var(--green, #16a34a);
        color: #fff;
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

        <!-- Giữ lại đúng danh sách sản phẩm đã chọn từ giỏ hàng -->
        <input type="hidden" name="selectedIds" value="<?php echo htmlspecialchars($selectedIdsStr ?? ''); ?>">

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
                        <button type="button" class="btn-add-addr-modal" onclick="openAddressModal()">
                            <i class="fa-solid fa-plus"></i> Thêm địa chỉ mới
                        </button>
                    </div>

                    <?php
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

                            <label class="addr-select-option" id="optNewAddr">
                                <input type="radio" name="diaChiChon" value="new">
                                <div class="addr-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                                <div class="addr-body">
                                    <div class="addr-title-row"><span class="addr-name">Nhập địa chỉ tạm thời khác</span></div>
                                </div>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="addr-item-checkout" id="manualAddrBox" style="<?php echo !empty($diaChiList) ? 'display:none;' : ''; ?>">
                        <div class="addr-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="addr-body">
                            <div class="addr-title-row">
                                <span class="addr-name">Thông tin người nhận</span>
                            </div>
                            <div class="addr-grid">
                                <input class="addr-input" type="text" name="hoTenNguoiNhan" id="f_hoTenNguoiNhan"
                                    placeholder="Họ và tên người nhận"
                                    value="<?php echo htmlspecialchars($diaChiMacDinh['tenNguoiNhan'] ?? ($_SESSION['user_name'] ?? '')); ?>" required>
                                <input class="addr-input" type="text" name="soDienThoaiNhan" id="f_soDienThoaiNhan"
                                    placeholder="Số điện thoại"
                                    value="<?php echo htmlspecialchars($diaChiMacDinh['soDienThoaiNhan'] ?? ''); ?>" required>
                                <input class="addr-input span-2" type="text" name="diaChiGiaoHang" id="f_diaChiGiaoHang"
                                    placeholder="Địa chỉ giao hàng cụ thể (số nhà, đường, phường/xã, tỉnh/thành)"
                                    value="<?php echo htmlspecialchars($diaChiMacDinh['diaChiChiTiet'] ?? ''); ?>" required>
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

<!-- ══ MODAL THÊM ĐỊA CHỈ MỚI ══ -->
<div class="modal-overlay" id="addrModalOverlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Thêm địa chỉ giao hàng mới</h3>
            <button type="button" class="modal-close" onclick="closeAddressModal()">
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
            <button type="button" class="btn btn-ghost" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer;" onclick="closeAddressModal()">Hủy</button>
            <button type="button" class="btn btn-primary" style="padding: 8px 16px; background: var(--green); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" onclick="submitAddress()">
                <i class="fa-solid fa-check"></i> Lưu & Chọn địa chỉ này
            </button>
        </div>
    </div>
</div>

<script>
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

    // Modal địa chỉ
    const addrModalOverlay = document.getElementById('addrModalOverlay');

    function openAddressModal() {
        document.getElementById('addrForm').reset();
        document.getElementById('mRecipient').value = fHoTen.value || '';
        document.getElementById('mPhone').value = fSdt.value || '';
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

    // Thêm địa chỉ mới qua AJAX tới API thongTinCaNhan/themDiaChi
    function submitAddress() {
        const recipient = document.getElementById('mRecipient').value.trim();
        const phone = document.getElementById('mPhone').value.trim();
        const detail = document.getElementById('mDetail').value.trim();
        const isDefault = document.getElementById('mDefault').checked;

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
                    closeAddressModal();
                    // Tải lại trang để tự động cập nhật danh sách địa chỉ mới được thêm vào CSDL
                    window.location.reload();
                } else {
                    alert(res.message || 'Thêm địa chỉ thất bại, vui lòng thử lại.');
                }
            })
            .catch(() => alert('Không thể kết nối đến máy chủ.'));
    }

    // Đổi trạng thái khi chọn phương thức thanh toán
    document.querySelectorAll('.pay-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.pay-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            opt.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Xác nhận đặt hàng
    document.getElementById('checkoutForm').addEventListener('submit', (e) => {
        if (!confirm('Xác nhận đặt hàng với các sản phẩm và địa chỉ trên?')) {
            e.preventDefault();
        }
    });
</script>