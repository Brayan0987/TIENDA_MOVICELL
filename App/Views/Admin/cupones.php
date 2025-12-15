<?php
require_once __DIR__ . '/../../../includes/layout.php';

renderHeader(
    'Gestión de Cupones',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/producto.css?v=' . time() . '">'
);
?>

<main class="productos-container">

    <!-- Header del módulo -->
    <header class="productos-header">
        <div class="header-text">
            <h1 class="header-title">Gestión de Cupones</h1>
            <p class="header-subtitle">Administra los códigos de descuento de tu tienda</p>
        </div>
    </header>

    <!-- Alertas -->
    <?php if (!empty($_SESSION['admin_coupon_msg'])): ?>
        <div class="alert-custom alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($_SESSION['admin_coupon_msg']); unset($_SESSION['admin_coupon_msg']); ?></span>
        </div>
    <?php endif; ?>

    <!-- FORMULARIO CREAR / EDITAR CUPÓN -->
    <section class="productos-stats" style="margin-bottom: 1.5rem;">
        <div class="stat-card" style="flex: 1 1 100%;">
            <i class="bi bi-pencil-square"></i>
            <div class="stat-info" style="width: 100%;">
                <span class="stat-value">Crear / Editar Cupón</span>
                <span class="stat-label">Completa los campos y guarda los cambios</span>
            </div>
        </div>
    </section>

    <div class="productos-table-wrapper" style="margin-bottom: 2rem;">
        <form method="POST" action="/TIENDA_MOVICELL/public/index.php?r=/admin/cupones/save" class="row g-3">
            <input type="hidden" name="id_cupon" id="id_cupon">

            <div class="col-md-3">
                <label class="form-label">Código *</label>
                <input type="text" name="codigo" id="codigo" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Tipo *</label>
                <select name="tipo" id="tipo" class="form-control" required>
                    <option value="percent">Porcentaje (%)</option>
                    <option value="fixed">Valor fijo (COP)</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Valor *</label>
                <input type="number" step="0.01" min="0" name="valor" id="valor" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Monto mínimo</label>
                <input type="number" step="0.01" min="0" name="monto_minimo" id="monto_minimo" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Usos máximos</label>
                <input type="number" min="0" name="uso_maximo" id="uso_maximo" class="form-control" placeholder="Vacío = ilimitado">
            </div>

            <div class="col-md-3">
                <label class="form-label">Fecha inicio</label>
                <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Fecha fin</label>
                <input type="datetime-local" name="fecha_fin" id="fecha_fin" class="form-control">
            </div>

            <div class="col-md-2 d-flex align-items-center">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="activo" id="activo" checked>
                    <label class="form-check-label" for="activo">
                        Activo
                    </label>
                </div>
            </div>

            <div class="col-md-4 d-flex align-items-end justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" id="btn-reset-form">
                    Limpiar
                </button>
                <button type="submit" class="btn btn-primary">
                    Guardar Cupón
                </button>
            </div>
        </form>
    </div>

    <!-- Barra de búsqueda -->
    <div class="productos-search-bar">
        <div class="search-wrapper">
            <i class="bi bi-search"></i>
            <input
                type="text"
                id="buscador"
                class="search-input"
                placeholder="Buscar por código, tipo...">
        </div>
    </div>

    <!-- Tabla de cupones -->
    <div class="productos-table-wrapper">
        <?php if (!empty($cupones)): ?>
            <table class="productos-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Mínimo</th>
                        <th>Vigencia</th>
                        <th>Usos</th>
                        <th class="th-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-productos">
                    <?php foreach ($cupones as $c): ?>
                        <tr
                            data-id="<?= (int)$c['id_cupon'] ?>"
                            data-codigo="<?= htmlspecialchars($c['codigo']) ?>"
                            data-tipo="<?= htmlspecialchars($c['tipo']) ?>"
                            data-valor="<?= (float)$c['valor'] ?>"
                            data-minimo="<?= htmlspecialchars($c['monto_minimo']) ?>"
                            data-inicio="<?= htmlspecialchars($c['fecha_inicio']) ?>"
                            data-fin="<?= htmlspecialchars($c['fecha_fin']) ?>"
                            data-uso-max="<?= htmlspecialchars($c['uso_maximo']) ?>"
                            data-activo="<?= (int)$c['activo'] ?>"
                        >
                            <td><?= (int)$c['id_cupon'] ?></td>

                            <td class="td-producto">
                                <span class="producto-nombre"><?= htmlspecialchars($c['codigo']) ?></span>
                                <span class="producto-id">ID: <?= (int)$c['id_cupon'] ?></span>
                            </td>

                            <td class="td-marca">
                                <?= htmlspecialchars($c['tipo']) ?>
                            </td>

                            <td class="td-precio">
                                <?php if ($c['tipo'] === 'percent'): ?>
                                    <span class="precio-valor"><?= (float)$c['valor'] ?>%</span>
                                <?php else: ?>
                                    <span class="precio-valor">$<?= number_format((float)$c['valor'], 0, ',', '.') ?></span>
                                <?php endif; ?>
                            </td>

                            <td class="td-precio">
                                <?php if ($c['monto_minimo'] !== null): ?>
                                    <span class="precio-valor">
                                        $<?= number_format((float)$c['monto_minimo'], 0, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="producto-id">Sin mínimo</span>
                                <?php endif; ?>
                            </td>

                            <td class="td-specs">
                                <div class="specs-list">
                                    <span class="spec-badge">
                                        Inicio: <?= $c['fecha_inicio'] ? htmlspecialchars($c['fecha_inicio']) : '-' ?>
                                    </span>
                                    <span class="spec-badge">
                                        Fin: <?= $c['fecha_fin'] ? htmlspecialchars($c['fecha_fin']) : '-' ?>
                                    </span>
                                </div>
                            </td>

                            <td class="td-stock">
                                <?php
                                $usoActual = (int)$c['uso_actual'];
                                $usoMax    = $c['uso_maximo'] !== null ? (int)$c['uso_maximo'] : null;
                                $stockClass = 'stock-ok';
                                if ($usoMax !== null && $usoActual >= $usoMax) {
                                    $stockClass = 'stock-agotado';
                                }
                                ?>
                                <span class="stock-badge <?= $stockClass ?>">
                                    <?= $usoActual ?><?= $usoMax !== null ? ' / ' . $usoMax : ' / ∞' ?>
                                </span>
                            </td>

                            <td class="td-actions">
                                <div class="actions-group">
                                    <!-- Editar: carga datos en el formulario de arriba -->
                                    <button type="button"
                                            class="action-btn action-edit btn-edit-coupon"
                                            title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>

                                    <!-- Eliminar -->
                                    <form method="POST"
                                          action="/TIENDA_MOVICELL/public/index.php?r=/admin/cupones/delete"
                                          style="display:inline;"
                                          onsubmit="return confirm('¿Eliminar este cupón?');">
                                        <input type="hidden" name="id_cupon" value="<?= (int)$c['id_cupon'] ?>">
                                        <button type="submit"
                                                class="action-btn action-delete"
                                                title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>

                                    <!-- Estado -->
                                    <?php if ((int)$c['activo'] === 1): ?>
                                        <span class="stock-badge stock-ok ms-1">Activo</span>
                                    <?php else: ?>
                                        <span class="stock-badge stock-agotado ms-1">Inactivo</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="productos-empty">
                <i class="bi bi-inbox"></i>
                <h3>No hay cupones registrados</h3>
                <p>Crea tu primer cupón usando el formulario superior.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    // Debounce para buscador
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

    // Cargar datos de la fila en el formulario (editar)
    const editButtons = document.querySelectorAll('.btn-edit-coupon');
    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            if (!row) return;

            document.getElementById('id_cupon').value      = row.dataset.id || '';
            document.getElementById('codigo').value        = row.dataset.codigo || '';
            document.getElementById('tipo').value          = row.dataset.tipo || 'percent';
            document.getElementById('valor').value         = row.dataset.valor || '';
            document.getElementById('monto_minimo').value  = row.dataset.minimo !== 'null' ? (row.dataset.minimo || '') : '';
            document.getElementById('uso_maximo').value    = row.dataset.usoMax !== 'null' ? (row.dataset.usoMax || '') : '';

            // Adaptar formato datetime-local si la fecha viene con espacio
            const inicio = row.dataset.inicio && row.dataset.inicio !== 'null'
                ? row.dataset.inicio.replace(' ', 'T')
                : '';
            const fin = row.dataset.fin && row.dataset.fin !== 'null'
                ? row.dataset.fin.replace(' ', 'T')
                : '';

            document.getElementById('fecha_inicio').value  = inicio;
            document.getElementById('fecha_fin').value     = fin;
            document.getElementById('activo').checked      = row.dataset.activo === '1';
        });
    });

    // Reset formulario
    document.getElementById('btn-reset-form')?.addEventListener('click', () => {
        document.getElementById('id_cupon').value      = '';
        document.getElementById('codigo').value        = '';
        document.getElementById('tipo').value          = 'percent';
        document.getElementById('valor').value         = '';
        document.getElementById('monto_minimo').value  = '';
        document.getElementById('uso_maximo').value    = '';
        document.getElementById('fecha_inicio').value  = '';
        document.getElementById('fecha_fin').value     = '';
        document.getElementById('activo').checked      = true;
    });
</script>

<?php renderFooter(); ?>
