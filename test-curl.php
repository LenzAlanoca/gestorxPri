<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

echo "=== TEST ENDPOINT EMPRESAS-SIMPLE.PHP ===\n\n";

echo "1. Intentando SIN token:\n";
$output = shell_exec('curl -X GET http://localhost/GestorX/gestorx-backend/api/empresas-simple.php 2>&1');
echo $output . "\n\n";

echo "2. Intentando CON token inválido:\n";
$output = shell_exec('curl -H "Authorization: Bearer INVALIDO" http://localhost/GestorX/gestorx-backend/api/empresas-simple.php 2>&1');
echo $output . "\n\n";

echo "3. Creando token válido e intentando:\n";
require_once __DIR__ . '/gestorx-backend/config/database.php';
require_once __DIR__ . '/gestorx-backend/helpers/JWT.php';

$payload = [
    'id_usuario' => 1,
    'id_empresa' => null,
    'id_rol' => 1,
    'correo' => 'maestro@gestorx.test'
];

$token = JWT::encode($payload);
echo "Token: " . substr($token, 0, 60) . "...\n\n";

$output = shell_exec('curl -H "Authorization: Bearer ' . $token . '" http://localhost/GestorX/gestorx-backend/api/empresas-simple.php 2>&1');
echo $output;
