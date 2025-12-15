<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$base  = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
$order = $order ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle Pedido #<?= $order['id_pedido'] ?? 'N/A' ?> - Admin</title>
    <base href="<?= htmlspecialchars($base, ENT_QUOTES) ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            padding: 2rem 0;
        }
        .detail-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 2px solid #000;
            padding-bottom: 1rem;
        }
        .info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }
        .info-value {
            font-size: 1rem;
            color: #000;
            font-weight: 500;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
            width: fit-content;
        }
        .status-pendiente   { background: #ffc107; color: #333; }
        .status-en-proceso  { background: #17a2b8; color: white; }
        .status-enviado     { background: #0d6efd; color: white; }
        .status-entregado   { background: #198754; color: white; }
        .status-cancelado   { background: #dc3545; color: white; }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .items-table thead {
            background: #f8f9fa;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        .items-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #000;
        }
        .btn-back {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        .totals-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
            text-align: right;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            gap: 2rem;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        .total-row.grand-total {
            border-top: 2px solid #000;
            padding-top: 1rem;
            font-size: 1.3rem;
            font-weight: 700;
        }
        @media (max-width: 768px) {
            .detail-card {
                padding: 1rem;
            }
            .info-row {
                grid-template-columns: 1fr;
            }
            .items-table {
                font-size: 0.9rem;
            }
            .items-table th, .items-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <!-- Header -->
    <div class="detail-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2><i class="bi bi-box-seam"></i> Detalle del Pedido #<?= htmlspecialchars($order['id_pedido'] ?? '') ?></h2>
            <a href="<?= $base ?>index.php?r=/admin/ventas" class="btn-back">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="detail-card">
        <div class="section-title">
            <i class="bi bi-person-circle"></i> Información del Cliente
        </div>
        <div class="info-row">
            <div class="info-item">
                <div class="info-label">Nombre</div>
                <div class="info-value"><?= htmlspecialchars($order['nombre'] ?? 'N/A') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Correo</div>
                <div class="info-value"><?= htmlspecialchars($order['correo'] ?? 'N/A') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Teléfono</div>
                <div class="info-value"><?= htmlspecialchars($order['telefono'] ?? 'N/A') ?></div>
            </div>
        </div>
    </div>

    <!-- Información de Envío -->
    <div class="detail-card">
        <div class="section-title">
            <i class="bi bi-geo-alt"></i> Información de Envío
        </div>
        <div class="info-row">
            <div class="info-item">
                <div class="info-label">Dirección</div>
                <div class="info-value"><?= htmlspecialchars($order['direccion'] ?? 'N/A') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Código Postal</div>
                <div class="info-value"><?= htmlspecialchars($order['codigo_postal'] ?? 'N/A') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Ciudad</div>
                <div class="info-value"><?= htmlspecialchars($order['id_ciudad'] ?? 'N/A') ?></div>
            </div>
        </div>
    </div>

    <!-- Información del Pedido -->
    <div class="detail-card">
        <div class="section-title">
            <i class="bi bi-receipt"></i> Información del Pedido
        </div>
        <div class="info-row">
            <div class="info-item">
                <div class="info-label">Fecha</div>
                <div class="info-value"><?= date('d/m/Y H:i', strtotime($order['fecha'] ?? 'now')) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Estado</div>
                <div class="info-value">
                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['estado_nombre'] ?? 'pendiente')) ?>">
                        <?= htmlspecialchars($order['estado_nombre'] ?? 'Pendiente') ?>
                    </span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Notas de Envío</div>
                <div class="info-value"><?= htmlspecialchars($order['descripcion del envio'] ?? 'Sin notas') ?></div>
            </div>
        </div>
    </div>

    <!-- Productos Comprados -->
    <div class="detail-card">
        <div class="section-title">
            <i class="bi bi-bag-check"></i> Productos Comprados
        </div>

        <?php if (!empty($order['items'])): ?>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Producto</th>
                        <th style="width: 15%; text-align: center;">Cantidad</th>
                        <th style="width: 20%; text-align: right;">Precio Unit.</th>
                        <th style="width: 25%; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['nombre_producto'] ?? 'Producto sin nombre') ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <?= (int)$item['cantidad'] ?>
                            </td>
                            <td style="text-align: right;">
                                $<?= number_format((float)$item['precio_unitario'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: right;">
                                <strong>$<?= number_format((float)$item['precio_unitario'] * (int)$item['cantidad'], 0, ',', '.') ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Totales -->
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>$<?= number_format((float)$order['total'], 0, ',', '.') ?></span>
                </div>
                <div class="total-row">
                    <span>Envío:</span>
                    <span>GRATIS</span>
                </div>
                <div class="total-row grand-total">
                    <span>TOTAL:</span>
                    <span>$<?= number_format((float)$order['total'], 0, ',', '.') ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle"></i> No hay productos en este pedido.
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
