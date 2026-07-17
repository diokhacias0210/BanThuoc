<?php
// Sử dụng APPROOT để nạp các partials an toàn
require_once APPROOT . '/views/layouts/adminPartials/adminHeader.php';
require_once APPROOT . '/views/layouts/adminPartials/adminNavbar.php';
?>

<main class="main">
    <header class="topbar">
        <div class="page-heading">
            <div class="icon-wrap">
                <i class="<?php echo isset($page_icon) ? $page_icon : 'fa-solid fa-folder'; ?>"></i>
            </div>
            <div class="page-title"><?php echo isset($page_title) ? $page_title : 'Quản lý hệ thống'; ?></div>
        </div>
        <!-- Vùng nút hành động bên phải Topbar -->
        <?php if (!empty($topbar_action)): ?>
            <?php echo $topbar_action; ?>
        <?php endif; ?>
    </header>

    <section class="content">
        <!-- View cụ thể được Render động -->
        <?php echo $content; ?>
    </section>
</main>

<?php
require_once APPROOT . '/views/layouts/adminPartials/adminFooter.php';
?>