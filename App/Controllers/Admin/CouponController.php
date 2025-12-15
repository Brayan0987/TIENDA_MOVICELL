<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Db;

class CouponController
{
    private \mysqli $db;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Solo admin
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /TIENDA_MOVICELL/public/index.php?r=/login');
            exit;
        }

        $this->db = Db::conn();
    }

    /**
     * Listar cupones
     */
    public function index(): void
    {
        $cupones = [];
        $sql = "SELECT id_cupon, codigo, tipo, valor, monto_minimo,
                       fecha_inicio, fecha_fin, uso_maximo, uso_actual, activo
                FROM cupon
                ORDER BY id_cupon DESC";
        if ($res = $this->db->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $cupones[] = $row;
            }
            $res->free();
        }

        $data = ['cupones' => $cupones];

        extract($data);
        require __DIR__ . '/../../Views/Admin/cupones.php';
    }

    /**
     * Crear / actualizar cupón (POST desde la misma vista)
     */
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /TIENDA_MOVICELL/public/index.php?r=/admin/cupones');
            exit;
        }

        $id_cupon    = isset($_POST['id_cupon']) ? (int)$_POST['id_cupon'] : 0;
        $codigo      = trim($_POST['codigo'] ?? '');
        $tipo        = $_POST['tipo'] ?? 'percent'; // percent | fixed
        $valor       = (float)($_POST['valor'] ?? 0);
        $montoMinimo = $_POST['monto_minimo'] !== '' ? (float)$_POST['monto_minimo'] : null;
        $fechaInicio = $_POST['fecha_inicio'] !== '' ? $_POST['fecha_inicio'] : null;
        $fechaFin    = $_POST['fecha_fin'] !== '' ? $_POST['fecha_fin'] : null;
        $usoMaximo   = $_POST['uso_maximo'] !== '' ? (int)$_POST['uso_maximo'] : null;
        $activo      = isset($_POST['activo']) ? 1 : 0;

        if ($codigo === '' || $valor <= 0) {
            $_SESSION['admin_coupon_msg'] = 'Código y valor son obligatorios.';
            header('Location: /TIENDA_MOVICELL/public/index.php?r=/admin/cupones');
            exit;
        }

        if ($id_cupon > 0) {
            // UPDATE
            $sql = "UPDATE cupon
                    SET codigo = ?, tipo = ?, valor = ?, monto_minimo = ?,
                        fecha_inicio = ?, fecha_fin = ?, uso_maximo = ?, activo = ?
                    WHERE id_cupon = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'ssdsssiii',
                $codigo,
                $tipo,
                $valor,
                $montoMinimo,
                $fechaInicio,
                $fechaFin,
                $usoMaximo,
                $activo,
                $id_cupon
            );
            $stmt->execute();
            $stmt->close();

            $_SESSION['admin_coupon_msg'] = 'Cupón actualizado correctamente.';
        } else {
            // INSERT
            $sql = "INSERT INTO cupon
                    (codigo, tipo, valor, monto_minimo, fecha_inicio, fecha_fin, uso_maximo, uso_actual, activo)
                    VALUES (?,?,?,?,?,?,?,?,?)";
            $usoActual = 0;
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'ssdsssiii',
                $codigo,
                $tipo,
                $valor,
                $montoMinimo,
                $fechaInicio,
                $fechaFin,
                $usoMaximo,
                $usoActual,
                $activo
            );
            $stmt->execute();
            $stmt->close();

            $_SESSION['admin_coupon_msg'] = 'Cupón creado correctamente.';
        }

        header('Location: /TIENDA_MOVICELL/public/index.php?r=/admin/cupones');
        exit;
    }

    /**
     * Eliminar cupón
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /TIENDA_MOVICELL/public/index.php?r=/admin/cupones');
            exit;
        }

        $id_cupon = isset($_POST['id_cupon']) ? (int)$_POST['id_cupon'] : 0;
        if ($id_cupon > 0) {
            $stmt = $this->db->prepare("DELETE FROM cupon WHERE id_cupon = ?");
            $stmt->bind_param('i', $id_cupon);
            $stmt->execute();
            $stmt->close();

            $_SESSION['admin_coupon_msg'] = 'Cupón eliminado correctamente.';
        }

        header('Location: /TIENDA_MOVICELL/public/index.php?r=/admin/cupones');
        exit;
    }
}
