<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">
            <div class="icon icon-brand"></div>
        </div>
        <div>
            <div class="brand-name">Admin Panel</div>
            <div class="brand-sub">PharmaCare quản trị</div>
        </div>
    </div>
    <nav class="nav-group">
        <div class="nav-label">Điều hướng</div>
        <a class="nav-item <?php echo ($active_tab == 'tongquan') ? 'active' : ''; ?>" href="/admin/tongquan">
            <div class="icon icon-chart">
                <div class="bar-1"></div>
                <div class="bar-2"></div>
                <div class="bar-3"></div>
            </div>
            Tổng quan
        </a>
        <a class="nav-item <?php echo ($active_tab == 'thuoc') ? 'active' : ''; ?>" href="/admin/quanlythuoc">
            <div class="icon icon-grid-item">
                <div class="sq sq-1"></div>
                <div class="sq sq-2"></div>
                <div class="sq sq-3"></div>
                <div class="sq sq-4"></div>
            </div>
            Quản lý thuốc
        </a>
        <a class="nav-item <?php echo ($active_tab == 'danhmuc') ? 'active' : ''; ?>" href="/admin/quanlydanhmuc">
            <div class="icon icon-folder"></div>
            Quản lý danh mục thuốc
        </a>
        <a class="nav-item <?php echo ($active_tab == 'taikhoan') ? 'active' : ''; ?>" href="/admin/quanlytaikhoan">
            <div class="icon icon-user"></div>
            Quản lý tài khoản
        </a>
    </nav>
    <a class="logout-link" href="/xacthuc/dangxuat" onclick="return confirm('Bạn muốn đăng xuất khỏi Admin Panel?');">
        <div class="icon icon-logout">
            <div class="line"></div>
        </div>
        Đăng xuất
    </a>
</aside>