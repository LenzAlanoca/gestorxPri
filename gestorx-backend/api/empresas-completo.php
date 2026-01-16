<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Incluir dependencias
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../helpers/JWT.php';
    require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

    // 2. Conectar a BD
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // 3. Validar autenticación
    $auth = new \GestorX\Middlewares\AuthMiddleware($conn);
    $user = $auth->validate();

    if (!$user) {
        http_response_code(401);
        throw new Exception('No autorizado - Token inválido');
    }

    // 4. Verificar rol
    if ($user['id_rol'] != 1) {
        http_response_code(403);
        throw new Exception('Solo superadministrador puede acceder');
    }

    // 5. Determinar operación según el método y parámetros
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // Listar todas las empresas
        listarEmpresas($conn);
    } elseif ($method === 'PUT') {
        // Alternar estado de empresa (suspender/activar)
        if (isset($_GET['id'])) {
            alternarEstadoEmpresa($conn, $_GET['id']);
        } else {
            throw new Exception('ID de empresa requerido');
        }
    } else {
        throw new Exception('Método no permitido', 405);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
 * Listar todas las empresas
 */
function listarEmpresas($conn)
{
    try {
        $query = "
            SELECT 
                e.id_empresa,
                e.nombre_comercial,
                e.razon_social,
                COUNT(u.id_usuario) as total_usuarios,
                SUM(CASE WHEN u.estado_usuario = 'activo' THEN 1 ELSE 0 END) as usuarios_activos,
                e.estado_empresa
            FROM empresa e
            LEFT JOIN usuario u ON e.id_empresa = u.id_empresa
            GROUP BY e.id_empresa, e.nombre_comercial, e.razon_social, e.estado_empresa
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
        throw new Exception('Error al listar empresas: ' . $e->getMessage());
    }
}

/**
 * Alternar estado de empresa (suspender/activar)
 */
function alternarEstadoEmpresa($conn, $id_empresa)
{
    try {
        // Obtener estado actual
        $query = "SELECT estado_empresa FROM empresa WHERE id_empresa = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $id_empresa]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) {
            http_response_code(404);
            throw new Exception('Empresa no encontrada');
        }

        // Alternar estado
        $nuevo_estado = ($empresa['estado_empresa'] === 'activa') ? 'suspendida' : 'activa';

        // Actualizar en BD
        $update_query = "UPDATE empresa SET estado_empresa = :estado WHERE id_empresa = :id";
        $stmt = $conn->prepare($update_query);
        $result = $stmt->execute([
            ':estado' => $nuevo_estado,
            ':id' => $id_empresa
        ]);

        if (!$result) {
            throw new Exception('Error al actualizar la empresa');
        }

        // Obtener datos actualizados
        $get_query = "SELECT id_empresa, nombre_comercial, estado_empresa FROM empresa WHERE id_empresa = :id";
        $stmt = $conn->prepare($get_query);
        $stmt->execute([':id' => $id_empresa]);
        $empresa_actualizada = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Empresa ' . $nuevo_estado,
            'id_empresa' => (int)$empresa_actualizada['id_empresa'],
            'nombre_comercial' => $empresa_actualizada['nombre_comercial'],
            'estado_nuevo' => $empresa_actualizada['estado_empresa'],
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code($e->getCode() ?: 500);
        throw $e;
    }
}
