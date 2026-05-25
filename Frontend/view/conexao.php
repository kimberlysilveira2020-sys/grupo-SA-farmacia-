<?php
$host   = "localhost";
$banco  = "farmaweb";
$usuario = "root";      
$senha  = "";           

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>