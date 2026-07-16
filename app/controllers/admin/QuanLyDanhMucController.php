<?php
class QuanLyDanhMucController extends Controller
{
    private $danhMucModel;

    public function __construct()
    {
        $this->danhMucModel = $this->model("DanhMucModel");
    }

    // Hiển thị giao diện quản lý chính
    public function index()
    {
        $data['title'] = "Quản Lý Danh Mục Thuốc";
        $data['page_title'] = "Quản lý danh mục thuốc";
        $data['page_icon'] = "icon-folder";
        $data['active_tab'] = "danhmuc";
        $data['page_css'] = "quanLyDanhMuc";

        // Sử dụng APPROOT để định vị chính xác file view
        ob_start();
        require_once APPROOT . '/views/admin/quanLyDanhMuc.php';
        $data['content'] = ob_get_clean();

        // Sử dụng APPROOT cho file layout chính
        $this->view('layouts/adminLayout', $data);
    }

    // API: Lấy danh sách danh mục (JSON)
    public function getList()
    {
        header('Content-Type: application/json');
        // Thay đổi ?? thành isset() để tương thích PHP bản cũ
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $list = $this->danhMucModel->getAll($search);
        echo json_encode(array('status' => true, 'data' => $list));
        exit;
    }

    // API: Lấy chi tiết một danh mục
    public function detail($id)
    {
        header('Content-Type: application/json');
        $detail = $this->danhMucModel->getById($id);
        if ($detail) {
            echo json_encode(array('status' => true, 'data' => $detail));
        } else {
            echo json_encode(array('status' => false, 'message' => 'Không tìm thấy danh mục'));
        }
        exit;
    }

    // API: Xử lý Lưu dữ liệu (Thêm mới hoặc Cập nhật)
    public function save()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idDanhMuc']) ? $_POST['idDanhMuc'] : '';
            $ten = isset($_POST['tenDanhMuc']) ? trim($_POST['tenDanhMuc']) : '';
            $moTa = isset($_POST['moTa']) ? trim($_POST['moTa']) : '';

            if (empty($ten)) {
                echo json_encode(array('status' => false, 'message' => 'Tên danh mục không được để trống'));
                exit;
            }

            if (!empty($id)) {
                // Thực hiện cập nhật
                $result = $this->danhMucModel->update($id, $ten, $moTa);
                $msg = "Đã cập nhật danh mục thuốc thành công!";
            } else {
                // Thực hiện thêm mới
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

    // API: Xử lý xóa danh mục
    public function delete($id)
    {
        header('Content-Type: application/json');
        if ($this->danhMucModel->delete($id)) {
            echo json_encode(array('status' => true, 'message' => 'Đã xóa danh mục và điều chuyển dữ liệu thuốc liên quan!'));
        } else {
            echo json_encode(array('status' => false, 'message' => 'Không thể xóa danh mục này'));
        }
        exit;
    }
}
