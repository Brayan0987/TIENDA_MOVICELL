<?php
// App/Views/productos.php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Core/Cart.php';
$cart = new App\Core\Cart();
$cartCount = $cart->getTotalQuantity();

// Conectar a la base de datos y obtener productos
require_once __DIR__ . '/../Core/conexion.php';
$con = conectar();

// Consultar los celulares desde la base de datos
$sql = "SELECT c.id_celulares, 
               (SELECT imagen_url 
                FROM imagenes_celulares 
                WHERE id_celulares = c.id_celulares AND es_principal = 1 
                LIMIT 1) AS imagen_url,
               p.nombre AS producto, 
               m.marca, 
               col.color, 
               r.ram, 
               a.almacenamiento, 
               pr.precio, 
               c.cantidad_stock AS cantidad
        FROM celulares c
        INNER JOIN producto p ON c.id_producto = p.id_producto
        INNER JOIN marcas m ON c.id_marcas = m.id_marcas
        INNER JOIN color col ON c.id_color = col.id_color
        INNER JOIN ram r ON c.id_ram = r.id_ram
        INNER JOIN almacenamiento a ON c.id_almacenamiento = a.id_almacenamiento
        INNER JOIN precio pr ON c.id_precio = pr.id_precio
        WHERE c.cantidad_stock > 0
        ORDER BY c.id_celulares DESC";

// Obtener el parámetro brand pero NO filtrar en PHP - se filtrará en JavaScript
$brand = '';
if (!empty($_GET['brand'])) {
  $brand = trim($_GET['brand']);
} elseif (!empty($_GET['search'])) {
  $brand = trim($_GET['search']);
}
// El filtrado ahora se hace en JavaScript, no en PHP
// Esto permite que cuando limpies los filtros, veas todos los productos

$query_celulares = mysqli_query($con, $sql);
$productos = [];
if ($query_celulares) {
  $productos = mysqli_fetch_all($query_celulares, MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Catálogo Premium - Movi Cell</title>
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
  <link href="/TIENDA_MOVICELL/Public/assets/Css/productos.css" rel="stylesheet">
  <link href="/TIENDA_MOVICELL/Public/assets/Css/search-button.css" rel="stylesheet">
</head>
<body data-base-url="<?= $base ?>">

  <!-- PRELOADER -->
  <div id="preloader">
    <div class="spinner-modern">
      <div class="spinner-ring"></div>
      <i class="bi bi-phone-fill"></i>
    </div>
  </div>

  <!-- NAVBAR -->
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
          <a href="<?= $base ?>index.php?r=/productos" class="nav-item active">
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

  <!-- OFFCANVAS MOBILE -->
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
        <a href="<?= $base ?>index.php?r=/productos" class="mobile-menu-item active">
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

  <!-- HERO SECTION -->
  <section class="hero-products">
    <div class="hero-overlay"></div>
    <div class="container-xl">
      <div class="hero-content" data-aos="fade-up">
        <div class="hero-badge">
          <i class="bi bi-grid-3x3-gap"></i>
          <span>Catálogo Completo 2025</span>
        </div>
        <h1 class="hero-title">
          Encuentra tu próximo<br>
          <span class="gradient-text">Smartphone</span>
        </h1>
        <p class="hero-subtitle">
          Explora nuestra colección completa de dispositivos móviles con tecnología avanzada y el mejor rendimiento del mercado
        </p>
        <div class="hero-actions">
          <a href="#productos" class="btn-hero primary">
            <span>Ver Productos</span>
            <i class="bi bi-arrow-down"></i>
          </a>
          <a href="<?= $base ?>index.php?r=/cart" class="btn-hero outline">
            <i class="bi bi-bag"></i>
            <span>Mi Carrito</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- SEARCH -->
  <div class="container-xl" style="margin-top: -3rem; position: relative; z-index: 100;">
    <div class="search-premium" data-aos="fade-up">
      <div class="position-relative">
        <i class="bi bi-search search-icon-premium"></i>
        <form id="searchForm" onsubmit="return false;" style="display: flex; gap: 8px; width: 100%;">
          <input id="searchInput" class="search-input-premium" 
            type="text" 
            placeholder="Busca tu smartphone ideal... Samsung, iPhone, Xiaomi..."
            value="<?= isset($brand) && $brand !== '' ? htmlspecialchars($brand) : '' ?>"
            style="flex: 1;">
          
        </form>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="container-xl pb-5" id="productos">
    <div class="row g-4">
      <!-- FILTERS -->
      <div class="col-lg-3">
        <div class="filters-premium" data-aos="fade-right">
          <h5 class="filter-title-premium">
            <i class="bi bi-funnel-fill"></i>Filtros
          </h5>
          
          <div class="filter-section-premium">
            <label class="filter-label-premium">Marca</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="samsung" name="brand" value="samsung">
              <label class="form-check-label" for="samsung">Samsung</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="apple" name="brand" value="apple">
              <label class="form-check-label" for="apple">Apple</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="xiaomi" name="brand" value="xiaomi">
              <label class="form-check-label" for="xiaomi">Xiaomi</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="oneplus" name="brand" value="oneplus">
              <label class="form-check-label" for="oneplus">OnePlus</label>
            </div>
          </div>
          
          <div class="filter-section-premium">
            <label class="filter-label-premium">Almacenamiento</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="128gb" name="storage" value="128">
              <label class="form-check-label" for="128gb">128GB</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="256gb" name="storage" value="256">
              <label class="form-check-label" for="256gb">256GB</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="512gb" name="storage" value="512">
              <label class="form-check-label" for="512gb">512GB</label>
            </div>
          </div>
          
          <div class="filter-section-premium">
            <label class="filter-label-premium">Rango de Precio (COP)</label>
            <div class="row g-2">
              <div class="col-6">
                <input id="minPrice" class="form-control-premium" type="number" placeholder="Mínimo" min="0" step="100000">
              </div>
              <div class="col-6">
                <input id="maxPrice" class="form-control-premium" type="number" placeholder="Máximo" min="0" step="100000">
              </div>
            </div>
            <div class="d-grid gap-2 mt-3">
              <button id="btnApplyPrice" class="btn-premium" type="button">
                <i class="bi bi-check-circle me-1"></i>Aplicar Filtros
              </button>
              <button id="btnClear" class="btn-premium outline" type="button">
                <i class="bi bi-arrow-clockwise me-1"></i>Limpiar Todo
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- PRODUCTS -->
      <div class="col-lg-9">
        <div class="results-header-premium" data-aos="fade-left">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="results-count-premium">
              <i class="bi bi-grid-3x3-gap me-2"></i>
              <span id="totalSpan"><?= count($productos) ?></span> productos encontrados
            </div>
            <div class="d-flex align-items-center gap-2">
              <label class="text-secondary mb-0 small">Ordenar:</label>
              <select id="sortSelect" class="form-select-premium" style="width: auto;">
                <option value="relevance">Relevancia</option>
                <option value="newest">Más nuevos</option>
                <option value="price_asc">Menor precio</option>
                <option value="price_desc">Mayor precio</option>
              </select>
            </div>
          </div>
        </div>
        
        <div id="grid" class="row g-4">
          <?php if (count($productos) > 0): ?>
            <?php foreach($productos as $index => $producto): ?>
              <div class="col-md-6 col-xl-4" 
                   data-aos="fade-up" 
                   data-aos-delay="<?= ($index % 3) * 100 + 100 ?>">
                <div class="product-card-premium" data-brand="<?= htmlspecialchars(strtolower($producto['marca'])) ?>">
                  <!-- Imagen + click a detalle -->
                  <div class="position-relative overflow-hidden"
                       onclick="window.location.href='<?= $base ?>index.php?r=/producto-detalle&id=<?= (int)$producto['id_celulares'] ?>'"
                       style="cursor: pointer;">
                    <?php 
                      $imagen = !empty($producto['imagen_url']) 
                          ? $base . $producto['imagen_url'] 
                          : $base . 'assets/Imagenes/default.jpg';
                    ?>
                    <img src="<?= htmlspecialchars($imagen) ?>" 
                         class="product-image-premium" 
                         alt="<?= htmlspecialchars($producto['producto'] . ' ' . $producto['marca']) ?>">
                    <?php if ($producto['cantidad'] > 0 && $producto['cantidad'] < 5): ?>
                      <div class="product-badge-premium badge-sale-premium">Últimas unidades</div>
                    <?php elseif ($index < 3): ?>
                      <div class="product-badge-premium badge-new-premium">Nuevo</div>
                    <?php endif; ?>
                  </div>

                  <div class="product-body-premium">
                    <h5 class="product-title-premium">
                      <?= htmlspecialchars($producto['marca'] . ' ' . $producto['producto']) ?>
                    </h5>
                    <p class="product-specs-premium">
                      <?= htmlspecialchars($producto['almacenamiento']) ?> • 
                      <?= htmlspecialchars($producto['ram']) ?> • 
                      <?= htmlspecialchars($producto['color']) ?>
                    </p>
                    <div class="product-price-container-premium mb-2">
                      <div class="product-price-premium">
                        $<?= number_format($producto['precio'], 0, ',', '.') ?>
                      </div>
                    </div>

                    <!-- BOTÓN AGREGAR AL CARRITO -->
                    <form method="post" action="<?= $base ?>index.php?r=/cart/add">
                      <input type="hidden" name="product_id" value="<?= (int)$producto['id_celulares'] ?>">
                      <input type="hidden" name="name"       value="<?= htmlspecialchars($producto['marca'] . ' ' . $producto['producto']) ?>">
                      <input type="hidden" name="price"      value="<?= (float)$producto['precio'] ?>">
                      <input type="hidden" name="image"      value="<?= htmlspecialchars($producto['imagen_url'] ?? '') ?>">
                      <input type="hidden" name="quantity"   value="1">
                      <button type="submit" class="btn-premium w-100">
                        <i class="bi bi-cart-plus"></i>
                        Añadir al carrito
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="alert alert-info text-center p-5" role="alert">
                <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
                <h5>No hay productos disponibles</h5>
                <p class="mb-0">Actualmente no hay productos en el catálogo. Por favor, vuelve más tarde.</p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- CTA SECTION -->
  <section class="cta-section">
    <div class="cta-section::before"></div>
    <div class="container-xl">
      <div class="cta-content" data-aos="fade-up">
        <div class="cta-text">
          <h3 class="cta-title">¿Necesitas asesoría personalizada?</h3>
          <p class="cta-subtitle">
            Nuestros expertos te ayudan a encontrar el smartphone perfecto según tus necesidades específicas
          </p>
        </div>
        <div class="cta-actions">
          <a href="https://wa.me/573135187288" target="_blank" class="btn-cta primary">
            <i class="bi bi-whatsapp"></i>
            <span>Contactar Asesor</span>
          </a>
          <a href="<?= $base ?>index.php?r=/cart" class="btn-cta outline">
            <i class="bi bi-bag"></i>
            <span>Ver Mi Carrito</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
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
  <script src="/TIENDA_MOVICELL/Public/assets/JS/productos.js"></script>
  <script>
    // Asegurar que los filtros se apliquen al cargar la página
    (function() {
      function tryInit() {
        if (typeof applyAllFilters === 'function') {
          try { applyAllFilters(); } catch(e) { console.error('init filtro:', e); }
        } else {
          // si aún no existe, intentar después
          setTimeout(tryInit, 150);
        }
      }
      tryInit();
    })();
    
    // Validar carrito al agregar (máximo 10 unidades)
    document.querySelectorAll('form[action*="/cart/add"]').forEach(form => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('ajax', '1');
        
        try {
          // Hacer solicitud AJAX para validar
          const response = await fetch(form.getAttribute('action'), {
            method: 'POST',
            body: formData,
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          
          // Intentar parsear JSON
          const text = await response.text();
          let data = null;
          
          try {
            data = JSON.parse(text);
          } catch(e) {
            console.log('Respuesta no es JSON:', text);
          }
          
          if (data && data.login_required) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '350px';
            alertDiv.innerHTML = `
              <i class="bi bi-exclamation-octagon-fill me-2"></i>
              <strong>Debes iniciar sesión</strong>
              <p class="mb-0 mt-2" style="font-size: 0.95rem;">${data.message || 'Inicia sesión para agregar productos al carrito.'}</p>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.insertBefore(alertDiv, document.body.firstChild);
            setTimeout(() => { if (alertDiv.parentNode) alertDiv.remove(); }, 3500);
            // redirigir al login
            setTimeout(() => { window.location.href = '<?= $base ?>index.php?r=/login'; }, 900);
            return false;
          } else if (data && data.limited) {
            // Mostrar alerta visual de ERROR (rojo)
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '350px';
            alertDiv.innerHTML = `
              <i class="bi bi-exclamation-octagon-fill me-2"></i>
              <strong>¡Error: Límite de cantidad alcanzado!</strong>
              <p class="mb-0 mt-2" style="font-size: 0.95rem;">${data.message}</p>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.insertBefore(alertDiv, document.body.firstChild);
            
            // Auto-remover después de 4 segundos
            setTimeout(() => {
              if (alertDiv.parentNode) {
                alertDiv.remove();
              }
            }, 4000);
            
            return false;
          } else if (data && data.success) {
            // Éxito - redirigir al carrito
            window.location.href = '<?= $base ?>index.php?r=/cart';
          } else {
            // Sin respuesta JSON, enviar formulario normal
            form.submit();
          }
        } catch (error) {
          console.error('Error al agregar al carrito:', error);
          // Permitir envío normal si hay error
          form.submit();
        }
      });
    });
  </script>
</body>
</html>
