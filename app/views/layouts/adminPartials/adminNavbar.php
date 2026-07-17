<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">
            <!-- Icon Logo thuốc Font Awesome -->
            <i class="fa-solid fa-capsules"></i>
        </div>
        <div>
            <div class="brand-name">Admin Panel</div>
            <div class="brand-sub">PharmaCare quản trị</div>
        </div>
    </div>
    <nav class="nav-group">
        <div class="nav-label">Điều hướng</div>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'tongquan') ? 'active' : ''; ?>" href="/admin/tongquan">
            <i class="fa-solid fa-chart-line"></i>
            Tổng quan
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'thuoc') ? 'active' : ''; ?>" href="/admin/quanlythuoc">
            <i class="fa-solid fa-pills"></i>
            Quản lý thuốc
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'danhmuc') ? 'active' : ''; ?>" href="/admin/quanlydanhmuc">
            <i class="fa-solid fa-folder-open"></i>
            Quản lý danh mục thuốc
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'taikhoan') ? 'active' : ''; ?>" href="/admin/quanlytaikhoan">
            <i class="fa-solid fa-users-gear"></i>
            Quản lý tài khoản
        </a>
    </nav>
    <a class="logout-link" href="/xacthuc/dangxuat" onclick="return confirm('Bạn muốn đăng xuất khỏi Admin Panel?');">
        <i class="fa-solid fa-right-from-bracket"></i>
        Đăng xuất
    </a>
</aside>