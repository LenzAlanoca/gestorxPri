# INSTRUCCIONES PARA PROBAR CONTROL MAESTRO

## ✅ CAMBIOS REALIZADOS

### 1. Correcciones en Backend (PHP)

#### AuthMiddleware.php
- ✅ Removida la importación de namespace incorrecto `use GestorX\Helpers\JWT`
- ✅ Actualizado todas las referencias a `\JWT::decode()` (con backslash para namespace global)
- ✅ Mejorado el método `getHeaders()` para ser compatible con CLI y múltiples formas de obtener headers
- ✅ Agregado soporte para `$_SERVER['HTTP_AUTHORIZATION']`

#### api/auth.php
- ✅ Agregada conversión de `id_empresa` vacío a `null` en la respuesta JSON
- ✅ Agregado `JSON_UNESCAPED_UNICODE` para mejor formato JSON

#### config/Seeder.php
- ✅ Creado usuario Control Maestro: `maestro@gestorx.test / Maestro@2026`
- ✅ Usuario sin empresa (id_empresa = NULL)
- ✅ Rol: superadministrador (id_rol = 1)

### 2. Cambios en Frontend (Vue.js)

#### views/Login.vue
- ✅ Mejorada la lógica de redirección post-login
- ✅ Verificar si user.id_empresa es NULL → redirigir a `/control-maestro`
- ✅ Si user.id_empresa existe → redirigir a `/admin/usuarios`

#### router/index.js
- ✅ Actualizado el guard para detectar Control Maestro (superadministrador SIN empresa)
- ✅ Redireccionamiento inteligente según rol y empresa

#### views/ControlMaestro.vue
- ✅ Vista completa para listar empresas
- ✅ Estadísticas globales (total empresas, usuarios, suspendidas)
- ✅ Tabla de empresas con filtrado y búsqueda
- ✅ Botones para suspender/activar empresas
- ✅ Modal de confirmación
- ✅ Vista detallada de usuarios por empresa

#### views/Layout/MaestroLayout.vue
- ✅ Layout especial para Control Maestro
- ✅ Sidebar con navegación
- ✅ Header con información del usuario

### 3. API Endpoints

#### /gestorx-backend/api/empresas.php
- ✅ GET /api/empresas.php → Listar todas las empresas
- ✅ GET /api/empresas.php?id=X → Obtener empresa específica
- ✅ GET /api/empresas.php?usuarios=X → Listar usuarios de una empresa
- ✅ PUT /api/empresas.php?id=X → Desactivar/Activar empresa

## 🚀 CÓMO PROBAR

### Requisitos
- XAMPP con Apache y MySQL ejecutándose
- Node.js instalado

### Paso 1: Iniciar servidor PHP/API
```bash
# Ya debería estar corriendo en XAMPP
http://localhost/gestorx-backend/
```

### Paso 2: Iniciar servidor Vue.js (Frontend)
```bash
cd C:\xampp\htdocs\GestorX\gestorx
npm install  # Solo si no está hecho
npm run serve
```

El servidor estará en: `http://localhost:8082`

### Paso 3: Iniciar sesión como Control Maestro
1. Ir a `http://localhost:8082/login`
2. Ingresar credenciales:
   - **Email**: maestro@gestorx.test
   - **Contraseña**: Maestro@2026
3. Deberá redirigirse automáticamente a `/control-maestro`

### Paso 4: Usar el Centro de Control Maestro
- Ver todas las empresas registradas
- Ver estadísticas (empresas activas, suspendidas, usuarios)
- Buscar empresas por nombre
- Filtrar por estado (activas/suspendidas)
- Hacer clic en "Ver" para listar usuarios de una empresa
- Hacer clic en "Suspender" o "Activar" para cambiar estado

### Paso 5: Probar que usuarios de empresa suspendida NO pueden loginear
1. Suspender la empresa "GestorX Demo" desde Control Maestro
2. Intentar loginear con `admin@gestorx.test`
3. Debería mostrar error "Credenciales incorrectas" (empresa suspendida)

## 📋 USUARIOS DE PRUEBA

### Control Maestro (Superadministrador sin empresa)
- Email: `maestro@gestorx.test`
- Contraseña: `Maestro@2026`
- Rol: superadministrador
- Empresa: NINGUNA (NULL)
- Acceso: `/control-maestro`

### Empresa 1: GestorX Demo
- Nombre Comercial: GestorX Demo
- Razón Social: GestorX SAS
- Estado: activa
- Usuarios:
  - **admin@gestorx.test** / Admin@2026 (Superadministrador)
  - **gerente@gestorx.test** / Gerente@2026 (Gerente)
  - **cajera@gestorx.test** / Cajera@2026 (Cajero)
  - **almacen@gestorx.test** / Almacen@2026 (Almacenero)

### Empresa 2: Empresa1
- Nombre Comercial: Empresa1
- Estado: activa
- Usuarios: 1 (usuario admin)

## 🔐 SEGURIDAD

✅ Middleware valida JWT en cada request
✅ Solo superadministrador SIN empresa puede acceder a `/control-maestro`
✅ Verificación de estado de empresa antes de permitir login
✅ Token JWT con expiración de 24 horas
✅ Soft delete (suspensión) de empresas, no eliminación permanente

## 🐛 TROUBLESHOOTING

Si recibe error "No autorizado" al acceder a empresas.php:
1. Verificar que el token está siendo enviado en header `Authorization: Bearer <token>`
2. Verificar que el usuario es superadministrador (id_rol = 1)
3. Revisar la consola del navegador (F12 → Network) para ver los requests

Si el redireccionamiento no funciona después del login:
1. Limpiar localStorage: `localStorage.clear()` en la consola del navegador
2. Limpiar cookies del navegador
3. Recargar la página

Si el Control Maestro no carga empresas:
1. Verificar que el endpoint `/gestorx-backend/api/empresas.php` está accesible
2. Verificar permisos en nginx/Apache
3. Revisar logs de Apache/PHP
