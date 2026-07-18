<?php
class ThuocModel extends Model
{
    private $table = "Thuoc";

    // 1. Lấy danh sách thuốc kèm bộ lọc và tính tổng số lượng tồn kho khả dụng từ LoThuoc
    public function getAll($search = '', $idDanhMuc = 'all', $phanLoai = 'all', $trangThai = 'all')
    {
        $sql = "SELECT t.*, d.tenDanhMuc, COALESCE(SUM(l.soLuongTon), 0) AS tongTon 
                FROM {$this->table} t 
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc 
                LEFT JOIN LoThuoc l ON t.idThuoc = l.idThuoc AND l.hanSuDung >= CURDATE()
                WHERE 1=1";

        if (!empty($search)) $sql .= " AND t.tenThuoc LIKE :search";
        if ($idDanhMuc !== 'all') $sql .= " AND t.idDanhMuc = :idDanhMuc";
        if ($phanLoai !== 'all') $sql .= " AND t.yeuCauKeDon = :phanLoai";
        if ($trangThai !== 'all') $sql .= " AND t.trangThai = :trangThai";

        $sql .= " GROUP BY t.idThuoc ORDER BY t.idThuoc DESC";
        $this->db->query($sql);

        if (!empty($search)) $this->db->bind(':search', "%$search%");
        if ($idDanhMuc !== 'all') $this->db->bind(':idDanhMuc', $idDanhMuc);
        if ($phanLoai !== 'all') $this->db->bind(':phanLoai', $phanLoai);
        if ($trangThai !== 'all') $this->db->bind(':trangThai', $trangThai === 'active' ? 1 : 0);

        return $this->db->resultSet();
    }

    public function getById($id)
    {
        $sql = "SELECT t.*, d.tenDanhMuc FROM {$this->table} t 
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc WHERE t.idThuoc = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // 2. Lấy danh sách lô thuốc của sản phẩm và thông tin dược sĩ nhập lô
    public function getLotsByThuocId($idThuoc)
    {
        $sql = "SELECT l.*, n.hoTen, n.email FROM LoThuoc l 
                LEFT JOIN NguoiDung n ON l.idDuocSi = n.idNguoiDung 
                WHERE l.idThuoc = :idThuoc ORDER BY l.hanSuDung ASC";
        $this->db->query($sql);
        $this->db->bind(':idThuoc', $idThuoc);
        return $this->db->resultSet();
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, hinhAnh, yeuCauKeDon, gioiHanMua, trangThai) 
                VALUES (:idDanhMuc, :tenThuoc, :thanhPhan, :hamLuong, :congDung, :donViTinh, :giaBan, :hinhAnh, :yeuCauKeDon, :gioiHanMua, :trangThai)";
        $this->db->query($sql);
        $this->db->bind(':idDanhMuc', !empty($data['idDanhMuc']) ? $data['idDanhMuc'] : null);
        $this->db->bind(':tenThuoc', $data['tenThuoc']);
        $this->db->bind(':thanhPhan', $data['thanhPhan']);
        $this->db->bind(':hamLuong', $data['hamLuong']);
        $this->db->bind(':congDung', $data['congDung']);
        $this->db->bind(':donViTinh', $data['donViTinh']);
        $this->db->bind(':giaBan', $data['giaBan']);
        $this->db->bind(':hinhAnh', $data['hinhAnh']);
        $this->db->bind(':yeuCauKeDon', $data['yeuCauKeDon']);
        $this->db->bind(':gioiHanMua', $data['gioiHanMua']);
        $this->db->bind(':trangThai', $data['trangThai']);
        return $this->db->execute();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET idDanhMuc = :idDanhMuc, tenThuoc = :tenThuoc, thanhPhan = :thanhPhan, hamLuong = :hamLuong, 
                congDung = :congDung, donViTinh = :donViTinh, giaBan = :giaBan, hinhAnh = :hinhAnh, yeuCauKeDon = :yeuCauKeDon, 
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
        $this->db->bind(':hinhAnh', $data['hinhAnh']);
        $this->db->bind(':yeuCauKeDon', $data['yeuCauKeDon']);
        $this->db->bind(':gioiHanMua', $data['gioiHanMua']);
        $this->db->bind(':trangThai', $data['trangThai']);
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
