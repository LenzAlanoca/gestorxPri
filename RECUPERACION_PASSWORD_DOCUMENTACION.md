# 🔐 Recuperación de Contraseña - Documentación Técnica

**Fecha:** 16 de enero de 2026  
**Estado:** ✅ Backend Preparado (sin frontend aún)  
**Módulo:** Futuro - Recuperación de Contraseñas

---

## 📋 Descripción General

Se ha implementado la infraestructura backend para el módulo de recuperación de contraseñas. El sistema está **completamente funcional pero sin interfaz frontend** para que puedas implementar la UI cuando lo necesites.

---

## 🗄️ Tabla en Base de Datos

### `recuperacion_password`

```sql
CREATE TABLE recuperacion_password (
  id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion DATETIME NOT NULL,
  estado_token ENUM('vigente','usado','expirado') DEFAULT 'vigente',
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_usuario (id_usuario),
  INDEX idx_estado (estado_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Campos:
- **id_recuperacion**: Identificador único
- **id_usuario**: Referencia al usuario que solicita recuperación
- **token**: Token único de 64 caracteres (hex) - válido 24 horas
- **fecha_creacion**: Cuándo se creó la solicitud
- **fecha_expiracion**: Cuándo expira el token
- **estado_token**: 'vigente' | 'usado' | 'expirado'

### Índices:
- `idx_token`: Búsqueda rápida por token
- `idx_usuario`: Búsqueda de solicitudes por usuario
- `idx_estado`: Filtrado por estado

---

## 🔧 Modelo PHP: RecuperacionPassword

**Ubicación:** `gestorx-backend/models/RecuperacionPassword.php`

### Métodos disponibles:

#### 1. `crearSolicitud($id_usuario, $expiracion_horas = 24)`

Crea una nueva solicitud de recuperación de contraseña.

```php
$recuperacion = new RecuperacionPassword($conn);
$resultado = $recuperacion->crearSolicitud(5, 24);

// Respuesta:
[
    'success' => true,
    'token' => 'a1b2c3d4e5f6...', // 64 caracteres
    'fecha_expiracion' => '2026-01-17 14:30:00',
    'id_recuperacion' => 1
]
```

**Parámetros:**
- `$id_usuario` (int): ID del usuario
- `$expiracion_horas` (int, default 24): Horas hasta expiración

**Retorna:** Array con token y fecha de expiración

---

#### 2. `validarToken($token)`

Valida un token y verifica su estado.

```php
$resultado = $recuperacion->validarToken('a1b2c3d4e5f6...');

// Respuesta (éxito):
[
    'success' => true,
    'id_recuperacion' => 1,
    'id_usuario' => 5,
    'estado' => 'vigente'
]

// Respuesta (error - expirado):
[
    'success' => false,
    'error' => 'El token ha expirado'
]
```

**Verifica:**
- ✅ Que el token existe
- ✅ Que no ha sido usado
- ✅ Que no ha expirado
- ✅ Marca automáticamente como expirado si pasó la fecha

**Retorna:** Array con ID de recuperación e ID de usuario, o error

---

#### 3. `marcarUsado($id_recuperacion)`

Marca un token como usado (después de resetear contraseña).

```php
$recuperacion->marcarUsado(1);
// true o false
```

**Parámetros:**
- `$id_recuperacion` (int): ID de la recuperación

**Retorna:** bool

---

#### 4. `obtenerSolicitudesActivas($id_usuario)`

Obtiene todas las solicitudes de recuperación vigentes de un usuario.

```php
$resultado = $recuperacion->obtenerSolicitudesActivas(5);

// Respuesta:
[
    'success' => true,
    'data' => [
        [
            'id_recuperacion' => 1,
            'fecha_creacion' => '2026-01-16 14:30:00',
            'fecha_expiracion' => '2026-01-17 14:30:00',
            'estado_token' => 'vigente'
        ]
    ]
]
```

**Retorna:** Array con lista de solicitudes activas

---

#### 5. `cancelarSolicitudes($id_usuario)`

Cancela todas las solicitudes vigentes de un usuario (ej: cuando recuerda su contraseña).

```php
$recuperacion->cancelarSolicitudes(5);
// true o false
```

**Parámetros:**
- `$id_usuario` (int): ID del usuario

**Retorna:** bool

---

#### 6. `limpiarTokenosAntiguos($dias_antiguedad = 30)`

Limpia tokens expirados o usados más antiguos que X días. Útil para mantenimiento de BD.

```php
$registros_eliminados = $recuperacion->limpiarTokenosAntiguos(30);
// 15 (eliminó 15 registros)
```

**Parámetros:**
- `$dias_antiguedad` (int, default 30): Elimina tokens más antiguos

**Retorna:** int (cantidad de registros eliminados)

---

## 🔌 Endpoints API (Futuros)

Cuando implementes la UI, crea estos endpoints:

### 1. **POST /api/recuperacion/solicitar**
Solicitar recuperación de contraseña

```
Body:
{
    "correo": "usuario@ejemplo.com"
}

Response (200):
{
    "success": true,
    "message": "Se envió un email con instrucciones"
}

Response (404):
{
    "success": false,
    "error": "Usuario no encontrado"
}
```

**Lógica:**
1. Buscar usuario por correo
2. Crear solicitud con `crearSolicitud()`
3. Enviar email con link: `https://tuapp.com/reset-password?token=XXX`

---

### 2. **POST /api/recuperacion/validar**
Validar token antes de mostrar formulario de reset

```
Body:
{
    "token": "a1b2c3d4e5f6..."
}

Response (200):
{
    "success": true,
    "id_usuario": 5
}

Response (400):
{
    "success": false,
    "error": "Token expirado"
}
```

**Lógica:**
1. Llamar a `validarToken()`
2. Si es válido, permitir cambio de contraseña

---

### 3. **POST /api/recuperacion/resetear**
Resetear contraseña con token válido

```
Body:
{
    "token": "a1b2c3d4e5f6...",
    "nueva_password": "NuevaPass123!"
}

Response (200):
{
    "success": true,
    "message": "Contraseña cambiada exitosamente"
}

Response (400):
{
    "success": false,
    "error": "Token inválido"
}
```

**Lógica:**
1. Validar token con `validarToken()`
2. Actualizar contraseña en tabla `usuario`
3. Marcar como usado con `marcarUsado()`
4. Opcionalmente cancelar todas las otras solicitudes

---

## 📧 Envío de Emails

Cuando implementes endpoints, necesitarás enviar emails. Recomendación:

```php
// Usar librería: composer require phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->Host = 'smtp.gmail.com'; // Configurar servidor
$mail->setFrom('noreply@tuapp.com', 'GestorX');
$mail->addAddress('usuario@ejemplo.com');
$mail->Subject = 'Recupera tu contraseña';
$mail->Body = "Haz clic aquí para resetear: https://tuapp.com/reset?token=$token";
$mail->send();
```

---

## 🛡️ Seguridad

✅ **Token seguro:** 32 bytes de datos aleatorios = 64 caracteres hex  
✅ **Expiración:** 24 horas por defecto (configurable)  
✅ **Uso único:** Token se marca como usado después de resetear  
✅ **Foreign key:** Si usuario se elimina, sus tokens se borran  
✅ **Índices:** Búsquedas rápidas sin N+1 queries  

---

## 🧪 Pruebas (CLI)

Cuando quieras testear sin frontend:

```php
<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/RecuperacionPassword.php';

$database = new Database();
$conn = $database->getConnection();

$recuperacion = new RecuperacionPassword($conn);

// 1. Crear solicitud
$solicitud = $recuperacion->crearSolicitud(1, 24);
echo "Token: " . $solicitud['token'] . "\n";

// 2. Validar token
$validacion = $recuperacion->validarToken($solicitud['token']);
echo "Válido: " . ($validacion['success'] ? 'SÍ' : 'NO') . "\n";

// 3. Marcar como usado
$recuperacion->marcarUsado($validacion['id_recuperacion']);

// 4. Intentar reutilizar (debería fallar)
$revalidacion = $recuperacion->validarToken($solicitud['token']);
echo "Token reusable: " . ($revalidacion['success'] ? 'SÍ (ERROR!)' : 'NO (correcto)') . "\n";
?>
```

---

## 📊 Flujo de Recuperación Completo

```
1. Usuario hace clic en "¿Olvidaste contraseña?"
   ↓
2. Ingresa su correo
   ↓
3. Sistema busca usuario por correo
   ↓
4. Si existe → crearSolicitud() → Genera token de 24 horas
   ↓
5. Se envía email con link: /reset-password?token=XXX
   ↓
6. Usuario recibe email y hace clic en link
   ↓
7. Frontend valida token → validarToken()
   ↓
8. Si token es válido, muestra formulario de nueva contraseña
   ↓
9. Usuario ingresa nueva contraseña
   ↓
10. Backend resetea contraseña + marcarUsado()
    ↓
11. Sistema cancela todas las otras solicitudes (opcional)
    ↓
12. Éxito: "Contraseña actualizada"
```

---

## 🔮 Próximos Pasos

Cuando tengas UI lista, necesitarás:

1. **Crear formulario "Olvidé contraseña"**
2. **Implementar endpoints API** (3 mencionados arriba)
3. **Integrar librería de emails** (PHPMailer, SwiftMailer, etc.)
4. **Crear vista de reset de contraseña**
5. **Agregar validaciones de fortaleza de contraseña**
6. **Crear tarea programada** para limpiar tokens antiguos
7. **Agregar logging** de intentos fallidos

---

## 🚀 Instalación

La tabla se crea automáticamente al inicializar la BD. **No necesitas hacer nada** - cuando reinicies el servidor, la tabla aparecerá.

Verifica en phpmyadmin:
```
Base de datos: gestorxbd
Tabla: recuperacion_password
```

---

## 📞 Soporte

Si en el futuro necesitas:
- Cambiar duración de expiración: Parámetro `$expiracion_horas`
- Cambiar seguridad del token: Modificar `bin2hex(random_bytes(32))`
- Agregar más campos: Alterar tabla y modelo

El código está preparado para ser expandido sin romper lo existente. ✅

---

**Documento creado:** 16-01-2026  
**Estado:** Backend 100% funcional, UI Pendiente  
**Compatibilidad:** No afecta módulos existentes
