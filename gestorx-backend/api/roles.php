<?php
// Los headers CORS los maneja Apache a través de .htaccess
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        AuthMiddleware::verifyToken();

        $query = "SELECT * FROM rol ORDER BY nombre_rol";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $roles = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $roles
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'MÃ©todo no permitido']);
}
