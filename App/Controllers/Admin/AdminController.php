<?php
namespace App\Controllers\Admin;

class AdminController {

    public function productos() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?r=/login');
            exit();
        }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Acceso denegado. Solo administradores pueden acceder.';
            header('Location: index.php?r=/');
            exit();
        }
        require_once __DIR__ . '/../../Views/Admin/productos.php';
    }

    public function crearProducto() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?r=/login');
            exit();
        }
        require_once __DIR__ . '/../../Views/Admin/crear_producto.php';
    }

    public function editarProducto() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?r=/login');
            exit();
        }
        require_once __DIR__ . '/../../Views/Admin/editar_producto.php';
    }

    public function eliminarProducto() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?r=/login');
            exit();
        }
        // Lógica para eliminar producto
        header('Location: index.php?r=/admin/productos');
        exit();
    }
}
