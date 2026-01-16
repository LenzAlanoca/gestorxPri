<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Incluir dependencias
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../helpers/JWT.php';
    require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

    // DEBUG: obtener headers
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? 'NO HEADER';
    error_log("Authorization Header presente: " . (strpos($auth_header, 'Bearer') !== false ? 'SÍ' : 'NO'));

    // 2. Conectar a BD
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // 3. Validar autenticación
    $auth = new \GestorX\Middlewares\AuthMiddleware($conn);
    $user = $auth->validate();

    error_log("User validation result: " . ($user ? 'VÁLIDO' : 'INVÁLIDO'));

    if (!$user) {
        http_response_code(401);
        throw new Exception('No autorizado - Token inválido');
    }

    // 4. Verificar rol
    if ($user['id_rol'] != 1) {
        http_response_code(403);
        throw new Exception('Solo superadministrador puede acceder');
    }

    // 5. Listar empresas
    $query = "
        SELECT 
            e.id_empresa,
            e.nombre_comercial,
            COUNT(u.id_usuario) as total_usuarios,
            e.estado_empresa
        FROM empresa e
        LEFT JOIN usuario u ON e.id_empresa = u.id_empresa
        GROUP BY e.id_empresa, e.nombre_comercial, e.estado_empresa
        ORDER BY e.fecha_registro DESC
    ";

    $stmt = $conn->query($query);
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $empresas,
        'total' => count($empresas),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
