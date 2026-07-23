<?php
class BaoCaoThongKeController extends Controller
{
    private $thongKeModel;

    public function __construct()
    {
        $this->thongKeModel = $this->model("ThongKeModel");
    }

    // Hiển thị giao diện báo cáo
    public function index()
    {
        $data['title'] = "Báo Cáo Thống Kê";
        $data['page_title'] = "Báo cáo thống kê kinh doanh";
        $data['page_icon'] = "fa-solid fa-chart-column";
        $data['active_tab'] = "baocao";
        $data['page_css'] = "baoCaoThongKe";

        ob_start();
        require_once APPROOT . '/views/admin/baoCaoThongKe.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/adminLayout', $data);
    }

    // API: Trả về JSON tổng quan và chi tiết danh sách bán hàng theo ngày
    public function getData()
    {
        header('Content-Type: application/json');
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');

        $overview = $this->thongKeModel->getOverviewStats($startDate, $endDate);
        $medicineStats = $this->thongKeModel->getMedicineSalesStats($startDate, $endDate);

        echo json_encode(array(
            'status' => true,
            'overview' => $overview,
            'medicines' => $medicineStats
        ));
        exit;
    }
}
