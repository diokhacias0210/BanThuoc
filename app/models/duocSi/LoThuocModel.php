<?php
class LoThuocModel extends Model
{
    private $table = "LoThuoc";

    /**
     * Lấy danh sách lô thuốc kèm thông tin thuốc và danh mục, có phân trang
     */
    public function getAll($search = '', $status = 'all', $idDanhMuc = 'all', $page = 1, $pageSize = 8)
    {
        $offset = ($page - 1) * $pageSize;

        $sql = "SELECT l.*, t.tenThuoc, t.donViTinh, d.tenDanhMuc,
                       DATEDIFF(l.hanSuDung, CURDATE()) AS soNgayConLai
                FROM {$this->table} l
                LEFT JOIN Thuoc t ON l.idThuoc = t.idThuoc
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc
                WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (l.maLo LIKE :search OR t.tenThuoc LIKE :search2)";
        }

        if ($idDanhMuc !== 'all') {
            $sql .= " AND t.idDanhMuc = :idDanhMuc";
        }

        if ($status !== 'all') {
            switch ($status) {
                case 'active':
                    $sql .= " AND DATEDIFF(l.hanSuDung, CURDATE()) >= 90";
                    break;
                case 'warn':
                    $sql .= " AND DATEDIFF(l.hanSuDung, CURDATE()) BETWEEN 30 AND 89";
                    break;
                case 'disabled':
                    $sql .= " AND DATEDIFF(l.hanSuDung, CURDATE()) BETWEEN 1 AND 29";
                    break;
                case 'expired':
                    $sql .= " AND DATEDIFF(l.hanSuDung, CURDATE()) < 0";
                    break;
            }
        }

        $sql .= " ORDER BY l.idLo DESC LIMIT :offset, :pageSize";

        $this->db->query($sql);

        if (!empty($search)) {
            $searchParam = "%$search%";
            $this->db->bind(':search', $searchParam);
            $this->db->bind(':search2', $searchParam);
        }
        if ($idDanhMuc !== 'all') {
            $this->db->bind(':idDanhMuc', $idDanhMuc);
        }
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        $this->db->bind(':pageSize', $pageSize, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Đếm tổng số lô thuốc (phân trang)
     */
    public function countAll($search = '', $status = 'all', $idDanhMuc = 'all')
    {
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->table} l
                LEFT JOIN Thuoc t ON l.idThuoc = t.idThuoc
                WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (l.maLo LIKE :search OR t.tenThuoc LIKE :search2)";
        }

        if ($idDanhMuc !== 'all') {
            $sql .= " AND t.idDanhMuc = :idDanhMuc";
        }

        if ($status !== 'all') {
            switch ($status) {
                case 'active':
                    $sql .= " AND DATEDIFF(l.hanSuDung, CURDATE()) >= 90";
                    break;
                case 'warn':
                    $sql .= " AND DATEDIFF(l.hanSuDung, CURDATE()) BETWEEN 30 AND 89";
                    break;
                case 'disabled':
                    $sql .= " AND DATEDIFF(l.hanSuDung, CURDATE()) BETWEEN 1 AND 29";
                    break;
                case 'expired':
                    $sql .= " AND DATEDIFF(l.hanSuDung, CURDATE()) < 0";
                    break;
            }
        }

        $this->db->query($sql);

        if (!empty($search)) {
            $searchParam = "%$search%";
            $this->db->bind(':search', $searchParam);
            $this->db->bind(':search2', $searchParam);
        }
        if ($idDanhMuc !== 'all') {
            $this->db->bind(':idDanhMuc', $idDanhMuc);
        }

        $result = $this->db->single();
        return $result ? $result['total'] : 0;
    }

    /**
     * Lấy thống kê: tổng số, sắp hết hạn, vô hiệu hóa
     */
    public function getStats()
    {
        $sql = "SELECT
                    COUNT(*) AS tongSo,
                    SUM(CASE WHEN DATEDIFF(hanSuDung, CURDATE()) BETWEEN 30 AND 89 THEN 1 ELSE 0 END) AS sapHetHan,
                    SUM(CASE WHEN DATEDIFF(hanSuDung, CURDATE()) BETWEEN 1 AND 29 THEN 1 ELSE 0 END) AS voHieuHoa,
                    SUM(CASE WHEN DATEDIFF(hanSuDung, CURDATE()) < 0 THEN 1 ELSE 0 END) AS daHetHan
                FROM {$this->table}
                WHERE 1=1";
        $this->db->query($sql);
        return $this->db->single();
    }

    /**
     * Lấy chi tiết 1 lô thuốc
     */
    public function getById($id)
    {
        $sql = "SELECT l.*, t.tenThuoc, t.donViTinh, d.tenDanhMuc,
                       DATEDIFF(l.hanSuDung, CURDATE()) AS soNgayConLai
                FROM {$this->table} l
                LEFT JOIN Thuoc t ON l.idThuoc = t.idThuoc
                LEFT JOIN DanhMucThuoc d ON t.idDanhMuc = d.idDanhMuc
                WHERE l.idLo = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Thêm mới lô thuốc
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap)
                VALUES (:idThuoc, :maLo, :ngaySanXuat, :hanSuDung, :soLuongTon, :giaNhap)";
        $this->db->query($sql);
        $this->db->bind(':idThuoc', $data['idThuoc']);
        $this->db->bind(':maLo', $data['maLo']);
        $this->db->bind(':ngaySanXuat', !empty($data['ngaySanXuat']) ? $data['ngaySanXuat'] : null);
        $this->db->bind(':hanSuDung', $data['hanSuDung']);
        $this->db->bind(':soLuongTon', $data['soLuongTon']);
        $this->db->bind(':giaNhap', $data['giaNhap']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Cập nhật lô thuốc
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
                    idThuoc = :idThuoc,
                    maLo = :maLo,
                    ngaySanXuat = :ngaySanXuat,
                    hanSuDung = :hanSuDung,
                    soLuongTon = :soLuongTon,
                    giaNhap = :giaNhap
                WHERE idLo = :id";
        $this->db->query($sql);
        $this->db->bind(':idThuoc', $data['idThuoc']);
        $this->db->bind(':maLo', $data['maLo']);
        $this->db->bind(':ngaySanXuat', !empty($data['ngaySanXuat']) ? $data['ngaySanXuat'] : null);
        $this->db->bind(':hanSuDung', $data['hanSuDung']);
        $this->db->bind(':soLuongTon', $data['soLuongTon']);
        $this->db->bind(':giaNhap', $data['giaNhap']);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Xóa lô thuốc
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE idLo = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
