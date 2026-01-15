<?php

/**
 * INICIALIZADOR AUTOMÁTICO DE BASE DE DATOS
 * 
 * Este archivo:
 * 1. Crea las tablas si no existen
 * 2. Inserta datos de prueba si no hay datos
 * 3. Se ejecuta silenciosamente en segundo plano
 */

namespace GestorX\Database;

require_once __DIR__ . '/Seeder.php';

class Initializer
{
    private $connection;
    private $initialized = false;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Verifica e inicializa la base de datos
     * @return bool true si se inicializó o ya estaba inicializada
     */
    public function initialize()
    {
        try {
            // Paso 1: Crear tablas si no existen
            if (!$this->tablesExist()) {
                $this->createTables();
            }

            // Paso 2: Insertar datos de prueba si no hay datos
            seedDatabase($this->connection);

            $this->initialized = true;
            return true;
        } catch (\Exception $e) {
            // Log del error (no mostrar al usuario)
            error_log('Database initialization error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si las tablas ya existen
     */
    private function tablesExist()
    {
        try {
            $query = "SHOW TABLES FROM gestorxbd";
            $stmt = $this->connection->query($query);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Crea todas las tablas desde el schema
     */
    private function createTables()
    {
        try {
            $schemaFile = __DIR__ . '/../../DATABASE_SCHEMA.sql';

            if (!file_exists($schemaFile)) {
                throw new \Exception('Archivo DATABASE_SCHEMA.sql no encontrado');
            }

            $schema = file_get_contents($schemaFile);

            // Deshabilitar verificación de foreign keys temporalmente
            $this->connection->exec('SET FOREIGN_KEY_CHECKS = 0;');

            // Separar y ejecutar queries
            $queries = array_filter(
                array_map('trim', preg_split('/;(?![^"]*"[^"]*")/m', $schema)),
                function ($q) {
                    return !empty($q) &&
                        stripos($q, 'SET FOREIGN_KEY_CHECKS') === false &&
                        stripos($q, '--') !== 0;
                }
            );

            foreach ($queries as $query) {
                if (!empty(trim($query))) {
                    $this->connection->exec($query . ';');
                }
            }

            // Habilitar verificación de foreign keys
            $this->connection->exec('SET FOREIGN_KEY_CHECKS = 1;');

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Devuelve el estado de inicialización
     */
    public function isInitialized()
    {
        return $this->initialized;
    }
}

/**
 * Función helper para inicializar automáticamente
 */
function initializeDatabase($connection)
{
    static $initializer = null;

    if ($initializer === null) {
        $initializer = new Initializer($connection);
        $initializer->initialize();
    }

    return $initializer->isInitialized();
}
