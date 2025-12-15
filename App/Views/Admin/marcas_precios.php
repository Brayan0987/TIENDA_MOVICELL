<?php
require_once __DIR__ . '/../../Controllers/Admin/atributos_controller.php';
require_once __DIR__ . '/../../../includes/layout.php';

renderHeader(
    'Gestión de Atributos',
    '<link rel="stylesheet" href="/TIENDA_MOVICELL/Public/assets/Css/Admin/marcas_precios.css?v='.time().'">'
);
?>

<main class="atributos-container">

    <!-- Header del módulo -->
    <header class="atributos-header">
        <div class="header-icon">
            <i class="bi bi-gear-fill"></i>
        </div>
        <div class="header-text">
            <h1 class="header-title">Gestión de Atributos</h1>
            <p class="header-subtitle">Administra marcas, precios, colores y especificaciones</p>
        </div>
    </header>

    <!-- Barra de búsqueda global -->
    <div class="search-bar-global">
        <form class="search-form" id="searchForm" onsubmit="return false;">
            <i class="bi bi-search"></i>
            <input type="search"
                   id="searchInput"
                   name="q"
                   class="search-input"
                   placeholder="Buscar en todas las listas..."
                   value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="button" id="btnSearch" class="btn-search">
                Buscar
            </button>
        </form>
    </div>

    <!-- Alertas -->
    <?php if (!empty($msg)): ?>
        <div class="alert-custom alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($msg); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert-custom alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Grid de atributos -->
    <div class="atributos-grid">

        <!-- CARD: MARCAS -->
        <article class="atributo-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="bi bi-award-fill"></i>
                </div>
                <h2 class="card-title">Marcas</h2>
            </div>

            <form method="POST" action="" class="card-form">
                <div class="form-input-group">
                    <input type="text"
                           name="nueva_marca"
                           class="form-input"
                           placeholder="Nombre de la marca"
                           required>
                    <button type="submit"
                            name="submit_marca"
                            class="btn-add">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </form>

            <div class="card-list">
                <h3 class="list-title">Registradas (<?= count($marcas) ?>)</h3>
                <ul class="items-list">
                    <?php foreach($marcas as $row): ?>
                        <li class="list-item">
                            <span class="item-text"><?= htmlspecialchars($row['marca']); ?></span>
                            <form method="POST"
                                  action=""
                                  class="item-form"
                                  onsubmit="return confirm('¿Eliminar esta marca?')">
                                <input type="hidden" name="action" value="delete_attribute">
                                <input type="hidden" name="tipo" value="marca">
                                <input type="hidden" name="id" value="<?= $row['id_marcas']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </article>

        <!-- CARD: PRECIOS -->
        <article class="atributo-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <h2 class="card-title">Precios</h2>
            </div>

            <form method="POST" action="" class="card-form">
                <div class="form-input-group">
                    <input type="number"
                           step="0.01"
                           name="nuevo_precio"
                           class="form-input"
                           placeholder="Ej: 1500.00"
                           required>
                    <button type="submit"
                            name="submit_precio"
                            class="btn-add">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </form>

            <div class="card-list">
                <h3 class="list-title">Registrados (<?= count($precios) ?>)</h3>
                <ul class="items-list">
                    <?php foreach($precios as $row): ?>
                        <li class="list-item">
                            <span class="item-text item-price">$<?= number_format($row['precio'], 2); ?></span>
                            <form method="POST"
                                  action=""
                                  class="item-form"
                                  onsubmit="return confirm('¿Eliminar este precio?')">
                                <input type="hidden" name="action" value="delete_attribute">
                                <input type="hidden" name="tipo" value="precio">
                                <input type="hidden" name="id" value="<?= $row['id_precio']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </article>

        <!-- CARD: COLORES -->
        <article class="atributo-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="bi bi-palette-fill"></i>
                </div>
                <h2 class="card-title">Colores</h2>
            </div>

            <form method="POST" action="" class="card-form">
                <div class="form-input-group">
                    <input type="text"
                           name="nuevo_color"
                           class="form-input"
                           placeholder="Nombre del color"
                           required>
                    <button type="submit"
                            name="submit_color"
                            class="btn-add">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </form>

            <div class="card-list">
                <h3 class="list-title">Registrados (<?= count($colores) ?>)</h3>
                <ul class="items-list">
                    <?php foreach($colores as $row): ?>
                        <li class="list-item">
                            <span class="item-text"><?= htmlspecialchars($row['color']); ?></span>
                            <form method="POST"
                                  action=""
                                  class="item-form"
                                  onsubmit="return confirm('¿Eliminar este color?')">
                                <input type="hidden" name="action" value="delete_attribute">
                                <input type="hidden" name="tipo" value="color">
                                <input type="hidden" name="id" value="<?= $row['id_color']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </article>

        <!-- CARD: RAM -->
        <article class="atributo-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="bi bi-cpu-fill"></i>
                </div>
                <h2 class="card-title">RAM</h2>
            </div>

            <form method="POST" action="" class="card-form">
                <div class="form-input-group">
                    <input type="text"
                           name="nueva_ram"
                           class="form-input"
                           placeholder="Ej: 8GB"
                           required>
                    <button type="submit"
                            name="submit_ram"
                            class="btn-add">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </form>

            <div class="card-list">
                <h3 class="list-title">Registradas (<?= count($rams) ?>)</h3>
                <ul class="items-list">
                    <?php foreach($rams as $row): ?>
                        <li class="list-item">
                            <span class="item-text"><?= htmlspecialchars($row['ram']); ?></span>
                            <form method="POST"
                                  action=""
                                  class="item-form"
                                  onsubmit="return confirm('¿Eliminar esta RAM?')">
                                <input type="hidden" name="action" value="delete_attribute">
                                <input type="hidden" name="tipo" value="ram">
                                <input type="hidden" name="id" value="<?= $row['id_ram']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </article>

        <!-- CARD: ALMACENAMIENTO -->
        <article class="atributo-card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="bi bi-hdd-fill"></i>
                </div>
                <h2 class="card-title">Almacenamiento</h2>
            </div>

            <form method="POST" action="" class="card-form">
                <div class="form-input-group">
                    <input type="text"
                           name="nuevo_almacenamiento"
                           class="form-input"
                           placeholder="Ej: 256GB"
                           required>
                    <button type="submit"
                            name="submit_almacenamiento"
                            class="btn-add">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </form>

            <div class="card-list">
                <h3 class="list-title">Registrados (<?= count($almacenamientos) ?>)</h3>
                <ul class="items-list">
                    <?php foreach($almacenamientos as $row): ?>
                        <li class="list-item">
                            <span class="item-text"><?= htmlspecialchars($row['almacenamiento']); ?></span>
                            <form method="POST"
                                  action=""
                                  class="item-form"
                                  onsubmit="return confirm('¿Eliminar este almacenamiento?')">
                                <input type="hidden" name="action" value="delete_attribute">
                                <input type="hidden" name="tipo" value="almacenamiento">
                                <input type="hidden" name="id" value="<?= $row['id_almacenamiento']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </article>

    </div>

</main>

<script src="/TIENDA_MOVICELL/Public/assets/JS/marcas_precios_filtro_v2.js"></script>
<?php renderFooter(); ?>

