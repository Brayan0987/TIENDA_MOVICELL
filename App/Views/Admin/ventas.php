<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
require_once __DIR__ . '/../../../includes/layout.php';
// CSRF token for AJAX
$csrf = \App\Core\Security::csrfToken();

renderHeader(
    'Gestión de Ventas',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/ventas.css?v='.time().'">'
);
?>

<main class="ventas-container">

    <!-- Header del módulo -->
    <header class="ventas-header">
        <div class="header-text">
            <h1 class="header-title">Gestión de Ventas</h1>
            <p class="header-subtitle">Visualiza y administra todos los pedidos de tus clientes</p>
        </div>
    </header>

    <!-- Stats rápidas -->
    <div class="ventas-stats">
        <div class="stat-card">
            <i class="bi bi-box-seam"></i>
            <div class="stat-info">
                <span class="stat-value"><?= count($pedidos ?? []) ?></span>
                <span class="stat-label">Total Pedidos</span>
            </div>
        </div>

        <div class="stat-card">
            <i class="bi bi-clock-history"></i>
            <div class="stat-info">
                <span class="stat-value">
                    <?= count(array_filter($pedidos ?? [], fn($p) => ($p['estado_nombre'] ?? '') === 'Pendiente')) ?>
                </span>
                <span class="stat-label">Pendientes</span>
            </div>
        </div>

        <div class="stat-card">
            <i class="bi bi-truck"></i>
            <div class="stat-info">
                <span class="stat-value">
                    <?= count(array_filter($pedidos ?? [], fn($p) => ($p['estado_nombre'] ?? '') === 'Enviado')) ?>
                </span>
                <span class="stat-label">Enviados</span>
            </div>
        </div>

        <div class="stat-card">
            <i class="bi bi-check-circle"></i>
            <div class="stat-info">
                <span class="stat-value">
                    <?= count(array_filter($pedidos ?? [], fn($p) => ($p['estado_nombre'] ?? '') === 'Entregado')) ?>
                </span>
                <span class="stat-label">Entregados</span>
            </div>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <div class="ventas-search-bar">
        <div class="search-wrapper">
            <i class="bi bi-search"></i>
            <input
                type="text"
                id="buscador"
                class="search-input"
                placeholder="Buscar por ID, cliente, teléfono...">
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

    <!-- Tabla de ventas -->
    <div class="ventas-table-wrapper">
        <?php if (!empty($pedidos)): ?>
            <table class="ventas-table">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="th-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-ventas">
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td class="td-id">
                                <span class="pedido-id">#<?= htmlspecialchars($pedido['id_pedido'] ?? '') ?></span>
                            </td>

                            <td class="td-cliente">
                                <span class="cliente-nombre"><?= htmlspecialchars($pedido['nombre'] ?? 'Sin nombre') ?></span>
                                <span class="cliente-email"><?= htmlspecialchars($pedido['correo'] ?? 'N/A') ?></span>
                            </td>

                            <td class="td-telefono">
                                <?= htmlspecialchars($pedido['telefono'] ?? 'N/A') ?>
                            </td>

                            <td class="td-total">
                                <span class="total-valor">$<?= number_format($pedido['total'] ?? 0, 0, ',', '.') ?></span>
                            </td>

                            <td class="td-fecha">
                                <?= date('d/m/Y H:i', strtotime($pedido['fecha'] ?? 'now')) ?>
                            </td>

                            <td class="td-estado">
                                <span class="estado-badge estado-<?= strtolower(str_replace(' ', '-', $pedido['estado_nombre'] ?? 'pendiente')) ?>">
                                    <?= htmlspecialchars($pedido['estado_nombre'] ?? 'Pendiente') ?>
                                </span>
                            </td>

                            <td class="td-actions">
                                <div class="actions-group">
                                    <a href="index.php?r=/admin/pedido&id=<?= $pedido['id_pedido'] ?>"
                                       class="action-btn action-view"
                                       title="Ver Detalle">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <button type="button"
                                       class="action-btn action-preview"
                                       title="Enviar Factura por Email"
                                       onclick="enviarFacturaEmail(<?= $pedido['id_pedido'] ?>)">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                    <a href="index.php?r=/factura/descargar&id=<?= $pedido['id_pedido'] ?>"
                                       class="action-btn action-download"
                                       title="Descargar Factura PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button type="button"
                                            class="action-btn action-edit"
                                            title="Editar estado"
                                            data-id="<?= $pedido['id_pedido'] ?>"
                                            data-estado="<?= intval($pedido['id_estado'] ?? 1) ?>">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="ventas-empty">
                <i class="bi bi-inbox"></i>
                <h3>No hay pedidos registrados</h3>
                <p>Los pedidos de tus clientes aparecerán aquí</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<script>
    // Expose globals for client scripts
    window.APP = window.APP || {};
    window.APP.base = '<?= $base ?>';
    window.APP.csrf = '<?= htmlspecialchars($csrf, ENT_QUOTES) ?>';
    window.APP.estados = <?= json_encode($estados ?? []) ?>;
    // Absolute endpoint URL for updating pedido status (helps avoid duplicated path segments)
    <?php
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), "\/\\");
        $apiPedidoUpdateUrl = $scheme . '://' . $host . $scriptDir . '/index.php?r=/admin/pedido_update_status';
    ?>
    window.APP.apiPedidoUpdateUrl = '<?= $apiPedidoUpdateUrl ?>';
</script>
<script src="<?= $base ?>assets/JS/ventas_status_edit.js"></script>
<style>
    .estado-badge { display:inline-block; padding:4px 8px; border-radius:12px; color:#fff; font-size:0.9rem; }
    .estado-pendiente { background:#6c757d; }
    .estado-en-proceso { background:#0d6efd; }
    .estado-enviado { background:#0dcaf0; color:#000; }
    .estado-entregado { background:#198754; }
    .estado-cancelado { background:#dc3545; }
    .estado-devuelto { background:#ffc107; color:#000; }

    /* Toasts */
    .app-toast-container { position:fixed; right:20px; top:20px; z-index:1100; }
    .app-toast { background:#333; color:#fff; padding:10px 14px; margin-bottom:8px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.2); opacity:0.95 }
    .app-toast.success { background: #198754; }
    .app-toast.error { background: #dc3545; }
</style>

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
            const rows = document.querySelectorAll('#tabla-ventas tr');
            rows.forEach(row => {
                const text = (row.textContent || '').toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        }, 150));
    }

    // Función para enviar factura por email
    function enviarFacturaEmail(id_pedido) {
        if (!confirm('¿Enviar la factura por correo electrónico?')) {
            return;
        }
        
        // Usar la URL correcta del servidor (sin duplicaciones)
        const apiUrl = <?php echo json_encode($base); ?> + 'index.php?r=/factura/reenviar';
        
        console.log('Enviando a:', apiUrl);
        console.log('ID Pedido:', id_pedido);
        
        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_pedido=' + id_pedido
        })
        .then(response => {
            console.log('Respuesta status:', response.status);
            console.log('Respuesta headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            if (data.success) {
                alert('✅ Factura enviada correctamente al correo\n\n' + (data.message || ''));
            } else {
                alert('❌ Error: ' + (data.message || 'No se pudo enviar la factura'));
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            alert('❌ Error al enviar la factura: ' + error.message);
        });
    }
</script>

<?php renderFooter(); ?>