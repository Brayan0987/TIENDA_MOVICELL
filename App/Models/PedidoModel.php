<?php

namespace App\Models;

use PDO;

class PedidoModel {
    private $db;

    public function __construct() {
        $this->db = new \PDO(
            'mysql:host=localhost;dbname=movi_cell',
            'root',
            ''
        );
    }

    public function crearPedido($data) {
        $sql = "INSERT INTO pedido (id_usuario_rol, id_estado, total, id_ciudad, id_metodo, direccion, codigo_postal, imagen_pago, `descripcion del envio`, fecha) 
                VALUES (:id_usuario_rol, :id_estado, :total, :id_ciudad, :id_metodo, :direccion, :codigo_postal, :imagen_pago, :descripcion, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario_rol' => $data['id_usuario_rol'],
            ':id_estado' => $data['id_estado'],
            ':total' => $data['total'],
            ':id_ciudad' => $data['id_ciudad'],
            ':id_metodo' => $data['id_metodo'],
            ':direccion' => $data['direccion'],
            ':codigo_postal' => $data['codigo_postal'],
            ':imagen_pago' => $data['imagen_pago'],
            ':descripcion' => $data['descripcion del envio']
        ]);

        return $this->db->lastInsertId();
    }

    public function obtenerPedidos() {
        $sql = "SELECT p.*, u.nombre, u.telefono, e.estado, c.ciudad 
                FROM pedido p
                JOIN roles_usuario ru ON p.id_usuario_rol = ru.id_usuario_rol
                JOIN usuario u ON ru.id_usuario = u.id_usuario
                JOIN estado e ON p.id_estado = e.id_estado
                LEFT JOIN ubicacion c ON p.id_ciudad = c.id_ciudad
                ORDER BY p.fecha DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerPedidoPorId($id_pedido) {
        $sql = "SELECT p.*, u.nombre, u.telefono, u.correo, e.estado, c.ciudad, m.tipo as metodo_nombre
                FROM pedido p
                JOIN roles_usuario ru ON p.id_usuario_rol = ru.id_usuario_rol
                JOIN usuario u ON ru.id_usuario = u.id_usuario
                JOIN estado e ON p.id_estado = e.id_estado
                LEFT JOIN ubicacion c ON p.id_ciudad = c.id_ciudad
                LEFT JOIN metodo_pago m ON p.id_metodo = m.id_metodo
                WHERE p.id_pedido = :id_pedido
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_pedido' => $id_pedido]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}