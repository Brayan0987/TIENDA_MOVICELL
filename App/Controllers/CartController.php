<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Cart;
use App\Core\Db;

final class CartController extends Controller
{
    private Cart $cart;
    
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->cart = new Cart();
    }
    
    public function index(): void
    {
        // Items y totales base
        $cartItems      = $this->cart->getItems();
        $totalPrice     = $this->cart->getTotalPrice(); // subtotal
        $totalQuantity  = $this->cart->getTotalQuantity();

        // Cálculo de descuento si hay cupón en sesión
        $discount          = 0.0;
        $totalWithDiscount = $totalPrice;

        if (!empty($_SESSION['coupon']) && $totalPrice > 0) {
            $c = $_SESSION['coupon'];

            if (($c['tipo'] ?? '') === 'percent') {
                $discount = $totalPrice * ((float)$c['valor'] / 100);
            } elseif (($c['tipo'] ?? '') === 'fixed') {
                $discount = (float)$c['valor'];
            }

            if ($discount > $totalPrice) {
                $discount = $totalPrice;
            }

            $totalWithDiscount = $totalPrice - $discount;
        }

        $this->view('cart.index', [
            'items'               => $cartItems,
            'total_price'         => $totalPrice,
            'total_quantity'      => $totalQuantity,
            'discount'            => $discount,
            'total_with_discount' => $totalWithDiscount,
            'title'               => 'Carrito de Compras',
        ]);
    }
    
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Requerir sesión de usuario para agregar al carrito
            if (empty($_SESSION['user_id'])) {

                // Si es AJAX, devolver JSON indicando que debe iniciar sesión
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success'        => false,
                        'message'        => 'Debes iniciar sesión para agregar productos al carrito.',
                        'login_required' => true,
                    ]);
                    exit();
                }

                // No AJAX -> redirigir a login
                $this->redirect('/login');
            }

            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

            // Datos que vienen del formulario
            $name  = trim($_POST['name']  ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $image = trim($_POST['image'] ?? '');

            // Normalizar ruta de imagen para evitar problemas al recargar
            if ($image !== '') {
                // quitar dominio si viene completo
                $image = preg_replace('#^https?://[^/]+#', '', $image);
            }

            // ==============================
            // OBTENER STOCK REAL DESDE LA BD
            // ==============================
            $stock = 0;
            if ($productId > 0) {
                $db = Db::conn();
                $stmt = $db->prepare('SELECT cantidad_stock FROM celulares WHERE id_celulares = ? LIMIT 1');
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $stmt->bind_result($dbStock);
                if ($stmt->fetch()) {
                    $stock = (int)$dbStock;
                }
                $stmt->close();
            }

            // Si no hay stock, bloquear
            if ($stock <= 0) {
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'limited' => true,
                        'message' => 'No hay stock disponible para este producto.',
                    ]);
                    exit();
                }
                $_SESSION['cart_error'] = 'No hay stock disponible para este producto.';
                $this->redirect('/productos');
            }

            if ($productId > 0 && $name !== '' && $price > 0) {

                $productData = [
                    'name'  => $name,
                    'price' => $price,
                    'image' => $image,
                    'stock' => $stock, // stock real de celulares.cantidad_stock
                ];

                // Añadir al carrito (usa la lógica de límite en Cart::addItem)
                $result = $this->cart->addItem($productId, $quantity, $productData);
                
                // Si es AJAX o formulario desde JS, devolver JSON
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success'     => $result['success'],
                        'message'     => $result['message'],
                        'limited'     => $result['limited'] ?? false,
                        'cart_count'  => $this->cart->getTotalQuantity(),
                        'current_qty' => $result['current_qty'] ?? 0,
                        'max_stock'   => $result['max_stock'] ?? $stock,
                        'product_id'  => $productId,
                    ]);
                    exit();
                }
                
                // No es AJAX
                if (empty($result['limited'])) {
                    $this->redirect('/cart');
                } else {
                    $_SESSION['cart_error'] = $result['message'] ?? 'No se pudo agregar más unidades.';
                    $this->redirect('/productos');
                }
            }
        }
        
        $this->redirect('/productos');
    }
    
    /**
     * Detectar si es una solicitud AJAX/JSON
     */
    private function isAjaxRequest(): bool
    {
        if (!empty($_POST['ajax']) || !empty($_GET['ajax'])) {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        $accept      = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        return strpos($accept, 'application/json') !== false ||
               strpos($contentType, 'application/json') !== false;
    }
    
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity  = (int)($_POST['quantity'] ?? 0);

            if ($productId > 0) {

                // 1) Obtener el stock real desde la BD
                $maxStock = 0;
                $db = Db::conn();
                $stmt = $db->prepare('SELECT cantidad_stock FROM celulares WHERE id_celulares = ? LIMIT 1');
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $stmt->bind_result($dbStock);
                if ($stmt->fetch()) {
                    $maxStock = (int)$dbStock;
                }
                $stmt->close();

                // 2) Asegurar límite máximo 10 también
                if ($maxStock <= 0) {
                    $maxStock = 1;
                }
                $maxStock = min($maxStock, 10);

                // 3) Actualizar usando la lógica de Cart::updateItem
                $wasLimited = !$this->cart->updateItem($productId, $quantity, $maxStock);
                
                if (!empty($_POST['ajax'])) {
                    header('Content-Type: application/json; charset=utf-8');
                    $cartItems = $this->cart->getItems();
                    $actualQty = isset($cartItems[$productId]) ? (int)$cartItems[$productId]['quantity'] : 0;
                    
                    echo json_encode([
                        'success'     => true,
                        'message'     => $wasLimited
                            ? "Se alcanzó el límite máximo ($maxStock unidades) para este producto."
                            : 'Carrito actualizado',
                        'limited'     => $wasLimited,
                        'actual_qty'  => $actualQty,
                        'max_stock'   => $maxStock,
                        'cart_count'  => $this->cart->getTotalQuantity(),
                        'total_price' => $this->cart->getTotalPrice(),
                    ]);
                    exit();
                }
            }
        }
        
        $this->redirect('/cart');
    }
    
    public function remove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int)($_POST['product_id'] ?? 0);
            
            if ($productId > 0) {
                $this->cart->removeItem($productId);
                
                if (!empty($_POST['ajax'])) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success'    => true,
                        'message'    => 'Producto eliminado',
                        'cart_count' => $this->cart->getTotalQuantity(),
                    ]);
                    exit();
                }
            }
        }
        
        $this->redirect('/cart');
    }
    
    public function clear(): void
    {
        $this->cart->clearCart();
        // Al limpiar carrito, también limpiamos cupón
        unset($_SESSION['coupon'], $_SESSION['coupon_message']);
        $this->redirect('/cart');
    }
    
    public function count(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'count' => $this->cart->getTotalQuantity(),
        ]);
        exit();
    }

    /**
     * Aplicar cupón de descuento al carrito
     */
    public function applyCoupon(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
        }

        $code = trim($_POST['coupon_code'] ?? '');
        if ($code === '') {
            $_SESSION['coupon_message'] = 'Debes ingresar un código de cupón.';
            $this->redirect('/cart');
        }

        // Subtotal actual del carrito
        $subtotal = $this->cart->getTotalPrice();

        if ($subtotal <= 0) {
            $_SESSION['coupon_message'] = 'No puedes aplicar un cupón a un carrito vacío.';
            unset($_SESSION['coupon']);
            $this->redirect('/cart');
        }

        // Conexión DB
        $db = Db::conn();
        $stmt = $db->prepare("
            SELECT id_cupon, codigo, tipo, valor, monto_minimo,
                   fecha_inicio, fecha_fin, uso_maximo, uso_actual, activo
            FROM cupon
            WHERE codigo = ?
            LIMIT 1
        ");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $coupon = $result->fetch_assoc();
        $stmt->close();

        if (!$coupon || (int)$coupon['activo'] !== 1) {
            $_SESSION['coupon_message'] = 'Cupón inválido o inactivo.';
            unset($_SESSION['coupon']);
            $this->redirect('/cart');
        }

        $now = new \DateTimeImmutable();

        if (!empty($coupon['fecha_inicio']) && $now < new \DateTimeImmutable($coupon['fecha_inicio'])) {
            $_SESSION['coupon_message'] = 'Este cupón aún no está disponible.';
            unset($_SESSION['coupon']);
            $this->redirect('/cart');
        }

        if (!empty($coupon['fecha_fin']) && $now > new \DateTimeImmutable($coupon['fecha_fin'])) {
            $_SESSION['coupon_message'] = 'Este cupón ha expirado.';
            unset($_SESSION['coupon']);
            $this->redirect('/cart');
        }

        // Monto mínimo
        if (!empty($coupon['monto_minimo']) && $subtotal < (float)$coupon['monto_minimo']) {
            $_SESSION['coupon_message'] = 'El total del carrito no alcanza el mínimo para este cupón.';
            unset($_SESSION['coupon']);
            $this->redirect('/cart');
        }

        // Límite de usos global (opcional)
        if (!empty($coupon['uso_maximo']) && (int)$coupon['uso_actual'] >= (int)$coupon['uso_maximo']) {
            $_SESSION['coupon_message'] = 'Este cupón ya alcanzó el número máximo de usos.';
            unset($_SESSION['coupon']);
            $this->redirect('/cart');
        }

        // Guardar datos esenciales en sesión
        $_SESSION['coupon'] = [
            'id_cupon' => (int)$coupon['id_cupon'],
            'codigo'   => $coupon['codigo'],
            'tipo'     => $coupon['tipo'],   // 'percent' o 'fixed'
            'valor'    => (float)$coupon['valor'],
        ];

        $_SESSION['coupon_message'] = 'Cupón aplicado correctamente.';

        $this->redirect('/cart');
    }

    public function removeCoupon(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION['coupon'], $_SESSION['coupon_message']);

        $this->redirect('/cart');
    }
}
