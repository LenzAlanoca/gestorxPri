<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    echo "Usuarios en la BD:\n";
    echo str_repeat("-", 100) . "\n";

    $query = "SELECT u.*, r.nombre_rol FROM usuario u
              LEFT JOIN rol r ON u.id_rol = r.id_rol
              ORDER BY u.id_usuario";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) > 0) {
        foreach ($usuarios as $u) {
            echo sprintf(
                "ID:%2d | Correo:%-25s | Nombre:%-15s | Rol:%-20s | Empresa:%s\n",
                $u['id_usuario'],
                $u['correo'],
                $u['nombre'] . ' ' . $u['apellido'],
                $u['nombre_rol'],
                ($u['id_empresa'] ?? 'NULL')
            );
        }
    } else {
        echo "No hay usuarios registrados\n";
    }

    echo str_repeat("-", 100) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
