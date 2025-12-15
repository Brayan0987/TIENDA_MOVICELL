<?php
require_once __DIR__ . '/../../Controllers/Admin/editar_usuario_controller.php';
require_once __DIR__ . '/../../../includes/layout.php';

renderHeader(
    'Editar Perfil',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/editar_usuario.css?v='.time().'">
    <link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/alerts.css?v='.time().'">'
);
?>

<main class="editar-container">

    <!-- Header del módulo -->
    <header class="editar-header">
        <div class="header-icon">
            <i class="bi bi-pencil-square"></i>
        </div>
        <div class="header-text">
            <h1 class="header-title">Editar Perfil</h1>
            <p class="header-subtitle">Actualiza la información de la cuenta</p>
        </div>
    </header>

    <div class="editar-wrapper">

        <?php if ($dbError): ?>
            <div class="error-card">
                <i class="bi bi-exclamation-triangle"></i>
                <p>Error de conexión a la base de datos.</p>
            </div>

        <?php elseif ($user): ?>
            
            <?php 
            // Mostrar mensajes de error si existen en URL
            $error = $_GET['error'] ?? null;
            $errorMsgs = [
                'invalid' => 'Por favor completa todos los campos requeridos.',
                'img_invalid' => 'La imagen no es válida o excede el tamaño máximo (3MB).',
                'update_failed' => 'Error al guardar los cambios. Intenta nuevamente.',
            ];
            ?>
            
            <?php if ($error && isset($errorMsgs[$error])): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <i class="bi bi-exclamation-circle"></i>
                    <?= $errorMsgs[$error] ?>
                </div>
            <?php endif; ?>

            <article class="editar-card">
                
                <form id="perfilForm"
                      method="POST"
                      action="/TIENDA_MOVICELL/public/index.php?r=/admin/editar_usuario"
                      enctype="multipart/form-data"
                      class="editar-form">
                    
                    <input type="hidden" name="id_usuario" value="<?= intval($user['id_usuario']) ?>">

                    <!-- Sección Avatar -->
                    <div class="form-section section-avatar">
                        <h3 class="section-title">
                            <i class="bi bi-person-circle"></i>
                            Foto de Perfil
                        </h3>

                        <?php 
                        $placeholderLetter = !empty($user['nombre']) 
                            ? htmlspecialchars(strtoupper(substr($user['nombre'], 0, 1))) 
                            : 'U';
                        ?>

                        <div class="avatar-upload-wrapper">
                            <label class="avatar-upload-btn" for="fileInput" title="Cambiar foto">
                                <div id="avatarPreview" class="avatar-preview">
                                    <?php if (!empty($user['imagen'])): ?>
                                        <img src="/TIENDA_MOVICELL/Public/<?= htmlspecialchars($user['imagen']) ?>"
                                             alt="Avatar"
                                             class="avatar-img">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <?= $placeholderLetter ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="avatar-overlay">
                                    <i class="bi bi-camera-fill"></i>
                                </div>
                            </label>
                            
                            <input id="fileInput"
                                   type="file"
                                   name="imagen"
                                   accept="image/*"
                                   class="file-input">

                            <p class="avatar-hint">
                                <i class="bi bi-info-circle"></i>
                                Haz clic para cambiar la foto
                            </p>
                        </div>
                    </div>

                    <!-- Sección Datos -->
                    <div class="form-section section-datos">
                        <h3 class="section-title">
                            <i class="bi bi-person-lines-fill"></i>
                            Información Personal
                        </h3>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="nombre">
                                    <i class="bi bi-person"></i>
                                    Nombre Completo
                                </label>
                                <input class="form-input"
                                       id="nombre"
                                       type="text"
                                       name="nombre"
                                       value="<?= htmlspecialchars($user['nombre']) ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="correo">
                                    <i class="bi bi-envelope"></i>
                                    Correo Electrónico
                                </label>
                                <input class="form-input"
                                       id="correo"
                                       type="email"
                                       name="correo"
                                       value="<?= htmlspecialchars($user['correo']) ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="telefono">
                                    <i class="bi bi-telephone"></i>
                                    Teléfono
                                </label>
                                <input class="form-input"
                                       id="telefono"
                                       type="tel"
                                       name="telefono"
                                       value="<?= htmlspecialchars($user['telefono'] ?? '') ?>">
                            </div>

                            <div class="form-group form-group-readonly">
                                <label class="form-label">
                                    <i class="bi bi-shield-check"></i>
                                    Rol Asignado
                                </label>
                                <div class="readonly-value">
                                    <span class="rol-badge">
                                        <?= htmlspecialchars($user['tipo_rol'] ?? 'Sin rol') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <footer class="form-actions">
                        <button type="submit" class="btn-action btn-primary" id="btnGuardar">
                            <i class="bi bi-check-circle"></i>
                            Guardar Cambios
                        </button>

                        <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/perfil"
                           class="btn-action btn-secondary">
                            <i class="bi bi-x-circle"></i>
                            Cancelar
                        </a>
                    </footer>

                </form>

            </article>

        <?php else: ?>
            <div class="error-card">
                <i class="bi bi-person-x"></i>
                <p>Usuario no encontrado.</p>
            </div>
        <?php endif; ?>

    </div>

</main>

<script src="/TIENDA_MOVICELL/public/assets/JS/perfil_mejorado.js"></script>
<?php renderFooter(); ?>
