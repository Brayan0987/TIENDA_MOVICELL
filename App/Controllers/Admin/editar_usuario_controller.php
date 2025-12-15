<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Core/conexion.php';

$con = conectar();

// --- SEGURIDAD ---
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id = intval($_SESSION['id_usuario']);
$dbError = false;
$user = null;
$uploadError = null; // Para almacenar errores de carga

if (!$con) {
    $dbError = true;
} else {
    // --- MANEJADOR DE POST (Actualizar Perfil) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Debug logging
        error_log("=== POST Request Debug ===");
        error_log("FILES: " . print_r($_FILES, true));
        error_log("POST: " . print_r($_POST, true));
        
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($nombre === '' || $correo === '') {
            error_log("Validation failed: empty nombre or correo");
            header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/editar_usuario&error=invalid");
            exit;
        }

        // Helper para subir imagen
        function handle_upload_image($file) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            
            // Validar errores del upload
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                return null; // No se envió archivo
            }
            if ($file['error'] !== UPLOAD_ERR_OK) {
                error_log("Upload error code: " . $file['error']);
                return false; // Error en la carga
            }
            
            // Validar MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime, $allowed)) {
                error_log("Invalid MIME type: " . $mime);
                return false;
            }
            
            // Validar tamaño (3MB máximo)
            $maxBytes = 3 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                error_log("File too large: " . $file['size']);
                return false;
            }
            
            // Crear carpeta si no existe
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/TIENDA_MOVICELL/Public/assets/uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("Failed to create upload directory: " . $uploadDir);
                    return false;
                }
            }
            
            // Generar nombre único
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            try { 
                $rand = bin2hex(random_bytes(6)); 
            } catch (Exception $e) { 
                $rand = uniqid(); 
            }
            $filename = time() . '_' . $rand . '.' . $ext;
            $target = $uploadDir . $filename;
            
            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $target)) {
                error_log("File uploaded successfully: " . $target);
                return 'assets/uploads/' . $filename;
            } else {
                error_log("Failed to move uploaded file to: " . $target);
                return false;
            }
        }

        // Obtener imagen anterior
        $oldImage = null;
        $stmtOld = mysqli_prepare($con, "SELECT imagen FROM usuario WHERE id_usuario = ? LIMIT 1");
        if ($stmtOld) {
            mysqli_stmt_bind_param($stmtOld, 'i', $id);
            mysqli_stmt_execute($stmtOld);
            $resOld = mysqli_stmt_get_result($stmtOld);
            $rowOld = mysqli_fetch_assoc($resOld);
            $oldImage = $rowOld['imagen'] ?? null;
            mysqli_stmt_close($stmtOld);
        }

        // Manejar subida si vino archivo
        $newImagePath = null;
        if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            $resUpload = handle_upload_image($_FILES['imagen']);
            if ($resUpload === false) {
                header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/editar_usuario&error=img_invalid");
                exit;
            }
            if ($resUpload !== null) {
                $newImagePath = $resUpload;
            }
        }

        // Armar query update
        if ($newImagePath !== null) {
            $stmt = mysqli_prepare($con, "UPDATE usuario SET nombre = ?, correo = ?, telefono = ?, imagen = ? WHERE id_usuario = ?");
            if (!$stmt) {
                error_log("Prepare failed: " . mysqli_error($con));
                header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/editar_usuario&error=update_failed");
                exit;
            }
            mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $correo, $telefono, $newImagePath, $id);
        } else {
            $stmt = mysqli_prepare($con, "UPDATE usuario SET nombre = ?, correo = ?, telefono = ? WHERE id_usuario = ?");
            if (!$stmt) {
                error_log("Prepare failed: " . mysqli_error($con));
                header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/editar_usuario&error=update_failed");
                exit;
            }
            mysqli_stmt_bind_param($stmt, 'sssi', $nombre, $correo, $telefono, $id);
        }

        $ok = false;
        if ($stmt) {
            $ok = mysqli_stmt_execute($stmt);
            if (!$ok) {
                error_log("Execute failed: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        }

        if ($ok) {
            // Borrar imagen antigua si se reemplazó
            if ($newImagePath !== null && !empty($oldImage)) {
                $oldFile = $_SERVER['DOCUMENT_ROOT'] . '/TIENDA_MOVICELL/Public/' . $oldImage;
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/perfil&success=updated");
            exit;
        } else {
            // Si hubo nueva subida y fallo, eliminar archivo subido
            if ($newImagePath !== null) {
                $fileToRemove = $_SERVER['DOCUMENT_ROOT'] . '/TIENDA_MOVICELL/Public/' . $newImagePath;
                if (file_exists($fileToRemove)) {
                    @unlink($fileToRemove);
                }
            }
            header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/editar_usuario&error=update_failed");
            exit;
        }
    }

    // --- MANEJADOR GET (Cargar datos para el formulario) ---
    $sql = "SELECT u.id_usuario, u.nombre, u.correo, u.telefono, u.imagen, r.tipo_rol
            FROM usuario u
            LEFT JOIN roles_usuario ru ON ru.id_usuario = u.id_usuario
            LEFT JOIN roles r ON r.id_roles = ru.id_roles
            WHERE u.id_usuario = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }
    
    if ($user === null) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>