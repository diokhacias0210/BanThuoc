<?php
class QuanLyDonHangController extends Controller
{
    private $donHangModel;

    public function __construct()
    {
        $this->donHangModel = $this->model("quanLyDonHangModel");
    }

    // Trang danh sách đơn hàng của khách hàng đang đăng nhập
    public function index()
    {


        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $data['title'] = "PharmaCare – Quản lý đơn hàng";
        $data['page_title'] = "Quản lý đơn hàng";
        $data['active_tab'] = "quanLyDonHang";
        $data['page_css'] = "quanLyDonHang";

        // Quan trọng: phải tạo biến rời $donHangList (không chỉ $data[...])
        // vì View được nạp bằng require_once bên dưới đọc trực tiếp biến này.
        $donHangList = $idKhachHang ? $this->donHangModel->getDonHangTheoKhachHang($idKhachHang) : [];

        ob_start();
        require_once APPROOT . '/views/khachHang/quanLyDonHang.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // API: huỷ đơn hàng — nhận idDonHang qua segment URL (.../huyDonHang/5), lý do huỷ qua POST
    public function huyDonHang($idDonHang = null)
    {
        header('Content-Type: application/json');
        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $lyDoHuy = isset($_POST['lyDoHuy']) ? trim($_POST['lyDoHuy']) : '';

        if (!$idKhachHang || !$idDonHang || empty($lyDoHuy)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->donHangModel->huyDonHang($idDonHang, $idKhachHang, $lyDoHuy);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }
}
