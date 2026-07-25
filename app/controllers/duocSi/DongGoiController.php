<?php
class DongGoiController extends Controller
{
    private $dongGoiModel;

    public function __construct()
    {
        $this->dongGoiModel = $this->model('DongGoiModel');
    }

    // Trang danh sách đơn hàng cần đóng gói
    public function index()
    {
        $donHangKhoList = $this->dongGoiModel->layDanhSachDonCanDongGoi();

        // Chuẩn hoá dữ liệu để JS phía View dùng được (giữ nguyên tên trường tiếng Việt gốc)
        foreach ($donHangKhoList as &$dh) {
            $dh['idDonHang']   = (int) $dh['idDonHang'];
            $dh['tongTien']    = (float) $dh['tongTien'];
            $dh['soLoaiThuoc'] = (int) $dh['soLoaiThuoc'];
            $dh['tongSoThuoc'] = (int) $dh['tongSoThuoc'];
        }

        $data['title'] = "PharmaCare – Xử lý & đóng gói";
        $data['page_title'] = "Xử lý & đóng gói";
        $data['active_tab'] = "dongoi";
        $data['page_css'] = "dongGoiDonHang";
        $data['donHangKhoList'] = $donHangKhoList;

        ob_start();
        extract($data);
        require_once APPROOT . '/views/duocSi/dongGoi.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/duocSiLayout', $data);
    }

    // API: lấy chi tiết 1 đơn hàng (danh sách thuốc + gợi ý lô FEFO) để đổ vào modal đóng gói
    public function layChiTietDon($idDonHang = null)
    {
        header('Content-Type: application/json');

        $idDonHang = (int) $idDonHang;
        if ($idDonHang <= 0) {
            echo json_encode(['status' => false, 'message' => 'Thiếu mã đơn hàng']);
            exit;
        }

        $thongTin = $this->dongGoiModel->layThongTinDonHang($idDonHang);
        if (!$thongTin) {
            echo json_encode(['status' => false, 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }

        $diaChi = $this->dongGoiModel->layDiaChiMacDinh($thongTin['idKhachHang']);
        $chiTiet = $this->dongGoiModel->layChiTietDeDongGoi($idDonHang);

        echo json_encode([
            'status' => true,
            'donHang' => [
                'idDonHang'       => (int) $thongTin['idDonHang'],
                'hoTen'           => $thongTin['hoTen'],
                'trangThai'       => $thongTin['trangThai'],
                'diaChiGiaoHang'  => $diaChi['diaChiChiTiet'] ?? 'Chưa có địa chỉ giao hàng',
                'tenNguoiNhan'    => $diaChi['tenNguoiNhan'] ?? $thongTin['hoTen'],
                'soDienThoaiNhan' => $diaChi['soDienThoaiNhan'] ?? $thongTin['soDienThoai'],
            ],
            'chiTiet' => $chiTiet
        ]);
        exit;
    }

    // API BƯỚC 1: xác nhận đơn hợp lệ -> chuyển "Chờ xác nhận" sang "Đã xác nhận" (chưa đụng tồn kho)
    public function xacNhanDon($idDonHang = null)
    {
        header('Content-Type: application/json');

        $idDonHang = (int) $idDonHang;
        if ($idDonHang <= 0) {
            echo json_encode(['status' => false, 'message' => 'Thiếu mã đơn hàng']);
            exit;
        }

        $ok = $this->dongGoiModel->xacNhanDon($idDonHang);

        echo json_encode([
            'status' => (bool) $ok,
            'message' => $ok ? 'Đã xác nhận đơn hàng!' : 'Đơn hàng không ở trạng thái chờ xác nhận.'
        ]);
        exit;
    }

    // API BƯỚC 1b: từ chối đơn hàng không hợp lệ -> chuyển "Chờ xác nhận" sang "Đã huỷ" kèm lý do
    public function tuChoiDon($idDonHang = null)
    {
        header('Content-Type: application/json');

        $idDonHang = (int) $idDonHang;
        if ($idDonHang <= 0) {
            echo json_encode(['status' => false, 'message' => 'Thiếu mã đơn hàng']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $lyDo = trim($input['lyDo'] ?? '');
        if ($lyDo === '') {
            echo json_encode(['status' => false, 'message' => 'Vui lòng nhập lý do từ chối']);
            exit;
        }

        $ok = $this->dongGoiModel->tuChoiDon($idDonHang, $lyDo);

        echo json_encode([
            'status' => (bool) $ok,
            'message' => $ok ? 'Đã từ chối đơn hàng!' : 'Đơn hàng không ở trạng thái chờ xác nhận.'
        ]);
        exit;
    }

    // API BƯỚC 2: xác nhận đã đóng gói xong -> chuyển đơn sang "Đang giao" + trừ tồn kho theo FEFO
    public function xacNhanDongGoi($idDonHang = null)
    {
        header('Content-Type: application/json');

        $idDonHang = (int) $idDonHang;
        if ($idDonHang <= 0) {
            echo json_encode(['status' => false, 'message' => 'Thiếu mã đơn hàng']);
            exit;
        }

        $ok = $this->dongGoiModel->xacNhanDongGoiXong($idDonHang);

        echo json_encode([
            'status' => (bool) $ok,
            'message' => $ok ? 'Đã xác nhận đóng gói và bàn giao vận chuyển!' : 'Đơn hàng không ở trạng thái chờ đóng gói.'
        ]);
        exit;
    }
}
