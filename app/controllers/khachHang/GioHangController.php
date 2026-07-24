<?php
class gioHangController extends Controller
{
    private $gioHangModel;

    public function __construct()
    {
        $this->gioHangModel = $this->model("gioHangModel");
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

    public function index()
    {
        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            $this->redirect('khachHang/xacThuc/dangNhap');
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $rawItems = $this->gioHangModel->layDanhSachChiTietGioHang($idGioHang);

        $items = array();
        if (!empty($rawItems)) {
            foreach ($rawItems as $item) {
                $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh(isset($item['hinhAnh']) ? $item['hinhAnh'] : '');
                $gioiHanMua = intval($item['gioiHanMua']);
                $tongTon = intval($item['tongTon']);
                $item['maxAllowed'] = ($gioiHanMua > 0) ? min($gioiHanMua, $tongTon) : $tongTon;
                $items[] = $item;
            }
        }

        $data = [
            'title' => "PharmaCare – Giỏ hàng của bạn",
            'page_title' => "Giỏ hàng",
            'active_tab' => "giohang",
            'page_css' => "gioHang",
            'cartItems' => $items
        ];

        ob_start();
        extract($data);
        require_once APPROOT . '/views/khachHang/gioHang.php';
        $content = ob_get_clean();

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }

    public function themVaoGio()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            echo json_encode(array(
                'status' => false,
                'requireLogin' => true,
                'message' => 'Bạn cần đăng nhập tài khoản để thực hiện thêm sản phẩm vào giỏ hàng!'
            ));
            exit;
        }

        $idThuoc = isset($_POST['idThuoc']) ? intval($_POST['idThuoc']) : 0;
        $soLuong = isset($_POST['soLuong']) ? intval($_POST['soLuong']) : 1;

        if ($idThuoc <= 0 || $soLuong <= 0) {
            echo json_encode(array('status' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ.'));
            exit;
        }

        $chiTietModel = $this->model("chiTietThuocModel");
        $thuoc = $chiTietModel->getChiTietThuocTheoID($idThuoc);

        if (!$thuoc) {
            echo json_encode(array('status' => false, 'message' => 'Sản phẩm không tồn tại trong hệ thống.'));
            exit;
        }

        if ($thuoc['yeuCauKeDon'] === 'Kê đơn') {
            echo json_encode(array(
                'status' => false,
                'message' => 'Đây là thuốc kê đơn! Bạn cần gửi đơn thuốc để dược sĩ tư vấn.',
                'isRx' => true
            ));
            exit;
        }

        $gioiHanMua = intval($thuoc['gioiHanMua']);
        $tongTon = intval($thuoc['tongTon']);
        $maxAllowed = ($gioiHanMua > 0) ? min($gioiHanMua, $tongTon) : $tongTon;

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $currentInCart = $this->gioHangModel->getSoLuongHienCoTrongGio($idGioHang, $idThuoc);
        $tongSoLuongMoi = $currentInCart + $soLuong;

        if ($tongSoLuongMoi > $maxAllowed) {
            echo json_encode(array(
                'status' => false,
                'message' => "Sản phẩm này giới hạn mua tối đa {$maxAllowed} đơn vị! (Đã có {$currentInCart} trong giỏ)."
            ));
            exit;
        }

        $ok = $this->gioHangModel->themItemVaoGio($idGioHang, $idThuoc, $soLuong, $thuoc['giaBan'], 'CHO_PHEP', null);
        $cartCount = $this->gioHangModel->demSoChungLoaiThuocTrongGio($idGioHang);

        echo json_encode(array(
            'status' => (bool)$ok,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!',
            'cartCount' => $cartCount
        ));
        exit;
    }

    public function capNhatSoLuong()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        $idKhachHang = $this->checkLogin();
        $idChiTiet = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $soLuong = isset($_POST['soLuong']) ? intval($_POST['soLuong']) : 1;

        if (!$idKhachHang || $idChiTiet <= 0 || $soLuong <= 0) {
            echo json_encode(array('status' => false, 'message' => 'Yêu cầu không hợp lệ.'));
            exit;
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $itemInfo = $this->gioHangModel->getChiTietItemTheoID($idChiTiet, $idGioHang);

        if (!$itemInfo) {
            echo json_encode(array('status' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ.'));
            exit;
        }

        $gioiHanMua = intval($itemInfo['gioiHanMua']);
        $tongTon = intval($itemInfo['tongTon']);
        $maxAllowed = ($gioiHanMua > 0) ? min($gioiHanMua, $tongTon) : $tongTon;

        if ($soLuong > $maxAllowed) {
            echo json_encode(array(
                'status' => false,
                'message' => "Sản phẩm này giới hạn mua tối đa {$maxAllowed} đơn vị!"
            ));
            exit;
        }

        $ok = $this->gioHangModel->capNhatSoLuongItem($idChiTiet, $idGioHang, $soLuong);
        echo json_encode(array('status' => (bool)$ok));
        exit;
    }

    public function xoaItem()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        $idKhachHang = $this->checkLogin();
        $idChiTiet = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if (!$idKhachHang || $idChiTiet <= 0) {
            echo json_encode(array('status' => false));
            exit;
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $ok = $this->gioHangModel->xoaItemKhoiGio($idChiTiet, $idGioHang);
        $cartCount = $this->gioHangModel->demSoChungLoaiThuocTrongGio($idGioHang);

        echo json_encode(array('status' => (bool)$ok, 'cartCount' => $cartCount));
        exit;
    }
}
