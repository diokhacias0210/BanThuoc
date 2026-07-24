<?php
class QuanLyLoController extends Controller
{
    private $loThuocModel;
    private $thuocModel;
    private $danhMucModel;


    public function __construct()
    {
        $this->loThuocModel = $this->model("LoThuocModel");
        $this->thuocModel = $this->model("ThuocModel");
        $this->danhMucModel = $this->model("DanhMucModel");
    }

    // Giao diện quản lý lô thuốc
    public function index()
    {
        $data['title'] = "Quản Lý Lô Thuốc";
        $data['page_title'] = "Quản lý lô thuốc";
        $data['page_icon'] = "fa-solid fa-boxes-stacked";
        $data['active_tab'] = "lothuoc";
        $data['page_css'] = "quanLyLoThuoc";

        ob_start();
        require_once APPROOT . '/views/duocSi/quanLyLo.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/duocSiLayout', $data);
    }

    // API: Lấy danh sách thuốc cho dropdown (dược sĩ dùng)
    public function getListThuoc()
    {
        header('Content-Type: application/json');
        $list = $this->thuocModel->getAll();
        echo json_encode(array('status' => true, 'data' => $list));
        exit;
    }

    // API: Lấy danh sách lô thuốc + thống kê
    public function getList()
    {
        header('Content-Type: application/json');
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $idDanhMuc = isset($_GET['idDanhMuc']) ? $_GET['idDanhMuc'] : 'all';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = 8;

        $list = $this->loThuocModel->getAll($search, $status, $idDanhMuc, $page, $pageSize);
        $total = $this->loThuocModel->countAll($search, $status, $idDanhMuc);
        $stats = $this->loThuocModel->getStats();
        $categories = $this->danhMucModel->getAll();

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

    // API: Lấy chi tiết 1 lô thuốc
    public function detail($id)
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

    // API: Thêm mới hoặc Cập nhật lô thuốc
    public function save()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idLo']) ? $_POST['idLo'] : '';

            $payload = array(
                'idThuoc' => $_POST['idThuoc'],
                'maLo' => trim($_POST['maLo']),
                'ngaySanXuat' => !empty($_POST['ngaySanXuat']) ? $_POST['ngaySanXuat'] : null,
                'hanSuDung' => $_POST['hanSuDung'],
                'soLuongTon' => intval($_POST['soLuongTon']),
                'giaNhap' => floatval($_POST['giaNhap'])
            );

            if (!empty($id)) {
                $result = $this->loThuocModel->update($id, $payload);
                $msg = "Đã cập nhật lô thuốc thành công!";
            } else {
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

    // API: Xóa lô thuốc
    public function delete($id)
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
