<?php
class thongTinCaNhanModel extends Model
{
    // Model này CHỈ thao tác với các cột CÓ THẬT trong CSDL:
    //   - NguoiDung: hoTen, email, soDienThoai
    //   - DiaChiGiaoHang: idDiaChi, tenNguoiNhan, soDienThoaiNhan, diaChiChiTiet, laMacDinh
    // Các trường không có trong bảng (địa chỉ thường trú, nhãn địa chỉ, ghi chú giao hàng,
    // trạng thái xác thực tài khoản...) KHÔNG được xử lý ở đây.

    // Lấy thông tin cơ bản của người dùng
    public function getThongTinNguoiDung($idNguoiDung)
    {
        $sql = "SELECT idNguoiDung, hoTen, email, soDienThoai FROM NguoiDung WHERE idNguoiDung = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $idNguoiDung);
        return $this->db->single();
    }

    // Cập nhật họ tên + email
    // Không cho cập nhật soDienThoai vì đang dùng làm tên đăng nhập (đã readonly trên View)
    public function capNhatThongTin($idNguoiDung, $hoTen, $email)
    {
        $sql = "UPDATE NguoiDung SET hoTen = :hoTen, email = :email WHERE idNguoiDung = :id";
        $this->db->query($sql);
        $this->db->bind(':hoTen', $hoTen);
        $this->db->bind(':email', $email);
        $this->db->bind(':id', $idNguoiDung);
        return $this->db->execute();
    }

    // Lấy danh sách địa chỉ giao hàng của người dùng, mặc định lên đầu
    public function getDanhSachDiaChi($idNguoiDung)
    {
        $sql = "SELECT idDiaChi, tenNguoiNhan, soDienThoaiNhan, diaChiChiTiet, laMacDinh
                FROM DiaChiGiaoHang
                WHERE idNguoiDung = :id
                ORDER BY laMacDinh DESC, idDiaChi DESC";
        $this->db->query($sql);
        $this->db->bind(':id', $idNguoiDung);
        return $this->db->resultSet();
    }

    // Thêm địa chỉ giao hàng mới
    public function themDiaChi($idNguoiDung, $tenNguoiNhan, $soDienThoaiNhan, $diaChiChiTiet, $laMacDinh)
    {
        if ($laMacDinh) {
            $this->boMacDinhTatCa($idNguoiDung);
        }

        $sql = "INSERT INTO DiaChiGiaoHang (idNguoiDung, tenNguoiNhan, soDienThoaiNhan, diaChiChiTiet, laMacDinh)
                VALUES (:idNguoiDung, :tenNguoiNhan, :soDienThoaiNhan, :diaChiChiTiet, :laMacDinh)";
        $this->db->query($sql);
        $this->db->bind(':idNguoiDung', $idNguoiDung);
        $this->db->bind(':tenNguoiNhan', $tenNguoiNhan);
        $this->db->bind(':soDienThoaiNhan', $soDienThoaiNhan);
        $this->db->bind(':diaChiChiTiet', $diaChiChiTiet);
        $this->db->bind(':laMacDinh', $laMacDinh ? 1 : 0);
        return $this->db->execute();
    }

    // Xoá địa chỉ giao hàng (kèm kiểm tra đúng chủ sở hữu)
    public function xoaDiaChi($idDiaChi, $idNguoiDung)
    {
        $sql = "DELETE FROM DiaChiGiaoHang WHERE idDiaChi = :idDiaChi AND idNguoiDung = :idNguoiDung";
        $this->db->query($sql);
        $this->db->bind(':idDiaChi', $idDiaChi);
        $this->db->bind(':idNguoiDung', $idNguoiDung);
        return $this->db->execute();
    }

    // Đặt 1 địa chỉ làm mặc định, đồng thời bỏ mặc định các địa chỉ còn lại
    public function datMacDinh($idDiaChi, $idNguoiDung)
    {
        $this->boMacDinhTatCa($idNguoiDung);

        $sql = "UPDATE DiaChiGiaoHang SET laMacDinh = 1 WHERE idDiaChi = :idDiaChi AND idNguoiDung = :idNguoiDung";
        $this->db->query($sql);
        $this->db->bind(':idDiaChi', $idDiaChi);
        $this->db->bind(':idNguoiDung', $idNguoiDung);
        return $this->db->execute();
    }

    private function boMacDinhTatCa($idNguoiDung)
    {
        $sql = "UPDATE DiaChiGiaoHang SET laMacDinh = 0 WHERE idNguoiDung = :idNguoiDung";
        $this->db->query($sql);
        $this->db->bind(':idNguoiDung', $idNguoiDung);
        $this->db->execute();
    }
}
