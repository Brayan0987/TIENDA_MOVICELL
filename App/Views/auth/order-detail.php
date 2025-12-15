<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";

// Si no llega el pedido, redirigir al panel
if (empty($order) || empty($order['id_pedido'])) {
    header('Location: ' . $base . 'index.php?r=/panel');
    exit;
}

// Normalizar estado para reutilizar en la vista
$estado = strtolower($order['estado_nombre'] ?? 'pendiente');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle del Pedido #<?= (int)$order['id_pedido'] ?> - Movi Cell</title>
    <base href="<?= htmlspecialchars($base, ENT_QUOTES) ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-white: #ffffff;
            --primary-black: #000000;
            --primary-silver: #c0c0c0;
            --primary-silver-light: #e5e5e5;
            --primary-silver-dark: #a9a9a9;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --info: #0dcaf0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding-top: 80px;
            min-height: 100vh;
        }
        
        .navbar-custom {
            background-color: var(--primary-black) !important;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: white !important;
        }
        
        .btn-back {
            background: white;
            color: var(--primary-black);
            border: 2px solid white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background: var(--primary-silver-light);
            color: var(--primary-black);
            transform: translateY(-2px);
        }
        
        .order-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid var(--primary-silver-light);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-silver-light);
            margin-bottom: 2rem;
        }
        
        .order-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-black);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .order-badge {
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-pendiente {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }
        
        .badge-procesando {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            color: white;
        }
        
        .badge-enviado {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            color: white;
        }
        
        .badge-entregado {
            background: linear-gradient(135deg, #34d399, #10b981);
            color: white;
        }
        
        .badge-cancelado {
            background: linear-gradient(135deg, #f87171, #ef4444);
            color: white;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--primary-silver-light);
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-black);
            margin-bottom: 0.5rem;
        }
        
        .product-quantity {
            color: var(--primary-silver-dark);
            font-size: 0.9rem;
        }
        
        .product-price {
            text-align: right;
        }
        
        .unit-price {
            font-size: 0.9rem;
            color: var(--primary-silver-dark);
        }
        
        .subtotal-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-black);
        }
        
        .total-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            font-size: 1.1rem;
        }
        
        .total-final {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-black);
            border-top: 3px solid var(--primary-black);
            padding-top: 1.5rem;
            margin-top: 1rem;
        }
        
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: 100%;
            border: 1px solid var(--primary-silver-light);
        }
        
        .info-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--primary-black);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .info-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--primary-silver-light);
        }
        
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--primary-silver-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-size: 1rem;
            color: var(--primary-black);
            font-weight: 600;
        }
        
        .shipping-address {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 1.5rem;
        }
        
        .shipping-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-black), #333);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }
            
            .order-card {
                padding: 1.5rem;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .order-title {
                font-size: 1.5rem;
            }
            
            .product-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .product-price {
                text-align: left;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="<?= $base ?>">
                <i class="bi bi-phone-fill me-2"></i>Movi Cell
            </a>
            <a href="<?= $base ?>index.php?r=/panel" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Volver al Panel
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <!-- HEADER DEL PEDIDO -->
        <div class="order-card">
            <div class="order-header">
                <div class="order-title">
                    <i class="bi bi-receipt"></i>
                    Pedido #<?= (int)$order['id_pedido'] ?>
                </div>
                <div>
                    <?php
                    $badgeClass = 'badge-pendiente';
                    if ($estado === 'procesando') $badgeClass = 'badge-procesando';
                    elseif ($estado === 'enviado') $badgeClass = 'badge-enviado';
                    elseif ($estado === 'entregado') $badgeClass = 'badge-entregado';
                    elseif ($estado === 'cancelado') $badgeClass = 'badge-cancelado';
                    ?>
                    <span class="order-badge <?= $badgeClass ?>">
                        <?= ucfirst($order['estado_nombre'] ?? 'Pendiente') ?>
                    </span>
                </div>
            </div>

            <!-- PRODUCTOS DEL PEDIDO -->
            <div class="products-section">
                <h4 class="mb-4" style="font-weight: 700;">
                    <i class="bi bi-box-seam me-2"></i>
                    Productos del Pedido
                </h4>
                
                <?php if (!empty($order['items'])): ?>
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="product-item">
                            <div class="product-info">
                                <div class="product-name">
                                    <?= htmlspecialchars($item['nombre_producto']) ?>
                                </div>
                                <div class="product-quantity">
                                    Cantidad: <?= (int)$item['cantidad'] ?> unidad<?= $item['cantidad'] > 1 ? 'es' : '' ?>
                                </div>
                            </div>
                            <div class="product-price">
                                <div class="unit-price">
                                    $<?= number_format((float)$item['precio_unitario'], 0, ',', '.') ?> c/u
                                </div>
                                <div class="subtotal-price">
                                    $<?= number_format((float)$item['subtotal'], 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No se encontraron productos en este pedido.
                    </div>
                <?php endif; ?>

                <!-- TOTAL -->
                <div class="total-section">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?= number_format((float)$order['total'], 0, ',', '.') ?></span>
                    </div>
                    <div class="total-row">
                        <span>Envío:</span>
                        <span class="text-success fw-bold">GRATIS</span>
                    </div>
                    <div class="total-row total-final">
                        <span>Total:</span>
                        <span>$<?= number_format((float)$order['total'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMACIÓN DEL PEDIDO -->
        <div class="row g-4">
            <!-- INFORMACIÓN GENERAL -->
            <div class="col-lg-6">
                <div class="info-card">
                    <h5 class="info-title">
                        <i class="bi bi-info-circle"></i>
                        Información del Pedido
                    </h5>
                    
                    <div class="info-item">
                        <div class="info-label">Fecha del Pedido</div>
                        <div class="info-value">
                            <?= date('d/m/Y H:i', strtotime($order['fecha'])) ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Método de Pago</div>
                        <div class="info-value">
                            <i class="bi bi-cash-coin me-2"></i>
                            <?= htmlspecialchars($order['metodo_nombre'] ?? 'Contraentrega') ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Estado Actual</div>
                        <div class="info-value">
                            <?php
                            $statusIcon = 'clock-history';
                            if ($estado === 'procesando') $statusIcon = 'arrow-repeat';
                            elseif ($estado === 'enviado') $statusIcon = 'truck';
                            elseif ($estado === 'entregado') $statusIcon = 'check-circle';
                            elseif ($estado === 'cancelado') $statusIcon = 'x-circle';
                            ?>
                            <i class="bi bi-<?= $statusIcon ?> me-2"></i>
                            <?= ucfirst($order['estado_nombre'] ?? 'Pendiente') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN DE ENVÍO -->
            <div class="col-lg-6">
                <div class="info-card">
                    <h5 class="info-title">
                        <i class="bi bi-truck"></i>
                        Datos de Envío
                    </h5>
                    
                    <div class="shipping-address">
                        <div class="shipping-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Nombre Completo</div>
                            <div class="info-value">
                                <?= htmlspecialchars($order['nombre_completo']) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Dirección</div>
                            <div class="info-value">
                                <?= htmlspecialchars($order['direccion']) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Ciudad</div>
                            <div class="info-value">
                                <?= htmlspecialchars($order['ciudad_nombre'] ?? 'No especificada') ?>
                                <?php if (!empty($order['codigo_postal'])): ?>
                                    - CP: <?= htmlspecialchars($order['codigo_postal']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Teléfono de Contacto</div>
                            <div class="info-value">
                                <i class="bi bi-phone me-2"></i>
                                <?= htmlspecialchars($order['telefono_envio']) ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($order['notas'])): ?>
                            <div class="info-item">
                                <div class="info-label">Notas del Pedido</div>
                                <div class="info-value">
                                    <i class="bi bi-chat-left-text me-2"></i>
                                    <?= htmlspecialchars($order['notas']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMACIÓN ADICIONAL -->
        <div class="order-card mt-4">
            <div class="alert alert-info mb-0">
                <h6 class="mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Información Importante</strong>
                </h6>
                <ul class="mb-0">
                    <li class="mb-2">
                        <i class="bi bi-clock me-2"></i>
                        El tiempo estimado de entrega es de <strong>2-3 días hábiles</strong>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-whatsapp me-2"></i>
                        Recibirás notificaciones por WhatsApp sobre el estado de tu pedido
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-shield-check me-2"></i>
                        Todos nuestros productos cuentan con <strong>garantía de 30 días</strong>
                    </li>
                    <li>
                        <i class="bi bi-headset me-2"></i>
                        ¿Tienes dudas? Contáctanos en nuestro centro de ayuda
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
