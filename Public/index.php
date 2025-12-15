<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CARGAR CONFIGURACIÓN GLOBAL
require_once __DIR__ . '/../App/Core/config.php';

// COMPOSER AUTOLOADER (para PHPMailer, mPDF, etc.)
require_once __DIR__ . '/../vendor/autoload.php';

// DEFINIR LA VARIABLE $route
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$route      = parse_url($requestUri, PHP_URL_PATH);


// Capturar parámetro 'url' del .htaccess
if (isset($_GET['url'])) {
    $route = '/' . trim($_GET['url'], '/');
}


// Limpiar la ruta removiendo el directorio base del proyecto
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/') {
    $route = str_replace($basePath, '', $route);
}


// Si la ruta está vacía, asignar '/'
if (empty($route) || $route === '/') {
    $route = '/';
}


// SOPORTE PARA RUTAS CON ?r= (compatibilidad con tu sistema actual)
$routeParam = $_GET['r'] ?? null;
if ($routeParam) {
    $route = $routeParam;
}


// Variable base URL para redirecciones
$baseUrl = rtrim($basePath, '/');


// AUTOLOADER PARA LAS CLASES
spl_autoload_register(function ($class) {
    $prefix   = 'App\\';
    $base_dir = __DIR__ . '/../App/';
    $len      = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file           = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Asegurar que $_SESSION['user_name'] está establecido si hay user_id
if (!empty($_SESSION['user_id']) && empty($_SESSION['user_name'])) {
    try {
        $userModel = new \App\Models\User();
        $user = $userModel->findById((int)$_SESSION['user_id']);
        if ($user && isset($user['nombre'])) {
            $_SESSION['user_name'] = $user['nombre'];
        }
    } catch (\Exception $e) {
        error_log('Error sincronizando user_name: ' . $e->getMessage());
    }
}

// DEBUG
error_log("=== ROUTING DEBUG ===");
error_log("Route: " . $route);
error_log("Method: " . $_SERVER['REQUEST_METHOD']);


// --------------------------- ROUTING ---------------------------
switch (true) {

    // PÁGINA DE INICIO
    case ($route === '/' || $route === '/home'):
        include_once __DIR__ . '/../App/Views/home.php';
        break;

    // RUTA DE DETALLE DEL PRODUCTO (amigable): /producto/16
    case (preg_match('/^\/producto\/(\d+)$/', $route, $matches) ? true : false):
        $productId = (int)$matches[1];
        require_once __DIR__ . '/../App/Controllers/ProductController.php';
        $controller = new \App\Controllers\ProductController();
        $controller->detail($productId);
        break;

    // RUTA DE DETALLE DEL PRODUCTO CON ?r=/producto-detalle&id=16
    case ($route === '/producto-detalle'):
        $productId = (int)($_GET['id'] ?? 0);
        require_once __DIR__ . '/../App/Controllers/ProductController.php';
        $controller = new \App\Controllers\ProductController();
        $controller->detail($productId);
        break;

    // LISTADO DE PRODUCTOS
    case ($route === '/productos'):
        include_once __DIR__ . '/../App/Views/producto.php';
        break;

    // RUTAS DE AUTENTICACIÓN
    case ($route === '/login'):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../App/Controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->login();
        } else {
            include_once __DIR__ . '/../App/Views/auth/login.php';
        }
        break;

    case ($route === '/register'):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../App/Controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->register();
        } else {
            include_once __DIR__ . '/../App/Views/auth/register.php';
        }
        break;

    case ($route === '/logout'):
        require_once __DIR__ . '/../App/Controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->logout();
        break;

    // PANEL DE USUARIO
    case ($route === '/panel'):
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para acceder al panel';
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->panel();
        break;

    // CRUD USUARIOS PANEL
    case ($route === '/panel/profile'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../App/Controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->updateProfile();
        } else {
            header('Location: ' . $baseUrl . '/index.php?r=/panel');
            exit;
        }
        break;

    case ($route === '/panel/password'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../App/Controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->updatePassword();
        } else {
            header('Location: ' . $baseUrl . '/index.php?r=/panel');
            exit;
        }
        break;

    case ($route === '/account/delete'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../App/Controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->deleteAccount();
        } else {
            header('Location: ' . $baseUrl . '/index.php?r=/panel');
            exit;
        }
        break;

    case ($route === '/panel/orders' || $route === '/panel/order'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->viewOrders();
        break;

    // CARRITO Y CHECKOUT
    case ($route === '/cart'):
        require_once __DIR__ . '/../App/Controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->index();
        break;

    case ($route === '/cart/add'):
        require_once __DIR__ . '/../App/Controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->add();
        break;

    case ($route === '/cart/update'):
        require_once __DIR__ . '/../App/Controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->update();
        break;

    case ($route === '/cart/remove'):
        require_once __DIR__ . '/../App/Controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->remove();
        break;

    case ($route === '/cart/clear'):
        require_once __DIR__ . '/../App/Controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->clear();
        break;

    case ($route === '/cart/count'):
        require_once __DIR__ . '/../App/Controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->count();
        break;

            case ($route === '/cart/apply-coupon'):
        require_once __DIR__ . '/../App/Controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->applyCoupon();
        break;


    // CHECKOUT
    case ($route === '/checkout'):
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para realizar una compra';
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/CheckoutController.php';
        $controller = new \App\Controllers\CheckoutController();
        $controller->index();
        break;

    case ($route === '/checkout/process'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/CheckoutController.php';
        $controller = new \App\Controllers\CheckoutController();
        $controller->process();
        break;

    case ($route === '/checkout/success'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/CheckoutController.php';
        $controller = new \App\Controllers\CheckoutController();
        $controller->success();
        break;

    // ------------- ADMIN PANEL RUTAS PRINCIPALES -------------
    case ($route === '/admin/productos'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/listar_productos_controller.php';
        include_once __DIR__ . '/../App/Views/Admin/productos.php';
        break;

    case ($route === '/admin/actualizar-producto'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de producto inválido';
            header('Location: ' . $baseUrl . '/index.php?r=/admin/productos');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/actualizar.php';
        include_once __DIR__ . '/../App/Views/Admin/editar_celular.php';
        break;

    case ($route === '/admin/perfil'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        include_once __DIR__ . '/../App/Views/Admin/perfil.php';
        break;

    // NUEVA RUTA: EDITAR USUARIO DESDE PERFIL
    case ($route === '/admin/editar_usuario'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        // Si necesitas un controlador, lo requieres aquí
        // require_once __DIR__ . '/../App/Controllers/Admin/editar_usuario_controller.php';
        include_once __DIR__ . '/../App/Views/Admin/editar_usuario.php';
        break;

    case ($route === '/admin/insertar-producto'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        include_once __DIR__ . '/../App/Views/Admin/Insertar_producto.php';
        break;

    case ($route === '/admin/marcas_precios'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        include_once __DIR__ . '/../App/Views/Admin/marcas_precios.php';
        break;

    case ($route === '/admin/atributos'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        include_once __DIR__ . '/../App/Views/Admin/editar_rol.php';
        break;

    case ($route === '/admin/visualizar_usuarios'):
    case ($route === '/admin/usuarios'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        include_once __DIR__ . '/../App/Views/Admin/visualizar_usuarios.php';
        break;

    case ($route === '/admin/ventas'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/PedidosController.php';
        $controller = new \App\Controllers\Admin\PedidosController();
        $controller->index();
        break;

    case ($route === '/admin/pedido'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/PedidosController.php';
        $controller = new \App\Controllers\Admin\PedidosController();
        $controller->detail();
        break;

    case ($route === '/admin/estados_list'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/PedidosController.php';
        $controller = new \App\Controllers\Admin\PedidosController();
        $controller->getEstados();
        break;

    case ($route === '/admin/pedido_update_status'):
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/PedidosController.php';
        $controller = new \App\Controllers\Admin\PedidosController();
        $controller->updateStatus();
        break;

    case ($route === '/admin/editar-rol'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        include_once __DIR__ . '/../App/Views/Admin/editar_rol.php';
        break;

    // ===== RUTAS DE FACTURAS =====
    case ($route === '/factura/ver'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/InvoiceController.php';
        $controller = new \App\Controllers\InvoiceController();
        $controller->viewInvoice();
        break;

    case ($route === '/factura/descargar'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/InvoiceController.php';
        $controller = new \App\Controllers\InvoiceController();
        $controller->download();
        break;

    case ($route === '/factura/reenviar'):
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        if (empty($_SESSION['user_id'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/InvoiceController.php';
        $controller = new \App\Controllers\InvoiceController();
        $controller->resend();
        break;

    // RUTA NO ENCONTRADA
    default:
        error_log("=== ROUTE NOT FOUND: " . $route . " ===");
        header('Location: ' . $baseUrl . '/');
        exit;
    case ($route === '/orders'):
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/CheckoutController.php';
        $controller = new \App\Controllers\CheckoutController();
        $controller->orderDetail();
        break;
    // RECUPERACIÓN DE CONTRASEÑA
    case ($route === '/forgot-password'):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../App/Controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->forgotPassword();
        } else {
            include_once __DIR__ . '/../App/Views/auth/forgot-password.php';
        }
        break;

    case ($route === '/reset-password'):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../App/Controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->resetPassword();
        } else {
            require_once __DIR__ . '/../App/Controllers/AuthController.php';
            $controller = new \App\Controllers\AuthController();
            $controller->showResetPassword();
        }
        break;
    case ($route === '/admin/cupones'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/CouponController.php';
        $controller = new \App\Controllers\Admin\CouponController();
        $controller->index();
        break;

    case ($route === '/admin/cupones/save'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/CouponController.php';
        $controller = new \App\Controllers\Admin\CouponController();
        $controller->save();
        break;

    case ($route === '/admin/cupones/delete'):
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . $baseUrl . '/index.php?r=/login');
            exit;
        }
        require_once __DIR__ . '/../App/Controllers/Admin/CouponController.php';
        $controller = new \App\Controllers\Admin\CouponController();
        $controller->delete();
        break;

        case ($route === '/cart/remove-coupon'):
    require_once __DIR__ . '/../App/Controllers/CartController.php';
    $controller = new \App\Controllers\CartController();
    $controller->removeCoupon();
    break;



}  