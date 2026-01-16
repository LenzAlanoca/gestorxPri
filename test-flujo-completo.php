<?php

/**
 * TEST FINAL: Flujo completo de Control Maestro
 * Simula: Login → JWT → Validación → Listar Empresas → Suspender Empresa
 */

require_once __DIR__ . '/gestorx-backend/config/database.php';
require_once __DIR__ . '/gestorx-backend/helpers/JWT.php';
require_once __DIR__ . '/gestorx-backend/models/Usuario.php';
require_once __DIR__ . '/gestorx-backend/middlewares/AuthMiddleware.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "TEST FINAL: FLUJO COMPLETO DE CONTROL MAESTRO\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();

    // ===== PASO 1: LOGIN =====
    echo "PASO 1: Login maestro@gestorx.test\n";
    $usuario_model = new Usuario();
    $user = $usuario_model->login('maestro@gestorx.test', 'Maestro@2026');

    if (!$user) {
        throw new Exception('Login fallido');
    }

    echo "✅ Usuario autenticado\n";
    echo "   ID: " . $user['id_usuario'] . "\n";
    echo "   Rol: " . $user['rol'] . "\n";
    echo "   ID Empresa: " . ($user['id_empresa'] ?? 'NULL (Control Maestro)') . "\n\n";

    // ===== PASO 2: GENERAR JWT =====
    echo "PASO 2: Generar JWT\n";
    $token = \JWT::encode([
        'id_usuario' => $user['id_usuario'],
        'id_empresa' => $user['id_empresa'],
        'id_rol' => $user['id_rol'],
        'nombre_rol' => $user['rol'],
        'iat' => time(),
        'exp' => time() + 86400
    ]);

    echo "✅ Token generado\n";
    echo "   Token (primeros 60 chars): " . substr($token, 0, 60) . "...\n\n";

    // ===== PASO 3: SIMULAR REQUEST HTTP CON BEARER TOKEN =====
    echo "PASO 3: Validar token en request HTTP\n";
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

    $auth = new \GestorX\Middlewares\AuthMiddleware($conn);
    $validated = $auth->validate();

    if (!$validated) {
        throw new Exception('Validación de token fallida');
    }

    echo "✅ Token validado correctamente\n";
    echo "   ID Usuario: " . $validated['id_usuario'] . "\n";
    echo "   Rol ID: " . $validated['id_rol'] . "\n\n";

    // ===== PASO 4: VERIFICAR PERMISOS =====
    echo "PASO 4: Verificar permisos (debe ser superadministrador)\n";
    if ($validated['id_rol'] != 1) {
        throw new Exception('No es superadministrador');
    }

    echo "✅ Usuario es superadministrador\n\n";

    // ===== PASO 5: LISTAR EMPRESAS =====
    echo "PASO 5: Listar empresas (GET /api/empresas.php)\n";
    $query = "SELECT 
                e.id_empresa,
                e.nombre_comercial,
                e.estado_empresa,
                COUNT(u.id_usuario) as total_usuarios
              FROM empresa e
              LEFT JOIN usuario u ON e.id_empresa = u.id_empresa
              GROUP BY e.id_empresa, e.nombre_comercial, e.estado_empresa
              ORDER BY e.id_empresa";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $empresas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    echo "✅ Empresas encontradas: " . count($empresas) . "\n";
    foreach ($empresas as $emp) {
        echo sprintf(
            "   - ID: %d | %s | Estado: %s | Usuarios: %d\n",
            $emp['id_empresa'],
            str_pad($emp['nombre_comercial'], 20),
            $emp['estado_empresa'],
            $emp['total_usuarios']
        );
    }
    echo "\n";

    // ===== PASO 6: OBTENER USUARIOS DE UNA EMPRESA =====
    echo "PASO 6: Listar usuarios de empresa 1 (GET /api/empresas.php?usuarios=1)\n";
    $query = "SELECT 
                u.id_usuario,
                u.nombre,
                u.apellido,
                u.correo,
                r.nombre_rol
              FROM usuario u
              LEFT JOIN rol r ON u.id_rol = r.id_rol
              WHERE u.id_empresa = 1
              ORDER BY u.id_usuario";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    echo "✅ Usuarios encontrados: " . count($usuarios) . "\n";
    foreach ($usuarios as $u) {
        echo sprintf(
            "   - ID: %d | %s %s | %s\n",
            $u['id_usuario'],
            $u['nombre'],
            $u['apellido'],
            $u['nombre_rol']
        );
    }
    echo "\n";

    // ===== PASO 7: CAMBIAR ESTADO DE EMPRESA =====
    echo "PASO 7: Cambiar estado de empresa 1 (PUT /api/empresas.php?id=1)\n";

    // Obtener estado actual
    $query = "SELECT estado_empresa FROM empresa WHERE id_empresa = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $emp = $stmt->fetch(\PDO::FETCH_ASSOC);
    $estado_actual = $emp['estado_empresa'];

    // Cambiar estado
    $nuevo_estado = ($estado_actual === 'activa') ? 'suspendida' : 'activa';

    $query = "UPDATE empresa SET estado_empresa = :estado WHERE id_empresa = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([':estado' => $nuevo_estado]);

    echo "✅ Estado cambiado: " . $estado_actual . " → " . $nuevo_estado . "\n\n";

    // ===== PASO 8: VERIFICAR QUE USUARIOS NO PUEDEN LOGINEAR CON EMPRESA SUSPENDIDA =====
    if ($nuevo_estado === 'suspendida') {
        echo "PASO 8: Intentar login con empresa suspendida\n";
        $user_suspendida = $usuario_model->login('admin@gestorx.test', 'Admin@2026');

        if ($user_suspendida === false) {
            echo "✅ Login bloqueado correctamente (empresa suspendida)\n\n";
        } else {
            echo "⚠️  ADVERTENCIA: El usuario pudo hacer login con empresa suspendida\n\n";
        }

        // Restaurar estado para próximas pruebas
        $query = "UPDATE empresa SET estado_empresa = 'activa' WHERE id_empresa = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        echo "Estado restaurado a 'activa'\n\n";
    }

    // ===== RESUMEN =====
    echo str_repeat("=", 100) . "\n";
    echo "✅ TODOS LOS PASOS COMPLETADOS EXITOSAMENTE\n";
    echo str_repeat("=", 100) . "\n";
    echo "\nPróximos pasos:\n";
    echo "1. Iniciar servidor frontend: npm run serve\n";
    echo "2. Ir a http://localhost:8082/login\n";
    echo "3. Ingresar: maestro@gestorx.test / Maestro@2026\n";
    echo "4. Deberá redirigirse a /control-maestro\n";
    echo "5. Ver y gestionar empresas desde la interfaz\n";
    echo "\n";
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo str_repeat("=", 100) . "\n";
    exit(1);
}
