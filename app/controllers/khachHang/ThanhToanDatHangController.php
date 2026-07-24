<?php

class thanhToanDatHangController extends Controller
{
    private $gioHangModel;
    private $donHangModel;
    private $thongTinModel;

    public function __construct()
    {
        $this->gioHangModel = $this->model("gioHangModel");
        $this->donHangModel = $this->model("DonHangModel");
        $this->thongTinModel = $this->model("thongTinCaNhanModel");
    }

    private function checkLogin()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) return $_SESSION['user_id'];
        if (isset($_SESSION['user']) && isset($_SESSION['user']['idNguoiDung'])) return $_SESSION['user']['idNguoiDung'];
        return null;
    }

    private function layDanhSachMua($idKhachHang, $selectedIds = null)
    {
        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        if (!$idGioHang) return [null, []];

        $danhSachChiTiet = $this->gioHangModel->layDanhSachChiTietGioHang($idGioHang);

        $danhSachMua = array_filter($danhSachChiTiet, function ($item) use ($selectedIds) {
            if ($item['trangThaiThaoTac'] === 'KHOA') return false;

            if ($selectedIds !== null && !in_array((string)$item['id'], $selectedIds, true)) {
                return false;
            }

            return true;
        });

        $danhSachMua = array_values($danhSachMua);

        foreach ($danhSachMua as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh(isset($item['hinhAnh']) ? $item['hinhAnh'] : '');
        }
        unset($item);

        return [$idGioHang, $danhSachMua];
    }

    private function layDanhSachIdDaChon()
    {
        $raw = $_POST['selectedIds'] ?? ($_GET['ids'] ?? null);
        if ($raw === null || $raw === '') return null;

        return array_filter(array_map('trim', explode(',', $raw)), function ($v) {
            return $v !== '';
        });
    }

    public function index()
    {
        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            header("Location: " . URLROOT . "/khachHang/xacThuc/dangNhap");
            exit();
        }

        $selectedIds = $this->layDanhSachIdDaChon();
        list($idGioHang, $danhSachMua) = $this->layDanhSachMua($idKhachHang, $selectedIds);

        if (empty($danhSachMua)) {
            header("Location: " . URLROOT . "/khachHang/gioHang");
            exit();
        }

        $tongTien = 0;
        foreach ($danhSachMua as $thuoc) {
            $tongTien += $thuoc["soLuong"] * $thuoc["donGia"];
        }

        $data = [
            'title' => "PharmaCare – Thanh toán đặt hàng",
            'page_title' => "Thanh toán",
            'active_tab' => "thanhtoan",
            'page_css' => "thanhToanDatHang",
            'cartItems' => $danhSachMua,
            'tongTien' => $tongTien,
            'diaChiList' => $this->thongTinModel->getDanhSachDiaChi($idKhachHang),
            'selectedIdsStr' => $selectedIds ? implode(',', $selectedIds) : ''
        ];

        ob_start();
        extract($data);
        require_once APPROOT . '/views/khachHang/thanhToanDatHang.php';
        $content = ob_get_clean();

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }

    public function xacNhan()
    {
        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            header("Location: " . URLROOT . "/khachHang/xacThuc/dangNhap");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . URLROOT . "/khachHang/thanhToanDatHang");
            exit();
        }

        $selectedIds = $this->layDanhSachIdDaChon();
        list($idGioHang, $danhSachMua) = $this->layDanhSachMua($idKhachHang, $selectedIds);

        if (empty($danhSachMua)) {
            header("Location: " . URLROOT . "/khachHang/gioHang");
            exit();
        }

        $hoTenNguoiNhan  = trim($_POST['hoTenNguoiNhan'] ?? '');
        $soDienThoaiNhan = trim($_POST['soDienThoaiNhan'] ?? '');
        $diaChiGiaoHang  = trim($_POST['diaChiGiaoHang'] ?? '');
        $phuongThucTT    = trim($_POST['phuongThucThanhToan'] ?? 'COD');
        $ghiChu          = trim($_POST['ghiChu'] ?? '');

        $tongTien = 0;
        foreach ($danhSachMua as $thuoc) {
            $tongTien += $thuoc["soLuong"] * $thuoc["donGia"];
        }

        // Tạo đơn hàng
        $this->donHangModel->taoDonHang($idKhachHang, $tongTien);
        $idDonHang = $this->donHangModel->getLastId();

        foreach ($danhSachMua as $thuoc) {
            $this->donHangModel->themChiTiet(
                $idDonHang,
                $thuoc["idThuoc"],
                $thuoc["soLuong"],
                $thuoc["donGia"]
            );
        }

        // Xóa các sản phẩm đã chọn mua khỏi giỏ hàng
        foreach ($danhSachMua as $thuoc) {
            $this->gioHangModel->xoaItemKhoiGio($thuoc['id'], $idGioHang);
        }

        header("Location: " . URLROOT . "/khachHang/quanLyDonHang");
        exit();
    }
}
