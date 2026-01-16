# ⚡ RESUMEN TÉCNICO RÁPIDO - CONTROL MAESTRO

## 🎯 ¿Qué se implementó?

Centro de administración global para gestionar todas las empresas del sistema sin pertenecer a ninguna.

---

## 📂 Archivos Nuevos

```
✨ NUEVOS:
├── gestorx-backend/api/empresas.php          (API endpoints)
├── gestorx/src/views/ControlMaestro.vue      (Vista principal)
├── gestorx/src/views/Layout/MaestroLayout.vue (Layout)
└── CONTROL_MAESTRO_DOCUMENTACION.md          (Documentación)
```

---

## 📝 Archivos Modificados

```
🔄 MODIFICADOS:
├── gestorx-backend/config/Seeder.php         (Nuevos datos de prueba)
├── gestorx-backend/models/Usuario.php        (Soporta id_empresa NULL)
├── gestorx-backend/middlewares/AuthMiddleware.php (Namespace + mejoras)
└── gestorx/src/router/index.js               (Nuevas rutas)
```

---

## 🔐 Credenciales Control Maestro

| Campo | Valor |
|-------|-------|
| Email | `maestro@gestorx.test` |
| Password | `Maestro@2026` |

**→ Login en:** `http://localhost:8081`

---

## 🌐 Acceso

**URL:** `http://localhost:8081/control-maestro`

**Disponible para:** Solo Superadministrador

---

## 💾 Cambios BD

### Datos nuevos en Seeder:

1. **Permisos Control Maestro** (4 nuevos)
   - listar_empresas
   - ver_usuarios_empresa
   - desactivar_empresa
   - acceso_control_maestro

2. **Usuario Control Maestro**
   - Email: maestro@gestorx.test
   - Rol: superadministrador (id_rol=1)
   - id_empresa: NULL ← Sin empresa específica

### Cambios en tablas:

- `usuario.id_empresa` → Ahora puede ser NULL
- `empresa.estado_empresa` → Controla si usuarios pueden login

---

## 🔌 API Endpoints

### GET /api/empresas.php
Listar todas las empresas + estadísticas

```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost/gestorx-backend/api/empresas.php
```

### GET /api/empresas.php?usuarios=1
Ver usuarios de empresa 1

```bash
curl -H "Authorization: Bearer {token}" \
  "http://localhost/gestorx-backend/api/empresas.php?usuarios=1"
```

### PUT /api/empresas.php?id=1
Suspender/Activar empresa 1

```bash
curl -X PUT -H "Authorization: Bearer {token}" \
  http://localhost/gestorx-backend/api/empresas.php?id=1
```

---

## 🎨 Componentes Vue

### ControlMaestro.vue
- Tabla de empresas
- Filtros y búsqueda
- Estadísticas globales
- Modal de confirmación
- Vista detallada de usuarios
- Notificaciones (toast)

### MaestroLayout.vue
- Sidebar con menú
- Header
- Layout similar a AdminLayout
- Estilos especiales (rojo oscuro para Maestro)

---

## 🛡️ Seguridad

✅ Solo Superadministrador puede acceder
✅ Verifica token JWT en cada request
✅ Empresas suspendidas bloquean login de sus usuarios
✅ Datos no se borran (soft delete)

---

## ✅ Pasos para Activar

1. **XAMPP encendido:** Apache + MySQL
2. **Ejecutar:** `http://localhost/GestorX/gestorx-backend/init.php`
3. **npm run serve** en carpeta `gestorx`
4. **Login:** `maestro@gestorx.test` / `Maestro@2026`

---

## 🚀 Funcionalidades

| Función | Descripción |
|---------|------------|
| Listar empresas | Todas las empresas + estadísticas |
| Buscar empresa | Por nombre o razón social |
| Filtrar estado | Activas/Suspendidas |
| Ver usuarios | Tabla con usuarios por empresa |
| Suspender empresa | Bloquea usuarios - soft delete |
| Activar empresa | Desbloquea usuarios |
| Estadísticas | Total empresas, usuarios, suspendidas |

---

## 📊 Estructura de Datos

```
USUARIO (Control Maestro)
├── id_empresa: NULL           ← Sin empresa
├── id_rol: 1                  ← Superadministrador
├── correo: maestro@...
└── password: Maestro@2026

USUARIO (Dentro de empresa)
├── id_empresa: 1              ← Específica
├── id_rol: 2,3,4,5            ← Admin/Gerente/Cajero/Almacén
├── correo: admin@...
└── password: Admin@2026
```

---

## 🔄 Flujo Login Empresa Suspendida

```
Usuario intenta login
        ↓
Valida credenciales ✓
        ↓
Verifica estado_empresa
        ↓
estado = 'suspendida' ✗
        ↓
return false (credenciales incorrectas)
        ↓
❌ Login denegado
```

---

## 🧪 Test Rápido

1. **Login Maestro:**
   - Email: maestro@gestorx.test
   - Pass: Maestro@2026

2. **Deberías ver:**
   - Sidebar "CONTROL MAESTRO" (rojo)
   - Tabla con empresas
   - Estadísticas globales

3. **Prueba suspender:**
   - Click "Suspender" en empresa
   - Intenta login con usuario de esa empresa
   - ❌ Debe fallar

4. **Prueba activar:**
   - Click "Activar"
   - Login con usuario de esa empresa
   - ✓ Debe funcionar

---

## 📞 Resumen de Cambios

| Aspecto | Antes | Después |
|--------|-------|---------|
| Roles | Admin/Gerente/Cajero/Almacén | + Superadministrador |
| Login | Solo empresas | + Sin empresa (Maestro) |
| Usuarios | id_empresa obligatorio | Puede ser NULL |
| Vistas | Admin layout | + Maestro layout |
| Rutas | /admin/usuarios | + /control-maestro |
| API | usuarios.php, roles.php | + empresas.php |

---

**Versión:** 1.0  
**Fecha:** 15-01-2026  
**Estado:** ✅ IMPLEMENTADO
