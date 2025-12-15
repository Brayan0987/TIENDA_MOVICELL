<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
$csrf = $_SESSION['csrf_token'] ?? ($_SESSION['csrf_token'] = bin2hex(random_bytes(32)));

require_once __DIR__ . '/../../Core/Cart.php';
$cart = new App\Core\Cart();
$cartCount = $cart->getTotalQuantity();

$token = htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar Contraseña - Movi Cell</title>
    
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
                <a href="<?= $base ?>index.php?r=/login" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    Volver a iniciar sesión
                </a>
                
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="bi bi-key"></i>
                    </div>
                    <h1 class="auth-title">Cambiar Contraseña</h1>
                    <p class="auth-subtitle">Ingresa tu nueva contraseña</p>
                </div>
                
                <!-- Alerts -->
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert-premium alert-danger-premium">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Form -->
                <form method="post" action="<?= $base ?>index.php?r=/reset-password" id="resetForm">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    
                    <!-- Nueva Contraseña -->
                    <div class="form-group-premium">
                        <label class="form-label-premium">
                            <i class="bi bi-lock"></i>
                            Nueva Contraseña
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
                                autofocus
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Confirmar Contraseña -->
                    <div class="form-group-premium">
                        <label class="form-label-premium">
                            <i class="bi bi-lock"></i>
                            Confirmar Contraseña
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
                    
                    <!-- Requirements -->
                    <div class="need-account-box">
                        <p style="font-size: 13px;">
                            ✓ Mínimo 6 caracteres<br>
                            ✓ Las contraseñas deben ser iguales
                        </p>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" class="btn-premium">
                        <i class="bi bi-check-lg me-2"></i>
                        Cambiar Contraseña
                    </button>
                </form>
                
                <div class="divider-premium">
                    <span>o vuelve</span>
                </div>
                
                <a href="<?= $base ?>index.php?r=/login" class="btn-premium" style="background: linear-gradient(135deg, var(--primary-silver-dark), var(--primary-silver)); color: var(--primary-black);">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver a Iniciar Sesión
                </a>
            </div>
            
            <div class="auth-footer">
                <p class="auth-link">
                    Este enlace expira en 24 horas
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base ?>assets/js/auth.js"></script>
</body>
</html>