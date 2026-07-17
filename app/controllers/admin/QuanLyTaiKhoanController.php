<?php
class QuanLyTaiKhoanController extends Controller
{
    private $taiKhoanModel;

    public function __construct()
    {
        $this->taiKhoanModel = $this->model("TaiKhoanModel");
    }

    // Hiển thị giao diện quản lý
    public function index()
    {
        $data['title'] = "Quản Lý Tài Khoản";
        $data['page_title'] = "Quản lý tài khoản hệ thống";
        $data['page_icon'] = "fa-solid fa-users-gear";
        $data['active_tab'] = "taikhoan";
        $data['page_css'] = "quanLyTaiKhoan";

        ob_start();
        require_once APPROOT . '/views/admin/quanLyTaiKhoan.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/adminLayout', $data);
    }

    // API: Lấy danh sách tài khoản kèm bộ lọc
    public function getList()
    {
        header('Content-Type: application/json');
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $vaiTro = isset($_GET['vaiTro']) ? $_GET['vaiTro'] : 'all';
        $trangThai = isset($_GET['trangThai']) ? $_GET['trangThai'] : 'all';

        $list = $this->taiKhoanModel->getAll($search, $vaiTro, $trangThai);
        echo json_encode(array('status' => true, 'data' => $list));
        exit;
    }

    // API: Xem chi tiết tài khoản (Đảm bảo mật khẩu đã bị loại bỏ từ tầng Model)
    public function detail($id)
    {
        header('Content-Type: application/json');
        $user = $this->taiKhoanModel->getDetailById($id);
        if ($user) {
            echo json_encode(array('status' => true, 'data' => $user));
        } else {
            echo json_encode(array('status' => false, 'message' => 'Tài khoản không tồn tại trên hệ thống.'));
        }
        exit;
    }

    // API: Phân quyền vai trò mới (Đã thêm cơ chế chặn tự đổi quyền chính mình)
    public function saveRole()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idNguoiDung']) ? $_POST['idNguoiDung'] : '';
            $newRole = isset($_POST['vaiTro']) ? $_POST['vaiTro'] : '';

            // KIỂM TRA BẢO MẬT: Nếu ID cần sửa trùng với ID Admin trong Session
            if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
                echo json_encode(array('status' => false, 'message' => 'Quy tắc hệ thống: Bạn không thể tự hạ quyền hạn của chính tài khoản đang đăng nhập!'));
                exit;
            }

            if (empty($id) || empty($newRole)) {
                echo json_encode(array('status' => false, 'message' => 'Dữ liệu không hợp lệ.'));
                exit;
            }

            if ($this->taiKhoanModel->updateRole($id, $newRole)) {
                echo json_encode(array('status' => true, 'message' => 'Đã cập nhật quyền hạn tài khoản thành công!'));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Lỗi phân quyền hệ thống.'));
            }
        }
        exit;
    }

    // API: Khóa / Mở khóa tài khoản (Đã thêm cơ chế chặn tự khóa chính mình)
    public function toggleStatus($id)
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // KIỂM TRA BẢO MẬT: Nếu ID cần khóa trùng với ID Admin trong Session
            if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
                echo json_encode(array('status' => false, 'message' => 'Quy tắc an toàn: Bạn không thể tự khóa tài khoản quản trị của chính mình!'));
                exit;
            }

            $user = $this->taiKhoanModel->getDetailById($id);
            if (!$user) {
                echo json_encode(array('status' => false, 'message' => 'Không tìm thấy người dùng.'));
                exit;
            }

            $newStatus = $user['trangThai'] ? 0 : 1;
            $msg = $newStatus ? 'Đã mở khóa tài khoản thành công!' : 'Đã khóa tài khoản thành công! Người dùng này không thể tiếp tục đăng nhập.';

            if ($this->taiKhoanModel->updateStatus($id, $newStatus)) {
                echo json_encode(array('status' => true, 'message' => $msg));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Thao tác trạng thái thất bại.'));
            }
        }
        exit;
    }
}
