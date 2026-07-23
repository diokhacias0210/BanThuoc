<?php
class ThongTinCaNhanController extends Controller
{
    private $thongTinModel;

    public function __construct()
    {
        $this->thongTinModel = $this->model("thongTinCaNhanModel");
    }

    // Hàm phụ trợ lấy ID người dùng chính xác từ Session đăng nhập
    private function getUserId()
    {
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        if (isset($_SESSION['user']) && isset($_SESSION['user']['idNguoiDung'])) {
            return $_SESSION['user']['idNguoiDung'];
        }
        return null;
    }

    // Trang hiển thị thông tin cá nhân + địa chỉ giao hàng
    public function index()
    {
        $idNguoiDung = $this->getUserId();

        // Nếu chưa đăng nhập -> Chuyển hướng về trang Đăng Nhập
        if (!$idNguoiDung) {
            $this->redirect('khachHang/xacThuc/dangNhap');
        }

        $data['title'] = "PharmaCare – Thông tin cá nhân";
        $data['page_title'] = "Thông tin cá nhân";
        $data['active_tab'] = "thongTinCaNhan";
        $data['page_css'] = "thongTinCaNhan";

        $data['thongTin'] = $this->thongTinModel->getThongTinNguoiDung($idNguoiDung);
        $data['diaChiList'] = $this->thongTinModel->getDanhSachDiaChi($idNguoiDung);

        ob_start();
        require_once APPROOT . '/views/khachHang/thongTinCaNhan.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // API: cập nhật họ tên + email (NguoiDung)
    public function capNhatThongTin()
    {
        header('Content-Type: application/json');
        $idNguoiDung = $this->getUserId();
        $hoTen = isset($_POST['hoTen']) ? trim($_POST['hoTen']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        if (!$idNguoiDung || empty($hoTen) || empty($email)) {
            echo json_encode(array('status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập'));
            exit;
        }

        $ok = $this->thongTinModel->capNhatThongTin($idNguoiDung, $hoTen, $email);
        echo json_encode(array('status' => (bool)$ok));
        exit;
    }

    // API: thêm địa chỉ giao hàng (DiaChiGiaoHang)
    public function themDiaChi()
    {
        header('Content-Type: application/json');
        $idNguoiDung = $this->getUserId();
        $tenNguoiNhan = isset($_POST['tenNguoiNhan']) ? trim($_POST['tenNguoiNhan']) : '';
        $soDienThoaiNhan = isset($_POST['soDienThoaiNhan']) ? trim($_POST['soDienThoaiNhan']) : '';
        $diaChiChiTiet = isset($_POST['diaChiChiTiet']) ? trim($_POST['diaChiChiTiet']) : '';
        $laMacDinh = isset($_POST['laMacDinh']) && $_POST['laMacDinh'] == '1';

        if (!$idNguoiDung || empty($tenNguoiNhan) || empty($soDienThoaiNhan) || empty($diaChiChiTiet)) {
            echo json_encode(array('status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập'));
            exit;
        }

        $ok = $this->thongTinModel->themDiaChi($idNguoiDung, $tenNguoiNhan, $soDienThoaiNhan, $diaChiChiTiet, $laMacDinh);
        echo json_encode(array('status' => (bool)$ok));
        exit;
    }

    // API: xoá địa chỉ giao hàng
    public function xoaDiaChi($idDiaChi = null)
    {
        header('Content-Type: application/json');
        $idNguoiDung = $this->getUserId();

        if (!$idNguoiDung || !$idDiaChi) {
            echo json_encode(array('status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập'));
            exit;
        }

        $ok = $this->thongTinModel->xoaDiaChi($idDiaChi, $idNguoiDung);
        echo json_encode(array('status' => (bool)$ok));
        exit;
    }

    // API: đặt địa chỉ mặc định
    public function datMacDinh($idDiaChi = null)
    {
        header('Content-Type: application/json');
        $idNguoiDung = $this->getUserId();

        if (!$idNguoiDung || !$idDiaChi) {
            echo json_encode(array('status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập'));
            exit;
        }

        $ok = $this->thongTinModel->datMacDinh($idDiaChi, $idNguoiDung);
        echo json_encode(array('status' => (bool)$ok));
        exit;
    }
}
