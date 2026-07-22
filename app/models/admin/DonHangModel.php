<?php

class DonHangModel extends Model
{
    public function taoDonHang($idKhachHang,$tongTien)
    {
        $this->db->query("
            INSERT INTO DonHang
            (
                idKhachHang,
                tongTien
            )

            VALUES
            (
                :idKhachHang,
                :tongTien
            )
        ");

        $this->db->bind(":idKhachHang",$idKhachHang);
        $this->db->bind(":tongTien",$tongTien);

        return $this->db->execute();
    }

    public function getLastId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * Thêm chi tiết đơn hàng
     */
    public function themChiTiet($idDonHang, $idThuoc, $soLuong, $donGia)
    {
        $this->db->query("
            INSERT INTO ChiTietDonHang
            (
                idDonHang,
                idThuoc,
                soLuong,
                donGia
            )

            VALUES
            (
                :idDonHang,
                :idThuoc,
                :soLuong,
                :donGia
            )
        ");

        $this->db->bind(":idDonHang", $idDonHang);
        $this->db->bind(":idThuoc", $idThuoc);
        $this->db->bind(":soLuong", $soLuong);
        $this->db->bind(":donGia", $donGia);

        return $this->db->execute();
    }

    /**
     * Danh sách đơn hàng
     */
    public function getByKhachHang($idKhachHang)
    {
        $this->db->query("
            SELECT *

            FROM DonHang

            WHERE idKhachHang = :idKhachHang

            ORDER BY idDonHang DESC
        ");

        $this->db->bind(":idKhachHang",$idKhachHang);

        return $this->db->resultSet();
    }

    /**
     * Lấy danh sách đơn hàng của khách hàng
     */
    public function getDanhSachDonHang($idKhachHang)
    {
        $this->db->query("
            SELECT *

            FROM DonHang

            WHERE idKhachHang = :idKhachHang

            ORDER BY ngayDat DESC
        ");

        $this->db->bind(":idKhachHang",$idKhachHang);

        return $this->db->resultSet();
    }

    /**
     * Chi tiết đơn hàng
     */
    public function getChiTietDonHang($idDonHang)
    {
        $this->db->query("
            SELECT

                c.*,

                t.tenThuoc

            FROM ChiTietDonHang c

            INNER JOIN Thuoc t

            ON c.idThuoc=t.idThuoc

            WHERE c.idDonHang=:idDonHang
        ");

        $this->db->bind(":idDonHang",$idDonHang);

        return $this->db->resultSet();
    }

}