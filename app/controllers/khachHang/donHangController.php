<?php

/**
 * Class: DonHangController
 * Controller phụ trách chức năng "Đơn hàng" dành cho Khách hàng.
 * Chức năng tổng quan:
 *  - Hiển thị danh sách đơn hàng của khách hàng đang đăng nhập (yêu cầu
 *    phải đăng nhập, nếu chưa sẽ chuyển hướng về trang đăng nhập).
 *  - Hiển thị chi tiết một đơn hàng cụ thể theo id.
 */
class DonHangController extends Controller
{

    // Model xử lý truy vấn dữ liệu đơn hàng từ database
    private $donHangModel;

    /**
     * Khởi tạo controller: nạp DonHangModel để dùng cho các thao tác lấy dữ liệu đơn hàng.
     */
    public function __construct()
    {
        $this->donHangModel = $this->model("DonHangModel");
    }

    /**
     * Hiển thị danh sách đơn hàng của khách hàng đang đăng nhập.
     * Nếu chưa đăng nhập (session "user" không tồn tại), chuyển hướng
     * về trang đăng nhập và dừng xử lý ngay.
     */
    public function index()
    {
        if (!isset($_SESSION["user"]))
        {
            header("Location: " . URLROOT . "/khachHang/xacThuc/dangNhap"); // Chưa đăng nhập -> chuyển về trang đăng nhập
            exit();
        }

        $idKhachHang = $_SESSION["user"]["idNguoiDung"]; // ID khách hàng đang đăng nhập, lấy từ session

        $danhSachDonHang =
            $this->donHangModel
            ->getDanhSachDonHang($idKhachHang); // Danh sách đơn hàng của khách hàng này

        $data = [

            "title" => "Đơn hàng", // Tiêu đề trang (thẻ <title>)

            "content" => "khachHang/donHang", // View con chứa nội dung danh sách đơn hàng

            "danhSachDonHang" => $danhSachDonHang // Dữ liệu danh sách đơn hàng truyền ra view

        ];

        $this->view("layouts/khachHangLayout", $data);
    }

    /**
     * Hiển thị chi tiết một đơn hàng theo id.
     * @param idDonHang ID đơn hàng cần xem chi tiết
     */
    public function chiTiet($idDonHang)
    {
        $chiTiet =
            $this->donHangModel
            ->getChiTietDonHang($idDonHang); // Dữ liệu chi tiết đơn hàng (thông tin đơn, danh sách sản phẩm...)

        $data = [

            "title" => "Chi tiết đơn hàng", // Tiêu đề trang (thẻ <title>)

            "content" => "khachHang/chiTietDonHang", // View con chứa nội dung chi tiết đơn hàng

            "chiTiet" => $chiTiet // Dữ liệu chi tiết đơn hàng truyền ra view

        ];

        $this->view("layouts/khachHangLayout", $data);
    }
}



