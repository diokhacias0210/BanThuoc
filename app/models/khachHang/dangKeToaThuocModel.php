<?php
class dangKeToaThuocModel extends Model
{
    public function layDanhSachThuocSystem()
    {
        $sql = "SELECT idThuoc, tenThuoc, giaBan, yeuCauKeDon, donViTinh 
                FROM Thuoc 
                WHERE (trangThai = 1 OR trangThai = 'true' OR trangThai = '1') 
                  AND yeuCauKeDon = 'Kê đơn'
                ORDER BY tenThuoc ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function taoDonThuoc($idKhachHang, $ghiChu)
    {
        $sql = "INSERT INTO DonThuoc (idKhachHang, ghiChu, trangThai, ngayGui) 
                VALUES (:idKhachHang, :ghiChu, 'CHO_DUYET', NOW())";
        $this->db->query($sql);
        $this->db->bind(':idKhachHang', $idKhachHang);
        $this->db->bind(':ghiChu', $ghiChu);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function themHinhAnhDonThuoc($idDonThuoc, $duongDan)
    {
        $sql = "INSERT INTO HinhAnhDonThuoc (idDonThuoc, duongDan) VALUES (:idDonThuoc, :duongDan)";
        $this->db->query($sql);
        $this->db->bind(':idDonThuoc', $idDonThuoc);
        $this->db->bind(':duongDan', $duongDan);
        return $this->db->execute();
    }

    public function themChiTietDonThuoc($idDonThuoc, $tenThuoc, $soLuong = 1)
    {
        $sql = "INSERT INTO ChiTietDonThuoc (idDonThuoc, tenThuoc, soLuong) VALUES (:idDonThuoc, :tenThuoc, :soLuong)";
        $this->db->query($sql);
        $this->db->bind(':idDonThuoc', $idDonThuoc);
        $this->db->bind(':tenThuoc', $tenThuoc);
        $this->db->bind(':soLuong', $soLuong);
        return $this->db->execute();
    }

    public function timThuocTheoTen($tenThuoc)
    {
        $sql = "SELECT idThuoc, tenThuoc, giaBan FROM Thuoc WHERE LOWER(tenThuoc) = LOWER(:tenThuoc) LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':tenThuoc', trim($tenThuoc));
        return $this->db->single();
    }
}
