<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Clase base abstracta para todos los controladores
 * 
 * Proporciona métodos comunes como view() y redirect()
 * que serán heredados por todos los controladores de la aplicación
 */
abstract class Controller
{
    /**
     * Cargar una vista con datos opcionales
     * 
     * @param string $name Nombre de la vista (sin extensión .php)
     * @param array $data Datos a pasar a la vista como variables
     * @return void
     * @throws RuntimeException Si la vista no existe
     */
    protected function view(string $name, array $data = []): void
    {
        // Extraer datos como variables individuales
        // EXTR_SKIP: No sobrescribir variables existentes (seguridad)
        extract($data, EXTR_SKIP);
        
        // Construir ruta completa de la vista
        // Soporta notación con puntos: 'producto-detalle' o 'auth.login'
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', DIRECTORY_SEPARATOR, $name) . '.php';
        
        // Verificar que la vista existe antes de cargarla
        if (!file_exists($viewPath)) {
            throw new RuntimeException(
                "La vista '{$name}' no fue encontrada. Ruta buscada: {$viewPath}"
            );
        }
        
        // Cargar la vista (las variables de $data están disponibles)
        require $viewPath;
    }
    
    /**
     * Redirigir a otra ruta
     * 
     * @param string $path Ruta de destino (ejemplo: '/productos' o '/login')
     * @param int $code Código HTTP de redirección (302 por defecto)
     * @return void
     */
    protected function redirect(string $path, int $code = 302): void
    {
        // Obtener la base del proyecto
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        
        // Si la base termina en /public, removerlo
        if (basename($base) === 'public') {
            $base = dirname($base);
        }
        
        // Construir URL completa
        if ($path === '/') {
            $url = $base . '/';
        } elseif (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            // URL absoluta externa
            $url = $path;
        } else {
            // Ruta interna con parámetro ?r=
            $url = $base . '/Public/index.php?r=' . $path;
        }
        
        // Realizar la redirección
        header('Location: ' . $url, true, $code);
        exit;
    }
    
    /**
     * Devolver una respuesta JSON
     * 
     * @param mixed $data Datos a convertir en JSON
     * @param int $code Código HTTP de respuesta
     * @return void
     */
    protected function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Verificar si el usuario está autenticado
     * 
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return !empty($_SESSION['user_id']);
    }
    
    /**
     * Requerir autenticación (redirige a login si no está autenticado)
     * 
     * @param string $redirectTo Ruta de redirección si no está autenticado
     * @return void
     */
    protected function requireAuth(string $redirectTo = '/login'): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['error'] = 'Debes iniciar sesión para acceder a esta página.';
            $this->redirect($redirectTo);
        }
    }
}
