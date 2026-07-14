<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — <?php echo $title ?? 'PharmaCare'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/assets/css/admin/admin.css">
    <?php if (!empty($page_css)): ?>
        <link rel="stylesheet" href="/public/assets/css/admin/<?php echo $page_css; ?>.css">
    <?php endif; ?>
</head>

<body>
    <div class="app">