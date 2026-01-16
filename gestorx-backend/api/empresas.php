<?php

/**
 * API: GESTIÓN DE EMPRESAS
 * Endpoints para Control Maestro
 * 
 * Require: Rol superadministrador
 * 
 * ENDPOINTS:
 * - GET /api/empresas.php              → Listar todas las empresas con estadísticas
 * - GET /api/empresas.php?id=X         → Obtener empresa específica
 * - GET /api/empresas.php?usuarios=X   → Listar usuarios de empresa X
 * - PUT /api/empresas.php?id=X         → Desactivar/Activar empresa
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');

// Headers CORS manejados por .htaccess
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // Verificar autenticación
    $auth = new \GestorX\Middlewares\AuthMiddleware($conn);
    $user = $auth->validate();

    if (!$user) {
        http_response_code(401);
        throw new Exception('No autorizado - Token inválido o ausente');
    }

    // Verificar que es superadministrador
    if ($user['id_rol'] != 1) {
        http_response_code(403);
        throw new Exception('Solo superadministrador puede acceder a esta función');
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // Listar todas las empresas CON usuarios
        if (!isset($_GET['usuarios']) && !isset($_GET['id'])) {
            listarEmpresas($conn);
        }
        // Listar usuarios de una empresa específica
        elseif (isset($_GET['usuarios'])) {
            listarUsuariosEmpresa($conn, $_GET['usuarios']);
        }
        // Obtener empresa específica
        elseif (isset($_GET['id'])) {
            obtenerEmpresa($conn, $_GET['id']);
        }
    } elseif ($method === 'PUT') {
        // Desactivar/Activar empresa
        if (isset($_GET['id'])) {
            desactivarEmpresa($conn, $_GET['id']);
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
 * LISTAR TODAS LAS EMPRESAS
 * Retorna: lista de empresas + cantidad de usuarios activos
 */
function listarEmpresas($conn)
{
    try {
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
                p.nombre_plan,
                COUNT(u.id_usuario) as total_usuarios,
                SUM(CASE WHEN u.estado_usuario = 'activo' THEN 1 ELSE 0 END) as usuarios_activos
            FROM empresa e
            LEFT JOIN plan p ON e.id_plan = p.id_plan
            LEFT JOIN usuario u ON e.id_empresa = u.id_empresa
            GROUP BY 
                e.id_empresa, 
                e.nombre_comercial, 
                e.razon_social,
                e.telefono,
                e.correo_contacto,
                e.estado_empresa,
                e.fecha_registro,
                e.fecha_expiracion_suscripcion,
                p.nombre_plan
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
 * OBTENER EMPRESA ESPECÍFICA
 */
function obtenerEmpresa($conn, $id_empresa)
{
    try {
        $query = "
            SELECT 
                e.*,
                p.nombre_plan
            FROM empresa e
            LEFT JOIN plan p ON e.id_plan = p.id_plan
            WHERE e.id_empresa = :id_empresa
        ";

        $stmt = $conn->prepare($query);
        $stmt->execute([':id_empresa' => $id_empresa]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) {
            throw new Exception('Empresa no encontrada', 404);
        }

        echo json_encode([
            'success' => true,
            'data' => $empresa,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * LISTAR USUARIOS DE UNA EMPRESA
 * Retorna: lista de usuarios + rol
 */
function listarUsuariosEmpresa($conn, $id_empresa)
{
    try {
        $query = "
            SELECT 
                u.id_usuario,
                u.nombre,
                u.apellido,
                u.correo,
                u.estado_usuario,
                u.ultimo_acceso,
                u.fecha_creacion,
                r.nombre_rol,
                e.nombre_comercial as empresa
            FROM usuario u
            LEFT JOIN rol r ON u.id_rol = r.id_rol
            LEFT JOIN empresa e ON u.id_empresa = e.id_empresa
            WHERE u.id_empresa = :id_empresa
            ORDER BY u.fecha_creacion DESC
        ";

        $stmt = $conn->prepare($query);
        $stmt->execute([':id_empresa' => $id_empresa]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $usuarios,
            'total' => count($usuarios),
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        throw new Exception('Error al listar usuarios: ' . $e->getMessage());
    }
}

/**
 * DESACTIVAR/ACTIVAR EMPRESA
 * Al desactivar: Los usuarios de esa empresa NO podrán hacer login
 */
function desactivarEmpresa($conn, $id_empresa)
{
    try {
        // Obtener estado actual
        $query = "SELECT estado_empresa FROM empresa WHERE id_empresa = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $id_empresa]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) {
            throw new Exception('Empresa no encontrada', 404);
        }

        // Alternar estado
        $nuevo_estado = ($empresa['estado_empresa'] === 'activa') ? 'suspendida' : 'activa';

        // Actualizar
        $update_query = "UPDATE empresa SET estado_empresa = :estado WHERE id_empresa = :id";
        $stmt = $conn->prepare($update_query);
        $stmt->execute([
            ':estado' => $nuevo_estado,
            ':id' => $id_empresa
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Empresa ' . $nuevo_estado,
            'id_empresa' => $id_empresa,
            'estado_nuevo' => $nuevo_estado,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        throw $e;
    }
}
