<?php
/**
 * Class: BaoCaoThongKeController
 * Controller phụ trách chức năng "Báo cáo thống kê" trong khu vực quản trị (admin).
 * Chức năng tổng quan:
 *  - Hiển thị giao diện trang báo cáo thống kê kinh doanh (view baoCaoThongKe).
 *  - Cung cấp API trả về dữ liệu JSON (tổng quan doanh thu và thống kê bán thuốc)
 *    theo khoảng thời gian được truyền vào, phục vụ vẽ biểu đồ/bảng ở phía client.
 */
class BaoCaoThongKeController extends Controller
{
    // Model xử lý truy vấn dữ liệu thống kê từ database
    private $thongKeModel;

    /**
     * Khởi tạo controller: nạp ThongKeModel để dùng cho các thao tác thống kê.
     */
    public function __construct()
    {
        $this->thongKeModel = $this->model("ThongKeModel");
    }

    /**
     * Hiển thị giao diện trang báo cáo thống kê (view baoCaoThongKe) trong adminLayout.
     * Chuẩn bị các dữ liệu meta của trang (tiêu đề, icon, tab đang active, css riêng)
     * rồi render nội dung view vào layout chung của khu vực admin.
     */
    public function index()
    {
        $data['title'] = "Báo Cáo Thống Kê";                          // Tiêu đề trang (thẻ <title>)
        $data['page_title'] = "Báo cáo thống kê kinh doanh";           // Tiêu đề hiển thị trên header trang
        $data['page_icon'] = "fa-solid fa-chart-column";               // Icon hiển thị kèm tiêu đề trang
        $data['active_tab'] = "baocao";                                // Tab đang active trên sidebar
        $data['page_css'] = "baoCaoThongKe";                           // Tên file CSS riêng cho trang này

        ob_start();
        // Nạp nội dung view báo cáo thống kê, output được bắt lại vào buffer
        require_once APPROOT . '/views/admin/baoCaoThongKe.php';
        $data['content'] = ob_get_clean(); // Nội dung view, gán vào layout chung

        $this->view('layouts/adminLayout', $data);
    }

    /**
     * API: Trả về dữ liệu JSON gồm số liệu tổng quan và thống kê chi tiết
     * doanh số bán thuốc theo khoảng thời gian (startDate - endDate).
     * Được gọi từ phía client (JS) để đổ dữ liệu vào biểu đồ/bảng báo cáo.
     */
    public function layDuLieu()
    {
        header('Content-Type: application/json');
        // Ngày bắt đầu lọc thống kê; mặc định là ngày đầu tháng hiện tại nếu không truyền lên
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        // Ngày kết thúc lọc thống kê; mặc định là ngày hiện tại nếu không truyền lên
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');

        // Số liệu tổng quan (ví dụ: tổng doanh thu, tổng đơn hàng...) trong khoảng thời gian
        $overview = $this->thongKeModel->getOverviewStats($startDate, $endDate);
        // Thống kê chi tiết doanh số bán theo từng loại thuốc trong khoảng thời gian
        $medicineStats = $this->thongKeModel->getMedicineSalesStats($startDate, $endDate);

        echo json_encode(array(
            'status' => true,
            'overview' => $overview,     // Dữ liệu tổng quan trả về cho client
            'medicines' => $medicineStats // Dữ liệu thống kê theo thuốc trả về cho client
        ));
        exit;
    }
}


