<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CREANDO TABLA RECUPERACION_PASSWORD ===\n\n";

try {
    require_once __DIR__ . '/gestorx-backend/config/database.php';

    $database = new Database();
    $conn = $database->getConnection();

    echo "✅ Conexión a BD exitosa\n\n";

    // SQL para crear la tabla
    $sql = "CREATE TABLE IF NOT EXISTS recuperacion_password (
        id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        token VARCHAR(255) NOT NULL UNIQUE,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_expiracion DATETIME NOT NULL,
        estado_token ENUM('vigente','usado','expirado') DEFAULT 'vigente',
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
        INDEX idx_token (token),
        INDEX idx_usuario (id_usuario),
        INDEX idx_estado (estado_token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->exec($sql);
    echo "✅ Tabla 'recuperacion_password' creada exitosamente\n\n";

    // Verificar que existe
    $result = $conn->query("SHOW TABLES LIKE 'recuperacion_password'");
    if ($result->rowCount() > 0) {
        echo "✅ Tabla verificada en la BD\n";
        echo "✅ Lista para usar RecuperacionPassword model\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
