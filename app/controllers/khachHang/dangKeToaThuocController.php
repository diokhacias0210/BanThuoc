<?php
class dangKeToaThuocController extends Controller
{
    private $dangKeModel;
    private $gioHangModel;

    public function __construct()
    {
        // Gọi model dangKeToaThuocModel
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
        // Load đúng file view dangKeToaThuoc.php
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

        $idDonThuoc = $this->dangKeModel->taoDonThuoc($idKhachHang, $ghiChu);

        if (!$idDonThuoc) {
            echo json_encode(array('status' => false, 'message' => 'Lỗi lưu thông tin đơn thuốc.'));
            exit;
        }

        if (isset($_FILES['hinhAnhFiles']) && !empty($_FILES['hinhAnhFiles']['name'][0])) {
            // APPROOT = .../BanThuoc/app, còn public/ nằm ngang hàng với app/
            // (.../BanThuoc/public) nên phải lùi ra ngoài APPROOT 1 cấp rồi mới
            // vào public/. Không dùng đường dẫn tương đối 'public/...' vì nó
            // phụ thuộc vào thư mục làm việc hiện tại lúc script chạy (thường
            // đã là public/ sẵn rồi) và từng gây lưu ảnh lồng vào public/public/.
            $uploadDir = dirname(APPROOT) . '/public/assets/images/uploads/donThuoc/';
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

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);

        $tenKhongKhopHeThong = array(); // thuốc khách gõ nhưng không có trong bảng Thuoc
        $themGioLoi = array();          // thuốc match được nhưng insert vào giỏ vẫn fail
        $soLuongThemGioThanhCong = 0;

        if (!empty($danhSachThuocInput) && is_array($danhSachThuocInput)) {
            foreach ($danhSachThuocInput as $tenThuoc) {
                $tenClean = trim($tenThuoc);
                if (empty($tenClean)) continue;

                $this->dangKeModel->themChiTietDonThuoc($idDonThuoc, $tenClean, 1);
                $thuoc = $this->dangKeModel->timThuocTheoTen($tenClean);

                // KHÔNG fallback idThuoc = 1 nữa: nếu không tìm thấy thuốc khớp tên
                // trong hệ thống thì bỏ qua bước thêm vào giỏ (tránh vỡ FK / fail âm thầm),
                // đơn thuốc vẫn được ghi nhận bình thường để dược sĩ duyệt thủ công.
                if (!$thuoc) {
                    $tenKhongKhopHeThong[] = $tenClean;
                    continue;
                }

                $ok = $this->gioHangModel->themItemVaoGio(
                    $idGioHang, $thuoc['idThuoc'], 1, $thuoc['giaBan'], 'KHOA', $idDonThuoc
                );

                if ($ok) {
                    $soLuongThemGioThanhCong++;
                } else {
                    $themGioLoi[] = $tenClean;
                }
            }
        }

        $message = 'Gửi đơn thuốc thành công!';
        if ($soLuongThemGioThanhCong > 0) {
            $message .= ' Các sản phẩm kê đơn khớp hệ thống đã được đưa vào giỏ hàng ở trạng thái Chờ dược sĩ duyệt.';
        }
        if (!empty($tenKhongKhopHeThong)) {
            $message .= ' Lưu ý: ' . count($tenKhongKhopHeThong) . ' thuốc bạn nhập chưa có trong hệ thống nên chưa hiện trong giỏ hàng, dược sĩ sẽ xử lý thủ công (' . implode(', ', $tenKhongKhopHeThong) . ').';
        }

        echo json_encode(array(
            'status' => true,
            'message' => $message,
            'soLuongThemGioThanhCong' => $soLuongThemGioThanhCong,
            'tenKhongKhopHeThong' => $tenKhongKhopHeThong,
            'themGioLoi' => $themGioLoi,
            'redirect' => URLROOT . '/khachHang/gioHang'
        ));
        exit;
    }
}
