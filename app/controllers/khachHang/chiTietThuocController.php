<?php
/**
 * Class: chiTietThuocController
 * Controller phụ trách hiển thị trang "Chi tiết sản phẩm" (thuốc) cho Khách hàng.
 * Chức năng tổng quan:
 *  - Lấy thông tin chi tiết của một loại thuốc theo id, kèm danh sách ảnh phụ
 *    và thông tin lô mới nhất (mã lô, ngày sản xuất, hạn sử dụng).
 *  - Tính toán hạn mức mua tối đa hợp lý dựa trên giới hạn mua và tồn kho thực tế.
 *  - Render dữ liệu ra view chi tiết thuốc trong layout dành cho Khách hàng.
 */
class chiTietThuocController extends Controller
{
    // Model xử lý truy vấn dữ liệu chi tiết thuốc (thông tin thuốc, ảnh, lô) từ database
    private $chiTietModel;

    /**
     * Khởi tạo controller: nạp chiTietThuocModel để dùng cho các thao tác lấy dữ liệu chi tiết thuốc.
     */
    public function __construct()
    {
        $this->chiTietModel = $this->model("chiTietThuocModel");
    }

    /**
     * Hiển thị trang chi tiết một loại thuốc theo id: lấy thông tin thuốc,
     * danh sách ảnh phụ, thông tin lô mới nhất, tính hạn mức mua tối đa
     * phù hợp rồi render toàn bộ dữ liệu vào view chi tiết thuốc.
     * Nếu id không hợp lệ hoặc không tìm thấy thuốc, chuyển hướng về
     * danh sách thuốc của khách hàng.
     * @param idThuoc ID thuốc cần xem chi tiết (mặc định 0 nếu không truyền)
     */
    public function chiTiet($idThuoc = 0)
    {
        $idThuoc = intval($idThuoc); // Ép kiểu về số nguyên để tránh dữ liệu đầu vào không hợp lệ
        if ($idThuoc <= 0) {
            $this->redirect('khachHang/thuoc'); // ID không hợp lệ -> quay về danh sách thuốc
        }

        $thuoc = $this->chiTietModel->getChiTietThuocTheoID($idThuoc); // Thông tin chi tiết thuốc theo id
        if (!$thuoc) {
            $this->redirect('khachHang/thuoc'); // Không tìm thấy thuốc -> quay về danh sách thuốc
        }


        // Lấy danh sách ảnh phụ
        $danhSachAnhRaw = $this->chiTietModel->getDanhSachAnhThuocTheoID($idThuoc); // Danh sách ảnh gốc từ database
        $danhSachAnh = array(); // Danh sách ảnh đã xử lý đường dẫn, dùng để hiển thị ra view
        if (!empty($danhSachAnhRaw)) {
            foreach ($danhSachAnhRaw as $img) {
                $danhSachAnh[] = ['duongDan' => $this->xuLyDuongDanAnh($img['duongDan'])];
            }
        } else {
            // Không có ảnh nào -> dùng ảnh mặc định/placeholder
            $danhSachAnh[] = ['duongDan' => $this->xuLyDuongDanAnh('')];
        }

        // Lấy thông tin lô
        $loInfo = $this->chiTietModel->getThongTinLoMoiNhatTheoID($idThuoc); // Thông tin lô mới nhất của thuốc (có thể null)

        $gioiHanMua = intval($thuoc['gioiHanMua']); // Giới hạn số lượng được phép mua mỗi lần (0 = không giới hạn)
        $tongTon = intval($thuoc['tongTon']);        // Tổng số lượng tồn kho hiện có của thuốc

        // TÍNH HẠN MỨC MUA TỐI ĐA PHÙ HỢP (TỒN KHO & GIỚI HẠN MUA)
        if ($gioiHanMua > 0) {
            $maxAllowed = min($gioiHanMua, $tongTon); // Lấy giá trị nhỏ hơn giữa giới hạn mua và tồn kho thực tế
            $gioiHanTxt = $gioiHanMua . ' ' . $thuoc['donViTinh']; // Chuỗi hiển thị giới hạn mua kèm đơn vị tính
        } else {
            $maxAllowed = $tongTon; // Không giới hạn mua -> hạn mức tối đa chính là tồn kho hiện có
            $gioiHanTxt = 'Không giới hạn'; // Chuỗi hiển thị khi thuốc không có giới hạn mua
        }

        $data = [
            'title' => $thuoc['tenThuoc'] . ' – PharmaCare',                                              // Tiêu đề trang (thẻ <title>)
            'page_title' => 'Chi tiết sản phẩm',                                                          // Tiêu đề hiển thị trên header trang
            'active_tab' => 'thuoc',                                                                      // Tab đang active trên thanh điều hướng
            'page_css' => 'chiTietThuoc',                                                                  // Tên file CSS riêng cho trang này
            'thuoc' => $thuoc,                                                                             // Toàn bộ dữ liệu chi tiết thuốc
            'isKeDon' => false,                                                                            // Cờ đánh dấu đây không phải thuốc kê đơn
            'anhChinhUrl' => $danhSachAnh[0]['duongDan'],                                                  // Ảnh chính hiển thị đầu tiên (ảnh đầu trong danh sách)
            'danhSachAnh' => $danhSachAnh,                                                                 // Toàn bộ danh sách ảnh để hiển thị gallery
            'maLoTxt' => $loInfo ? $loInfo['maLo'] : 'Chưa cập nhật',                                       // Mã lô hiển thị, hoặc thông báo nếu chưa có lô
            'nsxTxt' => ($loInfo && $loInfo['ngaySanXuat']) ? date('d/m/Y', strtotime($loInfo['ngaySanXuat'])) : '—', // Ngày sản xuất đã định dạng, hoặc '—' nếu không có
            'hsdTxt' => ($loInfo && $loInfo['hanSuDung']) ? date('d/m/Y', strtotime($loInfo['hanSuDung'])) : '—',     // Hạn sử dụng đã định dạng, hoặc '—' nếu không có
            'gioiHanTxt' => $gioiHanTxt,                                                                    // Chuỗi hiển thị giới hạn mua đã tính ở trên
            'maxAllowed' => $maxAllowed                                                                     // Số lượng tối đa khách hàng được phép mua
        ];

        ob_start();
        $this->view('khachHang/chiTietThuoc', $data);
        $content = ob_get_clean(); // Nội dung view chi tiết thuốc, dùng để gán vào layout chung

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }
}
