<?php

/**
 * Test del endpoint de empresas
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/JWT.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/middlewares/AuthMiddleware.php';

echo "=" . str_repeat("=", 70) . "\n";
echo "PRUEBA DEL ENDPOINT DE EMPRESAS\n";
echo "=" . str_repeat("=", 70) . "\n\n";

try {
    // 1. Login
    echo "PASO 1: Login del usuario admin (superadministrador)...\n";
    $database = new Database();
    $conn = $database->getConnection();

    $usuario_model = new Usuario();
    $user = $usuario_model->login('admin@gestorx.test', 'Admin@2026');

    if (!$user) {
        throw new Exception('Login fallido');
    }

    echo "✅ Login exitoso\n";
    echo "   ID Usuario: " . $user['id_usuario'] . "\n";
    echo "   Rol: " . $user['rol'] . "\n\n";

    // 2. Generar token
    echo "PASO 2: Generando JWT...\n";
    $token = JWT::encode([
        'id_usuario' => $user['id_usuario'],
        'correo' => $user['correo'],
        'id_empresa' => $user['id_empresa'],
        'id_rol' => $user['id_rol'],
        'nombre_rol' => $user['rol'],
        'iat' => time(),
        'exp' => time() + 86400
    ]);

    echo "✅ Token generado\n\n";

    // 3. Simular request a empresas.php con el token
    echo "PASO 3: Validando token con AuthMiddleware...\n";

    // Simular el Authorization header en CLI
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

    $auth = new \GestorX\Middlewares\AuthMiddleware($conn);
    $validated_user = $auth->validate();

    if (!$validated_user) {
        // Debug: verificar si el token es válido en sí mismo
        echo "⚠️  Validación del token fallida. Verificando token...\n";
        $decoded = JWT::decode($token);
        if ($decoded) {
            echo "   Token es válido pero validate() retornó null\n";
            var_dump($decoded);
        } else {
            echo "   El token en sí mismo es inválido\n";
        }
        throw new Exception('Validación del token fallida');
    }

    echo "✅ Token validado correctamente\n";
    echo "   ID Usuario: " . $validated_user['id_usuario'] . "\n";
    echo "   Rol ID: " . $validated_user['id_rol'] . "\n\n";

    // 4. Verificar que es superadministrador
    echo "PASO 4: Verificando permisos (debe ser superadministrador)...\n";
    if ($validated_user['id_rol'] != 1) {
        throw new Exception('El usuario no es superadministrador. ID Rol: ' . $validated_user['id_rol']);
    }

    echo "✅ Usuario es superadministrador\n\n";

    // 5. Listar empresas
    echo "PASO 5: Listando empresas desde BD...\n";
    $query = "SELECT * FROM empresa ORDER BY id_empresa";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ Empresas encontradas: " . count($empresas) . "\n\n";
    foreach ($empresas as $emp) {
        echo "   - ID: " . $emp['id_empresa'] . " | Nombre: " . $emp['nombre_comercial'] . " | Estado: " . $emp['estado_empresa'] . "\n";
    }

    echo "\n" . str_repeat("=", 70) . "\n";
    echo "✅ TODAS LAS PRUEBAS PASARON CORRECTAMENTE\n";
    echo "=" . str_repeat("=", 70) . "\n";
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo str_repeat("=", 70) . "\n";
}
