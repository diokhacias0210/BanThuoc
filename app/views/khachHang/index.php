<div class="wrap">
    <!-- BANNER KHUYẾN MÃI CHÍNH -->
    <div class="hero">
        <div class="hero-left">
            <div class="hero-title">Mua thuốc chính hãng,<br>giao tận cửa nhà bạn</div>
            <div class="hero-desc">Hàng nghìn loại thuốc từ các nhà sản xuất uy tín, được dược sĩ kiểm duyệt và tư vấn miễn phí.</div>
            <div class="hero-btns">
                <a href="<?php echo URLROOT; ?>/khachHang/thuoc" class="hero-btn-main"><i class="fa-solid fa-magnifying-glass"></i> Tìm thuốc ngay</a>
                <a href="<?php echo URLROOT; ?>/khachHang/taiDonThuoc" class="hero-btn-sec"><i class="fa-solid fa-file-arrow-up"></i> Gửi đơn thuốc</a>
            </div>
        </div>
    </div>

    <!-- HÀNH ĐỘNG NHANH -->
    <div class="quick-actions">
        <a class="qa" href="<?php echo URLROOT; ?>/khachHang/thuoc">
            <div class="qa-icon" style="background:#e8f5ee"><i class="fa-solid fa-prescription-bottle-medical" style="color:#2d7a4f"></i></div>
            <div class="qa-label">Tìm thuốc</div>
            <div class="qa-sub">100.000+ sản phẩm</div>
        </a>
        <a class="qa" href="<?php echo URLROOT; ?>/khachHang/taiDonThuoc">
            <div class="qa-icon" style="background:#fff3e0"><i class="fa-solid fa-file-medical" style="color:#e65100"></i></div>
            <div class="qa-label">Gửi đơn thuốc</div>
            <div class="qa-sub">Kê đơn RX nhanh chóng</div>
        </a>
        <a class="qa" href="<?php echo URLROOT; ?>/khachHang/quanLyDonHang">
            <div class="qa-icon" style="background:#e3f2fd"><i class="fa-solid fa-truck-fast" style="color:#1565c0"></i></div>
            <div class="qa-label">Đơn hàng của tôi</div>
            <div class="qa-sub">Theo dõi lộ trình đơn</div>
        </a>
    </div>

    <!-- 1. PHÂN HỆ THUỐC PHỔ BIẾN (THUỐC BÁN CHẠY NHẤT) -->
    <div class="sec-head">
        <div class="sec-title">Thuốc phổ biến (Bán chạy nhất)</div>
        <a href="<?php echo URLROOT; ?>/khachHang/thuoc" class="sec-more">Xem tất cả <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <div class="popular-grid">
        <?php if (!empty($dsBanChay)): ?>
            <?php foreach ($dsBanChay as $t): ?>
                <?php
                $isKeDon = ($t['yeuCauKeDon'] === 'Kê đơn');
                $hetHang = ($t['tongTon'] <= 0);
                ?>
                <div class="pcard" onclick="window.location.href='<?php echo URLROOT; ?>/khachHang/thuoc/chiTiet/<?php echo $t['idThuoc']; ?>'">
                    <div class="pcard-img">
                        <?php if ($isKeDon): ?>
                            <span class="pcard-tag tag-rx">RX</span>
                        <?php elseif ($hetHang): ?>
                            <span class="pcard-tag" style="background:#fdecea; color:#c0392b; border:1px solid #f9d6d2;">Hết hàng</span>
                        <?php endif; ?>
                        <img src="<?php echo $t['hinhAnhUrl']; ?>" alt="<?php echo htmlspecialchars($t['tenThuoc']); ?>" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="pcard-body">
                        <div class="pcard-name" title="<?php echo htmlspecialchars($t['tenThuoc']); ?>"><?php echo htmlspecialchars($t['tenThuoc']); ?></div>
                        <div class="pcard-foot">
                            <div class="pcard-price"><?php echo number_format($t['giaBan'], 0, ',', '.'); ?>đ</div>

                            <?php if ($isKeDon): ?>
                                <button type="button" class="btn-view-detail">Xem chi tiết</button>
                            <?php elseif ($hetHang): ?>
                                <!-- KHI HẾT HÀNG: VÔ HIỆU HÓA NÚT THÊM NHANH -->
                                <button type="button" class="add-btn" disabled style="opacity: 0.4; cursor: not-allowed; background: #888780;" title="Sản phẩm tạm hết hàng">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="add-btn" onclick="event.stopPropagation(); xuLyThemNhanh(<?php echo $t['idThuoc']; ?>)" title="Thêm vào giỏ">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align:center; color:var(--muted2); padding: 20px;">Đang cập nhật danh sách sản phẩm...</div>
        <?php endif; ?>
    </div>

    <!-- 2. PHÂN HỆ TẤT CẢ SẢN PHẨM (MỚI NHẤT) -->
    <div class="sec-head">
        <div class="sec-title">Tất cả sản phẩm (Mới nhất)</div>
        <a href="<?php echo URLROOT; ?>/khachHang/thuoc" class="sec-more">Xem tất cả <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <div class="all-products-grid">
        <?php if (!empty($dsMoiNhat)): ?>
            <?php foreach ($dsMoiNhat as $t): ?>
                <?php
                $isKeDon = ($t['yeuCauKeDon'] === 'Kê đơn');
                $hetHang = ($t['tongTon'] <= 0);
                ?>
                <a class="pcard-large" href="<?php echo URLROOT; ?>/khachHang/thuoc/chiTiet/<?php echo $t['idThuoc']; ?>">
                    <div class="plarge-img">
                        <img src="<?php echo $t['hinhAnhUrl']; ?>" alt="<?php echo htmlspecialchars($t['tenThuoc']); ?>">
                    </div>
                    <div class="plarge-info">
                        <div class="plarge-name"><?php echo htmlspecialchars($t['tenThuoc']); ?></div>
                        <div class="plarge-foot">
                            <span class="plarge-price"><?php echo number_format($t['giaBan'], 0, ',', '.'); ?>đ</span>

                            <?php if ($isKeDon): ?>
                                <span class="btn-view-detail">Xem chi tiết</span>
                            <?php elseif ($hetHang): ?>
                                <!-- KHI HẾT HÀNG: VÔ HIỆU HÓA NÚT THÊM NHANH -->
                                <button type="button" class="add-btn" disabled style="opacity: 0.4; cursor: not-allowed; background: #888780;" title="Sản phẩm tạm hết hàng">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="add-btn" onclick="event.preventDefault(); event.stopPropagation(); xuLyThemNhanh(<?php echo $t['idThuoc']; ?>)" title="Thêm vào giỏ">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <!-- 3. TIN TỨC SỨC KHỎE (ĐÃ BỎ NÚT "XEM TẤT CẢ") -->
    <div class="sec-head">
        <div class="sec-title">Tin tức sức khoẻ</div>
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
    function xuLyThemNhanh(idThuoc) {
        fetch(`<?php echo URLROOT; ?>/khachHang/gioHang/themVaoGio`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `idThuoc=${idThuoc}&soLuong=1`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    alert(res.message || "Đã thêm sản phẩm vào giỏ!");
                    const badge = document.getElementById('cartCountBadge');
                    if (badge) {
                        badge.textContent = parseInt(badge.textContent || 0) + 1;
                    }
                } else if (res.requireLogin) {
                    alert(res.message);
                    window.location.href = `<?php echo URLROOT; ?>/khachHang/xacThuc/dangNhap`;
                } else {
                    alert(res.message || "Thao tác thất bại");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Lỗi kết nối máy chủ");
            });
    }
</script>