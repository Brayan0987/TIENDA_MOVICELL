<?php
include 'conexion.php';
$con = conectar();

function obtenerOpciones($tabla, $campo_id, $campo_nombre) {
    global $con;
    $sql = "SELECT $campo_id, $campo_nombre FROM $tabla";
    $result = mysqli_query($con, $sql);
    $opciones = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $opciones[] = $row;
    }
    return $opciones;
}

$tipo = $_GET['tipo'] ?? '';
$opciones = [];

switch ($tipo) {
    case 'marcas':
        $opciones = obtenerOpciones('marcas', 'id_marcas', 'nombre');
        break;
    case 'precios':
        $opciones = obtenerOpciones('precios', 'id_precio', 'valor');
        break;
    case 'colores':
        $opciones = obtenerOpciones('colores', 'id_color', 'nombre');
        break;
    case 'ram':
        $opciones = obtenerOpciones('ram', 'id_ram', 'capacidad');
        break;
    case 'almacenamiento':
        $opciones = obtenerOpciones('almacenamiento', 'id_almacenamiento', 'capacidad');
        break;
}

header('Content-Type: application/json');
echo json_encode($opciones);
