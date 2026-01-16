<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DIRECTO DE EMPRESAS.PHP ===\n\n";

// 1. Crear token válido
require_once __DIR__ . '/gestorx-backend/config/database.php';
require_once __DIR__ . '/gestorx-backend/helpers/JWT.php';
require_once __DIR__ . '/gestorx-backend/middlewares/AuthMiddleware.php';
require_once __DIR__ . '/gestorx-backend/models/Usuario.php';

$database = new Database();
$conn = $database->getConnection();

// Crear token de prueba
$payload = [
    'id_usuario' => 1,
    'id_empresa' => null,  // NULL para Control Maestro
    'id_rol' => 1,
    'correo' => 'maestro@gestorx.test',
    'rol' => 'superadministrador'
];

$token = JWT::encode($payload);
echo "✅ Token creado: " . substr($token, 0, 50) . "...\n\n";

// 2. Simular request HTTP
echo "=== SIMULANDO REQUEST HTTP ===\n";
echo "GET /api/empresas.php\n";
echo "Authorization: Bearer " . substr($token, 0, 30) . "...\n";
echo "Content-Type: application/json\n\n";

// Simular headers HTTP
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
$_GET = [];

echo "=== EJECUTANDO SCRIPT ===\n";

ob_start();

// Incluir el script de empresas
include __DIR__ . '/gestorx-backend/api/empresas.php';

$output = ob_get_clean();

echo "=== RESPUESTA DEL SERVIDOR ===\n";
echo "Output length: " . strlen($output) . " bytes\n\n";
echo $output . "\n";

// Verificar si es JSON válido
echo "\n=== VALIDACIÓN JSON ===\n";
$data = json_decode($output, true);
if ($data) {
    echo "✅ JSON válido\n";
    echo "   success: " . ($data['success'] ? 'true' : 'false') . "\n";
    if (isset($data['data'])) {
        echo "   empresas: " . count($data['data']) . "\n";
    }
} else {
    echo "❌ JSON INVÁLIDO\n";
    echo "   Error: " . json_last_error_msg() . "\n";
}
