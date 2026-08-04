<?php
/**
 * Class: QuanLyDanhMucController
 * Controller phụ trách chức năng "Quản lý danh mục thuốc" trong khu vực quản trị (admin).
 * Chức năng tổng quan:
 *  - Hiển thị giao diện quản lý danh mục thuốc (danh sách, thêm/sửa, xóa).
 *  - Cung cấp các API dạng JSON để lấy danh sách, xem chi tiết, lưu (thêm mới/cập nhật)
 *    và xóa danh mục thuốc, phục vụ thao tác AJAX từ phía client.
 */
class QuanLyDanhMucController extends Controller
{
    // Model xử lý truy vấn/thao tác dữ liệu danh mục thuốc từ database
    private $danhMucModel;

    /**
     * Khởi tạo controller: nạp DanhMucModel để dùng cho các thao tác quản lý danh mục.
     */
    public function __construct()
    {
        $this->danhMucModel = $this->model("DanhMucModel");
    }

    /**
     * Hiển thị giao diện quản lý danh mục thuốc (view quanLyDanhMuc) trong adminLayout.
     * Chuẩn bị các dữ liệu meta của trang (tiêu đề, icon, tab đang active, css riêng)
     * rồi render nội dung view vào layout chung của khu vực admin.
     */
    public function index()
    {
        $data['title'] = "Quản Lý Danh Mục Thuốc";        // Tiêu đề trang (thẻ <title>)
        $data['page_title'] = "Quản lý danh mục thuốc";    // Tiêu đề hiển thị trên header trang
        $data['page_icon'] = "fa-solid fa-folder-open";    // Icon hiển thị kèm tiêu đề trang
        $data['active_tab'] = "danhmuc";                   // Tab đang active trên sidebar
        $data['page_css'] = "quanLyDanhMuc";                // Tên file CSS riêng cho trang này

        ob_start();
        // Nạp nội dung view quản lý danh mục, output được bắt lại vào buffer
        require_once APPROOT . '/views/admin/quanLyDanhMuc.php';
        $data['content'] = ob_get_clean(); // Nội dung view, gán vào layout chung

        $this->view('layouts/adminLayout', $data);
    }

    /**
     * API: Trả về danh sách danh mục thuốc dạng JSON, có hỗ trợ tìm kiếm theo từ khóa.
     * Được gọi từ phía client để đổ dữ liệu vào bảng danh mục.
     */
    public function layDanhSach()
    {
        header('Content-Type: application/json');
        // Từ khóa tìm kiếm danh mục; mặc định rỗng nếu không truyền lên (lấy tất cả)
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $list = $this->danhMucModel->getAll($search); // Danh sách danh mục khớp từ khóa tìm kiếm
        echo json_encode(array('status' => true, 'data' => $list));
        exit;
    }

    /**
     * API: Trả về chi tiết một danh mục thuốc theo id, dạng JSON.
     * @param id ID danh mục cần lấy chi tiết
     */
    public function chiTiet($id)
    {
        header('Content-Type: application/json');
        $detail = $this->danhMucModel->getById($id); // Dữ liệu chi tiết danh mục (null nếu không tồn tại)
        if ($detail) {
            echo json_encode(array('status' => true, 'data' => $detail));
        } else {
            echo json_encode(array('status' => false, 'message' => 'Không tìm thấy danh mục'));
        }
        exit;
    }

    /**
     * API: Xử lý lưu dữ liệu danh mục thuốc — thêm mới nếu chưa có id,
     * hoặc cập nhật nếu đã có id được truyền lên. Yêu cầu tên danh mục
     * không được để trống trước khi xử lý.
     */
    public function luu()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idDanhMuc']) ? $_POST['idDanhMuc'] : '';        // ID danh mục (rỗng nếu là thêm mới)
            $ten = isset($_POST['tenDanhMuc']) ? trim($_POST['tenDanhMuc']) : ''; // Tên danh mục (đã trim khoảng trắng)
            $moTa = isset($_POST['moTa']) ? trim($_POST['moTa']) : '';          // Mô tả danh mục (đã trim khoảng trắng)

            // Bắt buộc phải có tên danh mục, dừng xử lý nếu để trống
            if (empty($ten)) {
                echo json_encode(array('status' => false, 'message' => 'Tên danh mục không được để trống'));
                exit;
            }

            // Nếu có id -> cập nhật danh mục hiện có; ngược lại -> tạo mới danh mục
            if (!empty($id)) {
                $result = $this->danhMucModel->update($id, $ten, $moTa);
                $msg = "Đã cập nhật danh mục thuốc thành công!";
            } else {
                $result = $this->danhMucModel->create($ten, $moTa);
                $msg = "Đã thêm danh mục mới thành công!";
            }

            if ($result) {
                echo json_encode(array('status' => true, 'message' => $msg));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Lỗi xử lý dữ liệu hệ thống'));
            }
        }
        exit;
    }

    /**
     * API: Xử lý xóa một danh mục thuốc theo id và trả về thông báo kết quả cho client.
     * Khi xóa thành công, các thuốc thuộc danh mục này sẽ được hệ thống tự động
     * chuyển sang nhóm "Chưa phân loại". Danh mục "Chưa phân loại" mặc định
     * (dùng làm fallback của hệ thống) không thể bị xóa.
     * @param id ID danh mục cần xóa
     */
    public function xoa($id)
    {
        header('Content-Type: application/json');
        if ($this->danhMucModel->delete($id)) {
            echo json_encode(array(
                'status' => true,
                'message' => 'Đã xóa danh mục thành công! Toàn bộ thuốc thuộc danh mục này đã được tự động điều hướng chuyển sang nhóm "Chưa phân loại".'
            ));
        } else {
            echo json_encode(array(
                'status' => false,
                'message' => 'Không thể xóa danh mục này! (Đây có thể là danh mục "Chưa phân loại" mặc định dùng làm fallback của hệ thống).'
            ));
        }
        exit;
    }
}


