<?php
class chiTietThuocModel extends Model
{
    // 1. Lấy thông tin chi tiết của thuốc theo Mã định danh (idThuoc)
    public function getChiTietThuocTheoID($idThuoc)
    {
        $sql = "SELECT t.*, d.tenDanhMuc, COALESCE(SUM(l.soLuongTon), 0) AS tongTon
                FROM Thuoc t
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc
                LEFT JOIN LoThuoc l ON t.idThuoc = l.idThuoc AND l.hanSuDung >= CURDATE()
                WHERE t.idThuoc = :idThuoc AND (t.trangThai = 1 OR t.trangThai = 'true' OR t.trangThai = '1')
                GROUP BY t.idThuoc";

        $this->db->query($sql);
        $this->db->bind(':idThuoc', $idThuoc);
        return $this->db->single();
    }

    // 2. Lấy danh sách tất cả hình ảnh phụ từ bảng HinhAnhThuoc
    public function getDanhSachAnhThuocTheoID($idThuoc)
    {
        $sql = "SELECT duongDan FROM HinhAnhThuoc WHERE idThuoc = :idThuoc ORDER BY idHinhAnh ASC";
        $this->db->query($sql);
        $this->db->bind(':idThuoc', $idThuoc);
        return $this->db->resultSet();
    }

    // 3. Lấy thông tin lô thuốc gần nhất còn hạn dùng
    public function getThongTinLoMoiNhatTheoID($idThuoc)
    {
        $sql = "SELECT maLo, ngaySanXuat, hanSuDung FROM LoThuoc 
                WHERE idThuoc = :idThuoc AND hanSuDung >= CURDATE() 
                ORDER BY hanSuDung ASC LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':idThuoc', $idThuoc);
        return $this->db->single();
    }
}
