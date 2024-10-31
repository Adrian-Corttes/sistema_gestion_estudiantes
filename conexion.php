<?php
// Conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$bd = "sistema_gestion";

$conexion = new mysqli($host, $user, $password, $bd);

if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}
