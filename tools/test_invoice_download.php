<?php
/**
 * Script de prueba para descargar factura en PDF
 * Uso: http://localhost/TIENDA_MOVICELL/Public/index.php?r=/factura/descargar&id=20
 */

// Rutas correctas
$BASEURL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/TIENDA_MOVICELL/';

// Simular sesión del usuario (si es necesario)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Simular que el usuario es un admin (para pruebas)
// Comentar estas líneas en producción
//$_SESSION['user_id'] = 1;
//$_SESSION['user_role'] = 'admin';

echo "<h2>🧪 Prueba de Descarga de Facturas</h2>";
echo "<p>Accede a estas rutas para probar las descargas:</p>";
echo "<ul>";

// Lista de pedidos para prueba
$pedidos = [19, 20, 21];

foreach ($pedidos as $id) {
    echo "<li>";
    echo "<strong>Pedido #$id</strong><br>";
    echo "📋 <a href='" . $BASEURL . "Public/index.php?r=/factura/ver&id=$id' target='_blank'>Ver Factura en Navegador</a> | ";
    echo "⬇️ <a href='" . $BASEURL . "Public/index.php?r=/factura/descargar&id=$id' target='_blank'>Descargar PDF</a>";
    echo "</li>";
}

echo "</ul>";

echo "<h3>✅ Características Implementadas:</h3>";
echo "<ul>";
echo "<li>✓ Descargar facturas en PDF desde admin y panel de usuario</li>";
echo "<li>✓ Ver facturas embebidas en navegador</li>";
echo "<li>✓ Diseño premium con gradientes púrpura/azul</li>";
echo "<li>✓ Imágenes de productos en las facturas</li>";
echo "<li>✓ Estados de pedido con colores (Pendiente/Enviado/Entregado/Cancelado)</li>";
echo "<li>✓ Información completa del cliente y detalles</li>";
echo "<li>✓ Protección de acceso (usuarios ven solo sus pedidos, admins ven todos)</li>";
echo "</ul>";

echo "<h3>📝 Notas Importantes:</h3>";
echo "<ol>";
echo "<li>Las rutas están disponibles en <strong>/factura/ver</strong> y <strong>/factura/descargar</strong></li>";
echo "<li>El usuario debe estar logueado</li>";
echo "<li>Los usuarios normales solo pueden ver/descargar sus propios pedidos</li>";
echo "<li>Los admins pueden ver/descargar cualquier pedido</li>";
echo "<li>Las imágenes se incluyen automáticamente si existen en la BD</li>";
echo "</ol>";
