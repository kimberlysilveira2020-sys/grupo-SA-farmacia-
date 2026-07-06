<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Controllers/PedidoController.php';
(new PedidoController())->index();
