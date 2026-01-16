# 🚀 INICIO RÁPIDO - CONTROL MAESTRO

## Paso 1: Asegurar que XAMPP está corriendo
- Apache: ✅
- MySQL: ✅

## Paso 2: Iniciar servidor frontend (Terminal/PowerShell)
```powershell
cd C:\xampp\htdocs\GestorX\gestorx
npm run serve
```

Salida esperada:
```
  App running at:
  - Local:   http://localhost:8082/
```

## Paso 3: Abrir navegador
```
http://localhost:8082/login
```

## Paso 4: Ingresar credenciales de Control Maestro
```
Email:       maestro@gestorx.test
Contraseña:  Maestro@2026
```

## Paso 5: ¡Listo!
Debería ver el **Centro de Control Maestro** con:
- 📊 Estadísticas globales
- 📋 Tabla de empresas
- 🔍 Búsqueda y filtros
- 👥 Lista de usuarios por empresa
- 🔒 Botones para suspender/activar

---

## Si necesitas probar como ADMIN de empresa

Credenciales para GestorX Demo:
```
Email:       admin@gestorx.test
Contraseña:  Admin@2026
```

Esto te llevará a `/admin/usuarios` (panel de administrador de la empresa)

---

## URLs importantes

| Sección | URL |
|---------|-----|
| Login | http://localhost:8082/login |
| Control Maestro | http://localhost:8082/control-maestro |
| Admin Empresa | http://localhost:8082/admin/usuarios |
| API Empresas | http://localhost/GestorX/gestorx-backend/api/empresas.php |

---

## Troubleshooting rápido

**Error "No puedo conectar al servidor"**
- Verificar que XAMPP está corriendo
- Verificar que el backend está en `http://localhost/GestorX/gestorx-backend/`

**El login no redirige a Control Maestro**
- Limpiar localStorage: `localStorage.clear()` en consola (F12)
- Recargar la página (Ctrl+Shift+R)

**Las empresas no se cargan**
- Verificar console (F12 → Network) para ver errores
- Asegurar que el token está siendo enviado correctamente
- Verificar permisos en la BD (usuario debe ser superadministrador con id_rol=1)

---

**Listo para empezar! 🎉**
