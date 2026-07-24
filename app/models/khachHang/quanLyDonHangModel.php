<?php
class quanLyDonHangModel extends Model
{
    // Chỉ dùng các cột CÓ THẬT trong bảng DonHang:
    // idDonHang, ngayDat, tongTien, trangThai, lyDoHuy

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
}
