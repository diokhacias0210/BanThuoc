<?php
class QuanLyDonHangController extends Controller
{
    private $donHangModel;

    public function __construct()
    {
        $this->donHangModel = $this->model("quanLyDonHangModel");
    }


    // Trang danh sách đơn hàng của khách hàng đang đăng nhập
    public function index()
    {


        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $data['title'] = "PharmaCare – Quản lý đơn hàng";
        $data['page_title'] = "Quản lý đơn hàng";
        $data['active_tab'] = "quanLyDonHang";
        $data['page_css'] = "quanLyDonHang";

        // Quan trọng: phải tạo biến rời $donHangList (không chỉ $data[...])
        // vì View được nạp bằng require_once bên dưới đọc trực tiếp biến này.
        $donHangList = $idKhachHang ? $this->donHangModel->getDonHangTheoKhachHang($idKhachHang) : [];

        ob_start();
        require_once APPROOT . '/views/khachHang/quanLyDonHang.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // API: huỷ đơn hàng — nhận idDonHang qua segment URL (.../huyDonHang/5), lý do huỷ qua POST
    public function huyDonHang($idDonHang = null)
    {
        header('Content-Type: application/json');
        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $lyDoHuy = isset($_POST['lyDoHuy']) ? trim($_POST['lyDoHuy']) : '';

        if (!$idKhachHang || !$idDonHang || empty($lyDoHuy)) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $ok = $this->donHangModel->huyDonHang($idDonHang, $idKhachHang, $lyDoHuy);
        echo json_encode(['status' => (bool)$ok]);
        exit;
    }

    // ===================== MỚI THÊM =====================
    // Trang chi tiết đơn hàng — được gọi khi bấm vào 1 dòng đơn hàng ở trang danh sách
    // URL dạng: .../QuanLyDonHang/chiTiet/5
    public function chiTiet($idDonHang = null)
    {
        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

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

        ob_start();
        require_once APPROOT . '/views/khachHang/chiTietDonHang.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // ===================== MỚI THÊM: CHI TIẾT DẠNG POPUP (AJAX) =====================
    // Trả về JSON thông tin đơn hàng để hiển thị trong modal ở trang danh sách,
    // KHÔNG reload trang. Gọi từ JS: fetch(URLROOT + '/QuanLyDonHang/chiTietAjax/' + id)
    // Dùng lại đúng các hàm Model đã có ở chiTiet(), chỉ khác là trả JSON thay vì render view.
    public function chiTietAjax($idDonHang = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        $idKhachHang = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        if (!$idKhachHang || !$idDonHang) {
            echo json_encode(['status' => false, 'message' => 'Thiếu dữ liệu hoặc chưa đăng nhập']);
            exit;
        }

        $donHangInfo = $this->donHangModel->layThongTinDonHang($idDonHang, $idKhachHang);

        if (!$donHangInfo) {
            echo json_encode(['status' => false, 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }

        $sanPhamList = $this->donHangModel->laySanPhamTrongDonHang($idDonHang);

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
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
