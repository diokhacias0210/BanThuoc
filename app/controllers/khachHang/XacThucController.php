<?php
class XacThucController extends Controller {
    private $taiKhoanModel;

    public function __construct() {
        $this->taiKhoanModel = $this->model('TaiKhoanModel');
    }

    // Hiển thị giao diện Đăng Nhập
    public function dangNhap() {
        $this->view('khachHang/xacThuc/dangNhap', [
            'page_css' => 'xacThuc',
            'is_auth'  => true
        ]);
    }

    public function xuLyDangNhap() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $sdt = trim($_POST['soDienThoai']);
            $matKhau = $_POST['matKhau'];
            $user = $this->taiKhoanModel->kiemTraDangNhap($sdt);

            if ($user && $matKhau === $user['matKhau']) {
                $_SESSION['user_id'] = $user['idNguoiDung'];
                $_SESSION['user_name'] = $user['hoTen'];
                $_SESSION['user_role'] = $user['vaiTro'];
                
                // ĐỒNG BỘ: Tạo thêm session này để ăn khớp với phần check isset($_SESSION["user"]) ở file navbar của bạn
                $_SESSION['user'] = $user; 

                switch ($user['vaiTro']) {
                    case 'QUAN_TRI_VIEN': $this->redirect('admin/quanLyTaiKhoan'); break;
                    case 'DUOC_SI': $this->redirect('duocSi/duyetDon'); break;
                    case 'KHACH_HANG': $this->redirect('khachHang/trangChu'); break;
                }
            } else {
                $this->view('khachHang/xacThuc/dangNhap', [
                    'error'    => 'Số điện thoại hoặc mật khẩu không chính xác!',
                    'page_css' => 'xacThuc',
                    'is_auth'  => true
                ]);
            }
        }
    }

    // Hiển thị giao diện Đăng Ký
    public function dangKy() {
        $this->view('khachHang/xacThuc/dangKy', [
            'page_css' => 'xacThuc',
            'is_auth'  => true
        ]);
    }

    public function xuLyDangKy() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $hoTen = trim($_POST['hoTen']);
            $email = trim($_POST['email']);
            $sdt = trim($_POST['soDienThoai']);
            $matKhau = $_POST['matKhau'];

            $result = $this->taiKhoanModel->dangKyKhachHang($hoTen, $email, $sdt, $matKhau);

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
    public function dangXuat() {
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