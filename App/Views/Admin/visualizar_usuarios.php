<?php
require_once __DIR__ . '/../../Controllers/Admin/usuarios_controller.php';
require_once __DIR__ . '/../../../includes/layout.php';

renderHeader(
    'Gestión de Usuarios',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/visualizar_usuarios.css?v='.time().'">'
);
?>

<main class="usuarios-container">

    <!-- Header del módulo -->
    <header class="usuarios-header">
        <div class="header-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="header-text">
            <h1 class="header-title">Gestión de Usuarios</h1>
            <p class="header-subtitle">Administra cuentas y permisos del sistema</p>
        </div>
    </header>

    <!-- Alertas -->
    <?php if (!empty($msg)): ?>
        <div class="alert-custom alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert-custom alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="usuarios-content">

        <!-- CARD: Registrar Usuario -->
        <aside class="registro-card">
            <div class="registro-header">
                <div class="registro-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div class="registro-title-group">
                    <h2 class="registro-title">Nuevo Usuario</h2>
                    <p class="registro-desc">Crear cuenta de acceso</p>
                </div>
            </div>

            <form method="POST"
                  action="/TIENDA_MOVICELL/public/index.php?r=/admin/visualizar_usuarios"
                  class="registro-form">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-person"></i>
                        Nombre Completo
                    </label>
                    <input type="text"
                           name="nombre"
                           class="form-input"
                           placeholder="Ej: Juan Pérez"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-envelope"></i>
                        Correo Electrónico
                    </label>
                    <input type="email"
                           name="correo"
                           class="form-input"
                           placeholder="usuario@ejemplo.com"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-telephone"></i>
                        Teléfono
                    </label>
                    <input type="tel"
                           name="telefono"
                           class="form-input"
                           placeholder="Opcional">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-lock"></i>
                        Contraseña
                    </label>
                    <input type="password"
                           name="password"
                           class="form-input"
                           placeholder="Mínimo 6 caracteres"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-shield-check"></i>
                        Rol
                    </label>
                    <select name="id_rol" class="form-select" required>
                        <option value="">Seleccionar rol...</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['id_roles'] ?>">
                                <?= htmlspecialchars($rol['tipo_rol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="registrar_usuario" class="btn-submit">
                    <i class="bi bi-plus-circle"></i>
                    Registrar Usuario
                </button>
            </form>
        </aside>

        <!-- SECCIÓN: Listado de Usuarios -->
        <section class="listado-section">
            
            <!-- Filtros y búsqueda -->
            <div class="filtros-card">
                <form method="POST"
                      action="/TIENDA_MOVICELL/public/index.php?r=/admin/visualizar_usuarios"
                      class="filtros-form">
                    <input type="hidden" name="r" value="/admin/visualizar_usuarios">

                    <div class="filtro-search">
                        <i class="bi bi-search"></i>
                        <input type="search"
                               id="buscador"
                               name="q"
                               class="search-input"
                               placeholder="Buscar por nombre o correo..."
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>

                    <div class="filtro-rol">
                        <select name="filtro_rol" class="form-select">
                            <option value="">Todos los Roles</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id_roles'] ?>"
                                        <?= ($filtro_rol == $rol['id_roles']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rol['tipo_rol']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-filtrar">
                        <i class="bi bi-funnel"></i>
                        Filtrar
                    </button>
                </form>
            </div>

            <!-- Stats -->
            <div class="usuarios-stats">
                <div class="stat-mini">
                    <i class="bi bi-person-check"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?= count($usuarios) ?></span>
                        <span class="stat-label">Usuarios</span>
                    </div>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="tabla-wrapper">
                <table class="usuarios-table">
                    <thead>
                        <tr>
                            <th class="th-avatar">Avatar</th>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th class="th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td class="td-avatar">
                                    <?php if (!empty($u['imagen'])): ?>
                                        <img src="/TIENDA_MOVICELL/Public/<?= htmlspecialchars($u['imagen']) ?>"
                                             class="user-avatar"
                                             alt="Avatar">
                                    <?php else: ?>
                                        <div class="user-avatar-placeholder">
                                            <?= htmlspecialchars(strtoupper(substr($u['nombre'], 0, 1))) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="td-usuario">
                                    <span class="usuario-nombre"><?= htmlspecialchars($u['nombre']) ?></span>
                                    <span class="usuario-id">ID: <?= intval($u['id_usuario']) ?></span>
                                </td>

                                <td class="td-correo"><?= htmlspecialchars($u['correo']) ?></td>

                                <td class="td-telefono"><?= htmlspecialchars($u['telefono'] ?? 'N/A') ?></td>

                                <td class="td-rol">
                                    <span class="rol-badge">
                                        <?= htmlspecialchars($u['tipo_rol'] ?? 'Sin rol') ?>
                                    </span>
                                </td>

                                <td class="td-actions">
                                    <div class="actions-group">
                                        <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/perfil&id=<?= intval($u['id_usuario']) ?>"
                                           class="action-btn action-view"
                                           title="Ver perfil">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        <a href="/TIENDA_MOVICELL/public/index.php?r=/admin/editar-rol&id=<?= intval($u['id_usuario']) ?>"
                                           class="action-btn action-edit"
                                           title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <form method="POST"
                                              action="/TIENDA_MOVICELL/public/index.php?r=/admin/visualizar_usuarios"
                                              style="display:inline;"
                                              onsubmit="return confirm('¿Eliminar este usuario?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id_usuario" value="<?= intval($u['id_usuario']) ?>">
                                            <button type="submit"
                                                    class="action-btn action-delete"
                                                    title="Eliminar">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>No se encontraron usuarios con esos filtros.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </section>

    </div>

</main>

<?php renderFooter(); ?>
