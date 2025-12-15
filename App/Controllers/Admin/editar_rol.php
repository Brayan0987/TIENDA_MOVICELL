<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Core/conexion.php';
$con = conectar();
if (!$con) {
    die("Error de conexión a la base de datos");
}

// Obtener el ID del usuario a editar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: ../view/visualizar_usuarios.php?error=invalid_id");
    exit;
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = intval($_POST['id_usuario']);
    $id_roles = intval($_POST['id_roles']);

    if ($id_usuario > 0 && $id_roles > 0) {
        // Primero eliminamos cualquier rol existente
        $stmt_delete = mysqli_prepare($con, "DELETE FROM roles_usuario WHERE id_usuario = ?");
        mysqli_stmt_bind_param($stmt_delete, "i", $id_usuario);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);

        // Luego asignamos el nuevo rol
        $stmt_insert = mysqli_prepare($con, "INSERT INTO roles_usuario (id_usuario, id_roles) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "ii", $id_usuario, $id_roles);
        
        if (mysqli_stmt_execute($stmt_insert)) {
            header("Location: ../view/visualizar_usuarios.php?msg=rol_actualizado");
            exit;
        } else {
            header("Location: ../view/editar_rol.php?id=$id_usuario&error=update_failed");
            exit;
        }
        mysqli_stmt_close($stmt_insert);
    }
}

// Obtener información del usuario
$stmt = mysqli_prepare($con, "SELECT u.*, r.id_roles as rol_actual 
                            FROM usuario u 
                            LEFT JOIN roles_usuario ru ON u.id_usuario = ru.id_usuario 
                            LEFT JOIN roles r ON ru.id_roles = r.id_roles 
                            WHERE u.id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: ../view/visualizar_usuarios.php?error=user_not_found");
    exit;
}

// Obtener el rol actual
$curRole = $user['rol_actual'];

// Obtener todos los roles disponibles
$result_roles = mysqli_query($con, "SELECT id_roles, tipo_rol FROM roles ORDER BY tipo_rol");
$roles = mysqli_fetch_all($result_roles, MYSQLI_ASSOC);

?>