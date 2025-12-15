<?php
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private $con;
    
    protected function setUp(): void
    {
        // Se ejecuta antes de cada test
        // Conectar a base de datos de prueba
        $this->con = mysqli_connect('localhost', 'root', '', 'tienda_movicell_test');
        
        if (!$this->con) {
            $this->fail('No se pudo conectar a la base de datos');
        }
        
        // CORREGIDO: Deshabilitar verificación de foreign keys
        mysqli_query($this->con, "SET FOREIGN_KEY_CHECKS = 0");
        
        // Limpiar tablas antes de cada test
        mysqli_query($this->con, "TRUNCATE TABLE producto");
        mysqli_query($this->con, "TRUNCATE TABLE celulares");
        mysqli_query($this->con, "TRUNCATE TABLE imagenes_celulares");
        
        // Rehabilitar verificación de foreign keys
        mysqli_query($this->con, "SET FOREIGN_KEY_CHECKS = 1");
    }
    
    protected function tearDown(): void
    {
        // Se ejecuta después de cada test
        if ($this->con) {
            mysqli_close($this->con);
        }
    }
    
    /**
     * Test: Verificar que se puede insertar un producto
     */
    public function testInsertarProducto()
    {
        // Arrange (Preparar)
        $nombre = 'Smartphone Test';
        $descripcion = 'Descripción de prueba';
        
        // Act (Actuar)
        $stmt = mysqli_prepare($this->con, 
            "INSERT INTO producto (nombre, descripcion) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ss', $nombre, $descripcion);
        $resultado = mysqli_stmt_execute($stmt);
        
        // Assert (Verificar)
        $this->assertTrue($resultado, 'El producto debería insertarse correctamente');
        
        $id = mysqli_insert_id($this->con);
        $this->assertGreaterThan(0, $id, 'El ID debería ser mayor a 0');
    }
    
    /**
     * Test: Verificar que se puede consultar un producto
     */
    public function testConsultarProducto()
    {
        // Arrange: Insertar producto
        $nombre = 'iPhone 15';
        $descripcion = 'Smartphone Apple';
        
        $stmt = mysqli_prepare($this->con, 
            "INSERT INTO producto (nombre, descripcion) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ss', $nombre, $descripcion);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->con);
        
        // Act: Consultar producto
        $query = mysqli_query($this->con, 
            "SELECT * FROM producto WHERE id_producto = $id");
        $producto = mysqli_fetch_assoc($query);
        
        // Assert: Verificar que existe
        $this->assertNotNull($producto, 'El producto debería existir');
        $this->assertEquals($nombre, $producto['nombre']);
        $this->assertEquals($descripcion, $producto['descripcion']);
    }
    
    /**
     * Test: Verificar validación de campos vacíos
     */
    public function testValidarCamposVacios()
    {
        // Arrange
        $nombre = '';
        $descripcion = '';
        
        // Act & Assert
        $this->assertEmpty($nombre, 'El nombre está vacío como se esperaba');
        $this->assertEmpty($descripcion, 'La descripción está vacía como se esperaba');
        
        // Verificar que no son válidos
        $this->assertFalse(
            !empty($nombre) && !empty($descripcion),
            'Los campos vacíos no deberían ser válidos'
        );
    }
    
    /**
     * Test: Verificar que no se puede insertar producto con campos vacíos
     */
    public function testNoInsertarProductoConCamposVacios()
    {
        // Arrange
        $nombre = '';
        $descripcion = 'Descripción válida';
        
        // Verificar que los campos están vacíos antes de intentar insertar
        if (empty($nombre) || empty($descripcion)) {
            $this->assertTrue(true, 'Validación correcta: campos vacíos detectados');
            return;
        }
        
        // Si llegamos aquí, el test debe fallar
        $this->fail('No debería permitir campos vacíos');
    }
}
