<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaCare — <?php echo isset($title) ? $title : 'Nhà thuốc trực tuyến'; ?></title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Bootstrap 5 CSS Core -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- CSS Hệ thống dùng chung -->
    <link rel="stylesheet" href="<?php echo ASSETROOT; ?>/css/layout/layout.css">

    <!-- ĐÃ SỬA: Tự động dùng trangChu.css làm mặc định nếu Controller không truyền page_css -->
    <?php if (!empty($page_css)): ?>
        <link rel="stylesheet" href="<?php echo ASSETROOT; ?>/css/khachHang/<?php echo $page_css; ?>.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo ASSETROOT; ?>/css/khachHang/trangChu.css">
    <?php endif; ?>
</head>
<body>
    <div class="app">