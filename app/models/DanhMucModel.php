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
            return $this->db->resultSet(); // Sử dụng resultSet() để lấy mảng dữ liệu
        }
        $sql = "SELECT * FROM {$this->table} ORDER BY idDanhMuc DESC";
        $this->db->query($sql);
        return $this->db->resultSet(); // Sử dụng resultSet() để lấy mảng dữ liệu
    }

    // 2. Lấy thông tin chi tiết một danh mục
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE idDanhMuc = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->single(); // Sử dụng single() để lấy 1 dòng duy nhất
    }

    // 3. Thêm mới danh mục
    public function create($tenDanhMuc, $moTa)
    {
        $sql = "INSERT INTO {$this->table} (tenDanhMuc, moTa) VALUES (:ten, :moTa)";
        $this->db->query($sql);
        $this->db->bind(':ten', $tenDanhMuc);
        $this->db->bind(':moTa', $moTa);
        return $this->db->execute(); // Sử dụng execute() để thực thi câu lệnh INSERT
    }

    // 4. Cập nhật danh mục hiện tại
    public function update($id, $tenDanhMuc, $moTa)
    {
        $sql = "UPDATE {$this->table} SET tenDanhMuc = :ten, moTa = :moTa WHERE idDanhMuc = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':ten', $tenDanhMuc);
        $this->db->bind(':moTa', $moTa);
        return $this->db->execute(); // Sử dụng execute() để thực thi câu lệnh UPDATE
    }

    // 5. Xóa danh mục
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE idDanhMuc = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->execute(); // Sử dụng execute() để thực thi câu lệnh DELETE
    }
}
