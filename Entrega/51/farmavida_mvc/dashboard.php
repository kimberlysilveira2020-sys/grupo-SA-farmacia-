<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';
(new DashboardController())->index();
