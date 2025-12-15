<?php
// App/Views/home.php - DISEÑO RENOVADO PROFESIONAL
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Core/Cart.php';
$cart = new App\Core\Cart();
$cartCount = $cart->getTotalQuantity();

require_once __DIR__ . '/../Core/Db.php';
$db = App\Core\Db::conn();

$stmt = $db->prepare('
  SELECT 
    c.id_celulares,
    (SELECT imagen_url FROM imagenes_celulares WHERE id_celulares = c.id_celulares AND es_principal = 1 LIMIT 1) AS imagen_url,
    p.nombre,
    pr.precio,
    m.marca,
    c.cantidad_stock as stock
  FROM celulares c
  LEFT JOIN producto p ON c.id_producto = p.id_producto
  LEFT JOIN precio pr ON c.id_precio = pr.id_precio
  LEFT JOIN marcas m ON c.id_marcas = m.id_marcas
  WHERE c.cantidad_stock > 0
  ORDER BY c.id_celulares DESC
  LIMIT 6
');
$stmt->execute();
$featuredProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Movi Cell - Tecnología al Alcance de Todos</title>
  <meta name="description" content="Los mejores smartphones del mercado con garantía y atención personalizada">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="<?= $base ?>assets/css/home.css" rel="stylesheet">
  <!-- AOS CSS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body data-base-url="<?= $base ?>">

  <!-- PRELOADER -->
  <div id="preloader">
    <div class="spinner-modern">
      <div class="spinner-ring"></div>
      <i class="bi bi-phone-fill"></i>
    </div>
  </div>

  <!-- NAVBAR GLASS EFFECT -->
  <nav class="navbar-glass" id="mainNavbar">
    <div class="container-xl">
      <div class="navbar-content">
        <a class="brand-logo" href="<?= $base ?>">
          <div class="brand-icon">
            <i class="bi bi-phone-fill"></i>
          </div>
          <span class="brand-text">Movi<span class="text-primary">Cell</span></span>
        </a>
        
        <div class="nav-links d-none d-lg-flex">
          <a href="<?= $base ?>" class="nav-item active">
            <i class="bi bi-house-door"></i>
            <span>Inicio</span>
          </a>
          <a href="<?= $base ?>index.php?r=/productos" class="nav-item">
            <i class="bi bi-grid-3x3"></i>
            <span>Catálogo</span>
          </a>
          <a href="<?= $base ?>#footer" class="nav-item">
            <i class="bi bi-envelope"></i>
            <span>Contacto</span>
          </a>
        </div>
        
        <div class="nav-actions d-none d-lg-flex">
          <?php if (!empty($_SESSION['user_id'])): ?>
            <div class="user-menu">
              <button class="btn-user" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
                <span><?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Usuario')[0]) ?></span>
                <i class="bi bi-chevron-down ms-1"></i>
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
        
        <button class="btn-mobile-menu d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
          <i class="bi bi-list"></i>
        </button>
      </div>
    </div>
  </nav>

  <!-- OFFCANVAS MOBILE -->
  <div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">
        <i class="bi bi-phone-fill me-2"></i>Movi Cell
      </h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <div class="mobile-menu">
        <a href="<?= $base ?>" class="mobile-menu-item active">
          <i class="bi bi-house-door"></i>
          <span>Inicio</span>
        </a>
        <a href="<?= $base ?>index.php?r=/productos" class="mobile-menu-item">
          <i class="bi bi-grid-3x3"></i>
          <span>Catálogo</span>
        </a>
        <a href="<?= $base ?>index.php?r=/cart" class="mobile-menu-item">
          <i class="bi bi-bag"></i>
          <span>Carrito</span>
          <?php if ($cartCount > 0): ?>
            <span class="badge bg-primary ms-auto"><?= $cartCount ?></span>
          <?php endif; ?>
        </a>
      </div>
      
      <div class="mobile-auth">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <div class="user-info">
            <i class="bi bi-person-circle"></i>
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

  <!-- HERO MODERN -->
  <section class="hero-modern">
    <video class="hero-video" autoplay muted loop playsinline>
      <source src="<?= $base ?>assets/Imagenes/video-k-con-imagenes-variadas_XINZZqk5.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="container-xl">
      <div class="hero-content">
        <div class="hero-badge" data-aos="fade-down">
          <i class="bi bi-stars"></i>
          <span>Nueva Colección 2025</span>
        </div>
        <h1 class="hero-title" data-aos="fade-up">
          Encuentra tu próximo<br>
          <span class="gradient-text">Smartphone</span>
        </h1>
        <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="100">
          La mejor tecnología móvil con garantía real y asesoría experta
        </p>
        <div class="hero-actions" data-aos="fade-up" data-aos-delay="200">
          <a href="<?= $base ?>index.php?r=/productos" class="btn-hero primary">
            <span>Explorar Catálogo</span>
            <i class="bi bi-arrow-right"></i>
          </a>
          <a href="https://wa.me/573135187288?text=Hola,%20quiero%20más%20información" target="_blank" class="btn-hero outline">
            <i class="bi bi-whatsapp"></i>
            <span>Contactar</span>
          </a>
        </div>
        
        <div class="hero-stats" data-aos="fade-up" data-aos-delay="300">
          <div class="stat-item">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-content">
              <span class="stat-number">500+</span>
              <span class="stat-label">Productos</span>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-content">
              <span class="stat-number">2K+</span>
              <span class="stat-label">Clientes</span>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
            <div class="stat-content">
              <span class="stat-number">4.8</span>
              <span class="stat-label">Rating</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="hero-scroll-indicator">
      <span>Desliza para ver más</span>
      <i class="bi bi-chevron-down"></i>
    </div>
  </section>

  <!-- QUICKVIEW MODAL (contenido cargado por AJAX) -->
  <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Vista rápida</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="quickViewBody">
          <!-- Contenido cargado dinámicamente -->
          <div class="text-center p-4">Cargando...</div>
        </div>
      </div>
    </div>
  </div>

  <!-- MARCAS / CATEGORÍAS -->
  <section class="brands-section">
    <div class="container-xl">
      <div class="section-header text-center">
        <span class="section-tag">Nuestras Marcas</span>
        <h2 class="section-title">Explora por Fabricante</h2>
      </div>
      <div class="brands-grid">
        <?php
          $brands = [
            ['name' => 'Samsung', 'icon' => 'phone', 'color' => '#1428a0'],
            ['name' => 'Apple', 'icon' => 'apple', 'color' => '#000000'],
            ['name' => 'Xiaomi', 'icon' => 'phone', 'color' => '#ff6700'],
            ['name' => 'OnePlus', 'icon' => 'phone', 'color' => '#eb0029'],
            ['name' => 'Google', 'icon' => 'google', 'color' => '#4285f4'],
            ['name' => 'Motorola', 'icon' => 'phone', 'color' => '#5c92fa'],
          ];
          foreach ($brands as $brand):
        ?>
        <a href="<?= $base ?>index.php?r=/productos&brand=<?= urlencode($brand['name']) ?>" class="brand-card" style="--brand-color: <?= $brand['color'] ?>">
          <div class="brand-icon">
            <i class="bi bi-<?= $brand['icon'] ?>"></i>
          </div>
          <span class="brand-name"><?= $brand['name'] ?></span>
          <i class="bi bi-arrow-right brand-arrow"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- PRODUCTOS DESTACADOS -->
  <section id="productos" class="products-section">
    <div class="container-xl">
      <div class="section-header">
        <div>
          <span class="section-tag">Lo Mejor</span>
          <h2 class="section-title">Productos Destacados</h2>
        </div>
        <a href="<?= $base ?>index.php?r=/productos" class="btn-link">
          Ver todo <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      
      <div class="products-grid">
        <?php if (!empty($featuredProducts)): ?>
          <?php foreach ($featuredProducts as $index => $product): ?>
            <article class="product-card" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
              <div class="product-image">
                <?php
                  $imgPath = !empty($product['imagen_url']) 
                    ? $base . $product['imagen_url'] 
                    : $base . 'assets/Imagenes/A' . ((($index % 3) + 1)) . '.jpg';
                ?>
                <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($product['nombre']) ?>">
                <div class="product-badges">
                  <?php if ($product['stock'] <= 5): ?>
                    <span class="badge-stock">Pocas unidades</span>
                  <?php endif; ?>
                </div>
                <button class="btn-quick-view" onclick="quickView(<?= (int)$product['id_celulares'] ?>)">
                  <i class="bi bi-eye"></i>
                  Vista rápida
                </button>
              </div>
              <div class="product-info">
                <span class="product-brand"><?= htmlspecialchars($product['marca']) ?></span>
                <h3 class="product-name"><?= htmlspecialchars($product['nombre']) ?></h3>
                <div class="product-footer">
                  <span class="product-price">$<?= number_format($product['precio'], 0, ',', '.') ?></span>

                  <!-- FORMULARIO CORREGIDO PARA AGREGAR AL CARRITO -->
                  <form method="post" action="<?= $base ?>index.php?r=/cart/add" class="add-to-cart-form" style="margin:0">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id_celulares'] ?>">
                    <input type="hidden" name="name"       value="<?= htmlspecialchars($product['marca'] . ' ' . $product['nombre']) ?>">
                    <input type="hidden" name="price"      value="<?= (float)$product['precio'] ?>">
                    <input type="hidden" name="image"      value="<?= htmlspecialchars($product['imagen_url'] ?? '') ?>">
                    <input type="hidden" name="quantity"   value="1">
                    <button type="submit" class="btn-add-cart">
                      <i class="bi bi-bag-plus"></i>
                    </button>
                  </form>
                  <!-- FIN FORMULARIO -->
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="mt-3">Próximamente nuevos productos</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- BENEFICIOS -->
  <section class="benefits-section">
    <div class="container-xl">
      <div class="benefits-grid">
        <div class="benefit-card" data-aos="fade-right">
          <div class="benefit-icon" style="--icon-color: #10b981">
            <i class="bi bi-shield-check"></i>
          </div>
          <div class="benefit-content">
            <h3 class="benefit-title">Garantía Real</h3>
            <p class="benefit-text">Cobertura completa en todos nuestros productos</p>
          </div>
        </div>
        
        <div class="benefit-card" data-aos="fade-up">
          <div class="benefit-icon" style="--icon-color: #3b82f6">
            <i class="bi bi-truck"></i>
          </div>
          <div class="benefit-content">
            <h3 class="benefit-title">Envíos Rápidos</h3>
            <p class="benefit-text">Entrega en 24-48 horas a todo el país</p>
          </div>
        </div>
        
        <div class="benefit-card" data-aos="fade-left">
          <div class="benefit-icon" style="--icon-color: #f59e0b">
            <i class="bi bi-headset"></i>
          </div>
          <div class="benefit-content">
            <h3 class="benefit-title">Soporte 24/7</h3>
            <p class="benefit-text">Asistencia técnica siempre disponible</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA SECTION -->
  <section class="cta-section">
    <div class="container-xl">
      <div class="cta-content">
        <div class="cta-text">
          <h2 class="cta-title">¿Necesitas ayuda para elegir?</h2>
          <p class="cta-subtitle">Nuestro equipo está listo para asesorarte</p>
        </div>
        <div class="cta-actions">
          <a href="https://wa.me/573135187288" target="_blank" class="btn-cta primary">
            <i class="bi bi-whatsapp"></i>
            <span>Chatear Ahora</span>
          </a>
          <a href="tel:+573135187288" class="btn-cta outline">
            <i class="bi bi-telephone"></i>
            <span>Llamar</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer id="footer" class="footer-modern">
    <div class="container-xl">
      <div class="footer-top">
        <div class="footer-brand">
          <div class="brand-logo">
            <div class="brand-icon">
              <i class="bi bi-phone-fill"></i>
            </div>
            <span class="brand-text">Movi<span class="text-primary">Cell</span></span>
          </div>
          <p class="footer-desc">Tu tienda de confianza para tecnología móvil de última generación</p>
          <div class="footer-social">
            <a href="https://instagram.com/movicell" target="_blank" class="social-link instagram">
              <i class="bi bi-instagram"></i>
            </a>
            <a href="https://facebook.com/movicell" target="_blank" class="social-link facebook">
              <i class="bi bi-facebook"></i>
            </a>
            <a href="https://wa.me/573135187288" target="_blank" class="social-link whatsapp">
              <i class="bi bi-whatsapp"></i>
            </a>
          </div>
        </div>
        
        <div class="footer-links">
          <div class="footer-col">
            <h4>Tienda</h4>
            <a href="<?= $base ?>index.php?r=/productos">Catálogo</a>
             <a href="#productos">Ofertas</a>
            <a href="<?= $base ?>index.php?r=/cart">Carrito</a>
          </div>
          
          <div class="footer-col">
            <h4>Soporte</h4>
            <a href="https://wa.me/573135187288">WhatsApp</a>
            <a href="tel:+573135187288">Teléfono</a>
            <a href="#">Preguntas</a>
          </div>
          
          <div class="footer-col">
            <h4>Contacto</h4>
            <p><i class="bi bi-geo-alt"></i> Centro Comercial Local 123</p>
            <p><i class="bi bi-clock"></i> Lun - Sáb: 9AM - 7PM</p>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>© <?= date('Y') ?> Movi Cell. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>

  <!-- WHATSAPP FLOAT -->
  <a href="https://wa.me/573135187288" class="whatsapp-float" target="_blank">
    <i class="bi bi-whatsapp"></i>
  </a>

  <!-- SCROLL TO TOP -->
  <button id="scrollTop" class="scroll-to-top">
    <i class="bi bi-arrow-up"></i>
  </button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="<?= $base ?>assets/js/home.js"></script>
</body>
</html>
