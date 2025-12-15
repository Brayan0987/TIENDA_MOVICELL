<?php
namespace App\Core;
use mysqli;

final class Db {
    public static function conn(): mysqli {
        // Cambia los parámetros si tu DB/usuario es diferente
        $host = '127.0.0.1';
        $user = 'root';
        $pass = '';
        $db   = 'movi_cell'; // Verifica que tu base de datos tiene este nombre exacto en phpMyAdmin

        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            throw new \RuntimeException('DB connection error: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');

        return $conn;
    }
}
