<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Headers CORS manejados por .htaccess
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
                            // Asegurar que id_empresa es null o int
                            $id_empresa_token = $user['id_empresa'] ? (int)$user['id_empresa'] : null;

                            $token = JWT::encode([
                                'id_usuario' => (int)$user['id_usuario'],
                                'id_empresa' => $id_empresa_token,
                                'id_rol' => (int)$user['id_rol'],
                                'correo' => $user['correo'],
                                'rol' => $user['rol'],
                                'nombre' => $user['nombre'] . ' ' . $user['apellido'],
                                'empresa' => $user['empresa']
                            ]);

                            // Preparar respuesta con id_empresa correcto (null, no string vacío)
                            $response_user = $user;
                            $response_user['id_empresa'] = $id_empresa_token;
                            $response_user['id_rol'] = (int)$user['id_rol'];
                            $response_user['id_usuario'] = (int)$user['id_usuario'];

                            echo json_encode([
                                'success' => true,
                                'token' => $token,
                                'user' => $response_user
                            ], JSON_UNESCAPED_UNICODE);
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
