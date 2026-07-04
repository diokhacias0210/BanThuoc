<?php
// File: app/controllers/TrangChuController.php

class TrangChuController extends Controller
{

    // Trang chủ mặc định
    public function index()
    {
        // Gọi view và truyền tiêu đề trang
        $this->view("khachHang/trangChu/index", ["tieuDe" => "Trang Chủ Bán Thuốc"]);
    }

    // Trang chi tiết thuốc
    public function chiTiet($idThuoc = "")
    {
        $this->view("khachHang/trangChu/chiTiet", ["id" => $idThuoc]);
    }
}
