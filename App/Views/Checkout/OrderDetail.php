<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
$order = $order ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura Pedido #<?= $order['id_pedido'] ?? 'N/A' ?> - Movi Cell</title>
    <base href="<?= htmlspecialchars($base, ENT_QUOTES) ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            padding: 2rem 0;
        }

        .invoice-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #000;
        }

        .brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: #000;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .invoice-title p {
            color: #666;
            font-size: 0.95rem;
        }

        .invoice-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .meta-section h5 {
            font-weight: 600;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
        }

        .meta-section p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .items-table {
            width: 100%;
            margin: 2rem 0;
            border-collapse: collapse;
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

        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin: 2rem 0;
        }

        .totals-table {
            width: 100%;
            max-width: 400px;
        }

        .totals-table tr td:first-child {
            text-align: right;
            padding-right: 2rem;
            color: #666;
        }

        .totals-table tr td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .totals-table .total-row td {
            padding-top: 1rem;
            font-size: 1.2rem;
            border-top: 2px solid #000;
            color: #000;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-pending { background: #ffc107; color: #333; }
        .status-processing { background: #17a2b8; color: white; }
        .status-shipped { background: #0d6efd; color: white; }
        .status-delivered { background: #198754; color: white; }
        .status-cancelled { background: #dc3545; color: white; }

        .footer-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        .btn-back, .btn-print {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            margin-right: 0.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
            color: white;
        }

        .btn-print {
            background: #000;
            color: white;
        }

        .btn-print:hover {
            background: #333;
        }

        .buttons-container {
            text-align: center;
            margin-top: 2rem;
        }

        @media print {
            body {
                background: white;
            }
            .invoice-container {
                box-shadow: none;
            }
            .buttons-container {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .invoice-container {
                padding: 1.5rem;
            }

            .invoice-header {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }

            .invoice-title {
                text-align: left;
                margin-top: 1rem;
            }

            .invoice-meta {
                grid-template-columns: 1fr;
            }

            .items-table {
                font-size: 0.85rem;
            }

            .items-table th, .items-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <!-- Header -->
    <div class="invoice-header">
        <div class="brand">
            <i class="bi bi-phone-fill"></i> Movi Cell
        </div>
        <div class="invoice-title">
            <h2>FACTURA</h2>
            <p>Pedido #<?= htmlspecialchars($order['id_pedido'] ?? 'N/A') ?></p>
        </div>
    </div>

    <!-- Meta Information -->
    <div class="invoice-meta">
        <div class="meta-section">
            <h5><i class="bi bi-person"></i> CLIENTE</h5>
            <p>
                <strong><?= htmlspecialchars($order['nombre'] ?? '') ?></strong><br>
                <?= htmlspecialchars($order['telefono'] ?? '') ?><br>
                <?= htmlspecialchars($order['direccion'] ?? '') ?><br>
                <?= htmlspecialchars($order['ciudad_nombre'] ?? '') ?> <?= htmlspecialchars($order['codigo_postal'] ?? '') ?>
            </p>
        </div>
        <div class="meta-section">
            <h5><i class="bi bi-calendar3"></i> INFORMACIÓN DEL PEDIDO</h5>
            <p>
                <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($order['fecha'] ?? 'now')) ?><br>
                <strong>Estado:</strong> <br>
                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['estado'] ?? 'pendiente')) ?>">
                    <?= htmlspecialchars($order['estado'] ?? 'Pendiente') ?>
                </span><br><br>
                <strong>Método de Pago:</strong> <?= htmlspecialchars($order['metodo_nombre'] ?? 'Contraentrega') ?>
            </p>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Producto</th>
                <th style="width: 15%; text-align: center;">Cantidad</th>
                <th style="width: 17.5%; text-align: right;">Precio Unit.</th>
                <th style="width: 17.5%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($order['detalles'])): ?>
                <?php foreach ($order['detalles'] as $detalle): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($detalle['nombre_producto'] ?? $detalle['id_celulares']) ?></strong><br>
                            <small style="color: #999;">ID: <?= $detalle['id_celulares'] ?></small>
                        </td>
                        <td style="text-align: center;">
                            <?= (int)$detalle['cantidad'] ?>
                        </td>
                        <td style="text-align: right;">
                            $<?= number_format((float)$detalle['precio_unitario'], 0, ',', '.') ?>
                        </td>
                        <td style="text-align: right;">
                            $<?= number_format((float)$detalle['precio_unitario'] * (int)$detalle['cantidad'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td>$<?= number_format((float)$order['total'], 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Envío:</td>
                <td>GRATIS</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL:</td>
                <td>$<?= number_format((float)$order['total'], 0, ',', '.') ?></td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer-section">
        <p>
            <strong>Gracias por tu compra en Movi Cell</strong><br>
            Si tienes dudas, contáctanos en <a href="mailto:info@movicell.com">info@movicell.com</a>
        </p>
    </div>

    <!-- Buttons -->
    <div class="buttons-container">
        <a href="<?= $base ?>index.php?r=/panel" class="btn-back">
            <i class="bi bi-arrow-left"></i> Volver al Panel
        </a>
        <button class="btn-print" onclick="window.print()">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

</body>
</html>