<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
$csrf = $_SESSION['csrf_token'] ?? ($_SESSION['csrf_token'] = bin2hex(random_bytes(32)));

// Inicializar carrito para contador
require_once __DIR__ . '/../../Core/Cart.php';
$cart = new App\Core\Cart();
$cartCount = $cart->getTotalQuantity();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Cuenta - Movi Cell</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?= $base ?>assets/css/auth.css" rel="stylesheet">
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-premium">
        <div class="container">
            <a class="navbar-brand-premium" href="<?= $base ?>">
                <i class="bi bi-phone-fill me-2"></i>
                Movi Cell
            </a>
            <a href="<?= $base ?>index.php?r=/cart" class="btn-cart-premium position-relative">
                <i class="bi bi-cart3"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge-premium"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="auth-container">
        <div class="auth-card-premium">
            <div class="card-body-premium">
                <a href="<?= $base ?>" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    Volver al inicio
                </a>
                
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h1 class="auth-title">Crear Cuenta</h1>
                    <p class="auth-subtitle">Regístrate para comenzar a comprar</p>
                </div>
                
                <!-- Alerts -->
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert-premium alert-danger-premium">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert-premium alert-success-premium">
                        <i class="bi bi-check-circle-fill"></i>
                        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Form -->
                <form method="post" action="<?= $base ?>index.php?r=/register" id="registerForm">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                    
                    <!-- Nombre -->
                    <div class="form-group-premium">
                        <label class="form-label-premium">
                            <i class="bi bi-person"></i>
                            Nombre completo
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-person-fill input-icon"></i>
                            <input 
                                type="text" 
                                name="name" 
                                class="form-control-premium" 
                                placeholder="Ej: Juan Pérez"
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                required
                                autofocus
                                minlength="3"
                                maxlength="100"
                            >
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group-premium">
                        <label class="form-label-premium">
                            <i class="bi bi-envelope"></i>
                            Correo electrónico
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input 
                                type="email" 
                                name="correo" 
                                class="form-control-premium" 
                                placeholder="tu@email.com"
                                value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <!-- Teléfono -->
                    <div class="form-group-premium">
                        <label class="form-label-premium">
                            <i class="bi bi-phone"></i>
                            Teléfono (opcional)
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-phone-fill input-icon"></i>
                            <input 
                                type="tel" 
                                name="telefono" 
                                class="form-control-premium" 
                                placeholder="300 123 4567"
                                value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                            >
                        </div>
                    </div>
                    
                    <!-- Contraseña -->
                    <div class="form-group-premium">
                        <label class="form-label-premium">
                            <i class="bi bi-lock"></i>
                            Contraseña
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-control-premium" 
                                placeholder="Mínimo 6 caracteres"
                                required
                                minlength="6"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Confirmar Contraseña -->
                    <div class="form-group-premium">
                        <label class="form-label-premium">
                            <i class="bi bi-lock-fill"></i>
                            Confirmar contraseña
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input 
                                type="password" 
                                name="password_confirm" 
                                id="password_confirm" 
                                class="form-control-premium" 
                                placeholder="Repite tu contraseña"
                                required
                                minlength="6"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirm', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" class="btn-premium">
                        <i class="bi bi-check-circle me-2"></i>
                        Crear mi cuenta
                    </button>
                </form>
                
                <div class="divider-premium">
                    <span>o continúa explorando</span>
                </div>
                
                <a href="<?= $base ?>index.php?r=/productos" class="btn-premium" style="background: linear-gradient(135deg, var(--primary-silver-dark), var(--primary-silver)); color: var(--primary-black);">
                    <i class="bi bi-grid me-2"></i>
                    Ver productos
                </a>
            </div>
            
            <div class="auth-footer">
                <p class="auth-link">
                    ¿Ya tienes cuenta? 
                    <a href="<?= $base ?>index.php?r=/login">Inicia sesión</a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base ?>assets/js/auth.js"></script>
</body>
</html>
