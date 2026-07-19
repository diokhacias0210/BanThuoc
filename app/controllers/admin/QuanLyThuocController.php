<?php
class QuanLyThuocController extends Controller
{
    private $thuocModel;
    private $danhMucModel;

    public function __construct()
    {
        $this->thuocModel = $this->model("ThuocModel");
        $this->danhMucModel = $this->model("DanhMucModel");
    }

    // Giao diện danh sách sản phẩm
    public function index()
    {
        $data['title'] = "Quản Lý Thuốc";
        $data['page_title'] = "Quản lý thuốc";
        $data['page_icon'] = "fa-solid fa-pills";
        $data['active_tab'] = "thuoc";
        $data['page_css'] = "quanLyThuoc";

        ob_start();
        require_once APPROOT . '/views/admin/quanLyThuoc.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/adminLayout', $data);
    }

    // Giao diện chi tiết thông tin thuốc (Xem chi tiết)
    public function chitiet($id)
    {
        $data['title'] = "Chi Tiết Thuốc";
        $data['page_title'] = "Thông tin chi tiết thuốc";
        $data['page_icon'] = "fa-solid fa-circle-info";
        $data['active_tab'] = "thuoc";
        $data['page_css'] = "quanLyThuoc";
        $data['idThuoc'] = $id;

        $data['topbar_action'] = '
            <a class="btn btn-ghost" href="' . URLROOT . '/admin/quanLyThuoc">
              <i class="fa-solid fa-arrow-left-long"></i> Trở lại danh sách
            </a>';

        ob_start();
        require_once APPROOT . '/views/admin/chiTietThuoc.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/adminLayout', $data);
    }

    // API: Lấy danh sách JSON thuốc phối hợp bộ lọc
    public function getList()
    {
        header('Content-Type: application/json');
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $idDanhMuc = isset($_GET['idDanhMuc']) ? $_GET['idDanhMuc'] : 'all';
        $phanLoai = isset($_GET['phanLoai']) ? $_GET['phanLoai'] : 'all';
        $trangThai = isset($_GET['trangThai']) ? $_GET['trangThai'] : 'all';

        $list = $this->thuocModel->getAll($search, $idDanhMuc, $phanLoai, $trangThai);
        $categories = $this->danhMucModel->getAll();

        echo json_encode(array('status' => true, 'data' => $list, 'categories' => $categories));
        exit;
    }

    // API: Lấy chi tiết thông tin thuốc & danh sách lô kho vận phục vụ trang chi tiết
    public function getDetailData($id)
    {
        header('Content-Type: application/json');
        $thuoc = $this->thuocModel->getById($id);
        if (!$thuoc) {
            echo json_encode(array('status' => false, 'message' => 'Không tìm thấy thuốc'));
            exit;
        }
        $lots = $this->thuocModel->getLotsByThuocId($id);
        echo json_encode(array('status' => true, 'thuoc' => $thuoc, 'lots' => $lots));
        exit;
    }

    // API: Thêm mới hoặc Cập nhật thông tin thuốc
    public function save()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idThuoc']) ? $_POST['idThuoc'] : '';

            $payload = array(
                'tenThuoc' => trim(isset($_POST['tenThuoc']) ? $_POST['tenThuoc'] : ''),
                'idDanhMuc' => isset($_POST['idDanhMuc']) ? $_POST['idDanhMuc'] : null,
                'donViTinh' => trim(isset($_POST['donViTinh']) ? $_POST['donViTinh'] : ''),
                'thanhPhan' => trim(isset($_POST['thanhPhan']) ? $_POST['thanhPhan'] : ''),
                'hamLuong' => trim(isset($_POST['hamLuong']) ? $_POST['hamLuong'] : ''),
                'congDung' => trim(isset($_POST['congDung']) ? $_POST['congDung'] : ''),
                'giaBan' => isset($_POST['giaBan']) ? floatval($_POST['giaBan']) : 0,
                'yeuCauKeDon' => isset($_POST['yeuCauKeDon']) ? $_POST['yeuCauKeDon'] : 'Không kê đơn',
                'gioiHanMua' => isset($_POST['khongGioiHan']) ? -1 : intval(isset($_POST['gioiHanMua']) ? $_POST['gioiHanMua'] : 5),
                'trangThai' => isset($_POST['trangThai']) ? 1 : 0,
                'hinhAnh' => isset($_POST['hinhAnhUrlHienTai']) ? $_POST['hinhAnhUrlHienTai'] : ''
            );

            // Xử lý Upload hình ảnh an toàn tương thích PHP bản cũ
            if (isset($_FILES['hinhAnhFile']) && $_FILES['hinhAnhFile']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['hinhAnhFile']['name'], PATHINFO_EXTENSION);
                $fileName = time() . '_' . uniqid() . '.' . $ext;
                $uploadDir = 'public/assets/images/uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // ĐÃ SỬA: Thay thế toán tử ?? thành cấu trúc toán tử ba ngôi truyền thống
                $tmp_name = isset($_FILES['hinhAnhFile']['tmp_tmp_name']) ? $_FILES['hinhAnhFile']['tmp_tmp_name'] : $_FILES['hinhAnhFile']['tmp_name'];

                if (move_uploaded_file($tmp_name, $uploadDir . $fileName)) {
                    $payload['hinhAnh'] = '/' . $uploadDir . $fileName;
                }
            }

            if (!empty($id)) {
                $result = $this->thuocModel->update($id, $payload);
                $msg = "Đã cập nhật thông tin thuốc thành công!";
            } else {
                $result = $this->thuocModel->create($payload);
                $msg = "Đã thêm thuốc mới vào hệ thống thành công!";
            }

            if ($result) {
                echo json_encode(array('status' => true, 'message' => $msg));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Lỗi xử lý dữ liệu hệ thống cơ sở dữ liệu.'));
            }
        }
        exit;
    }

    // API: Tạm ngưng / Mở bán lại nhanh sản phẩm
    public function toggleStatus($id)
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $thuoc = $this->thuocModel->getById($id);
            if ($thuoc) {
                $newStatus = $thuoc['trangThai'] ? 0 : 1;
                $msg = $newStatus ? "Đã mở kinh doanh lại thuốc thành công!" : "Đã tạm ngưng bán sản phẩm thuốc này trên hệ thống!";
                $this->thuocModel->changeStatus($id, $newStatus);
                echo json_encode(array('status' => true, 'message' => $msg));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Thuốc không tồn tại.'));
            }
        }
        exit;
    }
}
