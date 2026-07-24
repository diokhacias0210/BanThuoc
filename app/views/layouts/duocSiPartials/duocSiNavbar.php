<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-logo">
            <div class="icon icon-brand"></div>
        </div>
        <div>
            <div class="brand-name">PharmaCare</div>
            <div class="brand-sub">Cổng dược sĩ</div>
        </div>
    </div>

    <nav class="nav-group">
        <div class="nav-label">Dược sĩ</div>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'thongtin') ? 'active' : ''; ?>" href="/duocSi/thongTinDuocSi">
            <div class="icon icon-user"></div>
            Thông tin dược sĩ
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'lothuoc') ? 'active' : ''; ?>" href="/duocSi/quanlylo">
            <div class="icon icon-box"></div>
            Quản lý lô thuốc
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'donthuoc') ? 'active' : ''; ?>" href="/duocSi/duyetdon">
            <div class="icon icon-check-list"></div>
            Duyệt thuốc kê đơn
            <span class="dot-badge" id="sidebarBadge" style="display: none;">0</span>
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'dongoi') ? 'active' : ''; ?>" href="/duocSi/donggoi">
            <div class="icon icon-cube"></div>
            Xử lý &amp; đóng gói
        </a>
    </nav>

    <button class="btn-logout-sidebar" id="btnLogout" onclick="handleLogout()">
        <div class="icon icon-logout">
            <div class="icon-logout-line"></div>
        </div>
        Đăng xuất
    </button>
</aside>

<script>
    function handleLogout() {
        if (confirm('Xác nhận đăng xuất khỏi hệ thống PharmaCare?')) {
            window.location.href = '<?php echo URLROOT; ?>/khachHang/xacThuc/dangXuat';
        }
    }
</script>