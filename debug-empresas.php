<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG EMPRESAS ENDPOINT ===\n\n";

// 1. Verificar includes
echo "1. Verificando includes...\n";
if (file_exists(__DIR__ . '/gestorx-backend/config/database.php')) {
    echo "   ✅ database.php existe\n";
} else {
    echo "   ❌ database.php NO existe\n";
}

if (file_exists(__DIR__ . '/gestorx-backend/helpers/JWT.php')) {
    echo "   ✅ JWT.php existe\n";
} else {
    echo "   ❌ JWT.php NO existe\n";
}

if (file_exists(__DIR__ . '/gestorx-backend/middlewares/AuthMiddleware.php')) {
    echo "   ✅ AuthMiddleware.php existe\n";
} else {
    echo "   ❌ AuthMiddleware.php NO existe\n";
}

echo "\n2. Intentando cargar clases...\n";

try {
    require_once __DIR__ . '/gestorx-backend/config/database.php';
    echo "   ✅ Database cargada\n";
} catch (Exception $e) {
    echo "   ❌ Error en Database: " . $e->getMessage() . "\n";
    die();
}

try {
    require_once __DIR__ . '/gestorx-backend/helpers/JWT.php';
    echo "   ✅ JWT cargado\n";
} catch (Exception $e) {
    echo "   ❌ Error en JWT: " . $e->getMessage() . "\n";
    die();
}

try {
    require_once __DIR__ . '/gestorx-backend/middlewares/AuthMiddleware.php';
    echo "   ✅ AuthMiddleware cargado\n";
} catch (Exception $e) {
    echo "   ❌ Error en AuthMiddleware: " . $e->getMessage() . "\n";
    die();
}

echo "\n3. Intentando conectar a base de datos...\n";

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        echo "   ✅ Conexión exitosa\n";

        // Test query
        $result = $conn->query("SELECT COUNT(*) as total FROM empresa");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        echo "   ✅ Empresas en DB: " . $row['total'] . "\n";
    } else {
        echo "   ❌ Conexión falló (NULL)\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    die();
}

echo "\n4. Prueba de autenticación...\n";

// Crear token de prueba
try {
    $payload = [
        'id_usuario' => 1,
        'correo' => 'maestro@gestorx.test',
        'id_rol' => 1,
        'id_empresa' => null,
        'exp' => time() + 86400
    ];

    $token = JWT::encode($payload);
    echo "   ✅ Token creado: " . substr($token, 0, 50) . "...\n";

    // Simular header
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

    $auth = new \GestorX\Middlewares\AuthMiddleware($conn);
    $user = $auth->validate();

    if ($user) {
        echo "   ✅ Token validado correctamente\n";
        echo "   📊 Usuario: " . print_r($user, true) . "\n";
    } else {
        echo "   ❌ Token no validado\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   📍 Línea: " . $e->getLine() . "\n";
}

echo "\n5. Test directo de listar empresas...\n";

try {
    if ($user && $user['id_rol'] == 1) {
        $query = "
            SELECT 
                e.id_empresa,
                e.nombre_comercial,
                COUNT(u.id_usuario) as total_usuarios,
                e.estado_empresa
            FROM empresa e
            LEFT JOIN usuario u ON e.id_empresa = u.id_empresa
            GROUP BY e.id_empresa, e.nombre_comercial, e.estado_empresa
        ";

        $stmt = $conn->query($query);
        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "   ✅ Empresas encontradas: " . count($empresas) . "\n";
        foreach ($empresas as $emp) {
            echo "      • " . $emp['nombre_comercial'] . " (" . $emp['total_usuarios'] . " usuarios)\n";
        }
    } else {
        echo "   ❌ Usuario no es superadministrador\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Debug completado\n";
