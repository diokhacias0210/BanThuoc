<?php
/**
 * Controller xử lý việc khách hàng đăng ký/tải lên đơn thuốc (toa thuốc):
 * - Hiển thị trang tải lên đơn thuốc
 * - Nhận đơn thuốc (kèm hình ảnh) từ khách hàng, tự động đối chiếu tên thuốc
 *   với hệ thống và thêm vào giỏ hàng những thuốc khớp được
 */
class dangKeToaThuocController extends Controller
{
    private $dangKeModel;  // Model thao tác dữ liệu đơn thuốc (tạo đơn, thêm chi tiết, tìm thuốc theo tên...)
    private $gioHangModel; // Model thao tác dữ liệu giỏ hàng (lấy/tạo giỏ, thêm item vào giỏ)

    /**
     * Khởi tạo controller, nạp sẵn dangKeToaThuocModel và gioHangModel để dùng cho toàn bộ các hàm bên dưới.
     */
    public function __construct()
    {
        // Gọi model dangKeToaThuocModel
        $this->dangKeModel = $this->model("dangKeToaThuocModel");
        $this->gioHangModel = $this->model("gioHangModel");
    }

    /**
     * Kiểm tra khách hàng đã đăng nhập hay chưa, hỗ trợ cả 2 kiểu lưu session
     * (user_id trực tiếp, hoặc user['idNguoiDung'] lồng bên trong).
     * @return int|null ID khách hàng nếu đã đăng nhập, null nếu chưa.
     */
    private function checkLogin()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // Đảm bảo session đã được khởi động trước khi đọc
        }
        if (isset($_SESSION['user_id'])) return $_SESSION['user_id']; // Kiểu lưu session thứ nhất
        if (isset($_SESSION['user']) && isset($_SESSION['user']['idNguoiDung'])) return $_SESSION['user']['idNguoiDung']; // Kiểu lưu session thứ hai
        return null; // Chưa đăng nhập
    }

    /**
     * Hiển thị trang tải lên đơn thuốc cho khách hàng.
     * Yêu cầu đã đăng nhập; nếu được điều hướng từ trang chi tiết sản phẩm thì tự động
     * điền sẵn tên thuốc đó vào form.
     */
    public function index()
    {
        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            // Chưa đăng nhập -> chuyển hướng sang trang đăng nhập
            $this->redirect('khachHang/xacThuc/dangNhap');
        }

        // Tự động nhận tên thuốc nếu chuyển qua từ trang Chi tiết sản phẩm
        $idThuoc = isset($_GET['idThuoc']) ? intval($_GET['idThuoc']) : 0; // ID thuốc truyền qua query string (nếu có)
        $tenThuocChonSan = ''; // Tên thuốc điền sẵn vào form
        if ($idThuoc > 0) {
            $chiTietModel = $this->model("chiTietThuocModel");
            $thuocInfo = $chiTietModel->getChiTietThuocTheoID($idThuoc);
            if ($thuocInfo) {
                $tenThuocChonSan = $thuocInfo['tenThuoc']; // Lấy tên thuốc để hiển thị sẵn trên form
            }
        }

        $data['title'] = "PharmaCare – Tải lên đơn thuốc";
        $data['page_title'] = "Tải lên đơn thuốc";
        $data['active_tab'] = "taidon";
        $data['page_css'] = "dangKeToaThuoc";
        $data['danhSachThuocModal'] = $this->dangKeModel->layDanhSachThuocSystem(); // Danh sách thuốc hệ thống để gợi ý/autocomplete
        $data['tenThuocChonSan'] = $tenThuocChonSan;

        // Render nội dung view con vào buffer, sau đó nhúng vào layout chung của khách hàng
        ob_start();
        extract($data);
        // Load đúng file view dangKeToaThuoc.php
        require_once APPROOT . '/views/khachHang/dangKeToaThuoc.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    /**
     * API xử lý gửi đơn thuốc từ khách hàng: tạo bản ghi đơn thuốc, lưu hình ảnh đơn thuốc (nếu có),
     * đối chiếu danh sách tên thuốc khách nhập với hệ thống và tự động thêm các thuốc khớp vào giỏ hàng
     * (trạng thái chờ dược sĩ duyệt). Những thuốc không khớp tên trong hệ thống sẽ được dược sĩ xử lý thủ công.
     * @return void In ra JSON và kết thúc request.
     */
    public function guiDonThuoc()
    {
        if (ob_get_length()) ob_clean(); // Xóa buffer output cũ (nếu có) để tránh lẫn nội dung thừa vào JSON trả về
        header('Content-Type: application/json');

        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            // Chưa đăng nhập -> trả về yêu cầu đăng nhập, không xử lý tiếp
            echo json_encode(array(
                'status' => false,
                'requireLogin' => true,
                'message' => 'Vui lòng đăng nhập trước khi gửi đơn thuốc!'
            ));
            exit;
        }

        $ghiChu = isset($_POST['ghiChu']) ? trim($_POST['ghiChu']) : ''; // Ghi chú kèm theo đơn thuốc
        $danhSachThuocInput = isset($_POST['danhSachThuoc']) ? $_POST['danhSachThuoc'] : array(); // Danh sách tên thuốc khách nhập tay

        // Tạo bản ghi đơn thuốc trước, lấy ID để gắn hình ảnh và chi tiết thuốc vào sau
        $idDonThuoc = $this->dangKeModel->taoDonThuoc($idKhachHang, $ghiChu);

        if (!$idDonThuoc) {
            // Tạo đơn thất bại -> dừng lại ngay, không xử lý ảnh/thuốc
            echo json_encode(array('status' => false, 'message' => 'Lỗi lưu thông tin đơn thuốc.'));
            exit;
        }

        // Xử lý upload hình ảnh đơn thuốc (nếu khách có đính kèm ảnh)
        if (isset($_FILES['hinhAnhFiles']) && !empty($_FILES['hinhAnhFiles']['name'][0])) {
            // APPROOT = .../BanThuoc/app, còn public/ nằm ngang hàng với app/
            // (.../BanThuoc/public) nên phải lùi ra ngoài APPROOT 1 cấp rồi mới
            // vào public/. Không dùng đường dẫn tương đối 'public/...' vì nó
            // phụ thuộc vào thư mục làm việc hiện tại lúc script chạy (thường
            // đã là public/ sẵn rồi) và từng gây lưu ảnh lồng vào public/public/.
            $uploadDir = dirname(APPROOT) . '/public/assets/images/uploads/donThuoc/'; // Thư mục vật lý lưu ảnh
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Tạo thư mục nếu chưa tồn tại
            }

            // Duyệt qua từng file ảnh được gửi lên
            foreach ($_FILES['hinhAnhFiles']['name'] as $key => $name) {
                if ($_FILES['hinhAnhFiles']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION); // Lấy phần đuôi file gốc
                    // Đặt tên file mới duy nhất để tránh trùng/ghi đè file cũ
                    $fileName = time() . '_' . $key . '_' . uniqid() . '.' . $ext;

                    if (move_uploaded_file($_FILES['hinhAnhFiles']['tmp_name'][$key], $uploadDir . $fileName)) {
                        $duongDan = 'assets/images/uploads/donThuoc/' . $fileName; // Đường dẫn tương đối lưu vào DB
                        $this->dangKeModel->themHinhAnhDonThuoc($idDonThuoc, $duongDan);
                    }
                }
            }
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang); // Lấy giỏ hàng hiện có hoặc tạo mới nếu chưa có

        $tenKhongKhopHeThong = array(); // thuốc khách gõ nhưng không có trong bảng Thuoc
        $themGioLoi = array();          // thuốc match được nhưng insert vào giỏ vẫn fail
        $soLuongThemGioThanhCong = 0;   // Đếm số thuốc thêm vào giỏ hàng thành công

        if (!empty($danhSachThuocInput) && is_array($danhSachThuocInput)) {
            // Duyệt qua từng tên thuốc khách nhập tay
            foreach ($danhSachThuocInput as $tenThuoc) {
                $tenClean = trim($tenThuoc); // Loại bỏ khoảng trắng thừa
                if (empty($tenClean)) continue; // Bỏ qua tên rỗng

                // Luôn ghi nhận chi tiết đơn thuốc (dù có khớp hệ thống hay không) để dược sĩ tham khảo
                $this->dangKeModel->themChiTietDonThuoc($idDonThuoc, $tenClean, 1);
                $thuoc = $this->dangKeModel->timThuocTheoTen($tenClean); // Tìm thuốc khớp tên trong hệ thống

                // KHÔNG fallback idThuoc = 1 nữa: nếu không tìm thấy thuốc khớp tên
                // trong hệ thống thì bỏ qua bước thêm vào giỏ (tránh vỡ FK / fail âm thầm),
                // đơn thuốc vẫn được ghi nhận bình thường để dược sĩ duyệt thủ công.
                if (!$thuoc) {
                    $tenKhongKhopHeThong[] = $tenClean; // Ghi nhận tên không khớp để báo lại cho khách
                    continue;
                }

                // Thêm thuốc khớp được vào giỏ hàng với trạng thái "KHOA" (chờ dược sĩ duyệt)
                $ok = $this->gioHangModel->themItemVaoGio(
                    $idGioHang, $thuoc['idThuoc'], 1, $thuoc['giaBan'], 'KHOA', $idDonThuoc
                );

                if ($ok) {
                    $soLuongThemGioThanhCong++;
                } else {
                    $themGioLoi[] = $tenClean; // Ghi nhận thuốc khớp tên nhưng thêm vào giỏ bị lỗi
                }
            }
        }

        // Xây dựng thông báo trả về, ghép thêm các lưu ý nếu có thuốc không khớp/thêm giỏ lỗi
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
