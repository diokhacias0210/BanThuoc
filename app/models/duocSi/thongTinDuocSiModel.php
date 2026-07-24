<?php
class thongTinDuocSiModel extends Model
{
    // Dùng các cột CÓ THẬT trong 2 bảng:
    //   - NguoiDung: hoTen, email, soDienThoai, trangThai, vaiTro
    //   - DuocSi: chungChiHanhNghe, trinhDo, noiCap
    // (bản mock có "Dược sĩ trưởng" nhưng cột vaiTro trong DB chỉ có ENUM
    //  KHACH_HANG/DUOC_SI/QUAN_TRI_VIEN - không có phân biệt "trưởng"/"phó",
    //  nên phần label hiển thị được xử lý riêng ở Controller, không lấy từ DB)

    // Lấy thông tin dược sĩ (join NguoiDung + DuocSi)
    public function layThongTin($idNguoiDung)
    {
        $sql = "SELECT nd.idNguoiDung, nd.hoTen, nd.email, nd.soDienThoai, nd.trangThai, nd.vaiTro,
                       ds.chungChiHanhNghe, ds.trinhDo, ds.noiCap
                FROM NguoiDung nd
                JOIN DuocSi ds ON ds.idNguoiDung = nd.idNguoiDung
                WHERE nd.idNguoiDung = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $idNguoiDung);
        return $this->db->single();
    }

    // Cập nhật thông tin tài khoản (bảng NguoiDung)
    public function capNhatTaiKhoan($idNguoiDung, $hoTen, $email, $soDienThoai)
    {
        $sql = "UPDATE NguoiDung SET hoTen = :hoTen, email = :email, soDienThoai = :soDienThoai
                WHERE idNguoiDung = :id";
        $this->db->query($sql);
        $this->db->bind(':hoTen', $hoTen);
        $this->db->bind(':email', $email);
        $this->db->bind(':soDienThoai', $soDienThoai);
        $this->db->bind(':id', $idNguoiDung);
        return $this->db->execute();
    }

    // Cập nhật hồ sơ chuyên môn (bảng DuocSi)
    public function capNhatHoSoChuyenMon($idNguoiDung, $chungChiHanhNghe, $trinhDo, $noiCap)
    {
        $sql = "UPDATE DuocSi SET chungChiHanhNghe = :chungChi, trinhDo = :trinhDo, noiCap = :noiCap
                WHERE idNguoiDung = :id";
        $this->db->query($sql);
        $this->db->bind(':chungChi', $chungChiHanhNghe);
        $this->db->bind(':trinhDo', $trinhDo);
        $this->db->bind(':noiCap', $noiCap);
        $this->db->bind(':id', $idNguoiDung);
        return $this->db->execute();
    }

    // Kiểm tra email hoặc SĐT đã được người khác dùng chưa (2 cột UNIQUE trong NguoiDung)
    // để tránh lỗi CSDL khi lưu trùng
    public function kiemTraTrungEmailHoacSdt($idNguoiDung, $email, $soDienThoai)
    {
        $sql = "SELECT idNguoiDung FROM NguoiDung
                WHERE (email = :email OR soDienThoai = :soDienThoai) AND idNguoiDung != :id";
        $this->db->query($sql);
        $this->db->bind(':email', $email);
        $this->db->bind(':soDienThoai', $soDienThoai);
        $this->db->bind(':id', $idNguoiDung);
        return $this->db->single() ? true : false;
    }
}
