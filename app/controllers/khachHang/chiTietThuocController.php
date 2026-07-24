<?php
class chiTietThuocController extends Controller
{
    private $chiTietModel;

    public function __construct()
    {
        $this->chiTietModel = $this->model("chiTietThuocModel");
    }

    public function chiTiet($idThuoc = 0)
    {
        $idThuoc = intval($idThuoc);
        if ($idThuoc <= 0) {
            $this->redirect('khachHang/thuoc');
        }

        $thuoc = $this->chiTietModel->getChiTietThuocTheoID($idThuoc);
        if (!$thuoc) {
            $this->redirect('khachHang/thuoc');
        }

        // TỰ ĐỘNG CHUYỂN HƯỚNG NẾU LÀ THUỐC KÊ ĐƠN (RX)
        if ($thuoc['yeuCauKeDon'] === 'Kê đơn') {
            $this->redirect('khachHang/dangKeToaThuoc?idThuoc=' . $idThuoc);
        }

        // Lấy danh sách ảnh phụ
        $danhSachAnhRaw = $this->chiTietModel->getDanhSachAnhThuocTheoID($idThuoc);
        $danhSachAnh = array();
        if (!empty($danhSachAnhRaw)) {
            foreach ($danhSachAnhRaw as $img) {
                $danhSachAnh[] = ['duongDan' => $this->xuLyDuongDanAnh($img['duongDan'])];
            }
        } else {
            $danhSachAnh[] = ['duongDan' => $this->xuLyDuongDanAnh('')];
        }

        // Lấy thông tin lô
        $loInfo = $this->chiTietModel->getThongTinLoMoiNhatTheoID($idThuoc);

        $gioiHanMua = intval($thuoc['gioiHanMua']);
        $tongTon = intval($thuoc['tongTon']);

        // TÍNH HẠN MỨC MUA TỐI ĐA PHÙ HỢP (TỒN KHO & GIỚI HẠN MUA)
        if ($gioiHanMua > 0) {
            $maxAllowed = min($gioiHanMua, $tongTon);
            $gioiHanTxt = $gioiHanMua . ' ' . $thuoc['donViTinh'];
        } else {
            $maxAllowed = $tongTon;
            $gioiHanTxt = 'Không giới hạn';
        }

        $data = [
            'title' => $thuoc['tenThuoc'] . ' – PharmaCare',
            'page_title' => 'Chi tiết sản phẩm',
            'active_tab' => 'thuoc',
            'page_css' => 'chiTietThuoc',
            'thuoc' => $thuoc,
            'isKeDon' => false,
            'anhChinhUrl' => $danhSachAnh[0]['duongDan'],
            'danhSachAnh' => $danhSachAnh,
            'maLoTxt' => $loInfo ? $loInfo['maLo'] : 'Chưa cập nhật',
            'nsxTxt' => ($loInfo && $loInfo['ngaySanXuat']) ? date('d/m/Y', strtotime($loInfo['ngaySanXuat'])) : '—',
            'hsdTxt' => ($loInfo && $loInfo['hanSuDung']) ? date('d/m/Y', strtotime($loInfo['hanSuDung'])) : '—',
            'gioiHanTxt' => $gioiHanTxt,
            'maxAllowed' => $maxAllowed
        ];

        ob_start();
        $this->view('khachHang/chiTietThuoc', $data);
        $content = ob_get_clean();

        $this->view('layouts/khachHangLayout', array_merge($data, ['content' => $content]));
    }
}
