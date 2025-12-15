<?php
require_once __DIR__ . '/../../../includes/layout.php';

if (!isset($row) || !isset($imgs) || !isset($productos) || !isset($marcas) || !isset($colores) || !isset($rams) || !isset($almacenamientos)) {
    header("Location: /TIENDA_MOVICELL/public/index.php?r=/admin/productos");
    exit;
}

renderHeader(
    'Editar Celular',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/actualizar_producto.css?v='.time().'">'
);
?>

<main class="actualizar-container">

    <!-- Header del módulo -->
    <header class="actualizar-header">
        <div class="header-icon">
            <i class="bi bi-pencil-square"></i>
        </div>
        <div class="header-text">
            <h1 class="header-title">Editar Celular</h1>
            <p class="header-subtitle">
                Actualiza los datos del producto #<?= htmlspecialchars($row['id_celulares']) ?>
            </p>
        </div>
    </header>

    <article class="actualizar-card">

        <form method="POST"
              action="/TIENDA_MOVICELL/public/index.php?r=/admin/actualizar-producto&id=<?= intval($row['id_celulares']) ?>"
              enctype="multipart/form-data"
              class="actualizar-form">
            
            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id_celulares']) ?>">
            <input type="hidden" name="id_precio" value="<?= htmlspecialchars($row['id_precio']) ?>">

            <!-- Sección: Especificaciones -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="bi bi-info-circle-fill"></i>
                    Especificaciones del Producto
                </h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="id_producto">
                            <i class="bi bi-box"></i>
                            Producto
                        </label>
                        <select name="id_producto" id="id_producto" class="form-select" required>
                            <?php mysqli_data_seek($productos, 0); 
                            while ($p = mysqli_fetch_assoc($productos)) {
                                $sel = ($p['id_producto'] == $row['id_producto']) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($p['id_producto']) ?>" <?= $sel ?>>
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_marcas">
                            <i class="bi bi-award"></i>
                            Marca
                        </label>
                        <select name="id_marcas" id="id_marcas" class="form-select" required>
                            <?php mysqli_data_seek($marcas, 0); 
                            while ($m = mysqli_fetch_assoc($marcas)) {
                                $sel = ($m['id_marcas'] == $row['id_marcas']) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($m['id_marcas']) ?>" <?= $sel ?>>
                                    <?= htmlspecialchars($m['marca']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_color">
                            <i class="bi bi-palette"></i>
                            Color
                        </label>
                        <select name="id_color" id="id_color" class="form-select" required>
                            <?php mysqli_data_seek($colores, 0); 
                            while ($c = mysqli_fetch_assoc($colores)) {
                                $sel = ($c['id_color'] == $row['id_color']) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($c['id_color']) ?>" <?= $sel ?>>
                                    <?= htmlspecialchars($c['color']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_ram">
                            <i class="bi bi-memory"></i>
                            RAM
                        </label>
                        <select name="id_ram" id="id_ram" class="form-select" required>
                            <?php mysqli_data_seek($rams, 0); 
                            while ($r = mysqli_fetch_assoc($rams)) {
                                $sel = ($r['id_ram'] == $row['id_ram']) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($r['id_ram']) ?>" <?= $sel ?>>
                                    <?= htmlspecialchars($r['ram']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_almacenamiento">
                            <i class="bi bi-hdd"></i>
                            Almacenamiento
                        </label>
                        <select name="id_almacenamiento" id="id_almacenamiento" class="form-select" required>
                            <?php mysqli_data_seek($almacenamientos, 0); 
                            while ($a = mysqli_fetch_assoc($almacenamientos)) {
                                $sel = ($a['id_almacenamiento'] == $row['id_almacenamiento']) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($a['id_almacenamiento']) ?>" <?= $sel ?>>
                                    <?= htmlspecialchars($a['almacenamiento']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="precio">
                            <i class="bi bi-currency-dollar"></i>
                            Precio
                        </label>
                        <input class="form-input"
                               type="text"
                               name="precio"
                               id="precio"
                               value="<?= htmlspecialchars($row['precio']) ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cantidad">
                            <i class="bi bi-box-seam"></i>
                            Stock
                        </label>
                        <input class="form-input"
                               type="number"
                               name="cantidad"
                               id="cantidad"
                               min="0"
                               value="<?= htmlspecialchars($row['cantidad']) ?>"
                               required>
                    </div>
                </div>
            </div>

            <!-- Sección: Imágenes Actuales -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="bi bi-images"></i>
                    Imágenes Actuales
                </h3>

                <div class="imagenes-grid">
                    <?php if (count($imgs) === 0): ?>
                        <div class="empty-images">
                            <i class="bi bi-image"></i>
                            <p>No hay imágenes asociadas a este producto</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($imgs as $img): ?>
                            <div class="imagen-item">
                                <img src="/TIENDA_MOVICELL/Public/<?= htmlspecialchars($img['imagen_url']) ?>"
                                     alt="Imagen del producto"
                                     class="imagen-thumb">
                                <label class="imagen-delete">
                                    <input type="checkbox"
                                           name="delete_image[]"
                                           value="<?= intval($img['id_imagen']) ?>">
                                    <span>Eliminar</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sección: Subir Nuevas Imágenes -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="bi bi-cloud-upload-fill"></i>
                    Agregar Nuevas Imágenes
                </h3>

                <div class="form-group">
                    <div class="file-upload-wrapper">
                        <input type="file"
                               name="imagenes[]"
                               id="imagenes"
                               class="file-input"
                               accept="image/*"
                               multiple>
                        <label for="imagenes" class="file-label">
                            <i class="bi bi-cloud-upload"></i>
                            <span>Seleccionar imágenes</span>
                        </label>
                        <small class="file-hint">
                            <i class="bi bi-info-circle"></i>
                            Formatos: JPG, PNG, GIF, WEBP. Máximo 3MB por imagen
                        </small>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <footer class="form-actions">
                <button type="submit" class="btn-action btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Guardar Cambios
                </button>

                <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/productos"
                   class="btn-action btn-secondary">
                    <i class="bi bi-x-circle"></i>
                    Cancelar
                </a>
            </footer>

        </form>

    </article>

</main>

<?php renderFooter(); ?>
