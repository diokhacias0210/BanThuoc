<?php
class DuyetDonModel extends Model
{
    public function getList($search = '', $status = 'all', $page = 1, $pageSize = 8)
    {
        $offset = ($page - 1) * $pageSize;
        $sql = "SELECT dt.idDonThuoc, dt.idKhachHang, dt.ngayGui, dt.ghiChu, dt.trangThai,
                       nd.hoTen AS tenKhachHang
                FROM DonThuoc dt
                LEFT JOIN KhachHang kh ON kh.idNguoiDung = dt.idKhachHang
                LEFT JOIN NguoiDung nd ON nd.idNguoiDung = kh.idNguoiDung
                WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (CAST(dt.idDonThuoc AS CHAR) LIKE :search OR nd.hoTen LIKE :search2)";
        }

        if ($status !== 'all') {
            $sql .= ' AND dt.trangThai = :status';
        }

        $sql .= ' ORDER BY dt.ngayGui DESC LIMIT :offset, :pageSize';

        $this->db->query($sql);

        if (!empty($search)) {
            $searchParam = "%$search%";
            $this->db->bind(':search', $searchParam);
            $this->db->bind(':search2', $searchParam);
        }

        if ($status !== 'all') {
            $this->db->bind(':status', $status);
        }

        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        $this->db->bind(':pageSize', $pageSize, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function countList($search = '', $status = 'all')
    {
        $sql = "SELECT COUNT(*) AS total
                FROM DonThuoc dt
                LEFT JOIN KhachHang kh ON kh.idNguoiDung = dt.idKhachHang
                LEFT JOIN NguoiDung nd ON nd.idNguoiDung = kh.idNguoiDung
                WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (CAST(dt.idDonThuoc AS CHAR) LIKE :search OR nd.hoTen LIKE :search2)";
        }

        if ($status !== 'all') {
            $sql .= ' AND dt.trangThai = :status';
        }

        $this->db->query($sql);

        if (!empty($search)) {
            $searchParam = "%$search%";
            $this->db->bind(':search', $searchParam);
            $this->db->bind(':search2', $searchParam);
        }

        if ($status !== 'all') {
            $this->db->bind(':status', $status);
        }

        $result = $this->db->single();
        return $result ? intval($result['total']) : 0;
    }

    public function getById($id)
    {
        $sql = "SELECT dt.idDonThuoc, dt.idKhachHang, dt.idDuocSi, dt.idDonHang, dt.ngayGui, dt.ghiChu, dt.trangThai,
                       nd.hoTen AS tenKhachHang, nd.email AS emailKhachHang, nd.soDienThoai AS sdtKhachHang
                FROM DonThuoc dt
                LEFT JOIN KhachHang kh ON kh.idNguoiDung = dt.idKhachHang
                LEFT JOIN NguoiDung nd ON nd.idNguoiDung = kh.idNguoiDung
                WHERE dt.idDonThuoc = :id";

        $this->db->query($sql);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $prescription = $this->db->single();

        if ($prescription) {
            $prescription['chiTiet'] = $this->getChiTiet($id);
            $prescription['hinhAnhDonThuoc'] = $this->getImage($id);
        }

        return $prescription;
    }

    public function getChiTiet($idDonThuoc)
    {
        $sql = "SELECT tenThuoc, lieuDung, soLuong
                FROM ChiTietDonThuoc
                WHERE idDonThuoc = :idDonThuoc
                ORDER BY id ASC";

        $this->db->query($sql);
        $this->db->bind(':idDonThuoc', $idDonThuoc, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getImage($idDonThuoc)
    {
        $sql = "SELECT duongDan FROM HinhAnhDonThuoc WHERE idDonThuoc = :idDonThuoc ORDER BY idHinhAnh ASC LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':idDonThuoc', $idDonThuoc, PDO::PARAM_INT);
        $row = $this->db->single();
        return $row ? $row['duongDan'] : null;
    }

    public function updateStatus($id, $status, $idDuocSi = null, $reason = '')
    {
        $sql = "UPDATE DonThuoc SET trangThai = :status";

        if (!empty($idDuocSi)) {
            $sql .= ', idDuocSi = :idDuocSi';
        }

        if ($this->hasColumn('DonThuoc', 'lyDoHuy')) {
            if ($status === 'TU_CHOI' || $status === 'KH_HUY') {
                $sql .= ', lyDoHuy = :reason';
            }
        } elseif (!empty($reason) && ($status === 'TU_CHOI' || $status === 'KH_HUY')) {
            $sql .= ", ghiChu = CONCAT(IFNULL(ghiChu, ''), CASE WHEN IFNULL(ghiChu, '') = '' THEN '' ELSE ' | ' END, :reason)";
        }

        $sql .= ' WHERE idDonThuoc = :id';

        $this->db->query($sql);
        $this->db->bind(':status', $status);

        if (!empty($idDuocSi)) {
            $this->db->bind(':idDuocSi', $idDuocSi, PDO::PARAM_INT);
        }

        if ($this->hasColumn('DonThuoc', 'lyDoHuy') && ($status === 'TU_CHOI' || $status === 'KH_HUY')) {
            $this->db->bind(':reason', $reason);
        } elseif (!empty($reason) && ($status === 'TU_CHOI' || $status === 'KH_HUY')) {
            $this->db->bind(':reason', $reason);
        }

        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function getPendingCount()
    {
        $sql = "SELECT COUNT(*) AS total FROM DonThuoc WHERE trangThai = 'CHO_DUYET'";
        $this->db->query($sql);
        $result = $this->db->single();
        return $result ? intval($result['total']) : 0;
    }

    private function hasColumn($table, $column)
    {
        $sql = "SHOW COLUMNS FROM `{$table}` LIKE :column";
        $this->db->query($sql);
        $this->db->bind(':column', $column);
        return (bool) $this->db->single();
    }
}
