<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST: RECUPERACIÓN DE CONTRASEÑA ===\n\n";

try {
    // 1. Conectar a BD
    require_once __DIR__ . '/gestorx-backend/config/database.php';
    require_once __DIR__ . '/gestorx-backend/models/RecuperacionPassword.php';

    $database = new Database();
    $conn = $database->getConnection();

    echo "✅ Conexión a BD exitosa\n\n";

    $recuperacion = new RecuperacionPassword($conn);

    // 2. Verificar que tabla existe
    $result = $conn->query("SHOW TABLES LIKE 'recuperacion_password'");
    if ($result->rowCount() > 0) {
        echo "✅ Tabla 'recuperacion_password' existe\n\n";
    } else {
        echo "❌ Tabla no existe. Reinicia el servidor para crear las tablas.\n";
        exit;
    }

    // 3. Test: Crear solicitud
    echo "TEST 1: Crear solicitud de recuperación\n";
    $solicitud = $recuperacion->crearSolicitud(1, 24);

    if ($solicitud['success']) {
        echo "✅ Solicitud creada exitosamente\n";
        echo "   Token: " . substr($solicitud['token'], 0, 20) . "...\n";
        echo "   Expira: " . $solicitud['fecha_expiracion'] . "\n";
        echo "   ID: " . $solicitud['id_recuperacion'] . "\n\n";

        $token = $solicitud['token'];
        $id_recuperacion = $solicitud['id_recuperacion'];
    } else {
        echo "❌ Error: " . $solicitud['error'] . "\n";
        exit;
    }

    // 4. Test: Validar token vigente
    echo "TEST 2: Validar token vigente\n";
    $validacion = $recuperacion->validarToken($token);

    if ($validacion['success']) {
        echo "✅ Token válido\n";
        echo "   Estado: " . $validacion['estado'] . "\n";
        echo "   ID Usuario: " . $validacion['id_usuario'] . "\n";
        echo "   ID Recuperación: " . $validacion['id_recuperacion'] . "\n\n";
    } else {
        echo "❌ Error: " . $validacion['error'] . "\n";
        exit;
    }

    // 5. Test: Marcar como usado
    echo "TEST 3: Marcar token como usado\n";
    $marcado = $recuperacion->marcarUsado($id_recuperacion);

    if ($marcado) {
        echo "✅ Token marcado como usado\n\n";
    } else {
        echo "❌ Error al marcar\n";
        exit;
    }

    // 6. Test: Intentar reutilizar (debe fallar)
    echo "TEST 4: Intentar reutilizar token (debe fallar)\n";
    $revalidacion = $recuperacion->validarToken($token);

    if (!$revalidacion['success']) {
        echo "✅ Token rechazado correctamente\n";
        echo "   Error: " . $revalidacion['error'] . "\n\n";
    } else {
        echo "❌ ERROR: Token se reutilizó (seguridad comprometida!)\n";
        exit;
    }

    // 7. Test: Obtener solicitudes activas
    echo "TEST 5: Obtener solicitudes activas de usuario 1\n";
    $activas = $recuperacion->obtenerSolicitudesActivas(1);

    if ($activas['success']) {
        echo "✅ Consulta exitosa\n";
        echo "   Solicitudes vigentes: " . count($activas['data']) . "\n\n";
    } else {
        echo "❌ Error: " . $activas['error'] . "\n";
        exit;
    }

    // 8. Test: Crear otra solicitud y cancelarla
    echo "TEST 6: Crear y cancelar solicitud\n";
    $solicitud2 = $recuperacion->crearSolicitud(1, 24);

    if ($solicitud2['success']) {
        echo "✅ Segunda solicitud creada\n";

        $cancelada = $recuperacion->cancelarSolicitudes(1);

        if ($cancelada) {
            echo "✅ Todas las solicitudes de usuario 1 canceladas\n\n";
        } else {
            echo "❌ Error al cancelar\n";
        }
    }

    // 9. Resumen
    echo "╔════════════════════════════════════════════╗\n";
    echo "║  ✅ TODOS LOS TESTS PASARON CORRECTAMENTE  ║\n";
    echo "╚════════════════════════════════════════════╝\n";
    echo "\nFuncionalidad lista para implementar módulo de recuperación de contraseña.\n";
    echo "Documentación: RECUPERACION_PASSWORD_DOCUMENTACION.md\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
