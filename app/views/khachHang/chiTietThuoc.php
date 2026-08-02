<?php
/**
 * View: Chi tiết thuốc
 * Chức năng: Hiển thị đầy đủ thông tin chi tiết của 1 sản phẩm thuốc
 * (hình ảnh, giá bán, tồn kho, hạn sử dụng, hàm lượng, công dụng...),
 * cho phép người dùng chọn số lượng và thêm vào giỏ hàng hoặc đăng kê toa
 * (nếu thuốc yêu cầu kê đơn).
 * Dữ liệu đầu vào: mảng $data (được extract ra các biến $thuoc, $anhChinhUrl,
 * $danhSachAnh, $maxAllowed, $maLoTxt, $nsxTxt, $hsdTxt, $gioiHanTxt...)
 * truyền từ Controller xuống View.
 */

// Nếu Controller truyền dữ liệu dưới dạng mảng $data thì giải nén (extract)
// thành các biến riêng lẻ để sử dụng trực tiếp trong View
if (isset($data) && is_array($data)) {
    extract($data);
}

// $maxAllowedVal: số lượng tối đa được phép mua cho sản phẩm này
// Nếu không có $maxAllowed truyền vào thì mặc định = 0 (không giới hạn hiển thị / hết hàng)
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
                <?php // Kiểm tra tổng tồn kho của thuốc để hiển thị trạng thái Còn hàng / Hết hàng ?>
                <?php if (isset($thuoc['tongTon']) && $thuoc['tongTon'] > 0): ?>
                    <div class="stock-pill"><span class="dot"></span> Còn hàng (<?php echo number_format($thuoc['tongTon']); ?> <?php echo htmlspecialchars($thuoc['donViTinh']); ?>)</div>
                <?php else: ?>
                    <div class="stock-pill out-of-stock" style="color: var(--red);"><span class="dot"></span> Tạm hết hàng</div>
                <?php endif; ?>

                <!-- Ảnh chính của thuốc, lấy từ biến $anhChinhUrl -->
                <img id="mainStageImg" src="<?php echo isset($anhChinhUrl) ? $anhChinhUrl : ''; ?>" alt="<?php echo htmlspecialchars(isset($thuoc['tenThuoc']) ? $thuoc['tenThuoc'] : ''); ?>">
            </div>

            <?php // Chỉ hiển thị dãy ảnh thumbnail khi có nhiều hơn 1 ảnh trong $danhSachAnh ?>
            <?php if (!empty($danhSachAnh) && count($danhSachAnh) > 1): ?>
                <div class="thumb-row" id="thumbRow">
                    <?php foreach ($danhSachAnh as $index => $img): ?>
                        <!-- $index: vị trí ảnh trong danh sách, dùng để đánh dấu ảnh đầu tiên là active -->
                        <!-- $img: mảng chứa thông tin 1 ảnh phụ, gồm đường dẫn 'duongDan' -->
                        <div class="thumb <?php echo ($index === 0) ? 'active' : ''; ?>" data-src="<?php echo $img['duongDan']; ?>">
                            <img src="<?php echo $img['duongDan']; ?>" alt="thumb">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="detail-info">
            <!-- Tên danh mục thuốc, nếu chưa có thì hiển thị mặc định -->
            <div class="cat-line"><?php echo htmlspecialchars(isset($thuoc['tenDanhMuc']) && $thuoc['tenDanhMuc'] ? $thuoc['tenDanhMuc'] : 'Chưa phân loại'); ?></div>
            <h1><?php echo htmlspecialchars(isset($thuoc['tenThuoc']) ? $thuoc['tenThuoc'] : ''); ?></h1>

            <div class="price-block">
                <!-- Giá bán hiện tại của thuốc, định dạng theo chuẩn VNĐ -->
                <span class="price-now"><?php echo number_format(isset($thuoc['giaBan']) ? $thuoc['giaBan'] : 0, 0, ',', '.'); ?>đ</span>
                <span style="font-size:13px; color:var(--muted);"> / <?php echo htmlspecialchars(isset($thuoc['donViTinh']) ? $thuoc['donViTinh'] : ''); ?></span>
            </div>

            <div class="specs">
                <!-- Đơn vị tính lẻ của thuốc (viên, hộp, chai...) -->
                <div class="spec-row"><span class="k">Đơn vị tính lẻ</span><span class="v"><?php echo htmlspecialchars(isset($thuoc['donViTinh']) ? $thuoc['donViTinh'] : ''); ?></span></div>
                <!-- Mã số lô sản xuất của thuốc -->
                <div class="spec-row"><span class="k">Mã số lô thuốc</span><span class="v"><?php echo isset($maLoTxt) ? $maLoTxt : 'Chưa cập nhật'; ?></span></div>
                <!-- Ngày sản xuất -->
                <div class="spec-row"><span class="k">Ngày sản xuất</span><span class="v"><?php echo isset($nsxTxt) ? $nsxTxt : '—'; ?></span></div>
                <!-- Hạn sử dụng -->
                <div class="spec-row"><span class="k">Hạn sử dụng</span><span class="v"><?php echo isset($hsdTxt) ? $hsdTxt : '—'; ?></span></div>
                <!-- Giới hạn số lượng tối đa được phép mua trong 1 đơn hàng -->
                <div class="spec-row"><span class="k">Giới hạn mua tối đa</span><span class="v"><?php echo isset($gioiHanTxt) ? $gioiHanTxt : 'Không giới hạn'; ?></span></div>
            </div>

            <div class="sticky-actions">
                <?php // Chỉ hiển thị khu vực chọn số lượng & nút hành động khi thuốc còn được phép mua (maxAllowedVal > 0) ?>
                <?php if ($maxAllowedVal > 0): ?>
                    <div class="qty-row">
                        <div class="qty-box">
                            <!-- Nút giảm số lượng, gọi hàm JS xuLyThayDoiSoLuong với delta = -1 -->
                            <button type="button" onclick="xuLyThayDoiSoLuong(-1)"><i class="fa-solid fa-minus"></i></button>
                            <span class="qty-val" id="qtyVal">1</span>
                            <!-- Nút tăng số lượng, gọi hàm JS xuLyThayDoiSoLuong với delta = +1 -->
                            <button type="button" onclick="xuLyThayDoiSoLuong(1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <span style="font-size:13px; color:var(--muted);">Đơn vị: <?php echo htmlspecialchars(isset($thuoc['donViTinh']) ? $thuoc['donViTinh'] : ''); ?> (Tối đa <?php echo $maxAllowedVal; ?>)</span>
                    </div>

                    <div class="action-row">
                        <?php // Nếu thuốc yêu cầu kê đơn thì chuyển hướng sang trang đăng kê toa thay vì thêm giỏ hàng trực tiếp ?>
                        <?php if (isset($thuoc['yeuCauKeDon']) && $thuoc['yeuCauKeDon'] === 'Kê đơn'): ?>
                            <button class="btn btn-solid" onclick="window.location.href='<?php echo URLROOT; ?>/khachHang/dangKeToaThuoc?idThuoc=<?php echo isset($thuoc['idThuoc']) ? $thuoc['idThuoc'] : 0; ?>'">
                                <i class="fa-solid fa-file-prescription"></i> Đăng kê toa thuốc
                            </button>
                        <?php else: ?>
                            <!-- Nút thêm vào giỏ hàng, truyền idThuoc cho hàm JS xuLyThemGioHang -->
                            <button class="btn btn-solid" onclick="xuLyThemGioHang(<?php echo isset($thuoc['idThuoc']) ? $thuoc['idThuoc'] : 0; ?>)">
                                <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Trường hợp không được phép mua (hết hàng), hiển thị nút vô hiệu hóa -->
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
                <!-- Thành phần của thuốc, nl2br giữ nguyên xuống dòng theo dữ liệu gốc -->
                <?php echo nl2br(htmlspecialchars(isset($thuoc['thanhPhan']) ? $thuoc['thanhPhan'] : '')); ?>
                <?php if (!empty($thuoc['hamLuong'])): ?>
                    <!-- Hàm lượng biệt dược, chỉ hiển thị khi có dữ liệu -->
                    <br><strong>Hàm lượng biệt dược:</strong> <?php echo htmlspecialchars($thuoc['hamLuong']); ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="card">
            <div class="info-block-title"><i class="fa-solid fa-shield-virus"></i> Chỉ định & Công dụng</div>
            <p class="info-content-text">
                <!-- Công dụng / chỉ định sử dụng của thuốc -->
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
    // soLuongHienTai: số lượng thuốc người dùng đang chọn để mua, mặc định = 1
    let soLuongHienTai = 1;
    // maxAllowed: số lượng tối đa được phép mua, lấy từ biến PHP $maxAllowedVal
    const maxAllowed = <?php echo $maxAllowedVal; ?>;

    /**
     * Xử lý tăng/giảm số lượng thuốc được chọn mua.
     * @param {number} delta - Giá trị thay đổi số lượng (+1 khi bấm tăng, -1 khi bấm giảm)
     * Đảm bảo số lượng không nhỏ hơn 1 và không vượt quá giới hạn maxAllowed.
     */
    function xuLyThayDoiSoLuong(delta) {
        // n: số lượng mới sau khi cộng delta vào số lượng hiện tại
        let n = soLuongHienTai + delta;
        // Không cho phép số lượng nhỏ hơn 1
        if (n < 1) n = 1;
        // Nếu có giới hạn (maxAllowed > 0) và vượt quá thì cảnh báo và ép về giới hạn tối đa
        if (maxAllowed > 0 && n > maxAllowed) {
            alert(`Sản phẩm này giới hạn mua tối đa ${maxAllowed} đơn vị!`);
            n = maxAllowed;
        }
        soLuongHienTai = n;
        // Cập nhật lại số lượng hiển thị trên giao diện
        const qtyElem = document.getElementById('qtyVal');
        if (qtyElem) qtyElem.textContent = soLuongHienTai;
    }

    /**
     * Hiển thị thông báo dạng toast (popup nhỏ góc màn hình) trong 3 giây.
     * @param {string} msg - Nội dung thông báo cần hiển thị
     */
    function hienThiThongBao(msg) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');
        if (toastMsg) toastMsg.textContent = msg;
        toast.classList.add('show');
        // Tự động ẩn thông báo sau 3000ms (3 giây)
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    /**
     * Xử lý thêm thuốc vào giỏ hàng thông qua gọi API (fetch) đến server.
     * @param {number} idThuoc - Mã của thuốc cần thêm vào giỏ hàng
     */
    function xuLyThemGioHang(idThuoc) {
        // qty: số lượng thuốc đang được chọn, lấy từ nội dung hiển thị trên giao diện
        const qty = parseInt(document.getElementById('qtyVal').textContent) || 1;

        // Gọi API thêm vào giỏ hàng bằng phương thức POST
        fetch(`<?php echo URLROOT; ?>/khachHang/gioHang/themVaoGio`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `idThuoc=${idThuoc}&soLuong=${qty}`
            })
            .then(res => res.json())
            .then(res => {
                // res.status: true nếu thêm giỏ hàng thành công
                if (res.status) {
                    hienThiThongBao(res.message);
                    // Cập nhật số lượng hiển thị trên badge giỏ hàng (nếu có)
                    const badge = document.getElementById('cartCountBadge');
                    if (badge && res.cartCount !== undefined) {
                        badge.textContent = res.cartCount;
                    }
                } else if (res.requireLogin) {
                    // Trường hợp chưa đăng nhập, yêu cầu đăng nhập trước khi thêm giỏ hàng
                    alert(res.message);
                    window.location.href = `<?php echo URLROOT; ?>/khachHang/xacThuc/dangNhap`;
                } else {
                    // Các trường hợp lỗi khác từ server
                    alert(res.message || "Thêm giỏ hàng thất bại.");
                }
            })
            .catch(() => alert("Không thể kết nối đến máy chủ."));
    }

    // thumbRow: khu vực chứa các ảnh thumbnail, dùng để lắng nghe sự kiện click chọn ảnh
    const thumbRow = document.getElementById('thumbRow');
    if (thumbRow) {
        // Khi click vào 1 thumbnail, đổi ảnh chính (mainStageImg) và đánh dấu thumbnail đang active
        thumbRow.addEventListener('click', function(e) {
            // Tìm phần tử .thumb gần nhất chứa vị trí click (đề phòng click vào ảnh con bên trong)
            const thumb = e.target.closest('.thumb');
            if (!thumb) return;
            // Bỏ trạng thái active của tất cả thumbnail trước đó
            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            // Đánh dấu thumbnail vừa được click là active
            thumb.classList.add('active');
            // Cập nhật ảnh chính bằng đường dẫn lưu trong data-src của thumbnail
            document.getElementById('mainStageImg').src = thumb.dataset.src;
        });
    }
</script>
