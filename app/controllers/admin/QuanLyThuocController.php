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
        $images = $this->thuocModel->getImagesByThuocId($id);

        echo json_encode(array(
            'status' => true,
            'thuoc' => $thuoc,
            'lots' => $lots,
            'images' => $images
        ));
        exit;
    }

    // API: Thêm mới hoặc Cập nhật thông tin thuốc
    // API: Thêm mới hoặc Cập nhật thông tin thuốc
    public function save()
    {
        // Xóa mọi output thừa trước đó nếu có
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idThuoc']) ? $_POST['idThuoc'] : '';

            $payload = array(
                'tenThuoc' => trim(isset($_POST['tenThuoc']) ? $_POST['tenThuoc'] : ''),
                'idDanhMuc' => !empty($_POST['idDanhMuc']) ? $_POST['idDanhMuc'] : null,
                'donViTinh' => trim(isset($_POST['donViTinh']) ? $_POST['donViTinh'] : ''),
                'thanhPhan' => trim(isset($_POST['thanhPhan']) ? $_POST['thanhPhan'] : ''),
                'hamLuong' => trim(isset($_POST['hamLuong']) ? $_POST['hamLuong'] : ''),
                'congDung' => trim(isset($_POST['congDung']) ? $_POST['congDung'] : ''),
                'giaBan' => isset($_POST['giaBan']) ? floatval($_POST['giaBan']) : 0,
                'yeuCauKeDon' => isset($_POST['yeuCauKeDon']) ? $_POST['yeuCauKeDon'] : 'Không kê đơn',
                'gioiHanMua' => isset($_POST['khongGioiHan']) ? -1 : intval(isset($_POST['gioiHanMua']) ? $_POST['gioiHanMua'] : 5),
                'trangThai' => isset($_POST['trangThai']) ? 1 : 0,
            );

            // Xử lý xóa ảnh được chỉ định (chỉ khi update)
            if (!empty($id) && isset($_POST['deleteImages']) && is_array($_POST['deleteImages'])) {
                foreach ($_POST['deleteImages'] as $duongDanXoa) {
                    $duongDanXoa = trim($duongDanXoa);
                    if (!empty($duongDanXoa)) {
                        // Xóa trong DB
                        $this->thuocModel->deleteImageByPath($duongDanXoa);
                        // Xóa file vật lý
                        $filePath = APPROOT . '/../public/' . str_replace(URLROOT . '/', '', $duongDanXoa);
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                    }
                }
            }

            // Xử lý Upload nhiều hình ảnh
            $uploadedImages = [];
            if (isset($_FILES['hinhAnhFiles']) && is_array($_FILES['hinhAnhFiles']['name'])) {
                $fileCount = count($_FILES['hinhAnhFiles']['name']);
                $uploadDir = 'assets/images/uploads/thuoc/';
                $fullPath = APPROOT . '/../public/' . $uploadDir;

                if (!is_dir($fullPath)) {
                    @mkdir($fullPath, 0777, true);
                }

                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['hinhAnhFiles']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['hinhAnhFiles']['name'][$i], PATHINFO_EXTENSION);
                        $fileName = time() . '_' . uniqid() . '.' . $ext;
                        $tmp_name = $_FILES['hinhAnhFiles']['tmp_name'][$i];

                        if (@move_uploaded_file($tmp_name, $fullPath . $fileName)) {
                            $uploadedImages[] = URLROOT . '/' . $uploadDir . $fileName;
                        }
                    }
                }
            }

            if (!empty($id)) {
                $result = $this->thuocModel->update($id, $payload);
                // Thêm ảnh mới (nếu có)
                if ($result && !empty($uploadedImages)) {
                    foreach ($uploadedImages as $imgPath) {
                        $this->thuocModel->addImage($id, $imgPath);
                    }
                }
                $msg = "Đã cập nhật thông tin thuốc thành công!";
            } else {
                $idThuoc = $this->thuocModel->create($payload);
                if ($idThuoc && !empty($uploadedImages)) {
                    foreach ($uploadedImages as $imgPath) {
                        $this->thuocModel->addImage($idThuoc, $imgPath);
                    }
                }
                $result = $idThuoc ? true : false;
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
