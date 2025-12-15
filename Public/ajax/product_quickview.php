<?php
// Public/ajax/product_quickview.php
// Devuelve un fragmento HTML con la vista rápida del producto (para modal)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../App/Core/conexion.php';
$con = conectar();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Producto no válido';
    exit;
}

// Consulta básica para obtener datos del celular
$sql = "SELECT c.id_celulares, p.nombre AS producto, pr.precio, m.marca, c.cantidad_stock AS cantidad,
        col.color, r.ram, a.almacenamiento,
        (SELECT imagen_url FROM imagenes_celulares WHERE id_celulares = c.id_celulares AND es_principal = 1 LIMIT 1) AS imagen_url
    FROM celulares c
    LEFT JOIN producto p ON c.id_producto = p.id_producto
    LEFT JOIN precio pr ON c.id_precio = pr.id_precio
    LEFT JOIN marcas m ON c.id_marcas = m.id_marcas
    LEFT JOIN color col ON c.id_color = col.id_color
    LEFT JOIN ram r ON c.id_ram = r.id_ram
    LEFT JOIN almacenamiento a ON c.id_almacenamiento = a.id_almacenamiento
    WHERE c.id_celulares = ? AND c.cantidad_stock > 0
    LIMIT 1";

if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
} else {
    http_response_code(500);
    echo 'Error de consulta';
    exit;
}

if (!$product) {
    http_response_code(404);
    echo '<div class="p-4">Producto no encontrado o sin stock.</div>';
    exit;
}

// Obtener imágenes adicionales si existen
$images = [];
$imgSql = "SELECT imagen_url FROM imagenes_celulares WHERE id_celulares = ? ORDER BY es_principal DESC, id_imagen LIMIT 5";
if ($stmt2 = $con->prepare($imgSql)) {
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($row = $res2->fetch_assoc()) {
        $images[] = $row['imagen_url'];
    }
    $stmt2->close();
}

$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), "/\\") . "/"; // nivel Public/

// Normalizar ruta de imagen si es relativa
if (!empty($images)) {
  $firstImg = $images[0];
  if (!preg_match('#^(https?:)?//#i', $firstImg) && strpos($firstImg, '/') !== 0) {
    $imgSrc = $base . ltrim($firstImg, '/');
  } else {
    $imgSrc = $firstImg;
  }
} else {
  $imgSrc = $base . 'assets/Imagenes/default.jpg';
}

ob_start();
?>
<div class="quickview-content container p-3">
  <div class="row">
    <div class="col-md-6 text-center">
      <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($product['producto']) ?>" style="max-width:100%; height:auto; object-fit:contain;">
    </div>
    <div class="col-md-6">
      <h5 class="mb-1 text-muted"><?= htmlspecialchars($product['marca']) ?></h5>
      <h3 class="fw-bold"><?= htmlspecialchars($product['producto']) ?></h3>
      <div class="h4 text-primary fw-bold mb-3">$<?= number_format($product['precio'],0,',','.') ?></div>
      <p><strong>RAM:</strong> <?= htmlspecialchars($product['ram'] ?? '') ?> &nbsp; <strong>Almacenamiento:</strong> <?= htmlspecialchars($product['almacenamiento'] ?? '') ?></p>
      <p><strong>Color:</strong> <?= htmlspecialchars($product['color'] ?? '') ?></p>

      <div class="d-flex gap-2 mt-3">
        <form class="add-to-cart-quick" method="post" action="<?= $base ?>index.php?r=/cart/add">
          <input type="hidden" name="product_id" value="<?= (int)$product['id_celulares'] ?>">
          <input type="hidden" name="name" value="<?= htmlspecialchars($product['marca'] . ' ' . $product['producto']) ?>">
          <input type="hidden" name="price" value="<?= (float)$product['precio'] ?>">
          <input type="hidden" name="image" value="<?= htmlspecialchars($imgSrc) ?>">
          <input type="hidden" name="quantity" value="1">
          <button type="submit" class="btn btn-primary">Agregar al carrito</button>
        </form>
        <a href="<?= $base ?>index.php?r=/producto-detalle&id=<?= (int)$product['id_celulares'] ?>" class="btn btn-outline-secondary">Ver detalles</a>
      </div>
    </div>
  </div>
</div>
<?php
$html = ob_get_clean();

// Devolver el fragmento
header('Content-Type: text/html; charset=utf-8');
echo $html;
exit;
