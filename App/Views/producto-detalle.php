<?php
// App/Views/producto-detalle.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Variables del controlador
$product   = $product   ?? [];
$related   = $related   ?? [];
$cartCount = $cartCount ?? 0;
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";

// Datos del producto
$stock    = (int)($product['stock'] ?? 0);
$hasStock = $stock > 0;
// Si no hay imágenes en el array 'images', usa la imagen principal
$images   = $product['images'] ?? [$product['imagen'] ?? 'assets/Imagenes/placeholder.jpg'];

// Normalizar rutas de imágenes: anteponer $base si la ruta es relativa
foreach ($images as $i => $imgPath) {
    if (!$imgPath) continue;
    $trim = ltrim($imgPath, " ");
    if (!preg_match('#^(https?:)?//#i', $trim) && strpos($trim, '/') !== 0) {
        $images[$i] = $base . ltrim($trim, '/');
    } else {
        $images[$i] = $trim;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($product['nombre'] ?? 'Producto') ?> - Movi Cell</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="<?= $base ?>assets/css/home.css" rel="stylesheet"> 
<link href="<?= $base ?>assets/css/producto-detalle.css" rel="stylesheet">
</head>
<body>

<nav class="navbar-custom">
  <div class="container-xl d-flex justify-content-between align-items-center px-4">
    
    <a class="navbar-brand" href="<?= $base ?>">
        <i class="bi bi-phone-fill"></i>
        <span>Movi<span style="color: var(--gray-400);">Cell</span></span>
    </a>
    
    <div class="d-none d-lg-flex align-items-center">
        <div class="d-flex gap-3 me-5">
            <a href="<?= $base ?>index.php?r=/" class="nav-link">Inicio</a>
            <a href="<?= $base ?>index.php?r=/productos" class="nav-link active">Catálogo</a>
            <a href="<?= $base ?>#footer" class="nav-link">Contacto</a>
        </div>
        
        <div class="d-flex gap-3 align-items-center">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <button class="btn text-white dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Usuario')[0]) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $base ?>index.php?r=/panel">Mi Panel</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $base ?>index.php?r=/logout">Salir</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= $base ?>index.php?r=/panel" class="nav-link text-white"><i class="bi bi-person me-1"></i> Panel</a>
            <?php endif; ?>

            <a href="<?= $base ?>index.php?r=/cart" class="btn-cart">
              <i class="bi bi-bag"></i>
              <?php if ($cartCount > 0): ?>
                <span class="ms-1"><?= (int)$cartCount ?></span>
              <?php endif; ?>
            </a>
        </div>
    </div>

    <button class="btn text-white d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
        <i class="bi bi-list fs-2"></i>
    </button>
  </div>
</nav>

<div class="offcanvas offcanvas-end bg-dark text-white" tabindex="-1" id="mobileMenu">
  <div class="offcanvas-header border-bottom border-secondary">
    <h5 class="offcanvas-title">Menú</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div class="d-grid gap-3">
        <a href="<?= $base ?>" class="btn btn-outline-light text-start border-0"><i class="bi bi-house me-2"></i>Inicio</a>
        <a href="<?= $base ?>index.php?r=/productos" class="btn btn-outline-light text-start border-0"><i class="bi bi-grid me-2"></i>Catálogo</a>
        <a href="<?= $base ?>index.php?r=/cart" class="btn btn-outline-light text-start border-0"><i class="bi bi-bag me-2"></i>Carrito (<?= $cartCount ?>)</a>
    </div>
  </div>
</div>

<div class="container my-5 pt-4">
    <div class="breadcrumb-wrapper">
        <nav class="breadcrumb-custom">
            <a href="<?= $base ?>"><i class="bi bi-house-door me-1"></i>Inicio</a>
            <span class="mx-2 text-muted">/</span>
            <a href="<?= $base ?>index.php?r=/productos">Productos</a>
            <span class="mx-2 text-muted">/</span>
            <span class="fw-bold text-dark"><?= htmlspecialchars($product['nombre'] ?? 'Detalle') ?></span>
        </nav>
    </div>
    
    <div class="product-container">
        <div class="row g-0"> 
            
            <div class="col-lg-6 product-gallery-col">
                <div class="sticky-gallery">
                    <span class="badge-stock <?= !$hasStock ? 'badge-out-of-stock' : '' ?>">
                        <?= $hasStock ? 'En Stock' : 'Agotado' ?>
                    </span>

                    <div class="product-image-wrapper">
                        <div id="carouselProduct" class="carousel slide carousel-fade" data-bs-interval="false">
                            <div class="carousel-inner">
                                <?php foreach ($images as $idx => $img): ?>
                                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100 product-image" alt="Producto">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($images) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduct" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon filter-dark" aria-hidden="true" style="filter: invert(1);"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselProduct" data-bs-slide="next">
                                    <span class="carousel-control-next-icon filter-dark" aria-hidden="true" style="filter: invert(1);"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (count($images) > 1): ?>
                        <div class="carousel-thumbs-wrapper">
                            <?php foreach ($images as $idx => $img): ?>
                                <button type="button" class="carousel-thumb-btn <?= $idx === 0 ? 'active' : '' ?>" 
                                        data-bs-target="#carouselProduct" 
                                        data-bs-slide-to="<?= $idx ?>">
                                    <img src="<?= htmlspecialchars($img) ?>" class="carousel-thumb-img">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6 product-info-col">
                <div class="product-brand">
                    <?= htmlspecialchars($product['marca'] ?? 'MoviCell') ?>
                </div>

                <h1 class="product-title">
                    <?= htmlspecialchars($product['nombre'] ?? 'Nombre del Producto') ?>
                </h1>
                
                <div class="product-price">
                    $<?= number_format((float)($product['precio'] ?? 0), 0, ',', '.') ?>
                    <small>COP</small>
                </div>

                <div class="specs-section">
                    <div class="specs-title">Especificaciones Técnicas</div>
                    <div class="specs-grid">
                        <?php if (!empty($product['ram_gb'])): ?>
                            <div class="spec-card">
                                <i class="bi bi-memory"></i>
                                <span class="label">RAM</span>
                                <span class="value"><?= htmlspecialchars($product['ram_gb']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['almacenamiento_gb'])): ?>
                            <div class="spec-card">
                                <i class="bi bi-hdd"></i>
                                <span class="label">Memoria</span>
                                <span class="value"><?= htmlspecialchars($product['almacenamiento_gb']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['color'])): ?>
                            <div class="spec-card">
                                <i class="bi bi-palette"></i>
                                <span class="label">Color</span>
                                <span class="value"><?= htmlspecialchars($product['color']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['pantalla'])): ?>
                            <div class="spec-card">
                                <i class="bi bi-phone"></i>
                                <span class="label">Pantalla</span>
                                <span class="value"><?= htmlspecialchars($product['pantalla']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="cart-actions-box">
                    <form action="<?= $base ?>index.php?r=/cart/add" method="POST" id="addToCartForm" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?= (int)($product['id_celulares'] ?? 0) ?>">
                        <input type="hidden" name="name"       value="<?= htmlspecialchars($product['nombre'] ?? '') ?>">
                        <input type="hidden" name="price"      value="<?= (float)($product['precio'] ?? 0) ?>">
                        <input type="hidden" name="image"      value="<?= htmlspecialchars($product['imagen'] ?? '') ?>">

                        <div class="quantity-row">
                            <label>Cantidad:</label>
                            <input type="number" 
                                   name="quantity" 
                                   class="quantity-input" 
                                   value="1" 
                                   min="1" 
                                   max="<?= $stock ?>" 
                                   <?= !$hasStock ? 'disabled' : '' ?>>
                        </div>

                        <button type="submit" class="btn-add-to-cart" <?= !$hasStock ? 'disabled' : '' ?>>
                            <?php if($hasStock): ?>
                                <i class="bi bi-cart-plus-fill"></i> Agregar al Carrito
                            <?php else: ?>
                                <i class="bi bi-x-circle"></i> Agotado
                            <?php endif; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($product['descripcion'])): ?>
        <div class="description-container">
            <h3><i class="bi bi-text-paragraph me-2"></i>Descripción Detallada</h3>
            <p><?= nl2br(htmlspecialchars($product['descripcion'])) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($related)): ?>
        <div class="related-section mt-5">
            <h3 class="fw-bold mb-4 text-center">También te podría interesar</h3>
            <div class="row g-4">
                <?php foreach ($related as $rel): ?>
                    <div class="col-6 col-md-3">
                        <?php
                            $relImg = $rel['imagen'] ?? '';
                            if ($relImg) {
                                if (!preg_match('#^(https?:)?//#i', $relImg) && strpos($relImg, '/') !== 0) {
                                    $relImg = $base . ltrim($relImg, '/');
                                }
                            } else {
                                $relImg = $base . 'assets/Imagenes/default.jpg';
                            }
                        ?>
                        <a href="<?= $base ?>index.php?r=/producto-detalle&id=<?= (int)$rel['id_celulares'] ?>" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px; overflow:hidden;">
                                <div class="p-3 bg-light text-center">
                                    <img src="<?= htmlspecialchars($relImg) ?>" style="height: 150px; object-fit: contain;" alt="Rel">
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title text-dark fw-bold"><?= htmlspecialchars($rel['nombre']) ?></h6>
                                    <div class="text-primary fw-bold">$<?= number_format((float)$rel['precio'], 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>assets/js/producto-detalle.js"></script>
</body>
</html>