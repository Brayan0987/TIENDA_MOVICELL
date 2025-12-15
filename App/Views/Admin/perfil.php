<?php
require_once __DIR__ . '/../../Controllers/Admin/perfil_controller.php';
require_once __DIR__ . '/../../../includes/layout.php';

renderHeader(
    'Perfil de Usuario',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/perfil.css?v='.time().'">'
);
?>

<main class="perfil-container">
    <?php if ($dbError): ?>
        <div class="perfil-error">
            <i class="bi bi-exclamation-triangle"></i>
            <p>Error de conexión a la base de datos.</p>
        </div>

    <?php elseif ($user === null): ?>
        <div class="perfil-error">
            <i class="bi bi-person-x"></i>
            <h3>Perfil no encontrado</h3>
            <p>No se pudo cargar la información del usuario.</p>
            <a class="btn-link" href="/TIENDA_MOVICELL/public/index.php?r=/admin/visualizar_usuarios">
                Ver usuarios
            </a>
        </div>

    <?php else: ?>

        <div class="perfil-wrapper">
            
            <!-- Sidebar izquierdo: Avatar + nombre -->
            <aside class="perfil-sidebar">
                <?php
                $placeholderLetter = !empty($user['nombre'])
                    ? htmlspecialchars(strtoupper(substr($user['nombre'], 0, 1)))
                    : 'U';
                ?>
                
                <div class="sidebar-avatar">
                    <?php if (!empty($user['imagen'])): ?>
                        <img src="/TIENDA_MOVICELL/Public/<?= htmlspecialchars($user['imagen']) ?>"
                             alt="Avatar"
                             class="avatar-image">
                    <?php else: ?>
                        <div class="avatar-letter">
                            <?= $placeholderLetter ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h2 class="sidebar-name"><?= htmlspecialchars($user['nombre']) ?></h2>
                <span class="sidebar-badge"><?= htmlspecialchars($user['tipo_rol'] ?? 'Sin rol') ?></span>
            </aside>

            <!-- Contenido derecho: Info + acciones -->
            <div class="perfil-content">
                
                <header class="content-header">
                    <h3 class="content-title">Información de Contacto</h3>
                    <?php if (!empty($simulacion_sesion) && !empty($isOwner)): ?>
                        <span class="badge-dev">
                            <i class="bi bi-code-slash"></i>
                            Dev Mode
                        </span>
                    <?php endif; ?>
                </header>

                <div class="content-info">
                    <div class="info-item">
                        <i class="bi bi-envelope-fill"></i>
                        <div class="info-text">
                            <span class="info-key">Correo electrónico</span>
                            <span class="info-val"><?= htmlspecialchars($user['correo']) ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div class="info-text">
                            <span class="info-key">Teléfono</span>
                            <span class="info-val"><?= htmlspecialchars($user['telefono'] ?? 'No especificado') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Barra de acciones -->
                <footer class="content-actions">
                    <?php if (!empty($isOwner)): ?>
                        <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/editar_usuario"
                           class="action-btn action-primary">
                            <i class="bi bi-pencil-square"></i>
                            Editar
                        </a>

                        <a href="/TIENDA_MOVICELL/public/index.php?r=/logout"
                           class="action-btn action-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            Cerrar Sesión
                        </a>

                    <?php else: ?>
                        <?php if (isset($sessionId) && $sessionId > 0): ?>
                            <a class="action-btn action-primary"
                               href="/TIENDA_MOVICELL/public/index.php?r=/admin/perfil">
                                <i class="bi bi-person-circle"></i>
                                Ver Mi Perfil
                            </a>
                        <?php else: ?>
                            <a class="action-btn action-primary"
                               href="/TIENDA_MOVICELL/public/index.php?r=/login">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Iniciar sesión
                            </a>
                        <?php endif; ?>

                        <a class="action-btn action-secondary"
                           href="/TIENDA_MOVICELL/public/index.php?r=/admin/visualizar_usuarios">
                            <i class="bi bi-arrow-left"></i>
                            Volver
                        </a>
                    <?php endif; ?>
                </footer>

            </div>

        </div>

    <?php endif; ?>
</main>

<?php renderFooter(); ?>
