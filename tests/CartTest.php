<?php
use PHPUnit\Framework\TestCase;

// Incluir la clase Cart
require_once __DIR__ . '/../App/Core/Cart.php';

class CartTest extends TestCase
{
    private $cart;
    
    protected function setUp(): void
    {
        // Iniciar sesión para el carrito
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        // Limpiar carrito antes de cada test
        $_SESSION['cart_items'] = [];
        
        $this->cart = new App\Core\Cart();
    }
    
    protected function tearDown(): void
    {
        // Limpiar después del test
        if (isset($_SESSION['cart_items'])) {
            unset($_SESSION['cart_items']);
        }
    }
    
    /**
     * Test: Verificar que el carrito inicia vacío
     */
    public function testCarritoIniciaVacio()
    {
        $this->assertEquals(0, $this->cart->getTotalQuantity(), 
            'El carrito debería iniciar vacío');
        $this->assertEquals(0, $this->cart->getTotal(), 
            'El total debería ser 0');
        $this->assertEmpty($this->cart->getItems(), 
            'No debería haber items');
        $this->assertFalse($this->cart->hasItems(), 
            'hasItems() debería retornar false');
    }
    
    /**
     * Test: Agregar un producto al carrito
     */
    public function testAgregarProductoAlCarrito()
    {
        // Arrange
        $productData = [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'imagen.jpg'
        ];
        
        // Act
        $this->cart->addItem(1, 1, $productData);
        
        // Assert
        $items = $this->cart->getItems();
        $this->assertCount(1, $items, 'Debería haber 1 tipo de producto');
        $this->assertTrue($this->cart->hasItems(), 'El carrito debería tener items');
        $this->assertEquals(1, $this->cart->getItemCount(), 'Debería haber 1 item único');
        
        // Verificar el producto agregado
        $this->assertArrayHasKey(1, $items, 'Debería existir el producto con ID 1');
        $this->assertEquals('iPhone 15', $items[1]['name']);
        $this->assertEquals(1000000, $items[1]['price']);
        $this->assertEquals(1, $items[1]['quantity']);
    }
    
    /**
     * Test: Agregar múltiples unidades del mismo producto
     */
    public function testAgregarMultiplesUnidadesMismoProducto()
    {
        // Arrange
        $productData = [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'imagen.jpg'
        ];
        
        // Act - Agregar 2 veces el mismo producto
        $this->cart->addItem(1, 1, $productData);
        $this->cart->addItem(1, 2, $productData); // Agregar 2 más
        
        // Assert
        $items = $this->cart->getItems();
        $this->assertEquals(3, $items[1]['quantity'], 
            'Debería tener 3 unidades del mismo producto');
        $this->assertEquals(3, $this->cart->getTotalQuantity());
    }
    
    /**
     * Test: Agregar múltiples productos diferentes
     */
    public function testAgregarMultiplesProductos()
    {
        // Arrange & Act
        $this->cart->addItem(1, 1, [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'img1.jpg'
        ]);
        
        $this->cart->addItem(2, 2, [
            'name' => 'Samsung S24',
            'price' => 800000,
            'image' => 'img2.jpg'
        ]);
        
        $this->cart->addItem(3, 1, [
            'name' => 'Xiaomi 14',
            'price' => 600000,
            'image' => 'img3.jpg'
        ]);
        
        // Assert
        $this->assertEquals(3, $this->cart->getItemCount(), 
            'Debería haber 3 productos diferentes');
        $this->assertEquals(4, $this->cart->getTotalQuantity(), 
            'Cantidad total debería ser 4 (1+2+1)');
    }
    
    /**
     * Test: Calcular total del carrito correctamente
     */
    public function testCalcularTotalCarrito()
    {
        // Arrange & Act
        $this->cart->addItem(1, 2, [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'img1.jpg'
        ]);
        
        $this->cart->addItem(2, 1, [
            'name' => 'Samsung S24',
            'price' => 800000,
            'image' => 'img2.jpg'
        ]);
        
        // Assert
        // (1,000,000 * 2) + (800,000 * 1) = 2,800,000
        $total = $this->cart->getTotal();
        $this->assertEquals(2800000, $total, 
            'El total debería ser 2,800,000');
        
        $totalPrice = $this->cart->getTotalPrice();
        $this->assertEquals(2800000, $totalPrice, 
            'getTotalPrice() debería retornar 2,800,000');
    }
    
    /**
     * Test: Actualizar cantidad de producto
     */
    public function testActualizarCantidadProducto()
    {
        // Arrange
        $this->cart->addItem(1, 1, [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'imagen.jpg'
        ]);
        
        // Act
        $this->cart->updateItem(1, 5);
        
        // Assert
        $items = $this->cart->getItems();
        $this->assertEquals(5, $items[1]['quantity'], 
            'La cantidad debería ser 5');
        $this->assertEquals(5000000, $this->cart->getTotal(), 
            'El total debería ser 5,000,000');
    }
    
    /**
     * Test: Actualizar cantidad a 0 elimina el producto
     */
    public function testActualizarCantidadACeroEliminaProducto()
    {
        // Arrange
        $this->cart->addItem(1, 3, [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'imagen.jpg'
        ]);
        
        // Act
        $this->cart->updateItem(1, 0);
        
        // Assert
        $this->assertEmpty($this->cart->getItems(), 
            'El carrito debería estar vacío');
        $this->assertEquals(0, $this->cart->getTotalQuantity());
    }
    
    /**
     * Test: Eliminar producto del carrito
     */
    public function testEliminarProductoDelCarrito()
    {
        // Arrange
        $this->cart->addItem(1, 1, [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'imagen.jpg'
        ]);
        
        $this->cart->addItem(2, 1, [
            'name' => 'Samsung S24',
            'price' => 800000,
            'imagen' => 'imagen2.jpg'
        ]);
        
        // Act
        $this->cart->removeItem(1); // Eliminar iPhone
        
        // Assert
        $items = $this->cart->getItems();
        $this->assertEquals(1, $this->cart->getItemCount(), 
            'Debería quedar 1 producto');
        $this->assertArrayNotHasKey(1, $items, 
            'No debería existir el producto ID 1');
        $this->assertArrayHasKey(2, $items, 
            'Debería existir el producto ID 2');
    }
    
    /**
     * Test: Vaciar el carrito completamente
     */
    public function testVaciarCarrito()
    {
        // Arrange
        $this->cart->addItem(1, 1, [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'imagen.jpg'
        ]);
        
        $this->cart->addItem(2, 1, [
            'name' => 'Samsung S24',
            'price' => 800000,
            'image' => 'imagen.jpg'
        ]);
        
        // Act
        $this->cart->clear();
        
        // Assert
        $this->assertEmpty($this->cart->getItems(), 
            'El carrito debería estar vacío');
        $this->assertEquals(0, $this->cart->getTotalQuantity());
        $this->assertEquals(0, $this->cart->getTotal());
        $this->assertFalse($this->cart->hasItems());
    }
    
    /**
     * Test: clearCart() también funciona (alias)
     */
    public function testClearCartAlias()
    {
        // Arrange
        $this->cart->addItem(1, 2, [
            'name' => 'iPhone 15',
            'price' => 1000000,
            'image' => 'imagen.jpg'
        ]);
        
        // Act
        $this->cart->clearCart();
        
        // Assert
        $this->assertEquals(0, $this->cart->getItemCount());
    }
}
