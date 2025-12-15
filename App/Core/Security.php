<?php
namespace App\Core;

final class Security 
{
    public static function csrfToken(): string 
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    public static function enforceCsrfPost(): void 
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $token = $_POST['csrf'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        if (!hash_equals($sessionToken, $token)) {
            throw new \RuntimeException('CSRF token mismatch');
        }
    }
}
