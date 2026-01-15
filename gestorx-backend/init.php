<?php

/**
 * SCRIPT DE INICIALIZACIÓN MANUAL
 * Ejecuta la creación de tablas y datos de prueba
 * 
 * Acceso: http://localhost/GestorX/gestorx-backend/init.php
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Incluir configuración
    require_once __DIR__ . '/config/database.php';

    // Crear instancia de base de datos
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        echo json_encode([
            'success' => true,
            'message' => '✅ Base de datos inicializada correctamente',
            'status' => 'Las tablas se crearon y los datos de prueba se insertaron',
            'timestamp' => date('Y-m-d H:i:s'),
            'next_step' => 'Abre http://localhost:8081 y inicia sesión con: admin@gestorx.test / Admin@2026'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        throw new Exception('No se pudo conectar a la base de datos');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'hint' => 'Verifica que: 1) MySQL está ejecutándose, 2) XAMPP está corriendo, 3) La base de datos "gestorxbd" existe'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
