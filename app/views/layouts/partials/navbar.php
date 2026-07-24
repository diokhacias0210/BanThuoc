<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<div class="overlay" id="overlay"></div>
<div class="drawer" id="drawer">
    <div class="drawer-head">
        <div class="drawer-logo-icon"><i class="fa-solid fa-notes-medical"></i></div>
        <div>
            <div class="drawer-brand-name">PharmaCare</div>
            <div class="drawer-brand-sub">
                <?php if ($isLoggedIn): ?>
                    Xin chào, <strong><?php echo htmlspecialchars($userName); ?></strong>
                <?php else: ?>
                    Nhà thuốc trực tuyến
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="drawer-body">
        <div class="drawer-section">Mua hàng</div>
        <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'trangchu') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/trangChu">
            <i class="fa-solid fa-chart-pie"></i>Tổng quan
        </a>
        <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'thuoc') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/thuoc">
            <i class="fa-solid fa-table-list"></i>Danh sách hàng hóa
        </a>
        <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'giohang') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/gioHang">
            <i class="fa-solid fa-cart-shopping"></i>Giỏ hàng
        </a>
        <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'taidon') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/dangKeToaThuoc">
            <i class="fa-solid fa-file-prescription"></i>Tải đơn thuốc
        </a>

        <div class="drawer-section">Tài khoản</div>

        <?php if ($isLoggedIn): ?>
            <!-- KHU VỰC DÀNH CHO TÀI KHOẢN ĐÃ ĐĂNG NHẬP -->
            <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'donhang') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/quanLyDonHang">
                <i class="fa-solid fa-box-open"></i>Đơn hàng của tôi
            </a>
            <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'thongTinCaNhan') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/thongTinCaNhan">
                <i class="fa-solid fa-user-gear"></i>Thông tin cá nhân
            </a>

            <!-- NÚT ĐĂNG XUẤT TÀI KHOẢN -->
            <a class="drawer-item" href="<?php echo URLROOT; ?>/khachHang/xacThuc/dangXuat" style="color: var(--red); margin-top: 10px;">
                <i class="fa-solid fa-right-from-bracket" style="color: var(--red);"></i>Đăng xuất
            </a>
        <?php else: ?>
            <!-- KHU VỰC DÀNH CHO TÀI KHOẢN CHƯA ĐĂNG NHẬP -->
            <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'dangnhap') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/xacThuc/dangNhap">
                <i class="fa-solid fa-right-to-bracket"></i>Đăng nhập
            </a>
            <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'dangky') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/xacThuc/dangKy">
                <i class="fa-solid fa-user-plus"></i>Đăng ký tài khoản
            </a>
        <?php endif; ?>
    </div>
</div>