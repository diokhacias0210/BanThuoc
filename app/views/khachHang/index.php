<div class="wrap">

    <!-- BANNER KHUYẾN MÃI CHÍNH -->
    <div class="hero">
        <div class="hero-left">
            <div class="hero-title">Mua thuốc chính hãng,<br>giao tận cửa nhà bạn</div>
            <div class="hero-desc">Hàng nghìn loại thuốc từ các nhà sản xuất uy tín, được dược sĩ kiểm duyệt và tư vấn miễn phí.</div>
            <div class="hero-btns">
                <a class="hero-btn-main" href="<?php echo URLROOT; ?>/khachHang/thuoc">
                    <i class="fa-solid fa-magnifying-glass"></i> Tìm thuốc ngay
                </a>
                <a class="hero-btn-sec" href="<?php echo URLROOT; ?>/khachHang/taiDonThuoc">
                    <i class="fa-solid fa-file-arrow-up"></i> Gửi đơn thuốc
                </a>
            </div>
        </div>
    </div>

    <!-- HÀNH ĐỘNG NHANH -->
    <div class="quick-actions">
        <a class="qa" href="<?php echo URLROOT; ?>/khachHang/thuoc">
            <div class="qa-icon" style="background:#e8f5ee"><i class="fa-solid fa-prescription-bottle-medical" style="color:#2d7a4f"></i></div>
            <div class="qa-label">Tìm thuốc</div>
            <div class="qa-sub">Hàng nghìn sản phẩm</div>
        </a>
        <a class="qa" href="<?php echo URLROOT; ?>/khachHang/taiDonThuoc">
            <div class="qa-icon" style="background:#fff3e0"><i class="fa-solid fa-file-medical" style="color:#e65100"></i></div>
            <div class="qa-label">Gửi đơn thuốc</div>
            <div class="qa-sub">Kê đơn RX nhanh chóng</div>
        </a>
        <a class="qa" href="<?php echo URLROOT; ?>/khachHang/donHang">
            <div class="qa-icon" style="background:#e3f2fd"><i class="fa-solid fa-truck-fast" style="color:#1565c0"></i></div>
            <div class="qa-label">Đơn hàng của tôi</div>
            <div class="qa-sub">Theo dõi lộ trình đơn</div>
        </a>
    </div>

    <!-- PHÂN HỆ THUỐC PHỔ BIẾN -->
    <div class="sec-head">
        <div class="sec-title">Thuốc phổ biến</div>
        <a class="sec-more" href="<?php echo URLROOT; ?>/khachHang/thuoc">Xem tất cả <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <div class="popular-grid">
        <?php if (!empty($thuocPhoBien)): ?>
            <?php foreach ($thuocPhoBien as $item): ?>
                <?php $isRx = ($item['yeuCauKeDon'] === 'Kê đơn'); ?>
                <a class="pcard" href="<?php echo URLROOT; ?>/khachHang/thuoc/chiTiet/<?php echo $item['idThuoc']; ?>">
                    <div class="pcard-img">
                        <?php if ($isRx): ?><span class="pcard-tag tag-rx">RX</span><?php endif; ?>
                        <img src="<?php echo $item['hinhAnhUrl']; ?>" alt="<?php echo htmlspecialchars($item['tenThuoc']); ?>">
                    </div>
                    <div class="pcard-body">
                        <div class="pcard-name"><?php echo htmlspecialchars($item['tenThuoc']); ?></div>
                        <div class="pcard-foot">
                            <div class="pcard-price"><?php echo number_format($item['giaBan'], 0, ',', '.'); ?>đ</div>
                            <?php if ($isRx): ?>
                                <span class="btn-view-detail">Xem chi tiết</span>
                            <?php else: ?>
                                <button type="button" class="add-btn" onclick="themNhanhGioHang(event, <?php echo $item['idThuoc']; ?>)"><i class="fa-solid fa-plus"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- PHÂN HỆ CÓ THỂ BẠN CẦN -->
    <div class="sec-head">
        <div class="sec-title">Có thể bạn cần</div>
        <a class="sec-more" href="<?php echo URLROOT; ?>/khachHang/thuoc">Xem tất cả <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <div class="popular-grid" style="margin-bottom: 28px;">
        <?php if (!empty($thuocGoiY)): ?>
            <?php foreach ($thuocGoiY as $item): ?>
                <?php $isRx = ($item['yeuCauKeDon'] === 'Kê đơn'); ?>
                <a class="pcard" href="<?php echo URLROOT; ?>/khachHang/thuoc/chiTiet/<?php echo $item['idThuoc']; ?>">
                    <div class="pcard-img">
                        <?php if ($isRx): ?><span class="pcard-tag tag-rx">RX</span><?php endif; ?>
                        <img src="<?php echo $item['hinhAnhUrl']; ?>" alt="<?php echo htmlspecialchars($item['tenThuoc']); ?>">
                    </div>
                    <div class="pcard-body">
                        <div class="pcard-name"><?php echo htmlspecialchars($item['tenThuoc']); ?></div>
                        <div class="pcard-foot">
                            <div class="pcard-price"><?php echo number_format($item['giaBan'], 0, ',', '.'); ?>đ</div>
                            <?php if ($isRx): ?>
                                <span class="btn-view-detail">Xem chi tiết</span>
                            <?php else: ?>
                                <button type="button" class="add-btn" onclick="themNhanhGioHang(event, <?php echo $item['idThuoc']; ?>)"><i class="fa-solid fa-plus"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- PHÂN HỆ TẤT CẢ SẢN PHẨM (3 CỘT LỚN) -->
    <div class="sec-head">
        <div class="sec-title">Sản phẩm nổi bật</div>
        <a class="sec-more" href="<?php echo URLROOT; ?>/khachHang/thuoc">Xem tất cả <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <div class="all-products-grid">
        <?php if (!empty($tatCaThuoc)): ?>
            <?php foreach ($tatCaThuoc as $item): ?>
                <a class="pcard-large" href="<?php echo URLROOT; ?>/khachHang/thuoc/chiTiet/<?php echo $item['idThuoc']; ?>">
                    <div class="plarge-img">
                        <img src="<?php echo $item['hinhAnhUrl']; ?>" alt="<?php echo htmlspecialchars($item['tenThuoc']); ?>">
                    </div>
                    <div class="plarge-info">
                        <div class="plarge-name"><?php echo htmlspecialchars($item['tenThuoc']); ?></div>
                        <div class="plarge-foot">
                            <span class="plarge-price"><?php echo number_format($item['giaBan'], 0, ',', '.'); ?>đ</span>
                            <button type="button" class="add-btn" onclick="themNhanhGioHang(event, <?php echo $item['idThuoc']; ?>)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <!-- TIN TỨC SỨC KHỎE (Chỉ xem) -->
    <div class="sec-head">
        <div class="sec-title">Tin tức sức khoẻ</div>
        <span class="sec-more">Xem tất cả <i class="fa-solid fa-angle-right"></i></span>
    </div>
    <div class="blog-grid">
        <div class="blog-card">
            <div class="blog-img">🌿</div>
            <div class="blog-body">
                <div class="blog-cat">Dinh dưỡng</div>
                <div class="blog-title">Bổ sung vitamin D3 đúng cách cho người trưởng thành</div>
                <div class="blog-meta">10/07/2026 · 5 phút đọc</div>
            </div>
        </div>
        <div class="blog-card">
            <div class="blog-img">💊</div>
            <div class="blog-body">
                <div class="blog-cat">Thuốc & điều trị</div>
                <div class="blog-title">Những điều cần biết khi dùng kháng sinh Amoxicillin</div>
                <div class="blog-meta">08/07/2026 · 7 phút đọc</div>
            </div>
        </div>
        <div class="blog-card">
            <div class="blog-img">❤️</div>
            <div class="blog-body">
                <div class="blog-cat">Tim mạch</div>
                <div class="blog-title">Cách kiểm soát huyết áp tại nhà hiệu quả và an toàn</div>
                <div class="blog-meta">05/07/2026 · 6 phút đọc</div>
            </div>
        </div>
    </div>

</div>

<script>
    function themNhanhGioHang(event, idThuoc) {
        event.preventDefault();
        event.stopPropagation();
        alert("Đã thêm sản phẩm vào giỏ hàng thành công!");
    }
</script>