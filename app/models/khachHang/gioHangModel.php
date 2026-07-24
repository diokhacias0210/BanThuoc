<?php
class gioHangModel extends Model
{
    public function layHoacTaoGioHang($idKhachHang)
    {
        $sqlEnsureKhachHang = "INSERT IGNORE INTO KhachHang (idNguoiDung, diemTichLuy) VALUES (:id, 0)";
        $this->db->query($sqlEnsureKhachHang);
        $this->db->bind(':id', $idKhachHang);
        $this->db->execute();

        $sql = "SELECT idGioHang FROM GioHang WHERE idKhachHang = :idKhachHang";
        $this->db->query($sql);
        $this->db->bind(':idKhachHang', $idKhachHang);
        $gioHang = $this->db->single();

        if ($gioHang) {
            return $gioHang['idGioHang'];
        }

        $sqlInsert = "INSERT INTO GioHang (idKhachHang, ngayTao) VALUES (:idKhachHang, NOW())";
        $this->db->query($sqlInsert);
        $this->db->bind(':idKhachHang', $idKhachHang);
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return null;
    }

    public function layDanhSachChiTietGioHang($idGioHang)
    {
        $sql = "SELECT c.id, c.idGioHang, c.idThuoc, c.idDonThuoc, c.soLuong, c.donGia, c.trangThaiThaoTac,
                       t.tenThuoc, t.donViTinh, t.gioiHanMua, d.tenDanhMuc,
                       COALESCE(SUM(l.soLuongTon), 0) AS tongTon,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY h.idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM ChiTietGioHang c
                INNER JOIN Thuoc t ON c.idThuoc = t.idThuoc
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc
                LEFT JOIN LoThuoc l ON t.idThuoc = l.idThuoc AND l.hanSuDung >= CURDATE()
                WHERE c.idGioHang = :idGioHang
                GROUP BY c.id
                ORDER BY c.id DESC";

        $this->db->query($sql);
        $this->db->bind(':idGioHang', $idGioHang);
        return $this->db->resultSet();
    }

    public function getSoLuongHienCoTrongGio($idGioHang, $idThuoc)
    {
        $sql = "SELECT COALESCE(SUM(soLuong), 0) AS total 
                FROM ChiTietGioHang 
                WHERE idGioHang = :idGioHang AND idThuoc = :idThuoc AND trangThaiThaoTac = 'CHO_PHEP'";
        $this->db->query($sql);
        $this->db->bind(':idGioHang', $idGioHang);
        $this->db->bind(':idThuoc', $idThuoc);
        $row = $this->db->single();
        return $row ? intval($row['total']) : 0;
    }

    public function getChiTietItemTheoID($idChiTiet, $idGioHang)
    {
        $sql = "SELECT c.*, t.gioiHanMua, COALESCE(SUM(l.soLuongTon), 0) AS tongTon
                FROM ChiTietGioHang c
                INNER JOIN Thuoc t ON c.idThuoc = t.idThuoc
                LEFT JOIN LoThuoc l ON t.idThuoc = l.idThuoc AND l.hanSuDung >= CURDATE()
                WHERE c.id = :id AND c.idGioHang = :idGioHang
                GROUP BY c.id";
        $this->db->query($sql);
        $this->db->bind(':id', $idChiTiet);
        $this->db->bind(':idGioHang', $idGioHang);
        return $this->db->single();
    }

    public function demSoChungLoaiThuocTrongGio($idGioHang)
    {
        $sql = "SELECT COUNT(DISTINCT idThuoc) AS total FROM ChiTietGioHang WHERE idGioHang = :idGioHang";
        $this->db->query($sql);
        $this->db->bind(':idGioHang', $idGioHang);
        $row = $this->db->single();
        return $row ? intval($row['total']) : 0;
    }

    public function themItemVaoGio($idGioHang, $idThuoc, $soLuong, $donGia, $trangThaiThaoTac = 'CHO_PHEP', $idDonThuoc = null)
    {
        if ($trangThaiThaoTac === 'CHO_PHEP') {
            $sqlCheck = "SELECT id, soLuong FROM ChiTietGioHang 
                         WHERE idGioHang = :idGioHang AND idThuoc = :idThuoc AND trangThaiThaoTac = 'CHO_PHEP'";
            $this->db->query($sqlCheck);
            $this->db->bind(':idGioHang', $idGioHang);
            $this->db->bind(':idThuoc', $idThuoc);
            $item = $this->db->single();

            if ($item) {
                $soLuongMoi = $item['soLuong'] + $soLuong;
                $sqlUpdate = "UPDATE ChiTietGioHang SET soLuong = :soLuong, donGia = :donGia WHERE id = :id";
                $this->db->query($sqlUpdate);
                $this->db->bind(':soLuong', $soLuongMoi);
                $this->db->bind(':donGia', $donGia);
                $this->db->bind(':id', $item['id']);
                return $this->db->execute();
            }
        }

        $sqlInsert = "INSERT INTO ChiTietGioHang (idGioHang, idThuoc, idDonThuoc, soLuong, donGia, trangThaiThaoTac) 
                      VALUES (:idGioHang, :idThuoc, :idDonThuoc, :soLuong, :donGia, :trangThaiThaoTac)";
        $this->db->query($sqlInsert);
        $this->db->bind(':idGioHang', $idGioHang);
        $this->db->bind(':idThuoc', $idThuoc);
        $this->db->bind(':idDonThuoc', $idDonThuoc);
        $this->db->bind(':soLuong', $soLuong);
        $this->db->bind(':donGia', $donGia);
        $this->db->bind(':trangThaiThaoTac', $trangThaiThaoTac);

        return $this->db->execute();
    }

    public function capNhatSoLuongItem($idChiTiet, $idGioHang, $soLuong)
    {
        $sql = "UPDATE ChiTietGioHang SET soLuong = :soLuong WHERE id = :id AND idGioHang = :idGioHang AND trangThaiThaoTac = 'CHO_PHEP'";
        $this->db->query($sql);
        $this->db->bind(':soLuong', $soLuong);
        $this->db->bind(':id', $idChiTiet);
        $this->db->bind(':idGioHang', $idGioHang);
        return $this->db->execute();
    }

    public function xoaItemKhoiGio($idChiTiet, $idGioHang)
    {
        $sql = "DELETE FROM ChiTietGioHang WHERE id = :id AND idGioHang = :idGioHang";
        $this->db->query($sql);
        $this->db->bind(':id', $idChiTiet);
        $this->db->bind(':idGioHang', $idGioHang);
        return $this->db->execute();
    }
}
