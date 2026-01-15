# 🚀 INSTRUCCIONES PARA INICIAR GESTORX

## PASO 1: Asegurar que XAMPP está ejecutándose

1. **Abre XAMPP Control Panel**
   - Busca "XAMPP" en Windows
   - Haz clic en `xampp-control.exe`

2. **Inicia los servicios necesarios:**
   - Apache: Haz clic en **Start** (debe estar en verde)
   - MySQL: Haz clic en **Start** (debe estar en verde)

```
Apache  [Start] ← Verde = Ejecutándose
MySQL   [Start] ← Verde = Ejecutándose
```

---

## PASO 2: Inicializar la Base de Datos

### Opción A: Inicialización Automática (RECOMENDADO)

1. En el navegador, abre:
   ```
   http://localhost/GestorX/gestorx-backend/init.php
   ```

2. Deberías ver un mensaje verde:
   ```json
   {
     "success": true,
     "message": "✅ Base de datos inicializada correctamente",
     "status": "Las tablas se crearon y los datos de prueba se insertaron",
     "timestamp": "2026-01-14 14:30:45",
     "next_step": "Abre http://localhost:8081 y inicia sesión..."
   }
   ```

3. ¡Listo! Continúa con el PASO 3.

### Opción B: Si algo falla

Si ves un error rojo, verifica:
- ✅ XAMPP Apache está ejecutándose (verde)
- ✅ XAMPP MySQL está ejecutándose (verde)
- ✅ La ruta es correcta: `http://localhost/GestorX/gestorx-backend/init.php`

Si sigue sin funcionar:
1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Busca la base de datos `gestorxbd`
3. Si NO existe, créala:
   - Haz clic en "Nuevo"
   - Nombre: `gestorxbd`
   - Haz clic en "Crear"

4. Luego intenta de nuevo el Paso 2, Opción A

---

## PASO 3: Iniciar el Servidor Vue

1. **Abre una terminal** (PowerShell o CMD)

2. **Navega a la carpeta del proyecto:**
   ```powershell
   cd C:\xampp\htdocs\GestorX\gestorx
   ```

3. **Inicia el servidor de desarrollo:**
   ```bash
   npm run serve
   ```

4. **Espera a que compile** (puede tardar 30-60 segundos)

5. Cuando veas:
   ```
   App running at:
   - Local:   http://localhost:8081/
   ```

   ¡El servidor está listo! 🎉

---

## PASO 4: Acceder a la Aplicación

1. **Abre en el navegador:**
   ```
   http://localhost:8081
   ```

2. **Verás la página de Login**

3. **Usa estas credenciales:**

   **Usuario Administrador:**
   - Email: `admin@gestorx.test`
   - Contraseña: `Admin@2026`

   O prueba con otros usuarios:
   - **Gerente**: `gerente@gestorx.test` / `Gerente@2026`
   - **Cajera**: `cajera@gestorx.test` / `Cajera@2026`
   - **Almacén**: `almacen@gestorx.test` / `Almacen@2026`

4. **¡Entra y explora el sistema!** 🎊

---

## ✅ CHECKLIST DE VERIFICACIÓN

- [ ] XAMPP Apache está ejecutándose (verde)
- [ ] XAMPP MySQL está ejecutándose (verde)
- [ ] Ejecutaste: `http://localhost/GestorX/gestorx-backend/init.php`
- [ ] Viste el mensaje de éxito (JSON verde)
- [ ] Ejecutaste: `npm run serve` desde la carpeta `gestorx`
- [ ] El servidor Vue está corriendo en `http://localhost:8081`
- [ ] Puedes acceder a `http://localhost:8081` sin errores
- [ ] Puedes iniciar sesión con `admin@gestorx.test`

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Error: "Cannot GET /GestorX/gestorx-backend/init.php"
- **Causa**: Apache no está ejecutándose
- **Solución**: Inicia Apache en XAMPP Control Panel

### Error: "Access denied for user 'root'@'localhost'"
- **Causa**: MySQL no está ejecutándose o credenciales incorrectas
- **Solución**: 
  1. Inicia MySQL en XAMPP Control Panel
  2. Verifica que en `database.php` el usuario sea `root` con contraseña vacía

### Error: "Unknown database 'gestorxbd'"
- **Causa**: La base de datos no existe
- **Solución**:
  1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
  2. Crea la base de datos manualmente
  3. Luego ejecuta el init.php

### Error: "npm: comando no encontrado"
- **Causa**: Node.js no está instalado o no está en el PATH
- **Solución**: 
  1. Instala Node.js desde https://nodejs.org/
  2. Abre una nueva terminal después de instalar
  3. Intenta de nuevo

### Error: "Port 8081 already in use"
- **Causa**: Otro proceso está usando el puerto 8081
- **Solución**:
  ```powershell
  # Mata el proceso en el puerto 8081
  netstat -ano | findstr :8081
  taskkill /PID [numero] /F
  ```

---

## 📞 RESUMEN RÁPIDO

```
1. XAMPP → Apache ON, MySQL ON
2. Navegador → http://localhost/GestorX/gestorx-backend/init.php
3. Terminal → cd C:\xampp\htdocs\GestorX\gestorx && npm run serve
4. Navegador → http://localhost:8081
5. Login → admin@gestorx.test / Admin@2026
```

---

**¿Todo funciona?** 🎉 ¡Bienvenido a GestorX! 

Si algo falla, verifica el checklist arriba o revisa la consola para ver los errores específicos.
