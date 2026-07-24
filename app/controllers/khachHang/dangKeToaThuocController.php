<?php
class DangKeToaThuocController extends Controller
{
    private $dangKeModel;
    private $gioHangModel;

    public function __construct()
    {
        $this->dangKeModel = $this->model("dangKeToaThuocModel");
        $this->gioHangModel = $this->model("gioHangModel");
    }

    private function checkLogin()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) return $_SESSION['user_id'];
        if (isset($_SESSION['user']) && isset($_SESSION['user']['idNguoiDung'])) return $_SESSION['user']['idNguoiDung'];
        return null;
    }

    public function index()
    {
        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            $this->redirect('khachHang/xacThuc/dangNhap');
        }

        // Tự động nhận tên thuốc nếu chuyển qua từ trang Chi tiết sản phẩm
        $idThuoc = isset($_GET['idThuoc']) ? intval($_GET['idThuoc']) : 0;
        $tenThuocChonSan = '';
        if ($idThuoc > 0) {
            $chiTietModel = $this->model("chiTietThuocModel");
            $thuocInfo = $chiTietModel->getChiTietThuocTheoID($idThuoc);
            if ($thuocInfo) {
                $tenThuocChonSan = $thuocInfo['tenThuoc'];
            }
        }

        $data['title'] = "PharmaCare – Tải lên đơn thuốc";
        $data['page_title'] = "Tải lên đơn thuốc";
        $data['active_tab'] = "taidon";
        $data['page_css'] = "dangKeToaThuoc";
        $data['danhSachThuocModal'] = $this->dangKeModel->layDanhSachThuocSystem();
        $data['tenThuocChonSan'] = $tenThuocChonSan;

        ob_start();
        extract($data);
        require_once APPROOT . '/views/khachHang/dangKeToaThuoc.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // API XỬ LÝ GỬI ĐƠN THUỐC
    public function guiDonThuoc()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            echo json_encode(array(
                'status' => false,
                'requireLogin' => true,
                'message' => 'Vui lòng đăng nhập trước khi gửi đơn thuốc!'
            ));
            exit;
        }

        $ghiChu = isset($_POST['ghiChu']) ? trim($_POST['ghiChu']) : '';
        $danhSachThuocInput = isset($_POST['danhSachThuoc']) ? $_POST['danhSachThuoc'] : array();

        // 1. Tạo đơn thuốc trong CSDL
        $idDonThuoc = $this->dangKeModel->taoDonThuoc($idKhachHang, $ghiChu);

        if (!$idDonThuoc) {
            echo json_encode(array('status' => false, 'message' => 'Lỗi lưu thông tin đơn thuốc.'));
            exit;
        }

        // 2. Xử lý lưu NHIỀU HÌNH ẢNH vào bảng HinhAnhDonThuoc
        if (isset($_FILES['hinhAnhFiles']) && !empty($_FILES['hinhAnhFiles']['name'][0])) {
            $uploadDir = 'public/assets/images/uploads/donThuoc/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['hinhAnhFiles']['name'] as $key => $name) {
                if ($_FILES['hinhAnhFiles']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $fileName = time() . '_' . $key . '_' . uniqid() . '.' . $ext;

                    if (move_uploaded_file($_FILES['hinhAnhFiles']['tmp_name'][$key], $uploadDir . $fileName)) {
                        $duongDan = 'assets/images/uploads/donThuoc/' . $fileName;
                        $this->dangKeModel->themHinhAnhDonThuoc($idDonThuoc, $duongDan);
                    }
                }
            }
        }

        // 3. Lấy hoặc tạo Giỏ hàng
        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);

        // 4. Lặp qua danh sách thuốc đã chọn -> LƯU MỖI THUỐC THÀNH 1 DÒNG KHOÁ RIÊNG TRONG GIỎ HÀNG
        if (!empty($danhSachThuocInput) && is_array($danhSachThuocInput)) {
            foreach ($danhSachThuocInput as $tenThuoc) {
                $tenClean = trim($tenThuoc);
                if (empty($tenClean)) continue;

                // Thêm vào bảng ChiTietDonThuoc
                $this->dangKeModel->themChiTietDonThuoc($idDonThuoc, $tenClean, 1);

                // Tìm thông tin thuốc trong hệ thống
                $thuoc = $this->dangKeModel->timThuocTheoTen($tenClean);

                $idThuoc = $thuoc ? $thuoc['idThuoc'] : 1;
                $donGia = $thuoc ? $thuoc['giaBan'] : 0;

                // Lưu riêng từng dòng vào ChiTietGioHang với trạng thái KHOA
                $this->gioHangModel->themItemVaoGio($idGioHang, $idThuoc, 1, $donGia, 'KHOA', $idDonThuoc);
            }
        }

        echo json_encode(array(
            'status' => true,
            'message' => 'Gửi đơn thuốc thành công! Các sản phẩm kê đơn đã được đưa vào giỏ hàng ở trạng thái Chờ dược sĩ duyệt.',
            'redirect' => URLROOT . '/khachHang/gioHang'
        ));
        exit;
    }
}
