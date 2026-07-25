<?php
class DongGoiController extends Controller
{
    private $dongGoiModel;

    public function __construct()
    {
        $this->dongGoiModel = $this->model('DongGoiModel');
    }

    public function index()
    {
        $data['title'] = 'Xử lý & đóng gói';
        $data['page_title'] = 'Xử lý & đóng gói đơn hàng';
        $data['active_tab'] = 'dongoi';
        $data['page_css'] = 'dongGoiDonHang';

        ob_start();
        require_once APPROOT . '/views/duocSi/dongGoi.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/duocSiLayout', $data);
    }
}
