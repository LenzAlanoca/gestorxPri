<?php

/**
 * MODELO: RecuperacionPassword
 * 
 * Maneja la recuperación de contraseñas de usuarios
 * Funcionalidad preparada para futuros módulos
 */

class RecuperacionPassword
{
    private $table = 'recuperacion_password';
    private $conn;

    public function __construct($connection = null)
    {
        if ($connection) {
            $this->conn = $connection;
        }
    }

    /**
     * Crear solicitud de recuperación de contraseña
     * 
     * @param int $id_usuario ID del usuario
     * @param int $expiracion_horas Horas hasta que expire el token (default: 24)
     * @return array Token generado y fecha de expiración
     */
    public function crearSolicitud($id_usuario, $expiracion_horas = 24)
    {
        try {
            // Generar token único
            $token = bin2hex(random_bytes(32));

            // Calcular fecha de expiración
            $fecha_creacion = date('Y-m-d H:i:s');
            $fecha_expiracion = date('Y-m-d H:i:s', strtotime("+$expiracion_horas hours"));

            // Insertar en BD
            $query = "INSERT INTO " . $this->table . " 
                      (id_usuario, token, fecha_creacion, fecha_expiracion, estado_token)
                      VALUES (:id_usuario, :token, :fecha_creacion, :fecha_expiracion, 'vigente')";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':token' => $token,
                ':fecha_creacion' => $fecha_creacion,
                ':fecha_expiracion' => $fecha_expiracion
            ]);

            return [
                'success' => true,
                'token' => $token,
                'fecha_expiracion' => $fecha_expiracion,
                'id_recuperacion' => $this->conn->lastInsertId()
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al crear solicitud de recuperación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validar token de recuperación
     * 
     * @param string $token Token a validar
     * @return array Datos del token o error
     */
    public function validarToken($token)
    {
        try {
            $query = "SELECT id_recuperacion, id_usuario, estado_token, fecha_expiracion
                      FROM " . $this->table . "
                      WHERE token = :token";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $token]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'error' => 'Token no encontrado'
                ];
            }

            $recuperacion = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Verificar estado
            if ($recuperacion['estado_token'] === 'usado') {
                return [
                    'success' => false,
                    'error' => 'Este token ya ha sido utilizado'
                ];
            }

            // Verificar expiración
            if ($recuperacion['estado_token'] === 'expirado' || strtotime($recuperacion['fecha_expiracion']) < time()) {
                $this->marcarExpirado($recuperacion['id_recuperacion']);
                return [
                    'success' => false,
                    'error' => 'El token ha expirado'
                ];
            }

            return [
                'success' => true,
                'id_recuperacion' => $recuperacion['id_recuperacion'],
                'id_usuario' => $recuperacion['id_usuario'],
                'estado' => $recuperacion['estado_token']
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al validar token: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Marcar token como usado
     * 
     * @param int $id_recuperacion ID de la recuperación
     * @return bool
     */
    public function marcarUsado($id_recuperacion)
    {
        try {
            $query = "UPDATE " . $this->table . "
                      SET estado_token = 'usado'
                      WHERE id_recuperacion = :id";

            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id_recuperacion]);
        } catch (\PDOException $e) {
            error_log('Error al marcar token como usado: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar token como expirado
     * 
     * @param int $id_recuperacion ID de la recuperación
     * @return bool
     */
    private function marcarExpirado($id_recuperacion)
    {
        try {
            $query = "UPDATE " . $this->table . "
                      SET estado_token = 'expirado'
                      WHERE id_recuperacion = :id";

            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id_recuperacion]);
        } catch (\PDOException $e) {
            error_log('Error al marcar token como expirado: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener solicitudes activas de un usuario
     * 
     * @param int $id_usuario ID del usuario
     * @return array Lista de solicitudes activas
     */
    public function obtenerSolicitudesActivas($id_usuario)
    {
        try {
            $query = "SELECT id_recuperacion, fecha_creacion, fecha_expiracion, estado_token
                      FROM " . $this->table . "
                      WHERE id_usuario = :id_usuario AND estado_token = 'vigente'
                      ORDER BY fecha_creacion DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id_usuario' => $id_usuario]);

            return [
                'success' => true,
                'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener solicitudes: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancelar todas las solicitudes vigentes de un usuario
     * (Útil cuando el usuario recuerda su contraseña antes de usar el token)
     * 
     * @param int $id_usuario ID del usuario
     * @return bool
     */
    public function cancelarSolicitudes($id_usuario)
    {
        try {
            $query = "UPDATE " . $this->table . "
                      SET estado_token = 'expirado'
                      WHERE id_usuario = :id_usuario AND estado_token = 'vigente'";

            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id_usuario' => $id_usuario]);
        } catch (\PDOException $e) {
            error_log('Error al cancelar solicitudes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar tokens expirados antiguos (mantener BD limpia)
     * Se ejecuta periódicamente desde tareas de mantenimiento
     * 
     * @param int $dias_antiguedad Elimina records con más de X días
     * @return int Cantidad de registros eliminados
     */
    public function limpiarTokenosAntiguos($dias_antiguedad = 30)
    {
        try {
            $fecha_limite = date('Y-m-d H:i:s', strtotime("-$dias_antiguedad days"));

            $query = "DELETE FROM " . $this->table . "
                      WHERE estado_token IN ('usado', 'expirado')
                      AND fecha_creacion < :fecha_limite";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':fecha_limite' => $fecha_limite]);

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log('Error al limpiar tokens antiguos: ' . $e->getMessage());
            return 0;
        }
    }
}
