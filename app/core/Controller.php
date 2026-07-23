<?php

class Controller
{
    /**
     * Load Model (Đã tối ưu quét tự động các thư mục & tương thích PHP cũ)
     */
    protected function model($model)
    {
        // 1. Nếu truyền trực tiếp đường dẫn (VD: $this->model('admin/TaiKhoanModel'))
        $directPath = APPROOT . "/models/" . $model . ".php";
        if (file_exists($directPath)) {
            require_once $directPath;
            $className = basename($model);
            return new $className();
        }

        // 2. Thử tìm trong thư mục module hiện tại (VD: models/khachHang/)
        $currentModule = App::getModule();
        $modulePath = APPROOT . "/models/" . $currentModule . "/" . $model . ".php";
        if (file_exists($modulePath)) {
            require_once $modulePath;
            return new $model();
        }

        // 3. Nếu không tìm thấy, tự động tìm quét qua các thư mục module khác (admin, duocSi, khachHang)
        $subFolders = array("admin", "duocSi", "khachHang");
        foreach ($subFolders as $folder) {
            $fallbackPath = APPROOT . "/models/" . $folder . "/" . $model . ".php";
            if (file_exists($fallbackPath)) {
                require_once $fallbackPath;
                return new $model();
            }
        }

        // Báo lỗi nếu tìm khắp dự án vẫn không thấy
        die("Lỗi hệ thống: Model <b>{$model}</b> không tồn tại.");
    }

    /**
     * Load View
     */
    public function view($view, $data = array())
    {
        if (file_exists(APPROOT . "/views/" . $view . ".php")) {

            extract($data);

            require_once APPROOT . "/views/" . $view . ".php";
        } else {

            die("Không tìm thấy View.");
        }
    }

    /**
     * Redirect
     */
    protected function redirect($url)
    {
        header("Location: " . URLROOT . "/" . $url);
        exit;
    }

    /**
     * Chuẩn hóa đường dẫn hình ảnh (Đã chuyển sang dùng isset() không bị lỗi ??)
     */
    protected function xuLyDuongDanAnh($path)
    {
        if (empty($path)) {
            return 'https://placehold.co/400x400/e8f5ee/2d7a4f?text=💊';
        }
        if (strpos($path, 'http') === 0) {
            return $path;
        }
        $clean = str_replace('\\', '/', $path);
        if (strpos($clean, 'images/') === 0) {
            $clean = 'assets/' . $clean;
        }
        if (strpos($clean, '/') === 0) {
            $clean = substr($clean, 1);
        }
        if (strpos($clean, 'public/') === 0) {
            $clean = substr($clean, 7);
        }
        return URLROOT . '/' . $clean;
    }
}
