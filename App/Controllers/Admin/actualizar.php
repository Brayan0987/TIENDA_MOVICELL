<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Core/conexion.php';  // ¡AQUÍ!
$con = conectar();

if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}


// Asegurar tabla para varias imágenes
$createSql = "
CREATE TABLE IF NOT EXISTS imagenes_celulares (
  id_imagen BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_celulares BIGINT NOT NULL,
  imagen_url VARCHAR(255) NOT NULL,
  FOREIGN KEY (id_celulares) REFERENCES celulares(id_celulares) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
mysqli_query($con, $createSql);

// Helper para validar y mover una imagen (retorna ruta relativa o false)
function handle_upload_file($file) {
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) return false;
    $maxBytes = 3 * 1024 * 1024; // 3MB
    if ($file['size'] > $maxBytes) return false;
    $uploadDir = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    try { $rand = bin2hex(random_bytes(6)); } catch (Exception $e) { $rand = uniqid(); }
    $filename = time() . '_' . $rand . '.' . strtolower($ext);
    $target = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'assets/uploads/' . $filename;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos principales
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $id_precio = isset($_POST['id_precio']) ? intval($_POST['id_precio']) : 0;
    $id_producto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
    $id_marcas = isset($_POST['id_marcas']) ? intval($_POST['id_marcas']) : 0;
    $id_color = isset($_POST['id_color']) ? intval($_POST['id_color']) : 0;
    $id_ram = isset($_POST['id_ram']) ? intval($_POST['id_ram']) : 0;
    $id_almacenamiento = isset($_POST['id_almacenamiento']) ? intval($_POST['id_almacenamiento']) : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
    $precio_raw = isset($_POST['precio']) ? $_POST['precio'] : '0';
    $precio = floatval(str_replace(',', '.', $precio_raw));

    if ($id <= 0 || $id_precio <= 0) {
        header("Location: ../view/productos.php?error=invalid_id");
        exit;
    }

    // Obtener imágenes actuales (id + ruta) para mostrar / borrar
    $existingImgs = [];
    $stmtImgs = mysqli_prepare($con, "SELECT id_imagen, imagen_url FROM imagenes_celulares WHERE id_celulares = ?");
    if ($stmtImgs) {
        mysqli_stmt_bind_param($stmtImgs, 'i', $id);
        mysqli_stmt_execute($stmtImgs);
        $resImgs = mysqli_stmt_get_result($stmtImgs);
        while ($r = mysqli_fetch_assoc($resImgs)) {
            $existingImgs[] = $r;
        }
        mysqli_stmt_close($stmtImgs);
    }

    // Manejar intentos de borrado: ids de imagen recibidos en delete_image[]
    $toDeleteIds = [];
    if (!empty($_POST['delete_image']) && is_array($_POST['delete_image'])) {
        foreach ($_POST['delete_image'] as $delId) {
            $toDeleteIds[] = intval($delId);
        }
    }

    // Manejar nuevas subidas (imagenes[] campo multiple)
    $newImagePaths = []; // rutas relativas para insertar en DB
    $movedFilesForCleanup = []; // rutas absolutas por si hay rollback
    if (!empty($_FILES['imagenes'])) {
        // reformat $_FILES para iterar
        $filesArr = [];
        foreach ($_FILES['imagenes'] as $key => $list) {
            foreach ($list as $i => $val) {
                $filesArr[$i][$key] = $val;
            }
        }
        foreach ($filesArr as $file) {
            // si no se seleccionó archivo salta
            if ($file['error'] === UPLOAD_ERR_NO_FILE) continue;
            $ruta = handle_upload_file($file);
            if ($ruta === false) {
                // limpiar los movidos antes de abortar
                foreach ($movedFilesForCleanup as $f) if (file_exists($f)) @unlink($f);
                $msg = urlencode('Error en subida de alguna imagen (tipo/tamaño).');
                header("Location: ../controllers/actualizar.php?id={$id}&error=img_upload&msg={$msg}");
                exit;
            }
            $newImagePaths[] = $ruta;
            $movedFilesForCleanup[] = __DIR__ . '/../' . $ruta;
        }
    }

    // Iniciar transacción DB
    mysqli_autocommit($con, false);
    $ok = true;

    // 1) actualizar precio
    $stmt1 = mysqli_prepare($con, "UPDATE precio SET precio = ? WHERE id_precio = ?");
    if ($stmt1) {
        mysqli_stmt_bind_param($stmt1, 'di', $precio, $id_precio);
        if (!mysqli_stmt_execute($stmt1)) $ok = false;
        mysqli_stmt_close($stmt1);
    } else $ok = false;

    // 2) actualizar celulares (con o sin imagen_url principal)
    $stmt2 = mysqli_prepare($con, "UPDATE celulares SET id_producto = ?, id_marcas = ?, id_color = ?, id_ram = ?, id_almacenamiento = ?, cantidad_stock = ? WHERE id_celulares = ?");
    if ($stmt2) {
        mysqli_stmt_bind_param($stmt2, 'iiiiiii', $id_producto, $id_marcas, $id_color, $id_ram, $id_almacenamiento, $cantidad, $id);
        if (!mysqli_stmt_execute($stmt2)) $ok = false;
        mysqli_stmt_close($stmt2);
    } else $ok = false;

    // 3) Borrar imágenes marcadas (si las ids existen)
    if ($ok && count($toDeleteIds) > 0) {
        // preparar declaración delete con placeholders
        $placeholders = implode(',', array_fill(0, count($toDeleteIds), '?'));
        $types = str_repeat('i', count($toDeleteIds));
        $sqlDel = "SELECT imagen_url FROM imagenes_celulares WHERE id_imagen IN ($placeholders) AND id_celulares = ?";
        $stmtSel = mysqli_prepare($con, $sqlDel);
        if ($stmtSel) {
            // bind dynamic params
            $bind_names[] = $types . 'i';
            foreach ($toDeleteIds as $k => $v) $bind_names[] = &$toDeleteIds[$k];
            $bind_names[] = &$id;
            // call_user_func_array requires references
            mysqli_stmt_bind_param($stmtSel, str_repeat('i', count($toDeleteIds)+1), ...array_merge($toDeleteIds, [$id]));
            mysqli_stmt_execute($stmtSel);
            $resSel = mysqli_stmt_get_result($stmtSel);
            $deleteFilesAfterCommit = [];
            while ($r = mysqli_fetch_assoc($resSel)) {
                $deleteFilesAfterCommit[] = __DIR__ . '/../' . $r['imagen_url'];
            }
            mysqli_stmt_close($stmtSel);

            // ahora borrar filas
            $sqlDelete = "DELETE FROM imagenes_celulares WHERE id_imagen IN ($placeholders) AND id_celulares = ?";
            $stmtDel = mysqli_prepare($con, $sqlDelete);
            if ($stmtDel) {
                mysqli_stmt_bind_param($stmtDel, str_repeat('i', count($toDeleteIds)+1), ...array_merge($toDeleteIds, [$id]));
                if (!mysqli_stmt_execute($stmtDel)) $ok = false;
                mysqli_stmt_close($stmtDel);
            } else $ok = false;
        } else $ok = false;
    } else {
        $deleteFilesAfterCommit = [];
    }

    // 4) Insertar nuevas imágenes
    if ($ok && count($newImagePaths) > 0) {
        $stmtIns = mysqli_prepare($con, "INSERT INTO imagenes_celulares (id_celulares, imagen_url) VALUES (?, ?)");
        if ($stmtIns) {
            foreach ($newImagePaths as $ruta) {
                mysqli_stmt_bind_param($stmtIns, 'is', $id, $ruta);
                if (!mysqli_stmt_execute($stmtIns)) { $ok = false; break; }
            }
            mysqli_stmt_close($stmtIns);
        } else $ok = false;
    }

    if ($ok) {
    mysqli_commit($con);
    mysqli_autocommit($con, true);
    // borrar archivos marcados (si existían) ahora que commit fue exitoso
    if (!empty($deleteFilesAfterCommit)) {
        foreach ($deleteFilesAfterCommit as $f) if (file_exists($f)) @unlink($f);
    }
    // REDIRECT NUEVO
    header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/productos&success=updated");
    exit;
} else {
    // rollback y limpiar las subidas nuevas
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    foreach ($movedFilesForCleanup as $f) if (file_exists($f)) @unlink($f);
    // REDIRECT NUEVO
    header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/productos&error=update_failed");
    exit;
}
}

// GET: mostrar formulario edición con multiples imágenes y opciones de borrar
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id <= 0) { header("Location: ../view/productos.php"); exit; }

    $sql = "SELECT c.id_celulares, c.id_precio, c.id_producto, c.id_marcas, c.id_color, c.id_ram, c.id_almacenamiento,
                   p.nombre AS producto, m.marca, col.color, r.ram, a.almacenamiento, pr.precio, c.cantidad_stock AS cantidad
            FROM celulares c
            INNER JOIN producto p ON c.id_producto = p.id_producto
            INNER JOIN marcas m ON c.id_marcas = m.id_marcas
            INNER JOIN color col ON c.id_color = col.id_color
            INNER JOIN ram r ON c.id_ram = r.id_ram
            INNER JOIN almacenamiento a ON c.id_almacenamiento = a.id_almacenamiento
            INNER JOIN precio pr ON c.id_precio = pr.id_precio
            WHERE c.id_celulares = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) { header("Location: ../view/productos.php?error=query_fail"); exit; }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$row) { header("Location: ../view/productos.php?error=not_found"); exit; }

    // obtener imágenes relacionadas
    $imgs = [];
    $stmtImgs2 = mysqli_prepare($con, "SELECT id_imagen, imagen_url FROM imagenes_celulares WHERE id_celulares = ?");
    if ($stmtImgs2) {
        mysqli_stmt_bind_param($stmtImgs2, 'i', $id);
        mysqli_stmt_execute($stmtImgs2);
        $resImgs2 = mysqli_stmt_get_result($stmtImgs2);
        while ($r = mysqli_fetch_assoc($resImgs2)) $imgs[] = $r;
        mysqli_stmt_close($stmtImgs2);
    }

    // Consultar opciones para selects
    $productos = mysqli_query($con, "SELECT id_producto, nombre FROM producto");
    $marcas = mysqli_query($con, "SELECT id_marcas, marca FROM marcas");
    $colores = mysqli_query($con, "SELECT id_color, color FROM color");
    $rams = mysqli_query($con, "SELECT id_ram, ram FROM ram");
    $almacenamientos = mysqli_query($con, "SELECT id_almacenamiento, almacenamiento FROM almacenamiento");

    // Incluir la vista
    include(__DIR__ . '/../../Views/Admin/editar_celular.php');
    exit;
}

header("Location: ../view/productos.php");
exit;
?>
