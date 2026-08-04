<?php

/**
 * Class: thanhToanDatHangController
 * Controller phụ trách chức năng "Thanh toán đặt hàng" dành cho Khách hàng.
 * Chức năng tổng quan:
 *  - Lấy danh sách sản phẩm được chọn mua từ giỏ hàng (hoặc toàn bộ nếu không
 *    chọn cụ thể), tính tổng tiền và hiển thị trang thanh toán kèm danh sách
 *    địa chỉ giao hàng của khách hàng.
 *  - Xử lý xác nhận đặt hàng: tạo đơn hàng, thêm chi tiết đơn hàng, xóa các
 *    sản phẩm đã mua khỏi giỏ hàng, rồi chuyển hướng sang trang quản lý đơn hàng.
 */
class thanhToanDatHangController extends Controller
{
    // Model xử lý dữ liệu giỏ hàng (lấy/tạo giỏ hàng, chi tiết giỏ hàng, xóa item)
    private $gioHangModel;
    // Model xử lý dữ liệu đơn hàng (tạo đơn, thêm chi tiết đơn)
    private $donHangModel;
    // Model xử lý thông tin cá nhân khách hàng (danh sách địa chỉ giao hàng)
    private $thongTinModel;

    /**
     * Khởi tạo controller: nạp các model cần thiết cho luồng thanh toán
     * (giỏ hàng, đơn hàng, thông tin cá nhân).
     */
    public function __construct()
    {
        $this->gioHangModel = $this->model("gioHangModel");
        $this->donHangModel = $this->model("DonHangModel");
        $this->thongTinModel = $this->model("thongTinCaNhanModel");
    }

    /**
     * Kiểm tra khách hàng đã đăng nhập hay chưa, hỗ trợ cả 2 kiểu lưu session
     * (session_id trực tiếp hoặc session dạng mảng "user").
     * @return ID khách hàng nếu đã đăng nhập, hoặc null nếu chưa đăng nhập
     */
    private function checkLogin()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) return $_SESSION['user_id']; // Kiểu lưu session dạng user_id trực tiếp
        if (isset($_SESSION['user']) && isset($_SESSION['user']['idNguoiDung'])) return $_SESSION['user']['idNguoiDung']; // Kiểu lưu session dạng mảng "user"
        return null; // Chưa đăng nhập
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng của khách hàng sẽ được đưa vào
     * thanh toán: bỏ qua các sản phẩm đang bị khóa (trạng thái "KHOA"), và
     * nếu có danh sách id được chọn cụ thể thì chỉ lấy những sản phẩm đó.
     * Đồng thời xử lý đường dẫn ảnh cho từng sản phẩm.
     * @param idKhachHang ID khách hàng đang thanh toán
     * @param selectedIds Danh sách id sản phẩm đã được khách hàng chọn (null = lấy tất cả sản phẩm hợp lệ)
     * @return Mảng [idGioHang, danhSachMua] gồm id giỏ hàng và danh sách sản phẩm sẽ mua
     */
    private function layDanhSachMua($idKhachHang, $selectedIds = null)
    {
        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang); // Lấy giỏ hàng hiện có, hoặc tạo mới nếu chưa có
        if (!$idGioHang) return [null, []];

        $danhSachChiTiet = $this->gioHangModel->layDanhSachChiTietGioHang($idGioHang); // Toàn bộ chi tiết giỏ hàng của khách hàng

        $danhSachMua = array_filter($danhSachChiTiet, function ($item) use ($selectedIds) {
            if ($item['trangThaiThaoTac'] === 'KHOA') return false; // Bỏ qua sản phẩm đang bị khóa (không đủ điều kiện mua)

            if ($selectedIds !== null && !in_array((string)$item['id'], $selectedIds, true)) {
                return false; // Bỏ qua nếu có danh sách chọn cụ thể và item này không nằm trong đó
            }

            return true;
        });

        $danhSachMua = array_values($danhSachMua); // Đánh lại chỉ số mảng liên tục sau khi lọc

        foreach ($danhSachMua as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh(isset($item['hinhAnh']) ? $item['hinhAnh'] : ''); // Đường dẫn ảnh đã xử lý để hiển thị
        }
        unset($item); // Hủy tham chiếu sau vòng lặp foreach theo tham chiếu

        return [$idGioHang, $danhSachMua];
    }

    /**
     * Lấy danh sách id sản phẩm đã được khách hàng chọn để thanh toán,
     * đọc từ POST (khi submit form) hoặc GET (khi truyền qua URL).
     * @return Mảng id đã chọn (dạng chuỗi), hoặc null nếu không có lựa chọn cụ thể
     */
    private function layDanhSachIdDaChon()
    {
        $raw = isset($_POST['selectedIds']) ? $_POST['selectedIds'] : (isset($_GET['ids']) ? $_GET['ids'] : null); // Chuỗi id, cách nhau bởi dấu phẩy
        if ($raw === null || $raw === '') return null;

        return array_filter(array_map('trim', explode(',', $raw)), function ($v) {
            return $v !== ''; // Loại bỏ các phần tử rỗng sau khi tách chuỗi
        });
    }

    /**
     * Hiển thị trang thanh toán đặt hàng: kiểm tra đăng nhập, lấy danh sách
     * sản phẩm sẽ mua, tính tổng tiền, lấy danh sách địa chỉ giao hàng của
     * khách hàng rồi render dữ liệu ra view thanh toán.
     * Nếu chưa đăng nhập hoặc giỏ hàng không có sản phẩm hợp lệ, chuyển hướng
     * về trang tương ứng.
     */
    public function index()
    {
        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            header("Location: " . URLROOT . "/khachHang/xacThuc/dangNhap"); // Chưa đăng nhập -> chuyển về trang đăng nhập
            exit();
        }

        $selectedIds = $this->layDanhSachIdDaChon(); // Danh sách id sản phẩm khách hàng đã chọn để thanh toán
        list($idGioHang, $danhSachMua) = $this->layDanhSachMua($idKhachHang, $selectedIds);

        if (empty($danhSachMua)) {
            header("Location: " . URLROOT . "/khachHang/gioHang"); // Không có sản phẩm hợp lệ -> quay về giỏ hàng
            exit();
        }

        $tongTien = 0; // Tổng tiền của toàn bộ sản phẩm sẽ thanh toán
        foreach ($danhSachMua as $thuoc) {
            $tongTien += $thuoc["soLuong"] * $thuoc["donGia"];
        }

        $data = [
            'title' => "PharmaCare – Thanh toán đặt hàng",                          // Tiêu đề trang (thẻ <title>)
            'page_title' => "Thanh toán",                                           // Tiêu đề hiển thị trên header trang
            'active_tab' => "thanhtoan",                                           // Tab đang active trên thanh điều hướng
            'page_css' => "thanhToanDatHang",                                       // Tên file CSS riêng cho trang này
            'cartItems' => $danhSachMua,                                           // Danh sách sản phẩm sẽ thanh toán
            'tongTien' => $tongTien,                                               // Tổng tiền đã tính ở trên
            'diaChiList' => $this->thongTinModel->getDanhSachDiaChi($idKhachHang),  // Danh sách địa chỉ giao hàng đã lưu của khách hàng
            'selectedIdsStr' => $selectedIds ? implode(',', $selectedIds) : ''       // Chuỗi id đã chọn, dùng để giữ lại lựa chọn khi submit form
        ];

        ob_start();
        extract($data);
        require_once APPROOT . '/views/khachHang/thanhToanDatHang.php';
        $content = ob_get_clean(); // Nội dung view thanh toán, dùng để gán vào layout chung

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }

    /**
     * Xử lý xác nhận đặt hàng khi khách hàng submit form thanh toán:
     * kiểm tra đăng nhập và phương thức request, lấy lại danh sách sản phẩm
     * sẽ mua, đọc thông tin người nhận/giao hàng từ form, tạo đơn hàng và
     * chi tiết đơn hàng tương ứng, sau đó xóa các sản phẩm đã mua khỏi giỏ
     * hàng và chuyển hướng sang trang quản lý đơn hàng.
     */
    public function xacNhan()
    {
        $idKhachHang = $this->checkLogin();
        if (!$idKhachHang) {
            header("Location: " . URLROOT . "/khachHang/xacThuc/dangNhap"); // Chưa đăng nhập -> chuyển về trang đăng nhập
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . URLROOT . "/khachHang/thanhToanDatHang"); // Truy cập không đúng phương thức -> quay lại trang thanh toán
            exit();
        }

        $selectedIds = $this->layDanhSachIdDaChon(); // Danh sách id sản phẩm khách hàng đã chọn để thanh toán
        list($idGioHang, $danhSachMua) = $this->layDanhSachMua($idKhachHang, $selectedIds);

        if (empty($danhSachMua)) {
            header("Location: " . URLROOT . "/khachHang/gioHang"); // Không có sản phẩm hợp lệ -> quay về giỏ hàng
            exit();
        }

        $hoTenNguoiNhan  = trim(isset($_POST['hoTenNguoiNhan']) ? $_POST['hoTenNguoiNhan'] : '');       // Họ tên người nhận hàng
        $soDienThoaiNhan = trim(isset($_POST['soDienThoaiNhan']) ? $_POST['soDienThoaiNhan'] : '');     // Số điện thoại người nhận hàng
        $diaChiGiaoHang  = trim(isset($_POST['diaChiGiaoHang']) ? $_POST['diaChiGiaoHang'] : '');       // Địa chỉ giao hàng
        $phuongThucTT    = trim(isset($_POST['phuongThucThanhToan']) ? $_POST['phuongThucThanhToan'] : 'COD'); // Phương thức thanh toán (mặc định COD)
        $ghiChu          = trim(isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '');                       // Ghi chú của khách hàng cho đơn hàng

        $tongTien = 0; // Tổng tiền của toàn bộ sản phẩm trong đơn hàng
        foreach ($danhSachMua as $thuoc) {
            $tongTien += $thuoc["soLuong"] * $thuoc["donGia"];
        }

        // Tạo đơn hàng
        $this->donHangModel->taoDonHang($idKhachHang, $tongTien);
        $idDonHang = $this->donHangModel->getLastId(); // ID đơn hàng vừa được tạo

        foreach ($danhSachMua as $thuoc) {
            $this->donHangModel->themChiTiet(
                $idDonHang,
                $thuoc["idThuoc"],
                $thuoc["soLuong"],
                $thuoc["donGia"]
            );
        }

        // Xóa các sản phẩm đã chọn mua khỏi giỏ hàng
        foreach ($danhSachMua as $thuoc) {
            $this->gioHangModel->xoaItemKhoiGio($thuoc['id'], $idGioHang);
        }

        header("Location: " . URLROOT . "/khachHang/quanLyDonHang");
        exit();
    }
}

