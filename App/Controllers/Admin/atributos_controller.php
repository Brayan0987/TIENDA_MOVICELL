<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Core/conexion.php';
$con = conectar();

// CALCULA BASE URL PÚBLICO SIEMPRE
$baseUrl = '';
if (isset($_SERVER['SCRIPT_NAME'])) {
    $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
    if ($baseUrl === '/' || $baseUrl === '\\') $baseUrl = '';
}
if (strpos($baseUrl, '/public') === false) {
    $baseUrl .= '/public';
}

$msg = $_GET['msg'] ?? null;
$error = $_GET['error'] ?? null;
$search = $_GET['q'] ?? '';
$search_param = "%{$search}%";

// --- MANEJADOR DE POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- LÓGICA DE INSERCIÓN ---
    $insert_actions = [
        'submit_marca' => ['table' => 'marcas', 'column' => 'marca', 'param_type' => 's', 'post_field' => 'nueva_marca', 'success_msg' => 'Marca agregada'],
        'submit_precio' => ['table' => 'precio', 'column' => 'precio', 'param_type' => 'd', 'post_field' => 'nuevo_precio', 'success_msg' => 'Precio agregado'],
        'submit_color' => ['table' => 'color', 'column' => 'color', 'param_type' => 's', 'post_field' => 'nuevo_color', 'success_msg' => 'Color agregado'],
        'submit_ram' => ['table' => 'ram', 'column' => 'ram', 'param_type' => 's', 'post_field' => 'nueva_ram', 'success_msg' => 'RAM agregada'],
        'submit_almacenamiento' => ['table' => 'almacenamiento', 'column' => 'almacenamiento', 'param_type' => 's', 'post_field' => 'nuevo_almacenamiento', 'success_msg' => 'Almacenamiento agregado']
    ];

    foreach ($insert_actions as $submit_key => $action) {
        if (isset($_POST[$submit_key])) {
            $value = trim($_POST[$action['post_field']]);
            if ($value !== '') {
                $sql = "INSERT INTO {$action['table']} ({$action['column']}) VALUES (?)";
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, $action['param_type'], $value);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: $baseUrl/index.php?r=/admin/marcas_precios&msg" . urlencode($action['success_msg']));
                } else {
                    header("Location: $baseUrl/index.php?r=/admin/marcas_precios&error=" . urlencode(mysqli_error($con)));
                }
                mysqli_stmt_close($stmt);
                exit();
            }
        }
    }

    // --- LÓGICA DE ELIMINACIÓN ---
    if (isset($_POST['action']) && $_POST['action'] == 'delete_attribute') {
        $tipo = $_POST['tipo'] ?? '';
        $id = intval($_POST['id'] ?? 0);

        $delete_config = [
            'marca' => ['table' => 'marcas', 'id_column' => 'id_marcas'],
            'precio' => ['table' => 'precio', 'id_column' => 'id_precio'],
            'color' => ['table' => 'color', 'id_column' => 'id_color'],
            'ram' => ['table' => 'ram', 'id_column' => 'id_ram'],
            'almacenamiento' => ['table' => 'almacenamiento', 'id_column' => 'id_almacenamiento']
        ];

        if (array_key_exists($tipo, $delete_config) && $id > 0) {
            $config = $delete_config[$tipo];
            $sql = "DELETE FROM {$config['table']} WHERE {$config['id_column']} = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: $baseUrl/index.php?r=/admin/marcas_precios&msg=" . urlencode(ucfirst($tipo) . ' eliminado correctamente'));
            } else {
                header("Location: $baseUrl/index.php?r=/admin/marcas_precios&error=" . urlencode(mysqli_error($con)));
            }
            mysqli_stmt_close($stmt);
            exit();
        }
    }
}

// --- LÓGICA DE CONSULTA (SELECT) ---
function get_attributes($con, $table, $column, $search_param) {
    $sql = "SELECT * FROM {$table} WHERE {$column} LIKE ? ORDER BY {$column}";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $search_param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $data;
}

$marcas = get_attributes($con, 'marcas', 'marca', $search_param);
$precios = get_attributes($con, 'precio', 'precio', $search_param);
$colores = get_attributes($con, 'color', 'color', $search_param);
$rams = get_attributes($con, 'ram', 'ram', $search_param);
$almacenamientos = get_attributes($con, 'almacenamiento', 'almacenamiento', $search_param);

?>
