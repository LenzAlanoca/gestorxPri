<?php

/**
 * Test para verificar si el usuario maestro existe
 */

require_once __DIR__ . '/config/database.php';

echo "Verificando usuario maestro en la BD...\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Buscar usuario maestro
    $query = "SELECT u.*, r.nombre_rol FROM usuario u
              LEFT JOIN rol r ON u.id_rol = r.id_rol
              WHERE u.correo = 'maestro@gestorx.test'";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo "✅ Usuario encontrado:\n";
        echo "  ID: " . $usuario['id_usuario'] . "\n";
        echo "  Email: " . $usuario['email'] . "\n";
        echo "  Nombre: " . $usuario['nombre_usuario'] . "\n";
        echo "  ID Rol: " . $usuario['id_rol'] . "\n";
        echo "  Rol: " . $usuario['nombre_rol'] . "\n";
        echo "  ID Empresa: " . ($usuario['id_empresa'] ?? 'NULL') . "\n";
        echo "  Estado: " . $usuario['estado'] . "\n";
        echo "  Contraseña hash:\n";
        echo "  " . substr($usuario['password_hash'], 0, 40) . "...\n";

        // Verificar contraseña
        $password_test = 'Maestro@2026';
        echo "\nVerificando contraseña...\n";
        $verify = password_verify($password_test, $usuario['password_hash']);
        echo "Password_verify result: " . ($verify ? "TRUE ✅" : "FALSE ❌") . "\n";
    } else {
        echo "❌ Usuario no encontrado\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
