<?php

namespace GestorX\Middlewares;

require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../models/Empresa.php';

class AuthMiddleware
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Obtiene los headers HTTP. Compatible con CLI (para testing).
     */
    private function getHeaders()
    {
        // Primero intentar con apache_request_headers si está disponible
        if (function_exists('apache_request_headers')) {
            try {
                $headers = \apache_request_headers();
                if ($headers !== false) {
                    return $headers;
                }
            } catch (Exception $e) {
                // Si falla, continuar con el método alternativo
            }
        }

        // Método alternativo: buscar en $_SERVER
        $headers = [];

        // Buscar en $_SERVER las claves HTTP_*
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_name = str_replace('HTTP_', '', $key);
                $header_name = str_replace('_', '-', $header_name);
                $headers[$header_name] = $value;
            }
        }

        // También revisar AUTHORIZATION directamente
        if (isset($_SERVER['AUTHORIZATION'])) {
            $headers['Authorization'] = $_SERVER['AUTHORIZATION'];
        }

        // Y HTTP_AUTHORIZATION para otros casos
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
        }

        return $headers;
    }

    /**
     * Valida el token JWT y retorna datos del usuario
     * Compatible con Control Maestro (id_empresa NULL)
     */
    public function validate()
    {
        $headers = $this->getHeaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = \JWT::decode($token);

            if (!$decoded) {
                return null;
            }

            // Verificar si la empresa está activa (solo si no es Control Maestro)
            if ($decoded['id_empresa'] !== null) {
                if (!$this->verificarEmpresaActiva($decoded['id_empresa'])) {
                    return null;
                }
            }

            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verifica que la empresa esté activa
     */
    private function verificarEmpresaActiva($id_empresa)
    {
        try {
            $query = "SELECT estado_empresa FROM empresa WHERE id_empresa = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id_empresa]);
            $empresa = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $empresa && $empresa['estado_empresa'] === 'activa';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Método estático para compatibilidad con código existente
     */
    public static function verifyToken()
    {
        // Para funciones estáticas, no tenemos acceso a $this
        // Necesitamos obtener headers directamente
        if (function_exists('apache_request_headers')) {
            $headers = \apache_request_headers();
        } else {
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header_name = str_replace('HTTP_', '', $key);
                    $header_name = str_replace('_', '-', $header_name);
                    $headers[$header_name] = $value;
                }
            }
        }

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Token no proporcionado']);
            exit;
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = \JWT::decode($token);

            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['error' => 'Token inválido']);
                exit;
            }

            // Verificar si la empresa está activa (solo si no es Control Maestro)
            if ($decoded['id_empresa'] !== null) {
                require_once __DIR__ . '/../models/Empresa.php';
                $empresa = new \Empresa();
                if (!$empresa->verificarSuscripcionActiva($decoded['id_empresa'])) {
                    http_response_code(403);
                    echo json_encode(['error' => 'La suscripción de la empresa ha expirado']);
                    exit;
                }
            }

            return $decoded;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Error en autenticación']);
            exit;
        }
    }

    /**
     * Método estático para verificar roles
     */
    public static function checkRole($allowedRoles)
    {
        $userData = self::verifyToken();

        if (!in_array($userData['rol'], $allowedRoles)) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permisos para esta acción']);
            exit;
        }

        return $userData;
    }
}
