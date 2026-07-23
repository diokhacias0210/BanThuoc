<?php
class ThuocController extends Controller
{
    private $danhSachModel;

    public function __construct()
    {
        $this->danhSachModel = $this->model("danhSachThuocModel");
    }

    // Trang chủ hiển thị danh sách hàng hóa
    public function index()
    {
        $data['title'] = "PharmaCare – Danh sách sản phẩm thuốc";
        $data['page_title'] = "Danh sách sản phẩm";
        $data['active_tab'] = "thuoc";
        $data['page_css'] = "danhSachThuoc";

        ob_start();
        require_once APPROOT . '/views/khachHang/danhSachThuoc.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // API JSON cung cấp dữ liệu thuốc kèm lọc
    public function getList()
    {
        header('Content-Type: application/json');

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $idDanhMuc = isset($_GET['idDanhMuc']) ? $_GET['idDanhMuc'] : 'all';
        $keDon = isset($_GET['keDon']) ? $_GET['keDon'] : 'all';
        $minPrice = isset($_GET['minPrice']) ? floatval($_GET['minPrice']) : 0;
        $maxPrice = isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : 20000000;

        $medicines = $this->danhSachModel->getList($search, $idDanhMuc, $keDon, $minPrice, $maxPrice);
        $categories = $this->danhSachModel->getCategories();

        echo json_encode(array(
            'status' => true,
            'data' => $medicines,
            'categories' => $categories
        ));
        exit;
    }
}
