<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG: CARGANDO EMPRESAS COMO EL FRONTEND ===\n\n";

// 1. Crear token válido
require_once __DIR__ . '/gestorx-backend/config/database.php';
require_once __DIR__ . '/gestorx-backend/helpers/JWT.php';
require_once __DIR__ . '/gestorx-backend/middlewares/AuthMiddleware.php';

$database = new Database();
$conn = $database->getConnection();

// Crear token exacto como lo hace auth.php
$payload = [
    'id_usuario' => 1,
    'id_empresa' => null,
    'id_rol' => 1,
    'correo' => 'maestro@gestorx.test',
    'rol' => 'superadministrador'
];

$token = JWT::encode($payload);
echo "Token: " . substr($token, 0, 60) . "...\n";

// 2. Simular HTTP request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
$_GET = [];

echo "\n=== EJECUTANDO EMPRESAS.PHP ===\n\n";

try {
    ob_start();
    include __DIR__ . '/gestorx-backend/api/empresas.php';
    $output = ob_get_clean();

    echo "Output:\n";
    echo $output;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
