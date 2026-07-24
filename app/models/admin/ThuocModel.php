<?php
class ThuocModel extends Model
{
    private $table = "Thuoc";

    // 1. Lấy danh sách thuốc kèm ảnh đại diện đầu tiên từ bảng HinhAnhThuoc
    public function getAll($search = '', $idDanhMuc = 'all', $phanLoai = 'all', $trangThai = 'all')
    {
        $sql = "SELECT t.*, d.tenDanhMuc, COALESCE(SUM(l.soLuongTon), 0) AS tongTon,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY h.idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM {$this->table} t 
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc 
                LEFT JOIN LoThuoc l ON t.idThuoc = l.idThuoc AND l.hanSuDung >= CURDATE()
                WHERE 1=1";

        if (!empty($search)) $sql .= " AND t.tenThuoc LIKE :search";
        if ($idDanhMuc !== 'all') $sql .= " AND t.idDanhMuc = :idDanhMuc";
        if ($phanLoai !== 'all') $sql .= " AND t.yeuCauKeDon = :phanLoai";

        if ($trangThai !== 'all') {
            if ($trangThai === 'active') {
                $sql .= " AND (t.trangThai = 1 OR t.trangThai = 'true' OR t.trangThai = '1')";
            } else {
                $sql .= " AND (t.trangThai = 0 OR t.trangThai = 'false' OR t.trangThai = '0' OR t.trangThai IS NULL)";
            }
        }

        $sql .= " GROUP BY t.idThuoc ORDER BY t.idThuoc DESC";
        $this->db->query($sql);

        if (!empty($search)) $this->db->bind(':search', "%$search%");
        if ($idDanhMuc !== 'all') $this->db->bind(':idDanhMuc', $idDanhMuc);
        if ($phanLoai !== 'all') $this->db->bind(':phanLoai', $phanLoai);

        return $this->db->resultSet();
    }

    public function getById($id)
    {
        $sql = "SELECT t.*, d.tenDanhMuc,
                       (SELECT duongDan FROM HinhAnhThuoc h WHERE h.idThuoc = t.idThuoc ORDER BY h.idHinhAnh ASC LIMIT 1) AS hinhAnh
                FROM {$this->table} t 
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc WHERE t.idThuoc = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Lấy tất cả danh sách ảnh của 1 loại thuốc
    public function getImagesByThuocId($idThuoc)
    {
        $sql = "SELECT duongDan FROM HinhAnhThuoc WHERE idThuoc = :idThuoc ORDER BY idHinhAnh ASC";
        $this->db->query($sql);
        $this->db->bind(':idThuoc', $idThuoc);
        return $this->db->resultSet();
    }

    // Lấy danh sách lô thuốc của sản phẩm
    public function getLotsByThuocId($idThuoc)
    {
        $sql = "SELECT *
                FROM LoThuoc
                WHERE idThuoc=:id
                ORDER BY hanSuDung ASC";
        $this->db->query($sql);
        $this->db->bind(':id', $idThuoc);
        return $this->db->resultSet();
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) 
                VALUES (:idDanhMuc, :tenThuoc, :thanhPhan, :hamLuong, :congDung, :donViTinh, :giaBan, :yeuCauKeDon, :gioiHanMua, :trangThai)";
        $this->db->query($sql);
        $this->db->bind(':idDanhMuc', !empty($data['idDanhMuc']) ? $data['idDanhMuc'] : null);
        $this->db->bind(':tenThuoc', $data['tenThuoc']);
        $this->db->bind(':thanhPhan', $data['thanhPhan']);
        $this->db->bind(':hamLuong', $data['hamLuong']);
        $this->db->bind(':congDung', $data['congDung']);
        $this->db->bind(':donViTinh', $data['donViTinh']);
        $this->db->bind(':giaBan', $data['giaBan']);
        $this->db->bind(':yeuCauKeDon', $data['yeuCauKeDon']);
        $this->db->bind(':gioiHanMua', $data['gioiHanMua']);
        $this->db->bind(':trangThai', $data['trangThai']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET idDanhMuc = :idDanhMuc, tenThuoc = :tenThuoc, thanhPhan = :thanhPhan, hamLuong = :hamLuong, 
                congDung = :congDung, donViTinh = :donViTinh, giaBan = :giaBan, yeuCauKeDon = :yeuCauKeDon, 
                gioiHanMua = :gioiHanMua, trangThai = :trangThai WHERE idThuoc = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':idDanhMuc', !empty($data['idDanhMuc']) ? $data['idDanhMuc'] : null);
        $this->db->bind(':tenThuoc', $data['tenThuoc']);
        $this->db->bind(':thanhPhan', $data['thanhPhan']);
        $this->db->bind(':hamLuong', $data['hamLuong']);
        $this->db->bind(':congDung', $data['congDung']);
        $this->db->bind(':donViTinh', $data['donViTinh']);
        $this->db->bind(':giaBan', $data['giaBan']);
        $this->db->bind(':yeuCauKeDon', $data['yeuCauKeDon']);
        $this->db->bind(':gioiHanMua', $data['gioiHanMua']);
        $this->db->bind(':trangThai', $data['trangThai']);

        $res = $this->db->execute();

        return $res;
    }

    // Xóa 1 hình ảnh theo đường dẫn
    public function deleteImageByPath($duongDan)
    {
        $this->db->query("DELETE FROM HinhAnhThuoc WHERE duongDan = :duongDan");
        $this->db->bind(':duongDan', $duongDan);
        return $this->db->execute();
    }

    // Thêm 1 hình ảnh mới vào bảng HinhAnhThuoc
    public function addImage($idThuoc, $duongDan)
    {
        $this->db->query("
            INSERT INTO HinhAnhThuoc (idThuoc, duongDan)
            VALUES (:idThuoc, :duongDan)
        ");
        $this->db->bind(':idThuoc', $idThuoc);
        $this->db->bind(':duongDan', $duongDan);
        return $this->db->execute();
    }

    // Giữ lại saveImage cho tương thích ngược (xóa hết rồi thêm 1 ảnh)
    public function saveImage($idThuoc, $duongDan)
    {
        $this->db->query("DELETE FROM HinhAnhThuoc WHERE idThuoc=:idThuoc");
        $this->db->bind(':idThuoc', $idThuoc);
        $this->db->execute();

        $this->db->query("
            INSERT INTO HinhAnhThuoc (idThuoc, duongDan)
            VALUES (:idThuoc, :duongDan)
        ");
        $this->db->bind(':idThuoc', $idThuoc);
        $this->db->bind(':duongDan', $duongDan);
        return $this->db->execute();
    }

    public function changeStatus($id, $status)
    {
        $sql = "UPDATE {$this->table} SET trangThai = :trangThai WHERE idThuoc = :id";
        $this->db->query($sql);
        $this->db->bind(':trangThai', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
