<?php
class quanLyDonHangModel extends Model
{
    // Bảng DonHang thật có: idDonHang, idKhachHang, ngayDat, tongTien,
    // phiVanChuyen, trangThai, lyDoHuy

    // Lấy danh sách đơn hàng của 1 khách hàng, mới nhất lên đầu
    public function getDonHangTheoKhachHang($idKhachHang)
    {
        $sql = "SELECT idDonHang AS id,
                       DATE_FORMAT(ngayDat, '%Y-%m-%d %H:%i') AS date,
                       tongTien AS total,
                       trangThai AS status
                FROM DonHang
                WHERE idKhachHang = :idKhachHang
                ORDER BY ngayDat DESC";
        $this->db->query($sql);
        $this->db->bind(':idKhachHang', $idKhachHang);
        return $this->db->resultSet();
    }

    // Huỷ đơn hàng - chỉ cho phép huỷ khi đơn đang ở trạng thái Chờ xác nhận
    // và đúng chủ đơn hàng (idKhachHang khớp)
    public function huyDonHang($idDonHang, $idKhachHang, $lyDoHuy)
    {
        $sql = "UPDATE DonHang
                SET trangThai = 'DA_HUY', lyDoHuy = :lyDoHuy
                WHERE idDonHang = :idDonHang
                  AND idKhachHang = :idKhachHang
                  AND trangThai = 'CHO_XAC_NHAN'";
        $this->db->query($sql);
        $this->db->bind(':lyDoHuy', $lyDoHuy);
        $this->db->bind(':idDonHang', $idDonHang);
        $this->db->bind(':idKhachHang', $idKhachHang);
        return $this->db->execute();
    }

    // ===================== MỚI THÊM CHO TRANG CHI TIẾT =====================

    // Lấy thông tin header của 1 đơn hàng (kèm kiểm tra đúng chủ đơn hàng)
    // Join ThanhToan để lấy phương thức thanh toán (ThanhToan.idDonHang là UNIQUE
    // nên mỗi đơn hàng chỉ có tối đa 1 bản ghi thanh toán -> LEFT JOIN an toàn)
    public function layThongTinDonHang($idDonHang, $idKhachHang)
    {
        $sql = "SELECT dh.idDonHang,
                       DATE_FORMAT(dh.ngayDat, '%Y-%m-%d %H:%i') AS ngayDat,
                       dh.tongTien,
                       dh.phiVanChuyen,
                       dh.trangThai,
                       dh.lyDoHuy,
                       tt.phuongThuc AS phuongThucThanhToan,
                       tt.trangThai AS trangThaiThanhToan
                FROM DonHang dh
                LEFT JOIN ThanhToan tt ON tt.idDonHang = dh.idDonHang
                WHERE dh.idDonHang = :idDonHang
                  AND dh.idKhachHang = :idKhachHang";
        $this->db->query($sql);
        $this->db->bind(':idDonHang', $idDonHang);
        $this->db->bind(':idKhachHang', $idKhachHang);
        return $this->db->single();
    }

    // Lấy danh sách thuốc trong 1 đơn hàng (join bảng Thuoc để lấy tên)
    public function laySanPhamTrongDonHang($idDonHang)
    {
        $sql = "SELECT t.tenThuoc AS name,
                       ct.soLuong AS qty,
                       ct.donGia AS price,
                       ct.giamGia AS giamGia,
                       (ct.soLuong * ct.donGia - IFNULL(ct.giamGia, 0)) AS lineTotal
                FROM ChiTietDonHang ct
                JOIN Thuoc t ON t.idThuoc = ct.idThuoc
                WHERE ct.idDonHang = :idDonHang";
        $this->db->query($sql);
        $this->db->bind(':idDonHang', $idDonHang);
        return $this->db->resultSet();
    }

    // ⚠️ TẠM THỜI: DonHang chưa lưu idDiaChi nên không biết chính xác đơn hàng
    // đã giao tới địa chỉ nào tại thời điểm đặt. Hàm này lấy địa chỉ MẶC ĐỊNH
    // HIỆN TẠI của khách hàng để hiển thị tạm. Nếu khách đổi địa chỉ mặc định
    // sau khi đặt đơn, thông tin hiển thị ở đơn hàng cũ có thể không còn đúng.
    // -> Khuyến nghị: ALTER TABLE DonHang ADD COLUMN idDiaChi INT, rồi lưu
    //    idDiaChi lúc tạo đơn để có dữ liệu chính xác tuyệt đối.
    public function layDiaChiGiaoHangMacDinh($idKhachHang)
    {
        $sql = "SELECT tenNguoiNhan,
                       soDienThoaiNhan AS soDienThoai,
                       diaChiChiTiet AS diaChiGiaoHang
                FROM DiaChiGiaoHang
                WHERE idNguoiDung = :idKhachHang
                ORDER BY laMacDinh DESC, idDiaChi DESC
                LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':idKhachHang', $idKhachHang);
        return $this->db->single();
    }
}
