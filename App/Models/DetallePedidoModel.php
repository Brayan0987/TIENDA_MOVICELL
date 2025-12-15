<?php

namespace App\Models;

use PDO;

class DetallePedidoModel {
    private $db;

    public function __construct() {
        $this->db = new PDO(
            'mysql:host=localhost;dbname=movi_cell',
            'root',
            ''
        );
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->exec("SET NAMES utf8mb4");
    }

    public function crearDetalle($data) {
        $sql = "INSERT INTO detalle_pedido (id_pedido, id_celulares, cantidad, precio_unitario) 
                VALUES (:id_pedido, :id_celulares, :cantidad, :precio_unitario)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_pedido'       => $data['id_pedido'],
            ':id_celulares'    => $data['id_celulares'],
            ':cantidad'        => $data['cantidad'],
            ':precio_unitario' => $data['precio_unitario']
        ]);
    }

    public function obtenerDetallesPedido($id_pedido) {
        $sql = "SELECT 
                    dp.id_pedido,
                    dp.id_celulares,
                    dp.cantidad,
                    dp.precio_unitario,
                    pr.producto    AS nombre_producto,
                    pr.descripcion AS descripcion_producto,
                    pr.imagen      AS imagen_producto
                FROM detalle_pedido dp
                JOIN celulares c ON dp.id_celulares = c.id_celulares
                JOIN producto  pr ON c.id_producto  = pr.id_producto
                WHERE dp.id_pedido = :id_pedido";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_pedido' => $id_pedido]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
