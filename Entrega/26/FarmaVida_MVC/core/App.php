<?php
class App {
    protected $controller = 'DashboardController';
    protected $method     = 'index';
    protected $params     = [];

    public function __construct() {
        $url = $this->parseUrl();
        $controllerName = isset($url[0]) && $url[0]
            ? ucfirst(strtolower($url[0])) . 'Controller'
            : 'DashboardController';

        $file = __DIR__ . '/../app/Controllers/' . $controllerName . '.php';
        if (file_exists($file)) {
            require_once $file;
            $this->controller = $controllerName;
        } else {
            require_once __DIR__ . '/../app/Controllers/DashboardController.php';
            $this->controller = 'DashboardController';
        }

        $obj = new $this->controller;
        if (isset($url[1]) && method_exists($obj, $url[1])) {
            $this->method = $url[1];
        }
        $this->params = array_slice($url ?? [], 2);
        call_user_func_array([$obj, $this->method], $this->params);
    }

    private function parseUrl(): array {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
