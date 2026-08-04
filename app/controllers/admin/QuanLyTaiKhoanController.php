<?php
/**
 * Class: QuanLyTaiKhoanController
 * Controller phụ trách chức năng "Quản lý tài khoản hệ thống" trong khu vực quản trị (admin).
 * Chức năng tổng quan:
 *  - Hiển thị giao diện quản lý tài khoản người dùng (danh sách, lọc theo vai trò/trạng thái).
 *  - Cung cấp các API dạng JSON để lấy danh sách, xem chi tiết, phân quyền vai trò
 *    và khóa/mở khóa tài khoản.
 *  - Áp dụng các quy tắc an toàn: không cho phép tài khoản đang đăng nhập tự đổi
 *    quyền hoặc tự khóa chính mình.
 */
class QuanLyTaiKhoanController extends Controller
{
    // Model xử lý truy vấn/thao tác dữ liệu tài khoản người dùng từ database
    private $taiKhoanModel;

    /**
     * Khởi tạo controller: nạp TaiKhoanModel để dùng cho các thao tác quản lý tài khoản.
     */
    public function __construct()
    {
        $this->taiKhoanModel = $this->model("TaiKhoanModel");
    }

    /**
     * Hiển thị giao diện quản lý tài khoản (view quanLyTaiKhoan) trong adminLayout.
     * Chuẩn bị các dữ liệu meta của trang (tiêu đề, icon, tab đang active, css riêng)
     * rồi render nội dung view vào layout chung của khu vực admin.
     */
    public function index()
    {
        $data['title'] = "Quản Lý Tài Khoản";               // Tiêu đề trang (thẻ <title>)
        $data['page_title'] = "Quản lý tài khoản hệ thống";   // Tiêu đề hiển thị trên header trang
        $data['page_icon'] = "fa-solid fa-users-gear";        // Icon hiển thị kèm tiêu đề trang
        $data['active_tab'] = "taikhoan";                     // Tab đang active trên sidebar
        $data['page_css'] = "quanLyTaiKhoan";                  // Tên file CSS riêng cho trang này
        $data['root_admin_id'] = $this->taiKhoanModel->getRootAdminId(); // id admin gốc, dùng ở JS để ẩn/hiện nút thao tác

        ob_start();
        // Nạp nội dung view quản lý tài khoản, output được bắt lại vào buffer
        require_once APPROOT . '/views/admin/quanLyTaiKhoan.php';
        $data['content'] = ob_get_clean(); // Nội dung view, gán vào layout chung

        $this->view('layouts/adminLayout', $data);
    }

    /**
     * API: Trả về danh sách tài khoản dạng JSON, có hỗ trợ tìm kiếm và lọc
     * theo vai trò, trạng thái. Được gọi từ phía client để đổ dữ liệu vào bảng.
     */
    public function layDanhSach()
    {
        header('Content-Type: application/json');
        $search = isset($_GET['search']) ? $_GET['search'] : '';         // Từ khóa tìm kiếm tài khoản
        $vaiTro = isset($_GET['vaiTro']) ? $_GET['vaiTro'] : 'all';        // Vai trò đang lọc (mặc định: tất cả)
        $trangThai = isset($_GET['trangThai']) ? $_GET['trangThai'] : 'all'; // Trạng thái đang lọc (mặc định: tất cả)

        $list = $this->taiKhoanModel->getAll($search, $vaiTro, $trangThai); // Danh sách tài khoản khớp bộ lọc
        echo json_encode(array('status' => true, 'data' => $list));
        exit;
    }

    /**
     * API: Trả về chi tiết một tài khoản theo id, dạng JSON.
     * Lưu ý: mật khẩu đã được loại bỏ khỏi dữ liệu trả về ngay từ tầng Model,
     * đảm bảo không lộ thông tin nhạy cảm ra phía client.
     * @param id ID tài khoản cần lấy chi tiết
     */
    public function chiTiet($id)
    {
        header('Content-Type: application/json');
        $user = $this->taiKhoanModel->getDetailById($id); // Dữ liệu chi tiết tài khoản (đã ẩn mật khẩu)
        if ($user) {
            echo json_encode(array('status' => true, 'data' => $user));
        } else {
            echo json_encode(array('status' => false, 'message' => 'Tài khoản không tồn tại trên hệ thống.'));
        }
        exit;
    }

    /**
     * API: Cập nhật vai trò (phân quyền) cho một tài khoản.
     * Có cơ chế chặn: tài khoản đang đăng nhập không được phép tự đổi
     * quyền hạn của chính mình, tránh trường hợp tự hạ quyền ngoài ý muốn.
     */
    public function luuVaiTro()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idNguoiDung']) ? $_POST['idNguoiDung'] : ''; // ID tài khoản cần đổi vai trò
            $newRole = isset($_POST['vaiTro']) ? $_POST['vaiTro'] : '';      // Vai trò mới muốn gán

            // Chặn thao tác nếu tài khoản đang đăng nhập cố tự đổi quyền của chính mình
            if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
                echo json_encode(array('status' => false, 'message' => 'Quy tắc hệ thống: Bạn không thể tự hạ quyền hạn của chính tài khoản đang đăng nhập!'));
                exit;
            }

            // Bắt buộc phải có id và vai trò mới hợp lệ
            if (empty($id) || empty($newRole)) {
                echo json_encode(array('status' => false, 'message' => 'Dữ liệu không hợp lệ.'));
                exit;
            }

            // Lấy thông tin tài khoản mục tiêu để biết vai trò hiện tại
            $targetUser = $this->taiKhoanModel->getDetailById($id);
            if (!$targetUser) {
                echo json_encode(array('status' => false, 'message' => 'Không tìm thấy người dùng.'));
                exit;
            }

            // Quy tắc phân cấp admin: chỉ admin GỐC (tài khoản QUAN_TRI_VIEN có idNguoiDung nhỏ nhất
            // toàn hệ thống) mới được phép:
            //   (a) đổi vai trò của một admin đang tồn tại, HOẶC
            //   (b) nâng một tài khoản khác (khách hàng/dược sĩ) lên thành admin mới.
            // Các admin được cấp quyền sau đều bình đẳng với nhau -> KHÔNG ai trong số họ được
            // tự tạo thêm admin mới hoặc đụng vào vai trò của admin khác.
            $lienQuanAdmin = ($targetUser['vaiTro'] === 'QUAN_TRI_VIEN') || ($newRole === 'QUAN_TRI_VIEN');
            if ($lienQuanAdmin) {
                $rootAdminId = $this->taiKhoanModel->getRootAdminId();
                if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $rootAdminId) {
                    echo json_encode(array('status' => false, 'message' => 'Chỉ tài khoản quản trị viên gốc mới có quyền cấp/thay đổi vai trò quản trị viên.'));
                    exit;
                }
            }

            if ($this->taiKhoanModel->updateRole($id, $newRole)) {
                echo json_encode(array('status' => true, 'message' => 'Đã cập nhật quyền hạn tài khoản thành công!'));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Lỗi phân quyền hệ thống.'));
            }
        }
        exit;
    }

    /**
     * API: Đổi trạng thái khóa/mở khóa của một tài khoản theo id.
     * Có cơ chế chặn: tài khoản đang đăng nhập không được phép tự khóa
     * chính tài khoản của mình, tránh mất quyền truy cập hệ thống.
     * @param id ID tài khoản cần đổi trạng thái
     */
    public function doiTrangThai($id)
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Chặn thao tác nếu tài khoản đang đăng nhập cố tự khóa chính mình
            if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
                echo json_encode(array('status' => false, 'message' => 'Quy tắc an toàn: Bạn không thể tự khóa tài khoản quản trị của chính mình!'));
                exit;
            }

            $user = $this->taiKhoanModel->getDetailById($id); // Lấy thông tin tài khoản để biết trạng thái hiện tại
            if (!$user) {
                echo json_encode(array('status' => false, 'message' => 'Không tìm thấy người dùng.'));
                exit;
            }

            // Quy tắc phân cấp admin (giống luuVaiTro): chỉ admin GỐC mới được khóa/mở khóa admin khác.
            if ($user['vaiTro'] === 'QUAN_TRI_VIEN') {
                $rootAdminId = $this->taiKhoanModel->getRootAdminId();
                if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $rootAdminId) {
                    echo json_encode(array('status' => false, 'message' => 'Chỉ tài khoản quản trị viên gốc mới có quyền khóa/mở khóa admin khác.'));
                    exit;
                }
            }

            $newStatus = $user['trangThai'] ? 0 : 1; // Đảo ngược trạng thái hiện tại: đang mở -> khóa, đang khóa -> mở
            $msg = $newStatus ? 'Đã mở khóa tài khoản thành công!' : 'Đã khóa tài khoản thành công! Người dùng này không thể tiếp tục đăng nhập.'; // Thông báo tương ứng với trạng thái mới

            if ($this->taiKhoanModel->updateStatus($id, $newStatus)) {
                echo json_encode(array('status' => true, 'message' => $msg));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Thao tác trạng thái thất bại.'));
            }
        }
        exit;
    }
}
