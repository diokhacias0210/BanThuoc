<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-col">
            <div class="footer-logo"><i class="fa-solid fa-notes-medical"></i> <span>PharmaCare</span></div>
            <p class="footer-text">Hệ thống nhà thuốc trực tuyến đạt chuẩn GPP. Chuyên cung ứng dược phẩm, thực phẩm chức năng chính hãng, an toàn và bảo mật.</p>
        </div>
        <div class="footer-col">
            <h4>Thông tin liên hệ</h4>
            <ul class="footer-info-list">
                <li><i class="fa-solid fa-location-dot"></i> 123 Đường Nguyễn Trãi, P. An Bình, Q. Ninh Kiều, TP. Cần Thơ</li>
                <li><i class="fa-solid fa-phone"></i> Hotline: 0912 345 678</li>
                <li><i class="fa-solid fa-envelope"></i> Email: troly@pharmacare.vn</li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Thời gian mở cửa</h4>
            <ul class="footer-info-list">
                <li><i class="fa-solid fa-calendar-days"></i> Thứ 2 — Chủ Nhật</li>
                <li><i class="fa-solid fa-clock"></i> Giờ mở cửa: 06:00 — 22:00</li>
                <li><i class="fa-solid fa-user-doctor"></i> Tư vấn Dược sĩ 24/7</li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Giấy phép hoạt động</h4>
            <p class="footer-text">Số GPDKKD: 0102345678 do Sở Kế hoạch và Đầu tư cấp.</p>
            <p class="footer-text" style="margin-top: 8px;">Số giấy chứng nhận đủ điều kiện kinh doanh dược: 123/GCN-KKDD-CT.</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 PharmaCare. Hệ thống quản lý thông tin nhà thuốc nội bộ.</p>
    </div>
</footer>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const drawer = document.getElementById('drawer');
    const overlay = document.getElementById('overlay');

    if (menuToggle && drawer && overlay) {
        menuToggle.addEventListener('click', () => {
            drawer.classList.add('open');
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
        overlay.addEventListener('click', () => {
            drawer.classList.remove('open');
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
</script>
</body>

</html>