<?php
class ThongTinDuocSiController extends Controller
{
    private $duocSiModel;

    // Nhãn hiển thị cho vai trò - vì cột "vaiTro" trong CSDL chỉ có ENUM
    // KHACH_HANG/DUOC_SI/QUAN_TRI_VIEN, không có khái niệm "trưởng"/"phó"
    // như bản mock, nên map ra nhãn chung ở đây (không lấy từ DB)
    private $nhanVaiTro = [
        'DUOC_SI' => 'Dược sĩ',
        'QUAN_TRI_VIEN' => 'Quản trị viên',
        'KHACH_HANG' => 'Khách hàng'
    ];

    public function __construct()
    {
        $this->duocSiModel = $this->model("thongTinDuocSiModel");
    }

    // Trang hồ sơ thông tin dược sĩ đang đăng nhập
    public function index()
    {
        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $data['title'] = "PharmaCare – Hồ sơ dược sĩ";
        $data['page_title'] = "Thông tin dược sĩ";
        $data['active_tab'] = "thongTinDuocSi";
        $data['page_css'] = "thongTinDuocSi";

        // Biến rời để View (require_once bên dưới) đọc trực tiếp được
        $thongTin = $idNguoiDung ? $this->duocSiModel->layThongTin($idNguoiDung) : null;
        $nhanVaiTro = $this->nhanVaiTro;

        ob_start();
        // TODO: đổi lại đúng đường dẫn thư mục view của bạn (VD: views/duocSi/thongTinDuocSi.php)
        require_once APPROOT . '/views/duocSi/thongTinDuocSi.php';
        $data['content'] = ob_get_clean();

        // TODO: đổi lại đúng tên layout dùng cho khu vực Dược sĩ (VD: layouts/duocSiLayout)
        $this->view('layouts/duocSiLayout', $data);
    }

    // API: cập nhật thông tin tài khoản + hồ sơ chuyên môn
    public function capNhatThongTin()
    {
        header('Content-Type: application/json');
        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $hoTen = isset($_POST['hoTen']) ? trim($_POST['hoTen']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $soDienThoai = isset($_POST['soDienThoai']) ? trim($_POST['soDienThoai']) : '';
        $chungChiHanhNghe = isset($_POST['chungChiHanhNghe']) ? trim($_POST['chungChiHanhNghe']) : '';
        $trinhDo = isset($_POST['trinhDo']) ? trim($_POST['trinhDo']) : '';
        $noiCap = isset($_POST['noiCap']) ? trim($_POST['noiCap']) : '';

        if (!$idNguoiDung || empty($hoTen) || empty($email) || empty($soDienThoai)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        // Chặn trùng email/SĐT với tài khoản khác (2 cột này UNIQUE trong bảng NguoiDung)
        if ($this->duocSiModel->kiemTraTrungEmailHoacSdt($idNguoiDung, $email, $soDienThoai)) {
            echo json_encode(['status' => false, 'message' => 'Email hoặc số điện thoại đã được tài khoản khác sử dụng']);
            exit;
        }

        $ok1 = $this->duocSiModel->capNhatTaiKhoan($idNguoiDung, $hoTen, $email, $soDienThoai);
        $ok2 = $this->duocSiModel->capNhatHoSoChuyenMon($idNguoiDung, $chungChiHanhNghe, $trinhDo, $noiCap);

        echo json_encode(['status' => ($ok1 && $ok2)]);
        exit;
    }
}
