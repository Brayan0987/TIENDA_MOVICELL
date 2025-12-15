<?php
// Configuración global de la aplicación
define('APP_NAME', 'Movil Cell');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'movi_cell');

// Configuración de rutas
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de correo (SMTP)
// Si quieres usar SMTP real, cambia USE_SMTP a true y configura las credenciales.
define('MAIL_USE_SMTP', true);
// Usando SMTP Gmail según credenciales proporcionadas (ten en cuenta las notas sobre App Passwords)
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_USER', 'movilcell743@gmail.com');
define('MAIL_SMTP_PASS', 'wmbh nzqi nwza cwpi');
define('MAIL_SMTP_SECURE', 'tls'); // 'tls' o 'ssl' o ''
// Si se está usando SMTP, por defecto usar la cuenta SMTP como remitente
if (defined('MAIL_USE_SMTP') && MAIL_USE_SMTP && !empty(MAIL_SMTP_USER)) {
    define('MAIL_FROM_ADDRESS', MAIL_SMTP_USER);
} else {
    define('MAIL_FROM_ADDRESS', 'no-reply@localhost');
}
define('MAIL_FROM_NAME', 'Movil Cell');


// Iniciar sesión en todas las páginas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funciones auxiliares globales
function redirect($path) {
    header("Location: $path");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['id_usuario']);
}

function getUserId() {
    return $_SESSION['id_usuario'] ?? null;
}

// Asegurar que exista el directorio de uploads
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
?>