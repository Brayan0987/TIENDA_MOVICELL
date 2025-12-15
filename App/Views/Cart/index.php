<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Core/Cart.php';
$cart = new App\Core\Cart();
$cartCount = $cart->getTotalQuantity();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Carrito de Compras - Movi Cell</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= $base ?>assets/css/cart.css" rel="stylesheet">
</head>
<body data-base-url="<?= htmlspecialchars($base, ENT_QUOTES) ?>">

    <!-- PRELOADER -->
    <div id="preloader">
        <div class="spinner-modern">
            <div class="spinner-ring"></div>
            <i class="bi bi-bag-fill"></i>
        </div>
    </div>

    <!-- NAVBAR - IGUAL QUE HOME -->
    <nav class="navbar-glass" id="mainNavbar">
        <div class="container-xl">
            <div class="navbar-content">
                <a class="brand-logo" href="<?= $base ?>">
                    <div class="brand-icon">
                        <i class="bi bi-phone-fill"></i>
                    </div>
                    <span class="brand-text">Movi<span style="color: var(--gray-500);">Cell</span></span>
                </a>

                <div class="nav-links d-none d-lg-flex">
                    <a href="<?= $base ?>" class="nav-item">
                        <i class="bi bi-house-door"></i>
                        <span>Inicio</span>
                    </a>
                    <a href="<?= $base ?>index.php?r=/productos" class="nav-item">
                        <i class="bi bi-grid-3x3"></i>
                        <span>Catálogo</span>
                    </a>
                    <a href="<?= $base ?>index.php?r=/cart" class="nav-item active">
                        <i class="bi bi-bag"></i>
                        <span>Carrito</span>
                    </a>
                </div>

                <div class="nav-actions d-none d-lg-flex">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <div class="dropdown user-menu">
                            <button class="btn-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                                <span><?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Usuario')[0]) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= $base ?>index.php?r=/panel"><i class="bi bi-speedometer2 me-2"></i>Mi Panel</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $base ?>index.php?r=/logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= $base ?>index.php?r=/login" class="btn-nav-action">
                            <i class="bi bi-person"></i>
                            <span>Ingresar</span>
                        </a>
                    <?php endif; ?>

                    <a href="<?= $base ?>index.php?r=/cart" class="btn-cart">
                        <i class="bi bi-bag"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <button class="btn-mobile-menu d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="Menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- OFFCANVAS MOBILE - IGUAL QUE HOME -->
    <div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileMenuLabel">
                <i class="bi bi-phone-fill me-2"></i>Movi Cell
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-menu">
                <a href="<?= $base ?>" class="mobile-menu-item">
                    <i class="bi bi-house-door"></i>
                    <span>Inicio</span>
                </a>
                <a href="<?= $base ?>index.php?r=/productos" class="mobile-menu-item">
                    <i class="bi bi-grid-3x3"></i>
                    <span>Catálogo</span>
                </a>
                <a href="<?= $base ?>index.php?r=/cart" class="mobile-menu-item active">
                    <i class="bi bi-bag"></i>
                    <span>Carrito</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="badge bg-primary ms-auto"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="mobile-auth">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <div class="user-info mb-3">
                        <i class="bi bi-person-circle me-2"></i>
                        <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                    </div>
                    <a href="<?= $base ?>index.php?r=/panel" class="btn-mobile-action">Mi Panel</a>
                    <a href="<?= $base ?>index.php?r=/logout" class="btn-mobile-action outline">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="<?= $base ?>index.php?r=/login" class="btn-mobile-action">Iniciar Sesión</a>
                    <a href="<?= $base ?>index.php?r=/register" class="btn-mobile-action outline">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- BREADCRUMB -->
    <div class="breadcrumb-section">
        <div class="container-xl">
            <nav aria-label="breadcrumb" data-aos="fade-right">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>"><i class="bi bi-house-door me-1"></i>Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-bag me-1"></i>Carrito</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="cart-container">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <h1 class="page-title" data-aos="fade-up">
                        <i class="bi bi-bag-fill me-3"></i>Mi Carrito de Compras
                    </h1>
                </div>
            </div>

            <?php if (empty($items)): ?>
                <!-- EMPTY CART -->
                <div class="empty-cart" data-aos="fade-up">
                    <div class="empty-icon">
                        <i class="bi bi-bag-x"></i>
                    </div>
                    <h2 class="empty-title">Tu carrito está vacío</h2>
                    <p class="empty-subtitle">Agrega algunos productos increíbles para comenzar tu compra</p>
                    <div class="empty-actions">
                        <a href="<?= $base ?>index.php?r=/productos" class="btn-hero primary">
                            <i class="bi bi-grid-3x3"></i>
                            <span>Explorar Productos</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <!-- CART ITEMS -->
                    <div class="col-lg-8">
                        <div class="cart-items-header" data-aos="fade-right">
                            <h5><i class="bi bi-list-ul me-2"></i>Productos en tu carrito (<?= $total_quantity ?? 0 ?>)</h5>
                        </div>

                        <?php foreach ($items as $index => $item): ?>
                            <div class="cart-item-premium" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-2">
                                        <div class="cart-item-image">
                                            <?php
                                            $imagePath = $item['image'] ?? '';
                                            $imageSrc  = $imagePath !== ''
                                                ? $base . ltrim($imagePath, '/')
                                                : $base . 'assets/Imagenes/placeholder.png';
                                            ?>
                                            <img src="<?= htmlspecialchars($imageSrc) ?>"
                                                 alt="<?= htmlspecialchars($item['name']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="cart-item-title"><?= htmlspecialchars($item['name']) ?></h5>
                                        <p class="cart-item-price">$<?= number_format($item['price'], 0, ',', '.') ?> COP</p>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="quantity-input-premium">
                                            <button class="quantity-btn-premium" 
                                                    onclick="updateQuantity(<?= $item['product_id'] ?>, <?= max(1, $item['quantity'] - 1) ?>, 10)"
                                                    title="Disminuir">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" 
                                                   class="quantity-value-premium" 
                                                   value="<?= $item['quantity'] ?>" 
                                                   min="1"
                                                   max="10"
                                                   onchange="updateQuantity(<?= $item['product_id'] ?>, this.value, 10)"
                                                   title="Cantidad (máximo 10)">
                                            <button class="quantity-btn-premium" 
                                                    onclick="updateQuantity(<?= $item['product_id'] ?>, <?= min(10, $item['quantity'] + 1) ?>, 10)"
                                                    title="Aumentar">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="cart-item-total">
                                            $<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn-remove-premium" 
                                                onclick="removeItem(<?= $item['product_id'] ?>)"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- ACTIONS -->
                        <div class="cart-actions" data-aos="fade-up">
                            <a href="<?= $base ?>index.php?r=/productos" class="btn-cart-action outline">
                                <i class="bi bi-arrow-left"></i>
                                <span>Continuar Comprando</span>
                            </a>
                            <button class="btn-cart-action danger" onclick="clearCart()">
                                <i class="bi bi-trash"></i>
                                <span>Vaciar Carrito</span>
                            </button>
                        </div>
                    </div>

                    <!-- CART SUMMARY -->
                    <div class="col-lg-4">
                        <div class="cart-summary-premium" data-aos="fade-left">
                            <h4 class="summary-title-premium">
                                <i class="bi bi-receipt me-2"></i>Resumen del Pedido
                            </h4>
                            
                            <div class="summary-details">
                                <div class="summary-row-premium">
                                    <span>Subtotal (<?= $total_quantity ?? 0 ?> productos)</span>
                                    <span>$<?= number_format($total_price ?? 0, 0, ',', '.') ?></span>
                                </div>
                                
                                <div class="summary-row-premium">
                                    <span>Envío</span>
                                    <span class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>GRATIS
                                    </span>
                                </div>
                                
                                <div class="summary-row-premium">
                                    <span>Descuento</span>
                                    <span>
                                        $<?= number_format($discount ?? 0, 0, ',', '.') ?>
                                        <?php if (!empty($_SESSION['coupon']['codigo'])): ?>
                                            <small class="text-success ms-1">
                                                (<?= htmlspecialchars($_SESSION['coupon']['codigo']) ?>)
                                            </small>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <?php if (!empty($_SESSION['coupon'])): ?>
                                    <div class="mt-2 text-end">
                                        <a href="<?= $base ?>index.php?r=/cart/remove-coupon"
                                           class="text-danger small">
                                            Quitar cupón
                                        </a>
                                    </div>
                                <?php endif; ?>

                            </div>
                            
                            <div class="summary-divider"></div>
                            
                            <div class="summary-total-premium">
                                <span>Total</span>
                                <span id="total-price">
                                    $<?= number_format($total_with_discount ?? $total_price ?? 0, 0, ',', '.') ?>
                                </span>
                            </div>
                            
                            <div class="summary-actions">
                                <a href="<?= $base ?>index.php?r=/checkout" class="btn-premium">
                                    <i class="bi bi-credit-card me-2"></i>
                                    Proceder al Checkout
                                </a>
                            </div>

                            <form action="<?= $base ?>index.php?r=/cart/apply-coupon" method="POST" class="mt-3">
                                <div class="input-group">
                                    <input type="text"
                                           name="coupon_code"
                                           class="form-control"
                                           placeholder="Ingresa tu cupón"
                                           required>
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bi bi-tag me-1"></i> Aplicar
                                    </button>
                                </div>

                                <?php if (!empty($_SESSION['coupon_message'])): ?>
                                    <small class="d-block mt-1 <?= strpos($_SESSION['coupon_message'], 'correctamente') !== false ? 'text-success' : 'text-danger' ?>">
                                        <?= htmlspecialchars($_SESSION['coupon_message']) ?>
                                    </small>
                                    <?php unset($_SESSION['coupon_message']); ?>
                                <?php endif; ?>
                            </form>
                            
                            <div class="summary-features">
                                <div class="feature-item">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Compra 100% Segura</span>
                                </div>
                                <div class="feature-item">
                                    <i class="bi bi-truck"></i>
                                    <span>Envío Gratis</span>
                                </div>
                                <div class="feature-item">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    <span>Devolución Fácil</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER - IGUAL QUE HOME -->
    <footer class="footer-modern">
        <div class="container-xl">
            <div class="footer-top">
                <div>
                    <div class="brand-logo mb-3">
                        <div class="brand-icon">
                            <i class="bi bi-phone-fill"></i>
                        </div>
                        <span class="brand-text">Movi<span style="color: var(--gray-500);">Cell</span></span>
                    </div>
                    <p class="footer-desc">
                        Tu tienda de confianza para smartphones premium con garantía real y servicio personalizado.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="https://wa.me/573135187288" class="social-link"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <div class="footer-col">
                        <h4>Tienda</h4>
                        <a href="<?= $base ?>">Inicio</a>
                        <a href="<?= $base ?>index.php?r=/productos">Productos</a>
                        <a href="<?= $base ?>index.php?r=/cart">Carrito</a>
                    </div>
                    <div class="footer-col">
                        <h4>Cuenta</h4>
                        <a href="<?= $base ?>index.php?r=/login">Iniciar Sesión</a>
                        <a href="<?= $base ?>index.php?r=/register">Registrarse</a>
                        <a href="<?= $base ?>index.php?r=/panel">Mi Panel</a>
                    </div>
                    <div class="footer-col">
                        <h4>Contacto</h4>
                        <p><i class="bi bi-geo-alt me-2"></i>Colombia</p>
                        <p><i class="bi bi-phone me-2"></i>+57 313 518 7288</p>
                        <p><i class="bi bi-envelope me-2"></i>info@movicell.com</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Movi Cell. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- FLOATING ELEMENTS -->
    <a href="https://wa.me/573135187288" class="whatsapp-float" target="_blank" aria-label="WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <button class="scroll-to-top" id="scrollToTop" aria-label="Volver arriba">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="<?= $base ?>assets/js/cart.js"></script>
    
    <script>
        const baseUrl = document.body.getAttribute('data-base-url') || '/';
        
        function updateQuantity(productId, newQty, maxStock = 10) {
            const qty = Math.max(1, Math.min(parseInt(newQty, 10) || 1, maxStock));
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', qty);
            formData.append('max_stock', maxStock);
            formData.append('ajax', '1');
            
            fetch(baseUrl + 'index.php?r=/cart/update', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.limited) {
                    alert(data.message);
                }
                if (data.actual_qty !== undefined) {
                    document.querySelector(`input[onchange*="${productId}"]`)?.setAttribute('value', data.actual_qty);
                }
                location.reload();
            })
            .catch(e => console.error('Error:', e));
        }
        
        function removeItem(productId) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('ajax', '1');
            
            fetch(baseUrl + 'index.php?r=/cart/remove', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(e => console.error('Error:', e));
        }
        
        function clearCart() {
            if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
                window.location.href = baseUrl + 'index.php?r=/cart/clear';
            }
        }
        
        document.getElementById('scrollToTop')?.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        window.addEventListener('scroll', () => {
            const btn = document.getElementById('scrollToTop');
            if (btn) {
                btn.style.display = window.pageYOffset > 300 ? 'flex' : 'none';
            }
        });
    </script>
</body>
</html>
