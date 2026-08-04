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
        // FIX: trước đây join nhầm bảng DuocSi qua cột dt.idNguoiDung (không tồn
        // tại trong bảng DonThuoc) -> lỗi "Unknown column 'dt.idNguoiDung'".
        // Đúng ra phải join KhachHang qua dt.idKhachHang, giống getList()/countList().
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
        // Bảng DonThuoc không có cột lyDoHuy (chỉ có ghiChu) nên luôn nối lý do
        // từ chối/huỷ vào ghiChu. QUAN TRỌNG: không gọi thêm bất kỳ query() nào
        // khác của $this->db trước khi bind/execute câu UPDATE chính bên dưới,
        // vì điều đó từng làm rò rỉ tham số bind của câu query trước sang câu
        // UPDATE này (gây lỗi HY093: number of bound variables does not match
        // number of tokens) khi trước đây có gọi hasColumn() ở giữa.
        $capNhatLyDo = !empty($reason) && ($status === 'TU_CHOI' || $status === 'KH_HUY');

        $sql = "UPDATE DonThuoc SET trangThai = :status";

        if (!empty($idDuocSi)) {
            $sql .= ', idDuocSi = :idDuocSi';
        }

        if ($capNhatLyDo) {
            $sql .= ", ghiChu = CONCAT(IFNULL(ghiChu, ''), CASE WHEN IFNULL(ghiChu, '') = '' THEN '' ELSE ' | ' END, :reason)";
        }

        $sql .= ' WHERE idDonThuoc = :id';

        $this->db->query($sql);
        $this->db->bind(':status', $status);

        if (!empty($idDuocSi)) {
            $this->db->bind(':idDuocSi', $idDuocSi, PDO::PARAM_INT);
        }

        if ($capNhatLyDo) {
            $this->db->bind(':reason', $reason);
        }

        $this->db->bind(':id', $id, PDO::PARAM_INT);

        $ok = $this->db->execute();

        // Đồng bộ trạng thái bên giỏ hàng cho các item gắn với đơn thuốc này.
        // Chạy SAU KHI câu UPDATE chính đã execute() xong hoàn toàn (không xen
        // giữa lúc đang bind/execute câu trên) để tránh lỗi rò rỉ tham số bind
        // đã ghi chú ở trên.
        if ($ok) {
            if ($status === 'DA_DUYET') {
                // Mở khóa item trong giỏ hàng để khách có thể mua/thanh toán
                $sqlSyncGio = "UPDATE ChiTietGioHang SET trangThaiThaoTac = 'CHO_PHEP' WHERE idDonThuoc = :idDonThuoc";
                $this->db->query($sqlSyncGio);
                $this->db->bind(':idDonThuoc', $id, PDO::PARAM_INT);
                $this->db->execute();
            } elseif ($status === 'TU_CHOI' || $status === 'KH_HUY') {
                // Không xóa item nữa: chuyển sang trạng thái TU_CHOI để khách vẫn thấy
                // item trong giỏ hàng kèm badge "Đã từ chối" + lý do, thay vì biến mất
                // âm thầm khiến khách không hiểu vì sao thuốc không còn trong giỏ.
                $sqlSyncGio = "UPDATE ChiTietGioHang SET trangThaiThaoTac = 'TU_CHOI' WHERE idDonThuoc = :idDonThuoc";
                $this->db->query($sqlSyncGio);
                $this->db->bind(':idDonThuoc', $id, PDO::PARAM_INT);
                $this->db->execute();
            }
        }

        return $ok;
    }

    public function getPendingCount()
    {
        $sql = "SELECT COUNT(*) AS total FROM DonThuoc WHERE trangThai = 'CHO_DUYET'";
        $this->db->query($sql);
        $result = $this->db->single();
        return $result ? intval($result['total']) : 0;
    }
}



