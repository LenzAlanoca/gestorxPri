<?php
// Los headers CORS los maneja Apache a través de .htaccess
header('Content-Type: application/json; charset=utf-8');

// Responder a peticiones OPTIONS (preflight)
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/JWT.php';



$method = $_SERVER['REQUEST_METHOD'];
$usuario = new Usuario();

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (isset($data->action)) {
            switch ($data->action) {
                case 'login':
                    if (!empty($data->correo) && !empty($data->password)) {
                        $user = $usuario->login($data->correo, $data->password);

                        if ($user) {
                            $token = JWT::encode([
                                'id_usuario' => $user['id_usuario'],
                                'id_empresa' => $user['id_empresa'],
                                'rol' => $user['rol'],
                                'nombre' => $user['nombre'] . ' ' . $user['apellido'],
                                'empresa' => $user['empresa']
                            ]);

                            echo json_encode([
                                'success' => true,
                                'token' => $token,
                                'user' => $user
                            ]);
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Credenciales incorrectas'
                            ]);
                        }
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Datos incompletos'
                        ]);
                    }
                    break;

                case 'logout':
                    echo json_encode([
                        'success' => true,
                        'message' => 'SesiÃ³n cerrada'
                    ]);
                    break;
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'MÃ©todo no permitido']);
}
