<?php
class danhSachThuocModel extends Model
{

    // Lấy danh sách thuốc khả dụng kinh doanh
    public function getList($search = '', $idDanhMuc = 'all', $keDon = 'all', $minPrice = 0, $maxPrice = 20000000)
    {
        // CẬP NHẬT SQL: Lấy đường dẫn ảnh đầu tiên từ bảng HinhAnhThuoc
        $sql = "SELECT t.idThuoc, t.tenThuoc, t.thanhPhan, t.hamLuong, t.donViTinh, t.giaBan, t.yeuCauKeDon, d.tenDanhMuc,
                       COALESCE(SUM(l.soLuongTon), 0) AS tongTon,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY h.idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM Thuoc t
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc
                LEFT JOIN LoThuoc l ON t.idThuoc = l.idThuoc AND l.hanSuDung >= CURDATE()
                WHERE (t.trangThai = 1 OR t.trangThai = 'true' OR t.trangThai = '1')";

        if (!empty($search)) {
            $sql .= " AND (t.tenThuoc LIKE :search OR t.thanhPhan LIKE :search)";
        }
        if ($idDanhMuc !== 'all' && !empty($idDanhMuc)) {
            $sql .= " AND t.idDanhMuc = :idDanhMuc";
        }
        if ($keDon !== 'all' && !empty($keDon)) {
            $sql .= " AND t.yeuCauKeDon = :keDon";
        }

        $sql .= " AND t.giaBan BETWEEN :minPrice AND :maxPrice";
        $sql .= " GROUP BY t.idThuoc ORDER BY t.idThuoc DESC";

        $this->db->query($sql);

        if (!empty($search)) $this->db->bind(':search', "%$search%");
        if ($idDanhMuc !== 'all' && !empty($idDanhMuc)) $this->db->bind(':idDanhMuc', $idDanhMuc);
        if ($keDon !== 'all' && !empty($keDon)) $this->db->bind(':keDon', $keDon);
        $this->db->bind(':minPrice', $minPrice);
        $this->db->bind(':maxPrice', $maxPrice);

        return $this->db->resultSet();
    }

    // Lấy tất cả danh mục thuốc
    public function getCategories()
    {
        $sql = "SELECT * FROM DanhMucThuoc ORDER BY idDanhMuc ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }
}
