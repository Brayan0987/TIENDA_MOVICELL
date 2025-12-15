<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Core/conexion.php';

$con = conectar();
if (!$con) { 
    die("Error de conexión a DB"); 
}

// Genera baseUrl (igual que en tu layout y vistas)
$baseUrl = '';
if (isset($_SERVER['SCRIPT_NAME'])) {
    $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
    if ($baseUrl === '/' || $baseUrl === '\\') $baseUrl = '';
}
// Siempre fuerza "/public" por claridad y seguridad
if (strpos($baseUrl, '/public') === false) {
    $baseUrl .= '/public';
}


$msg = $_GET['msg'] ?? null;
$error = $_GET['error'] ?? null;

// --- MANEJADOR DE PETICIONES POST ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- REGISTRAR USUARIO ---
    if (isset($_POST['registrar_usuario'])) {
        $nombre = trim($_POST['nombre']);
        $correo = trim($_POST['correo']);
        $telefono = trim($_POST['telefono']);
        $password = trim($_POST['password']);
        $id_rol = intval($_POST['id_rol']);

        if (empty($nombre) || empty($correo) || empty($password) || empty($id_rol)) {
            header("$baseUrl/index.php?r=/admin/visualizar_usuarios&msg=" . urlencode("MENSAJE"));
exit();

        }

        $pass_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql_user = "INSERT INTO usuario (nombre, correo, telefono, contraseña) VALUES (?, ?, ?, ?)";
        $stmt_user = mysqli_prepare($con, $sql_user);
        mysqli_stmt_bind_param($stmt_user, "ssss", $nombre, $correo, $telefono, $pass_hash);
        
        if (mysqli_stmt_execute($stmt_user)) {
            $id_usuario_nuevo = mysqli_insert_id($con);
            mysqli_stmt_close($stmt_user);

            $sql_role = "INSERT INTO roles_usuario (id_usuario, id_roles) VALUES (?, ?)";
            $stmt_role = mysqli_prepare($con, $sql_role);
            mysqli_stmt_bind_param($stmt_role, "ii", $id_usuario_nuevo, $id_rol);
            
            if (mysqli_stmt_execute($stmt_role)) {
                mysqli_stmt_close($stmt_role);
               header("$baseUrl/index.php?r=/admin/visualizar_usuarios&msg=" . urlencode("MENSAJE"));
exit();

            } else {
                mysqli_query($con, "DELETE FROM usuario WHERE id_usuario = $id_usuario_nuevo");
               header("$baseUrl/index.php?r=/admin/visualizar_usuarios&msg=" . urlencode("MENSAJE"));
exit();

            }
        } else {
            mysqli_stmt_close($stmt_user);
            header("$baseUrl/index.php?r=/admin/visualizar_usuarios&msg=" . urlencode("MENSAJE"));
exit();

        }
    }

    // --- ELIMINAR USUARIO ---
    if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
        $id_usuario = intval($_POST['id_usuario'] ?? 0);

        if ($id_usuario > 0) {
            // Primero borra roles
            $sql_delete_roles = "DELETE FROM roles_usuario WHERE id_usuario = ?";
            $stmt_delete_roles = mysqli_prepare($con, $sql_delete_roles);
            mysqli_stmt_bind_param($stmt_delete_roles, "i", $id_usuario);
            mysqli_stmt_execute($stmt_delete_roles);
            mysqli_stmt_close($stmt_delete_roles);

            // Luego borra usuario
            $sql_delete_user = "DELETE FROM usuario WHERE id_usuario = ?";
            $stmt_delete_user = mysqli_prepare($con, $sql_delete_user);
            mysqli_stmt_bind_param($stmt_delete_user, "i", $id_usuario);

            if (mysqli_stmt_execute($stmt_delete_user)) {
                mysqli_stmt_close($stmt_delete_user);
               header("$baseUrl/index.php?r=/admin/visualizar_usuarios&msg=" . urlencode("MENSAJE"));
exit();

            } else {
                mysqli_stmt_close($stmt_delete_user);
                header("$baseUrl/index.php?r=/admin/visualizar_usuarios&msg=" . urlencode("MENSAJE"));
exit();

            }
        }
    }
}

// --- LÓGICA DE VISUALIZACIÓN Y FILTROS (Manejador GET) ---
$search = $_GET['q'] ?? '';
$filtro_rol = $_GET['filtro_rol'] ?? '';
$params = [];
$types = "";
$where_clauses = [];

$sql = "SELECT u.id_usuario, u.nombre, u.correo, u.telefono, u.imagen, r.tipo_rol
        FROM usuario u
        LEFT JOIN roles_usuario ru ON ru.id_usuario = u.id_usuario
        LEFT JOIN roles r ON r.id_roles = ru.id_roles";

if (!empty($search)) {
    $where_clauses[] = "(u.nombre LIKE ? OR u.correo LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($filtro_rol) && is_numeric($filtro_rol)) {
    $where_clauses[] = "r.id_roles = ?";
    $params[] = $filtro_rol;
    $types .= "i";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY u.id_usuario DESC";

$stmt = mysqli_prepare($con, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$usuarios = mysqli_fetch_all($res, MYSQLI_ASSOC);

// Obtener roles para los filtros y formularios
$roles_stmt = mysqli_prepare($con, "SELECT id_roles, tipo_rol FROM roles ORDER BY tipo_rol");
mysqli_stmt_execute($roles_stmt);
$roles_res = mysqli_stmt_get_result($roles_stmt);
$roles = mysqli_fetch_all($roles_res, MYSQLI_ASSOC);

?>
