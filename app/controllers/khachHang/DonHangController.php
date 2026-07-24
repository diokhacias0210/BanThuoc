<?php

class DonHangController extends Controller
{

    private $donHangModel;

    public function __construct()
    {
        $this->donHangModel = $this->model("DonHangModel");
    }

    public function index()
    {
        if (!isset($_SESSION["user"]))
        {
            header("Location: " . URLROOT . "/khachHang/xacThuc/dangNhap");
            exit();
        }

        $idKhachHang = $_SESSION["user"]["idNguoiDung"];

        $danhSachDonHang =
            $this->donHangModel
            ->getDanhSachDonHang($idKhachHang);

        $data = [

            "title" => "Đơn hàng",

            "content" => "khachHang/donHang",

            "danhSachDonHang" => $danhSachDonHang

        ];

        $this->view("layouts/khachHangLayout", $data);
    }

    public function chiTiet($idDonHang)
    {
        $chiTiet =
            $this->donHangModel
            ->getChiTietDonHang($idDonHang);

        $data = [

            "title" => "Chi tiết đơn hàng",

            "content" => "khachHang/chiTietDonHang",

            "chiTiet" => $chiTiet

        ];

        $this->view("layouts/khachHangLayout", $data);
    }
}
