<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../Core/conexion.php';

$con = conectar();

// Manejo de mensajes para el usuario
$msg = '';
if (isset($_GET['deleted'])) {
    $msg = 'Eliminado correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eliminar celular
    if (!empty($_POST['action']) && $_POST['action'] === 'eliminar_celular') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            // En BD: id_celulares
            $stmt = mysqli_prepare($con, "DELETE FROM celulares WHERE id_celulares = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: /TIENDA_MOVICELL/public/index.php?r=/admin/productos&deleted=1');
                exit;
            } else {
                $err = urlencode(mysqli_error($con));
                header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/productos&error=1&msg={$err}");
                exit;
            }
        }
    }
} else {
    // --- ACCIONES GET ---

    // Obtener todos los celulares para la tabla
    $sql = "
        SELECT 
            c.id_celulares,
            (
                SELECT imagen_url
                FROM imagenes_celulares
                WHERE id_celulares = c.id_celulares
                ORDER BY es_principal DESC, id_imagen ASC
                LIMIT 1
            ) AS imagen_url,
            p.nombre           AS producto,
            m.marca            AS marca,
            col.color          AS color,
            r.ram              AS ram,
            a.almacenamiento   AS almacenamiento,
            pr.precio          AS precio,
            c.cantidad_stock   AS cantidad
        FROM celulares c
        INNER JOIN producto       p  ON c.id_producto       = p.id_producto
        INNER JOIN marcas         m  ON c.id_marcas         = m.id_marcas
        INNER JOIN color          col ON c.id_color         = col.id_color
        INNER JOIN ram            r  ON c.id_ram            = r.id_ram
        INNER JOIN almacenamiento a  ON c.id_almacenamiento = a.id_almacenamiento
        INNER JOIN precio         pr ON c.id_precio         = pr.id_precio
    ";

    $query_celulares = mysqli_query($con, $sql);
    if (!$query_celulares) {
        die('Error en la consulta: ' . mysqli_error($con));
    }
    $productos = mysqli_fetch_all($query_celulares, MYSQLI_ASSOC);
}
?>
