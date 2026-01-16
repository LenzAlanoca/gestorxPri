# 🛠️ COMANDOS Y REFERENCIAS ÚTILES

## Terminal Commands

### Windows PowerShell

#### Iniciar Servidor Vue
```powershell
cd C:\xampp\htdocs\GestorX\gestorx
npm run serve
```

#### Ver si puerto está en uso
```powershell
netstat -ano | findstr :8081
netstat -ano | findstr :3306
netstat -ano | findstr :80
```

#### Matar proceso en puerto
```powershell
taskkill /PID {numero} /F
```

#### Limpiar cache npm
```powershell
npm cache clean --force
```

#### Reinstalar dependencias
```powershell
rm -r node_modules
npm install
```

---

## URLs Útiles

### Desarrollo Local

```
Frontend (Vue):
http://localhost:8081/

Backend API:
http://localhost/gestorx-backend/api/

Inicializar BD:
http://localhost/GestorX/gestorx-backend/init.php

phpMyAdmin:
http://localhost/phpmyadmin/
```

### Rutas de la Aplicación

```
Login:
http://localhost:8081/login

Control Maestro:
http://localhost:8081/control-maestro

Admin Panel:
http://localhost:8081/admin/usuarios

Dashboard Cajero:
http://localhost:8081/user
```

---

## Credenciales

### Control Maestro
```
Email:    maestro@gestorx.test
Password: Maestro@2026
```

### Admin de Empresa
```
Email:    admin@gestorx.test
Password: Admin@2026
```

### Otros Usuarios
```
Gerente:  gerente@gestorx.test / Gerente@2026
Cajera:   cajera@gestorx.test / Cajera@2026
Almacén:  almacen@gestorx.test / Almacen@2026
```

---

## Archivos Importantes

### Backend

```
gestorx-backend/
├── api/
│   ├── empresas.php           ← NUEVO (Control Maestro)
│   ├── auth.php               (Login)
│   ├── usuarios.php           (Gestión usuarios)
│   └── roles.php              (Gestión roles)
│
├── config/
│   ├── database.php           (Conexión BD)
│   ├── Seeder.php             (Datos de prueba)
│   └── Initializer.php        (Inicializar BD)
│
├── models/
│   ├── Usuario.php            ← MODIFICADO
│   ├── Empresa.php
│   └── Rol.php
│
├── middlewares/
│   ├── AuthMiddleware.php     ← MODIFICADO
│   └── CorsMiddleware.php
│
└── helpers/
    └── JWT.php                (Tokens JWT)
```

### Frontend

```
gestorx/src/
├── views/
│   ├── ControlMaestro.vue     ← NUEVO
│   ├── Login.vue
│   ├── Dashboard.vue
│   │
│   └── Layout/
│       ├── MaestroLayout.vue  ← NUEVO
│       ├── AdminLayout.vue
│       └── UserLayout.vue
│
├── router/
│   └── index.js               ← MODIFICADO
│
├── config/
│   └── api.js
│
└── main.js
```

---

## Comandos SQL Útiles

### Ver usuarios
```sql
SELECT * FROM usuario;
```

### Ver empresas
```sql
SELECT * FROM empresa;
```

### Ver roles
```sql
SELECT * FROM rol;
```

### Ver permisos
```sql
SELECT * FROM permiso;
```

### Ver usuarios de empresa específica
```sql
SELECT u.*, r.nombre_rol 
FROM usuario u 
JOIN rol r ON u.id_rol = r.id_rol 
WHERE u.id_empresa = 1;
```

### Ver si usuario es Control Maestro
```sql
SELECT * FROM usuario WHERE id_empresa IS NULL;
```

### Cambiar estado empresa
```sql
UPDATE empresa SET estado_empresa = 'suspendida' WHERE id_empresa = 1;
UPDATE empresa SET estado_empresa = 'activa' WHERE id_empresa = 1;
```

### Ver empresas suspendidas
```sql
SELECT * FROM empresa WHERE estado_empresa = 'suspendida';
```

---

## API Calls (cURL)

### Login
```bash
curl -X POST http://localhost/gestorx-backend/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "login",
    "correo": "maestro@gestorx.test",
    "password": "Maestro@2026"
  }'
```

### Listar Empresas
```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost/gestorx-backend/api/empresas.php
```

### Ver Usuarios de Empresa
```bash
curl -H "Authorization: Bearer {token}" \
  "http://localhost/gestorx-backend/api/empresas.php?usuarios=1"
```

### Suspender Empresa
```bash
curl -X PUT \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  http://localhost/gestorx-backend/api/empresas.php?id=1
```

---

## Debug

### Console del Navegador (F12)

#### Ver token actual
```javascript
localStorage.getItem('token')
```

#### Ver datos de usuario
```javascript
JSON.parse(localStorage.getItem('user'))
```

#### Ver rol
```javascript
JSON.parse(localStorage.getItem('user')).rol
```

#### Limpiar localStorage
```javascript
localStorage.clear()
```

#### Ver request a API
```javascript
// Network tab > Seleccionar request > Ver Response
```

#### Ver errores
```javascript
// Console tab > Ver mensajes en rojo
```

---

## Configuración de Desarrollo

### VS Code Extensions Recomendados
```
- Vetur (Vue support)
- PHP IntelliSense
- SQLTools
- Thunder Client (para API testing)
- REST Client
```

### Settings.json (VS Code)
```json
{
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "vetur.format.defaultFormatter.html": "prettier",
  "[php]": {
    "editor.defaultFormatter": "felixbecker.php-intellisense"
  }
}
```

---

## Troubleshooting

### Puerto 8081 ya está en uso
```powershell
netstat -ano | findstr :8081
taskkill /PID {numero} /F
npm run serve
```

### Base de datos no existe
```
1. Abrir phpMyAdmin: http://localhost/phpmyadmin
2. Crear BD: gestorxbd
3. Ejecutar init.php: http://localhost/GestorX/gestorx-backend/init.php
```

### Token inválido o expirado
```
1. Logout: localStorage.clear()
2. Volver a hacer login
3. Verificar que el token se guarda correctamente
```

### API retorna 403
```
1. Verificar que eres superadministrador
2. Verificar que el token es válido
3. Ver headers en Network tab
```

### Empresa no está suspendiendo
```sql
-- Verificar estado en BD
SELECT * FROM empresa WHERE id_empresa = 1;

-- Actualizar manualmente si es necesario
UPDATE empresa SET estado_empresa = 'suspendida' WHERE id_empresa = 1;
```

---

## Performance Tips

### Optimizar Búsqueda
```javascript
// En ControlMaestro.vue
computed: {
  empresasFiltradas() {
    // Usa indexOf en lugar de includes para mejor performance
    return this.empresas.filter(e => 
      e.nombre_comercial.toLowerCase().indexOf(this.searchTerm.toLowerCase()) !== -1
    )
  }
}
```

### Cache de Empresas
```javascript
// Considera guardar en localStorage después de cargar
localStorage.setItem('empresas', JSON.stringify(this.empresas))

// Y reutilizar en lugar de recargar siempre
if (localStorage.getItem('empresas')) {
  this.empresas = JSON.parse(localStorage.getItem('empresas'))
}
```

### Debounce en Búsqueda
```javascript
// Importar lodash
import { debounce } from 'lodash'

// En methods:
buscar: debounce(function(valor) {
  this.searchTerm = valor
}, 300)
```

---

## Documentación de Referencia

### Archivos de Documentación Locales
```
1. CONTROL_MAESTRO_DOCUMENTACION.md    (Completa)
2. CONTROL_MAESTRO_RESUMEN.md          (Resumen rápido)
3. CONTROL_MAESTRO_INICIO_RAPIDO.md    (Guía inicio)
4. CAMBIOS_CONTROL_MAESTRO.md          (Registro cambios)
5. ARQUITECTURA_CONTROL_MAESTRO.md     (Diagramas)
6. RESUMEN_FINAL_CONTROL_MAESTRO.md    (Conclusión)
```

### Links Externos
```
Vue.js:          https://vuejs.org/
Vue Router:      https://router.vuejs.org/
Axios:           https://axios-http.com/
PHP PDO:         https://www.php.net/manual/en/book.pdo.php
JWT:             https://jwt.io/
Bootstrap Icons: https://icons.getbootstrap.com/
```

---

## Actualizar Código

### Recargar el servidor sin perder el estado
```powershell
# Ctrl + C para detener
# npm run serve para reiniciar
```

### Actualizar dependencias
```powershell
npm update
```

### Checar vulnerabilidades
```powershell
npm audit
npm audit fix
```

---

## Backup y Restauración

### Backup BD MySQL
```bash
mysqldump -u root -p gestorxbd > backup_gestorxbd.sql
```

### Restaurar BD
```bash
mysql -u root -p gestorxbd < backup_gestorxbd.sql
```

### Limpiar BD (sin borrar estructura)
```sql
DELETE FROM usuario WHERE id_empresa IS NOT NULL;
DELETE FROM empresa;
```

---

## Testing Rápido

### Test 1: Listar empresas en consola
```javascript
// En console del navegador, después de login
const token = localStorage.getItem('token')
fetch('/gestorx-backend/api/empresas.php', {
  headers: { 'Authorization': `Bearer ${token}` }
})
.then(r => r.json())
.then(d => console.log(d))
```

### Test 2: Ver estructura de usuario
```javascript
console.log(JSON.parse(localStorage.getItem('user')))
```

### Test 3: Validar token
```javascript
const token = localStorage.getItem('token')
const parts = token.split('.')
const payload = JSON.parse(atob(parts[1]))
console.log(payload)
```

---

**Última actualización:** 15-01-2026  
**Versión:** 1.0
