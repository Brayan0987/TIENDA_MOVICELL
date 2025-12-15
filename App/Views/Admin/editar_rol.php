<?php
require_once __DIR__ . '/../../Controllers/Admin/editar_rol.php';
require_once __DIR__ . '/../../../includes/layout.php';

renderHeader(
    'Editar Rol de Usuario',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/editar_rol.css?v='.time().'">'
);
?>

<main class="editar-rol-container">

    <!-- Header del módulo -->
    <header class="editar-rol-header">
        <div class="header-icon">
            <i class="bi bi-shield-fill-check"></i>
        </div>
        <div class="header-text">
            <h1 class="header-title">Editar Rol de Usuario</h1>
            <p class="header-subtitle">Modifica los permisos de acceso</p>
        </div>
    </header>

    <div class="editar-rol-wrapper">

        <article class="editar-rol-card">
            
            <!-- Info del usuario -->
            <div class="usuario-info">
                <div class="usuario-avatar">
                    <?php 
                    $placeholderLetter = !empty($user['nombre']) 
                        ? htmlspecialchars(strtoupper(substr($user['nombre'], 0, 1))) 
                        : 'U';
                    ?>
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

                <div class="usuario-detalles">
                    <h2 class="usuario-nombre"><?= htmlspecialchars($user['nombre']) ?></h2>
                    <p class="usuario-id">
                        <i class="bi bi-hash"></i>
                        ID: <?= intval($user['id_usuario']) ?>
                    </p>
                    <?php if (!empty($user['correo'])): ?>
                        <p class="usuario-correo">
                            <i class="bi bi-envelope"></i>
                            <?= htmlspecialchars($user['correo']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Formulario de rol -->
            <form method="POST"
                  action="/TIENDA_MOVICELL/public/index.php?r=/admin/editar-rol&id=<?= intval($user['id_usuario']) ?>"
                  class="rol-form">
                
                <input type="hidden" name="id_usuario" value="<?= intval($user['id_usuario']) ?>">

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="bi bi-shield-lock"></i>
                        Asignar Rol
                    </h3>

                    <div class="form-group">
                        <label class="form-label" for="id_roles">
                            <i class="bi bi-gear"></i>
                            Seleccionar Rol
                        </label>
                        
                        <div class="select-wrapper">
                            <select name="id_roles" id="id_roles" class="form-select" required>
                                <option value="">-- Seleccione un rol --</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= intval($r['id_roles']) ?>"
                                            <?= ($curRole == $r['id_roles']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['tipo_rol']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="bi bi-chevron-down select-icon"></i>
                        </div>

                        <small class="form-hint">
                            <i class="bi bi-info-circle"></i>
                            El rol determina los permisos de acceso del usuario en el sistema
                        </small>
                    </div>
                </div>

                <!-- Botones de acción -->
                <footer class="form-actions">
                    <button type="submit" class="btn-action btn-primary">
                        <i class="bi bi-check-circle"></i>
                        Guardar Rol
                    </button>

                    <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/visualizar_usuarios"
                       class="btn-action btn-secondary">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>
                </footer>

            </form>

        </article>

    </div>

</main>

<?php renderFooter(); ?>
