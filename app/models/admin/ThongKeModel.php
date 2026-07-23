<?php
class ThongKeModel extends Model
{
    // 1. Tính toán các chỉ số tổng quan theo mốc thời gian
    public function getOverviewStats($startDate, $endDate)
    {
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        // Doanh thu và Số đơn giao thành công
        $sqlRev = "SELECT COALESCE(SUM(tongTien), 0) AS totalRevenue, COUNT(idDonHang) AS totalCompleted 
                   FROM DonHang 
                   WHERE trangThai = 'DA_GIAO' AND ngayDat BETWEEN :start AND :end";
        $this->db->query($sqlRev);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $revData = $this->db->single();

        // Số đơn bị hủy
        $sqlCancel = "SELECT COUNT(idDonHang) AS totalCanceled 
                      FROM DonHang 
                      WHERE trangThai = 'DA_HUY' AND ngayDat BETWEEN :start AND :end";
        $this->db->query($sqlCancel);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $cancelData = $this->db->single();

        // Số lượng sản phẩm bán ra
        $sqlItems = "SELECT COALESCE(SUM(ct.soLuong), 0) AS totalItems 
                     FROM ChiTietDonHang ct 
                     INNER JOIN DonHang dh ON ct.idDonHang = dh.idDonHang 
                     WHERE dh.trangThai = 'DA_GIAO' AND dh.ngayDat BETWEEN :start AND :end";
        $this->db->query($sqlItems);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $itemsData = $this->db->single();

        return array(
            'totalRevenue' => floatval($revData['totalRevenue']),
            'totalCompleted' => intval($revData['totalCompleted']),
            'totalCanceled' => intval($cancelData['totalCanceled']),
            'totalItems' => intval($itemsData['totalItems'])
        );
    }

    // 2. Thống kê thuốc bán ra xếp theo doanh thu giảm dần
    public function getMedicineSalesStats($startDate, $endDate)
    {
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        $sql = "SELECT t.idThuoc, t.tenThuoc, t.thanhPhan, t.hamLuong, d.tenDanhMuc, 
                       SUM(ct.soLuong) AS luotBan, 
                       SUM(ct.soLuong * (ct.donGia - ct.giamGia)) AS doanhThu 
                FROM ChiTietDonHang ct 
                INNER JOIN DonHang dh ON ct.idDonHang = dh.idDonHang 
                INNER JOIN Thuoc t ON ct.idThuoc = t.idThuoc 
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc 
                WHERE dh.trangThai = 'DA_GIAO' AND dh.ngayDat BETWEEN :start AND :end 
                GROUP BY t.idThuoc 
                ORDER BY doanhThu DESC";

        $this->db->query($sql);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        return $this->db->resultSet();
    }
}
