<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Controllers/ProdutoController.php';
(new ProdutoController())->index();
