<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
(new AuthController())->logout();
