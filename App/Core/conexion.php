<?php
function conectar() {
    $host = "localhost";
    $user = "root"; // cambia si tienes otro usuario
    $pass = "";     // cambia si tienes contraseña
    $db   = "movi_cell";

    $con = mysqli_connect($host, $user, $pass, $db);

    if (!$con) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $con;
}
?>
