<?php

/**
 * Test de login HTTP para verificar que retorna id_empresa
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/helpers/JWT.php';

echo "=" . str_repeat("=", 80) . "\n";
echo "TEST DE LOGIN - VERIFICAR RESPUESTA JSON\n";
echo "=" . str_repeat("=", 80) . "\n\n";

try {
    $usuario_model = new Usuario();

    // Test 1: Login del maestro
    echo "TEST 1: Login maestro@gestorx.test (Control Maestro)\n";
    $user = $usuario_model->login('maestro@gestorx.test', 'Maestro@2026');

    if ($user) {
        echo "✅ Login exitoso\n";
        echo "Datos retornados:\n";
        print_r($user);

        // Verificar que id_empresa está en el objeto
        if (!isset($user['id_empresa'])) {
            echo "\n⚠️  ADVERTENCIA: El campo 'id_empresa' no está en la respuesta\n";
        } else {
            echo "\n✅ El campo 'id_empresa' está presente: " . ($user['id_empresa'] ?? 'NULL') . "\n";
        }
    } else {
        echo "❌ Login fallido\n";
    }

    echo "\n" . str_repeat("-", 80) . "\n\n";

    // Test 2: Login del admin
    echo "TEST 2: Login admin@gestorx.test (Con empresa)\n";
    $user = $usuario_model->login('admin@gestorx.test', 'Admin@2026');

    if ($user) {
        echo "✅ Login exitoso\n";
        echo "Datos retornados:\n";
        print_r($user);

        // Verificar que id_empresa está en el objeto
        if (!isset($user['id_empresa'])) {
            echo "\n⚠️  ADVERTENCIA: El campo 'id_empresa' no está en la respuesta\n";
        } else {
            echo "\n✅ El campo 'id_empresa' está presente: " . $user['id_empresa'] . "\n";
        }
    } else {
        echo "❌ Login fallido\n";
    }

    echo "\n" . str_repeat("=", 80) . "\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo str_repeat("=", 80) . "\n";
}
