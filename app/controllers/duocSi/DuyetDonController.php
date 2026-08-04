<?php
/**
 * Class: DuyetDonController
 * Controller phụ trách chức năng "Duyệt đơn thuốc" dành cho Dược sĩ / Quản trị viên.
 * Chức năng tổng quan:
 *  - Kiểm soát quyền truy cập: chỉ cho phép vai trò DUOC_SI hoặc QUAN_TRI_VIEN
 *    sử dụng các chức năng của controller này.
 *  - Hiển thị giao diện danh sách đơn thuốc kê đơn cần duyệt.
 *  - Cung cấp các API dạng JSON để lấy danh sách, xem chi tiết, duyệt từng đơn,
 *    duyệt tất cả đơn đang chờ, và từ chối đơn kèm lý do.
 */
class DuyetDonController extends Controller
{
    // Model xử lý truy vấn/thao tác dữ liệu đơn thuốc kê đơn từ database
    private $duyetDonModel;

    /**
     * Khởi tạo controller: nạp DuyetDonModel để dùng cho các thao tác duyệt đơn.
     */
    public function __construct()
    {
        $this->duyetDonModel = $this->model('DuyetDonModel');
    }

    /**
     * Kiểm tra vai trò của tài khoản đang đăng nhập; nếu không phải Dược sĩ
     * hoặc Quản trị viên thì hủy phiên đăng nhập và chuyển hướng về trang
     * đăng nhập. Được gọi ở đầu mọi hàm xử lý của controller này để bảo vệ
     * toàn bộ chức năng duyệt đơn khỏi truy cập trái phép.
     */
    private function ensureAllowedRole()
    {
        $role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null; // Vai trò tài khoản đang đăng nhập
        if ($role !== 'DUOC_SI' && $role !== 'QUAN_TRI_VIEN') {
            session_unset();
            session_destroy();
            header('Location: ' . URLROOT . '/khachHang/xacThuc/dangNhap');
            exit;
        }
    }

    /**
     * Hiển thị giao diện danh sách đơn thuốc cần duyệt (view duyetDon) trong
     * layout dành cho Dược sĩ. Chuẩn bị các dữ liệu meta của trang trước khi render.
     */
    public function index()
    {
        $this->ensureAllowedRole();

        $data['title'] = 'Duyệt đơn thuốc';          // Tiêu đề trang (thẻ <title>)
        $data['page_title'] = 'Duyệt thuốc kê đơn';   // Tiêu đề hiển thị trên header trang
        $data['active_tab'] = 'donthuoc';             // Tab đang active trên sidebar
        $data['page_css'] = 'duyetDon';                // Tên file CSS riêng cho trang này

        ob_start();
        // Nạp nội dung view duyệt đơn thuốc, output được bắt lại vào buffer
        require_once APPROOT . '/views/duocSi/duyetDon.php';
        $data['content'] = ob_get_clean(); // Nội dung view, gán vào layout chung

        $this->view('layouts/duocSiLayout', $data);
    }

    /**
     * API: Trả về danh sách đơn thuốc dạng JSON, có hỗ trợ tìm kiếm, lọc theo
     * trạng thái và phân trang. Kèm theo tổng số bản ghi và số đơn đang chờ
     * duyệt (dùng để cập nhật badge trên giao diện).
     */
    public function layDanhSach()
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';       // Từ khóa tìm kiếm (mã yêu cầu/tên khách hàng)
        $status = isset($_GET['status']) ? trim($_GET['status']) : 'all';    // Trạng thái đang lọc (mặc định: tất cả)
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;    // Trang hiện tại, tối thiểu là 1
        $pageSize = 8; // Số dòng hiển thị mỗi trang

        try {
            $items = $this->duyetDonModel->getList($search, $status, $page, $pageSize); // Danh sách đơn thuốc của trang hiện tại
            $total = $this->duyetDonModel->countList($search, $status); // Tổng số bản ghi khớp bộ lọc (để tính phân trang)

            echo json_encode([
                'status' => true,
                'data' => $items,
                'total' => $total,
                'page' => $page,
                'pageSize' => $pageSize,
                'pendingCount' => $this->duyetDonModel->getPendingCount() // Số đơn đang chờ duyệt (toàn hệ thống)
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Trả về chi tiết một đơn thuốc theo id, dạng JSON.
     * @param id ID đơn thuốc cần lấy chi tiết
     */
    public function layChiTiet($id)
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');

        try {
            $item = $this->duyetDonModel->getById($id); // Dữ liệu chi tiết đơn thuốc (null nếu không tồn tại)
            if ($item) {
                echo json_encode(['status' => true, 'data' => $item]);
            } else {
                echo json_encode(['status' => false, 'message' => 'Không tìm thấy đơn thuốc.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Duyệt một đơn thuốc theo id, gán Dược sĩ đang đăng nhập là người duyệt.
     * @param id ID đơn thuốc cần duyệt
     */
    public function duyet($id)
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');

        $idDuocSi = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null; // ID Dược sĩ đang đăng nhập (người thực hiện duyệt)

        try {
            $ok = $this->duyetDonModel->updateStatus($id, 'DA_DUYET', $idDuocSi);
            if ($ok) {
                echo json_encode(['status' => true, 'message' => 'Đã duyệt đơn thuốc thành công.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Không thể duyệt đơn thuốc.']);
            }
        } catch (PDOException $e) {
            // Bắt lỗi thật từ CSDL (VD: vi phạm khóa ngoại idDuocSi nếu tài khoản
            // Dược sĩ đang đăng nhập chưa có bản ghi trong bảng DuocSi) thay vì
            // để PHP crash trắng trang khiến JS hiểu nhầm thành lỗi kết nối chung chung.
            echo json_encode(['status' => false, 'message' => 'Lỗi CSDL khi duyệt đơn: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Duyệt hàng loạt toàn bộ đơn thuốc đang ở trạng thái chờ duyệt,
     * gán Dược sĩ đang đăng nhập là người duyệt cho tất cả các đơn.
     */
    public function duyetTatCa()
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');
        $idDuocSi = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null; // ID Dược sĩ đang đăng nhập (người thực hiện duyệt)

        try {
            $items = $this->duyetDonModel->getList('', 'CHO_DUYET', 1, 1000); // Toàn bộ đơn đang chờ duyệt (giới hạn 1000 bản ghi)
            $updated = 0; // Đếm số đơn đã được duyệt thành công

            foreach ($items as $item) {
                if ($this->duyetDonModel->updateStatus($item['idDonThuoc'], 'DA_DUYET', $idDuocSi)) {
                    $updated++;
                }
            }

            echo json_encode(['status' => true, 'message' => "Đã duyệt {$updated} đơn thuốc.", 'updated' => $updated]);
        } catch (PDOException $e) {
            echo json_encode(['status' => false, 'message' => 'Lỗi CSDL khi duyệt tất cả: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Từ chối một đơn thuốc theo id kèm lý do bắt buộc, gán Dược sĩ
     * đang đăng nhập là người thực hiện từ chối.
     * @param id ID đơn thuốc cần từ chối
     */
    public function tuChoi($id)
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');

        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : ''; // Lý do từ chối do Dược sĩ nhập
        if (empty($reason)) {
            echo json_encode(['status' => false, 'message' => 'Vui lòng nhập lý do từ chối.']);
            exit;
        }

        $idDuocSi = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null; // ID Dược sĩ đang đăng nhập (người thực hiện từ chối)

        try {
            $ok = $this->duyetDonModel->updateStatus($id, 'TU_CHOI', $idDuocSi, $reason);
            if ($ok) {
                echo json_encode(['status' => true, 'message' => 'Đã từ chối đơn thuốc.']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Không thể từ chối đơn thuốc.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => false, 'message' => 'Lỗi CSDL khi từ chối đơn: ' . $e->getMessage()]);
        }
        exit;
    }
}


