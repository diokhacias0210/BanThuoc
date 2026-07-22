<?php

require_once APPROOT . '/core/Model.php';

class GioHangModel extends Model
{
    /**
     * Lấy giỏ hàng của khách hàng
     */
    public function getByKhachHang($idKhachHang)
    {
        $this->db->query("
            SELECT *
            FROM GioHang
            WHERE idKhachHang = :idKhachHang
            LIMIT 1
        ");

        $this->db->bind(":idKhachHang",$idKhachHang);

        return $this->db->single();
    }

    /**
     * Tạo giỏ hàng
     */
    public function taoGioHang($idKhachHang)
    {
        $this->db->query("
            INSERT INTO GioHang(idKhachHang)
            VALUES(:idKhachHang)
        ");

        $this->db->bind(":idKhachHang",$idKhachHang);

        return $this->db->execute();
    }

    /**
     * Lấy một sản phẩm trong giỏ
     */
    public function getChiTiet($idGioHang,$idThuoc)
    {
        $this->db->query("
            SELECT *
            FROM ChiTietGioHang
            WHERE idGioHang=:idGioHang
            AND idThuoc=:idThuoc
            LIMIT 1
        ");

        $this->db->bind(":idGioHang",$idGioHang);
        $this->db->bind(":idThuoc",$idThuoc);

        return $this->db->single();
    }

    /**
     * Thêm thuốc vào giỏ
     */
    public function themThuoc($idGioHang,$idThuoc,$soLuong,$donGia)
    {
        $this->db->query("
            INSERT INTO ChiTietGioHang
            (
                idGioHang,
                idThuoc,
                soLuong,
                donGia
            )

            VALUES
            (
                :idGioHang,
                :idThuoc,
                :soLuong,
                :donGia
            )
        ");

        $this->db->bind(":idGioHang",$idGioHang);
        $this->db->bind(":idThuoc",$idThuoc);
        $this->db->bind(":soLuong",$soLuong);
        $this->db->bind(":donGia",$donGia);

        return $this->db->execute();
    }

    /**
     * Tăng số lượng
     */
    public function tangSoLuong($id)
    {
        $this->db->query("
            UPDATE ChiTietGioHang

            SET soLuong=soLuong+1

            WHERE id=:id
        ");

        $this->db->bind(":id",$id);

        return $this->db->execute();
    }

    /**
     * Giảm số lượng
     */
    public function giamSoLuong($id)
    {
        $this->db->query("
            UPDATE ChiTietGioHang
            SET soLuong = soLuong - 1
            WHERE id = :id
        ");

        $this->db->bind(":id", $id);

        return $this->db->execute();
    }

    /**
     * Lấy toàn bộ thuốc trong giỏ hàng
     */
    public function getDanhSachThuoc($idGioHang)
    {
        $this->db->query("
            SELECT
                ctgh.id,
                ctgh.idThuoc,
                ctgh.soLuong,
                ctgh.donGia,
                t.tenThuoc,
                t.hamLuong,
                t.donViTinh
            FROM ChiTietGioHang ctgh
            INNER JOIN Thuoc t
                ON ctgh.idThuoc = t.idThuoc
            WHERE ctgh.idGioHang = :idGioHang
        ");

        $this->db->bind(":idGioHang", $idGioHang);

        return $this->db->resultSet();
    }

    /**
     * Lấy chi tiết giỏ hàng theo ID
     */
    public function getChiTietById($id)
    {
        $this->db->query("
            SELECT *
            FROM ChiTietGioHang
            WHERE id = :id
            LIMIT 1
        ");

        $this->db->bind(":id",$id);

        return $this->db->single();
    }

    /**
     * Xóa thuốc khỏi giỏ
     */
    // Người dùng bấm nút Xóa trong giỏ hàng
    public function xoaThuoc($id)
    {
        $this->db->query("
            DELETE FROM ChiTietGioHang
            WHERE id = :id
        ");

        $this->db->bind(":id", $id);

        return $this->db->execute();
    }

    /**
     * Lấy số lượng hiện tại của một thuốc trong giỏ
     */
    public function getSoLuongThuoc($idGioHang,$idThuoc)
    {
        $this->db->query("
            SELECT soLuong

            FROM ChiTietGioHang

            WHERE idGioHang=:idGioHang

            AND idThuoc=:idThuoc

            LIMIT 1
        ");

        $this->db->bind(":idGioHang",$idGioHang);
        $this->db->bind(":idThuoc",$idThuoc);

        return $this->db->single();
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    // Sau khi Đặt hàng thành công sản phẩm trong giỏ hàng sẽ bi xóa
    public function xoaTatCa($idGioHang)
    {
        $this->db->query("
            DELETE

            FROM ChiTietGioHang

            WHERE idGioHang = :idGioHang
        ");

        $this->db->bind(":idGioHang",$idGioHang);

        return $this->db->execute();
    }
}