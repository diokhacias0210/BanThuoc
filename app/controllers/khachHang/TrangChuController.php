<?php
class trangChuController extends Controller
{
    private $trangchuModel;

    public function __construct()
    {
        $this->trangchuModel = $this->model('trangChuModel');
    }

    public function index()
    {
        // 1. Lấy danh sách thuốc bán chạy (Phổ biến)
        $dsBanChay = $this->trangchuModel->getThuocBanChayNhat(8);
        foreach ($dsBanChay as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh($item['hinhAnh']);
        }

        // 2. Lấy danh sách tất cả thuốc mới nhất
        $dsMoiNhat = $this->trangchuModel->getThuocMoiNhat(12);
        foreach ($dsMoiNhat as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh($item['hinhAnh']);
        }

        $data = [
            'title' => 'PharmaCare – Nhà thuốc trực tuyến',
            'page_title' => 'Trang chủ',
            'active_tab' => 'trangchu',
            'page_css' => 'trangChu',
            'dsBanChay' => $dsBanChay,
            'dsMoiNhat' => $dsMoiNhat
        ];

        // Load View trong Layout
        ob_start();
        $this->view('khachHang/index', $data);
        $content = ob_get_clean();

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }
}
