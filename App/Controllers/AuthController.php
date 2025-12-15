<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $csrf = Security::csrfToken();
        $this->view('auth/login', ['csrf' => $csrf]);
    }

    public function showRegister(): void
    {
        $csrf = Security::csrfToken();
        $this->view('auth/register', ['csrf' => $csrf]);
    }

    public function login(): void
    {
        Security::enforceCsrfPost();
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            \session_start();
        }

        $correo = \trim($_POST['correo'] ?? '');
        $pass   = $_POST['password'] ?? '';

        if ($correo === '' || $pass === '') {
            $_SESSION['error'] = 'Completa tus credenciales.';
            $this->redirect('/login');
            return;
        }

        $u   = new User();
        $row = $u->findByEmail($correo);

        // Logs de depuración (puedes quitarlos luego)
        error_log('LOGIN intento correo=' . $correo);
        if ($row) {
            error_log('LOGIN usuario encontrado id=' . $row['id_usuario']);
            error_log('LOGIN hash en BD=' . $row['contraseña']);
        } else {
            error_log('LOGIN usuario NO encontrado');
        }

        if ($row && \password_verify($pass, $row['contraseña'] ?? '')) {
            \session_regenerate_id(true);
            $userId = (int)($row['id_usuario'] ?? 0);

            // Obtener datos del rol (fila) para tener id_usuario_rol
            $role = $u->getUserRole($userId);
            $roleRow = $u->getUserRoleRow($userId);

            // Normalizamos estructura de sesión que usan otras partes (checkout)
            $_SESSION['user_id'] = $userId;
            $_SESSION['user'] = [
                'id_usuario' => $userId,
                'id_usuario_rol' => isset($roleRow['id_usuario_rol']) ? (int)$roleRow['id_usuario_rol'] : null,
                'name' => $row['nombre'],
                'email' => $row['correo'],
                'phone' => $row['telefono'] ?? ''
            ];
            $_SESSION['user_role']  = $role;  // será 'admin' o 'user'
            $_SESSION['user_name']  = $row['nombre'];
            $_SESSION['success']    = '¡Bienvenido de nuevo, ' . htmlspecialchars($row['nombre']) . '!';

            if ($role === 'admin') {
                $this->redirect('/admin/productos');
            } else {
                // Redirigir a Public/index.php
                header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/Public/', true, 302);
                exit;
            }
            return;
        }

        $_SESSION['error'] = 'Correo o contraseña incorrectos.';
        $this->redirect('/login');
    }

    public function register(): void
    {
        Security::enforceCsrfPost();
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            \session_start();
        }

        $name   = \trim($_POST['name'] ?? '');
        $correo = \trim($_POST['correo'] ?? '');
        $tel    = \trim($_POST['telefono'] ?? '');
        $pass   = $_POST['password'] ?? '';
        $pass2  = $_POST['password_confirm'] ?? '';

        $u    = new User();
        $data = [
            'nombre'           => $name,
            'correo'           => $correo,
            'telefono'         => $tel,
            'password'         => $pass,
            'password_confirm' => $pass2,
        ];

        $errors = $u->validate($data, true);
        if ($errors) {
            $_SESSION['error'] = implode(' ', $errors);
            $this->redirect('/register');
            return;
        }

        if ($u->emailExists($correo)) {
            $_SESSION['error'] = 'Este correo ya está registrado.';
            $this->redirect('/register');
            return;
        }

        $ok = $u->create($name, $correo, $pass, $tel);
        if ($ok) {
            $row = $u->findByEmail($correo);
            if ($row) {
                \session_regenerate_id(true);
                $userId = (int)$row['id_usuario'];
                $roleRow = $u->getUserRoleRow($userId);

                $_SESSION['user_id'] = $userId;
                $_SESSION['user'] = [
                    'id_usuario' => $userId,
                    'id_usuario_rol' => isset($roleRow['id_usuario_rol']) ? (int)$roleRow['id_usuario_rol'] : null,
                    'name' => $row['nombre'],
                    'email' => $row['correo'],
                    'phone' => $row['telefono'] ?? ''
                ];
                $_SESSION['user_role']  = 'user';
                $_SESSION['user_name']  = $row['nombre'];
                $_SESSION['success']    = '¡Cuenta creada exitosamente! Bienvenido a MoviCell.';
                $this->redirect('/panel');
                return;
            }
        }

        $_SESSION['error'] = 'No fue posible registrar. Intenta nuevamente.';
        $this->redirect('/register');
    }

    public function panel(): void
    {
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            \session_start();
        }
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $u    = new User();
        $user = $u->findById((int)$_SESSION['user_id']);

        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            $this->logout();
            return;
        }

        $orders = [];
        try {
            if (file_exists(__DIR__ . '/../Models/Order.php')) {
                require_once __DIR__ . '/../Models/Order.php';
                $orderModel = new \App\Models\Order();
                $orders     = $orderModel->findByUserId((int)$_SESSION['user_id']);
            }
        } catch (\Exception $e) {
            error_log('Error al cargar pedidos: ' . $e->getMessage());
            $orders = [];
        }

        $this->view('auth/panel', [
            'user'      => $user,
            'user_name' => $user['nombre'] ?? 'Usuario',
            'orders'    => $orders,
        ]);
    }

    public function updateProfile(): void
    {
        Security::enforceCsrfPost();
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            \session_start();
        }
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $u    = new User();
        $data = [
            'nombre'   => trim($_POST['name'] ?? ''),
            'correo'   => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
        ];

        $errors = $u->validate($data, false);
        if ($errors) {
            $_SESSION['error'] = implode(' ', $errors);
            $this->redirect('/panel');
            return;
        }

        $currentUser = $u->findById((int)$_SESSION['user_id']);
        if ($data['correo'] !== $currentUser['correo'] && $u->emailExists($data['correo'])) {
            $_SESSION['error'] = 'El email ya está registrado por otro usuario.';
            $this->redirect('/panel');
            return;
        }

        if ($u->updateProfile((int)$_SESSION['user_id'], $data)) {
            $_SESSION['user_name']  = $data['nombre'];
            $_SESSION['user_email'] = $data['correo'];
            $_SESSION['user_phone'] = $data['telefono'];
            $_SESSION['success']    = '✅ Perfil actualizado correctamente.';
        } else {
            $_SESSION['error'] = '❌ No fue posible actualizar el perfil.';
        }
        $this->redirect('/panel');
    }

    public function updatePassword(): void
    {
        Security::enforceCsrfPost();
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            \session_start();
        }
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password_confirm'] ?? '';

        if ($pass === '') {
            $_SESSION['error'] = 'La contraseña no puede estar vacía.';
            $this->redirect('/panel');
            return;
        }

        if ($pass !== $pass2) {
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            $this->redirect('/panel');
            return;
        }

        if (mb_strlen($pass) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            $this->redirect('/panel');
            return;
        }

        $u = new User();
        if ($u->updatePassword((int)$_SESSION['user_id'], $pass)) {
            $_SESSION['success'] = '✅ Contraseña actualizada correctamente.';
        } else {
            $_SESSION['error'] = '❌ No fue posible actualizar la contraseña.';
        }
        $this->redirect('/panel');
    }

    public function deleteAccount(): void
    {
        Security::enforceCsrfPost();
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            \session_start();
        }
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $u       = new User();
        $deleted = $u->hardDelete((int)$_SESSION['user_id']);

        if ($deleted) {
            $_SESSION = [];
            \session_destroy();
            \session_start();
            $_SESSION['success'] = 'Cuenta eliminada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al eliminar la cuenta.';
        }

        $this->redirect('/');
    }

    public function logout(): void
    {
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            \session_start();
        }
        $_SESSION = [];

        if (\ini_get('session.use_cookies')) {
            $params = \session_get_cookie_params();
            \setcookie(
                \session_name(),
                '',
                \time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        \session_destroy();
        \session_start();
        $_SESSION['success'] = 'Sesión cerrada correctamente.';
        $this->redirect('/login');
    }

    public function viewOrder(): void
    {
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            \session_start();
        }
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $orderId = (int)($_GET['id'] ?? 0);

        if ($orderId < 1) {
            $_SESSION['error'] = 'ID de pedido inválido.';
            $this->redirect('/panel');
            return;
        }

        try {
            require_once __DIR__ . '/../Models/Order.php';
            $orderModel = new \App\Models\Order();
            $order      = $orderModel->findById($orderId);

            if (!$order) {
                $_SESSION['error'] = 'Pedido no encontrado.';
                $this->redirect('/panel');
                return;
            }

            // Verificar que el pedido pertenece al usuario logueado
            $db   = \App\Core\Db::conn();
            $stmt = $db->prepare(
                'SELECT 1 
                 FROM roles_usuario 
                 WHERE id_usuario_rol = ? AND id_usuario = ? 
                 LIMIT 1'
            );
            $stmt->bind_param('ii', $order['id_usuario_rol'], $_SESSION['user_id']);
            $stmt->execute();
            $res     = $stmt->get_result();
            $allowed = (bool)$res->fetch_row();
            $stmt->close();

            if (!$allowed) {
                $_SESSION['error'] = 'Pedido no encontrado.';
                $this->redirect('/panel');
                return;
            }

            $this->view('auth/order-detail', [
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cargar el pedido: ' . $e->getMessage();
            $this->redirect('/panel');
        }
    }

    public function viewOrders(): void
    {
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $this->viewOrder();
            return;
        }
        $this->redirect('/panel');
    }

    /**
     * Mostrar formulario de recuperación de contraseña
     */
    public function showForgotPassword(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $csrf = Security::csrfToken();
        $this->view('auth/forgot-password', ['csrf' => $csrf]);
    }

    /**
     * Procesar solicitud de recuperación de contraseña
     */
    public function forgotPassword(): void
    {
        Security::enforceCsrfPost();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $correo = trim($_POST['correo'] ?? '');

        if ($correo === '') {
            $_SESSION['error'] = 'Por favor ingresa tu correo electrónico.';
            $this->redirect('/forgot-password');
            return;
        }

        $u = new User();
        $user = $u->findByEmail($correo);

        if (!$user) {
            // Por seguridad, no revelar si el correo existe o no
            $_SESSION['success'] = 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación.';
            $this->redirect('/login');
            return;
        }

        // Generar token de recuperación
        $token = $u->createPasswordResetToken($user['id_usuario']);

        if (!$token) {
            $_SESSION['error'] = 'Error al generar el token de recuperación.';
            $this->redirect('/forgot-password');
            return;
        }

        // Obtener base path sin /public
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if (basename($basePath) === 'public') {
            $basePath = dirname($basePath);
        }
        $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . $basePath . '/Public/index.php?r=/reset-password&token=' . urlencode($token);

        // Preparar correo HTML
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: 'Poppins', sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
                .header { background: linear-gradient(135deg, #0369a1, #06b6d4); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                .content { background: white; padding: 30px; }
                .button { display: inline-block; background: #0369a1; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .warning { background: #fef3cd; border: 1px solid #ffc107; color: #856404; padding: 12px; border-radius: 4px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>🔐 Recuperar Contraseña</h2>
                </div>
                <div class="content">
                    <p>Hola <strong>{$user['nombre']}</strong>,</p>
                    <p>Recibimos una solicitud para recuperar tu contraseña en MoviCell. Si no solicitaste esto, ignora este correo.</p>
                    
                    <p><strong>Para cambiar tu contraseña, haz clic en el botón de abajo:</strong></p>
                    
                    <center>
                        <a href="{$resetLink}" class="button">Cambiar Contraseña</a>
                    </center>
                    
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p style="word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 12px;">
                        {$resetLink}
                    </p>
                    
                    <div class="warning">
                        ⏰ Este enlace expira en <strong>24 horas</strong>. Actúa rápido.
                    </div>
                    
                    <p>Si tienes problemas, contáctanos en nuestro sitio web.</p>
                </div>
                <div class="footer">
                    <p>&copy; 2025 MoviCell. Todos los derechos reservados.</p>
                    <p>Este es un correo automático, por favor no responder a este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;

        // Enviar correo
        \App\Core\Mailer::sendHtml($correo, 'Recuperar tu contraseña - MoviCell', $html);

        $_SESSION['success'] = 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación.';
        $this->redirect('/login');
    }

    /**
     * Mostrar formulario de cambio de contraseña
     */
    public function showResetPassword(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $token = $_GET['token'] ?? '';

        if ($token === '') {
            $_SESSION['error'] = 'Token de recuperación inválido.';
            $this->redirect('/login');
            return;
        }

        $u = new User();
        $resetData = $u->validatePasswordResetToken($token);

        if (!$resetData) {
            $_SESSION['error'] = 'El enlace de recuperación ha expirado o es inválido.';
            $this->redirect('/login');
            return;
        }

        $csrf = Security::csrfToken();
        $this->view('auth/reset-password', [
            'csrf' => $csrf,
            'token' => $token,
            'id_usuario' => $resetData['id_usuario']
        ]);
    }

    /**
     * Procesar cambio de contraseña
     */
    public function resetPassword(): void
    {
        Security::enforceCsrfPost();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($token === '') {
            $_SESSION['error'] = 'Token inválido.';
            $this->redirect('/login');
            return;
        }

        if ($password === '' || $passwordConfirm === '') {
            $_SESSION['error'] = 'Por favor completa todos los campos.';
            $this->redirect('/reset-password&token=' . urlencode($token));
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            $this->redirect('/reset-password&token=' . urlencode($token));
            return;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            $this->redirect('/reset-password&token=' . urlencode($token));
            return;
        }

        $u = new User();
        $resetData = $u->validatePasswordResetToken($token);

        if (!$resetData) {
            $_SESSION['error'] = 'El enlace de recuperación ha expirado o es inválido.';
            $this->redirect('/login');
            return;
        }

        // Cambiar contraseña
        if ($u->updatePassword($resetData['id_usuario'], $password)) {
            // Marcar token como utilizado
            $u->markPasswordResetAsUsed($resetData['id_reset']);

            $_SESSION['success'] = '¡Contraseña actualizada exitosamente! Inicia sesión con tu nueva contraseña.';
            $this->redirect('/login');
            return;
        }

        $_SESSION['error'] = 'Error al actualizar la contraseña. Intenta nuevamente.';
        $this->redirect('/reset-password&token=' . urlencode($token));
    }
}
