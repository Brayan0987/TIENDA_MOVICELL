<?php
// Página de logout (temporal)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
echo "Sesión cerrada. <a href='perfil.php'>Volver</a>";
