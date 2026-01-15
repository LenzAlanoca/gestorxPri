<?php
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../models/Empresa.php';

class AuthMiddleware
{
    public static function verifyToken()
    {
        $headers = apache_request_headers();

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Token no proporcionado']);
            exit;
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        $decoded = JWT::decode($token);

        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }

        // Verificar si la empresa está activa
        $empresa = new Empresa();
        if (!$empresa->verificarSuscripcionActiva($decoded['id_empresa'])) {
            http_response_code(403);
            echo json_encode(['error' => 'La suscripción de la empresa ha expirado']);
            exit;
        }

        return $decoded;
    }

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
