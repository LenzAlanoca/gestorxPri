<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Obtener estructura de la tabla usuario
    $query = "DESCRIBE usuario";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Estructura de la tabla usuario:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($columns as $col) {
        echo sprintf(
            "%-20s %-20s %-15s %-10s\n",
            $col['Field'],
            $col['Type'],
            $col['Null'] ?? '',
            $col['Key'] ?? ''
        );
    }
    echo str_repeat("-", 80) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
