<?php
/**
 * Controller xác thực người dùng (dùng chung cho cả 3 vai trò: Khách hàng, Dược sĩ, Quản trị viên).
 * Xử lý các luồng: hiển thị/xử lý đăng nhập, hiển thị/xử lý đăng ký (chỉ dành cho khách hàng),
 * và đăng xuất khỏi hệ thống.
 */
class XacThucController extends Controller
{
    private $taiKhoanModel; // Model thao tác với dữ liệu tài khoản (bảng NguoiDung)

    /**
     * Khởi tạo controller, load sẵn model dùng xuyên suốt các action bên dưới.
     */
    public function __construct()
    {
        $this->taiKhoanModel = $this->model('TaiKhoanModel');
    }

    /**
     * Hiển thị giao diện Đăng Nhập.
     */
    public function dangNhap()
    {
        $this->view('khachHang/xacThuc/dangNhap', [
            'page_css' => 'xacThuc',
            'is_auth'  => true // Cờ đánh dấu đây là trang xác thực (dùng để layout ẩn navbar/sidebar thông thường)
        ]);
    }

    /**
     * Xử lý đăng nhập: kiểm tra số điện thoại + mật khẩu, tạo session và
     * điều hướng người dùng theo đúng vai trò (Quản trị viên / Dược sĩ / Khách hàng).
     */
    public function xuLyDangNhap()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $sdt = trim($_POST['soDienThoai']);       // Số điện thoại dùng làm tên đăng nhập
            $matKhau = $_POST['matKhau'];               // Mật khẩu người dùng nhập
            $user = $this->taiKhoanModel->kiemTraDangNhap($sdt); // Tìm tài khoản theo số điện thoại

            // So khớp mật khẩu người dùng nhập với mật khẩu lưu trong CSDL
            if ($user && $matKhau === $user['matKhau']) {
                $_SESSION['user_id'] = $user['idNguoiDung'];
                $_SESSION['user_name'] = $user['hoTen'];
                $_SESSION['user_role'] = $user['vaiTro'];

                // ĐỒNG BỘ: Tạo thêm session này để ăn khớp với phần check isset($_SESSION["user"]) ở file navbar của bạn
                $_SESSION['user'] = $user;

                // Điều hướng theo vai trò tài khoản, mỗi vai trò có trang chủ riêng
                switch ($user['vaiTro']) {
                    case 'QUAN_TRI_VIEN':
                        $this->redirect('admin/quanLyTaiKhoan');
                        break;
                    case 'DUOC_SI':
                        $this->redirect('duocSi/ThongTinDuocSi');
                        break;
                    case 'KHACH_HANG':
                        $this->redirect('khachHang/trangChu');
                        break;
                }
            } else {
                // Sai số điện thoại hoặc mật khẩu -> hiển thị lại form kèm thông báo lỗi
                $this->view('khachHang/xacThuc/dangNhap', [
                    'error'    => 'Số điện thoại hoặc mật khẩu không chính xác!',
                    'page_css' => 'xacThuc',
                    'is_auth'  => true
                ]);
            }
        }
    }

    /**
     * Hiển thị giao diện Đăng Ký (dành cho khách hàng tự tạo tài khoản).
     */
    public function dangKy()
    {
        $this->view('khachHang/xacThuc/dangKy', [
            'page_css' => 'xacThuc',
            'is_auth'  => true
        ]);
    }

    /**
     * Xử lý đăng ký tài khoản khách hàng mới.
     * Nếu thành công thì chuyển hướng sang trang đăng nhập; thất bại (thường do trùng SĐT/Email)
     * thì hiển thị lại form kèm thông báo lỗi.
     */
    public function xuLyDangKy()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $hoTen = trim($_POST['hoTen']);
            $email = trim($_POST['email']);
            $sdt = trim($_POST['soDienThoai']);
            $matKhau = $_POST['matKhau'];

            $result = $this->taiKhoanModel->dangKyKhachHang($hoTen, $email, $sdt, $matKhau); // true nếu tạo tài khoản thành công

            if ($result) {
                $this->redirect('khachHang/xacThuc/dangNhap');
            } else {
                $this->view('khachHang/xacThuc/dangKy', [
                    'error'    => 'Đăng ký thất bại! Số điện thoại hoặc Email đã tồn tại.',
                    'page_css' => 'xacThuc',
                    'is_auth'  => true
                ]);
            }
        }
    }

    // ==================================================================
    // ĐÃ BỔ SUNG: Xử lý Đăng Xuất tài khoản khỏi hệ thống
    // ==================================================================
    /**
     * Đăng xuất: xoá toàn bộ session liên quan đến phiên đăng nhập hiện tại
     * rồi chuyển hướng về lại trang đăng nhập.
     */
    public function dangXuat()
    {
        // Xóa bỏ tận gốc toàn bộ các khóa dữ liệu Session liên quan đến phiên đăng nhập cũ
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user']);

        // Hoặc nếu muốn xóa sạch bách tất cả dữ liệu phiên làm việc, bạn có thể dùng: session_destroy();

        // Chuyển hướng người dùng an toàn về lại trang đăng nhập ban đầu
        $this->redirect('khachHang/xacThuc/dangNhap');
    }
}
