<?php
if (session_status() !== PHP_SESSION_ACTIVE) { 
    session_start(); 
}

// === VERIFICACIÓN DE ROL: REDIRIGIR ADMINS A SU PANEL ===
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
    header('Location: ' . $base . 'index.php?r=/admin/productos');
    exit;
}
// ========================================================

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\") . "/";
$csrf = $_SESSION['csrf_token'] ?? ($_SESSION['csrf_token'] = bin2hex(random_bytes(32)));


// Obtener datos del usuario desde el controlador o la BD
if (!isset($user) || empty($user)) {
    require_once __DIR__ . '/../../Models/User.php';
    $userModel = new \App\Models\User();
    
    if (!empty($_SESSION['user_id'])) {
        $user = $userModel->findById((int)$_SESSION['user_id']);
        if (!$user) {
            header('Location: ' . $base . 'index.php?r=/logout');
            exit;
        }
    } else {
        header('Location: ' . $base . 'index.php?r=/login');
        exit;
    }
}

$orders = $orders ?? [];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Cuenta - Movi Cell</title>
    <base href="<?= htmlspecialchars($base, ENT_QUOTES) ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?= $base ?>assets/css/panel.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar Elite -->
    <nav class="navbar-elite">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center w-100">
                <a class="navbar-brand-elite" href="<?= $base ?>">
                    <div class="brand-icon">
                        <i class="bi bi-phone-fill"></i>
                    </div>
                    <span>Movi Cell</span>
                </a>
                <a href="<?=$base?>index.php?r=/logout" class="btn-logout-elite">
                    <i class="bi bi-box-arrow-right"></i>
                    Salir
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="panel-container-elite">
        <!-- Hero Section -->
        <div class="hero-section animate-in">
            <div class="hero-avatar">
                <?= strtoupper(substr(htmlspecialchars($user['nombre'] ?? 'U'), 0, 1)) ?>
            </div>
            <h1 class="hero-title">
                Bienvenido, <?= htmlspecialchars(explode(' ', $user['nombre'] ?? 'Usuario')[0]) ?>
            </h1>
            <p class="hero-subtitle">
                <i class="bi bi-envelope-at me-2"></i>
                <?= htmlspecialchars($user['correo'] ?? '') ?>
            </p>
        </div>

        <!-- Alerts -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-elite alert-danger-elite">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.25rem;"></i>
                <span><?=$_SESSION['error']; unset($_SESSION['error']);?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert-elite alert-success-elite">
                <i class="bi bi-check-circle-fill" style="font-size: 1.25rem;"></i>
                <span><?=$_SESSION['success']; unset($_SESSION['success']);?></span>
            </div>
        <?php endif; ?>

        <!-- Cards Grid -->
        <div class="row g-4 mb-5">
            <!-- Profile Card -->
            <div class="col-lg-6">
                <div class="card-elite profile animate-in">
                    <div class="card-header-elite">
                        <div class="card-icon-elite">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div>
                            <h3 class="card-title-elite">Perfil Personal</h3>
                            <p class="card-subtitle-elite">Actualiza tu información personal</p>
                        </div>
                    </div>
                    
                    <form method="post" action="index.php?r=/panel/profile">
                        <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf, ENT_QUOTES)?>">
                        
                        <div class="form-group-elite">
                            <input type="text" class="form-control-elite" id="name" name="name" 
                                   placeholder=" " value="<?=htmlspecialchars($user['nombre'] ?? '')?>" required>
                            <label for="name" class="form-label-elite">Nombre completo</label>
                        </div>
                        
                        <div class="form-group-elite">
                            <input type="email" class="form-control-elite" id="email" name="email" 
                                   placeholder=" " value="<?=htmlspecialchars($user['correo'] ?? '')?>" required>
                            <label for="email" class="form-label-elite">Correo electrónico</label>
                        </div>
                        
                        <div class="form-group-elite">
                            <input type="tel" class="form-control-elite" id="telefono" name="telefono" 
                                   placeholder=" " value="<?=htmlspecialchars($user['telefono'] ?? '')?>">
                            <label for="telefono" class="form-label-elite">Teléfono (opcional)</label>
                        </div>
                        
                        <button type="submit" class="btn-elite btn-primary-elite">
                            <i class="bi bi-check2-circle"></i>
                            Actualizar Información
                        </button>
                    </form>
                </div>
            </div>

            <!-- Security Card -->
            <div class="col-lg-6">
                <div class="card-elite security animate-in">
                    <div class="card-header-elite">
                        <div class="card-icon-elite">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h3 class="card-title-elite">Seguridad</h3>
                            <p class="card-subtitle-elite">Cambia tu contraseña de acceso</p>
                        </div>
                    </div>
                    
                    <form method="post" action="index.php?r=/panel/password">
                        <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf, ENT_QUOTES)?>">
                        
                        <div class="form-group-elite">
                            <input type="password" class="form-control-elite" id="password" name="password" 
                                   placeholder=" " minlength="6" required>
                            <label for="password" class="form-label-elite">Nueva contraseña</label>
                        </div>
                        
                        <div class="form-group-elite">
                            <input type="password" class="form-control-elite" id="password_confirm" name="password_confirm" 
                                   placeholder=" " minlength="6" required>
                            <label for="password_confirm" class="form-label-elite">Confirmar contraseña</label>
                        </div>
                        
                        <button type="submit" class="btn-elite btn-success-elite">
                            <i class="bi bi-shield-check"></i>
                            Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mis Pedidos -->
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card-elite profile animate-in">
                    <div class="card-header-elite">
                        <div class="card-icon-elite">
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <div>
                            <h3 class="card-title-elite">Mis Pedidos</h3>
                            <p class="card-subtitle-elite">Historial completo de tus compras</p>
                        </div>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                        <div class="alert-elite alert-danger-elite">
                            <i class="bi bi-info-circle" style="font-size: 1.25rem;"></i>
                            <span>No tienes pedidos aún. <a href="<?= $base ?>index.php?r=/productos" style="color: inherit; text-decoration: underline;">Ver productos</a></span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead style="background: var(--bg-secondary);">
                                    <tr>
                                        <th style="padding: 1rem; font-weight: 700;">#Pedido</th>
                                        <th style="padding: 1rem; font-weight: 700;">Fecha</th>
                                        <th style="padding: 1rem; font-weight: 700;">Total</th>
                                        <th style="padding: 1rem; font-weight: 700;">Estado</th>
                                        <th style="padding: 1rem; font-weight: 700;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr style="border-bottom: 1px solid var(--border-light);">
                                            <td style="padding: 1rem;">
                                                <strong style="font-size: 1.1rem; color: var(--accent-primary);">#<?= $order['id_pedido'] ?></strong>
                                            </td>
                                            <td style="padding: 1rem; color: var(--text-secondary);">
                                                <?= date('d/m/Y', strtotime($order['fecha'])) ?>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <strong>$<?= number_format($order['total'], 0, ',', '.') ?></strong>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <?php
                                                $estado = strtolower($order['estado_nombre'] ?? 'pendiente');
                                                $badgeColors = [
                                                    'pendiente'  => '#ffc107',
                                                    'procesando' => '#0dcaf0',
                                                    'enviado'    => '#0d6efd',
                                                    'entregado'  => '#198754',
                                                    'cancelado'  => '#dc3545',
                                                ];
                                                $badgeColor = $badgeColors[$estado] ?? '#6c757d';
                                                ?>
                                                <span style="background: <?= $badgeColor ?>; color: white; padding: 0.5rem 1rem; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.875rem;">
                                                    <?= ucfirst($order['estado_nombre'] ?? 'Pendiente') ?>
                                                </span>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                    <a href="<?= $base ?>index.php?r=/orders&id=<?= $order['id_pedido'] ?>" 
                                                       style="background: var(--accent-primary); color: white; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); text-decoration: none; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.2s;">
                                                        <i class="bi bi-eye"></i>
                                                        Detalle
                                                    </a>
                                                    <a href="<?= $base ?>index.php?r=/factura/descargar&id=<?= $order['id_pedido'] ?>" 
                                                       style="background: #10b981; color: white; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); text-decoration: none; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.2s;">
                                                        <i class="bi bi-download"></i>
                                                        PDF
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card-elite danger animate-in">
                    <div class="card-header-elite">
                        <div class="card-icon-elite">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h3 class="card-title-elite">Zona de Peligro</h3>
                            <p class="card-subtitle-elite">Esta acción eliminará permanentemente tu cuenta y todos tus datos.</p>
                        </div>
                    </div>
                    
                    <form method="post" action="index.php?r=/account/delete" id="deleteAccountForm">
                        <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf, ENT_QUOTES)?>">
                        <button type="submit" class="btn-elite btn-danger-elite">
                            <i class="bi bi-trash3"></i>
                            Eliminar Cuenta Permanentemente
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav-section-elite animate-in">
            <h3 class="nav-title-elite">
                <i class="bi bi-compass"></i>
                Explorar MoviCell
            </h3>
            
            <div class="nav-grid-elite">
                <a href="<?=$base?>" class="nav-btn-elite primary">
                    <div class="nav-icon-elite">
                        <i class="bi bi-house-door"></i>
                    </div>
                    <span>Inicio</span>
                    <small>Página principal</small>
                </a>
                <a href="<?=$base?>index.php?r=/productos" class="nav-btn-elite">
                    <div class="nav-icon-elite">
                        <i class="bi bi-grid"></i>
                    </div>
                    <span>Productos</span>
                    <small>Ver catálogo</small>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
