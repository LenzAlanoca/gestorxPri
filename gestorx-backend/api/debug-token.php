<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../helpers/JWT.php';

    // Obtener el token del header
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    // Extraer el token
    $token = str_replace('Bearer ', '', $auth_header);

    // Información sobre el token recibido
    $result = [
        'received' => [
            'auth_header_present' => !empty($auth_header),
            'auth_header_length' => strlen($auth_header),
            'token_length' => strlen($token),
            'token_first_60' => substr($token, 0, 60),
            'token_parts_count' => count(explode('.', $token))
        ]
    ];

    // Intentar decodificar
    $decoded = JWT::decode($token);

    if ($decoded) {
        $result['decode_result'] = 'SUCCESS';
        $result['decoded_data'] = $decoded;
    } else {
        $result['decode_result'] = 'FAILED - JWT::decode returned false';

        // Debug: intentar decodificar manualmente
        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $result['manual_decode'] = [
                'header' => json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0])), true),
                'payload' => json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true),
                'signature_matches' => 'checking...'
            ];

            // Verificar firma
            $secret_key = 'Tu_Clave_Secreta_Super_Segura_Aqui';
            $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $secret_key, true);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            $result['manual_decode']['signature_matches'] = ($base64UrlSignature === $parts[2]) ? 'YES' : 'NO';
            $result['manual_decode']['expected_signature'] = substr($base64UrlSignature, 0, 20) . '...';
            $result['manual_decode']['received_signature'] = substr($parts[2], 0, 20) . '...';
        }
    }

    http_response_code(200);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
