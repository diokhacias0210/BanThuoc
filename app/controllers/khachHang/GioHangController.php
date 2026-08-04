<?php
/**
 * Controller quản lý giỏ hàng (khu vực Khách hàng).
 * Xử lý các luồng: hiển thị giỏ hàng, thêm sản phẩm vào giỏ, cập nhật số lượng,
 * và xóa sản phẩm khỏi giỏ — đều có kiểm tra đăng nhập và giới hạn mua theo tồn kho/gioiHanMua.
 */
class gioHangController extends Controller
{
    private $gioHangModel; // Model thao tác với dữ liệu giỏ hàng (bảng GioHang + ChiTietGioHang)

    /**
     * Khởi tạo controller, load sẵn model dùng xuyên suốt các action bên dưới.
     */
    public function __construct()
    {
        $this->gioHangModel = $this->model("gioHangModel");
    }

    /**
     * Kiểm tra người dùng đã đăng nhập chưa, hỗ trợ 2 cách lưu session khác nhau
     * (session['user_id'] trực tiếp, hoặc lồng trong session['user']['idNguoiDung']).
     * @return int|null id khách hàng nếu đã đăng nhập, null nếu chưa
     */
    private function checkLogin()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // Đảm bảo session đã được khởi động trước khi đọc $_SESSION
        }
        if (isset($_SESSION['user_id'])) return $_SESSION['user_id'];
        if (isset($_SESSION['user']) && isset($_SESSION['user']['idNguoiDung'])) return $_SESSION['user']['idNguoiDung'];
        return null; // Chưa đăng nhập
    }

    /**
     * Trang giỏ hàng của khách hàng đang đăng nhập.
     * Nếu chưa đăng nhập thì chuyển hướng sang trang đăng nhập.
     * Với mỗi sản phẩm trong giỏ, tính sẵn maxAllowed (số lượng tối đa được phép mua)
     * dựa trên giới hạn mua của sản phẩm và số lượng tồn kho thực tế.
     */
    public function index()
    {
        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            $this->redirect('khachHang/xacThuc/dangNhap'); // Bắt buộc đăng nhập mới xem được giỏ hàng
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang); // Lấy giỏ hàng hiện có, hoặc tạo mới nếu chưa có
        $rawItems = $this->gioHangModel->layDanhSachChiTietGioHang($idGioHang);

        $items = array();
        if (!empty($rawItems)) {
            foreach ($rawItems as $item) {
                $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh(isset($item['hinhAnh']) ? $item['hinhAnh'] : ''); // Chuẩn hoá đường dẫn ảnh hiển thị
                $gioiHanMua = intval($item['gioiHanMua']); // Giới hạn mua tối đa theo cấu hình sản phẩm (-1 = không giới hạn)
                $tongTon = intval($item['tongTon']);       // Tổng số lượng tồn kho thực tế của sản phẩm
                // Số lượng tối đa cho phép mua = min(giới hạn mua, tồn kho); nếu không giới hạn thì lấy luôn tồn kho
                $item['maxAllowed'] = ($gioiHanMua > 0) ? min($gioiHanMua, $tongTon) : $tongTon;
                $items[] = $item;
            }
        }

        $data = [
            'title' => "PharmaCare – Giỏ hàng của bạn",
            'page_title' => "Giỏ hàng",
            'active_tab' => "giohang", // Đánh dấu tab đang active trên sidebar khách hàng
            'page_css' => "gioHang",   // Tên file CSS riêng cho trang này
            'cartItems' => $items      // Danh sách sản phẩm trong giỏ, đã tính sẵn maxAllowed
        ];

        ob_start(); // Bắt đầu buffer output để nhúng view con vào biến $content
        extract($data); // Tách $data thành biến riêng lẻ để view gioHang.php dùng trực tiếp
        require_once APPROOT . '/views/khachHang/gioHang.php';
        $content = ob_get_clean();

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }

    /**
     * API: Thêm 1 sản phẩm vào giỏ hàng.
     * Kiểm tra lần lượt: đã đăng nhập, dữ liệu hợp lệ, sản phẩm tồn tại, sản phẩm
     * không phải thuốc kê đơn (Rx), và không vượt quá giới hạn mua cho phép.
     */
    public function themVaoGio()
    {
        if (ob_get_length()) ob_clean(); // Xả buffer output cũ (nếu có) để tránh lẫn nội dung thừa vào JSON trả về
        header('Content-Type: application/json');

        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            // Trả về cờ requireLogin để frontend biết cần hiển thị popup đăng nhập
            echo json_encode(array(
                'status' => false,
                'requireLogin' => true,
                'message' => 'Bạn cần đăng nhập tài khoản để thực hiện thêm sản phẩm vào giỏ hàng!'
            ));
            exit;
        }

        $idThuoc = isset($_POST['idThuoc']) ? intval($_POST['idThuoc']) : 0; // id sản phẩm cần thêm
        $soLuong = isset($_POST['soLuong']) ? intval($_POST['soLuong']) : 1; // Số lượng muốn thêm, mặc định 1

        if ($idThuoc <= 0 || $soLuong <= 0) {
            echo json_encode(array('status' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ.'));
            exit;
        }

        $chiTietModel = $this->model("chiTietThuocModel");
        $thuoc = $chiTietModel->getChiTietThuocTheoID($idThuoc);

        if (!$thuoc) {
            echo json_encode(array('status' => false, 'message' => 'Sản phẩm không tồn tại trong hệ thống.'));
            exit;
        }

        // Thuốc kê đơn (Rx) không cho thêm thẳng vào giỏ, phải qua luồng gửi đơn thuốc riêng
        if ($thuoc['yeuCauKeDon'] === 'Kê đơn') {
            echo json_encode(array(
                'status' => false,
                'message' => 'Đây là thuốc kê đơn! Bạn cần gửi đơn thuốc để dược sĩ tư vấn.',
                'isRx' => true
            ));
            exit;
        }

        $gioiHanMua = intval($thuoc['gioiHanMua']); // Giới hạn mua tối đa theo cấu hình sản phẩm (-1 = không giới hạn)
        $tongTon = intval($thuoc['tongTon']);       // Tổng số lượng tồn kho thực tế
        $maxAllowed = ($gioiHanMua > 0) ? min($gioiHanMua, $tongTon) : $tongTon; // Số lượng tối đa cho phép mua

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $currentInCart = $this->gioHangModel->getSoLuongHienCoTrongGio($idGioHang, $idThuoc); // Số lượng sản phẩm này đang có sẵn trong giỏ
        $tongSoLuongMoi = $currentInCart + $soLuong; // Tổng số lượng sau khi thêm

        if ($tongSoLuongMoi > $maxAllowed) {
            echo json_encode(array(
                'status' => false,
                'message' => "Sản phẩm này giới hạn mua tối đa {$maxAllowed} đơn vị! (Đã có {$currentInCart} trong giỏ)."
            ));
            exit;
        }

        // Thêm sản phẩm vào giỏ với trạng thái 'CHO_PHEP' (không kèm đơn thuốc vì đây là thuốc không kê đơn)
        $ok = $this->gioHangModel->themItemVaoGio($idGioHang, $idThuoc, $soLuong, $thuoc['giaBan'], 'CHO_PHEP', null);
        $cartCount = $this->gioHangModel->demSoChungLoaiThuocTrongGio($idGioHang); // Số loại thuốc hiện có trong giỏ (hiển thị badge icon giỏ hàng)

        echo json_encode(array(
            'status' => (bool)$ok,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!',
            'cartCount' => $cartCount
        ));
        exit;
    }

    /**
     * API: Cập nhật số lượng của 1 sản phẩm đang có trong giỏ hàng.
     * Kiểm tra sản phẩm thuộc đúng giỏ của khách hàng và không vượt quá giới hạn mua cho phép.
     */
    public function capNhatSoLuong()
    {
        if (ob_get_length()) ob_clean(); // Xả buffer output cũ (nếu có) để tránh lẫn nội dung thừa vào JSON trả về
        header('Content-Type: application/json');
        $idKhachHang = $this->checkLogin();
        $idChiTiet = isset($_POST['id']) ? intval($_POST['id']) : 0; // id dòng chi tiết giỏ hàng cần cập nhật
        $soLuong = isset($_POST['soLuong']) ? intval($_POST['soLuong']) : 1; // Số lượng mới muốn đặt

        if (!$idKhachHang || $idChiTiet <= 0 || $soLuong <= 0) {
            echo json_encode(array('status' => false, 'message' => 'Yêu cầu không hợp lệ.'));
            exit;
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $itemInfo = $this->gioHangModel->getChiTietItemTheoID($idChiTiet, $idGioHang); // Đảm bảo dòng chi tiết này thuộc đúng giỏ của khách hàng

        if (!$itemInfo) {
            echo json_encode(array('status' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ.'));
            exit;
        }

        $gioiHanMua = intval($itemInfo['gioiHanMua']); // Giới hạn mua tối đa theo cấu hình sản phẩm (-1 = không giới hạn)
        $tongTon = intval($itemInfo['tongTon']);       // Tổng số lượng tồn kho thực tế
        $maxAllowed = ($gioiHanMua > 0) ? min($gioiHanMua, $tongTon) : $tongTon; // Số lượng tối đa cho phép mua

        if ($soLuong > $maxAllowed) {
            echo json_encode(array(
                'status' => false,
                'message' => "Sản phẩm này giới hạn mua tối đa {$maxAllowed} đơn vị!"
            ));
            exit;
        }

        $ok = $this->gioHangModel->capNhatSoLuongItem($idChiTiet, $idGioHang, $soLuong);
        echo json_encode(array('status' => (bool)$ok));
        exit;
    }

    /**
     * API: Xóa 1 sản phẩm khỏi giỏ hàng.
     * @return void Trả về JSON gồm trạng thái xoá và số chủng loại thuốc còn lại trong giỏ
     */
    public function xoaItem()
    {
        if (ob_get_length()) ob_clean(); // Xả buffer output cũ (nếu có) để tránh lẫn nội dung thừa vào JSON trả về
        header('Content-Type: application/json');
        $idKhachHang = $this->checkLogin();
        $idChiTiet = isset($_POST['id']) ? intval($_POST['id']) : 0; // id dòng chi tiết giỏ hàng cần xoá

        if (!$idKhachHang || $idChiTiet <= 0) {
            echo json_encode(array('status' => false));
            exit;
        }

        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        $ok = $this->gioHangModel->xoaItemKhoiGio($idChiTiet, $idGioHang);
        $cartCount = $this->gioHangModel->demSoChungLoaiThuocTrongGio($idGioHang); // Cập nhật lại số chủng loại thuốc còn trong giỏ sau khi xoá

        echo json_encode(array('status' => (bool)$ok, 'cartCount' => $cartCount));
        exit;
    }
}

