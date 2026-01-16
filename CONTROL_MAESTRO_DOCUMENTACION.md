# 🎛️ CENTRO DE CONTROL MAESTRO - DOCUMENTACIÓN COMPLETA

**Fecha:** 15 de enero de 2026  
**Versión:** 1.0  
**Estado:** Implementado

---

## 📋 ÍNDICE

1. [Descripción General](#descripción-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Nuevo Rol: Superadministrador](#nuevo-rol-superadministrador)
4. [Cambios en la Base de Datos](#cambios-en-la-base-de-datos)
5. [Cambios en el Backend](#cambios-en-el-backend)
6. [Cambios en el Frontend](#cambios-en-el-frontend)
7. [Credenciales de Prueba](#credenciales-de-prueba)
8. [Guía de Uso](#guía-de-uso)
9. [Endpoints API](#endpoints-api)

---

## 🎯 Descripción General

El **Centro de Control Maestro** es una interfaz de administración global que permite gestionar todas las empresas del sistema sin pertenecer a ninguna de ellas específicamente.

### Características principales:

✅ **Listar todas las empresas** del sistema
✅ **Ver usuarios** de cada empresa con su rol
✅ **Desactivar/Activar empresas** (soft delete - sin borrar datos)
✅ **Bloquear usuarios** de empresas desactivadas automáticamente
✅ **Estadísticas globales** de empresas y usuarios activos
✅ **Búsqueda y filtrado** por estado de empresa

### Restricción de acceso:

🔒 **Solo Superadministrador** puede acceder (rol = `superadministrador`)
🔒 **No pertenece a ninguna empresa** (id_empresa = NULL)
🔒 **Acceso sin límite de empresas**

---

## 🏗️ Arquitectura del Sistema

### Estructura de Roles (Actualizada)

```
NIVEL 0: CONTROL MAESTRO
├── Rol: superadministrador
│   ├── id_rol = 1
│   ├── Acceso: /control-maestro
│   ├── id_empresa: NULL (sin empresa)
│   └── Permisos: TODOS

NIVEL 1: DENTRO DE EMPRESA
├── Rol: administrador (id_rol = 2)
│   └── Gestiona usuarios y configuración de su empresa
├── Rol: gerente (id_rol = 3) [ELIMINADO - No existe más]
│   └── [YA NO EXISTE]
├── Rol: cajero (id_rol = 4)
│   └── Realiza transacciones de venta
└── Rol: almacenero (id_rol = 5)
    └── Gestiona inventario
```

### Relación Empresa-Usuario

```
┌──────────────────────────────────────┐
│ TABLA: usuario                       │
├──────────────────────────────────────┤
│ id_usuario       INT (PK)            │
│ id_empresa       INT (FK) [NULLABLE] │ ← NULL para Control Maestro
│ id_rol          INT (FK)            │
│ nombre          VARCHAR             │
│ apellido        VARCHAR             │
│ correo          VARCHAR             │
│ password_hash   VARCHAR             │
│ estado_usuario  ENUM (activo/inactivo) │
│ fecha_creacion  DATETIME            │
│ ultimo_acceso   DATETIME            │
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│ TABLA: empresa                       │
├──────────────────────────────────────┤
│ id_empresa                INT (PK)   │
│ estado_empresa ENUM:                 │
│   'activa'      ← Usuarios pueden login
│   'suspendida'  ← Usuarios NO pueden login
│ ...otros campos...                   │
└──────────────────────────────────────┘
```

---

## 🗄️ Cambios en la Base de Datos

### 1. Nuevo Rol: Superadministrador

**Creado en:** `config/Seeder.php` > `seedRoles()`

```sql
INSERT INTO rol (id_rol, nombre_rol, descripcion)
VALUES (1, 'superadministrador', 'Acceso total al sistema');
```

### 2. Nuevos Permisos: Control Maestro

**Creados en:** `config/Seeder.php` > `seedPermisos()`

| Permiso | Módulo | Descripción |
|---------|--------|-------------|
| `listar_empresas` | control_maestro | Listar todas las empresas |
| `ver_usuarios_empresa` | control_maestro | Ver usuarios de cualquier empresa |
| `desactivar_empresa` | control_maestro | Desactivar/Activar empresa |
| `acceso_control_maestro` | control_maestro | Acceso al Control Maestro |

### 3. Asignación de Permisos

**El rol superadministrador recibe TODOS los permisos:**

```php
// En config/Seeder.php > seedRolesPermisos()
$stmt = $this->connection->query("SELECT id_permiso FROM permiso");
$permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($permisos as $id_permiso) {
    $this->connection->prepare(
        "INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (1, :id_permiso)"
    )->execute([':id_permiso' => $id_permiso]);
}
```

### 4. Usuario Control Maestro

**Creado en:** `config/Seeder.php` > `seedEmpresaPrueba()`

| Campo | Valor |
|-------|-------|
| nombre | Control |
| apellido | Maestro |
| correo | `maestro@gestorx.test` |
| password | `Maestro@2026` |
| id_empresa | **NULL** ← No pertenece a empresa |
| id_rol | 1 (superadministrador) |
| estado_usuario | activo |

---

## 🔧 Cambios en el Backend

### 1. Modelo: Usuario.php

**Archivo:** `gestorx-backend/models/Usuario.php`

**Cambios:**
- Modificado método `login()` para soportar `id_empresa = NULL`
- Query cambiada de `INNER JOIN empresa` a `LEFT JOIN empresa`
- Verificación de estado empresa: Solo bloquea si empresa está suspendida
- Permite login a Control Maestro (sin verificar empresa)

```php
// ANTES: Solo empresas
$query = "SELECT u.*, r.nombre_rol, e.nombre_comercial 
          FROM usuario u
          INNER JOIN rol r ON u.id_rol = r.id_rol
          INNER JOIN empresa e ON u.id_empresa = e.id_empresa
          WHERE u.correo = :correo AND u.estado_usuario = 'activo'";

// DESPUÉS: Empresas + Control Maestro
$query = "SELECT u.*, r.nombre_rol, 
                 IF(u.id_empresa IS NULL, 'Control Maestro', e.nombre_comercial) as nombre_comercial,
                 e.estado_empresa
          FROM usuario u
          INNER JOIN rol r ON u.id_rol = r.id_rol
          LEFT JOIN empresa e ON u.id_empresa = e.id_empresa
          WHERE u.correo = :correo AND u.estado_usuario = 'activo'";

// Verifica que empresa esté activa (solo si no es Control Maestro)
if ($row['id_empresa'] !== null && $row['estado_empresa'] !== 'activa') {
    return false;
}
```

### 2. Middleware: AuthMiddleware.php

**Archivo:** `gestorx-backend/middlewares/AuthMiddleware.php`

**Cambios:**
- Convertida a namespace: `GestorX\Middlewares\AuthMiddleware`
- Ahora es clase instanciable (no solo métodos estáticos)
- Método `validate()` para instancias
- Soporta usuarios sin empresa (Control Maestro)
- Mantiene métodos estáticos para compatibilidad

```php
namespace GestorX\Middlewares;

class AuthMiddleware {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function validate() {
        // Retorna datos del usuario o NULL
        // Compatible con Control Maestro (id_empresa = NULL)
    }
}
```

### 3. Nueva API: empresas.php

**Archivo:** `gestorx-backend/api/empresas.php`

**Descripción:** API de Control Maestro para gestionar empresas

**Endpoints:**

| Método | Ruta | Descripción | Require |
|--------|------|-------------|---------|
| GET | `/api/empresas.php` | Listar todas las empresas | Superadmin |
| GET | `/api/empresas.php?id=X` | Obtener empresa específica | Superadmin |
| GET | `/api/empresas.php?usuarios=X` | Listar usuarios de empresa X | Superadmin |
| PUT | `/api/empresas.php?id=X` | Desactivar/Activar empresa | Superadmin |

**Autenticación:** Bearer Token (JWT)

**Validaciones:**
- Solo superadministrador puede acceder
- Si no es autenticado: Error 401
- Si rol ≠ superadministrador: Error 403

**Funciones principales:**

```php
// Listar todas las empresas
listarEmpresas($conn)
  - SELECT con: JOIN plan, COUNT usuarios
  - Retorna: empresas + estadísticas

// Obtener empresa específica
obtenerEmpresa($conn, $id_empresa)
  - Retorna: datos de empresa + plan

// Listar usuarios de empresa
listarUsuariosEmpresa($conn, $id_empresa)
  - Retorna: lista de usuarios + rol + estado

// Desactivar/Activar empresa
desactivarEmpresa($conn, $id_empresa)
  - Alterna: activa <-> suspendida
  - Retorna: nuevo estado
```

---

## 🎨 Cambios en el Frontend

### 1. Nueva Vista: ControlMaestro.vue

**Archivo:** `gestorx/src/views/ControlMaestro.vue`

**Características:**

#### Encabezado y Estadísticas
```
┌─ Título ─────────────────────┐
│ 🎛️ Centro de Control Maestro │
└──────────────────────────────┘

┌─ Estadísticas Globales ──────────────────┐
│ 5 Empresas │ 28 Usuarios │ 1 Suspendida │
└──────────────────────────────────────────┘
```

#### Filtros y Búsqueda
- 🔍 Búsqueda por nombre comercial o razón social
- Filtro por estado: Todas / Activas / Suspendidas

#### Tabla de Empresas
Columnas:
- Empresa (nombre + razón social)
- Plan
- Usuarios (activos/total)
- Estado (✓ Activa / ⊘ Suspendida)
- Suscripción (fecha de expiración)
- Acciones (Ver usuarios / Suspender-Activar)

#### Vista Detallada de Usuarios
Al clickear "Ver usuarios":
```
Tabla de usuarios:
├── Nombre
├── Email
├── Rol
├── Estado
└── Último Acceso
```

#### Modal de Confirmación
Aparece al suspender/activar empresa:
```
⚠️ Confirmar Acción

¿Estás seguro de que deseas SUSPENDER "Empresa X"?
Sus usuarios no podrán hacer login.

[Cancelar] [Confirmar]
```

#### Notificaciones (Toast)
```
✓ Empresa activada    (verde, 3s)
✗ Error al cargar     (rojo, 3s)
```

### 2. Nuevo Layout: MaestroLayout.vue

**Archivo:** `gestorx/src/views/Layout/MaestroLayout.vue`

**Características:**

#### Sidebar
- Logo "🎛️ GestorX" + "CONTROL MAESTRO"
- Avatar del usuario (fondo rojo oscuro)
- Usuario: Nombre + "Administrador del Sistema"
- Menú:
  - 🎛️ Centro de Control
  - 📊 Reportes Globales
  - 📈 Estadísticas
  - ⚙️ Configuración Sistema
- Botón Cerrar Sesión

#### Content Area
- Header con título y breadcrumb
- Router-view para componentes

**Diferencias con AdminLayout:**
- Sidebar fijo (no colapsable en versión actual)
- Menú simplificado (sin rutas hijas complejas)
- Avatar con estilo especial (rojo) para Control Maestro

### 3. Router: router/index.js

**Cambios:**

#### Importaciones
```javascript
const MaestroLayout = () => import('../views/Layout/MaestroLayout.vue')
const ControlMaestro = () => import('../views/ControlMaestro.vue')
```

#### Nueva Ruta
```javascript
{
  path: '/control-maestro',
  component: MaestroLayout,
  meta: { requiresAuth: true, role: ['superadministrador'] },
  children: [
    {
      path: '',
      name: 'ControlMaestro',
      component: ControlMaestro
    }
  ]
}
```

#### Guard actualizado
```javascript
router.beforeEach((to, from, next) => {
  // Superadmin → /control-maestro
  if (user.rol === 'superadministrador') {
    next('/control-maestro')
  }
  // Admin → /admin/usuarios
  else if (user.rol === 'administrador') {
    next('/admin/usuarios')
  }
  // Otros → /user
  else {
    next('/user')
  }
})
```

---

## 🔐 Credenciales de Prueba

### Usuario Control Maestro

| Campo | Valor |
|-------|-------|
| **Email** | `maestro@gestorx.test` |
| **Contraseña** | `Maestro@2026` |
| **Rol** | Superadministrador |
| **Empresa** | Control Maestro (sin empresa específica) |

### Otros usuarios de prueba (en empresa GestorX Demo)

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Administrador | `admin@gestorx.test` | `Admin@2026` |
| Gerente | `gerente@gestorx.test` | `Gerente@2026` |
| Cajero | `cajera@gestorx.test` | `Cajera@2026` |
| Almacén | `almacen@gestorx.test` | `Almacen@2026` |

---

## 📖 Guía de Uso

### Acceder al Control Maestro

1. **Iniciar sesión** en `http://localhost:8081`
2. **Usar credenciales:**
   - Email: `maestro@gestorx.test`
   - Contraseña: `Maestro@2026`
3. **Automáticamente redirige a:** `/control-maestro`

### Listar Empresas

1. El sistema carga automáticamente todas las empresas
2. Se muestran estadísticas globales en tarjetas
3. Tabla con todas las empresas activas y suspendidas

### Buscar/Filtrar Empresas

1. **Búsqueda por nombre:**
   - Ingresa texto en el campo "🔍 Buscar empresa"
   - Filtra en tiempo real por nombre comercial o razón social

2. **Filtro por estado:**
   - "Todas las empresas" → Muestra todas
   - "Activas" → Solo empresas activas
   - "Suspendidas" → Solo empresas suspendidas

### Ver Usuarios de una Empresa

1. Clickear botón **"👥 Ver"** en la fila de la empresa
2. Se expande la sección con tabla de usuarios
3. Ver detalles: Nombre, Email, Rol, Estado, Último Acceso
4. Clickear **"✕"** para cerrar

### Suspender una Empresa

1. Clickear botón **"🔒 Suspender"** (en empresa activa)
2. Aparece modal de confirmación con advertencia
3. Clickear **"Confirmar"**
4. **Efecto:**
   - Empresa pasa a estado: "suspendida"
   - ❌ Sus usuarios **NO pueden hacer login**
   - ❌ Si estaban logueados, pierden sesión
   - Datos de empresa y usuarios se mantienen intactos

### Activar una Empresa

1. Clickear botón **"🔓 Activar"** (en empresa suspendida)
2. Aparece modal de confirmación
3. Clickear **"Confirmar"**
4. **Efecto:**
   - Empresa pasa a estado: "activa"
   - ✓ Sus usuarios pueden hacer login nuevamente
   - Datos se restauran completamente

### Cerrar Sesión

1. Clickear botón **"🚪 Cerrar Sesión"** en sidebar
2. Confirmar en diálogo
3. Redirige a `/login`

---

## 🔌 Endpoints API

### GET /api/empresas.php

**Listar todas las empresas con estadísticas**

**Request:**
```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost/gestorx-backend/api/empresas.php
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id_empresa": 1,
      "nombre_comercial": "GestorX Demo",
      "razon_social": "GestorX SAS",
      "telefono": "(+51) 987654321",
      "correo_contacto": "admin@gestorx.test",
      "estado_empresa": "activa",
      "fecha_registro": "2026-01-14 10:30:00",
      "fecha_expiracion_suscripcion": "2027-01-14",
      "nombre_plan": "Plan Pro",
      "total_usuarios": 5,
      "usuarios_activos": 5
    }
  ],
  "total": 1,
  "timestamp": "2026-01-15 14:30:00"
}
```

### GET /api/empresas.php?id=X

**Obtener empresa específica**

**Request:**
```bash
curl -H "Authorization: Bearer {token}" \
  "http://localhost/gestorx-backend/api/empresas.php?id=1"
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id_empresa": 1,
    "nombre_comercial": "GestorX Demo",
    ...
  },
  "timestamp": "2026-01-15 14:30:00"
}
```

### GET /api/empresas.php?usuarios=X

**Listar usuarios de una empresa**

**Request:**
```bash
curl -H "Authorization: Bearer {token}" \
  "http://localhost/gestorx-backend/api/empresas.php?usuarios=1"
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id_usuario": 2,
      "nombre": "Admin",
      "apellido": "GestorX",
      "correo": "admin@gestorx.test",
      "estado_usuario": "activo",
      "ultimo_acceso": "2026-01-15 10:00:00",
      "fecha_creacion": "2026-01-14 10:30:00",
      "nombre_rol": "administrador",
      "empresa": "GestorX Demo"
    }
  ],
  "total": 5,
  "timestamp": "2026-01-15 14:30:00"
}
```

### PUT /api/empresas.php?id=X

**Desactivar/Activar empresa**

**Request:**
```bash
curl -X PUT \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  http://localhost/gestorx-backend/api/empresas.php?id=1
```

**Response (200):**
```json
{
  "success": true,
  "message": "Empresa suspendida",
  "id_empresa": 1,
  "estado_nuevo": "suspendida",
  "timestamp": "2026-01-15 14:30:00"
}
```

**Error Responses:**

| Código | Mensaje | Causa |
|--------|---------|-------|
| 401 | No autorizado | Token no válido |
| 403 | Solo superadministrador | Rol insuficiente |
| 404 | Empresa no encontrada | ID no existe |
| 500 | Error interno | Excepción del servidor |

---

## 📊 Flujo de Login

```
┌─────────────────────────────────────────┐
│ Usuario ingresa credenciales            │
│ maestro@gestorx.test / Maestro@2026     │
└──────────────────┬──────────────────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │ POST /api/auth.php   │
        │ action: login        │
        └──────────────────┬───┘
                           │
                           ▼
        ┌──────────────────────────────┐
        │ Usuario.login($correo, $pwd) │
        └──────────────────┬───────────┘
                           │
         ┌─────────────────┴─────────────────┐
         │                                   │
         ▼ (Control Maestro)                 ▼ (Empresa)
    ┌────────────────┐             ┌─────────────────┐
    │ id_empresa NULL│             │ id_empresa != NULL
    │ id_rol = 1     │             │ Verifica:
    │ ✓ PERMITE      │             │ empresa.estado =
    │                │             │ 'activa'
    └────────┬───────┘             │ ✓ PERMITE
             │                      │ ✗ DENIEGA
             │                      └────────┬─────┘
             └──────────────┬────────────────┘
                            │
                            ▼
        ┌──────────────────────────────┐
        │ JWT::encode({...user data})  │
        │ Genera token                 │
        └──────────────────┬───────────┘
                           │
                           ▼
        ┌──────────────────────────────┐
        │ Response: token + user info  │
        │ localStorage.setItem('token')│
        │ localStorage.setItem('user') │
        └──────────────────┬───────────┘
                           │
                           ▼
        ┌──────────────────────────────┐
        │ router.beforeEach() guard    │
        │ Verifica role                │
        └──────────────────┬───────────┘
                           │
         ┌─────────────────┴─────────────────┐
         │                                   │
    role='super...'              role='admin'
         │                             │
         ▼                             ▼
   /control-maestro           /admin/usuarios
         │                             │
         └──────────┬──────────────────┘
                    │
                    ▼
        Dashboard correspondiente
```

---

## 🧪 Pruebas Sugeridas

### Test 1: Login Control Maestro
```
1. Ir a http://localhost:8081/login
2. Email: maestro@gestorx.test
3. Password: Maestro@2026
4. Verificar: Redirige a /control-maestro
5. Verificar: Muestra sidebar "CONTROL MAESTRO"
```

### Test 2: Listar Empresas
```
1. En Control Maestro, verificar carga de empresas
2. Verificar estadísticas globales correctas
3. Verificar tabla con todas las empresas
4. Contar: Total usuarios por empresa
```

### Test 3: Buscar/Filtrar
```
1. Ingresar texto en búsqueda
2. Verificar filtrado en tiempo real
3. Cambiar filtro de estado
4. Verificar resultados correctos
```

### Test 4: Suspender Empresa
```
1. Clickear "Suspender" en empresa activa
2. Verificar modal de confirmación
3. Clickear "Confirmar"
4. Verificar estado cambió a "suspendida"
5. Cerrar sesión

Prueba de bloqueo:
6. Intentar login con usuario de empresa suspendida
7. Verificar: Error "Credenciales incorrectas"
```

### Test 5: Ver Usuarios
```
1. Clickear "Ver" en empresa
2. Verificar tabla con usuarios
3. Verificar columnas: Nombre, Email, Rol, Estado, Último Acceso
4. Clickear "✕" para cerrar
```

### Test 6: Activar Empresa
```
1. Clickear "Activar" en empresa suspendida
2. Verificar modal de confirmación
3. Clickear "Confirmar"
4. Verificar estado cambió a "activa"
5. Usuarios pueden hacer login nuevamente
```

---

## 📁 Archivos Modificados/Creados

### Backend

| Archivo | Tipo | Cambios |
|---------|------|---------|
| `config/Seeder.php` | Modificado | Nuevos permisos, rol, usuario Control Maestro |
| `config/Initializer.php` | Sin cambios | Ejecuta Seeder automáticamente |
| `models/Usuario.php` | Modificado | Soporta id_empresa NULL |
| `middlewares/AuthMiddleware.php` | Modificado | Namespace, clase instanciable, soporta NULL |
| `api/empresas.php` | **NUEVO** | API de Control Maestro |

### Frontend

| Archivo | Tipo | Cambios |
|---------|------|---------|
| `src/views/ControlMaestro.vue` | **NUEVO** | Vista principal del Control Maestro |
| `src/views/Layout/MaestroLayout.vue` | **NUEVO** | Layout para Control Maestro |
| `src/router/index.js` | Modificado | Nuevas rutas y guard actualizado |

---

## 🚀 Próximas Mejoras Sugeridas

1. **Reportes Globales:** Gráficos de empresas, usuarios, ventas
2. **Auditoría:** Log de quién suspendió/activó empresas
3. **Gestión de Planes:** Cambiar plans de empresas desde Control Maestro
4. **Creación de Empresas:** Crear nuevas empresas desde aquí
5. **Gestión de Roles Globales:** Administrar roles del sistema
6. **Respaldos:** Crear backups de empresas
7. **Estadísticas Avanzadas:** Ingresos, clientes, productos por empresa

---

## ✅ Checklist de Implementación

- [x] Crear rol superadministrador
- [x] Crear permisos de Control Maestro
- [x] Crear usuario Control Maestro en BD
- [x] Modificar modelo Usuario para soportar id_empresa NULL
- [x] Modificar AuthMiddleware
- [x] Crear API empresas.php
- [x] Crear vista ControlMaestro.vue
- [x] Crear layout MaestroLayout.vue
- [x] Actualizar router con ruta /control-maestro
- [x] Actualizar guard de router
- [x] Documentación completa
- [ ] Testing manual en navegador
- [ ] Optimización de queries
- [ ] Caché de datos

---

**Documento creado:** 15 de enero de 2026  
**Versión:** 1.0  
**Estado:** ✅ LISTO PARA USAR
