<?php

class TrangChuController extends Controller
{
    private $thuoc;

    public function __construct()
    {
        $this->thuoc = $this->model("ThuocModel");
    }

    public function index()
    {
        $data = array(

            "danhSachThuoc"=>$this->thuoc->getAll(),

            "content"=>"khachHang/trangChu/index"

        );

        $this->view(
            "layouts/khachHangLayout",
            $data
        );
    }
}