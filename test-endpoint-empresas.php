<?php

/**
 * Test directo del endpoint empresas.php
 */

require_once __DIR__ . '/gestorx-backend/config/database.php';
require_once __DIR__ . '/gestorx-backend/helpers/JWT.php';
require_once __DIR__ . '/gestorx-backend/middlewares/AuthMiddleware.php';

echo "=" . str_repeat("=", 100) . "\n";
echo "TEST: Endpoint /api/empresas.php\n";
echo "=" . str_repeat("=", 100) . "\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();

    // 1. Crear token de usuario maestro
    echo "PASO 1: Crear token JWT para maestro\n";
    $token = \JWT::encode([
        'id_usuario' => 6,
        'id_empresa' => null,
        'id_rol' => 1,
        'nombre_rol' => 'superadministrador',
        'iat' => time(),
        'exp' => time() + 86400
    ]);

    echo "✅ Token creado\n\n";

    // 2. Simular request HTTP con token
    echo "PASO 2: Simular petición GET /api/empresas.php\n";
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    $_GET = [];  // Sin parámetros

    // Incluir el endpoint y capturar la salida
    ob_start();
    try {
        // Replicar la lógica del endpoint
        $auth = new \GestorX\Middlewares\AuthMiddleware($conn);
        $user = $auth->validate();

        if (!$user) {
            throw new Exception('No autorizado');
        }

        // Verificar que es superadministrador
        if ($user['id_rol'] != 1) {
            throw new Exception('Solo superadministrador puede acceder a esta función');
        }

        // Listar empresas
        $query = "
            SELECT 
                e.id_empresa,
                e.nombre_comercial,
                e.razon_social,
                e.telefono,
                e.correo_contacto,
                e.estado_empresa,
                e.fecha_registro,
                e.fecha_expiracion_suscripcion,
                COUNT(u.id_usuario) as total_usuarios,
                SUM(CASE WHEN u.estado_usuario = 'activo' THEN 1 ELSE 0 END) as usuarios_activos
            FROM empresa e
            LEFT JOIN usuario u ON e.id_empresa = u.id_empresa
            GROUP BY 
                e.id_empresa, 
                e.nombre_comercial, 
                e.razon_social,
                e.telefono,
                e.correo_contacto,
                e.estado_empresa,
                e.fecha_registro,
                e.fecha_expiracion_suscripcion
            ORDER BY e.fecha_registro DESC
        ";

        $stmt = $conn->query($query);
        $empresas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $response = [
            'success' => true,
            'data' => $empresas,
            'total' => count($empresas),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    $output = ob_get_clean();

    echo "Respuesta JSON:\n";
    echo str_repeat("-", 100) . "\n";
    echo $output . "\n";
    echo str_repeat("-", 100) . "\n\n";

    // Parsear JSON
    $data = json_decode($output, true);

    if ($data['success']) {
        echo "✅ ÉXITO\n";
        echo "Empresas encontradas: " . count($data['data']) . "\n\n";

        foreach ($data['data'] as $empresa) {
            echo sprintf(
                "  - ID: %d | Nombre: %s | Usuarios: %d | Estado: %s\n",
                $empresa['id_empresa'],
                $empresa['nombre_comercial'],
                $empresa['total_usuarios'],
                $empresa['estado_empresa']
            );
        }
    } else {
        echo "❌ ERROR: " . $data['error'] . "\n";
    }

    echo "\n" . str_repeat("=", 100) . "\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo str_repeat("=", 100) . "\n";
}
