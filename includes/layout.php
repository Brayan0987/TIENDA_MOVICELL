<?php
function renderHeader(string $title, string $additionalStyles = '') {
    // Calcular baseUrl (raíz pública del sitio)
    $baseUrl = '';
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        if ($baseUrl === '/' || $baseUrl === '\\') {
            $baseUrl = '';
        }
    }
    if (strpos($baseUrl, '/public') === false) {
        $baseUrl .= '/public';
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $r = $_GET['r'] ?? '';
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> - Movil Cell</title>

        <!-- CSS y fuentes globales -->
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">

        <!-- Estilos propios de cada vista -->
        <?= $additionalStyles ?>
    </head>
    <body>

    <div class="logo-wrapper">
        <img src="/TIENDA_MOVICELL/Public/assets/Imagenes/Logo_movicel.jpg"
             alt="MOVILCEL STORE"
             class="logo-movilcel">
    </div>

    <nav class="admin-navbar">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                <a href="<?= $baseUrl ?>/index.php?r=/admin/perfil"
                   class="btn btn-sm<?= $r === '/admin/perfil' ? ' btn-primary' : ' btn-outline-light' ?>">
                    <i class="bi bi-person"></i> Perfil
                </a>

                <a href="<?= $baseUrl ?>/index.php?r=/admin/productos"
                   class="btn btn-sm<?= $r === '/admin/productos' ? ' btn-primary' : ' btn-outline-light' ?>">
                    <i class="bi bi-phone"></i> Productos
                </a>

                <a href="<?= $baseUrl ?>/index.php?r=/admin/insertar-producto"
                   class="btn btn-sm<?= $r === '/admin/insertar-producto' ? ' btn-primary' : ' btn-outline-light' ?>">
                    <i class="bi bi-plus-circle"></i> Agregar Productos
                </a>

                <a href="<?= $baseUrl ?>/index.php?r=/admin/marcas_precios"
                   class="btn btn-sm<?= $r === '/admin/marcas_precios' ? ' btn-primary' : ' btn-outline-light' ?>">
                    <i class="bi bi-gear"></i> Gestión de Atributos
                </a>

                <a href="<?= $baseUrl ?>/index.php?r=/admin/visualizar_usuarios"
                   class="btn btn-sm<?= $r === '/admin/visualizar_usuarios' ? ' btn-primary' : ' btn-outline-light' ?>">
                    <i class="bi bi-people"></i> Visualizar Usuarios
                </a>

                <a href="<?= $baseUrl ?>/index.php?r=/admin/ventas"
                   class="btn btn-sm<?= $r === '/admin/ventas' ? ' btn-primary' : ' btn-outline-light' ?>">
                    <i class="bi bi-bag-check"></i> Gestión de Ventas
                </a>

                <!-- NUEVO: Gestión de Cupones -->
                <a href="<?= $baseUrl ?>/index.php?r=/admin/cupones"
                   class="btn btn-sm<?= $r === '/admin/cupones' ? ' btn-primary' : ' btn-outline-light' ?>">
                    <i class="bi bi-ticket-perforated"></i> Cupones
                </a>

                <a href="<?= $baseUrl ?>/index.php?r=/"
                   class="btn btn-sm btn-outline-light">
                    <i class="bi bi-house"></i> Ir al Sitio
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= $baseUrl ?>/index.php?r=/logout"
                       class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/index.php?r=/login"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <?php
}

function renderFooter() {
    // Calcular baseUrl (raíz pública del sitio)
    $baseUrl = '';
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        if ($baseUrl === '/' || $baseUrl === '\\') {
            $baseUrl = '';
        }
    }
    if (strpos($baseUrl, '/public') === false) {
        $baseUrl .= '/public';
    }
    ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?= $baseUrl ?>/assets/JS/sidebar.js"></script>
    </body>
    </html>
    <?php
}
?>
