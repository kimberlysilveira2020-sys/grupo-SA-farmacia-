<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Controllers/ClienteController.php';
(new ClienteController())->index();
