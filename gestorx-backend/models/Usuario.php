<?php
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private $conn;
    private $table = 'usuario';

    public $id_usuario;
    public $id_empresa;
    public $id_rol;
    public $nombre;
    public $apellido;
    public $correo;
    public $password_hash;
    public $estado_usuario;
    public $ultimo_acceso;
    public $fecha_creacion;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function crear()
    {
        $query = "SELECT id_usuario FROM " . $this->table . " 
                  WHERE correo = :correo AND id_empresa = :id_empresa";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return false;
        }

        $hashed_password = password_hash($this->password_hash, PASSWORD_BCRYPT);

        $query = "INSERT INTO " . $this->table . " 
                  SET id_empresa = :id_empresa,
                      id_rol = :id_rol,
                      nombre = :nombre,
                      apellido = :apellido,
                      correo = :correo,
                      password_hash = :password_hash,
                      estado_usuario = 'activo',
                      fecha_creacion = NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->bindParam(':id_rol', $this->id_rol);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':password_hash', $hashed_password);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function login($correo, $password)
    {
        $query = "SELECT u.*, r.nombre_rol, e.nombre_comercial 
                  FROM " . $this->table . " u
                  INNER JOIN rol r ON u.id_rol = r.id_rol
                  INNER JOIN empresa e ON u.id_empresa = e.id_empresa
                  WHERE u.correo = :correo AND u.estado_usuario = 'activo'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $row['password_hash'])) {
                $this->actualizarUltimoAcceso($row['id_usuario']);

                return [
                    'id_usuario' => $row['id_usuario'],
                    'id_empresa' => $row['id_empresa'],
                    'id_rol' => $row['id_rol'],
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido'],
                    'correo' => $row['correo'],
                    'rol' => $row['nombre_rol'],
                    'empresa' => $row['nombre_comercial']
                ];
            }
        }

        return false;
    }

    private function actualizarUltimoAcceso($id_usuario)
    {
        $query = "UPDATE " . $this->table . " 
                  SET ultimo_acceso = NOW() 
                  WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
    }

    public function obtenerPorEmpresa($id_empresa)
    {
        $query = "SELECT u.*, r.nombre_rol 
                  FROM " . $this->table . " u
                  INNER JOIN rol r ON u.id_rol = r.id_rol
                  WHERE u.id_empresa = :id_empresa
                  ORDER BY u.fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();

        return $stmt;
    }

    public function obtenerPorId($id_usuario)
    {
        $query = "SELECT u.*, r.nombre_rol 
                  FROM " . $this->table . " u
                  INNER JOIN rol r ON u.id_rol = r.id_rol
                  WHERE u.id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar()
    {
        $query = "UPDATE " . $this->table . " 
                  SET nombre = :nombre,
                      apellido = :apellido,
                      correo = :correo,
                      id_rol = :id_rol
                  WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':id_rol', $this->id_rol);
        $stmt->bindParam(':id_usuario', $this->id_usuario);

        return $stmt->execute();
    }

    public function desactivar($id_usuario)
    {
        $query = "UPDATE " . $this->table . " 
                  SET estado_usuario = 'inactivo' 
                  WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario);

        return $stmt->execute();
    }

    public function cambiarPassword($id_usuario, $nueva_password)
    {
        $hashed_password = password_hash($nueva_password, PASSWORD_BCRYPT);

        $query = "UPDATE " . $this->table . " 
                  SET password_hash = :password_hash 
                  WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password_hash', $hashed_password);
        $stmt->bindParam(':id_usuario', $id_usuario);

        return $stmt->execute();
    }
}
