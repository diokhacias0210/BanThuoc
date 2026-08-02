<?php
/**
 * Class: trangChuController
 * Controller phụ trách hiển thị "Trang chủ" của Khách hàng.
 * Chức năng tổng quan:
 *  - Lấy danh sách thuốc bán chạy nhất để hiển thị mục "Phổ biến".
 *  - Lấy danh sách thuốc mới nhất để hiển thị mục "Mới nhất".
 *  - Xử lý đường dẫn ảnh cho từng thuốc trước khi render ra view trang chủ.
 */
class trangChuController extends Controller
{
    // Model xử lý truy vấn dữ liệu cho trang chủ (thuốc bán chạy, thuốc mới nhất)
    private $trangchuModel;

    /**
     * Khởi tạo controller: nạp trangChuModel để dùng cho các thao tác lấy dữ liệu trang chủ.
     */
    public function __construct()
    {
        $this->trangchuModel = $this->model('trangChuModel');
    }

    /**
     * Hiển thị trang chủ: lấy danh sách thuốc bán chạy nhất và thuốc mới nhất,
     * xử lý đường dẫn ảnh cho từng thuốc, rồi render toàn bộ dữ liệu ra view
     * trang chủ trong layout dành cho Khách hàng.
     */
    public function index()
    {
        // 1. Lấy danh sách thuốc bán chạy (Phổ biến)
        $dsBanChay = $this->trangchuModel->getThuocBanChayNhat(8); // Top 8 thuốc bán chạy nhất
        foreach ($dsBanChay as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh($item['hinhAnh']); // Đường dẫn ảnh đã xử lý để hiển thị
        }
        unset($item); // Hủy tham chiếu sau vòng lặp foreach theo tham chiếu

        // 2. Lấy danh sách tất cả thuốc mới nhất
        $dsMoiNhat = $this->trangchuModel->getThuocMoiNhat(12); // 12 thuốc mới cập nhật gần nhất
        foreach ($dsMoiNhat as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh($item['hinhAnh']); // Đường dẫn ảnh đã xử lý để hiển thị
        }
        unset($item); // Hủy tham chiếu sau vòng lặp foreach theo tham chiếu

        $data = [
            'title' => 'PharmaCare – Nhà thuốc trực tuyến', // Tiêu đề trang (thẻ <title>)
            'page_title' => 'Trang chủ',                     // Tiêu đề hiển thị trên header trang
            'active_tab' => 'trangchu',                      // Tab đang active trên thanh điều hướng
            'page_css' => 'trangChu',                         // Tên file CSS riêng cho trang này
            'dsBanChay' => $dsBanChay,                        // Danh sách thuốc bán chạy truyền ra view
            'dsMoiNhat' => $dsMoiNhat                          // Danh sách thuốc mới nhất truyền ra view
        ];

        // Load View trong Layout
        ob_start();
        $this->view('khachHang/index', $data);
        $content = ob_get_clean(); // Nội dung view trang chủ, dùng để gán vào layout chung

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }
}
