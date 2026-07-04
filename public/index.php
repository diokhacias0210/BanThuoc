<?php
// 1. Khởi tạo session toàn hệ thống
session_start();

// 2. Nhúng các file nền tảng cốt lõi
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';
require_once '../app/config/config.php'; // Nếu chưa có, cứ tạo file trống

// 3. Khởi chạy ứng dụng
$ungDung = new App();
