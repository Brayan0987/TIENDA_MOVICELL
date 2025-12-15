<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";

// Compatibilidad sesión: normalizar $userData para la vista
// Mantener soporte para formatos antiguos y nuevos de sesión.
$userData = $userData ?? [];
if (empty($userData)) {
    $userData = [
        'name' => $_SESSION['user']['name'] ?? $_SESSION['user_name'] ?? $_SESSION['user']['nombre'] ?? '',
        'phone' => $_SESSION['user']['phone'] ?? $_SESSION['user_phone'] ?? $_SESSION['user']['telefono'] ?? '',
        'email' => $_SESSION['user']['email'] ?? $_SESSION['user_email'] ?? $_SESSION['user']['correo'] ?? '',
        'id_usuario_rol' => $_SESSION['user']['id_usuario_rol'] ?? $_SESSION['user_id_rol'] ?? null,
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Movi Cell</title>
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
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding-top: 80px;
        }
        
        .navbar-checkout {
            background-color: var(--primary-black) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        
        .checkout-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .form-control {
            border: 2px solid var(--primary-silver-light);
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-black);
            box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, var(--primary-black), #333);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 1rem 2rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-checkout:hover {
            background: linear-gradient(135deg, #333, var(--primary-black));
            color: white;
            transform: translateY(-2px);
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--primary-silver-light);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark fixed-top navbar-checkout">
        <div class="container">
            <a class="navbar-brand" href="<?= $base ?>">
                <i class="bi bi-phone-fill me-2"></i>Movi Cell
            </a>
            <span class="text-white">
                <i class="bi bi-shield-check me-2"></i>
                Pago Seguro
            </span>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <!-- FORMULARIO DE CHECKOUT -->
            <div class="col-lg-7">
                <div class="checkout-card">
                    <h2 class="mb-4">
                        <i class="bi bi-truck me-2"></i>
                        Información de Envío
                    </h2>
                    
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= $base ?>index.php?r=/checkout/process">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-person me-1"></i>
                                Nombre Completo *
                            </label>
                            <input type="text" 
                                   name="nombre_completo" 
                                   class="form-control" 
                                   placeholder="Ej: Juan Pérez"
                                   value="<?= htmlspecialchars($userData['name'] ?? '') ?>"
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-phone me-1"></i>
                                Teléfono *
                            </label>
                            <input type="tel" 
                                   name="telefono" 
                                   class="form-control" 
                                   placeholder="300 123 4567"
                                   value="<?= htmlspecialchars($userData['phone'] ?? '') ?>"
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-geo-alt me-1"></i>
                                Dirección Completa *
                            </label>
                            <input type="text" 
                                   name="direccion" 
                                   class="form-control" 
                                   placeholder="Calle 123 #45-67, Apto 101"
                                   required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-pin-map me-1"></i>
                                    Ciudad *
                                </label>
                                <select name="ciudad" class="form-control" required>
                                    <option value="">Selecciona una ciudad</option>
                                    <option value="1">Bogotá</option>
                                    <option value="2">Medellín</option>
                                    <option value="3">Cali</option>
                                    <option value="4">Barranquilla</option>
                                    <option value="5">Cartagena</option>
                                    <option value="6">Otra</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-mailbox me-1"></i>
                                    Código Postal (Opcional)
                                </label>
                                <input type="text" 
                                       name="codigo_postal" 
                                       class="form-control" 
                                       placeholder="110111">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-chat-text me-1"></i>
                                Notas del Pedido (Opcional)
                            </label>
                            <textarea name="notas" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Ej: Entregar en horario de oficina"></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Método de Pago:</strong> Contraentrega (Pago en efectivo al recibir)
                        </div>
                        
                        <button type="submit" class="btn-checkout">
                            <i class="bi bi-check-circle me-2"></i>
                            Confirmar Pedido
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- RESUMEN DEL PEDIDO -->
            <div class="col-lg-5">
                <div class="checkout-card">
                    <h4 class="mb-4">
                        <i class="bi bi-receipt me-2"></i>
                        Resumen del Pedido
                    </h4>
                    
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <div>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                <br>
                                <small class="text-muted">Cantidad: <?= $item['quantity'] ?></small>
                            </div>
                            <div class="text-end">
                                <strong>$<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="order-item">
                        <span>Subtotal</span>
                        <span>$<?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="order-item">
                        <span>Envío</span>
                        <span class="text-success">GRATIS</span>
                    </div>
                    
                    <div class="order-item">
                        <strong class="fs-5">Total</strong>
                        <strong class="fs-4">$<?= number_format($total, 0, ',', '.') ?></strong>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-shield-check text-success me-2"></i>
                            <small>Compra 100% Segura</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-truck text-primary me-2"></i>
                            <small>Envío en 2-3 días hábiles</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-arrow-clockwise text-info me-2"></i>
                            <small>Garantía de 30 días</small>
                        </div>
                    </div>
                </div>
                
                <a href="<?= $base ?>index.php?r=/cart" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver al Carrito
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

