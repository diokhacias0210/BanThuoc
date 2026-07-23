<?php
// Tự động kiểm tra và khởi tạo Session nếu chưa được bật
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'PharmaCare'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 Icons toàn hệ thống -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS Layout Dùng Chung Khách Hàng -->
    <link rel="stylesheet" href="<?php echo ASSETROOT; ?>/css/Layout/khachHangLayout.css">

    <?php if (!empty($page_css)): ?>
        <link rel="stylesheet" href="<?php echo ASSETROOT; ?>/css/khachHang/<?php echo $page_css; ?>.css">
    <?php endif; ?>
</head>

<body>
    <div class="topbar">
        <div class="topbar-inner">
            <div class="menu-toggle" id="menuToggle">
                <i class="fa-solid fa-bars"></i> <?php echo isset($page_title) ? $page_title : 'PharmaCare'; ?>
            </div>
            <div class="topbar-search">
                <div class="ts-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="globalSearchInput" placeholder="Tìm kiếm sản phẩm, thương hiệu...">
                </div>
            </div>
            <div class="topbar-right">
                <a class="tb-icon" href="<?php echo URLROOT; ?>/khachHang/gioHang" title="Giỏ hàng">
                    <span class="tb-icon-wrap"><i class="fa-solid fa-cart-shopping"></i><span class="tb-badge" id="cartCountBadge">0</span></span>
                </a>

                <!-- THAY ĐỔI BIỂU TƯỢNG TÙY THEO TRẠNG THÁI ĐĂNG NHẬP -->
                <?php if ($isLoggedIn): ?>
                    <!-- KHI ĐÃ ĐĂNG NHẬP: Hiển thị icon Tài khoản đã kích hoạt -->
                    <a class="tb-icon" href="<?php echo URLROOT; ?>/khachHang/thongTinCaNhan" title="Tài khoản: <?php echo htmlspecialchars($userName); ?>">
                        <span class="tb-icon-wrap" style="color: var(--green);"><i class="fa-solid fa-circle-user"></i></span>
                    </a>
                <?php else: ?>
                    <!-- KHI CHƯA ĐĂNG NHẬP: Hiển thị icon Đăng nhập -->
                    <a class="tb-icon" href="<?php echo URLROOT; ?>/khachHang/xacThuc/dangNhap" title="Đăng nhập / Đăng ký">
                        <span class="tb-icon-wrap"><i class="fa-solid fa-right-to-bracket"></i></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>