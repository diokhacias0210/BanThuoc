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
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'thongtin') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/duocSi/thongTinDuocSi">
            <div class="icon icon-user"></div>
            Thông tin dược sĩ
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'lothuoc') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/duocSi/quanLyLo">
            <div class="icon icon-box"></div>
            Quản lý lô thuốc
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'donthuoc') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/duocSi/duyetDon">
            <div class="icon icon-check-list"></div>
            Duyệt thuốc kê đơn
            <span class="dot-badge" id="sidebarBadge" style="display: none;">0</span>
        </a>
        <a class="nav-item <?php echo (isset($active_tab) && $active_tab == 'dongoi') ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/duocSi/dongGoi">
            <div class="icon icon-cube"></div>
            Xử lý &amp; đóng gói
        </a>
    </nav>

    <a class="btn-logout-sidebar" id="btnLogout"  href="<?php echo URLROOT; ?>/khachHang/xacThuc/dangXuat" onclick="return confirm('Xác nhận đăng xuất khỏi hệ thống PharmaCare?');">
        <i class="icon-logout-line"></i>
        Đăng xuất
    </a>
</aside>

<script>
    function handleLogout() {
        if (confirm('Xác nhận đăng xuất khỏi hệ thống PharmaCare?')) {
            window.location.href = '<?php echo URLROOT; ?>/khachHang/xacThuc/dangXuat';
        }
    }
</script>