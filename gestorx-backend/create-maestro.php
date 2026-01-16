<?php

/**
 * Crear usuario Control Maestro (superadministrador sin empresa)
 */

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    echo "Creando usuario Control Maestro...\n\n";

    // Verificar si ya existe
    $query = "SELECT id_usuario FROM usuario WHERE correo = 'maestro@gestorx.test'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "⚠️  El usuario ya existe. Eliminando...\n";
        $query = "DELETE FROM usuario WHERE correo = 'maestro@gestorx.test'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
    }

    // Crear nuevo usuario
    $password_hash = password_hash('Maestro@2026', PASSWORD_BCRYPT);
    $query = "INSERT INTO usuario 
              (id_empresa, id_rol, nombre, apellido, correo, password_hash, estado_usuario, fecha_creacion)
              VALUES 
              (NULL, 1, 'Control', 'Maestro', 'maestro@gestorx.test', :password, 'activo', NOW())";

    $stmt = $conn->prepare($query);
    $stmt->execute([':password' => $password_hash]);

    echo "✅ Usuario Control Maestro creado correctamente\n";
    echo "   Correo: maestro@gestorx.test\n";
    echo "   Contraseña: Maestro@2026\n";
    echo "   Rol: superadministrador (id_rol=1)\n";
    echo "   ID Empresa: NULL (Control Maestro)\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
