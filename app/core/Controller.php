<?php
class Controller
{
    // Hàm gọi Model
    public function model($model)
    {
        require_once "../app/models/" . $model . ".php";
        return new $model();
    }

    // Hàm gọi View và truyền dữ liệu ($data) ra giao diện
    public function view($view, $data = [])
    {
        require_once "../app/views/" . $view . ".php";
    }
}
