<?php
require_once __DIR__ . '/../../../includes/layout.php';
require_once __DIR__ . '/../../Controllers/Admin/listar_productos_controller.php';

renderHeader(
    'Listado de Celulares',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/producto.css?v='.time().'">'
);
?>

<main class="productos-container">

    <!-- Header del módulo -->
    <header class="productos-header">
        <div class="header-text">
            <h1 class="header-title">Gestión de Productos</h1>
            <p class="header-subtitle">Administra el catálogo completo de smartphones</p>
        </div>
        <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/insertar-producto"
           class="btn-add-product">
            <i class="bi bi-plus-circle-fill"></i>
            Agregar Producto
        </a>
    </header>

    <!-- Stats rápidas -->
    <div class="productos-stats">
        <div class="stat-card">
            <i class="bi bi-box-seam"></i>
            <div class="stat-info">
                <span class="stat-value"><?= count($productos) ?></span>
                <span class="stat-label">Total Productos</span>
            </div>
        </div>

        <div class="stat-card">
            <i class="bi bi-check-circle"></i>
            <div class="stat-info">
                <span class="stat-value">
                    <?= count(array_filter($productos, fn($p) => ($p['cantidad'] ?? 0) > 0)) ?>
                </span>
                <span class="stat-label">En Stock</span>
            </div>
        </div>

        <div class="stat-card">
            <i class="bi bi-exclamation-triangle"></i>
            <div class="stat-info">
                <span class="stat-value">
                    <?= count(array_filter($productos, fn($p) => ($p['cantidad'] ?? 0) == 0)) ?>
                </span>
                <span class="stat-label">Sin Stock</span>
            </div>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <div class="productos-search-bar">
        <div class="search-wrapper">
            <i class="bi bi-search"></i>
            <input
                type="text"
                id="buscador"
                class="search-input"
                placeholder="Buscar por nombre, marca, color...">
        </div>
    </div>

    <!-- Alertas -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-custom alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-custom alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Tabla de productos -->
    <div class="productos-table-wrapper">
        <?php if (!empty($productos)): ?>
            <table class="productos-table">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Especificaciones</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th class="th-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-productos">
                    <?php foreach ($productos as $row): ?>
                        <tr>
                            <td class="td-imagen">
                                <?php if (!empty($row['imagen_url'])): ?>
                                    <img src="/TIENDA_MOVICELL/Public/<?= htmlspecialchars($row['imagen_url']) ?>"
                                         alt="<?= htmlspecialchars($row['producto']) ?>"
                                         class="producto-thumb">
                                <?php else: ?>
                                    <div class="producto-thumb-placeholder">
                                        <i class="bi bi-phone"></i>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="td-producto">
                                <span class="producto-nombre"><?= htmlspecialchars($row['producto']) ?></span>
                                <span class="producto-id">ID: <?= htmlspecialchars($row['id_celulares']) ?></span>
                            </td>

                            <td class="td-marca"><?= htmlspecialchars($row['marca']) ?></td>

                            <td class="td-specs">
                                <div class="specs-list">
                                    <span class="spec-badge"><?= htmlspecialchars($row['ram']) ?> RAM</span>
                                    <span class="spec-badge"><?= htmlspecialchars($row['almacenamiento']) ?></span>
                                    <span class="spec-badge color-badge"><?= htmlspecialchars($row['color']) ?></span>
                                </div>
                            </td>

                            <td class="td-precio">
                                <span class="precio-valor">$<?= number_format($row['precio'], 0, ',', '.') ?></span>
                            </td>

                            <td class="td-stock">
                                <?php
                                $stock = (int)$row['cantidad'];
                                $stockClass = $stock > 5 ? 'stock-ok' : ($stock > 0 ? 'stock-bajo' : 'stock-agotado');
                                ?>
                                <span class="stock-badge <?= $stockClass ?>">
                                    <?= $stock ?>
                                </span>
                            </td>

                            <td class="td-actions">
                                <div class="actions-group">
                                    <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/actualizar-producto&id=<?= intval($row['id_celulares']) ?>"
                                       class="action-btn action-edit"
                                       title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>

                                    <form method="POST"
                                          action="/TIENDA_MOVICELL/public/index.php?r=/admin/productos"
                                          style="display:inline;"
                                          onsubmit="return confirm('¿Eliminar este producto?');">
                                        <input type="hidden" name="action" value="eliminar_celular">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id_celulares']) ?>">
                                        <button type="submit"
                                                class="action-btn action-delete"
                                                title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="productos-empty">
                <i class="bi bi-inbox"></i>
                <h3>No hay productos registrados</h3>
                <p>Comienza agregando tu primer smartphone al catálogo</p>
                <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/insertar-producto"
                   class="btn-add-first">
                    <i class="bi bi-plus-circle"></i>
                    Agregar Primer Producto
                </a>
            </div>
        <?php endif; ?>
    </div>

</main>

<script>
    // Debounce helper
    function debounce(fn, wait) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    const buscadorEl = document.getElementById('buscador');
    if (buscadorEl) {
        buscadorEl.addEventListener('input', debounce(function () {
            const searchValue = (this.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('#tabla-productos tr');
            rows.forEach(row => {
                const text = (row.textContent || '').toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        }, 150));
    }
</script>

<?php renderFooter(); ?>
