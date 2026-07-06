<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Controllers/VendaController.php';
(new VendaController())->index();
