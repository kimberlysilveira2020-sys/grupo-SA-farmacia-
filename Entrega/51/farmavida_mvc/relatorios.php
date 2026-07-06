<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Controllers/RelatorioController.php';
(new RelatorioController())->index();
