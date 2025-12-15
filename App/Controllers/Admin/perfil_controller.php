<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Core/conexion.php';

$con = conectar();
$dbError = false;
if (!$con) {
    $dbError = true;
}

// --- Simular sesión en desarrollo: si no existe id en sesión, tomar el primer usuario de la BD
$simulacion_sesion = false;
if (!$dbError && !isset($_SESSION['id_usuario'])) {
    $r = mysqli_query($con, "SELECT id_usuario FROM usuario LIMIT 1");
    if ($r && $row = mysqli_fetch_assoc($r)) {
        $_SESSION['id_usuario'] = intval($row['id_usuario']);
        $simulacion_sesion = true;
    }
}

// Identificar usuario a mostrar
$sessionId = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 0;
$getId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($sessionId > 0 && $getId == 0) { // Mostrar mi perfil si no hay ID en URL
    $id = $sessionId;
    $isOwner = true;
} elseif ($getId > 0) { // Mostrar perfil de otro usuario
    $id = $getId;
    $isOwner = ($sessionId == $getId); // Es dueño si el ID de sesión coincide con el de la URL
} else {
    $id = null;
    $isOwner = false;
}

$user = null;
if (!$dbError && $id !== null) {
    $sql = "SELECT u.id_usuario, u.nombre, u.correo, u.telefono, u.imagen,
                   r.tipo_rol
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
}

// Si no se encuentra el usuario y se está tratando de ver un perfil específico (no el propio)
if ($user === null && $getId > 0) {
    // Podríamos redirigir o simplemente dejar que la vista muestre un error
}
// Si el usuario de la sesión no existe (ej. borrado), destruir sesión
elseif ($user === null && $sessionId > 0 && $getId == 0) {
    session_destroy();
    // Asegúrate de que login.php exista o redirige a una página de inicio de sesión válida
    header("Location: login.php"); 
    exit();
}

?>