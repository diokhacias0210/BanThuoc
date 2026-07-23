<?php
class ThongTinCaNhanController extends Controller
{
    private $thongTinModel;

    public function __construct()
    {
        $this->thongTinModel = $this->model("thongTinCaNhanModel");
    }

    // Trang hiển thị thông tin cá nhân + địa chỉ giao hàng
    public function index()
    {
        // TODO: đổi lại đúng theo tên biến session đăng nhập thực tế của bạn
        $idNguoiDung = $_SESSION['idNguoiDung'] ?? null;

        $data['title'] = "PharmaCare – Thông tin cá nhân";
        $data['page_title'] = "Thông tin cá nhân";
        $data['active_tab'] = "thongTinCaNhan";
        $data['page_css'] = "thongTinCaNhan";

        $data['thongTin'] = $idNguoiDung ? $this->thongTinModel->getThongTinNguoiDung($idNguoiDung) : [];
        $data['diaChiList'] = $idNguoiDung ? $this->thongTinModel->getDanhSachDiaChi($idNguoiDung) : [];

        ob_start();
        require_once APPROOT . '/views/khachHang/thongTinCaNhan.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // API: cập nhật họ tên + email (NguoiDung)
    public function capNhatThongTin()
    {
        header('Content-Type: application/json');
        $idNguoiDung = $_SESSION['idNguoiDung'] ?? null;
        $hoTen = isset($_POST['hoTen']) ? trim($_POST['hoTen']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        if (!$idNguoiDung || empty($hoTen) || empty($email)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->thongTinModel->capNhatThongTin($idNguoiDung, $hoTen, $email);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }

    // API: thêm địa chỉ giao hàng (DiaChiGiaoHang)
    public function themDiaChi()
    {
        header('Content-Type: application/json');
        $idNguoiDung = $_SESSION['idNguoiDung'] ?? null;
        $tenNguoiNhan = isset($_POST['tenNguoiNhan']) ? trim($_POST['tenNguoiNhan']) : '';
        $soDienThoaiNhan = isset($_POST['soDienThoaiNhan']) ? trim($_POST['soDienThoaiNhan']) : '';
        $diaChiChiTiet = isset($_POST['diaChiChiTiet']) ? trim($_POST['diaChiChiTiet']) : '';
        $laMacDinh = isset($_POST['laMacDinh']) && $_POST['laMacDinh'] == '1';

        if (!$idNguoiDung || empty($tenNguoiNhan) || empty($soDienThoaiNhan) || empty($diaChiChiTiet)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->thongTinModel->themDiaChi($idNguoiDung, $tenNguoiNhan, $soDienThoaiNhan, $diaChiChiTiet, $laMacDinh);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }

    // API: xoá địa chỉ giao hàng — nhận idDiaChi qua segment URL (.../xoaDiaChi/5)
    public function xoaDiaChi($idDiaChi = null)
    {
        header('Content-Type: application/json');
        $idNguoiDung = $_SESSION['idNguoiDung'] ?? null;

        if (!$idNguoiDung || !$idDiaChi) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->thongTinModel->xoaDiaChi($idDiaChi, $idNguoiDung);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }

    // API: đặt địa chỉ mặc định — nhận idDiaChi qua segment URL (.../datMacDinh/5)
    public function datMacDinh($idDiaChi = null)
    {
        header('Content-Type: application/json');
        $idNguoiDung = $_SESSION['idNguoiDung'] ?? null;

        if (!$idNguoiDung || !$idDiaChi) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->thongTinModel->datMacDinh($idDiaChi, $idNguoiDung);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }
}
