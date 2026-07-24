<?php

class ThanhToanDatHangController extends Controller
{
    private $gioHangModel;
    private $donHangModel;
    private $thongTinModel;

    public function __construct()
    {
        $this->gioHangModel = $this->model("gioHangModel");
        $this->donHangModel = $this->model("DonHangModel");
        // Tái sử dụng model đã có sẵn (bên trang Thông tin cá nhân) để lấy danh sách địa chỉ đã lưu
        $this->thongTinModel = $this->model("thongTinCaNhanModel");
    }

    // Lấy danh sách sản phẩm ĐƯỢC PHÉP mua trong giỏ hàng hiện tại (bỏ thuốc kê đơn đang khóa)
    // $selectedIds: mảng ID chiTietGioHang mà người dùng đã TÍCH CHỌN ở trang giỏ hàng.
    // Nếu null -> không lọc theo lựa chọn (giữ hành vi cũ, không khuyến khích dùng trực tiếp nữa).
    private function layDanhSachMua($idKhachHang, $selectedIds = null)
    {
        $idGioHang = $this->gioHangModel->layHoacTaoGioHang($idKhachHang);
        if (!$idGioHang) return [null, []];

        $danhSachChiTiet = $this->gioHangModel->layDanhSachChiTietGioHang($idGioHang);

        $danhSachMua = array_filter($danhSachChiTiet, function ($item) use ($selectedIds) {
            if ($item['trangThaiThaoTac'] === 'KHOA') return false;

            // Quan trọng: CHỈ lấy đúng những sản phẩm người dùng đã tích chọn ở giỏ hàng
            // (trước đây thiếu điều kiện này nên luôn lấy TOÀN BỘ giỏ hàng)
            if ($selectedIds !== null && !in_array((string)$item['id'], $selectedIds, true)) {
                return false;
            }

            return true;
        });

        $danhSachMua = array_values($danhSachMua);

        // Xử lý đường dẫn ảnh đúng chuẩn - dùng lại đúng hàm xuLyDuongDanAnh() sẵn có
        // (bên GioHangController cũng dùng hàm này) để tránh ảnh bị sai/lệch.
        foreach ($danhSachMua as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh(isset($item['hinhAnh']) ? $item['hinhAnh'] : '');
        }
        unset($item);

        return [$idGioHang, $danhSachMua];
    }

    // Đọc danh sách ID sản phẩm đã chọn từ query string (?ids=1,2,3) hoặc từ POST (hidden field)
    private function layDanhSachIdDaChon()
    {
        $raw = isset($_POST['selectedIds']) ? $_POST['selectedIds'] : (isset($_GET['ids']) ? $_GET['ids'] : null);
        if ($raw === null || $raw === '') return null;

        return array_filter(array_map('trim', explode(',', $raw)), function ($v) {
            return $v !== '';
        });
    }

    // GET /khachHang/thanhToanDatHang -> Hiển thị trang thanh toán
    public function index()
    {
        if (!isset($_SESSION["user"])) {
            header("Location: " . URLROOT . "/khachHang/xacThuc/dangNhap");
            exit();
        }

        $idKhachHang = $_SESSION["user"]["idNguoiDung"];
        $selectedIds = $this->layDanhSachIdDaChon();

        list($idGioHang, $danhSachMua) = $this->layDanhSachMua($idKhachHang, $selectedIds);

        if (empty($danhSachMua)) {
            header("Location: " . URLROOT . "/khachHang/gioHang");
            exit();
        }

        $tongTien = 0;
        foreach ($danhSachMua as $thuoc) {
            $tongTien += $thuoc["soLuong"] * $thuoc["donGia"];
        }

        $data['title'] = "PharmaCare – Thanh toán đặt hàng";
        $data['page_title'] = "Thanh toán";
        $data['active_tab'] = "thanhtoan";
        $data['page_css'] = "thanhToanDatHang";
        $data['cartItems'] = $danhSachMua;
        $data['tongTien'] = $tongTien;
        // Danh sách địa chỉ đã lưu (bảng DiaChiGiaoHang) để chọn nhanh, khỏi phải nhập tay lại
        $data['diaChiList'] = $this->thongTinModel->getDanhSachDiaChi($idKhachHang);
        // Truyền lại chuỗi ID đã chọn để View gắn vào hidden field, giữ lựa chọn
        // xuyên suốt tới bước xacNhan() (vì đó là 1 request POST riêng biệt).
        $data['selectedIdsStr'] = $selectedIds ? implode(',', $selectedIds) : '';

        ob_start();
        extract($data);
        require_once APPROOT . '/views/khachHang/thanhToanDatHang.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/khachHangLayout', $data);
    }

    // POST /khachHang/thanhToanDatHang/xacNhan -> Xử lý tạo đơn hàng thật
    // (Chạy khi bấm nút "Xác nhận đặt hàng" trên trang thanh toán)
    public function xacNhan()
    {
        if (!isset($_SESSION["user"])) {
            header("Location: " . URLROOT . "/khachHang/xacThuc/dangNhap");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . URLROOT . "/khachHang/thanhToanDatHang");
            exit();
        }

        $idKhachHang = $_SESSION["user"]["idNguoiDung"];
        $selectedIds = $this->layDanhSachIdDaChon();

        list($idGioHang, $danhSachMua) = $this->layDanhSachMua($idKhachHang, $selectedIds);

        if (empty($danhSachMua)) {
            header("Location: " . URLROOT . "/khachHang/gioHang");
            exit();
        }

        // Dữ liệu form gửi từ trang thanh toán
        $hoTenNguoiNhan  = trim(isset($_POST['hoTenNguoiNhan']) ? $_POST['hoTenNguoiNhan'] : '');
        $soDienThoaiNhan = trim(isset($_POST['soDienThoaiNhan']) ? $_POST['soDienThoaiNhan'] : '');
        $diaChiGiaoHang  = trim(isset($_POST['diaChiGiaoHang']) ? $_POST['diaChiGiaoHang'] : '');
        $phuongThucTT    = trim(isset($_POST['phuongThucThanhToan']) ? $_POST['phuongThucThanhToan'] : 'COD');
        $ghiChu          = trim(isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '');

        $tongTien = 0;
        foreach ($danhSachMua as $thuoc) {
            $tongTien += $thuoc["soLuong"] * $thuoc["donGia"];
        }

        // GHI VÀO BẢNG DonHang (dùng DonHangModel vì đây là tên bảng thật trong CSDL)
        // LƯU Ý: taoDonHang() hiện chỉ nhận (idKhachHang, tongTien) theo code gốc.
        // Nếu bảng DonHang có thêm cột địa chỉ/SĐT người nhận/phương thức TT/ghi chú,
        // mở rộng hàm taoDonHang() trong DonHangModel để nhận thêm các tham số bên dưới.
        $this->donHangModel->taoDonHang(
            $idKhachHang,
            $tongTien
            // , $hoTenNguoiNhan, $soDienThoaiNhan, $diaChiGiaoHang, $phuongThucTT, $ghiChu
        );

        $idDonHang = $this->donHangModel->getLastId();

        foreach ($danhSachMua as $thuoc) {
            $this->donHangModel->themChiTiet(
                $idDonHang,
                $thuoc["idThuoc"],
                $thuoc["soLuong"],
                $thuoc["donGia"]
            );
        }

        // Xóa từng item đã mua khỏi giỏ hàng (chỉ những item đã chọn, không đụng phần còn lại trong giỏ)
        foreach ($danhSachMua as $thuoc) {
            $this->gioHangModel->xoaItemKhoiGio($thuoc['id'], $idGioHang);
        }

        header("Location: " . URLROOT . "/khachHang/quanLyDonHang");
        exit();
    }
}
