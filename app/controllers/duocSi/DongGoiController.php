<?php
/**
 * Controller xử lý nghiệp vụ đóng gói đơn hàng cho dược sĩ:
 * - Hiển thị danh sách đơn hàng cần đóng gói
 * - Xác nhận / từ chối đơn hàng chờ xử lý
 * - Xác nhận hoàn tất đóng gói và trừ tồn kho theo nguyên tắc FEFO (hết hạn trước xuất trước)
 */
class DongGoiController extends Controller
{
    private $dongGoiModel; // Model thao tác dữ liệu đơn hàng/đóng gói (bảng đơn hàng, lô thuốc, tồn kho...)

    /**
     * Khởi tạo controller, nạp sẵn DongGoiModel để dùng cho toàn bộ các hàm bên dưới.
     */
    public function __construct()
    {
        $this->dongGoiModel = $this->model('DongGoiModel');
    }

    /**
     * Hiển thị trang danh sách đơn hàng cần đóng gói cho dược sĩ.
     * Lấy dữ liệu từ model, chuẩn hoá kiểu dữ liệu rồi render ra view trong layout dược sĩ.
     */
    public function index()
    {
        // Lấy danh sách đơn hàng đang chờ đóng gói từ database
        $donHangKhoList = $this->dongGoiModel->layDanhSachDonCanDongGoi();

        // Chuẩn hoá dữ liệu để JS phía View dùng được (giữ nguyên tên trường tiếng Việt gốc)
        foreach ($donHangKhoList as &$dh) {
            $dh['idDonHang']   = (int) $dh['idDonHang'];   // Ép ID đơn hàng về kiểu số nguyên
            $dh['tongTien']    = (float) $dh['tongTien'];   // Ép tổng tiền về kiểu số thực
            $dh['soLoaiThuoc'] = (int) $dh['soLoaiThuoc'];  // Ép số loại thuốc về kiểu số nguyên
            $dh['tongSoThuoc'] = (int) $dh['tongSoThuoc'];  // Ép tổng số lượng thuốc về kiểu số nguyên
        }

        // Chuẩn bị dữ liệu truyền sang view (tiêu đề trang, tab đang active, file CSS riêng, danh sách đơn)
        $data['title'] = "PharmaCare – Xử lý & đóng gói";
        $data['page_title'] = "Xử lý & đóng gói";
        $data['active_tab'] = "dongoi";
        $data['page_css'] = "dongGoiDonHang";
        $data['donHangKhoList'] = $donHangKhoList;

        // Render nội dung view con vào buffer, sau đó nhúng vào layout chung của dược sĩ
        ob_start();
        extract($data);
        require_once APPROOT . '/views/duocSi/dongGoi.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/duocSiLayout', $data);
    }

    /**
     * API: lấy chi tiết một đơn hàng (thông tin giao hàng + danh sách thuốc kèm gợi ý lô FEFO)
     * để đổ dữ liệu vào modal đóng gói phía client.
     * @param int|null $idDonHang - ID đơn hàng cần lấy chi tiết (truyền qua route param).
     * @return void In ra JSON và kết thúc request.
     */
    public function layChiTietDon($idDonHang = null)
    {
        header('Content-Type: application/json');

        $idDonHang = (int) $idDonHang; // Ép về số nguyên để tránh lỗi truy vấn / injection
        if ($idDonHang <= 0) {
            // Không có ID hợp lệ -> trả lỗi ngay, không truy vấn database
            echo json_encode(['status' => false, 'message' => 'Thiếu mã đơn hàng']);
            exit;
        }

        // Lấy thông tin tổng quan của đơn hàng (khách hàng, trạng thái...)
        $thongTin = $this->dongGoiModel->layThongTinDonHang($idDonHang);
        if (!$thongTin) {
            // Không tìm thấy đơn hàng tương ứng
            echo json_encode(['status' => false, 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }

        // Lấy địa chỉ giao hàng mặc định của khách hàng và danh sách thuốc cần đóng gói (kèm gợi ý lô FEFO)
        $diaChi = $this->dongGoiModel->layDiaChiMacDinh($thongTin['idKhachHang']);
        $chiTiet = $this->dongGoiModel->layChiTietDeDongGoi($idDonHang);

        echo json_encode([
            'status' => true,
            'donHang' => [
                'idDonHang'       => (int) $thongTin['idDonHang'],
                'hoTen'           => $thongTin['hoTen'],
                'trangThai'       => $thongTin['trangThai'],
                // Nếu khách chưa có địa chỉ mặc định thì hiển thị thông báo thay vì để trống
                'diaChiGiaoHang'  => isset($diaChi['diaChiChiTiet']) ? $diaChi['diaChiChiTiet'] : 'Chưa có địa chỉ giao hàng',
                // Nếu địa chỉ không có tên người nhận riêng thì dùng luôn họ tên khách hàng
                'tenNguoiNhan'    => isset($diaChi['tenNguoiNhan']) ? $diaChi['tenNguoiNhan'] : $thongTin['hoTen'],
                // Nếu địa chỉ không có SĐT người nhận riêng thì dùng luôn SĐT khách hàng
                'soDienThoaiNhan' => isset($diaChi['soDienThoaiNhan']) ? $diaChi['soDienThoaiNhan'] : $thongTin['soDienThoai'],
            ],
            'chiTiet' => $chiTiet
        ]);
        exit;
    }

    /**
     * API BƯỚC 1: Xác nhận đơn hàng hợp lệ, chuyển trạng thái từ "Chờ xác nhận" sang "Đã xác nhận".
     * Bước này chưa tác động đến tồn kho.
     * @param int|null $idDonHang - ID đơn hàng cần xác nhận.
     * @return void In ra JSON và kết thúc request.
     */
    public function xacNhanDon($idDonHang = null)
    {
        header('Content-Type: application/json');

        $idDonHang = (int) $idDonHang;
        if ($idDonHang <= 0) {
            echo json_encode(['status' => false, 'message' => 'Thiếu mã đơn hàng']);
            exit;
        }

        // Gọi model thực hiện chuyển trạng thái, trả về true/false tùy đơn có đúng trạng thái chờ xác nhận hay không
        $ok = $this->dongGoiModel->xacNhanDon($idDonHang);

        echo json_encode([
            'status' => (bool) $ok,
            'message' => $ok ? 'Đã xác nhận đơn hàng!' : 'Đơn hàng không ở trạng thái chờ xác nhận.'
        ]);
        exit;
    }

    /**
     * API BƯỚC 1b: Từ chối đơn hàng không hợp lệ, chuyển trạng thái từ "Chờ xác nhận" sang "Đã huỷ" kèm lý do.
     * @param int|null $idDonHang - ID đơn hàng cần từ chối.
     * @return void In ra JSON và kết thúc request.
     */
    public function tuChoiDon($idDonHang = null)
    {
        header('Content-Type: application/json');

        $idDonHang = (int) $idDonHang;
        if ($idDonHang <= 0) {
            echo json_encode(['status' => false, 'message' => 'Thiếu mã đơn hàng']);
            exit;
        }

        // Đọc dữ liệu JSON gửi lên từ body request (lý do từ chối)
        $input = json_decode(file_get_contents('php://input'), true);
        $lyDo = trim(isset($input['lyDo']) ? $input['lyDo'] : ''); // Lấy lý do, loại bỏ khoảng trắng thừa
        if ($lyDo === '') {
            // Bắt buộc phải có lý do từ chối, không cho gửi rỗng
            echo json_encode(['status' => false, 'message' => 'Vui lòng nhập lý do từ chối']);
            exit;
        }

        // Gọi model thực hiện từ chối đơn kèm lý do
        $ok = $this->dongGoiModel->tuChoiDon($idDonHang, $lyDo);

        echo json_encode([
            'status' => (bool) $ok,
            'message' => $ok ? 'Đã từ chối đơn hàng!' : 'Đơn hàng không ở trạng thái chờ xác nhận.'
        ]);
        exit;
    }

    /**
     * API BƯỚC 2: Xác nhận đã đóng gói xong đơn hàng, chuyển trạng thái sang "Đang giao"
     * đồng thời trừ tồn kho theo nguyên tắc FEFO (lô hết hạn trước được xuất trước).
     * @param int|null $idDonHang - ID đơn hàng đã đóng gói xong.
     * @return void In ra JSON và kết thúc request.
     */
    public function xacNhanDongGoi($idDonHang = null)
    {
        header('Content-Type: application/json');

        $idDonHang = (int) $idDonHang;
        if ($idDonHang <= 0) {
            echo json_encode(['status' => false, 'message' => 'Thiếu mã đơn hàng']);
            exit;
        }

        // Gọi model thực hiện chuyển trạng thái + trừ tồn kho theo FEFO
        $ok = $this->dongGoiModel->xacNhanDongGoiXong($idDonHang);

        echo json_encode([
            'status' => (bool) $ok,
            'message' => $ok ? 'Đã xác nhận đóng gói và bàn giao vận chuyển!' : 'Đơn hàng không ở trạng thái chờ đóng gói.'
        ]);
        exit;
    }
}
