<?php
// Giải nén mảng $data từ Controller thành các biến đơn ($thuoc, $isKeDon, $maLoTxt, $anhChinhUrl,...)
if (isset($data) && is_array($data)) {
    extract($data);
}
?>

<div class="wrap">
    <div class="nav-action-bar">
        <a href="<?php echo URLROOT; ?>/khachHang/thuoc" class="btn-back-nav">
            <i class="fa-solid fa-arrow-left-long"></i> Quay lại danh sách sản phẩm
        </a>
    </div>

    <!-- PHẦN 1: THẺ THÔNG TIN CHÍNH SẢN PHẨM -->
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

            <?php if (isset($isKeDon) && $isKeDon): ?>
                <div class="rx-alert-banner">
                    <i class="fa-solid fa-file-waveform"></i> Thuốc bán theo đơn - Chỉ mua khi có chỉ định của bác sĩ
                </div>
            <?php endif; ?>

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
                <?php if (isset($isKeDon) && !$isKeDon): ?>
                    <!-- GIAO DIỆN CHO THUỐC KHÔNG KÊ ĐƠN (OTC) -->
                    <div class="qty-row">
                        <div class="qty-box">
                            <button type="button" onclick="xuLyThayDoiSoLuong(-1)"><i class="fa-solid fa-minus"></i></button>
                            <span class="qty-val" id="qtyVal">1</span>
                            <button type="button" onclick="xuLyThayDoiSoLuong(1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <span style="font-size:13px; color:var(--muted);">Đơn vị: <?php echo htmlspecialchars(isset($thuoc['donViTinh']) ? $thuoc['donViTinh'] : ''); ?></span>
                    </div>

                    <div class="action-row">
                        <button class="btn btn-solid" onclick="xuLyThemGioHang(<?php echo isset($thuoc['idThuoc']) ? $thuoc['idThuoc'] : 0; ?>)">
                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
                        </button>
                    </div>
                <?php else: ?>
                    <!-- GIAO DIỆN CHO THUỐC BẮT BUỘC KÊ ĐƠN (RX) -->
                    <div class="action-row">
                        <a href="<?php echo URLROOT; ?>/khachHang/taiDonThuoc" class="btn btn-rx-consult">
                            <i class="fa-solid fa-file-prescription"></i> Gửi đơn thuốc để tư vấn
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PHẦN 2: HÀM LƯỢNG VÀ CÔNG DỤNG THUỐC BÊN DƯỚI -->
    <div class="bottom-grid">
        <div class="card">
            <div class="info-block-title">
                <i class="fa-solid fa-flask"></i> Hàm lượng & Thành phần
            </div>
            <p class="info-content-text">
                <?php echo nl2br(htmlspecialchars(isset($thuoc['thanhPhan']) ? $thuoc['thanhPhan'] : '')); ?>
                <?php if (!empty($thuoc['hamLuong'])): ?>
                    <br><strong>Hàm lượng biệt dược:</strong> <?php echo htmlspecialchars($thuoc['hamLuong']); ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="card">
            <div class="info-block-title">
                <i class="fa-solid fa-shield-virus"></i> Chỉ định & Công dụng
            </div>
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

    function xuLyThayDoiSoLuong(delta) {
        soLuongHienTai = Math.max(1, soLuongHienTai + delta);
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
        hienThiThongBao(`Đã thêm ${soLuongHienTai} sản phẩm vào giỏ hàng thành công!`);
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