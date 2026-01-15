<?php
// Los headers CORS los maneja Apache a través de .htaccess
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Empresa.php";
require_once __DIR__ . "/../models/Usuario.php";

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "POST":
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nombre_comercial) && !empty($data->correo_contacto) && !empty($data->password)) {

            try {
                $database = new Database();
                $conn = $database->getConnection();

                // Versión SIN id_plan (para evitar foreign key issues)
                $query = "INSERT INTO empresa 
                          SET nombre_comercial = :nombre_comercial,
                              razon_social = :razon_social,
                              direccion = :direccion,
                              telefono = :telefono,
                              correo_contacto = :correo_contacto,
                              moneda = 'PEN',
                              impuesto = 18.0,
                              stock_minimo_default = 10,
                              estado_empresa = 'activa',
                              fecha_registro = NOW(),
                              fecha_expiracion_suscripcion = DATE_ADD(NOW(), INTERVAL 1 MONTH)";

                $stmt = $conn->prepare($query);

                // Variables para bindParam - CORREGIDO
                $nombre_comercial = $data->nombre_comercial;
                $razon_social = isset($data->razon_social) && !empty($data->razon_social) ? $data->razon_social : $data->nombre_comercial;
                $direccion = isset($data->direccion) ? $data->direccion : "";
                $telefono = isset($data->telefono) ? $data->telefono : "";
                $correo_contacto = $data->correo_contacto;

                $stmt->bindParam(":nombre_comercial", $nombre_comercial);
                $stmt->bindParam(":razon_social", $razon_social);
                $stmt->bindParam(":direccion", $direccion);
                $stmt->bindParam(":telefono", $telefono);
                $stmt->bindParam(":correo_contacto", $correo_contacto);

                if ($stmt->execute()) {
                    $id_empresa = $conn->lastInsertId();

                    $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);

                    $queryUsuario = "INSERT INTO usuario 
                                     SET id_empresa = :id_empresa,
                                         id_rol = 1,
                                         nombre = :nombre,
                                         apellido = :apellido,
                                         correo = :correo,
                                         password_hash = :password_hash,
                                         estado_usuario = 'activo',
                                         fecha_creacion = NOW()";

                    $stmtUsuario = $conn->prepare($queryUsuario);

                    // Variables para usuario
                    $nombre_admin = isset($data->nombre_administrador) ? $data->nombre_administrador : $data->nombre_comercial;
                    $apellido_admin = isset($data->apellido_administrador) ? $data->apellido_administrador : "";

                    $stmtUsuario->bindParam(":id_empresa", $id_empresa);
                    $stmtUsuario->bindParam(":nombre", $nombre_admin);
                    $stmtUsuario->bindParam(":apellido", $apellido_admin);
                    $stmtUsuario->bindParam(":correo", $correo_contacto);
                    $stmtUsuario->bindParam(":password_hash", $hashed_password);

                    if ($stmtUsuario->execute()) {
                        echo json_encode([
                            "success" => true,
                            "message" => "Empresa y usuario administrador creados correctamente",
                            "id_empresa" => $id_empresa
                        ]);
                    } else {
                        // Si falla la creación del usuario, eliminar la empresa
                        $queryDelete = "DELETE FROM empresa WHERE id_empresa = :id_empresa";
                        $stmtDelete = $conn->prepare($queryDelete);
                        $stmtDelete->bindParam(":id_empresa", $id_empresa);
                        $stmtDelete->execute();

                        echo json_encode([
                            "success" => false,
                            "message" => "Error al crear usuario administrador"
                        ]);
                    }
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Error al crear empresa: " . implode(" ", $stmt->errorInfo())
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "message" => "Error en el servidor: " . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Datos incompletos. Requeridos: nombre_comercial, correo_contacto, password"
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}
