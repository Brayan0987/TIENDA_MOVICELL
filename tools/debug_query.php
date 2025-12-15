<?php
require_once dirname(__DIR__) . '/App/Core/config.php';
require_once dirname(__DIR__) . '/App/Core/Db.php';

$db = \App\Core\Db::conn();

echo "Test 1: Verificar pedido 20\n";
$stmt = $db->prepare('SELECT * FROM pedido WHERE id_pedido = 20');
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

if (!$pedido) {
    die("❌ Pedido 20 no existe\n");
}

echo "✅ Pedido 20 encontrado\n";
echo "ID Usuario Rol: " . $pedido['id_usuario_rol'] . "\n";
echo "ID Estado: " . $pedido['id_estado'] . "\n\n";

// Obtener usuario
echo "Test 2: Obtener usuario del pedido\n";
$stmt = $db->prepare('SELECT u.* FROM roles_usuario ru
                      JOIN usuario u ON ru.id_usuario = u.id_usuario
                      WHERE ru.id_usuario_rol = ?');
$stmt->bind_param('i', $pedido['id_usuario_rol']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    die("❌ Usuario no encontrado\n");
}

echo "✅ Usuario encontrado\n";
echo "Nombre: " . $usuario['nombre'] . "\n";
echo "Correo: " . $usuario['correo'] . "\n";
echo "Teléfono: " . $usuario['telefono'] . "\n\n";

// Obtener detalles
echo "Test 3: Obtener detalles del pedido\n";
$stmt = $db->prepare('SELECT COUNT(*) as cnt FROM detalle_pedido WHERE id_pedido = 20');
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc();
$stmt->close();

echo "Detalles: " . $count['cnt'] . "\n\n";

// Test la consulta completa del InvoiceController
echo "Test 4: Consulta completa de InvoiceController\n";
$id_pedido = 20;
$stmt = $db->prepare('SELECT p.*, e.estado as estado_nombre, u.nombre, u.correo, u.telefono
                      FROM pedido p
                      LEFT JOIN estado e ON p.id_estado = e.id_estado
                      LEFT JOIN roles_usuario ru ON p.id_usuario_rol = ru.id_usuario_rol
                      LEFT JOIN usuario u ON ru.id_usuario = u.id_usuario
                      WHERE p.id_pedido = ?');
$stmt->bind_param('i', $id_pedido);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("❌ Consulta falló\n");
}

echo "✅ Consulta exitosa\n";
echo "Correo en resultado: " . ($order['correo'] ?? 'VACIO') . "\n";
echo "Nombre: " . $order['nombre'] . "\n";

?>
