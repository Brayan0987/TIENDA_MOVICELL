<?php
namespace App\Models;

use App\Core\Db;
use mysqli;

final class Order {
    private mysqli $db;

    public function __construct() {
        $this->db = Db::conn();
    }

    /**
     * Crear un nuevo pedido con sus items
     */
    public function create(array $orderData, array $items): ?int {
        $this->db->begin_transaction();

        try {
            error_log("📦 Creando pedido con datos: " . json_encode($orderData));

            // Insertar pedido
            $stmt = $this->db->prepare(
                'INSERT INTO pedido (id_usuario_rol, direccion, codigo_postal, imagen_pago, `descripcion del envio`, id_estado, total, id_ciudad, id_metodo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            if (!$stmt) {
                throw new \Exception('Error al preparar statement pedido: ' . $this->db->error);
            }

            $imagenPago = $orderData['imagen_pago'] ?? '';
            $descripcion = $orderData['notas'] ?? '';

            $stmt->bind_param(
                'issssidii',
                $orderData['id_usuario_rol'],
                $orderData['direccion'],
                $orderData['codigo_postal'],
                $imagenPago,
                $descripcion,
                $orderData['id_estado'],
                $orderData['total'],
                $orderData['id_ciudad'],
                $orderData['id_metodo']
            );

            if (!$stmt->execute()) {
                throw new \Exception('Error al crear pedido: ' . $stmt->error);
            }

            $orderId = $stmt->insert_id ?: $this->db->insert_id;
            $stmt->close();
            error_log("✅ Pedido #$orderId insertado en tabla pedido");

            // Insertar items en detalle_pedido
            $stmtItem = $this->db->prepare(
                'INSERT INTO detalle_pedido (id_pedido, id_celulares, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)'
            );

            if (!$stmtItem) {
                throw new \Exception('Error al preparar statement items: ' . $this->db->error);
            }

            foreach ($items as $item) {
                error_log('📦 Insertando item: ' . json_encode($item));

                $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
                $precio = isset($item['precio_unitario']) ? (float)$item['precio_unitario'] : 0.0;
                $subtotal = isset($item['subtotal']) ? (float)$item['subtotal'] : ($cantidad * $precio);

                $stmtItem->bind_param(
                    'iiidd',
                    $orderId,
                    $item['id_celulares'],
                    $cantidad,
                    $precio,
                    $subtotal
                );

                if (!$stmtItem->execute()) {
                    throw new \Exception('Error al insertar item: ' . $stmtItem->error);
                }

                error_log('✅ Item insertado correctamente');
            }

            $stmtItem->close();

            $this->db->commit();
            error_log("✅✅✅ Pedido #$orderId creado exitosamente con " . count($items) . " items");
            return $orderId;

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('❌❌❌ Error al crear pedido: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Obtener pedido por ID con sus items
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT p.*, e.estado as estado_nombre, m.tipo as metodo_nombre, u.ciudad as ciudad_nombre 
             FROM pedido p
             LEFT JOIN estado e ON p.id_estado = e.id_estado
             LEFT JOIN metodo_pago m ON p.id_metodo = m.id_metodo
             LEFT JOIN ubicacion u ON p.id_ciudad = u.id_ciudad
             WHERE p.id_pedido = ? LIMIT 1'
        );

        if (!$stmt) {
            error_log('Error al preparar query findById: ' . $this->db->error);
            return null;
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            error_log('Error al ejecutar query findById: ' . $stmt->error);
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();

        if (!$order) {
            return null;
        }

        // Traer items
        $stmtItems = $this->db->prepare('SELECT dp.*, c.nombre as nombre_producto FROM detalle_pedido dp LEFT JOIN celulares c ON dp.id_celulares = c.id_celulares WHERE dp.id_pedido = ?');
        if ($stmtItems) {
            $stmtItems->bind_param('i', $id);
            $stmtItems->execute();
            $res = $stmtItems->get_result();
            $items = [];
            while ($row = $res->fetch_assoc()) {
                $items[] = $row;
            }
            $stmtItems->close();
            $order['items'] = $items;
        } else {
            $order['items'] = [];
        }

        return $order;
    }

    /**
     * Obtener pedidos de un usuario (usando id_usuario real)
     */
    public function findByUserId(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT p.*, e.estado as estado_nombre 
             FROM pedido p
             JOIN roles_usuario ru ON ru.id_usuario_rol = p.id_usuario_rol
             LEFT JOIN estado e ON p.id_estado = e.id_estado
             WHERE ru.id_usuario = ?
             ORDER BY p.fecha DESC'
        );

        if (!$stmt) {
            error_log('Error al preparar query findByUserId: ' . $this->db->error);
            return [];
        }

        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            error_log('Error al ejecutar query findByUserId: ' . $stmt->error);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        return $orders;
    }

    /**
     * Actualizar estado del pedido
     */
    public function updateStatus(int $id, int $statusId): bool {
        $stmt = $this->db->prepare('UPDATE pedido SET id_estado = ? WHERE id_pedido = ?');

        if (!$stmt) {
            error_log("Error al preparar query: " . $this->db->error);
            return false;
        }

        $stmt->bind_param('ii', $statusId, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
