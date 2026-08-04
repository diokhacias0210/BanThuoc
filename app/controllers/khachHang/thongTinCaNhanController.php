<?php
/**
 * Controller quản lý trang Thông tin cá nhân (khu vực Khách hàng).
 * Xử lý hiển thị thông tin tài khoản + danh sách địa chỉ giao hàng, cập nhật họ tên/email,
 * và CRUD (thêm/xoá/đặt mặc định) địa chỉ giao hàng của khách hàng đang đăng nhập.
 */
class ThongTinCaNhanController extends Controller
{
    private $thongTinModel; // Model thao tác với dữ liệu cá nhân (bảng NguoiDung + DiaChiGiaoHang)

    /**
     * Khởi tạo controller, load sẵn model dùng xuyên suốt các action bên dưới.
     */
    public function __construct()
    {
        $this->thongTinModel = $this->model("thongTinCaNhanModel");
    }

    /**
     * Trang hiển thị thông tin cá nhân + địa chỉ giao hàng.
     * Nếu chưa đăng nhập thì trả về mảng rỗng thay vì lỗi (view sẽ tự hiển thị trạng thái trống).
     */
    public function index()
    {

        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // id tài khoản đang đăng nhập

        $data['title'] = "PharmaCare – Thông tin cá nhân";
        $data['page_title'] = "Thông tin cá nhân";
        $data['active_tab'] = "thongTinCaNhan"; // Đánh dấu tab đang active trên sidebar khách hàng
        $data['page_css'] = "thongTinCaNhan"; // Tên file CSS riêng cho trang này

        // Quan trọng: phải tạo biến rời $thongTin / $diaChiList (không chỉ $data[...])
        // vì View được nạp bằng require_once bên dưới đọc trực tiếp 2 biến này.
        $thongTin = $idNguoiDung ? $this->thongTinModel->getThongTinNguoiDung($idNguoiDung) : []; // Thông tin tài khoản (họ tên, email, SĐT...)
        $diaChiList = $idNguoiDung ? $this->thongTinModel->getDanhSachDiaChi($idNguoiDung) : []; // Danh sách địa chỉ giao hàng đã lưu

        ob_start(); // Bắt đầu buffer output để nhúng view con vào biến $data['content']
        require_once APPROOT . '/views/khachHang/thongTinCaNhan.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    /**
     * API: cập nhật họ tên + email (bảng NguoiDung).
     */
    public function capNhatThongTin()
    {
        header('Content-Type: application/json');
        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $hoTen = isset($_POST['hoTen']) ? trim($_POST['hoTen']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        // Bắt buộc phải đăng nhập và nhập đủ họ tên + email
        if (!$idNguoiDung || empty($hoTen) || empty($email)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->thongTinModel->capNhatThongTin($idNguoiDung, $hoTen, $email);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }

    /**
     * API: thêm địa chỉ giao hàng mới (bảng DiaChiGiaoHang).
     */
    public function themDiaChi()
    {
        header('Content-Type: application/json');
        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $tenNguoiNhan = isset($_POST['tenNguoiNhan']) ? trim($_POST['tenNguoiNhan']) : '';
        $soDienThoaiNhan = isset($_POST['soDienThoaiNhan']) ? trim($_POST['soDienThoaiNhan']) : '';
        $diaChiChiTiet = isset($_POST['diaChiChiTiet']) ? trim($_POST['diaChiChiTiet']) : '';
        $laMacDinh = isset($_POST['laMacDinh']) && $_POST['laMacDinh'] == '1'; // Checkbox "Đặt làm mặc định", so sánh '1' vì dữ liệu gửi lên dạng chuỗi

        // Bắt buộc phải đăng nhập và đủ 3 trường thông tin người nhận
        if (!$idNguoiDung || empty($tenNguoiNhan) || empty($soDienThoaiNhan) || empty($diaChiChiTiet)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->thongTinModel->themDiaChi($idNguoiDung, $tenNguoiNhan, $soDienThoaiNhan, $diaChiChiTiet, $laMacDinh);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }

    /**
     * API: xoá địa chỉ giao hàng — nhận idDiaChi qua segment URL (.../xoaDiaChi/5).
     * @param int|null $idDiaChi id địa chỉ cần xoá, lấy từ segment trên URL
     */
    public function xoaDiaChi($idDiaChi = null)
    {
        header('Content-Type: application/json');
        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        if (!$idNguoiDung || !$idDiaChi) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->thongTinModel->xoaDiaChi($idDiaChi, $idNguoiDung); // Truyền kèm idNguoiDung để đảm bảo chỉ xoá địa chỉ của đúng chủ tài khoản
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }

    /**
     * API: đặt địa chỉ mặc định — nhận idDiaChi qua segment URL (.../datMacDinh/5).
     * @param int|null $idDiaChi id địa chỉ cần đặt làm mặc định, lấy từ segment trên URL
     */
    public function datMacDinh($idDiaChi = null)
    {
        header('Content-Type: application/json');
        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        if (!$idNguoiDung || !$idDiaChi) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->thongTinModel->datMacDinh($idDiaChi, $idNguoiDung); // Truyền kèm idNguoiDung để đảm bảo chỉ đặt mặc định trên địa chỉ của đúng chủ tài khoản
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }
}


