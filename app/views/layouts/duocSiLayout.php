<?php
// Khởi tạo các partials
require_once 'duocSiPartials/duocSiHeader.php';
require_once 'duocSiPartials/duocSiNavbar.php';
?>

<main class="main">
    <header class="topbar">
        <div>
            <div class="page-title"><?php echo $page_title ?? 'Hệ thống quản trị'; ?></div>
        </div>
    </header>

    <section class="content">
        <?php echo $content; ?>
    </section>
</main>

<?php
require_once 'duocSiPartials/duocSiFooter.php';
?>