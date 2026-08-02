<?php
/**
 * Controller quản lý lô thuốc (khu vực Dược sĩ).
 * Xử lý các luồng: hiển thị giao diện quản lý lô, API lấy danh sách thuốc cho dropdown,
 * lấy danh sách/chi tiết lô thuốc (kèm thống kê + phân trang), thêm/cập nhật, và xóa lô thuốc.
 */
class QuanLyLoController extends Controller
{
    private $loThuocModel; // Model thao tác với bảng LoThuoc (lô hàng: mã lô, hạn dùng, số lượng tồn...)
    private $thuocModel;   // Model thao tác với bảng Thuoc (dùng để lấy danh sách thuốc cho dropdown)
    private $danhMucModel; // Model thao tác với bảng DanhMuc (danh mục thuốc, dùng cho bộ lọc)


    /**
     * Khởi tạo controller, load sẵn 3 model dùng xuyên suốt các action bên dưới.
     */
    public function __construct()
    {
        $this->loThuocModel = $this->model("LoThuocModel");
        $this->thuocModel = $this->model("ThuocModel");
        $this->danhMucModel = $this->model("DanhMucModel");
    }

    /**
     * Giao diện quản lý lô thuốc (trang chính, dành cho Dược sĩ).
     * Chuẩn bị dữ liệu cấu hình layout rồi render view quanLyLo.php bên trong duocSiLayout.
     */
    public function index()
    {
        $data['title'] = "Quản Lý Lô Thuốc";
        $data['page_title'] = "Quản lý lô thuốc";
        $data['page_icon'] = "fa-solid fa-boxes-stacked";
        $data['active_tab'] = "lothuoc"; // Đánh dấu tab đang active trên sidebar dược sĩ
        $data['page_css'] = "quanLyLoThuoc"; // Tên file CSS riêng cho trang này

        ob_start(); // Bắt đầu buffer output để nhúng view con vào biến $data['content']
        require_once APPROOT . '/views/duocSi/quanLyLo.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/duocSiLayout', $data);
    }

    /**
     * API: Lấy danh sách thuốc cho dropdown (dược sĩ dùng khi thêm/sửa lô, cần chọn thuốc tương ứng).
     */
    public function layDanhSachThuoc()
    {
        header('Content-Type: application/json');
        $list = $this->thuocModel->getAll();
        echo json_encode(array('status' => true, 'data' => $list));
        exit;
    }

    /**
     * API: Lấy danh sách lô thuốc + thống kê, có hỗ trợ tìm kiếm, lọc theo trạng thái/danh mục
     * và phân trang phía server.
     */
    public function layDanhSach()
    {
        header('Content-Type: application/json');
        // Đọc tham số lọc & phân trang từ query string, mặc định 'all'/1 nếu không truyền
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $idDanhMuc = isset($_GET['idDanhMuc']) ? $_GET['idDanhMuc'] : 'all';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = 8; // Số lô hiển thị trên mỗi trang, cố định

        $list = $this->loThuocModel->getAll($search, $status, $idDanhMuc, $page, $pageSize); // Danh sách lô đã lọc + cắt trang
        $total = $this->loThuocModel->countAll($search, $status, $idDanhMuc); // Tổng số lô khớp điều kiện lọc (dùng tính số trang)
        $stats = $this->loThuocModel->getStats(); // Thống kê tổng quan (VD: số lô sắp hết hạn, hết hàng...)
        $categories = $this->danhMucModel->getAll(); // Danh mục để đổ vào dropdown lọc

        echo json_encode(array(
            'status' => true,
            'data' => $list,
            'total' => $total,
            'stats' => $stats,
            'categories' => $categories,
            'page' => $page,
            'pageSize' => $pageSize
        ));
        exit;
    }

    /**
     * API: Lấy chi tiết 1 lô thuốc theo id (phục vụ mở form sửa).
     * @param int|string $id id của lô thuốc cần lấy chi tiết
     */
    public function layChiTiet($id)
    {
        header('Content-Type: application/json');
        $lo = $this->loThuocModel->getById($id);
        if ($lo) {
            echo json_encode(array('status' => true, 'data' => $lo));
        } else {
            echo json_encode(array('status' => false, 'message' => 'Không tìm thấy lô thuốc.'));
        }
        exit;
    }

    /**
     * API: Thêm mới hoặc Cập nhật lô thuốc (dựa vào có idLo hay không).
     */
    public function luu()
    {
        if (ob_get_length()) ob_clean(); // Xả buffer output cũ (nếu có) để tránh lẫn nội dung thừa vào JSON trả về
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idLo']) ? $_POST['idLo'] : ''; // id rỗng = thêm mới, có giá trị = cập nhật

            // Gom dữ liệu form thành payload chuẩn hoá để lưu xuống CSDL
            $payload = array(
                'idThuoc' => $_POST['idThuoc'],           // Thuốc mà lô này thuộc về
                'maLo' => trim($_POST['maLo']),             // Mã lô do người dùng nhập
                'ngaySanXuat' => !empty($_POST['ngaySanXuat']) ? $_POST['ngaySanXuat'] : null, // Không nhập -> null
                'hanSuDung' => $_POST['hanSuDung'],         // Hạn sử dụng của lô (dùng cho gợi ý FEFO ở nơi khác)
                'soLuongTon' => intval($_POST['soLuongTon']), // Số lượng tồn kho của lô
                'giaNhap' => floatval($_POST['giaNhap'])    // Giá nhập của lô hàng này
            );

            if (!empty($id)) {
                // Trường hợp CẬP NHẬT lô đã tồn tại
                $result = $this->loThuocModel->update($id, $payload);
                $msg = "Đã cập nhật lô thuốc thành công!";
            } else {
                // Trường hợp THÊM MỚI lô
                $result = $this->loThuocModel->create($payload);
                $msg = "Đã thêm lô thuốc mới thành công!";
            }

            if ($result) {
                echo json_encode(array('status' => true, 'message' => $msg));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Lỗi xử lý dữ liệu.'));
            }
        }
        exit;
    }

    /**
     * API: Xóa lô thuốc khỏi hệ thống.
     * @param int|string $id id của lô thuốc cần xóa
     */
    public function xoa($id)
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->loThuocModel->delete($id);
            if ($result) {
                echo json_encode(array('status' => true, 'message' => 'Đã xóa lô thuốc khỏi hệ thống.'));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Không thể xóa lô thuốc.'));
            }
        }
        exit;
    }
}
