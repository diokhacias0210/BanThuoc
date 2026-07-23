<?php

class Controller
{
    /**
     * Load Model
     * Ưu tiên tìm trong module hiện tại.
     * Nếu không thấy, quét tất cả các module khác.
     */
    protected function model($model)
    {
        // 1. Ưu tiên tìm trong thư mục module hiện tại
        $currentModule = App::getModule();
        $modelPath = APPROOT . "/models/" . $currentModule . "/" . $model . ".php";

        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }

        // 2. Fallback: quét tất cả các module
        $modules = array("khachHang", "admin", "duocSi");

        foreach ($modules as $module) {
            if ($module === $currentModule) {
                continue; // đã kiểm tra ở bước 1
            }

            $modelPath = APPROOT . "/models/" . $module . "/" . $model . ".php";

            if (file_exists($modelPath)) {
                require_once $modelPath;
                return new $model();
            }
        }

        // 3. Không tìm thấy ở bất kỳ module nào
        die("Model <b>{$model}</b> không tồn tại trong bất kỳ module nào.");
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
}