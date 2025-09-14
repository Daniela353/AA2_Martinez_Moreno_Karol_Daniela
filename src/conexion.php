<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "aa2_martinez_moreno_karol_daniela";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// No imprimir nada aquí
?>
