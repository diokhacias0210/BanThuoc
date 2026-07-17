<?php
class TaiKhoanModel extends Model
{
    private $table = "NguoiDung";

    // 1. Lấy danh sách tài khoản (Tuyệt đối không lấy trường matKhau để bảo mật)
    public function getAll($search = '', $vaiTro = 'all', $trangThai = 'all')
    {
        $sql = "SELECT idNguoiDung, hoTen, email, soDienThoai, trangThai, vaiTro FROM {$this->table} WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (hoTen LIKE :search OR email LIKE :search OR soDienThoai LIKE :search)";
        }
        if ($vaiTro !== 'all') {
            $sql .= " AND vaiTro = :vaiTro";
        }
        if ($trangThai !== 'all') {
            $sql .= " AND trangThai = :trangThai";
        }

        $sql .= " ORDER BY idNguoiDung DESC";
        $this->db->query($sql);

        if (!empty($search)) $this->db->bind(':search', "%$search%");
        if ($vaiTro !== 'all') $this->db->bind(':vaiTro', $vaiTro);
        if ($trangThai !== 'all') $this->db->bind(':trangThai', $trangThai === 'active' ? 1 : 0);

        return $this->db->resultSet();
    }

    // 2. Lấy chi tiết tài khoản kèm thông tin mở rộng của vai trò tương ứng (Không lấy mật khẩu)
    public function getDetailById($id)
    {
        // Lấy thông tin cơ bản trước
        $sqlBase = "SELECT idNguoiDung, hoTen, email, soDienThoai, trangThai, vaiTro FROM {$this->table} WHERE idNguoiDung = :id";
        $this->db->query($sqlBase);
        $this->db->bind(':id', $id);
        $user = $this->db->single();

        if (!$user) return null;

        // Tùy theo vai trò hiện tại, lấy thêm thông tin ở các bảng liên kết đặc thù
        if ($user['vaiTro'] === 'KHACH_HANG') {
            $sqlExt = "SELECT diemTichLuy, diaChiGiaoHang, ngaySinh FROM KhachHang WHERE idNguoiDung = :id";
            $this->db->query($sqlExt);
            $this->db->bind(':id', $id);
            $ext = $this->db->single();
            if ($ext) $user = array_merge($user, $ext);
        } elseif ($user['vaiTro'] === 'DUOC_SI') {
            $sqlExt = "SELECT chungChiHanhNghe, noiCap, trinhDo FROM DuocSi WHERE idNguoiDung = :id";
            $this->db->query($sqlExt);
            $this->db->bind(':id', $id);
            $ext = $this->db->single();
            if ($ext) $user = array_merge($user, $ext);
        }

        return $user;
    }

    // 3. Thay đổi vai trò (Phân quyền độc nhất)
    public function updateRole($id, $newRole)
    {
        try {
            $this->db->beginTransaction();

            // Cập nhật vai trò chính trong bảng NguoiDung
            $sql = "UPDATE {$this->table} SET vaiTro = :vaiTro WHERE idNguoiDung = :id";
            $this->db->query($sql);
            $this->db->bind(':vaiTro', $newRole);
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Đảm bảo cấu trúc dữ liệu bảng con tương ứng tồn tại để tránh lỗi hệ thống
            if ($newRole === 'KHACH_HANG') {
                $this->db->query("INSERT IGNORE INTO KhachHang (idNguoiDung) VALUES (:id)");
                $this->db->bind(':id', $id);
                $this->db->execute();
            } elseif ($newRole === 'DUOC_SI') {
                $this->db->query("INSERT IGNORE INTO DuocSi (idNguoiDung, chungChiHanhNghe, noiCap, trinhDo) VALUES (:id, 'Chờ bổ sung', 'Chưa cập nhật', 'Chưa cập nhật')");
                $this->db->bind(':id', $id);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    // 4. Khóa hoặc Mở khóa tài khoản (Thay thế hoàn toàn chức năng Xóa)
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE {$this->table} SET trangThai = :trangThai WHERE idNguoiDung = :id";
        $this->db->query($sql);
        $this->db->bind(':trangThai', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
