<?php
// simple-registro.php - Versión SUPER SIMPLE para pruebas
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . "/../config/database.php";

try {
    $database = new Database();
    $conn = $database->getConnection();

    $data = json_decode(file_get_contents("php://input"));

    if (!$data) {
        throw new Exception("No data received");
    }

    // Solo prueba la conexión y responde éxito
    echo json_encode([
        "success" => true,
        "message" => "Conexión a BD exitosa",
        "data_received" => $data,
        "db_connection" => "OK",
        "timestamp" => date("Y-m-d H:i:s")
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
