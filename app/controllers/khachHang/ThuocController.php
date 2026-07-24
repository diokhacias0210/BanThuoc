<?php
class thuocController extends Controller
{
    private $danhSachThuocModel;

    public function __construct()
    {
        $this->danhSachThuocModel = $this->model('danhSachThuocModel');
    }

    public function index()
    {
        // Lấy danh mục thuốc & tất cả danh sách thuốc có kiểm tra số lượng tồn
        $danhMucList = $this->danhSachThuocModel->getAllDanhMuc();
        $thuocList = $this->danhSachThuocModel->getDanhSachThuocFull();

        foreach ($thuocList as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh($item['hinhAnh']);
        }

        $data = [
            'title' => 'Danh sách sản phẩm thuốc – PharmaCare',
            'page_title' => 'Danh sách hàng hóa',
            'active_tab' => 'thuoc',
            'page_css' => 'danhSachThuoc',
            'danhMucList' => $danhMucList,
            'thuocList' => $thuocList
        ];

        ob_start();
        $this->view('khachHang/danhSachThuoc', $data);
        $content = ob_get_clean();

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }

    // API Tìm kiếm Ajax cho Thanh Search trên Header
    public function timKiemAjax()
    {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (empty($q)) {
            echo json_encode([]);
            exit;
        }

        $results = $this->danhSachThuocModel->timKiemThuocAjax($q);
        foreach ($results as &$item) {
            $item['hinhAnh'] = $this->xuLyDuongDanAnh($item['hinhAnh']);
            $item['giaBanFormatted'] = number_format($item['giaBan'], 0, ',', '.') . 'đ';
        }

        echo json_encode($results);
        exit;
    }
}
