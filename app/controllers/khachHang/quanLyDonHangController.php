<?php
/**
 * Controller quản lý đơn hàng của khách hàng đang đăng nhập:
 * - Hiển thị danh sách đơn hàng
 * - Huỷ đơn hàng
 * - Xem chi tiết đơn hàng (dạng trang riêng và dạng popup AJAX)
 */
class QuanLyDonHangController extends Controller
{
    private $donHangModel; // Model thao tác dữ liệu đơn hàng (lấy danh sách, chi tiết, huỷ đơn...)

    /**
     * Khởi tạo controller, nạp sẵn quanLyDonHangModel để dùng cho toàn bộ các hàm bên dưới.
     */
    public function __construct()
    {
        $this->donHangModel = $this->model("quanLyDonHangModel");
    }


    /**
     * Hiển thị trang danh sách đơn hàng của khách hàng đang đăng nhập.
     * Nếu chưa đăng nhập, danh sách sẽ trả về rỗng (không tự động chuyển hướng ở đây).
     */
    public function index()
    {


        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // ID khách hàng đang đăng nhập

        $data['title'] = "PharmaCare – Quản lý đơn hàng";
        $data['page_title'] = "Quản lý đơn hàng";
        $data['active_tab'] = "quanLyDonHang";
        $data['page_css'] = "quanLyDonHang";

        // Quan trọng: phải tạo biến rời $donHangList (không chỉ $data[...])
        // vì View được nạp bằng require_once bên dưới đọc trực tiếp biến này.
        $donHangList = $idKhachHang ? $this->donHangModel->getDonHangTheoKhachHang($idKhachHang) : [];

        // Render nội dung view con vào buffer, sau đó nhúng vào layout chung của khách hàng
        ob_start();
        require_once APPROOT . '/views/khachHang/quanLyDonHang.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    /**
     * API: huỷ đơn hàng của khách hàng đang đăng nhập.
     * @param int|null $idDonHang - ID đơn hàng cần huỷ, nhận qua segment URL (.../huyDonHang/5).
     * @return void In ra JSON và kết thúc request. Lý do huỷ được nhận qua POST.
     */
    public function huyDonHang($idDonHang = null)
    {
        header('Content-Type: application/json');
        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // ID khách hàng đang đăng nhập

        $lyDoHuy = isset($_POST['lyDoHuy']) ? trim($_POST['lyDoHuy']) : ''; // Lý do huỷ đơn, bắt buộc phải nhập

        // Validate: bắt buộc đã đăng nhập, có ID đơn hàng và có lý do huỷ
        if (!$idKhachHang || !$idDonHang || empty($lyDoHuy)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        // Gọi model thực hiện huỷ đơn, model tự kiểm tra đúng chủ đơn hàng bên trong
        $ok = $this->donHangModel->huyDonHang($idDonHang, $idKhachHang, $lyDoHuy);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }

    // ===================== MỚI THÊM =====================
    /**
     * Hiển thị trang chi tiết đơn hàng — được gọi khi bấm vào 1 dòng đơn hàng ở trang danh sách.
     * URL dạng: .../QuanLyDonHang/chiTiet/5
     * @param int|null $idDonHang - ID đơn hàng cần xem chi tiết.
     */
    public function chiTiet($idDonHang = null)
    {
        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // ID khách hàng đang đăng nhập

        // Chưa đăng nhập hoặc thiếu id -> đưa về trang danh sách
        if (!$idKhachHang || !$idDonHang) {
            header('Location: ' . URLROOT . '/QuanLyDonHang');
            exit;
        }

        // Lấy thông tin đơn hàng, đồng thời kiểm tra đúng chủ đơn hàng
        $donHangInfo = $this->donHangModel->layThongTinDonHang($idDonHang, $idKhachHang);

        // Không tìm thấy đơn hàng (không tồn tại hoặc không phải đơn của khách này)
        if (!$donHangInfo) {
            header('Location: ' . URLROOT . '/QuanLyDonHang');
            exit;
        }

        // Lấy danh sách sản phẩm (thuốc) trong đơn hàng
        $sanPhamList = $this->donHangModel->laySanPhamTrongDonHang($idDonHang);

        // Ghép thông tin người nhận / địa chỉ (tạm lấy địa chỉ mặc định hiện tại
        // của khách hàng - xem ghi chú trong Model::layDiaChiGiaoHangMacDinh)
        $diaChi = $this->donHangModel->layDiaChiGiaoHangMacDinh($idKhachHang);
        if ($diaChi) {
            $donHangInfo['tenNguoiNhan']    = $diaChi['tenNguoiNhan'];
            $donHangInfo['soDienThoai']     = $diaChi['soDienThoai'];
            $donHangInfo['diaChiGiaoHang']  = $diaChi['diaChiGiaoHang'];
        }

        $data['title'] = "PharmaCare – Chi tiết đơn hàng #" . $idDonHang;
        $data['page_title'] = "Chi tiết đơn hàng";
        $data['active_tab'] = "quanLyDonHang";
        $data['page_css'] = "chiTietDonHang";

        // Biến rời để view đọc trực tiếp (giống cách index() đang làm với $donHangList)
        // đã có sẵn ở trên: $donHangInfo, $sanPhamList

        // Render nội dung view con vào buffer, sau đó nhúng vào layout chung của khách hàng
        ob_start();
        require_once APPROOT . '/views/khachHang/chiTietDonHang.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // ===================== MỚI THÊM: CHI TIẾT DẠNG POPUP (AJAX) =====================
    /**
     * Trả về JSON thông tin đơn hàng để hiển thị trong modal ở trang danh sách,
     * KHÔNG reload trang. Gọi từ JS: fetch(URLROOT + '/QuanLyDonHang/chiTietAjax/' + id)
     * Dùng lại đúng các hàm Model đã có ở chiTiet(), chỉ khác là trả JSON thay vì render view.
     * @param int|null $idDonHang - ID đơn hàng cần lấy chi tiết.
     * @return void In ra JSON và kết thúc request.
     */
    public function chiTietAjax($idDonHang = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // ID khách hàng đang đăng nhập

        // Validate: bắt buộc đã đăng nhập và có ID đơn hàng
        if (!$idKhachHang || !$idDonHang) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        // Lấy thông tin đơn hàng, đồng thời kiểm tra đúng chủ đơn hàng
        $donHangInfo = $this->donHangModel->layThongTinDonHang($idDonHang, $idKhachHang);

        if (!$donHangInfo) {
            // Không tìm thấy đơn hàng (không tồn tại hoặc không phải đơn của khách này)
            echo json_encode(['status' => false, 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }

        // Lấy danh sách sản phẩm (thuốc) trong đơn hàng
        $sanPhamList = $this->donHangModel->laySanPhamTrongDonHang($idDonHang);

        // Ghép thông tin người nhận / địa chỉ (tạm lấy địa chỉ mặc định hiện tại của khách hàng)
        $diaChi = $this->donHangModel->layDiaChiGiaoHangMacDinh($idKhachHang);
        if ($diaChi) {
            $donHangInfo['tenNguoiNhan']    = $diaChi['tenNguoiNhan'];
            $donHangInfo['soDienThoai']     = $diaChi['soDienThoai'];
            $donHangInfo['diaChiGiaoHang']  = $diaChi['diaChiGiaoHang'];
        }

        echo json_encode([
            'status'      => true,
            'donHangInfo' => $donHangInfo,
            'sanPhamList' => $sanPhamList
        ], JSON_UNESCAPED_UNICODE); // Giữ nguyên tiếng Việt có dấu trong JSON, không escape thành unicode \uXXXX
        exit;
    }
}


