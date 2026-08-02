<?php
/**
 * View: Giỏ hàng
 * Chức năng: Hiển thị danh sách các thuốc khách hàng đã thêm vào giỏ, cho phép
 * chọn/bỏ chọn từng sản phẩm, tăng giảm số lượng, xóa sản phẩm khỏi giỏ,
 * tự động tính lại tổng tiền theo các sản phẩm đang được chọn, và chuyển sang
 * trang thanh toán với các sản phẩm đã chọn. Các sản phẩm đang chờ dược sĩ
 * duyệt (KHOA) hoặc bị từ chối (TU_CHOI) sẽ không cho phép chọn mua.
 * Dữ liệu đầu vào: biến $cartItems (mảng), mỗi phần tử là 1 dòng sản phẩm
 * trong giỏ hàng (id, tenThuoc, donGia, soLuong, trangThaiThaoTac, maxAllowed...).
 */
?>
<div class="wrap">
    <div class="cart-card">
        <div class="cart-head" id="cartHead" style="<?php echo empty($cartItems) ? 'display:none;' : ''; ?>">
            <label class="select-all">
                <!-- Checkbox chọn/bỏ chọn tất cả sản phẩm có thể mua trong giỏ -->
                <input type="checkbox" id="selectAll">
                <span id="selectAllLabel">Chọn mua tất cả</span>
            </label>
            <!-- Hiển thị tổng số chủng loại thuốc hiện có trong giỏ hàng -->
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
                <!-- Trạng thái giỏ hàng trống -->
                <div class="cart-empty">
                    <i class="fa-solid fa-basket-shopping" style="font-size:48px; color:var(--muted2); margin-bottom:12px;"></i>
                    <div class="cart-empty-title" style="font-size:16px; font-weight:700;">Giỏ hàng của bạn đang trống</div>
                    <div class="cart-empty-sub" style="color:var(--muted2); margin-top:4px;">Hãy quay lại danh sách để chọn mua dược phẩm nhé</div>
                </div>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <?php
                    // $isKhoa: sản phẩm đang bị khóa, chờ dược sĩ duyệt đơn kê toa
                    $isKhoa = ($item['trangThaiThaoTac'] === 'KHOA');
                    // $isTuChoi: sản phẩm đã bị dược sĩ từ chối duyệt đơn
                    $isTuChoi = ($item['trangThaiThaoTac'] === 'TU_CHOI');
                    // $khongChoMua: true nếu sản phẩm thuộc 1 trong 2 trạng thái trên -> không cho phép chọn mua
                    $khongChoMua = ($isKhoa || $isTuChoi);
                    // $thanhTien: thành tiền của dòng sản phẩm này = đơn giá * số lượng
                    $thanhTien = $item['donGia'] * $item['soLuong'];
                    // $maxAllowed: số lượng tối đa được phép mua của sản phẩm này (mặc định 999 nếu không giới hạn)
                    $maxAllowed = isset($item['maxAllowed']) ? $item['maxAllowed'] : 999;
                    ?>
                    <div class="cart-item <?php echo $khongChoMua ? 'status-pending unchecked' : ''; ?> <?php echo $isTuChoi ? 'status-rejected' : ''; ?>" data-id="<?php echo $item['id']; ?>" data-max="<?php echo $maxAllowed; ?>">
                        <div class="col-name-wrapper">
                            <!-- Checkbox chọn mua sản phẩm này, bị vô hiệu hóa nếu $khongChoMua = true -->
                            <input type="checkbox" class="ci-check"
                                data-id="<?php echo $item['id']; ?>"
                                data-price="<?php echo $item['donGia']; ?>"
                                <?php echo $khongChoMua ? 'disabled' : 'checked'; ?>
                                onchange="capNhatTongTien()">
                            <div class="ci-img">
                                <img src="<?php echo $item['hinhAnhUrl']; ?>" alt="<?php echo htmlspecialchars($item['tenThuoc']); ?>">
                            </div>
                            <div class="ci-info">
                                <div class="ci-name"><?php echo htmlspecialchars($item['tenThuoc']); ?></div>
                                <div class="ci-brand"><?php echo htmlspecialchars($item['tenDanhMuc'] ? $item['tenDanhMuc'] : 'Dược phẩm'); ?> · <?php echo htmlspecialchars($item['donViTinh']); ?></div>
                                <?php if ($isKhoa): ?>
                                    <!-- Nhãn báo sản phẩm đang chờ dược sĩ duyệt đơn kê toa -->
                                    <div class="badge-pending" style="color:var(--orange); font-size:12px; margin-top:4px;"><i class="fa-solid fa-hourglass-half"></i> Chờ dược sĩ duyệt đơn</div>
                                <?php elseif ($isTuChoi): ?>
                                    <!-- Nhãn báo sản phẩm đã bị dược sĩ từ chối, kèm lý do (nếu có) -->
                                    <div class="badge-rejected" style="color:#dc2626; font-size:12px; margin-top:4px; font-weight:600;">
                                        <i class="fa-solid fa-circle-xmark"></i> Đã bị dược sĩ từ chối
                                    </div>
                                    <?php if (!empty($item['ghiChuDonThuoc'])): ?>
                                        <div style="color:#dc2626; font-size:12px; margin-top:2px;">
                                            Lý do: <?php echo htmlspecialchars($item['ghiChuDonThuoc']); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Đơn giá của sản phẩm, định dạng theo chuẩn VNĐ -->
                        <div class="ci-price-cell"><?php echo number_format($item['donGia'], 0, ',', '.'); ?>đ</div>

                        <div class="col-action-wrapper">
                            <div class="qty-box">
                                <!-- Nút giảm số lượng, gọi hàm JS thayDoiSoLuong với delta = -1 -->
                                <button type="button" class="qty-btn" <?php echo $khongChoMua ? 'disabled' : ''; ?> onclick="thayDoiSoLuong(<?php echo $item['id']; ?>, -1)"><i class="fa-solid fa-minus"></i></button>
                                <span class="qty-val" id="qty_<?php echo $item['id']; ?>"><?php echo $item['soLuong']; ?></span>
                                <!-- Nút tăng số lượng, gọi hàm JS thayDoiSoLuong với delta = +1 -->
                                <button type="button" class="qty-btn" <?php echo $khongChoMua ? 'disabled' : ''; ?> onclick="thayDoiSoLuong(<?php echo $item['id']; ?>, 1)"><i class="fa-solid fa-plus"></i></button>
                            </div>

                            <!-- Nút xóa sản phẩm này khỏi giỏ hàng -->
                            <button type="button" class="ci-remove" onclick="xoaItemGioHang(<?php echo $item['id']; ?>)" title="Xoá khỏi giỏ">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>

                        <!-- Thành tiền của dòng sản phẩm này (đơn giá * số lượng), định dạng theo chuẩn VNĐ -->
                        <div class="ci-total-cell" id="total_<?php echo $item['id']; ?>"><?php echo number_format($thanhTien, 0, ',', '.'); ?>đ</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="cart-footer">
            <a class="back-btn" href="<?php echo URLROOT; ?>/khachHang/thuoc"><i class="fa-solid fa-arrow-left"></i> Tiếp tục mua hàng</a>
            <div class="total-block">
                <span class="total-label">Tổng thanh toán:</span>
                <!-- Tổng tiền của các sản phẩm đang được chọn, cập nhật bằng hàm capNhatTongTien() -->
                <span class="total-value" id="totalValue">0đ</span>
            </div>
            <!-- Nút thanh toán, mặc định vô hiệu hóa cho đến khi có ít nhất 1 sản phẩm được chọn -->
            <button type="button" class="checkout-btn" id="checkoutBtn" disabled>Mua hàng</button>
        </div>
    </div>
</div>

<script>
    /**
     * Định dạng 1 số tiền thành chuỗi hiển thị theo chuẩn tiền tệ Việt Nam.
     * @param {number} n - Số tiền cần định dạng
     * @returns {string} Chuỗi tiền đã định dạng, kèm ký hiệu "đ"
     */
    function dinhDangTien(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    /**
     * Tính lại tổng tiền thanh toán dựa trên các sản phẩm đang được chọn (checkbox checked),
     * đồng thời cập nhật trạng thái nút "Mua hàng" và checkbox "Chọn mua tất cả".
     */
    function capNhatTongTien() {
        // tongTien: tổng số tiền của các sản phẩm đang được chọn
        let tongTien = 0;
        // countSelected: số lượng sản phẩm đang được chọn
        let countSelected = 0;

        // checkboxes: danh sách các checkbox sản phẩm còn được phép mua (không bị disabled)
        const checkboxes = document.querySelectorAll('.ci-check:not([disabled])');
        // countPurchasable: tổng số sản phẩm có thể mua được (dùng để so sánh với "Chọn mua tất cả")
        const countPurchasable = checkboxes.length;

        checkboxes.forEach(cb => {
            const itemRow = cb.closest('.cart-item');
            if (cb.checked) {
                countSelected++;
                itemRow.classList.remove('unchecked');
                // id, price: mã sản phẩm và đơn giá lấy từ data attribute của checkbox
                const id = cb.dataset.id;
                const price = parseFloat(cb.dataset.price);
                // qty: số lượng hiện tại của sản phẩm này trên giao diện
                const qty = parseInt(document.getElementById(`qty_${id}`).textContent) || 0;
                tongTien += price * qty;
            } else {
                itemRow.classList.add('unchecked');
            }
        });

        document.getElementById('totalValue').textContent = dinhDangTien(tongTien);
        // Cập nhật trạng thái và nhãn của nút thanh toán theo số lượng sản phẩm đang chọn
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.disabled = (countSelected === 0);
            checkoutBtn.textContent = countSelected > 0 ? `Mua hàng (${countSelected})` : 'Mua hàng';
        }

        // Đồng bộ trạng thái checkbox "Chọn mua tất cả" theo số lượng sản phẩm đã chọn / có thể mua
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.checked = (countPurchasable > 0 && countSelected === countPurchasable);
            const selectAllLabel = document.getElementById('selectAllLabel');
            if (selectAllLabel) selectAllLabel.textContent = `Chọn mua tất cả (${countSelected}/${countPurchasable})`;
        }
    }

    /**
     * Thay đổi số lượng của 1 sản phẩm trong giỏ hàng (tăng/giảm) và đồng bộ lên server.
     * @param {number} idChiTiet - Id của dòng chi tiết giỏ hàng cần thay đổi số lượng
     * @param {number} delta - Giá trị thay đổi số lượng (+1 hoặc -1)
     */
    function thayDoiSoLuong(idChiTiet, delta) {
        const qtyElem = document.getElementById(`qty_${idChiTiet}`);
        // currentQty: số lượng hiện tại đang hiển thị trên giao diện
        let currentQty = parseInt(qtyElem.textContent) || 1;
        // newQty: số lượng mới sau khi cộng delta
        let newQty = currentQty + delta;
        // Không cho phép giảm số lượng xuống dưới 1
        if (newQty < 1) return;

        const cartItem = document.querySelector(`.cart-item[data-id="${idChiTiet}"]`);
        // maxAllowed: số lượng tối đa được phép mua của sản phẩm này, lấy từ data-max của dòng giỏ hàng
        const maxAllowed = cartItem ? parseInt(cartItem.dataset.max || 999) : 999;

        // Chặn tăng số lượng vượt quá giới hạn cho phép
        if (newQty > maxAllowed) {
            alert(`Sản phẩm này giới hạn mua tối đa ${maxAllowed} đơn vị!`);
            return;
        }

        // Gửi yêu cầu cập nhật số lượng lên server để đồng bộ dữ liệu giỏ hàng
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
                    // Cập nhật lại số lượng và thành tiền hiển thị trên giao diện sau khi server xác nhận thành công
                    qtyElem.textContent = newQty;
                    const cb = document.querySelector(`.ci-check[data-id="${idChiTiet}"]`);
                    const price = parseFloat(cb.dataset.price);
                    document.getElementById(`total_${idChiTiet}`).textContent = dinhDangTien(price * newQty);
                    capNhatTongTien();
                } else if (res.message) {
                    alert(res.message);
                }
            });
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ hàng sau khi người dùng xác nhận, đồng bộ lên server
     * và cập nhật lại giao diện (bao gồm badge số lượng giỏ hàng).
     * @param {number} idChiTiet - Id của dòng chi tiết giỏ hàng cần xóa
     */
    function xoaItemGioHang(idChiTiet) {
        // Yêu cầu xác nhận trước khi xóa để tránh xóa nhầm
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
                    // Xóa dòng sản phẩm tương ứng khỏi giao diện sau khi server xác nhận xóa thành công
                    const itemRow = document.querySelector(`.cart-item[data-id="${idChiTiet}"]`);
                    if (itemRow) itemRow.remove();
                    capNhatTongTien();

                    // Cập nhật lại số lượng hiển thị trên badge giỏ hàng (nếu có)
                    const badge = document.getElementById('cartCountBadge');
                    if (badge && res.cartCount !== undefined) {
                        badge.textContent = res.cartCount;
                    }

                    // Nếu giỏ hàng không còn sản phẩm nào thì tải lại trang để hiển thị trạng thái giỏ hàng trống
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        window.location.reload();
                    }
                }
            });
    }

    // Lắng nghe sự kiện thay đổi checkbox "Chọn mua tất cả"
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            // isChecked: trạng thái mới của checkbox "Chọn mua tất cả"
            const isChecked = e.target.checked;
            // Áp dụng trạng thái này cho tất cả checkbox sản phẩm còn được phép mua
            document.querySelectorAll('.ci-check:not([disabled])').forEach(cb => {
                cb.checked = isChecked;
            });
            capNhatTongTien();
        });
    }

    // Tính tổng tiền ngay khi tải trang để đồng bộ trạng thái ban đầu
    capNhatTongTien();

    // Lắng nghe sự kiện bấm nút "Mua hàng" để chuyển sang trang thanh toán
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            // selectedIds: danh sách id các sản phẩm đang được chọn để mang sang trang thanh toán
            const selectedIds = Array.from(document.querySelectorAll('.ci-check:checked'))
                .map(cb => cb.dataset.id);

            if (selectedIds.length === 0) return;

            window.location.href = `<?php echo URLROOT; ?>/khachHang/thanhToanDatHang?ids=${selectedIds.join(',')}`;
        });
    }
</script>
