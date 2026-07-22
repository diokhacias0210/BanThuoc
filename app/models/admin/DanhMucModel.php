<?php
class DanhMucModel extends Model
{
    private $table = "DanhMucThuoc";

    // 1. Lấy toàn bộ danh mục hoặc lọc theo từ khóa tìm kiếm
    public function getAll($search = '')
    {
        if (!empty($search)) {
            $sql = "SELECT * FROM {$this->table} WHERE tenDanhMuc LIKE :search ORDER BY idDanhMuc DESC";
            $this->db->query($sql);
            $this->db->bind(':search', "%$search%");
            return $this->db->resultSet();
        }
        $sql = "SELECT * FROM {$this->table} ORDER BY idDanhMuc DESC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    // 2. Lấy thông tin chi tiết một danh mục
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE idDanhMuc = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // 3. Thêm mới danh mục
    public function create($tenDanhMuc, $moTa)
    {
        $sql = "INSERT INTO {$this->table} (tenDanhMuc, moTa) VALUES (:ten, :moTa)";
        $this->db->query($sql);
        $this->db->bind(':ten', $tenDanhMuc);
        $this->db->bind(':moTa', $moTa);
        return $this->db->execute();
    }

    // 4. Cập nhật danh mục hiện tại
    public function update($id, $tenDanhMuc, $moTa)
    {
        $sql = "UPDATE {$this->table} SET tenDanhMuc = :ten, moTa = :moTa WHERE idDanhMuc = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':ten', $tenDanhMuc);
        $this->db->bind(':moTa', $moTa);
        return $this->db->execute();
    }

    // 5. XÓA DANH MỤC VÀ TỰ ĐỘNG CHUYỂN SẢN PHẨM SANG "CHƯA PHÂN LOẠI"
    public function delete($id)
    {
        try {
            // Khởi động Transaction để bảo vệ dữ liệu toàn vẹn
            $this->db->beginTransaction();

            // Bước 1: Tìm xem có danh mục "Chưa phân loại" hay chưa
            $sqlFind = "SELECT idDanhMuc FROM {$this->table} WHERE tenDanhMuc = :fallbackName LIMIT 1";
            $this->db->query($sqlFind);
            $this->db->bind(':fallbackName', 'Chưa phân loại');
            $fallbackCategory = $this->db->single();

            $fallbackId = null;
            if ($fallbackCategory) {
                $fallbackId = $fallbackCategory['idDanhMuc'];
            } else {
                // Nếu chưa có, tự động tạo mới danh mục "Chưa phân loại"
                $sqlCreate = "INSERT INTO {$this->table} (tenDanhMuc, moTa) VALUES (:fallbackName, :fallbackDesc)";
                $this->db->query($sqlCreate);
                $this->db->bind(':fallbackName', 'Chưa phân loại');
                $this->db->bind(':fallbackDesc', 'Danh mục mặc định của hệ thống dành cho các thuốc chưa được phân nhóm');
                $this->db->execute();
                $fallbackId = $this->db->lastInsertId();
            }

            // Ngăn chặn hành vi tự xóa chính danh mục "Chưa phân loại"
            if ($id == $fallbackId) {
                $this->db->rollback();
                return false;
            }

            // Bước 2: Chuyển toàn bộ thuốc thuộc danh mục sắp xóa ($id) sang danh mục mặc định ($fallbackId)
            $sqlUpdateThuoc = "UPDATE Thuoc SET idDanhMuc = :fallbackId WHERE idDanhMuc = :oldId";
            $this->db->query($sqlUpdateThuoc);
            $this->db->bind(':fallbackId', $fallbackId);
            $this->db->bind(':oldId', $id);
            $this->db->execute();

            // Bước 3: Thực hiện xóa danh mục mục tiêu
            $sqlDeleteCat = "DELETE FROM {$this->table} WHERE idDanhMuc = :id";
            $this->db->query($sqlDeleteCat);
            $this->db->bind(':id', $id);
            $result = $this->db->execute();

            // Xác nhận hoàn tất tiến trình
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            // Nếu phát sinh bất kỳ lỗi nào, hoàn trả lại trạng thái cũ của Database
            $this->db->rollback();
            return false;
        }
    }
}
