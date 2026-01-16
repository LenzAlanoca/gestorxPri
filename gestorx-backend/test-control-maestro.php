<?php

/**
 * TEST COMPLETO: Control Maestro
 * 1. Login del usuario maestro
 * 2. Generar JWT
 * 3. Validar token con AuthMiddleware
 * 4. Listar empresas
 * 5. Obtener usuarios de una empresa
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/JWT.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/middlewares/AuthMiddleware.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "TEST COMPLETO: CONTROL MAESTRO\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();

    // ===== PASO 1: LOGIN =====
    echo "PASO 1: Login del usuario maestro (Control Maestro)...\n";
    $usuario_model = new Usuario();
    $user = $usuario_model->login('maestro@gestorx.test', 'Maestro@2026');

    if (!$user) {
        throw new Exception('Login fallido para maestro@gestorx.test');
    }

    echo "✅ Login exitoso\n";
    echo "   - ID Usuario: " . $user['id_usuario'] . "\n";
    echo "   - Correo: " . $user['correo'] . "\n";
    echo "   - Nombre: " . $user['nombre'] . " " . $user['apellido'] . "\n";
    echo "   - Rol: " . $user['rol'] . "\n";
    echo "   - ID Empresa: " . ($user['id_empresa'] ?? 'NULL (Control Maestro)') . "\n\n";

    // ===== PASO 2: GENERAR TOKEN =====
    echo "PASO 2: Generando JWT...\n";
    $token = \JWT::encode([
        'id_usuario' => $user['id_usuario'],
        'correo' => $user['correo'],
        'id_empresa' => $user['id_empresa'],
        'id_rol' => $user['id_rol'],
        'nombre_rol' => $user['rol'],
        'iat' => time(),
        'exp' => time() + 86400
    ]);

    echo "✅ Token generado\n";
    echo "   Token (primeros 50 caracteres): " . substr($token, 0, 50) . "...\n\n";

    // ===== PASO 3: VALIDAR TOKEN =====
    echo "PASO 3: Validando token con AuthMiddleware...\n";
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

    $auth = new \GestorX\Middlewares\AuthMiddleware($conn);
    $validated_user = $auth->validate();

    if (!$validated_user) {
        throw new Exception('La validación del token falló');
    }

    echo "✅ Token validado correctamente\n";
    echo "   - ID Usuario: " . $validated_user['id_usuario'] . "\n";
    echo "   - Rol ID: " . $validated_user['id_rol'] . "\n\n";

    // ===== PASO 4: LISTAR EMPRESAS =====
    echo "PASO 4: Listando todas las empresas...\n";
    $query = "SELECT 
                e.id_empresa,
                e.nombre_comercial,
                e.estado_empresa,
                COUNT(u.id_usuario) as total_usuarios
              FROM empresa e
              LEFT JOIN usuario u ON e.id_empresa = u.id_empresa
              GROUP BY e.id_empresa
              ORDER BY e.id_empresa";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $empresas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    if (count($empresas) == 0) {
        throw new Exception('No hay empresas registradas');
    }

    echo "✅ " . count($empresas) . " empresa(s) encontrada(s)\n";
    foreach ($empresas as $emp) {
        echo sprintf(
            "   - ID: %d | Nombre: %-20s | Estado: %-10s | Usuarios: %d\n",
            $emp['id_empresa'],
            $emp['nombre_comercial'],
            $emp['estado_empresa'],
            $emp['total_usuarios']
        );
    }
    echo "\n";

    // ===== PASO 5: OBTENER USUARIOS DE UNA EMPRESA =====
    echo "PASO 5: Obteniendo usuarios de la empresa 1 (GestorX Demo)...\n";
    $query = "SELECT 
                u.id_usuario,
                u.nombre,
                u.apellido,
                u.correo,
                r.nombre_rol,
                u.estado_usuario
              FROM usuario u
              LEFT JOIN rol r ON u.id_rol = r.id_rol
              WHERE u.id_empresa = 1
              ORDER BY u.id_usuario";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    echo "✅ " . count($usuarios) . " usuario(s) encontrado(s)\n";
    foreach ($usuarios as $u) {
        echo sprintf(
            "   - ID: %d | %s %s | Rol: %-20s | Estado: %s\n",
            $u['id_usuario'],
            $u['nombre'],
            $u['apellido'],
            $u['nombre_rol'],
            $u['estado_usuario']
        );
    }
    echo "\n";

    // ===== PASO 6: OBTENER USUARIOS POR ROL =====
    echo "PASO 6: Contando usuarios por rol en empresa 1...\n";
    $query = "SELECT 
                r.nombre_rol,
                COUNT(u.id_usuario) as cantidad
              FROM usuario u
              LEFT JOIN rol r ON u.id_rol = r.id_rol
              WHERE u.id_empresa = 1
              GROUP BY u.id_rol, r.nombre_rol
              ORDER BY r.nombre_rol";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $usuarios_por_rol = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    echo "✅ Distribución de usuarios por rol:\n";
    foreach ($usuarios_por_rol as $dato) {
        echo sprintf(
            "   - %s: %d usuario(s)\n",
            $dato['nombre_rol'],
            $dato['cantidad']
        );
    }

    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ TODAS LAS PRUEBAS PASARON EXITOSAMENTE\n";
    echo str_repeat("=", 80) . "\n\n";
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo str_repeat("=", 80) . "\n\n";
    exit(1);
}
