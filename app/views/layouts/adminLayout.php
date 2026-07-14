<?php
require_once 'adminPartials/adminHeader.php';
require_once 'adminPartials/adminNavbar.php';
?>

<main class="main">
    <header class="topbar">
        <div class="page-heading">
            <div class="icon-wrap">
                <div class="icon <?php echo $page_icon ?? 'icon-grid-item'; ?>"></div>
            </div>
            <div class="page-title"><?php echo $page_title ?? 'Quản lý hệ thống'; ?></div>
        </div>
        <?php if (!empty($topbar_action)): ?>
            <?php echo $topbar_action; ?>
        <?php endif; ?>
    </header>

    <section class="content">
        <?php echo $content; ?>
    </section>
</main>

<?php
require_once 'adminPartials/adminFooter.php';
?>