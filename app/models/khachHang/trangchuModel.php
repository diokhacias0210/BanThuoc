<?php
class trangChuModel extends Model
{
    // Lấy danh sách sản phẩm Thuốc phổ biến kèm hình ảnh đầu tiên
    public function getThuocPhoBien($limit = 8)
    {
        $sql = "SELECT t.idThuoc, t.tenThuoc, t.giaBan, t.yeuCauKeDon, t.donViTinh,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY h.idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM Thuoc t
                WHERE (t.trangThai = 1 OR t.trangThai = 'true' OR t.trangThai = '1')
                ORDER BY t.idThuoc ASC
                LIMIT :limit";

        $this->db->query($sql);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    // Lấy danh sách sản phẩm "Có thể bạn cần" (Sắp xếp ngẫu nhiên hoặc theo tiêu chí)
    public function getThuocGoiY($limit = 4)
    {
        $sql = "SELECT t.idThuoc, t.tenThuoc, t.giaBan, t.yeuCauKeDon, t.donViTinh,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY h.idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM Thuoc t
                WHERE (t.trangThai = 1 OR t.trangThai = 'true' OR t.trangThai = '1')
                ORDER BY t.idThuoc DESC
                LIMIT :limit";

        $this->db->query($sql);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    // Lấy danh sách tất cả sản phẩm hiển thị khung lớn
    public function getTatCaThuocKhungLon($limit = 6)
    {
        $sql = "SELECT t.idThuoc, t.tenThuoc, t.giaBan, t.yeuCauKeDon, t.donViTinh,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY h.idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM Thuoc t
                WHERE (t.trangThai = 1 OR t.trangThai = 'true' OR t.trangThai = '1')
                LIMIT :limit";

        $this->db->query($sql);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
