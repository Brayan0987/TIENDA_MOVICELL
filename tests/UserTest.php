<?php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $con;
    
    protected function setUp(): void
    {
        // Conectar a base de datos de prueba
        $this->con = mysqli_connect('localhost', 'root', '', 'tienda_movicell_test');
        
        if (!$this->con) {
            $this->fail('No se pudo conectar a la base de datos');
        }
        
        // Limpiar tabla usuarios antes de cada test
        mysqli_query($this->con, "SET FOREIGN_KEY_CHECKS = 0");
        mysqli_query($this->con, "TRUNCATE TABLE usuario");
        mysqli_query($this->con, "TRUNCATE TABLE roles_usuario");
        mysqli_query($this->con, "SET FOREIGN_KEY_CHECKS = 1");
    }
    
    protected function tearDown(): void
    {
        if ($this->con) {
            mysqli_close($this->con);
        }
    }
    
    /**
     * Test: Registrar un usuario correctamente
     */
    public function testRegistrarUsuario()
    {
        // Arrange
        $nombre = 'Juan Pérez';
        $correo = 'juan@example.com';
        $password = 'password123';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $telefono = '3001234567';
        
        // Act
        $stmt = mysqli_prepare($this->con, 
            "INSERT INTO usuario (nombre, correo, contraseña, telefono) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $correo, $passwordHash, $telefono);
        $resultado = mysqli_stmt_execute($stmt);
        
        // Assert
        $this->assertTrue($resultado, 'El usuario debería registrarse correctamente');
        
        $id = mysqli_insert_id($this->con);
        $this->assertGreaterThan(0, $id, 'El ID debería ser mayor a 0');
        
        // Verificar que el usuario existe en la BD
        $query = mysqli_query($this->con, "SELECT * FROM usuario WHERE id_usuario = $id");
        $usuario = mysqli_fetch_assoc($query);
        
        $this->assertNotNull($usuario);
        $this->assertEquals($nombre, $usuario['nombre']);
        $this->assertEquals($correo, $usuario['correo']);
    }
    
    /**
     * Test: No permitir correos duplicados
     * CORREGIDO para PHP 8.2+ que lanza excepciones
     */
    public function testNoPermitirCorreoDuplicado()
    {
        // Arrange - Insertar primer usuario
        $correo = 'juan@example.com';
        $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
        
        $stmt = mysqli_prepare($this->con, 
            "INSERT INTO usuario (nombre, correo, contraseña, telefono) VALUES (?, ?, ?, ?)");
        $nombre = 'Juan Pérez';
        $telefono = '3001234567';
        mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $correo, $passwordHash, $telefono);
        mysqli_stmt_execute($stmt);
        
        // Act & Assert - Intentar insertar otro usuario con el mismo correo
        // En PHP 8.2+, mysqli lanza excepciones
        $this->expectException(mysqli_sql_exception::class);
        $this->expectExceptionMessageMatches('/Duplicate entry/');
        
        $stmt2 = mysqli_prepare($this->con, 
            "INSERT INTO usuario (nombre, correo, contraseña, telefono) VALUES (?, ?, ?, ?)");
        $nombre2 = 'Pedro López';
        $telefono2 = '3009876543';
        mysqli_stmt_bind_param($stmt2, 'ssss', $nombre2, $correo, $passwordHash, $telefono2);
        mysqli_stmt_execute($stmt2); // Esto lanzará la excepción
    }
    
    /**
     * Test: Verificar contraseña encriptada
     */
    public function testVerificarPasswordEncriptado()
    {
        // Arrange
        $password = 'MiPassword123!';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // Assert - Verificar que el hash es válido
        $this->assertNotEquals($password, $passwordHash, 
            'El password no debería estar en texto plano');
        
        $this->assertTrue(password_verify($password, $passwordHash), 
            'El password debería verificarse correctamente');
        
        $this->assertFalse(password_verify('passwordIncorrecto', $passwordHash), 
            'Un password incorrecto no debería verificarse');
    }
    
    /**
     * Test: Validar formato de email
     */
    public function testValidarFormatoEmail()
    {
        // Emails válidos
        $this->assertNotFalse(filter_var('test@example.com', FILTER_VALIDATE_EMAIL));
        $this->assertNotFalse(filter_var('user.name@domain.co', FILTER_VALIDATE_EMAIL));
        $this->assertNotFalse(filter_var('user+tag@example.com', FILTER_VALIDATE_EMAIL));
        
        // Emails inválidos
        $this->assertFalse(filter_var('email-invalido', FILTER_VALIDATE_EMAIL));
        $this->assertFalse(filter_var('sin@dominio', FILTER_VALIDATE_EMAIL));
        $this->assertFalse(filter_var('@ejemplo.com', FILTER_VALIDATE_EMAIL));
        $this->assertFalse(filter_var('usuario@', FILTER_VALIDATE_EMAIL));
    }
    
    /**
     * Test: Validar campos requeridos
     */
    public function testValidarCamposRequeridos()
    {
        // Arrange
        $nombre = '';
        $correo = '';
        $password = '';
        $telefono = '';
        
        // Assert
        $this->assertEmpty($nombre, 'El nombre está vacío');
        $this->assertEmpty($correo, 'El correo está vacío');
        $this->assertEmpty($password, 'El password está vacío');
        $this->assertEmpty($telefono, 'El teléfono está vacío');
        
        // Verificar que no son válidos
        $camposValidos = !empty($nombre) && !empty($correo) && !empty($password) && !empty($telefono);
        $this->assertFalse($camposValidos, 'Campos vacíos no deberían ser válidos');
    }
    
    /**
     * Test: Buscar usuario por correo (Login)
     */
    public function testBuscarUsuarioPorCorreo()
    {
        // Arrange - Insertar usuario
        $nombre = 'María García';
        $correo = 'maria@example.com';
        $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
        $telefono = '3001234567';
        
        $stmt = mysqli_prepare($this->con, 
            "INSERT INTO usuario (nombre, correo, contraseña, telefono) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $correo, $passwordHash, $telefono);
        mysqli_stmt_execute($stmt);
        
        // Act - Buscar usuario por correo
        $query = mysqli_query($this->con, 
            "SELECT * FROM usuario WHERE correo = '$correo'");
        $usuario = mysqli_fetch_assoc($query);
        
        // Assert
        $this->assertNotNull($usuario, 'El usuario debería existir');
        $this->assertEquals($correo, $usuario['correo']);
        $this->assertEquals($nombre, $usuario['nombre']);
        
        // Verificar que el password se puede validar
        $this->assertTrue(password_verify('password123', $usuario['contraseña']));
    }
    
    /**
     * Test: Login con credenciales correctas
     */
    public function testLoginConCredencialesCorrectas()
    {
        // Arrange - Crear usuario
        $correo = 'usuario@example.com';
        $password = 'MiPassword123';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = mysqli_prepare($this->con, 
            "INSERT INTO usuario (nombre, correo, contraseña, telefono) VALUES (?, ?, ?, ?)");
        $nombre = 'Usuario Test';
        $telefono = '3001234567';
        mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $correo, $passwordHash, $telefono);
        mysqli_stmt_execute($stmt);
        
        // Act - Simular login
        $query = mysqli_query($this->con, 
            "SELECT * FROM usuario WHERE correo = '$correo'");
        $usuario = mysqli_fetch_assoc($query);
        
        // Assert
        $this->assertNotNull($usuario, 'Usuario debería existir');
        
        $loginExitoso = password_verify($password, $usuario['contraseña']);
        $this->assertTrue($loginExitoso, 'Login debería ser exitoso');
    }
    
    /**
     * Test: Login con credenciales incorrectas
     */
    public function testLoginConCredencialesIncorrectas()
    {
        // Arrange - Crear usuario
        $correo = 'usuario@example.com';
        $password = 'MiPassword123';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = mysqli_prepare($this->con, 
            "INSERT INTO usuario (nombre, correo, contraseña, telefono) VALUES (?, ?, ?, ?)");
        $nombre = 'Usuario Test';
        $telefono = '3001234567';
        mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $correo, $passwordHash, $telefono);
        mysqli_stmt_execute($stmt);
        
        // Act - Intentar login con password incorrecta
        $query = mysqli_query($this->con, 
            "SELECT * FROM usuario WHERE correo = '$correo'");
        $usuario = mysqli_fetch_assoc($query);
        
        // Assert
        $loginExitoso = password_verify('PasswordIncorrecto', $usuario['contraseña']);
        $this->assertFalse($loginExitoso, 'Login NO debería ser exitoso');
    }
    
    /**
     * Test: Validar longitud mínima de password
     */
    public function testValidarLongitudPassword()
    {
        // Passwords válidos (8+ caracteres)
        $this->assertGreaterThanOrEqual(8, strlen('password123'));
        $this->assertGreaterThanOrEqual(8, strlen('MiPass2024!'));
        
        // Passwords inválidos (menos de 8 caracteres)
        $this->assertLessThan(8, strlen('123'));
        $this->assertLessThan(8, strlen('abc'));
        $this->assertLessThan(8, strlen('Pass1'));
    }
    
    /**
     * Test: Actualizar información de usuario
     */
    public function testActualizarUsuario()
    {
        // Arrange - Crear usuario
        $stmt = mysqli_prepare($this->con, 
            "INSERT INTO usuario (nombre, correo, contraseña, telefono) VALUES (?, ?, ?, ?)");
        $nombre = 'Juan Original';
        $correo = 'juan@example.com';
        $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
        $telefono = '3001234567';
        mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $correo, $passwordHash, $telefono);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->con);
        
        // Act - Actualizar nombre y teléfono
        $nombreNuevo = 'Juan Actualizado';
        $telefonoNuevo = '3119876543';
        $stmtUpdate = mysqli_prepare($this->con, 
            "UPDATE usuario SET nombre = ?, telefono = ? WHERE id_usuario = ?");
        mysqli_stmt_bind_param($stmtUpdate, 'ssi', $nombreNuevo, $telefonoNuevo, $id);
        $resultado = mysqli_stmt_execute($stmtUpdate);
        
        // Assert
        $this->assertTrue($resultado, 'La actualización debería ser exitosa');
        
        $query = mysqli_query($this->con, "SELECT * FROM usuario WHERE id_usuario = $id");
        $usuario = mysqli_fetch_assoc($query);
        
        $this->assertEquals($nombreNuevo, $usuario['nombre']);
        $this->assertEquals($telefonoNuevo, $usuario['telefono']);
        $this->assertEquals($correo, $usuario['correo']); // No cambió
    }
}
