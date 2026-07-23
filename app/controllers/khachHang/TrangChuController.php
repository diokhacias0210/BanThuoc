<?php
class TrangChuController extends Controller
{
    private $trangChuModel;

    public function __construct()
    {
        $this->trangChuModel = $this->model("trangChuModel");
    }

    // Trang chủ khách hàng
    public function index()
    {
        $data['title'] = "PharmaCare – Trang chủ nhà thuốc";
        $data['page_title'] = "Trang chủ";
        $data['active_tab'] = "trangchu";
        $data['page_css'] = "trangChu";

        // 1. Truy vấn các nhóm danh sách sản phẩm từ Model
        $thuocPhoBienRaw = $this->trangChuModel->getThuocPhoBien(8);
        $thuocGoiYRaw = $this->trangChuModel->getThuocGoiY(4);
        $tatCaThuocRaw = $this->trangChuModel->getTatCaThuocKhungLon(6);

        // 2. Chuẩn hóa đường dẫn hình ảnh cho từng danh sách
        $data['thuocPhoBien'] = $this->formatDachSachThuoc($thuocPhoBienRaw);
        $data['thuocGoiY'] = $this->formatDachSachThuoc($thuocGoiYRaw);
        $data['tatCaThuoc'] = $this->formatDachSachThuoc($tatCaThuocRaw);

        ob_start();
        require_once APPROOT . '/views/khachHang/index.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // Hàm phụ chuẩn hóa ảnh cho mảng danh sách thuốc
    private function formatDachSachThuoc($list)
    {
        $result = array();
        if (!empty($list)) {
            foreach ($list as $item) {
                $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh(isset($item['hinhAnh']) ? $item['hinhAnh'] : '');
                $result[] = $item;
            }
        }
        return $result;
    }
}
