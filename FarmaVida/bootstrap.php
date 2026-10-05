<?php
define('FARMAVIDA_ROOT', __DIR__);
spl_autoload_register(function (string $class): void {
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $class)) return;
    foreach (['Core', 'Controllers', 'Models'] as $folder) {
        $file = FARMAVIDA_ROOT . '/app/' . $folder . '/' . $class . '.php';
        if (is_file($file)) { require_once $file; return; }
    }
});
require_once FARMAVIDA_ROOT . '/config/config.php';
