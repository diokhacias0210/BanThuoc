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

    
    // Kiểm tra thông tin đăng nhập bằng số điện thoại
    public function kiemTraDangNhap($soDienThoai) {
        $this->db->query("SELECT * FROM NguoiDung WHERE soDienThoai = :sdt AND trangThai = 1");
        $this->db->bind(':sdt', $soDienThoai);
        return $this->db->single();
    }

    // Đăng ký tài khoản mới cho Khách hàng
    public function dangKyKhachHang($hoTen, $email, $soDienThoai, $matKhau) {
        try {
            $this->db->beginTransaction();

            // 1. Thêm vào bảng NguoiDung với vai trò mặc định KHACH_HANG
            $this->db->query("INSERT INTO NguoiDung (hoTen, email, soDienThoai, matKhau, vaiTro) 
                              VALUES (:hoTen, :email, :sdt, :matKhau, 'KHACH_HANG')");
            $this->db->bind(':hoTen', $hoTen);
            $this->db->bind(':email', $email);
            $this->db->bind(':sdt', $soDienThoai);
            $this->db->bind(':matKhau', $matKhau);
            $this->db->execute();

            $idNguoiDung = $this->db->lastInsertId();

            // 2. Thêm vào bảng KhachHang để đồng bộ dữ liệu quan hệ
            $this->db->query("INSERT INTO KhachHang (idNguoiDung) VALUES (:id)");
            $this->db->bind(':id', $idNguoiDung);
            $this->db->execute();

            // 3. Tạo giỏ hàng trống mặc định cho khách hàng mới
            $this->db->query("INSERT INTO GioHang (idKhachHang) VALUES (:idKhach)");
            $this->db->bind(':idKhach', $idNguoiDung);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
