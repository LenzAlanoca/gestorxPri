# 🔧 SOLUCIÓN: Empresas no aparecen en Control Maestro

## ❌ PROBLEMA
El URL de la API estaba incompleto. La vista ControlMaestro.vue estaba usando:
```javascript
'/gestorx-backend/api/empresas.php'
```

Pero debería usar:
```javascript
'/GestorX/gestorx-backend/api/empresas.php'
```

## ✅ SOLUCIÓN IMPLEMENTADA

### 1. Agregué configuración de API_URL en ControlMaestro.vue
```javascript
const API_URL = '/GestorX/gestorx-backend/api';
```

### 2. Actualicé todas las peticiones para usar API_URL
- `GET /api/empresas.php` → `GET ${API_URL}/empresas.php`
- `GET /api/empresas.php?usuarios=1` → `GET ${API_URL}/empresas.php?usuarios=1`
- `PUT /api/empresas.php?id=1` → `PUT ${API_URL}/empresas.php?id=1`

### 3. Mejoré los mensajes de error
Ahora muestran el error real de la API:
```javascript
this.mostrarToast('Error al cargar las empresas: ' + (error.response?.data?.error || error.message), 'error')
```

## 🧪 CÓMO PROBAR

### Opción 1: Usar la herramienta de test HTML
1. Abre: `http://localhost/GestorX/test-api.html`
2. Haz clic en "Login maestro@gestorx.test"
3. Luego haz clic en "Listar Empresas"
4. Debería mostrar todas las empresas en JSON

### Opción 2: Usar la aplicación Vue
1. Asegúrate que el servidor de desarrollo está corriendo:
   ```bash
   cd C:\xampp\htdocs\GestorX\gestorx
   npm run serve
   ```
   Debería estar en: `http://localhost:8082`

2. Ve a: `http://localhost:8082/login`
3. Inicia sesión con:
   - Email: `maestro@gestorx.test`
   - Contraseña: `Maestro@2026`
4. Se redirigirá a `/control-maestro`
5. **Ahora deberías ver la tabla de empresas** con:
   - Nombre de la empresa
   - Cantidad de usuarios
   - Estado (activa/suspendida)
   - Opciones para ver usuarios y suspender/activar

## 📋 EMPRESAS ESPERADAS

Deberías ver 2 empresas:

### Empresa 1: GestorX Demo
- Usuarios: 4
  - admin@gestorx.test (superadministrador)
  - gerente@gestorx.test (gerente)
  - cajera@gestorx.test (cajero)
  - almacen@gestorx.test (almacenero)
- Estado: activa

### Empresa 2: Empresa1
- Usuarios: 1
- Estado: activa

## 🐛 TROUBLESHOOTING

### Las empresas siguen sin aparecer
1. Abre la consola del navegador (F12)
2. Mira la pestaña "Network"
3. Busca el request a `/GestorX/gestorx-backend/api/empresas.php`
4. Verifica que devuelve algo como:
   ```json
   {
     "success": true,
     "data": [
       { "id_empresa": 1, "nombre_comercial": "GestorX Demo", ... }
     ]
   }
   ```

### Error 401 (No autorizado)
- El token no está siendo enviado correctamente
- Prueba limpiar localStorage: `localStorage.clear()` en consola
- Vuelve a hacer login

### Error 404 (URL no encontrada)
- Verifica que XAMPP está corriendo
- Verifica que estás en `http://localhost:8082` (puerto 8082 para Vue)
- Verifica que estás accediendo a `/GestorX/` (con slash al inicio)

### Error CORS
- Si ves error de CORS en consola, es un problema de configuración del servidor
- Verifica que `.htaccess` está configurado correctamente en gestorx-backend/

## ✨ FUNCIONALIDADES DISPONIBLES AHORA

Después de que aparezcan las empresas, puedes:

1. **Buscar empresas**
   - Escribe en el campo de búsqueda
   - Busca por nombre comercial o razón social

2. **Filtrar empresas**
   - Selecciona "Activas" o "Suspendidas"
   - O muestra "Todas"

3. **Ver usuarios de una empresa**
   - Haz clic en el botón "👥 Ver"
   - Se expande mostrando todos los usuarios
   - Muestra rol y estado de cada usuario

4. **Suspender/Activar empresa**
   - Haz clic en "🔒 Suspender" o "🔓 Activar"
   - Confirma en el modal
   - Los usuarios de la empresa suspendida no podrán hacer login

5. **Ver estadísticas**
   - Total de empresas
   - Empresas activas
   - Empresas suspendidas
   - Total de usuarios
   - Usuarios activos

---

**Ahora debería funcionar correctamente! 🎉**
