<div class="overlay" id="overlay"></div>
<div class="drawer" id="drawer">
    <div class="drawer-head">
        <div class="drawer-logo-icon"><i class="fa-solid fa-notes-medical"></i></div>
        <div>
            <div class="drawer-brand-name">PharmaCare</div>
            <div class="drawer-brand-sub">Nhà thuốc trực tuyến</div>
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
        <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'taidon') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/taiDonThuoc">
            <i class="fa-solid fa-file-prescription"></i>Tải đơn thuốc
        </a>
        <div class="drawer-section">Tài khoản</div>
        <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'donhang') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/donHang">
            <i class="fa-solid fa-box-open"></i>Đơn hàng của tôi
        </a>
        <a class="drawer-item <?php echo (isset($active_tab) && $active_tab == 'canhan') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/khachHang/caNhan">
            <i class="fa-solid fa-user-gear"></i>Thông tin cá nhân
        </a>
    </div>
</div>