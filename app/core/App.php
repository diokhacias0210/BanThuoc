<?php
class App
{
    protected $controller = "TrangChuController"; // Controller mặc định cho khách
    protected $action = "index";                  // Hàm mặc định
    protected $params = [];                       // Tham số mặc định
    protected $subFolder = "KhachHang/";          // Mặc định hướng vào thư mục KhachHang

    public function __construct()
    {
        $url = $this->urlProcess();

        // 1. Kiểm tra nếu URL bắt đầu bằng phân hệ 'admin' hoặc 'duocSi'
        if (isset($url[0]) && in_array($url[0], ['admin', 'duocSi'])) {
            $this->subFolder = $url[0] . '/';

            // Đặt lại Controller mặc định tương ứng cho từng phân hệ
            $this->controller = ($url[0] === 'admin') ? "QuanLyThuocController" : "DuyetDonController";

            unset($url[0]);            // Xóa chữ 'admin' hoặc 'duocSi' khỏi URL
            $url = array_values($url); // Reset lại chỉ số mảng về từ 0
        }

        // 2. Xử lý tìm kiếm Controller trong thư mục con tương ứng
        if (isset($url[0])) {
            $controllerName = ucfirst($url[0]) . "Controller";
            if (file_exists("../app/controllers/" . $this->subFolder . $controllerName . ".php")) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }

        // Nhúng file controller tìm được vào hệ thống
        require_once "../app/controllers/" . $this->subFolder . $this->controller . ".php";
        $this->controller = new $this->controller;

        // 3. Xử lý tìm Action (Hàm)
        $url = array_values($url); // Tiếp tục reset lại chỉ số mảng
        if (isset($url[0])) {
            if (method_exists($this->controller, $url[0])) {
                $this->action = $url[0];
                unset($url[0]);
            }
        }

        // 4. Xử lý các Tham số truyền vào (nếu có)
        $this->params = $url ? array_values($url) : [];

        // Kích hoạt hàm trong controller và truyền tham số
        call_user_func_array([$this->controller, $this->action], $this->params);
    }

    // Hàm bóc tách URL thành mảng
    protected function urlProcess()
    {
        if (isset($_GET["url"])) {
            return explode("/", filter_var(trim($_GET["url"], "/")));
        }
        return [];
    }
}
