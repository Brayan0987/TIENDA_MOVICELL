<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido Confirmado - Movi Cell</title>
    <base href="<?= htmlspecialchars($base, ENT_QUOTES) ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding-top: 80px;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            margin: 3rem auto;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #000, #333);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #333, #000);
            color: white;
            transform: translateY(-2px);
        }
        
        .order-detail {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <i class="bi bi-check-circle fs-1 text-white"></i>
            </div>
            
            <h1 class="mb-3">¡Pedido Confirmado!</h1>
            <p class="lead text-muted mb-4">
                Tu pedido ha sido recibido exitosamente
            </p>
            
            <?php if (!empty($order)): ?>
                <div class="order-detail">
                    <h5 class="mb-3">
                        <i class="bi bi-receipt me-2"></i>
                        Detalles del Pedido
                    </h5>
                    
                    <div class="row mb-2">
                        <div class="col-6 text-start"><strong>Número de Pedido:</strong></div>
                        <div class="col-6 text-end">#<?= $order['id_pedido'] ?></div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-6 text-start"><strong>Total:</strong></div>
                        <div class="col-6 text-end">$<?= number_format($order['total'], 0, ',', '.') ?></div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-6 text-start"><strong>Estado:</strong></div>
                        <div class="col-6 text-end">
                            <span class="badge bg-warning"><?= $order['estado_nombre'] ?? 'Pendiente' ?></span>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-6 text-start"><strong>Método de Pago:</strong></div>
                        <div class="col-6 text-end"><?= $order['metodo_nombre'] ?? 'Contraentrega' ?></div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3"><i class="bi bi-truck me-2"></i>Información de Envío</h6>
                    <p class="mb-1"><?= htmlspecialchars($order['direccion']) ?></p>
                    <p class="mb-1"><?= htmlspecialchars($order['ciudad_nombre'] ?? '') ?></p>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Recibirás un WhatsApp de confirmación en los próximos minutos.
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="<?= $base ?>" class="btn-primary-custom">
                    <i class="bi bi-house me-2"></i>
                    Volver al Inicio
                </a>
                <a href="<?= $base ?>index.php?r=/productos" class="btn-primary-custom">
                    <i class="bi bi-grid me-2"></i>
                    Seguir Comprando
                </a>
            </div>
        </div>
    </div>

</body>
</html>
