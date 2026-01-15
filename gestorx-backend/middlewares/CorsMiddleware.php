<?php
class CorsMiddleware
{
    private static $handled = false;

    public static function handle()
    {
        // Evitar ejecutar dos veces
        if (self::$handled) {
            return;
        }

        // Evitar errores de headers ya enviados
        if (headers_sent()) {
            self::$handled = true;
            return;
        }

        // Headers CORS esenciales - SOLO UNA VEZ
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Max-Age: 86400");

        // Content-Type para respuestas JSON
        header("Content-Type: application/json; charset=UTF-8");

        self::$handled = true;

        // Si es una petición OPTIONS (preflight), termina aquí
        if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
            http_response_code(200);
            exit();
        }
    }
}
