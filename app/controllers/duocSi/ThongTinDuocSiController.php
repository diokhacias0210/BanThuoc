<?php
/**
 * Controller quản lý trang hồ sơ thông tin của dược sĩ đang đăng nhập:
 * - Hiển thị thông tin tài khoản + hồ sơ chuyên môn
 * - Cập nhật thông tin tài khoản và hồ sơ chuyên môn
 */
class ThongTinDuocSiController extends Controller
{
    private $duocSiModel; // Model thao tác dữ liệu thông tin dược sĩ (bảng NguoiDung + hồ sơ chuyên môn)

    // Nhãn hiển thị cho vai trò - vì cột "vaiTro" trong CSDL chỉ có ENUM
    // KHACH_HANG/DUOC_SI/QUAN_TRI_VIEN, không có khái niệm "trưởng"/"phó"
    // như bản mock, nên map ra nhãn chung ở đây (không lấy từ DB)
    private $nhanVaiTro = [
        'DUOC_SI' => 'Dược sĩ',
        'QUAN_TRI_VIEN' => 'Quản trị viên',
        'KHACH_HANG' => 'Khách hàng'
    ];

    /**
     * Khởi tạo controller, nạp sẵn thongTinDuocSiModel để dùng cho toàn bộ các hàm bên dưới.
     */
    public function __construct()
    {
        $this->duocSiModel = $this->model("thongTinDuocSiModel");
    }

    /**
     * Hiển thị trang hồ sơ thông tin của dược sĩ đang đăng nhập.
     * Lấy thông tin từ model theo user_id trong session rồi render ra view trong layout dược sĩ.
     */
    public function index()
    {
        // Lấy ID người dùng đang đăng nhập từ session (null nếu chưa đăng nhập)
        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $data['title'] = "PharmaCare – Hồ sơ dược sĩ";
        $data['page_title'] = "Thông tin dược sĩ";
        $data['active_tab'] = "thongTinDuocSi";
        $data['page_css'] = "thongTinDuocSi";

        // Biến rời để View (require_once bên dưới) đọc trực tiếp được
        $thongTin = $idNguoiDung ? $this->duocSiModel->layThongTin($idNguoiDung) : null; // Thông tin dược sĩ, null nếu chưa đăng nhập
        $nhanVaiTro = $this->nhanVaiTro; // Map mã vai trò -> nhãn hiển thị, truyền sang view

        // Render nội dung view con vào buffer, sau đó nhúng vào layout chung của dược sĩ
        ob_start();
        // TODO: đổi lại đúng đường dẫn thư mục view của bạn (VD: views/duocSi/thongTinDuocSi.php)
        require_once APPROOT . '/views/duocSi/thongTinDuocSi.php';
        $data['content'] = ob_get_clean();

        // TODO: đổi lại đúng tên layout dùng cho khu vực Dược sĩ (VD: layouts/duocSiLayout)
        $this->view('layouts/duocSiLayout', $data);
    }

    /**
     * API: cập nhật thông tin tài khoản (họ tên, email, SĐT) và hồ sơ chuyên môn
     * (chứng chỉ hành nghề, trình độ, nơi cấp) của dược sĩ đang đăng nhập.
     * @return void In ra JSON và kết thúc request.
     */
    public function capNhatThongTin()
    {
        header('Content-Type: application/json');
        $idNguoiDung = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // ID người dùng đang đăng nhập

        // Lấy dữ liệu form gửi lên, loại bỏ khoảng trắng thừa, mặc định rỗng nếu không có
        $hoTen = isset($_POST['hoTen']) ? trim($_POST['hoTen']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $soDienThoai = isset($_POST['soDienThoai']) ? trim($_POST['soDienThoai']) : '';
        $chungChiHanhNghe = isset($_POST['chungChiHanhNghe']) ? trim($_POST['chungChiHanhNghe']) : '';
        $trinhDo = isset($_POST['trinhDo']) ? trim($_POST['trinhDo']) : '';
        $noiCap = isset($_POST['noiCap']) ? trim($_POST['noiCap']) : '';

        // Validate: bắt buộc đã đăng nhập và đủ 3 trường quan trọng (họ tên, email, SĐT)
        if (!$idNguoiDung || empty($hoTen) || empty($email) || empty($soDienThoai)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        // Chặn trùng email/SĐT với tài khoản khác (2 cột này UNIQUE trong bảng NguoiDung)
        if ($this->duocSiModel->kiemTraTrungEmailHoacSdt($idNguoiDung, $email, $soDienThoai)) {
            echo json_encode(['status' => false, 'message' => 'Email hoặc số điện thoại đã được tài khoản khác sử dụng']);
            exit;
        }

        // Cập nhật thông tin tài khoản (bảng NguoiDung) và hồ sơ chuyên môn (bảng riêng), cả 2 phải thành công
        $ok1 = $this->duocSiModel->capNhatTaiKhoan($idNguoiDung, $hoTen, $email, $soDienThoai);
        $ok2 = $this->duocSiModel->capNhatHoSoChuyenMon($idNguoiDung, $chungChiHanhNghe, $trinhDo, $noiCap);

        echo json_encode(['status' => ($ok1 && $ok2)]);
        exit;
    }
}


