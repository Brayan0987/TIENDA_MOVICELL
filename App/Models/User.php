<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Db;
use mysqli;

final class User 
{
    private mysqli $db;
    
    public function __construct()
    { 
        $this->db = Db::conn(); 
    }

    public function emailExists(string $correo): bool 
    {
        $stmt = $this->db->prepare('SELECT 1 FROM usuario WHERE correo=? LIMIT 1');
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $exists = (bool)$stmt->get_result()->fetch_row();
        $stmt->close();
        return $exists;
    }

    public function findByEmail(string $correo): ?array 
    {
        $stmt = $this->db->prepare('SELECT id_usuario, nombre, correo, contraseña, telefono FROM usuario WHERE correo=? LIMIT 1');
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$row) return null;
        
        return [
            'id_usuario' => (int)$row['id_usuario'],
            'nombre'     => $row['nombre'],
            'correo'     => $row['correo'],
            'contraseña' => $row['contraseña'],
            'telefono'   => $row['telefono'] ?? null,
            'role'       => 'user'
        ];
    }

    // MÉTODO CORREGIDO: Ahora inserta en usuario Y en roles_usuario
    public function create(string $nombre, string $correo, string $password, string $telefono): bool 
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (strlen($hash) < 60) { 
            return false; 
        }
        
        // Iniciar transacción
        $this->db->begin_transaction();
        
        try {
            // 1. Insertar usuario
            $stmt = $this->db->prepare('INSERT INTO usuario (nombre, correo, contraseña, telefono) VALUES (?,?,?,?)');
            $stmt->bind_param('ssss', $nombre, $correo, $hash, $telefono);
            
            if (!$stmt->execute()) {
                throw new \Exception('Error al insertar usuario');
            }
            
            // 2. Obtener el ID del usuario recién creado
            $id_usuario = $this->db->insert_id;
            $stmt->close();
            
            // 3. Asignar rol de Cliente (id_roles = 2)
            $stmt2 = $this->db->prepare('INSERT INTO roles_usuario (id_roles, id_usuario) VALUES (2, ?)');
            $stmt2->bind_param('i', $id_usuario);
            
            if (!$stmt2->execute()) {
                throw new \Exception('Error al asignar rol');
            }
            
            $stmt2->close();
            
            // Confirmar transacción
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            // Revertir cambios si algo falla
            $this->db->rollback();
            error_log("Error en User::create(): " . $e->getMessage());
            return false;
        }
    }

    public function findById(int $id): ?array 
    {
        $stmt = $this->db->prepare('SELECT id_usuario, nombre, correo, telefono FROM usuario WHERE id_usuario=? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$row) return null;
        
        return [
            'id_usuario' => (int)$row['id_usuario'],
            'nombre'     => $row['nombre'],
            'correo'     => $row['correo'],
            'telefono'   => $row['telefono'] ?? null,
            'role'       => 'user'
        ];
    }

    public function updateProfile(int $id, array $data): bool 
    {
        $nombre = $data['nombre'] ?? '';
        $correo = $data['correo'] ?? '';
        $telefono = $data['telefono'] ?? '';
        $stmt = $this->db->prepare('UPDATE usuario SET nombre=?, correo=?, telefono=? WHERE id_usuario=?');
        $stmt->bind_param('sssi', $nombre, $correo, $telefono, $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $ok && $affected > 0;
    }

    public function updatePassword(int $id, string $newPassword): bool 
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE usuario SET contraseña=? WHERE id_usuario=?');
        $stmt->bind_param('si', $hash, $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $ok && $affected > 0;
    }

    public function hardDelete(int $id): bool 
    {
        // Usar transacción para eliminar de ambas tablas
        $this->db->begin_transaction();
        
        try {
            // 1. Eliminar de roles_usuario primero (por la foreign key)
            $stmt1 = $this->db->prepare('DELETE FROM roles_usuario WHERE id_usuario=?');
            $stmt1->bind_param('i', $id);
            $stmt1->execute();
            $stmt1->close();
            
            // 2. Eliminar de usuario
            $stmt2 = $this->db->prepare('DELETE FROM usuario WHERE id_usuario=?');
            $stmt2->bind_param('i', $id);
            $ok = $stmt2->execute();
            $stmt2->close();
            
            $this->db->commit();
            return $ok;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error en User::hardDelete(): " . $e->getMessage());
            return false;
        }
    }

    public function validate(array $data, bool $checkPassword = true): array 
    {
        $errors = [];
        $nombre = $data['nombre'] ?? $data['name'] ?? '';
        if (empty($nombre) || mb_strlen(trim($nombre)) < 2) { 
            $errors[] = 'El nombre debe tener al menos 2 caracteres.'; 
        }
        $email = $data['correo'] ?? $data['email'] ?? '';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { 
            $errors[] = 'Email inválido.'; 
        }
        $telefono = $data['telefono'] ?? $data['Telefono'] ?? '';
        if (!empty($telefono) && !preg_match('/^[0-9\s\-\+\(\)]{7,20}$/', $telefono)) { 
            $errors[] = 'Teléfono inválido.'; 
        }
        if ($checkPassword) {
            if (empty($data['password']) || mb_strlen($data['password']) < 6) { 
                $errors[] = 'La contraseña debe tener al menos 6 caracteres.'; 
            }
            if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) { 
                $errors[] = 'Las contraseñas no coinciden.'; 
            }
        }
        return $errors;
    }

    public function getUserRole(int $id_usuario): string
    {
        $stmt = $this->db->prepare('SELECT id_roles FROM roles_usuario WHERE id_usuario=? LIMIT 1');
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            return ((int)$row['id_roles'] === 1) ? 'admin' : 'user';
        }
        return 'user';
    }

    public function getUserRoleRow(int $id_usuario): ?array
    {
        $stmt = $this->db->prepare('SELECT id_usuario_rol, id_roles FROM roles_usuario WHERE id_usuario = ? LIMIT 1');
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    /**
     * Crear un token de recuperación de contraseña
     */
    public function createPasswordResetToken(int $userId, int $expirationHours = 24): ?string
    {
        $token = bin2hex(random_bytes(32));
        $expirationTime = date('Y-m-d H:i:s', strtotime("+{$expirationHours} hours"));
        
        $stmt = $this->db->prepare(
            'INSERT INTO password_reset (id_usuario, token, fecha_expiracion) 
             VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iss', $userId, $token, $expirationTime);
        
        if ($stmt->execute()) {
            $stmt->close();
            return $token;
        }
        
        $stmt->close();
        return null;
    }

    /**
     * Validar token de recuperación
     */
    public function validatePasswordResetToken(string $token): ?array
    {
        $now = date('Y-m-d H:i:s');
        
        $stmt = $this->db->prepare(
            'SELECT id_usuario, id_reset FROM password_reset 
             WHERE token = ? 
             AND fecha_expiracion > ? 
             AND utilizado = 0 
             LIMIT 1'
        );
        $stmt->bind_param('ss', $token, $now);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $row ? [
            'id_usuario' => (int)$row['id_usuario'],
            'id_reset' => (int)$row['id_reset']
        ] : null;
    }

    /**
     * Marcar token como utilizado
     */
    public function markPasswordResetAsUsed(int $resetId): bool
    {
        $stmt = $this->db->prepare('UPDATE password_reset SET utilizado = 1 WHERE id_reset = ?');
        $stmt->bind_param('i', $resetId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
