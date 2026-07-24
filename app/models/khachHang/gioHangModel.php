<?php
class gioHangModel extends Model
{
    // Lấy hoặc tự động tạo giỏ hàng (Đã sửa lỗi FK 1452)
    public function layHoacTaoGioHang($idKhachHang)
    {
        // 1. Đảm bảo idKhachHang luôn tồn tại trong bảng KhachHang
        $sqlEnsureKhachHang = "INSERT IGNORE INTO KhachHang (idNguoiDung, diemTichLuy) VALUES (:id, 0)";
        $this->db->query($sqlEnsureKhachHang);
        $this->db->bind(':id', $idKhachHang);
        $this->db->execute();

        // 2. Tìm giỏ hàng hiện tại
        $sql = "SELECT idGioHang FROM GioHang WHERE idKhachHang = :idKhachHang";
        $this->db->query($sql);
        $this->db->bind(':idKhachHang', $idKhachHang);
        $gioHang = $this->db->single();

        if ($gioHang) {
            return $gioHang['idGioHang'];
        }

        // 3. Nếu chưa có -> Tạo giỏ hàng mới
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
                       t.tenThuoc, t.donViTinh, d.tenDanhMuc,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY h.idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM ChiTietGioHang c
                INNER JOIN Thuoc t ON c.idThuoc = t.idThuoc
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc
                WHERE c.idGioHang = :idGioHang
                ORDER BY c.id DESC";

        $this->db->query($sql);
        $this->db->bind(':idGioHang', $idGioHang);
        return $this->db->resultSet();
    }

    public function themItemVaoGio($idGioHang, $idThuoc, $soLuong, $donGia, $trangThaiThaoTac = 'CHO_PHEP', $idDonThuoc = null)
    {
        // Thuốc không kê đơn (CHO_PHEP) -> Cộng dồn số lượng nếu đã có trong giỏ
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

        // Thuốc kê đơn (KHOA) -> Tạo dòng riêng biệt độc lập
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
