# Instalacion del Sistema SACRGAPI con XAMPP en Windows

## Requisitos

- Windows 7 / 8 / 10 / 11
- XAMPP (incluye Apache + MySQL + phpMyAdmin)
  - Descarga: https://www.apachefriends.org/es/download.html
  - Version recomendada: XAMPP 8.2.x o superior

---

## Instalacion Automatica (Recomendada)

### Paso 1: Instalar XAMPP

1. Descargue XAMPP desde https://www.apachefriends.org/es/download.html
2. Ejecute el instalador y complete la instalacion
3. Al finalizar, abra el **Panel de Control de XAMPP**

### Paso 2: Iniciar servicios en XAMPP

En el Panel de Control de XAMPP:
- Haga clic en **[Start]** junto a **Apache** → espere a que diga "Running"
- Haga clic en **[Start]** junto a **MySQL** → espere a que diga "Running"

### Paso 3: Colocar el proyecto en htdocs

Copie la carpeta **SACRGAPI** dentro de:
```
C:\xampp\htdocs\
```
Resultado esperado:
```
C:\xampp\htdocs\SACRGAPI\
C:\xampp\htdocs\SACRGAPI\install.bat
C:\xampp\htdocs\SACRGAPI\public\
C:\xampp\htdocs\SACRGAPI\src\
...
```

### Paso 4: Ejecutar el instalador

1. Abra la carpeta `C:\xampp\htdocs\SACRGAPI\`
2. Haga **doble clic** en **`install.bat`**
3. El instalador automaticamente:
   - Verifica que MySQL este activo
   - Crea la base de datos `sacrgapi_database`
   - Crea el usuario `sacrgapi_user`
   - Importa el esquema y datos de ejemplo
   - Configura el archivo `config.env`

### Paso 5: Acceder al sistema

Abra su navegador y vaya a:
```
http://localhost/SACRGAPI/public/
```

Credenciales:
- **Usuario**: admin
- **Contrasena**: admin123

---

## Instalacion Manual (Si el instalador falla)

### 1. Configurar config.env

Abra `C:\xampp\htdocs\SACRGAPI\config.env` y verifique:
```
DB_HOST=localhost
DB_NAME=sacrgapi_database
DB_USER=sacrgapi_user
DB_PASS=sacrgapi_password_2024
APP_URL=http://localhost/SACRGAPI/public
```

### 2. Crear base de datos via phpMyAdmin

1. Abra http://localhost/phpmyadmin
2. Inicie sesion con usuario `root` (sin contrasena por defecto)
3. Haga clic en **"Nueva"** (panel izquierdo)
4. Nombre: `sacrgapi_database`, Cotejamiento: `utf8mb4_unicode_ci`
5. Haga clic en **"Crear"**

### 3. Importar el esquema

1. Seleccione la base de datos `sacrgapi_database` en phpMyAdmin
2. Haga clic en la pestana **"Importar"**
3. Haga clic en **"Seleccionar archivo"**
4. Seleccione: `C:\xampp\htdocs\SACRGAPI\database\init.sql`
5. Haga clic en **"Continuar"**

### 4. Crear usuario MySQL (via phpMyAdmin)

1. En phpMyAdmin, haga clic en **"Cuentas de usuario"** (menu superior)
2. Haga clic en **"Agregar cuenta de usuario"**
3. Complete:
   - Nombre de usuario: `sacrgapi_user`
   - Nombre del servidor: `localhost`
   - Contrasena: `sacrgapi_password_2024`
4. En "Privilegios globales" marque **"Todos los privilegios"** en `sacrgapi_database`
5. Haga clic en **"Continuar"**

### 5. Acceder al sistema

```
http://localhost/SACRGAPI/public/
```

---

## Estructura del Proyecto

```
SACRGAPI/
├── install.bat              # Instalador automatico para XAMPP
├── config.env               # Variables de entorno (DB, URL, etc.)
├── config.env.example       # Plantilla de configuracion
├── .htaccess                # Redireccion y seguridad raiz
├── database/
│   ├── init.sql             # Esquema completo + datos de ejemplo
│   └── fix_encoding.sql     # Correccion de codificacion UTF-8
├── public/
│   ├── index.php            # Punto de entrada principal (router)
│   └── .htaccess            # Routing interno del modulo public
└── src/                     # Codigo fuente PHP
    ├── includes/            # Config, Database, Auth
    ├── models/              # Modelos de datos
    └── views/               # Vistas HTML/PHP
```

---

## URLs del Sistema

| Recurso | URL |
|---|---|
| Sistema principal | http://localhost/SACRGAPI/public/ |
| phpMyAdmin | http://localhost/phpmyadmin |
| Test de conexion | http://localhost/SACRGAPI/public/test |

---

## Modulos Disponibles

- **Usuarios**: Gestion de usuarios del sistema
- **Programas**: Programas educativos
- **Instituciones**: Gestion de instituciones
- **Estudiantes**: Registro de estudiantes
- **Personal**: Personal docente
- **Talleres**: Talleres y calificaciones
- **Cursos**: Cursos y participantes
- **Inventario**: Equipos e inventario
- **Calificaciones**: Reportes y calificaciones

---

## Configuracion de correo (recuperacion de clave)

Para que la funcion **Recuperar clave** envie la nueva contrasena por correo, configure SMTP con Gmail (u otro servidor) en `config.env`:

1. **Instalar dependencia de correo**: En la carpeta del proyecto ejecute `composer update` (requiere Composer instalado) para instalar PHPMailer.

2. **Variables en config.env**:
   - `MAIL_FROM` y `MAIL_FROM_NAME`: correo y nombre que aparecen como remitente.
   - `MAIL_SMTP_HOST=smtp.gmail.com`
   - `MAIL_SMTP_PORT=587`
   - `MAIL_SMTP_USER`: su correo Gmail (ej. ctnjesusdenazareth@gmail.com)
   - `MAIL_SMTP_PASS`: **contraseña de aplicacion** de Google (no la contrasena normal de Gmail).

3. **Obtener contraseña de aplicacion en Gmail**:
   - Cuenta de Google → **Seguridad** → active **Verificacion en 2 pasos** si no esta activa.
   - **Seguridad** → **Verificacion en 2 pasos** → al final **Contraseñas de aplicaciones** → generar una para "Correo" u "Otro".
   - Google mostrara una contrasena de 16 caracteres; copiela en `MAIL_SMTP_PASS` en `config.env`.

Sin configurar SMTP, la recuperacion de clave actualizara la contrasena en la base de datos pero no enviara el correo (el sistema mostrara un mensaje indicandolo).

---

## Solucion de Problemas

### "No se puede conectar a la base de datos"
- Verifique que MySQL este **Running** en XAMPP
- Confirme que `DB_HOST=localhost` en `config.env`
- Abra http://localhost/phpmyadmin y verifique que la BD existe

### "Pagina no encontrada (404)"
- Verifique que Apache este **Running** en XAMPP
- Confirme que la carpeta esta en `C:\xampp\htdocs\SACRGAPI\`
- Verifique que `mod_rewrite` este habilitado en XAMPP:
  - Abra `C:\xampp\apache\conf\httpd.conf`
  - Busque `LoadModule rewrite_module` (debe estar sin `#` al inicio)
  - Busque el bloque `<Directory "C:/xampp/htdocs">` y asegurese que diga `AllowOverride All`

### "mod_rewrite no funciona / .htaccess ignorado"
1. Abra `C:\xampp\apache\conf\httpd.conf`
2. Localice: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Elimine el `#` al inicio para activarlo
4. Localice el bloque `<Directory "C:/xampp/htdocs">`
5. Cambie `AllowOverride None` por `AllowOverride All`
6. Guarde el archivo
7. Reinicie Apache en el Panel de XAMPP

### "El instalador (install.bat) no funciona"
- Haga clic derecho en `install.bat` y seleccione **"Ejecutar como administrador"**
- Si MySQL tiene contrasena root, ingresela cuando se solicite

### El sistema funciona pero las URL muestran la carpeta con otro nombre
- Abra `config.env`
- Cambie `APP_URL` al nombre correcto de su carpeta:
  ```
  APP_URL=http://localhost/NOMBRE_CARPETA/public
  ```
