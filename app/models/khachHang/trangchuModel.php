<?php
class trangChuModel extends Model
{
    /**
     * 1. Lấy danh sách thuốc bán chạy nhất (Thuốc phổ biến)
     */
    public function getThuocBanChayNhat($limit = 8)
    {
        $sql = "SELECT t.*, 
                       d.tenDanhMuc,
                       COALESCE(SUM(l.soLuongTon), 0) AS tongTon,
                       COALESCE(SUM(ctdh.soLuong), 0) AS tongDaBan,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM Thuoc t
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc
                LEFT JOIN LoThuoc l ON t.idThuoc = l.idThuoc AND l.hanSuDung >= CURDATE()
                LEFT JOIN ChiTietDonHang ctdh ON t.idThuoc = ctdh.idThuoc
                LEFT JOIN DonHang dh ON ctdh.idDonHang = dh.idDonHang AND dh.trangThai != 'DA_HUY'
                WHERE (t.trangThai = 1 OR t.trangThai = '1' OR t.trangThai = 'true')
                GROUP BY t.idThuoc
                ORDER BY tongDaBan DESC, t.idThuoc DESC
                LIMIT :limit";

        $this->db->query($sql);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * 2. Lấy tất cả thuốc thêm mới nhất (Tất cả sản phẩm)
     */
    public function getThuocMoiNhat($limit = 12)
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
                ORDER BY t.idThuoc DESC
                LIMIT :limit";

        $this->db->query($sql);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
