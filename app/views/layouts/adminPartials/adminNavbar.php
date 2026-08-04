<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">
            <!-- Icon Logo đồng bộ với logo bên khách hàng (fa-notes-medical) -->
            <i class="fa-solid fa-notes-medical"></i>
        </div>
        <div>
            <div class="brand-name">Admin Panel</div>
            <div class="brand-sub">PharmaCare quản trị</div>
        </div>
    </div>
    <nav class="nav-group">
        <div class="nav-label">Điều hướng</div>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'tongquan') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/baoCaoThongKe">
            <i class="fa-solid fa-chart-line"></i>
            Tổng quan
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'thuoc') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/quanLyThuoc">
            <i class="fa-solid fa-pills"></i>
            Quản lý thuốc
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'danhmuc') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/quanLyDanhMuc">
            <i class="fa-solid fa-folder-open"></i>
            Quản lý danh mục thuốc
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'taikhoan') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/quanLyTaiKhoan">
            <i class="fa-solid fa-users-gear"></i>
            Quản lý tài khoản
        </a>
    </nav>
    <a class="logout-link" href="<?php echo URLROOT; ?>/khachHang/xacThuc/dangXuat" onclick="return confirm('Bạn muốn đăng xuất khỏi Admin Panel?');">
        <i class="fa-solid fa-right-from-bracket"></i>
        Đăng xuất
    </a>
</aside>