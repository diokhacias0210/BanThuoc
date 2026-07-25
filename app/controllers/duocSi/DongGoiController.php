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
                'diaChiGiaoHang'  => $diaChi['diaChiChiTiet'] ?? 'Chưa có địa chỉ giao hàng',
                'tenNguoiNhan'    => $diaChi['tenNguoiNhan'] ?? $thongTin['hoTen'],
                'soDienThoaiNhan' => $diaChi['soDienThoaiNhan'] ?? $thongTin['soDienThoai'],
            ],
            'chiTiet' => $chiTiet
        ]);
        exit;
    }

    // API: xác nhận đã đóng gói xong -> chuyển đơn sang "Đang giao" + trừ tồn kho theo FEFO
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
