<?php

class Controller
{
    /**
     * Load Model
     */
    protected function model($model)
    {
        $modelPath = APPROOT . "/models/" . App::getModule() . "/" . $model . ".php";

        if (!file_exists($modelPath)) {
            die("Model <b>{$model}</b> không tồn tại.");
        }

        require_once $modelPath;

        return new $model();
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