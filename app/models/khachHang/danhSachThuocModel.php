<?php
class danhSachThuocModel extends Model
{
    public function getAllDanhMuc()
    {
        $sql = "SELECT * FROM DanhMucThuoc ORDER BY idDanhMuc ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function getDanhSachThuocFull()
    {
        $sql = "SELECT t.*, 
                       d.tenDanhMuc,
                       COALESCE(SUM(l.soLuongTon), 0) AS tongTon,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM Thuoc t
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc
                LEFT JOIN LoThuoc l ON t.idThuoc = l.idThuoc AND l.hanSuDung >= CURDATE()
                WHERE (t.trangThai = 1 OR t.trangThai = '1' OR t.trangThai = 'true')
                GROUP BY t.idThuoc
                ORDER BY t.idThuoc DESC";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function timKiemThuocAjax($keyword)
    {
        $sql = "SELECT t.idThuoc, t.tenThuoc, t.giaBan,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM Thuoc t
                WHERE (t.trangThai = 1 OR t.trangThai = '1' OR t.trangThai = 'true')
                  AND t.tenThuoc LIKE :kw
                LIMIT 8";

        $this->db->query($sql);
        $this->db->bind(':kw', '%' . $keyword . '%');
        return $this->db->resultSet();
    }
}
