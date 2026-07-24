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

    // BỔ SUNG METHOD CHIT IET NÀY ĐỂ XỬ LÝ URL /khachHang/thuoc/chiTiet/{id}
    public function chiTiet($idThuoc = 0)
    {
        $idThuoc = intval($idThuoc);
        if ($idThuoc <= 0) {
            $this->redirect('khachHang/thuoc');
        }

        $chiTietModel = $this->model("chiTietThuocModel");
        $thuoc = $chiTietModel->getChiTietThuocTheoID($idThuoc);

        if (!$thuoc) {
            $this->redirect('khachHang/thuoc');
        }

        // Chuyển hướng sang dangKeToaThuoc nếu là Thuốc kê đơn
        if ($thuoc['yeuCauKeDon'] === 'Kê đơn') {
            $this->redirect('khachHang/dangKeToaThuoc?idThuoc=' . $idThuoc);
        }

        $this->redirect('khachHang/chiTietThuoc/chiTiet/' . $idThuoc);
    }

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
