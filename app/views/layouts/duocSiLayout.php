<?php
// Khởi tạo các partials
require_once APPROOT . '/views/layouts/duocSiPartials/duocSiHeader.php';
require_once APPROOT . '/views/layouts/duocSiPartials/duocSiNavbar.php';
?>
<main class="main">
    <header class="topbar">
        <div>
            <div class="page-title"><?php echo isset($page_title) ? $page_title : 'Hệ thống quản trị'; ?></div>
        </div>
    </header>

    <section class="content">
        <?php echo $content; ?>
    </section>
</main>

<?php
require_once APPROOT . '/views/layouts/duocSiPartials/duoiSiFooter.php';
?>
