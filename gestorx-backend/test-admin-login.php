<?php

/**
 * Test del endpoint de login (auth.php)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/JWT.php';
require_once __DIR__ . '/models/Usuario.php';

echo "=" . str_repeat("=", 70) . "\n";
echo "PRUEBA DE LOGIN - Usuario Admin (Superadministrador)\n";
echo "=" . str_repeat("=", 70) . "\n\n";

try {
    // Conexión
    $database = new Database();
    $conn = $database->getConnection();

    echo "✅ Conexión a BD exitosa\n\n";

    // Instanciar modelo Usuario
    $usuario_model = new Usuario();

    echo "Intentando login con:\n";
    echo "  Correo: admin@gestorx.test\n";
    echo "  Password: Admin@2026\n\n";

    // Intentar login
    $user = $usuario_model->login('admin@gestorx.test', 'Admin@2026');

    if ($user) {
        echo "✅ LOGIN EXITOSO\n\n";
        echo "Datos del usuario:\n";
        echo "  ID Usuario: " . $user['id_usuario'] . "\n";
        echo "  Correo: " . $user['correo'] . "\n";
        echo "  Nombre: " . $user['nombre'] . " " . $user['apellido'] . "\n";
        echo "  ID Rol: " . $user['id_rol'] . "\n";
        echo "  Rol: " . $user['nombre_rol'] . "\n";
        echo "  ID Empresa: " . ($user['id_empresa'] ?? 'NULL') . "\n";
        echo "\n";

        // Generar token
        echo "Generando JWT...\n";
        $token = JWT::encode([
            'id_usuario' => $user['id_usuario'],
            'correo' => $user['correo'],
            'id_empresa' => $user['id_empresa'],
            'id_rol' => $user['id_rol'],
            'nombre_rol' => $user['nombre_rol'],
            'iat' => time(),
            'exp' => time() + 86400
        ]);

        echo "✅ Token generado exitosamente\n\n";
        echo "Token (primeros 100 chars):\n";
        echo str_repeat("-", 70) . "\n";
        echo substr($token, 0, 100) . "...\n";
        echo str_repeat("-", 70) . "\n";

        // Validar token
        echo "\nValidando token...\n";
        $decoded = JWT::decode($token);
        if ($decoded) {
            echo "✅ Token validado correctamente\n";
            echo "  ID Usuario: " . $decoded['id_usuario'] . "\n";
            echo "  Rol: " . $decoded['nombre_rol'] . "\n";
        } else {
            echo "❌ Error al validar token\n";
        }
    } else {
        echo "❌ LOGIN FALLIDO\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
