<?php
require_once __DIR__ . '/../../Controllers/Admin/insertar_producto_controller.php';
require_once __DIR__ . '/../../../includes/layout.php';

renderHeader(
    'Insertar Producto',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/insertar_producto.css?v='.time().'">'
);
?>

<main class="insertar-container">

    <!-- Header del módulo -->
    <header class="insertar-header">
        <div class="header-icon">
            <i class="bi bi-plus-square-fill"></i>
        </div>
        <div class="header-text">
            <h1 class="header-title">Agregar Nuevos Productos</h1>
            <p class="header-subtitle">Registra tipos de producto y celulares en el catálogo</p>
        </div>
    </header>

    <!-- Alertas -->
    <?php if (!empty($msg)): ?>
        <div class="alert-custom alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($msg); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['cel_added'])): ?>
        <div class="alert-custom alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>Celular creado correctamente.</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['added'])): ?>
        <div class="alert-custom alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>Tipo de producto registrado correctamente.</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && isset($_GET['msg'])): ?>
        <div class="alert-custom alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars(urldecode($_GET['msg'])); ?></span>
        </div>
    <?php endif; ?>

    <!-- Grid de formularios -->
    <div class="forms-grid">

        <!-- CARD 1: Tipo de Producto -->
        <article class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="bi bi-tag-fill"></i>
                </div>
                <div class="form-card-title-group">
                    <h2 class="form-card-title">Tipo de Producto</h2>
                    <p class="form-card-desc">Registra una nueva categoría</p>
                </div>
            </div>

            <form method="POST" action="" class="form-body">
                <input type="hidden" name="guardar_producto" value="1">

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-tag"></i>
                        Nombre
                    </label>
                    <input type="text"
                           name="nombre"
                           class="form-input"
                           placeholder="Ej: Smartphone"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-text-paragraph"></i>
                        Descripción
                    </label>
                    <textarea name="descripcion"
                              class="form-textarea"
                              placeholder="Descripción del tipo de producto"
                              rows="4"
                              required></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-save"></i>
                    Guardar Tipo
                </button>
            </form>
        </article>

        <!-- CARD 2: Agregar Celular -->
        <article class="form-card form-card-main">
            <div class="form-card-header">
                <div class="form-card-icon icon-primary">
                    <i class="bi bi-phone-fill"></i>
                </div>
                <div class="form-card-title-group">
                    <h2 class="form-card-title">Agregar Celular</h2>
                    <p class="form-card-desc">Completa los datos del smartphone</p>
                </div>
            </div>

            <form method="POST" action="" enctype="multipart/form-data" class="form-body">
                <input type="hidden" name="guardar" value="1">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-box"></i>
                            Producto
                        </label>
                        <select name="producto" class="form-select" required>
                            <?= getOptions('producto'); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-award"></i>
                            Marca
                        </label>
                        <select name="marca" class="form-select" required>
                            <?= getOptions('marcas'); ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-palette"></i>
                            Color
                        </label>
                        <select name="color" class="form-select" required>
                            <?= getOptions('color'); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-memory"></i>
                            RAM
                        </label>
                        <select name="ram" class="form-select" required>
                            <?= getOptions('ram'); ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-hdd"></i>
                            Almacenamiento
                        </label>
                        <select name="almacenamiento" class="form-select" required>
                            <?= getOptions('almacenamiento'); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-currency-dollar"></i>
                            Precio
                        </label>
                        <select name="precio" class="form-select" required>
                            <?= getOptions('precio'); ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-box-seam"></i>
                        Cantidad en Stock
                    </label>
                    <input type="number"
                           name="cantidad"
                           class="form-input"
                           placeholder="Ej: 10"
                           min="1"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-images"></i>
                        Imágenes del Celular
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file"
                               name="imagenes[]"
                               id="file-input"
                               class="file-input"
                               accept="image/*"
                               multiple>
                        <label for="file-input" class="file-label">
                            <i class="bi bi-cloud-upload"></i>
                            <span>Seleccionar imágenes</span>
                        </label>
                        <small class="file-hint">La primera imagen será la principal</small>
                    </div>
                </div>

                <button type="submit" class="btn-submit btn-submit-primary">
                    <i class="bi bi-plus-circle"></i>
                    Guardar Celular
                </button>
            </form>
        </article>

    </div>

</main>

<?php renderFooter(); ?>
