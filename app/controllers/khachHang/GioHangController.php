<?php
class GioHangController extends Controller
{
    private $gioHangModel;

    public function __construct()
    {
        $this->gioHangModel = $this->model("gioHangModel");
    }

    private function checkLogin()
    {
        if (isset($_SESSION['user_id'])) return $_SESSION['user_id'];
        if (isset($_SESSION['user']) && isset($_SESSION['user']['idNguoiDung'])) return $_SESSION['user']['idNguoiDung'];
        return null;
    }

    // Hiển thị giao diện Giỏ hàng
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
                $items[] = $item;
            }
        }

        $data['title'] = "PharmaCare – Giỏ hàng của bạn";
        $data['page_title'] = "Giỏ hàng";
        $data['active_tab'] = "giohang";
        $data['page_css'] = "gioHang";
        $data['cartItems'] = $items;

        ob_start();
        extract($data);
        require_once APPROOT . '/views/khachHang/gioHang.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // API: Thêm thuốc không kê đơn (OTC) vào giỏ
    public function themVaoGio()
    {
        ob_clean(); // Làm sạch bộ nhớ đệm trước khi xuất JSON
        header('Content-Type: application/json');

        $idKhachHang = $this->checkLogin();

        // BẮT BUỘC ĐĂNG NHẬP: Chưa đăng nhập -> Trả về thông báo yêu cầu đăng nhập
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

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $ok = $this->gioHangModel->themItemVaoGio($idGioHang, $idThuoc, $soLuong, $thuoc['giaBan'], 'CHO_PHEP', null);

        echo json_encode(array('status' => (bool)$ok, 'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!'));
        exit;
    }

    // API: Cập nhật số lượng
    public function capNhatSoLuong()
    {
        header('Content-Type: application/json');
        $idKhachHang = $this->checkLogin();
        $idChiTiet = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $soLuong = isset($_POST['soLuong']) ? intval($_POST['soLuong']) : 1;

        if (!$idKhachHang || $idChiTiet <= 0 || $soLuong <= 0) {
            echo json_encode(array('status' => false));
            exit;
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $ok = $this->gioHangModel->capNhatSoLuongItem($idChiTiet, $idGioHang, $soLuong);
        echo json_encode(array('status' => (bool)$ok));
        exit;
    }

    // API: Xóa item khỏi giỏ hàng
    public function xoaItem()
    {
        header('Content-Type: application/json');
        $idKhachHang = $this->checkLogin();
        $idChiTiet = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if (!$idKhachHang || $idChiTiet <= 0) {
            echo json_encode(array('status' => false));
            exit;
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $ok = $this->gioHangModel->xoaItemKhoiGio($idChiTiet, $idGioHang);
        echo json_encode(array('status' => (bool)$ok));
        exit;
    }
}
