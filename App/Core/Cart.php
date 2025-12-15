<?php
namespace App\Core;

class Cart
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Agregar o sumar un producto al carrito
     * Máximo 10 unidades por producto
     */
    public function addItem(int $productId, int $quantity, array $productData = []): array
    {
        if ($quantity < 1) {
            $quantity = 1;
        }

        // Datos que vienen del controlador (name, price, image)
        $name  = $productData['name']  ?? 'Producto';
        $price = (float)($productData['price'] ?? 0);
        $image = $productData['image'] ?? '';
        $maxStock = (int)($productData['stock'] ?? 10);

        $response = ['success' => true, 'message' => 'Producto agregado al carrito', 'limited' => false];

        // Si ya existe en el carrito, solo aumenta cantidad (máximo según stock)
        if (isset($_SESSION['cart'][$productId])) {
            $currentQty = $_SESSION['cart'][$productId]['quantity'];
            $newQty = $currentQty + $quantity;
            
            if ($newQty > $maxStock) {
                $_SESSION['cart'][$productId]['quantity'] = $maxStock;
                $response['message'] = "⚠️ Se alcanzó el límite máximo ($maxStock unidades). No se puede agregar más unidades de este producto.";
                $response['limited'] = true;
                $response['success'] = false;
            } else {
                $_SESSION['cart'][$productId]['quantity'] = $newQty;
                $response['message'] = "Producto agregado correctamente al carrito";
            }
        } else {
            $addQty = min($quantity, $maxStock);
            $_SESSION['cart'][$productId] = [
                'product_id' => $productId,
                'name'       => $name,
                'price'      => $price,
                'image'      => $image,
                'quantity'   => $addQty,
            ];
            
            if ($addQty < $quantity) {
                $response['message'] = "⚠️ Se alcanzó el límite máximo ($maxStock unidades). Solo se agregó cantidad máxima permitida.";
                $response['limited'] = true;
                $response['success'] = false;
            } else {
                $response['message'] = "Producto agregado correctamente al carrito";
            }
        }
        
        return $response;
    }

    /**
     * Actualizar cantidad de un item (máximo 10)
     */
    public function updateItem(int $productId, int $quantity, int $maxStock = 10): bool
    {
        if (!isset($_SESSION['cart'][$productId])) {
            return false;
        }

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
            return true;
        }

        // Limitar a máximo de stock (10)
        if ($quantity > $maxStock) {
            $_SESSION['cart'][$productId]['quantity'] = $maxStock;
            return false; // indica que fue limitado
        }

        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        return true; // exitoso sin limitaciones
    }

    /**
     * Eliminar un item
     */
    public function removeItem(int $productId): void
    {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
    }

    /**
     * Vaciar carrito
     */
    public function clearCart(): void
    {
        $_SESSION['cart'] = [];
    }

    /**
     * Obtener todos los items
     */
    public function getItems(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    /**
     * Cantidad total de productos
     */
    public function getTotalQuantity(): int
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += (int)($item['quantity'] ?? 0);
        }
        return $total;
    }

    /**
     * Precio total
     */
    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $q = (int)($item['quantity'] ?? 0);
            $p = (float)($item['price'] ?? 0);
            $total += $p * $q;
        }
        return $total;
    }
}
