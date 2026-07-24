<div class="wrap">
    <div class="cart-card">
        <div class="cart-head" id="cartHead" style="<?php echo empty($cartItems) ? 'display:none;' : ''; ?>">
            <label class="select-all">
                <input type="checkbox" id="selectAll">
                <span id="selectAllLabel">Chọn mua tất cả</span>
            </label>
            <span class="cart-count-pill" id="cartCountPill"><?php echo count($cartItems); ?> chủng loại thuốc</span>
        </div>

        <div class="cart-grid-header" id="cartGridHeader" style="<?php echo empty($cartItems) ? 'display:none;' : ''; ?>">
            <div>Tên sản phẩm</div>
            <div>Giá bán</div>
            <div>Thao tác</div>
            <div class="col-num">Tổng tiền</div>
        </div>

        <div id="cartList">
            <?php if (empty($cartItems)): ?>
                <div class="cart-empty">
                    <i class="fa-solid fa-basket-shopping" style="font-size:48px; color:var(--muted2); margin-bottom:12px;"></i>
                    <div class="cart-empty-title" style="font-size:16px; font-weight:700;">Giỏ hàng của bạn đang trống</div>
                    <div class="cart-empty-sub" style="color:var(--muted2); margin-top:4px;">Hãy quay lại danh sách để chọn mua dược phẩm nhé</div>
                </div>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <?php
                    $isKhoa = ($item['trangThaiThaoTac'] === 'KHOA');
                    $thanhTien = $item['donGia'] * $item['soLuong'];
                    $maxAllowed = isset($item['maxAllowed']) ? $item['maxAllowed'] : 999;
                    ?>
                    <div class="cart-item <?php echo $isKhoa ? 'status-pending unchecked' : ''; ?>" data-id="<?php echo $item['id']; ?>" data-max="<?php echo $maxAllowed; ?>">
                        <div class="col-name-wrapper">
                            <input type="checkbox" class="ci-check"
                                data-id="<?php echo $item['id']; ?>"
                                data-price="<?php echo $item['donGia']; ?>"
                                <?php echo $isKhoa ? 'disabled' : 'checked'; ?>
                                onchange="capNhatTongTien()">
                            <div class="ci-img">
                                <img src="<?php echo $item['hinhAnhUrl']; ?>" alt="<?php echo htmlspecialchars($item['tenThuoc']); ?>">
                            </div>
                            <div class="ci-info">
                                <div class="ci-name"><?php echo htmlspecialchars($item['tenThuoc']); ?></div>
                                <div class="ci-brand"><?php echo htmlspecialchars($item['tenDanhMuc'] ? $item['tenDanhMuc'] : 'Dược phẩm'); ?> · <?php echo htmlspecialchars($item['donViTinh']); ?></div>
                                <?php if ($isKhoa): ?>
                                    <div class="badge-pending" style="color:var(--orange); font-size:12px; margin-top:4px;"><i class="fa-solid fa-hourglass-half"></i> Chờ dược sĩ duyệt đơn</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ci-price-cell"><?php echo number_format($item['donGia'], 0, ',', '.'); ?>đ</div>

                        <div class="col-action-wrapper">
                            <div class="qty-box">
                                <button type="button" class="qty-btn" <?php echo $isKhoa ? 'disabled' : ''; ?> onclick="thayDoiSoLuong(<?php echo $item['id']; ?>, -1)"><i class="fa-solid fa-minus"></i></button>
                                <span class="qty-val" id="qty_<?php echo $item['id']; ?>"><?php echo $item['soLuong']; ?></span>
                                <button type="button" class="qty-btn" <?php echo $isKhoa ? 'disabled' : ''; ?> onclick="thayDoiSoLuong(<?php echo $item['id']; ?>, 1)"><i class="fa-solid fa-plus"></i></button>
                            </div>

                            <button type="button" class="ci-remove" onclick="xoaItemGioHang(<?php echo $item['id']; ?>)" title="Xoá khỏi giỏ">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>

                        <div class="ci-total-cell" id="total_<?php echo $item['id']; ?>"><?php echo number_format($thanhTien, 0, ',', '.'); ?>đ</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="cart-footer">
            <a class="back-btn" href="<?php echo URLROOT; ?>/khachHang/thuoc"><i class="fa-solid fa-arrow-left"></i> Tiếp tục mua hàng</a>
            <div class="total-block">
                <span class="total-label">Tổng thanh toán:</span>
                <span class="total-value" id="totalValue">0đ</span>
            </div>
            <button type="button" class="checkout-btn" id="checkoutBtn" disabled>Mua hàng</button>
        </div>
    </div>
</div>

<script>
    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    function capNhatTongTien() {
        let tongTien = 0;
        let countSelected = 0;

        const checkboxes = document.querySelectorAll('.ci-check:not([disabled])');
        const countPurchasable = checkboxes.length;

        checkboxes.forEach(cb => {
            const itemRow = cb.closest('.cart-item');
            if (cb.checked) {
                countSelected++;
                itemRow.classList.remove('unchecked');
                const id = cb.dataset.id;
                const price = parseFloat(cb.dataset.price);
                const qty = parseInt(document.getElementById(`qty_${id}`).textContent) || 0;
                tongTien += price * qty;
            } else {
                itemRow.classList.add('unchecked');
            }
        });

        document.getElementById('totalValue').textContent = fmtMoney(tongTien);
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.disabled = (countSelected === 0);
            checkoutBtn.textContent = countSelected > 0 ? `Mua hàng (${countSelected})` : 'Mua hàng';
        }

        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.checked = (countPurchasable > 0 && countSelected === countPurchasable);
            const selectAllLabel = document.getElementById('selectAllLabel');
            if (selectAllLabel) selectAllLabel.textContent = `Chọn mua tất cả (${countSelected}/${countPurchasable})`;
        }
    }

    function thayDoiSoLuong(idChiTiet, delta) {
        const qtyElem = document.getElementById(`qty_${idChiTiet}`);
        let currentQty = parseInt(qtyElem.textContent) || 1;
        let newQty = currentQty + delta;
        if (newQty < 1) return;

        const cartItem = document.querySelector(`.cart-item[data-id="${idChiTiet}"]`);
        const maxAllowed = cartItem ? parseInt(cartItem.dataset.max || 999) : 999;

        if (newQty > maxAllowed) {
            alert(`Sản phẩm này giới hạn mua tối đa ${maxAllowed} đơn vị!`);
            return;
        }

        fetch(`<?php echo URLROOT; ?>/khachHang/gioHang/capNhatSoLuong`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${idChiTiet}&soLuong=${newQty}`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    qtyElem.textContent = newQty;
                    const cb = document.querySelector(`.ci-check[data-id="${idChiTiet}"]`);
                    const price = parseFloat(cb.dataset.price);
                    document.getElementById(`total_${idChiTiet}`).textContent = fmtMoney(price * newQty);
                    capNhatTongTien();
                } else if (res.message) {
                    alert(res.message);
                }
            });
    }

    function xoaItemGioHang(idChiTiet) {
        if (!confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?")) return;

        fetch(`<?php echo URLROOT; ?>/khachHang/gioHang/xoaItem`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${idChiTiet}`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    const itemRow = document.querySelector(`.cart-item[data-id="${idChiTiet}"]`);
                    if (itemRow) itemRow.remove();
                    capNhatTongTien();

                    const badge = document.getElementById('cartCountBadge');
                    if (badge && res.cartCount !== undefined) {
                        badge.textContent = res.cartCount;
                    }

                    if (document.querySelectorAll('.cart-item').length === 0) {
                        window.location.reload();
                    }
                }
            });
    }

    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            document.querySelectorAll('.ci-check:not([disabled])').forEach(cb => {
                cb.checked = isChecked;
            });
            capNhatTongTien();
        });
    }

    capNhatTongTien();

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            const selectedIds = Array.from(document.querySelectorAll('.ci-check:checked'))
                .map(cb => cb.dataset.id);

            if (selectedIds.length === 0) return;

            window.location.href = `<?php echo URLROOT; ?>/khachHang/thanhToanDatHang?ids=${selectedIds.join(',')}`;
        });
    }
</script>