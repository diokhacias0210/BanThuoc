<?php
if (isset($data) && is_array($data)) {
    extract($data);
}
$maxAllowedVal = isset($maxAllowed) ? intval($maxAllowed) : 0;
?>

<div class="wrap">
    <div class="nav-action-bar">
        <a href="<?php echo URLROOT; ?>/khachHang/thuoc" class="btn-back-nav">
            <i class="fa-solid fa-arrow-left-long"></i> Quay lại danh sách sản phẩm
        </a>
    </div>

    <div class="detail-card">
        <div>
            <div class="detail-stage">
                <?php if (isset($thuoc['tongTon']) && $thuoc['tongTon'] > 0): ?>
                    <div class="stock-pill"><span class="dot"></span> Còn hàng (<?php echo number_format($thuoc['tongTon']); ?> <?php echo htmlspecialchars($thuoc['donViTinh']); ?>)</div>
                <?php else: ?>
                    <div class="stock-pill out-of-stock" style="color: var(--red);"><span class="dot"></span> Tạm hết hàng</div>
                <?php endif; ?>

                <img id="mainStageImg" src="<?php echo isset($anhChinhUrl) ? $anhChinhUrl : ''; ?>" alt="<?php echo htmlspecialchars(isset($thuoc['tenThuoc']) ? $thuoc['tenThuoc'] : ''); ?>">
            </div>

            <?php if (!empty($danhSachAnh) && count($danhSachAnh) > 1): ?>
                <div class="thumb-row" id="thumbRow">
                    <?php foreach ($danhSachAnh as $index => $img): ?>
                        <div class="thumb <?php echo ($index === 0) ? 'active' : ''; ?>" data-src="<?php echo $img['duongDan']; ?>">
                            <img src="<?php echo $img['duongDan']; ?>" alt="thumb">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="detail-info">
            <div class="cat-line"><?php echo htmlspecialchars(isset($thuoc['tenDanhMuc']) && $thuoc['tenDanhMuc'] ? $thuoc['tenDanhMuc'] : 'Chưa phân loại'); ?></div>
            <h1><?php echo htmlspecialchars(isset($thuoc['tenThuoc']) ? $thuoc['tenThuoc'] : ''); ?></h1>

            <div class="price-block">
                <span class="price-now"><?php echo number_format(isset($thuoc['giaBan']) ? $thuoc['giaBan'] : 0, 0, ',', '.'); ?>đ</span>
                <span style="font-size:13px; color:var(--muted);"> / <?php echo htmlspecialchars(isset($thuoc['donViTinh']) ? $thuoc['donViTinh'] : ''); ?></span>
            </div>

            <div class="specs">
                <div class="spec-row"><span class="k">Đơn vị tính lẻ</span><span class="v"><?php echo htmlspecialchars(isset($thuoc['donViTinh']) ? $thuoc['donViTinh'] : ''); ?></span></div>
                <div class="spec-row"><span class="k">Mã số lô thuốc</span><span class="v"><?php echo isset($maLoTxt) ? $maLoTxt : 'Chưa cập nhật'; ?></span></div>
                <div class="spec-row"><span class="k">Ngày sản xuất</span><span class="v"><?php echo isset($nsxTxt) ? $nsxTxt : '—'; ?></span></div>
                <div class="spec-row"><span class="k">Hạn sử dụng</span><span class="v"><?php echo isset($hsdTxt) ? $hsdTxt : '—'; ?></span></div>
                <div class="spec-row"><span class="k">Giới hạn mua tối đa</span><span class="v"><?php echo isset($gioiHanTxt) ? $gioiHanTxt : 'Không giới hạn'; ?></span></div>
            </div>

            <div class="sticky-actions">
                <?php if ($maxAllowedVal > 0): ?>
                    <div class="qty-row">
                        <div class="qty-box">
                            <button type="button" onclick="xuLyThayDoiSoLuong(-1)"><i class="fa-solid fa-minus"></i></button>
                            <span class="qty-val" id="qtyVal">1</span>
                            <button type="button" onclick="xuLyThayDoiSoLuong(1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <span style="font-size:13px; color:var(--muted);">Đơn vị: <?php echo htmlspecialchars(isset($thuoc['donViTinh']) ? $thuoc['donViTinh'] : ''); ?> (Tối đa <?php echo $maxAllowedVal; ?>)</span>
                    </div>

                    <div class="action-row">
                        <button class="btn btn-solid" onclick="xuLyThemGioHang(<?php echo isset($thuoc['idThuoc']) ? $thuoc['idThuoc'] : 0; ?>)">
                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
                        </button>
                    </div>
                <?php else: ?>
                    <div class="action-row">
                        <button class="btn btn-solid" disabled style="background:#888780; cursor:not-allowed; opacity:0.6;">
                            <i class="fa-solid fa-ban"></i> Sản phẩm tạm hết hàng
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bottom-grid">
        <div class="card">
            <div class="info-block-title"><i class="fa-solid fa-flask"></i> Hàm lượng & Thành phần</div>
            <p class="info-content-text">
                <?php echo nl2br(htmlspecialchars(isset($thuoc['thanhPhan']) ? $thuoc['thanhPhan'] : '')); ?>
                <?php if (!empty($thuoc['hamLuong'])): ?>
                    <br><strong>Hàm lượng biệt dược:</strong> <?php echo htmlspecialchars($thuoc['hamLuong']); ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="card">
            <div class="info-block-title"><i class="fa-solid fa-shield-virus"></i> Chỉ định & Công dụng</div>
            <p class="info-content-text">
                <?php echo nl2br(htmlspecialchars(isset($thuoc['congDung']) ? $thuoc['congDung'] : '')); ?>
            </p>
        </div>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="toastMsg">Thao tác thành công</span>
</div>

<script>
    let soLuongHienTai = 1;
    const maxAllowed = <?php echo $maxAllowedVal; ?>;

    function xuLyThayDoiSoLuong(delta) {
        let n = soLuongHienTai + delta;
        if (n < 1) n = 1;
        if (maxAllowed > 0 && n > maxAllowed) {
            alert(`Sản phẩm này giới hạn mua tối đa ${maxAllowed} đơn vị!`);
            n = maxAllowed;
        }
        soLuongHienTai = n;
        const qtyElem = document.getElementById('qtyVal');
        if (qtyElem) qtyElem.textContent = soLuongHienTai;
    }

    function hienThiThongBao(msg) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');
        if (toastMsg) toastMsg.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function xuLyThemGioHang(idThuoc) {
        const qty = parseInt(document.getElementById('qtyVal').textContent) || 1;

        fetch(`<?php echo URLROOT; ?>/khachHang/gioHang/themVaoGio`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `idThuoc=${idThuoc}&soLuong=${qty}`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    hienThiThongBao(res.message);
                    const badge = document.getElementById('cartCountBadge');
                    if (badge && res.cartCount !== undefined) {
                        badge.textContent = res.cartCount;
                    }
                } else if (res.requireLogin) {
                    alert(res.message);
                    window.location.href = `<?php echo URLROOT; ?>/khachHang/xacThuc/dangNhap`;
                } else {
                    alert(res.message || "Thêm giỏ hàng thất bại.");
                }
            })
            .catch(() => alert("Không thể kết nối đến máy chủ."));
    }

    const thumbRow = document.getElementById('thumbRow');
    if (thumbRow) {
        thumbRow.addEventListener('click', function(e) {
            const thumb = e.target.closest('.thumb');
            if (!thumb) return;
            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
            document.getElementById('mainStageImg').src = thumb.dataset.src;
        });
    }
</script>