# 🎛️ INICIO RÁPIDO - CONTROL MAESTRO

## Paso 1: Asegurar que todo está corriendo

```powershell
# Terminal 1: XAMPP
# Apache: START (verde)
# MySQL: START (verde)
```

## Paso 2: Reinicializar BD (para obtener datos nuevos)

1. Abre en navegador:
   ```
   http://localhost/GestorX/gestorx-backend/init.php
   ```

2. Deberías ver mensaje verde de éxito

## Paso 3: Iniciar servidor Vue

```powershell
# Terminal 2
cd C:\xampp\htdocs\GestorX\gestorx
npm run serve
```

## Paso 4: Acceder a Control Maestro

1. Abre navegador: `http://localhost:8081`

2. **Login con credenciales Control Maestro:**
   ```
   Email: maestro@gestorx.test
   Password: Maestro@2026
   ```

3. **Automáticamente redirige a:** `/control-maestro`

## Paso 5: Ya puedes usar Control Maestro ✅

### Lo que puedes hacer:

- ✅ **Ver todas las empresas** en tabla
- ✅ **Ver estadísticas globales** (5 empresas, 28 usuarios, etc.)
- ✅ **Buscar empresas** por nombre
- ✅ **Filtrar por estado** (activas/suspendidas)
- ✅ **Ver usuarios** de cada empresa
- ✅ **Suspender empresas** (bloquea a sus usuarios)
- ✅ **Activar empresas** (desbloquea a sus usuarios)

---

## 🧪 Prueba Rápida

### Test 1: Suspender Empresa

```
1. En tabla, busca "GestorX Demo" (empresa de prueba)
2. Click en botón rojo "🔒 Suspender"
3. Confirma en modal
4. La empresa aparece ahora como "⊘ Suspendida"
```

### Test 2: Intentar login con usuario de empresa suspendida

```
1. Logout desde Control Maestro
2. Intenta login con: admin@gestorx.test / Admin@2026
3. Error: "Credenciales incorrectas" (empresa está suspendida)
```

### Test 3: Activar empresa nuevamente

```
1. Vuelve a login como maestro@gestorx.test / Maestro@2026
2. Busca "GestorX Demo" 
3. Click botón verde "🔓 Activar"
4. Confirma en modal
5. Empresa vuelve a estado "✓ Activa"
```

### Test 4: Ahora puede hacer login

```
1. Logout desde Control Maestro
2. Intenta login nuevamente con: admin@gestorx.test / Admin@2026
3. ✓ Acceso permitido - redirige a /admin/usuarios
```

---

## 📊 Estadísticas que verás

```
┌─────────────────────────────────────────┐
│ Centro de Control Maestro               │
├─────────────────────────────────────────┤
│                                         │
│  ┌─────────┐  ┌─────────┐  ┌────────┐  │
│  │    1    │  │   28    │  │   0    │  │
│  │ Empresas│  │ Usuarios│  │Suspend.│  │
│  │1 activa │  │23 activos│  │         │  │
│  └─────────┘  └─────────┘  └────────┘  │
│                                         │
└─────────────────────────────────────────┘
```

---

## 🔐 Roles en el Sistema

Ahora el sistema tiene esta estructura:

```
NIVEL GLOBAL (Sin empresa)
├── 👑 CONTROL MAESTRO
│   └── maestro@gestorx.test / Maestro@2026
│   └── Acceso: /control-maestro
│   └── Ver todas las empresas
│   └── Suspender/Activar empresas

NIVEL EMPRESA
├── 👤 ADMINISTRADOR
│   └── admin@gestorx.test / Admin@2026
│   └── Gestiona usuarios de su empresa
│
├── 🛍️ CAJERO
│   └── cajera@gestorx.test / Cajera@2026
│   └── Realiza ventas
│
└── 📦 ALMACENERO
    └── almacen@gestorx.test / Almacen@2026
    └── Gestiona inventario
```

**NOTA:** El rol GERENTE fue eliminado como solicitaste.

---

## 📁 Archivos Nuevos

```
Backend:
└── gestorx-backend/api/empresas.php

Frontend:
├── gestorx/src/views/ControlMaestro.vue
└── gestorx/src/views/Layout/MaestroLayout.vue

Documentación:
├── CONTROL_MAESTRO_DOCUMENTACION.md
└── CONTROL_MAESTRO_RESUMEN.md
```

---

## 🆘 Si algo falla

### Error: "maestro@gestorx.test no existe"

**Solución:**
1. Asegúrate de haber ejecutado: `http://localhost/GestorX/gestorx-backend/init.php`
2. Verifica que viste el mensaje verde de éxito
3. Recarga la página de login

### Error: "Puerto 8081 en uso"

**Solución:**
```powershell
netstat -ano | findstr :8081
taskkill /PID [numero] /F
npm run serve
```

### Las empresas no cargan

**Solución:**
1. Abre consola de navegador (F12)
2. Ve a pestaña "Network"
3. Verifica que `/api/empresas.php` retorna datos
4. Si hay error 403: Verifica token JWT

---

## 📞 Contacto / Dudas

Si algo no funciona:
1. Revisa consola del navegador (F12)
2. Revisa logs del servidor
3. Verifica credenciales
4. Reinicia XAMPP

---

**¡Listo para usar!** 🚀

Ahora puedes administrar todas las empresas desde el Control Maestro.
