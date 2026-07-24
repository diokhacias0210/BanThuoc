<div class="wrap">
    <div class="cart-card">
        <div class="cart-head" id="cartHead" style="<?php echo empty($cartItems) ? 'display:none;' : ''; ?>">
            <label class="select-all">
                <input type="checkbox" id="selectAll">
                <span id="selectAllLabel">Chọn mua tất cả</span>
            </label>
            <span class="cart-count-pill" id="cartCountPill"><?php echo count($cartItems); ?> sản phẩm</span>
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
                    <i class="fa-solid fa-basket-shopping"></i>
                    <div class="cart-empty-title">Giỏ hàng của bạn đang trống</div>
                    <div class="cart-empty-sub">Hãy quay lại danh sách để thêm dược phẩm nhé</div>
                </div>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <?php
                    $isKhoa = ($item['trangThaiThaoTac'] === 'KHOA');
                    $thanhTien = $item['donGia'] * $item['soLuong'];
                    ?>
                    <div class="cart-item <?php echo $isKhoa ? 'status-pending unchecked' : ''; ?>" data-id="<?php echo $item['id']; ?>">
                        <div class="col-name-wrapper">
                            <!-- Thuốc KHOA (Kê đơn chờ duyệt): Checkbox bị khóa & Bắt buộc Uncheck -->
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
                                    <div class="badge-pending"><i class="fa-solid fa-hourglass-half"></i> Chờ dược sĩ duyệt đơn</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ci-price-cell"><?php echo number_format($item['donGia'], 0, ',', '.'); ?>đ</div>

                        <div class="col-action-wrapper">
                            <div class="qty-box">
                                <button class="qty-btn" <?php echo $isKhoa ? 'disabled' : ''; ?> onclick="thayDoiSoLuong(<?php echo $item['id']; ?>, -1)"><i class="fa-solid fa-minus"></i></button>
                                <span class="qty-val" id="qty_<?php echo $item['id']; ?>"><?php echo $item['soLuong']; ?></span>
                                <button class="qty-btn" <?php echo $isKhoa ? 'disabled' : ''; ?> onclick="thayDoiSoLuong(<?php echo $item['id']; ?>, 1)"><i class="fa-solid fa-plus"></i></button>
                            </div>

                            <!-- NÚT XÓA: Luôn hoạt động cho CẢ THUỐC KÊ ĐƠN & KHÔNG KÊ ĐƠN -->
                            <button class="ci-remove" onclick="xoaItemGioHang(<?php echo $item['id']; ?>)" title="Xoá khỏi giỏ">
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
            <button class="checkout-btn" id="checkoutBtn" disabled>Mua hàng</button>
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
        let countPurchasable = 0;

        const checkboxes = document.querySelectorAll('.ci-check:not([disabled])');
        countPurchasable = checkboxes.length;

        checkboxes.forEach(cb => {
            const itemRow = cb.closest('.cart-item');
            if (cb.checked) {
                countSelected++;
                itemRow.classList.remove('unchecked');
                const id = cb.dataset.id;
                const price = parseFloat(cb.dataset.price);
                const qty = intval(document.getElementById(`qty_${id}`).textContent);
                tongTien += price * qty;
            } else {
                itemRow.classList.add('unchecked');
            }
        });

        document.getElementById('totalValue').textContent = fmtMoney(tongTien);
        const checkoutBtn = document.getElementById('checkoutBtn');
        checkoutBtn.disabled = (countSelected === 0);
        checkoutBtn.textContent = countSelected > 0 ? `Mua hàng (${countSelected})` : 'Mua hàng';

        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.checked = (countPurchasable > 0 && countSelected === countPurchasable);
            document.getElementById('selectAllLabel').textContent = `Chọn mua tất cả (${countSelected}/${countPurchasable})`;
        }
    }

    function thayDoiSoLuong(idChiTiet, delta) {
        const qtyElem = document.getElementById(`qty_${idChiTiet}`);
        let currentQty = parseInt(qtyElem.textContent);
        let newQty = currentQty + delta;
        if (newQty < 1) return;

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

    function intval(str) {
        return parseInt(str, 10) || 0;
    }

    capNhatTongTien();
</script>