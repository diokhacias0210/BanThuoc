<?php
class DongGoiModel extends Model
{
    // Danh sách đơn hàng CẦN ĐÓNG GÓI: đã xác nhận (chờ đóng gói) hoặc đang giao (đã đóng gói xong)
    public function layDanhSachDonCanDongGoi()
    {
        $sql = "SELECT dh.idDonHang, dh.ngayDat, dh.tongTien, dh.trangThai,
                       nd.hoTen,
                       (SELECT COUNT(*) FROM ChiTietDonHang c WHERE c.idDonHang = dh.idDonHang) AS soLoaiThuoc,
                       (SELECT COALESCE(SUM(c.soLuong), 0) FROM ChiTietDonHang c WHERE c.idDonHang = dh.idDonHang) AS tongSoThuoc
                FROM DonHang dh
                INNER JOIN NguoiDung nd ON nd.idNguoiDung = dh.idKhachHang
                WHERE dh.trangThai IN ('DA_XAC_NHAN', 'DANG_GIAO')
                ORDER BY dh.ngayDat ASC";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    // Thông tin tổng quan 1 đơn hàng (dùng để hiển thị đầu phiếu đóng gói)
    public function layThongTinDonHang($idDonHang)
    {
        $sql = "SELECT dh.idDonHang, dh.ngayDat, dh.tongTien, dh.trangThai,
                       nd.idNguoiDung AS idKhachHang, nd.hoTen, nd.soDienThoai
                FROM DonHang dh
                INNER JOIN NguoiDung nd ON nd.idNguoiDung = dh.idKhachHang
                WHERE dh.idDonHang = :idDonHang";
        $this->db->query($sql);
        $this->db->bind(':idDonHang', $idDonHang);
        return $this->db->single();
    }

    // Địa chỉ giao hàng MẶC ĐỊNH của khách (DonHang không lưu trực tiếp địa chỉ,
    // nên lấy tạm địa chỉ mặc định của khách hàng trong bảng DiaChiGiaoHang)
    public function layDiaChiMacDinh($idKhachHang)
    {
        $sql = "SELECT tenNguoiNhan, soDienThoaiNhan, diaChiChiTiet
                FROM DiaChiGiaoHang
                WHERE idNguoiDung = :idKhachHang
                ORDER BY laMacDinh DESC, idDiaChi ASC
                LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':idKhachHang', $idKhachHang);
        return $this->db->single();
    }

    // Danh sách thuốc cần nhặt trong đơn + gợi ý LÔ theo FEFO (hạn dùng gần nhất, còn tồn kho)
    public function layChiTietDeDongGoi($idDonHang)
    {
        $sqlChiTiet = "SELECT c.id, c.idThuoc, c.soLuong, c.donGia,
                               t.tenThuoc, t.donViTinh
                        FROM ChiTietDonHang c
                        INNER JOIN Thuoc t ON t.idThuoc = c.idThuoc
                        WHERE c.idDonHang = :idDonHang
                        ORDER BY c.id ASC";
        $this->db->query($sqlChiTiet);
        $this->db->bind(':idDonHang', $idDonHang);
        $danhSach = $this->db->resultSet();

        // Với mỗi dòng thuốc, gợi ý lô có hạn dùng gần nhất còn đủ tồn kho (FEFO)
        foreach ($danhSach as &$item) {
            $sqlLo = "SELECT idLo, maLo, hanSuDung, soLuongTon
                       FROM LoThuoc
                       WHERE idThuoc = :idThuoc AND soLuongTon > 0
                       ORDER BY hanSuDung ASC
                       LIMIT 1";
            $this->db->query($sqlLo);
            $this->db->bind(':idThuoc', $item['idThuoc']);
            $item['loGoiY'] = $this->db->single(); // null nếu hết hàng trong kho
        }

        return $danhSach;
    }

    // Xác nhận đóng gói xong -> chuyển trạng thái đơn từ DA_XAC_NHAN sang DANG_GIAO
    // Đồng thời trừ tồn kho theo FEFO cho từng thuốc trong đơn
    public function xacNhanDongGoiXong($idDonHang)
    {
        // Chỉ cho phép chuyển khi đơn đang ở trạng thái DA_XAC_NHAN (tránh bấm nhầm/đóng gói trùng)
        $sqlCheck = "SELECT trangThai FROM DonHang WHERE idDonHang = :id";
        $this->db->query($sqlCheck);
        $this->db->bind(':id', $idDonHang);
        $donHang = $this->db->single();

        if (!$donHang || $donHang['trangThai'] !== 'DA_XAC_NHAN') {
            return false;
        }

        // Trừ tồn kho theo FEFO cho từng thuốc trong đơn
        $chiTiet = $this->layChiTietDeDongGoi($idDonHang);
        foreach ($chiTiet as $item) {
            $this->truKhoTheoFEFO($item['idThuoc'], $item['soLuong']);
        }

        $sqlUpdate = "UPDATE DonHang SET trangThai = 'DANG_GIAO' WHERE idDonHang = :id AND trangThai = 'DA_XAC_NHAN'";
        $this->db->query($sqlUpdate);
        $this->db->bind(':id', $idDonHang);
        return $this->db->execute();
    }

    // Trừ tồn kho theo nguyên tắc FEFO: ưu tiên trừ từ lô có hạn dùng gần nhất trước
    private function truKhoTheoFEFO($idThuoc, $soLuongCanTru)
    {
        $sqlLo = "SELECT idLo, soLuongTon FROM LoThuoc
                   WHERE idThuoc = :idThuoc AND soLuongTon > 0
                   ORDER BY hanSuDung ASC";
        $this->db->query($sqlLo);
        $this->db->bind(':idThuoc', $idThuoc);
        $cacLo = $this->db->resultSet();

        foreach ($cacLo as $lo) {
            if ($soLuongCanTru <= 0) break;

            $soLuongTru = min($lo['soLuongTon'], $soLuongCanTru);

            $sqlUpdate = "UPDATE LoThuoc SET soLuongTon = soLuongTon - :soLuongTru WHERE idLo = :idLo";
            $this->db->query($sqlUpdate);
            $this->db->bind(':soLuongTru', $soLuongTru);
            $this->db->bind(':idLo', $lo['idLo']);
            $this->db->execute();

            $soLuongCanTru -= $soLuongTru;
        }
    }
}
