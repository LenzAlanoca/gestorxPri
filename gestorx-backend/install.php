<?php

/**
 * INSTALADOR DE BASE DE DATOS - GestorX
 * 
 * Este script crea automáticamente todas las tablas si no existen
 * Ejecutar una sola vez: http://localhost/GestorX/gestorx-backend/install.php
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // Leer el schema SQL
    $schemaFile = __DIR__ . '/../DATABASE_SCHEMA.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception('Archivo DATABASE_SCHEMA.sql no encontrado');
    }

    $schema = file_get_contents($schemaFile);

    // Separar las consultas por punto y coma
    $queries = array_filter(
        array_map('trim', preg_split('/;(?![^"]*"[^"]*")/m', $schema)),
        function ($q) {
            return !empty($q) && stripos($q, 'SET FOREIGN_KEY_CHECKS') === false;
        }
    );

    $created = 0;
    $skipped = 0;
    $errors = [];

    // Ejecutar cada query
    foreach ($queries as $query) {
        if (empty(trim($query))) continue;

        try {
            // Agregar punto y coma si falta
            if (substr(trim($query), -1) !== ';') {
                $query .= ';';
            }

            $stmt = $conn->prepare($query);
            $stmt->execute();
            $created++;
        } catch (PDOException $e) {
            // Ignorar errores de "table already exists"
            if (strpos($e->getMessage(), 'already exists') !== false) {
                $skipped++;
            } else {
                $errors[] = [
                    'query' => substr($query, 0, 100) . '...',
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    // Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Base de datos inicializada correctamente',
        'database' => 'gestorxbd',
        'tables_created' => $created,
        'tables_skipped' => $skipped,
        'errors' => $errors,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
