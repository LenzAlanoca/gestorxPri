<?php
// Headers CORS simples
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Usuario.php';

try {
    // Conectar a BD
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("No se pudo conectar a la BD");
    }

    // Verificar si existen tablas
    $query = "SHOW TABLES LIKE 'usuario'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        throw new Exception("Tabla 'usuario' no existe. Ejecuta primero: http://localhost/GestorX/gestorx-backend/init.php");
    }

    // Verificar si existen datos
    $query = "SELECT COUNT(*) as total FROM usuario";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['total'] == 0) {
        throw new Exception("No hay usuarios en la BD. Ejecuta primero: http://localhost/GestorX/gestorx-backend/init.php");
    }

    // Listar usuarios
    $query = "SELECT id_usuario, nombre, apellido, correo FROM usuario LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'API funcionando correctamente',
        'total_usuarios' => $result['total'],
        'usuarios_muestra' => $usuarios,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
