<?php

class App
{
    /*
    |--------------------------------------------------------------------------
    | Cấu hình mặc định
    |--------------------------------------------------------------------------
    */

    private static $modules = array(
        "khachHang",
        "admin",
        "duocSi"
    );

    private static $module = "khachHang";

    private $controller = "TrangChuController";

    private $method = "index";

    private $params = array();


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $url = $this->getUrl();

        $this->detectModule($url);

        $this->detectController($url);

        $this->loadController();

        $this->detectMethod($url);

        $this->detectParams($url);

        //$this->checkPermission();

        $this->run();
    }


    /*
    |--------------------------------------------------------------------------
    | Lấy URL
    |--------------------------------------------------------------------------
    */

    // Chỉ đọc URL; Không biết Controller; Không biết Database.
    private function getUrl()
    {
        if (isset($_GET['url'])) {

            $url = rtrim($_GET['url'], '/');

            $url = filter_var($url, FILTER_SANITIZE_URL);

            return explode('/', $url);
        }

        return array();
    }


    /*
    |--------------------------------------------------------------------------
    | Xác định Module
    |--------------------------------------------------------------------------
    */

    // Nếu URL có module, thì xác định module; Nếu không, giữ nguyên module mặc định.
    private function detectModule(&$url)
    {
        if (
            isset($url[0]) &&
            in_array($url[0], self::$modules)
        ) {

            self::$module = $url[0];

            unset($url[0]);

            $url = array_values($url);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Xác định Controller
    |--------------------------------------------------------------------------
    */

    // Nếu URL có controller, thì xác định controller; Nếu không, giữ nguyên controller mặc định.(tìm tên controlllers)
    private function detectController(&$url)
    {
        if (!isset($url[0])) {

            return;
        }

        $controller = ucfirst($url[0]) . "Controller";

        $path =
            APPROOT .
            "/controllers/" .
            self::$module .
            "/" .
            $controller .
            ".php";

        if (file_exists($path)) {

            $this->controller = $controller;

            unset($url[0]);

            $url = array_values($url);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Nạp Controller
    |--------------------------------------------------------------------------
    */

    // Nạp file controller và khởi tạo đối tượng controller
    private function loadController()
    {
        $path =
            APPROOT .
            "/controllers/" .
            self::$module .
            "/" .
            $this->controller .
            ".php";

        if (!file_exists($path)) {

            $this->show404("Không tìm thấy Controller.");
        }

        require_once $path;

        $controller = $this->controller;

        $this->controller = new $controller();
    }


    /*
    |--------------------------------------------------------------------------
    | Xác định Method
    |--------------------------------------------------------------------------
    */

    // Nếu URL có method, thì xác định method; Nếu không, giữ nguyên method mặc định.
    private function detectMethod(&$url)
    {
        if (!isset($url[0])) {

            return;
        }

        if (method_exists($this->controller, $url[0])) {

            $this->method = $url[0];

            unset($url[0]);

            $url = array_values($url);

        } else {

            $this->show404("Không tìm thấy Method.");
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Tham số
    |--------------------------------------------------------------------------
    */

    // Xác định tham số truyền vào method (nếu có)
    private function detectParams($url)
    {
        $this->params = $url;
    }


    /*
    |--------------------------------------------------------------------------
    | Chạy Controller
    |--------------------------------------------------------------------------
    */

    // Gọi method của controller với tham số truyền vào (nếu có)
    private function run()
    {
        call_user_func_array(
            array(
                $this->controller,
                $this->method
            ),
            $this->params
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Middleware (Để sau)
    |--------------------------------------------------------------------------
    */

    private function checkPermission()
    {

    }


    /*
    |--------------------------------------------------------------------------
    | Trang lỗi 404
    |--------------------------------------------------------------------------
    */

    // Hiển thị trang lỗi 404 với thông báo tùy chỉnh
    private function show404($message = "")
    {
        http_response_code(404);

        echo "<h2>404 - Not Found</h2>";
        echo "<hr>";
        echo "<p>".$message."</p>";

        exit;
    }

    /**
     * Lấy module hiện tại
     */
    public static function getModule()
    {
        return self::$module;
    }
}