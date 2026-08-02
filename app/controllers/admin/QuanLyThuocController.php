<?php
/**
 * Controller quản lý thuốc (khu vực Admin).
 * Xử lý các luồng: hiển thị danh sách/chi tiết thuốc, API lấy dữ liệu JSON,
 * thêm/cập nhật thuốc (kèm upload/xoá ảnh), và bật/tắt trạng thái kinh doanh của thuốc.
 */
class QuanLyThuocController extends Controller
{
    private $thuocModel;    // Model thao tác với bảng Thuoc (và các bảng liên quan: Lo, HinhAnh)
    private $danhMucModel;  // Model thao tác với bảng DanhMuc (danh mục thuốc)

    /**
     * Khởi tạo controller, load sẵn 2 model dùng xuyên suốt các action bên dưới.
     */
    public function __construct()
    {
        $this->thuocModel = $this->model("ThuocModel");
        $this->danhMucModel = $this->model("DanhMucModel");
    }

    /**
     * Giao diện danh sách sản phẩm (trang chính "Quản lý thuốc").
     * Chuẩn bị dữ liệu cấu hình layout rồi render view quanLyThuoc.php bên trong adminLayout.
     */
    public function index()
    {
        $data['title'] = "Quản Lý Thuốc";
        $data['page_title'] = "Quản lý thuốc";
        $data['page_icon'] = "fa-solid fa-pills";
        $data['active_tab'] = "thuoc";   // Đánh dấu tab đang active trên sidebar admin
        $data['page_css'] = "quanLyThuoc"; // Tên file CSS riêng cho trang này

        ob_start(); // Bắt đầu buffer output để nhúng view con vào biến $data['content']
        require_once APPROOT . '/views/admin/quanLyThuoc.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/adminLayout', $data);
    }

    /**
     * Giao diện chi tiết thông tin thuốc (Xem chi tiết).
     * @param int|string $id id của thuốc cần xem chi tiết
     */
    public function chiTiet($id)
    {
        $data['title'] = "Chi Tiết Thuốc";
        $data['page_title'] = "Thông tin chi tiết thuốc";
        $data['page_icon'] = "fa-solid fa-circle-info";
        $data['active_tab'] = "thuoc";
        $data['page_css'] = "chiTietThuoc";
        $data['idThuoc'] = $id; // Truyền id xuống view để JS gọi API lấy chi tiết

        // Nút "Trở lại danh sách" ở topbar, được nhúng thẳng HTML vào layout
        $data['topbar_action'] = '
            <a class="btn btn-ghost" href="' . URLROOT . '/admin/quanLyThuoc">
              <i class="fa-solid fa-arrow-left-long"></i> Trở lại danh sách
            </a>';

        extract($data); // Tách các phần tử $data thành biến riêng lẻ để view chiTietThuoc.php dùng trực tiếp
        ob_start();
        require_once APPROOT . '/views/admin/chiTietThuoc.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/adminLayout', $data);
    }

    /**
     * API: Lấy danh sách JSON thuốc phối hợp bộ lọc (tìm kiếm, danh mục, phân loại, trạng thái).
     * Trả về cả danh sách thuốc lẫn danh sách danh mục để render bộ lọc trên giao diện.
     */
    public function layDanhSach()
    {
        header('Content-Type: application/json');
        // Đọc tham số lọc từ query string, mặc định 'all'/'' nếu không truyền
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $idDanhMuc = isset($_GET['idDanhMuc']) ? $_GET['idDanhMuc'] : 'all';
        $phanLoai = isset($_GET['phanLoai']) ? $_GET['phanLoai'] : 'all';
        $trangThai = isset($_GET['trangThai']) ? $_GET['trangThai'] : 'all';

        $list = $this->thuocModel->getAll($search, $idDanhMuc, $phanLoai, $trangThai); // Danh sách thuốc đã lọc
        $categories = $this->danhMucModel->getAll(); // Toàn bộ danh mục để đổ vào dropdown lọc

        echo json_encode(array('status' => true, 'data' => $list, 'categories' => $categories));
        exit;
    }

    /**
     * API: Lấy chi tiết thông tin thuốc & danh sách lô kho vận phục vụ trang chi tiết.
     * @param int|string $id id của thuốc cần lấy chi tiết
     */
    public function layChiTietDuLieu($id)
    {
        header('Content-Type: application/json');
        $thuoc = $this->thuocModel->getById($id);
        if (!$thuoc) {
            echo json_encode(array('status' => false, 'message' => 'Không tìm thấy thuốc'));
            exit;
        }
        $lots = $this->thuocModel->getLotsByThuocId($id);     // Danh sách lô hàng (số lô, hạn dùng, số lượng tồn) của thuốc
        $images = $this->thuocModel->getImagesByThuocId($id); // Danh sách hình ảnh gắn với thuốc

        echo json_encode(array(
            'status' => true,
            'thuoc' => $thuoc,
            'lots' => $lots,
            'images' => $images
        ));
        exit;
    }

    /**
     * API: Thêm mới hoặc Cập nhật thông tin thuốc (dựa vào có idThuoc hay không).
     * Xử lý luôn 2 tác vụ phụ: xoá ảnh cũ được chỉ định, và upload nhiều ảnh mới.
     */
    public function luu()
    {
        if (ob_get_length()) ob_clean(); // Xả buffer output cũ (nếu có) để tránh lẫn nội dung thừa vào JSON trả về
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['idThuoc']) ? $_POST['idThuoc'] : ''; // id rỗng = thêm mới, có giá trị = cập nhật

            // Gom dữ liệu form thành payload chuẩn hoá để lưu xuống CSDL
            $payload = array(
                'tenThuoc' => trim(isset($_POST['tenThuoc']) ? $_POST['tenThuoc'] : ''),
                'idDanhMuc' => !empty($_POST['idDanhMuc']) ? $_POST['idDanhMuc'] : null, // Không chọn danh mục -> null
                'donViTinh' => trim(isset($_POST['donViTinh']) ? $_POST['donViTinh'] : ''),
                'thanhPhan' => trim(isset($_POST['thanhPhan']) ? $_POST['thanhPhan'] : ''),
                'hamLuong' => trim(isset($_POST['hamLuong']) ? $_POST['hamLuong'] : ''),
                'congDung' => trim(isset($_POST['congDung']) ? $_POST['congDung'] : ''),
                'giaBan' => isset($_POST['giaBan']) ? floatval($_POST['giaBan']) : 0,
                'yeuCauKeDon' => isset($_POST['yeuCauKeDon']) ? $_POST['yeuCauKeDon'] : 'Không kê đơn', // Mặc định thuốc không cần kê đơn
                // Nếu tick "Không giới hạn" -> gioiHanMua = -1, ngược lại lấy giá trị nhập (mặc định 5)
                'gioiHanMua' => isset($_POST['khongGioiHan']) ? -1 : intval(isset($_POST['gioiHanMua']) ? $_POST['gioiHanMua'] : 5),
                'trangThai' => isset($_POST['trangThai']) ? 1 : 0, // Checkbox có tick mới gửi lên -> có key nghĩa là đang bán (1)
            );

            // Xử lý xóa ảnh được chỉ định (chỉ khi update, vì thêm mới chưa có ảnh cũ để xoá)
            if (!empty($id) && isset($_POST['deleteImages']) && is_array($_POST['deleteImages'])) {
                foreach ($_POST['deleteImages'] as $duongDanXoa) {
                    $duongDanXoa = trim($duongDanXoa);
                    if (!empty($duongDanXoa)) {
                        $this->thuocModel->deleteImageByPath($duongDanXoa); // Xoá bản ghi ảnh trong CSDL
                        // Chuyển URL công khai về path vật lý trên server để xoá file thật
                        $filePath = APPROOT . '/../public/' . str_replace(URLROOT . '/', '', $duongDanXoa);
                        if (file_exists($filePath)) {
                            @unlink($filePath); // Dùng @ để bỏ qua cảnh báo nếu xoá file thất bại (không chặn luồng chính)
                        }
                    }
                }
            }

            // Xử lý Upload nhiều hình ảnh mới
            $uploadedImages = []; // Danh sách URL công khai của các ảnh vừa upload thành công
            if (isset($_FILES['hinhAnhFiles']) && is_array($_FILES['hinhAnhFiles']['name'])) {
                $fileCount = count($_FILES['hinhAnhFiles']['name']);
                $uploadDir = 'assets/images/uploads/thuoc/'; // Thư mục lưu ảnh thuốc, tính từ thư mục public
                $fullPath = APPROOT . '/../public/' . $uploadDir; // Path vật lý tương ứng trên server

                if (!is_dir($fullPath)) {
                    @mkdir($fullPath, 0777, true); // Tự tạo thư mục nếu chưa tồn tại
                }

                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['hinhAnhFiles']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['hinhAnhFiles']['name'][$i], PATHINFO_EXTENSION);
                        $fileName = time() . '_' . uniqid() . '.' . $ext; // Đặt tên file theo timestamp + uniqid để tránh trùng
                        $tmp_name = $_FILES['hinhAnhFiles']['tmp_name'][$i];

                        if (@move_uploaded_file($tmp_name, $fullPath . $fileName)) {
                            $uploadedImages[] = URLROOT . '/' . $uploadDir . $fileName; // Lưu URL công khai để gắn vào CSDL sau
                        }
                    }
                }
            }

            if (!empty($id)) {
                // Trường hợp CẬP NHẬT thuốc đã tồn tại
                $result = $this->thuocModel->update($id, $payload);
                if ($result && !empty($uploadedImages)) {
                    foreach ($uploadedImages as $imgPath) {
                        $this->thuocModel->addImage($id, $imgPath); // Gắn thêm ảnh mới vào thuốc đang sửa
                    }
                }
                $msg = "Đã cập nhật thông tin thuốc thành công!";
            } else {
                // Trường hợp THÊM MỚI thuốc
                $idThuoc = $this->thuocModel->create($payload);
                if ($idThuoc && !empty($uploadedImages)) {
                    foreach ($uploadedImages as $imgPath) {
                        $this->thuocModel->addImage($idThuoc, $imgPath); // Gắn ảnh vừa upload vào thuốc vừa tạo
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

    /**
     * API: Tạm ngưng / Mở bán lại nhanh sản phẩm (đảo ngược trạng thái trangThai hiện tại).
     * @param int|string $id id của thuốc cần đổi trạng thái
     */
    public function doiTrangThai($id)
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $thuoc = $this->thuocModel->getById($id);
            if ($thuoc) {
                $newStatus = $thuoc['trangThai'] ? 0 : 1; // Đảo trạng thái: đang bán (1) <-> tạm ngưng (0)
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
