<?php

/**
 * Simular la respuesta JSON que enviaría auth.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/helpers/JWT.php';

echo "=" . str_repeat("=", 80) . "\n";
echo "SIMULACIÓN DE RESPUESTA JSON DE AUTH.PHP\n";
echo "=" . str_repeat("=", 80) . "\n\n";

try {
    $usuario_model = new Usuario();

    // Login del maestro
    $user = $usuario_model->login('maestro@gestorx.test', 'Maestro@2026');

    if ($user) {
        $token = JWT::encode([
            'id_usuario' => $user['id_usuario'],
            'id_empresa' => $user['id_empresa'],
            'rol' => $user['rol'],
            'nombre' => $user['nombre'] . ' ' . $user['apellido'],
            'empresa' => $user['empresa']
        ]);

        // Convertir id_empresa vacío a null (igual que en auth.php)
        $user['id_empresa'] = $user['id_empresa'] ? (int)$user['id_empresa'] : null;

        $response = [
            'success' => true,
            'token' => $token,
            'user' => $user
        ];

        $json = json_encode($response, JSON_UNESCAPED_UNICODE);

        echo "Respuesta JSON que se enviaría:\n";
        echo str_repeat("-", 80) . "\n";
        echo $json . "\n";
        echo str_repeat("-", 80) . "\n\n";

        // Parsear como lo haría JavaScript
        $parsed = json_decode($json, true);
        echo "Después de JSON.parse (JavaScript):\n";
        echo "user.id_empresa = " . var_export($parsed['user']['id_empresa'], true) . "\n";
        echo "!user.id_empresa = " . var_export(!$parsed['user']['id_empresa'], true) . "\n";
        echo "user.id_empresa === null = " . var_export($parsed['user']['id_empresa'] === null, true) . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
