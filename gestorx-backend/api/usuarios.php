<?php
// Los headers CORS los maneja Apache a través de .htaccess
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Empresa.php';

$method = $_SERVER['REQUEST_METHOD'];
$usuario = new Usuario();

switch ($method) {
    case 'GET':
        $userData = AuthMiddleware::verifyToken();

        if (isset($_GET['id'])) {
            $user = $usuario->obtenerPorId($_GET['id']);

            if ($user) {
                echo json_encode([
                    'success' => true,
                    'data' => $user
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
            }
        } else {
            $stmt = $usuario->obtenerPorEmpresa($userData['id_empresa']);
            $usuarios = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = $row;
            }

            echo json_encode([
                'success' => true,
                'data' => $usuarios
            ]);
        }
        break;

    case 'POST':
        $userData = AuthMiddleware::checkRole(['superadministrador', 'administrador']);

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nombre) && !empty($data->correo) && !empty($data->password)) {

            $empresa = new Empresa();
            if (!$empresa->verificarLimiteUsuarios($userData['id_empresa'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Se ha alcanzado el lÃ­mite de usuarios del plan'
                ]);
                break;
            }

            $usuario->id_empresa = $userData['id_empresa'];
            $usuario->id_rol = $data->id_rol;
            $usuario->nombre = $data->nombre;
            $usuario->apellido = $data->apellido;
            $usuario->correo = $data->correo;
            $usuario->password_hash = $data->password;
            if ($usuario->crear()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario creado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear usuario. El correo puede ya estar registrado.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }
        break;

    case 'PUT':
        AuthMiddleware::checkRole(['superadministrador', 'administrador']);

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id_usuario)) {
            $usuario->id_usuario = $data->id_usuario;
            $usuario->nombre = $data->nombre;
            $usuario->apellido = $data->apellido;
            $usuario->correo = $data->correo;
            $usuario->id_rol = $data->id_rol;

            if ($usuario->actualizar()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario actualizado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar usuario'
                ]);
            }
        }
        break;

    case 'DELETE':
        AuthMiddleware::checkRole(['superadministrador']);

        if (isset($_GET['id'])) {
            if ($usuario->desactivar($_GET['id'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario desactivado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al desactivar usuario'
                ]);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'MÃ©todo no permitido']);
}
