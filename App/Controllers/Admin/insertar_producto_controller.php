<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../Core/conexion.php';
require_once __DIR__ . '/helpers.php';

$con = conectar();

// Mensaje para el usuario
$msg = '';
if (isset($_GET['added'])) {
    $msg = 'Tipo de producto registrado correctamente.';
} elseif (isset($_GET['cel_added'])) {
    $msg = 'Celular creado correctamente.';
} elseif (isset($_GET['deleted'])) {
    $msg = 'Eliminado correctamente.';
}

// Base URL universal para redirección
$baseUrl = '';
if (isset($_SERVER['SCRIPT_NAME'])) {
    $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
    if ($baseUrl === '/' || $baseUrl === '\\') {
        $baseUrl = '';
    }
}

// ========== CRUD OPERACIONES ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Insertar tipo de producto
    if (!empty($_POST['guardar_producto'])) {
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($nombre === '' || $descripcion === '') {
            $error_msg = urlencode('Nombre y descripción son obligatorios');
            header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$error_msg}");
            exit;
        }

        $stmt = mysqli_prepare($con, 'INSERT INTO producto (nombre, descripcion) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $nombre, $descripcion);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: $baseUrl/index.php?r=/admin/insertar-producto&added=1");
            exit;
        } else {
            $err = urlencode(mysqli_error($con));
            header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$err}");
            exit;
        }
    }

    // Insertar celular + imagen
    if (!empty($_POST['guardar'])) {
        $producto      = intval($_POST['producto'] ?? 0);
        $marca         = intval($_POST['marca'] ?? 0);
        $color         = intval($_POST['color'] ?? 0);
        $ram           = intval($_POST['ram'] ?? 0);
        $almacenamiento= intval($_POST['almacenamiento'] ?? 0);
        $precio        = intval($_POST['precio'] ?? 0);
        $cantidad      = intval($_POST['cantidad'] ?? 0);

        if (
            $producto <= 0 || $marca <= 0 || $color <= 0 ||
            $ram <= 0 || $almacenamiento <= 0 || $precio <= 0 || $cantidad <= 0
        ) {
            $error_msg = urlencode('Campos inválidos al insertar celular. La cantidad debe ser mayor a 0.');
            header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$error_msg}");
            exit;
        }

        // Manejo de múltiples imágenes (input name="imagenes[]")
        $uploadedImages = [];
        if (!empty($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxBytes = 3 * 1024 * 1024; // 3MB

            // Carpeta DESTINO: Public/assets/Imagenes
            $uploadDir = dirname(__DIR__, 3) . '/Public/assets/Imagenes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            // Reorganizar arreglo de archivos
            $files = [];
            foreach ($_FILES['imagenes'] as $key => $list) {
                foreach ($list as $index => $value) {
                    $files[$index][$key] = $value;
                }
            }

            foreach ($files as $file) {
                if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error_msg = urlencode('Error en la subida de una de las imágenes.');
                    header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$error_msg}");
                    exit;
                }

                $mime = mime_content_type($file['tmp_name']);
                if (!in_array($mime, $allowed, true)) {
                    $error_msg = urlencode('Tipo de archivo no permitido. Solo imágenes JPG, PNG, GIF, WEBP.');
                    header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$error_msg}");
                    exit;
                }

                if ($file['size'] > $maxBytes) {
                    $error_msg = urlencode('Una de las imágenes supera el tamaño máximo (3MB).');
                    header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$error_msg}");
                    exit;
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                try {
                    $rand = bin2hex(random_bytes(6));
                } catch (\Exception $e) {
                    $rand = uniqid();
                }
                $filename = time() . '_' . $rand . '.' . strtolower($ext);
                $target   = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $uploadedImages[] = 'assets/Imagenes/' . $filename;
                } else {
                    $error_msg = urlencode('Error al mover una de las imágenes subidas.');
                    header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$error_msg}");
                    exit;
                }
            }
        }

        $stmt = mysqli_prepare(
            $con,
            'INSERT INTO celulares (id_producto, id_marcas, id_color, id_ram, id_almacenamiento, id_precio, cantidad_stock) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            $err = urlencode(mysqli_error($con));
            header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$err}");
            exit;
        }
        mysqli_stmt_bind_param($stmt, 'iiiiiii', $producto, $marca, $color, $ram, $almacenamiento, $precio, $cantidad);

        if (mysqli_stmt_execute($stmt)) {
            $idcelulares = mysqli_insert_id($con);

            // Insertar las imágenes subidas (si las hay)
            if (!empty($uploadedImages)) {
                // La primera imagen será la principal (es_principal = 1)
                $first = true;
                $stmtImg = mysqli_prepare($con, 'INSERT INTO imagenes_celulares (id_celulares, es_principal, imagen_url) VALUES (?, ?, ?)');
                foreach ($uploadedImages as $imgUrl) {
                    $es = $first ? 1 : 0;
                    mysqli_stmt_bind_param($stmtImg, 'iis', $idcelulares, $es, $imgUrl);
                    mysqli_stmt_execute($stmtImg);
                    $first = false;
                }
            }

            header("Location: $baseUrl/index.php?r=/admin/insertar-producto&cel_added=1");
            exit;
        } else {
            $err = urlencode(mysqli_error($con));
            header("Location: $baseUrl/index.php?r=/admin/insertar-producto&error=1&msg={$err}");
            exit;
        }
    }

    // Eliminar celular
    if (!empty($_POST['action']) && $_POST['action'] === 'eliminar_celular') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = mysqli_prepare($con, 'DELETE FROM celulares WHERE id_celulares = ?');
            mysqli_stmt_bind_param($stmt, 'i', $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: $baseUrl/index.php?r=/admin/productos&deleted=1");
                exit;
            }
            $err = urlencode(mysqli_error($con));
            header("Location: $baseUrl/index.php?r=/admin/productos&error=1&msg={$err}");
            exit;
        }
    }
}

// ========== CONSULTA LISTA CELULARES PARA EL ADMIN ==========
$sql = "SELECT 
            c.id_celulares, 
            (SELECT imagen_url 
             FROM imagenes_celulares 
             WHERE id_celulares = c.id_celulares 
             ORDER BY es_principal DESC, id_imagen ASC
             LIMIT 1) AS imagen_url,
            p.nombre AS producto, 
            m.marca, 
            col.color, 
            r.ram, 
            a.almacenamiento, 
            pr.precio, 
            c.cantidad_stock AS cantidad
        FROM celulares c
        INNER JOIN producto p ON c.id_producto = p.id_producto
        INNER JOIN marcas m ON c.id_marcas = m.id_marcas
        INNER JOIN color col ON c.id_color = col.id_color
        INNER JOIN ram r ON c.id_ram = r.id_ram
        INNER JOIN almacenamiento a ON c.id_almacenamiento = a.id_almacenamiento
        INNER JOIN precio pr ON c.id_precio = pr.id_precio";

$query_celulares = mysqli_query($con, $sql);
if (!$query_celulares) {
    die('Error en la consulta: ' . mysqli_error($con));
}
$productos = mysqli_fetch_all($query_celulares, MYSQLI_ASSOC);
