<?php
/**
 * Controller hiển thị danh sách sản phẩm thuốc cho khách hàng:
 * - Trang danh sách thuốc kèm danh mục
 * - Điều hướng sang trang chi tiết thuốc
 * - Tìm kiếm thuốc dạng AJAX (gợi ý khi gõ)
 */
class thuocController extends Controller
{
    private $danhSachThuocModel; // Model thao tác dữ liệu danh sách thuốc (danh mục, danh sách đầy đủ, tìm kiếm)

    /**
     * Khởi tạo controller, nạp sẵn danhSachThuocModel để dùng cho toàn bộ các hàm bên dưới.
     */
    public function __construct()
    {
        $this->danhSachThuocModel = $this->model('danhSachThuocModel');
    }

    /**
     * Hiển thị trang danh sách sản phẩm thuốc, kèm danh sách danh mục để lọc.
     * Mỗi thuốc được xử lý sẵn đường dẫn hình ảnh (hinhAnhUrl) trước khi truyền ra view.
     */
    public function index()
    {
        $danhMucList = $this->danhSachThuocModel->getAllDanhMuc(); // Danh sách danh mục thuốc (dùng để lọc)
        $thuocList = $this->danhSachThuocModel->getDanhSachThuocFull(); // Danh sách đầy đủ các thuốc

        // Chuẩn hoá đường dẫn hình ảnh cho từng thuốc trước khi hiển thị
        foreach ($thuocList as &$item) {
            $item['hinhAnhUrl'] = $this->xuLyDuongDanAnh($item['hinhAnh']);
        }

        $data = [
            'title' => 'Danh sách sản phẩm thuốc – PharmaCare',
            'page_title' => 'Danh sách hàng hóa',
            'active_tab' => 'thuoc',
            'page_css' => 'danhSachThuoc',
            'danhMucList' => $danhMucList,
            'thuocList' => $thuocList
        ];

        // Render nội dung view con vào buffer, sau đó nhúng vào layout chung của khách hàng
        ob_start();
        $this->view('khachHang/danhSachThuoc', $data);
        $content = ob_get_clean();

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }

    // BỔ SUNG METHOD CHIT IET NÀY ĐỂ XỬ LÝ URL /khachHang/thuoc/chiTiet/{id}
    /**
     * Xử lý URL /khachHang/thuoc/chiTiet/{id}: kiểm tra thuốc tồn tại rồi điều hướng
     * sang route chi tiết thực sự (khachHang/chiTietThuoc/chiTiet/{id}).
     * @param int $idThuoc - ID thuốc cần xem chi tiết.
     */
    public function chiTiet($idThuoc = 0)
    {
        $idThuoc = intval($idThuoc); // Ép về số nguyên để tránh lỗi truy vấn
        if ($idThuoc <= 0) {
            // ID không hợp lệ -> quay về trang danh sách
            $this->redirect('khachHang/thuoc');
        }

        $chiTietModel = $this->model("chiTietThuocModel");
        $thuoc = $chiTietModel->getChiTietThuocTheoID($idThuoc);

        if (!$thuoc) {
            // Không tìm thấy thuốc tương ứng -> quay về trang danh sách
            $this->redirect('khachHang/thuoc');
        }



        // Thuốc hợp lệ -> điều hướng sang route hiển thị chi tiết thực sự
        $this->redirect('khachHang/chiTietThuoc/chiTiet/' . $idThuoc);
    }

    /**
     * API tìm kiếm thuốc dạng AJAX, dùng cho ô tìm kiếm gợi ý (autocomplete).
     * @return void In ra JSON danh sách thuốc khớp từ khóa và kết thúc request.
     */
    public function timKiemAjax()
    {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? trim($_GET['q']) : ''; // Từ khóa tìm kiếm

        if (empty($q)) {
            // Không có từ khóa -> trả về mảng rỗng ngay, không truy vấn database
            echo json_encode([]);
            exit;
        }

        $results = $this->danhSachThuocModel->timKiemThuocAjax($q);
        // Chuẩn hoá đường dẫn hình ảnh và định dạng giá bán cho từng kết quả trước khi trả về
        foreach ($results as &$item) {
            $item['hinhAnh'] = $this->xuLyDuongDanAnh($item['hinhAnh']);
            $item['giaBanFormatted'] = number_format($item['giaBan'], 0, ',', '.') . 'đ';
        }

        echo json_encode($results);
        exit;
    }
}
