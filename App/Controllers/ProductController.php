<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Cart;

/**
 * ProductController
 * Maneja las operaciones relacionadas con productos
 */
final class ProductController extends Controller
{
    private $db;

    public function __construct()
    {
        // No llamar al constructor del padre
        $this->db = Db::conn();
    }

    /**
     * Mostrar detalle del producto (celular)
     * Ruta: index.php?r=/producto-detalle&id=ID
     */
    public function detail(int $id): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Validar ID
        if ($id < 1) {
            $_SESSION['error'] = 'ID de producto inválido.';
            $this->redirect('index.php?r=/productos');
            return;
        }

        // CONSULTAR CELULAR + IMAGEN PRINCIPAL
        $sql = "
            SELECT 
                c.id_celulares,
                c.cantidad_stock        AS stock,
                c.id_marcas,
                p.nombre                AS nombre,
                p.descripcion,
                pr.precio,
                m.marca,
                r.ram,
                a.almacenamiento,
                co.color,
                (
                    SELECT imagen_url 
                    FROM imagenes_celulares ic
                    WHERE ic.id_celulares = c.id_celulares
                    ORDER BY ic.es_principal DESC, ic.id_imagen ASC
                    LIMIT 1
                ) AS imagen
            FROM celulares c
            LEFT JOIN producto       p  ON c.id_producto       = p.id_producto
            LEFT JOIN precio         pr ON c.id_precio         = pr.id_precio
            LEFT JOIN marcas         m  ON c.id_marcas         = m.id_marcas
            LEFT JOIN ram            r  ON c.id_ram            = r.id_ram
            LEFT JOIN almacenamiento a  ON c.id_almacenamiento = a.id_almacenamiento
            LEFT JOIN color          co ON c.id_color          = co.id_color
            WHERE c.id_celulares = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result  = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        // Validar que existe
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado.';
            $this->redirect('index.php?r=/productos');
            return;
        }

        // Preparar datos derivados
        $product['ram_gb']            = $product['ram'] ?? '';
        $product['almacenamiento_gb'] = $product['almacenamiento'] ?? '';

        // Imagen por defecto si no hay en BD
        if (empty($product['imagen'])) {
            $product['imagen'] = 'assets/Imagenes/placeholder.jpg';
        }

        // Obtener todas las imágenes del producto para carrusel
        $productImages = [];
        $stmtImgs = $this->db->prepare('SELECT id_imagen, imagen_url, es_principal FROM imagenes_celulares WHERE id_celulares = ? ORDER BY es_principal DESC, id_imagen ASC');
        $stmtImgs->bind_param('i', $id);
        $stmtImgs->execute();
        $resImgs = $stmtImgs->get_result();
        while ($rowImg = $resImgs->fetch_assoc()) {
            $productImages[] = $rowImg['imagen_url'];
        }
        $stmtImgs->close();

        if (empty($productImages)) {
            $productImages[] = $product['imagen'];
        }
        $product['images'] = $productImages;

        // PRODUCTOS RELACIONADOS (misma marca)
        $related = [];
        if (!empty($product['id_marcas'])) {
            $sqlRelated = "
                SELECT 
                    c.id_celulares,
                    p.nombre,
                    pr.precio,
                    m.marca,
                    (
                        SELECT imagen_url 
                        FROM imagenes_celulares ic
                        WHERE ic.id_celulares = c.id_celulares
                        ORDER BY ic.es_principal DESC, ic.id_imagen ASC
                        LIMIT 1
                    ) AS imagen
                FROM celulares c
                LEFT JOIN producto p  ON c.id_producto = p.id_producto
                LEFT JOIN precio  pr ON c.id_precio   = pr.id_precio
                LEFT JOIN marcas  m  ON c.id_marcas   = m.id_marcas
                WHERE c.id_marcas = ?
                  AND c.id_celulares != ?
                  AND c.cantidad_stock > 0
                ORDER BY RAND()
                LIMIT 4
            ";

            $stmtRelated = $this->db->prepare($sqlRelated);
            $stmtRelated->bind_param('ii', $product['id_marcas'], $id);
            $stmtRelated->execute();
            $resultRelated = $stmtRelated->get_result();

            while ($row = $resultRelated->fetch_assoc()) {
                if (empty($row['imagen'])) {
                    $row['imagen'] = 'assets/Imagenes/placeholder-small.jpg';
                }
                $related[] = $row;
            }
            $stmtRelated->close();
        }

        // CONTADOR DEL CARRITO
        $cart      = new Cart();
        $cartCount = $cart->getTotalQuantity();

        // CARGAR VISTA
        $this->view('producto-detalle', [
            'product'   => $product,
            'related'   => $related,
            'cartCount' => $cartCount,
        ]);
    }

    /**
     * Listar todos los productos (página de catálogo)
     */
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Filtros
        $search   = $_GET['search']    ?? '';
        $brand    = $_GET['brand']     ?? '';
        $minPrice = (int)($_GET['min_price'] ?? 0);
        $maxPrice = (int)($_GET['max_price'] ?? 0);

        // Consulta base con imagen principal
        $query = "
            SELECT 
                c.id_celulares,
                c.cantidad_stock AS stock,
                p.nombre,
                p.descripcion,
                pr.precio,
                m.marca,
                r.ram,
                a.almacenamiento,
                (
                    SELECT imagen_url 
                    FROM imagenes_celulares ic
                    WHERE ic.id_celulares = c.id_celulares
                    ORDER BY ic.es_principal DESC, ic.id_imagen ASC
                    LIMIT 1
                ) AS imagen
            FROM celulares c
            LEFT JOIN producto       p  ON c.id_producto       = p.id_producto
            LEFT JOIN precio         pr ON c.id_precio         = pr.id_precio
            LEFT JOIN marcas         m  ON c.id_marcas         = m.id_marcas
            LEFT JOIN ram            r  ON c.id_ram            = r.id_ram
            LEFT JOIN almacenamiento a  ON c.id_almacenamiento = a.id_almacenamiento
            WHERE c.cantidad_stock > 0
        ";

        $params = [];
        $types  = '';

        // Filtro de búsqueda
        if (!empty($search)) {
            $query      .= ' AND (p.nombre LIKE ? OR m.marca LIKE ?)';
            $searchTerm  = '%' . $search . '%';
            $params[]    = $searchTerm;
            $params[]    = $searchTerm;
            $types      .= 'ss';
        }

        // Filtro de marca
        if (!empty($brand)) {
            $query   .= ' AND m.marca = ?';
            $params[] = $brand;
            $types   .= 's';
        }

        // Filtro de precio
        if ($minPrice > 0) {
            $query   .= ' AND pr.precio >= ?';
            $params[] = $minPrice;
            $types   .= 'i';
        }

        if ($maxPrice > 0) {
            $query   .= ' AND pr.precio <= ?';
            $params[] = $maxPrice;
            $types   .= 'i';
        }

        $query .= ' ORDER BY p.nombre ASC';

        // Ejecutar consulta
        $products = [];

        if (!empty($params)) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($query);
        }

        while ($row = $result->fetch_assoc()) {
            if (empty($row['imagen'])) {
                $row['imagen'] = 'assets/Imagenes/placeholder.jpg';
            }
            $products[] = $row;
        }
        if (isset($stmt)) {
            $stmt->close();
        }

        // Contador del carrito
        $cart      = new Cart();
        $cartCount = $cart->getTotalQuantity();

        // Cargar vista
        $this->view('productos', [
            'products' => $products,
            'cartCount'=> $cartCount,
            'search'   => $search,
            'brand'    => $brand,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }

    /**
     * Buscar productos
     */
    public function search(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $searchTerm = $_GET['q'] ?? '';

        if (empty($searchTerm)) {
            $this->redirect('index.php?r=/productos');
            return;
        }

        $searchPattern = '%' . $searchTerm . '%';

        $stmt = $this->db->prepare('
            SELECT 
                c.id_celulares,
                c.cantidad_stock AS stock,
                p.nombre,
                p.descripcion,
                pr.precio,
                m.marca,
                r.ram,
                a.almacenamiento,
                (
                    SELECT imagen_url 
                    FROM imagenes_celulares ic
                    WHERE ic.id_celulares = c.id_celulares
                    ORDER BY ic.es_principal DESC, ic.id_imagen ASC
                    LIMIT 1
                ) AS imagen
            FROM celulares c
            LEFT JOIN producto       p  ON c.id_producto       = p.id_producto
            LEFT JOIN precio         pr ON c.id_precio         = pr.id_precio
            LEFT JOIN marcas         m  ON c.id_marcas         = m.id_marcas
            LEFT JOIN ram            r  ON c.id_ram            = r.id_ram
            LEFT JOIN almacenamiento a  ON c.id_almacenamiento = a.id_almacenamiento
            WHERE (p.nombre LIKE ? OR m.marca LIKE ? OR p.descripcion LIKE ?)
              AND c.cantidad_stock > 0
            ORDER BY p.nombre ASC
        ');

        $stmt->bind_param('sss', $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            if (empty($row['imagen'])) {
                $row['imagen'] = 'assets/Imagenes/placeholder.jpg';
            }
            $products[] = $row;
        }
        $stmt->close();

        $cart      = new Cart();
        $cartCount = $cart->getTotalQuantity();

        $this->view('productos', [
            'products'   => $products,
            'cartCount'  => $cartCount,
            'searchTerm' => $searchTerm,
        ]);
    }

    /**
     * Obtener productos por categoría (marca)
     */
    public function category(string $category): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $stmt = $this->db->prepare('
            SELECT 
                c.id_celulares,
                c.cantidad_stock AS stock,
                p.nombre,
                p.descripcion,
                pr.precio,
                m.marca,
                (
                    SELECT imagen_url 
                    FROM imagenes_celulares ic
                    WHERE ic.id_celulares = c.id_celulares
                    ORDER BY ic.es_principal DESC, ic.id_imagen ASC
                    LIMIT 1
                ) AS imagen
            FROM celulares c
            LEFT JOIN producto p ON c.id_producto = p.id_producto
            LEFT JOIN precio  pr ON c.id_precio   = pr.id_precio
            LEFT JOIN marcas  m  ON c.id_marcas   = m.id_marcas
            WHERE m.marca = ?
              AND c.cantidad_stock > 0
            ORDER BY p.nombre ASC
        ');

        $stmt->bind_param('s', $category);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            if (empty($row['imagen'])) {
                $row['imagen'] = 'assets/Imagenes/placeholder.jpg';
            }
            $products[] = $row;
        }
        $stmt->close();

        $cart      = new Cart();
        $cartCount = $cart->getTotalQuantity();

        $this->view('productos', [
            'products'  => $products,
            'cartCount' => $cartCount,
            'category'  => $category,
        ]);
    }

    /**
     * Obtener todas las marcas disponibles
     */
    public function getBrands(): array
    {
        $query = '
            SELECT DISTINCT m.marca 
            FROM marcas m
            INNER JOIN celulares c ON m.id_marcas = c.id_marcas
            WHERE c.cantidad_stock > 0
            ORDER BY m.marca ASC
        ';

        $result = $this->db->query($query);
        $brands = [];

        while ($row = $result->fetch_assoc()) {
            $brands[] = $row['marca'];
        }

        return $brands;
    }

    /**
     * Obtener productos destacados
     */
    public function getFeatured(int $limit = 8): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                c.id_celulares,
                c.cantidad_stock AS stock,
                p.nombre,
                pr.precio,
                m.marca,
                (
                    SELECT imagen_url 
                    FROM imagenes_celulares ic
                    WHERE ic.id_celulares = c.id_celulares
                    ORDER BY ic.es_principal DESC, ic.id_imagen ASC
                    LIMIT 1
                ) AS imagen
            FROM celulares c
            LEFT JOIN producto p ON c.id_producto = p.id_producto
            LEFT JOIN precio  pr ON c.id_precio   = pr.id_precio
            LEFT JOIN marcas  m  ON c.id_marcas   = m.id_marcas
            WHERE c.cantidad_stock > 0
            ORDER BY RAND()
            LIMIT ?
        ');

        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            if (empty($row['imagen'])) {
                $row['imagen'] = 'assets/Imagenes/placeholder.jpg';
            }
            $products[] = $row;
        }
        $stmt->close();

        return $products;
    }

    /**
     * Verificar disponibilidad de stock
     */
    public function checkStock(int $productId): int
    {
        $stmt = $this->db->prepare('SELECT cantidad_stock FROM celulares WHERE id_celulares = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();

        return (int)($row['cantidad_stock'] ?? 0);
    }
}
