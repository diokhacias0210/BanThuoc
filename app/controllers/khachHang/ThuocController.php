<?php

class ThuocController extends Controller
{
    private $thuocModel;

    public function __construct()
    {
        $this->thuocModel = $this->model("ThuocModel");
    }

    public function chiTiet($idThuoc)
    {
        $thuoc = $this->thuocModel->getById($idThuoc);

        if (!$thuoc) {
            die("Không tìm thấy thuốc.");
        }

        $data = [
            "title"   => "Chi tiết thuốc",
            "content" => "khachHang/chiTiet",
            "thuoc"   => $thuoc
        ];

        $this->view("layouts/khachHangLayout", $data);
    }
}
