<?php
require_once __DIR__ . '/../config/database.php';

class Empresa
{
    private $conn;
    private $table = 'empresa';

    public $id_empresa;
    public $id_plan;
    public $nombre_comercial;
    public $razon_social;
    public $direccion;
    public $telefono;
    public $correo_contacto;
    public $logo;
    public $moneda;
    public $impuesto;
    public $stock_minimo_default;
    public $estado_empresa;
    public $fecha_registro;
    public $fecha_expiracion_suscripcion;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function obtenerPorId($id_empresa)
    {
        $query = "SELECT e.*, p.nombre_plan 
                  FROM " . $this->table . " e
                  INNER JOIN plan p ON e.id_plan = p.id_plan
                  WHERE e.id_empresa = :id_empresa";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verificarLimiteUsuarios($id_empresa)
    {
        $empresa = $this->obtenerPorId($id_empresa);

        $query = "SELECT COUNT(*) as total_usuarios 
                  FROM usuario 
                  WHERE id_empresa = :id_empresa AND estado_usuario = 'activo'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total_usuarios'] < $empresa['limite_usuarios'];
    }

    public function verificarSuscripcionActiva($id_empresa)
    {
        $query = "SELECT estado_empresa, fecha_expiracion_suscripcion 
                  FROM " . $this->table . " 
                  WHERE id_empresa = :id_empresa";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();

        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empresa['estado_empresa'] != 'activa') {
            return false;
        }

        $fecha_expiracion = new DateTime($empresa['fecha_expiracion_suscripcion']);
        $fecha_actual = new DateTime();

        return $fecha_expiracion > $fecha_actual;
    }
}
