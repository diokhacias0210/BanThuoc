<?php

class ThuocController extends Controller
{
    private $danhSachModel;
    private $chiTietModel;

    public function __construct()
    {
        $this->danhSachModel = $this->model("danhSachThuocModel");
        $this->chiTietModel = $this->model("chiTietThuocModel");
    }

    // Trang danh sách thuốc
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

    // Trang chi tiết thuốc theo ID
    public function chiTiet($idThuoc = 0)
    {
        $thuoc = $this->chiTietModel->getChiTietThuocTheoID($idThuoc);

        if (!$thuoc) {
            $this->redirect("khachHang/thuoc");
        }

        $danhSachAnhRaw = $this->chiTietModel->getDanhSachAnhThuocTheoID($idThuoc);
        $loThuoc = $this->chiTietModel->getThongTinLoMoiNhatTheoID($idThuoc);

        // 1. Chuẩn hóa đường dẫn cho toàn bộ mảng ảnh phụ
        $danhSachAnh = array();
        if (!empty($danhSachAnhRaw)) {
            foreach ($danhSachAnhRaw as $img) {
                $danhSachAnh[] = array(
                    'duongDan' => $this->xuLyDuongDanAnh($img['duongDan'])
                );
            }
        }

        // 2. Chuẩn bị các biến dữ liệu hiển thị đã qua xử lý logic
        $data['title'] = "PharmaCare – " . $thuoc['tenThuoc'];
        $data['page_title'] = "Chi tiết thuốc";
        $data['active_tab'] = "thuoc";
        $data['page_css'] = "chiTietThuoc";

        $data['thuoc'] = $thuoc;
        $data['danhSachAnh'] = $danhSachAnh;
        $data['loThuoc'] = $loThuoc;

        $data['isKeDon'] = ($thuoc['yeuCauKeDon'] === 'Kê đơn');
        $data['gioiHanTxt'] = ($thuoc['gioiHanMua'] == -1) ? 'Không giới hạn' : ($thuoc['gioiHanMua'] . ' ' . $thuoc['donViTinh'] . ' / đơn');
        $data['maLoTxt'] = !empty($loThuoc['maLo']) ? $loThuoc['maLo'] : 'Chưa cập nhật';
        $data['nsxTxt'] = !empty($loThuoc['ngaySanXuat']) ? date('d/m/Y', strtotime($loThuoc['ngaySanXuat'])) : '—';
        $data['hsdTxt'] = !empty($loThuoc['hanSuDung']) ? date('d/m/Y', strtotime($loThuoc['hanSuDung'])) : '—';

        // 3. Xác định đường dẫn ảnh chính
        $rawPath = !empty($danhSachAnhRaw) ? $danhSachAnhRaw[0]['duongDan'] : (isset($thuoc['hinhAnh']) ? $thuoc['hinhAnh'] : '');
        $data['anhChinhUrl'] = $this->xuLyDuongDanAnh($rawPath);

        ob_start();
        require_once APPROOT . '/views/khachHang/chiTietThuoc.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // API JSON phục vụ lọc dữ liệu
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
