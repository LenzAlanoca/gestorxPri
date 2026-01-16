<?php

/**
 * Test de login del usuario maestro (superadministrador)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/JWT.php';
require_once __DIR__ . '/models/Usuario.php';

echo "=" . str_repeat("=", 60) . "\n";
echo "PRUEBA DE LOGIN - USUARIO MAESTRO\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();

    echo "✅ Conexión a BD exitosa\n\n";

    // Instanciar modelo Usuario
    $usuario = new Usuario($conn);

    echo "Intentando login con:\n";
    echo "  Email: maestro@gestorx.test\n";
    echo "  Password: Maestro@2026\n\n";

    // Intentar login
    $user = $usuario->login('maestro@gestorx.test', 'Maestro@2026');

    if ($user) {
        echo "✅ LOGIN EXITOSO\n\n";
        echo "Datos del usuario:\n";
        echo "  ID Usuario: " . $user['id_usuario'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
        echo "  Nombre: " . $user['nombre_usuario'] . "\n";
        echo "  ID Rol: " . $user['id_rol'] . "\n";
        echo "  Rol: " . $user['nombre_rol'] . "\n";
        echo "  ID Empresa: " . ($user['id_empresa'] ?? 'NULL (Control Maestro)') . "\n";
        echo "\n";

        // Generar token
        echo "Generando JWT...\n";
        $token = JWT::encode([
            'id_usuario' => $user['id_usuario'],
            'email' => $user['email'],
            'id_empresa' => $user['id_empresa'],
            'id_rol' => $user['id_rol'],
            'nombre_rol' => $user['nombre_rol'],
            'iat' => time(),
            'exp' => time() + 86400
        ]);

        echo "✅ Token generado exitosamente\n\n";
        echo "Token:\n";
        echo str_repeat("-", 70) . "\n";
        echo $token . "\n";
        echo str_repeat("-", 70) . "\n";
    } else {
        echo "❌ LOGIN FALLIDO\n";
        echo "El usuario no pudo ser autenticado\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
