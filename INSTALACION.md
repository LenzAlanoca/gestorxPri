# 🚀 INSTALACIÓN Y CONFIGURACIÓN DE GESTORX

## 📋 Requisitos Previos

- ✅ XAMPP con PHP 8.2+
- ✅ MySQL/MariaDB activo
- ✅ Base de datos `gestorxbd` creada (vacía)
- ✅ Node.js instalado
- ✅ npm instalado

## 📁 Estructura del Proyecto

```
GestorX/
├── gestorx/                    # Frontend Vue.js
│   ├── src/
│   ├── public/
│   ├── package.json
│   └── vue.config.js          # Configuración con proxy
│
├── gestorx-backend/            # Backend PHP
│   ├── api/                   # Endpoints
│   │   ├── auth.php
│   │   ├── registro.php
│   │   ├── usuarios.php
│   │   └── roles.php
│   ├── config/
│   │   └── database.php       # Configuración BD
│   ├── install.php            # 🆕 Instalador automático
│   └── ...
│
└── DATABASE_SCHEMA.sql         # Schema de la BD
```

## 🔧 PASOS DE INSTALACIÓN

### 1️⃣ Crear la Base de Datos

En phpMyAdmin o MySQL:

```sql
CREATE DATABASE gestorxbd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

O en la terminal MySQL:
```bash
mysql -u root -p
> CREATE DATABASE gestorxbd CHARACTER SET utf8mb4;
> exit;
```

### 2️⃣ Ejecutar el Instalador Automático

Opción A: **Vía navegador** (Recomendado - MÁS FÁCIL)

1. Abre tu navegador
2. Ve a: `http://localhost/GestorX/gestorx-backend/install.php`
3. Verás un JSON confirmando la creación de tablas

```json
{
  "success": true,
  "message": "Base de datos inicializada correctamente",
  "database": "gestorxbd",
  "tables_created": 17,
  "tables_skipped": 0,
  "errors": [],
  "timestamp": "2026-01-14 19:30:00"
}
```

Opción B: **Vía phpMyAdmin**

1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Selecciona la BD `gestorxbd`
3. Ve a la pestaña "SQL"
4. Copia todo el contenido de `DATABASE_SCHEMA.sql`
5. Ejecuta

### 3️⃣ Configurar Backend PHP

Verifica que `gestorx-backend/config/database.php` tenga:

```php
private $host = 'localhost';
private $db = 'gestorxbd';      // ✅ Nombre correcto
private $user = 'root';
private $pass = '';
```

### 4️⃣ Instalar Dependencias Frontend

```bash
cd C:\xampp\htdocs\GestorX\gestorx
npm install
```

### 5️⃣ Iniciar el Servidor Vue.js

```bash
npm run serve
```

Verás algo como:
```
App running at:
- Local:   http://localhost:8081/
- Network: http://192.168.1.4:8081/
```

### 6️⃣ Abre la Aplicación

```
http://localhost:8081
```

---

## ✅ VERIFICAR INSTALACIÓN

### Verificar Tablas Creadas

En phpMyAdmin o MySQL:

```sql
USE gestorxbd;
SHOW TABLES;
```

Deberías ver:
- ✅ plan
- ✅ empresa
- ✅ rol
- ✅ usuario
- ✅ cliente
- ✅ proveedor
- ✅ categoria
- ✅ producto
- ✅ venta
- ✅ compra
- ✅ movimiento_inventario
- ✅ notificacion
- ... y más

### Verificar Backend Funciona

```
http://localhost/GestorX/gestorx-backend/api/test.php
```

Deberías ver JSON de respuesta.

### Verificar Frontend Funciona

```
http://localhost:8081
```

Deberías ver la página de login/registro.

---

## 🔌 Configuración del Proxy

El archivo `vue.config.js` ya está configurado para redirigir peticiones:

```javascript
proxy: {
  '/GestorX': {
    target: 'http://localhost',
    changeOrigin: true
  }
}
```

Esto permite que las peticiones desde `localhost:8081` lleguen a Apache en `localhost:80`.

---

## 🚀 INICIAR DESARROLLO

```bash
# Terminal 1: Backend (Apache/XAMPP debe estar corriendo)
# Asegúrate de que Apache esté iniciado en XAMPP

# Terminal 2: Frontend
cd c:\xampp\htdocs\GestorX\gestorx
npm run serve
```

Luego abre: `http://localhost:8081`

---

## 🐛 SOLUCIONAR PROBLEMAS

### Error: "Base de datos no encontrada"
- ✅ Verifica que `gestorxbd` exista en MySQL
- ✅ Revisa las credenciales en `config/database.php`

### Error 404 en peticiones a API
- ✅ Apache debe estar corriendo
- ✅ Las URLs deben ser `/GestorX/gestorx-backend/api/...`
- ✅ El proxy en `vue.config.js` debe estar configurado

### Tablas no se crean
- ✅ Abre `http://localhost/GestorX/gestorx-backend/install.php`
- ✅ Verifica el JSON de respuesta para ver errores
- ✅ Asegúrate de que MySQL/MariaDB está corriendo

### El servidor Vue no inicia
- ✅ `npm install` debe estar ejecutado
- ✅ Puerto 8081 debe estar disponible
- ✅ Node.js debe estar instalado

---

## 📝 COMANDOS ÚTILES

```bash
# Instalar dependencias
npm install

# Iniciar servidor desarrollo
npm run serve

# Compilar para producción
npm run build

# Verificar errores de linting
npm run lint
```

---

## 🔑 USUARIO INICIAL (Después de crear datos)

Una vez que las tablas estén creadas, necesitarás:

1. Crear un plan en la tabla `plan`
2. Crear una empresa en `empresa`
3. Crear roles en `rol`
4. Crear un usuario en `usuario`

Luego podrás registrarte con esos datos.

---

## 📚 DOCUMENTACIÓN ADICIONAL

- [DATABASE_SCHEMA.sql](../DATABASE_SCHEMA.sql) - Schema de la base de datos
- [DATABASE_SCHEMA_CORRECTIONS.txt](../DATABASE_SCHEMA_CORRECTIONS.txt) - Detalles de las correcciones
- [INTEGRACION_BACKEND.md](gestorx/INTEGRACION_BACKEND.md) - Detalles de integración

---

## ✨ ESTADO: LISTO PARA DESARROLLO

✅ Base de datos automáticamente configurada
✅ Backend integrado en el proyecto
✅ Frontend conectado con proxy
✅ URLs correctamente configuradas
✅ Documentación completa

**Felicidades! 🎉 GestorX está listo para usarse.**
