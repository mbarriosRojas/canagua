# Sistema SACRGAPI - Gestión Administrativa

**Sistema Automatizado para el Control y Registro de la Gestión Administrativa**

## 📋 Descripción

Sistema web desarrollado en PHP con Docker para la gestión administrativa de instituciones educativas, incluyendo control de estudiantes, personal, talleres, cursos, inventario y calificaciones.

## 🚀 Características Principales

- **Autenticación y Autorización**: Sistema de login con jerarquía de usuarios (Admin, Supervisor, Operador)
- **Gestión de Usuarios**: CRUD completo con roles y permisos
- **Módulos Principales**:
  - 👨‍🎓 Gestión de Estudiantes
  - 👨‍🏫 Gestión de Personal
  - 🔧 Gestión de Talleres
  - 📚 Gestión de Cursos
  - 📦 Control de Inventario
  - 📊 Sistema de Calificaciones
- **Interfaz Moderna**: Diseño responsive con Bootstrap 5
- **Base de Datos**: MySQL con estructura normalizada
- **Docker**: Contenedores para desarrollo y producción

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 8.2
- **Base de Datos**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Contenedores**: Docker & Docker Compose
- **Herramientas**: DataTables, Font Awesome

## 📁 Estructura del Proyecto

```
SACRGAPI/
├── config/                 # Archivos de configuración
│   ├── php.ini            # Configuración PHP
│   └── config.env.example # Variables de entorno
├── database/              # Scripts de base de datos
│   └── init.sql          # Esquema inicial
├── public/               # Punto de entrada público
│   └── index.php        # Router principal
├── src/                 # Código fuente
│   ├── includes/       # Clases base
│   │   ├── Config.php  # Configuración
│   │   ├── Database.php # Conexión BD
│   │   └── Auth.php    # Autenticación
│   ├── views/          # Vistas (HTML/PHP)
│   │   ├── login.php   # Página de login
│   │   ├── dashboard.php # Panel principal
│   │   └── usuarios/   # Gestión de usuarios
│   └── assets/         # Recursos estáticos
├── docker-compose.yml  # Configuración Docker
├── Dockerfile         # Imagen PHP
└── README.md         # Este archivo
```

## 🚀 Instalación y Configuración

### ⚡ Instalación Automática (Recomendada)

Para usuarios sin conocimientos técnicos, simplemente ejecute uno de estos scripts:

**En Linux/macOS:**
```bash
./install.sh
```

**En Windows:**
```cmd
install.bat
```

Los scripts automáticamente:
- ✅ Verifican que Docker esté instalado
- ✅ Configuran las variables de entorno
- ✅ Construyen e inician los contenedores
- ✅ Incluyen datos de ejemplo para probar el sistema
- ✅ Muestran las credenciales de acceso

### 🔧 Instalación Manual

Si prefiere instalar manualmente:

#### Prerrequisitos
- Docker
- Docker Compose
- Git

#### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   cd SACRGAPI
   ```

2. **Configurar variables de entorno**
   ```bash
   cp config.env.example config.env
   # Editar config.env con tus configuraciones (opcional)
   ```

3. **Construir y ejecutar con Docker**
   ```bash
   docker-compose up --build -d
   ```

4. **Verificar la instalación**
   - Acceder a: http://localhost:8080/test_connection.php
   - Verificar que todas las pruebas pasen

## 🌐 Acceso al Sistema

### URLs Principales

- **Sistema Principal**: http://localhost:8080/public
- **phpMyAdmin**: http://localhost:8081
- **Prueba de Conexión**: http://localhost:8080/test_connection.php

### Credenciales por Defecto

- **Usuario**: `admin`
- **Contraseña**: `admin123`
- **Rol**: Administrador

### Credenciales de Base de Datos

- **Host**: `db` (desde contenedor) / `localhost:3306` (desde host)
- **Base de Datos**: `sacrgapi_database`
- **Usuario**: `sacrgapi_user`
- **Contraseña**: `sacrgapi_password_2024`

### 📊 Datos de Ejemplo Incluidos

El sistema viene con datos de demostración para que puedas probar todas las funcionalidades:

- **5 Programas educativos** (Tecnología, Educación, Social, Empresarial, Lingüística)
- **5 Instituciones** (Institutos tecnológicos, universidades, centros de formación)
- **6 Personal docente** (Coordinadores, instructores, supervisores)
- **6 Estudiantes** (Con datos completos de contacto)
- **5 Talleres** (Diferentes grados, secciones y lapsos)
- **5 Cursos** (Programación, desarrollo web, metodologías, etc.)
- **6 Calificaciones** (Con promedios calculados automáticamente)
- **5 Equipos de inventario** (Computadoras, impresoras, proyectores)
- **6 Participantes en cursos** (Con información de contacto)
- **6 Notas de certificación** (Estados de aprobación y certificados)

Estos datos te permiten explorar todas las funcionalidades del sistema sin necesidad de crear datos desde cero.

## 👥 Roles y Permisos

### Administrador
- Acceso completo a todos los módulos
- Gestión de usuarios
- Configuración del sistema

### Supervisor
- Gestión de usuarios (excepto otros administradores)
- Acceso a todos los módulos operativos
- Reportes y estadísticas

### Operador
- Acceso a módulos operativos
- Gestión de datos (estudiantes, personal, talleres, etc.)
- Sin acceso a gestión de usuarios

## 📊 Base de Datos

### Tablas Principales

- **usuarios**: Sistema de autenticación
- **programas**: Programas educativos
- **instituciones**: Instituciones participantes
- **estudiante**: Información de estudiantes
- **personal**: Personal docente y administrativo
- **talleres**: Talleres y cursos
- **cursos**: Cursos de capacitación
- **calificaciones**: Sistema de calificaciones
- **inventario**: Control de equipos
- **participantes**: Participantes en cursos
- **notas**: Notas y certificaciones

## 🔧 Comandos Útiles

### Instalación y Verificación

```bash
# Instalación automática completa (incluye datos de ejemplo)
./install.sh

# Verificar estado del sistema
docker-compose ps
```

### Docker

```bash
# Iniciar servicios
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down

# Reconstruir contenedores
docker-compose up --build

# Acceder al contenedor PHP
docker-compose exec web bash

# Acceder a MySQL
docker-compose exec db mysql -u sacrgapi_user -p sacrgapi_database
```

### Base de Datos

```bash
# Backup de la base de datos
docker-compose exec db mysqldump -u sacrgapi_user -p sacrgapi_database > backup.sql

# Restaurar backup
docker-compose exec -T db mysql -u sacrgapi_user -p sacrgapi_database < backup.sql
```

## 🐛 Solución de Problemas

### Problemas Comunes

1. **Error de conexión a base de datos**
   - Verificar que el contenedor `db` esté ejecutándose
   - Comprobar las credenciales en `config.env`

2. **Permisos de archivos**
   ```bash
   sudo chown -R www-data:www-data /var/www/html
   sudo chmod -R 755 /var/www/html
   ```

3. **Puertos ocupados**
   - Cambiar puertos en `docker-compose.yml`
   - Verificar que no haya otros servicios usando los puertos 8080, 8081, 3306

### Logs y Debugging

```bash
# Ver logs de la aplicación
docker-compose logs web

# Ver logs de la base de datos
docker-compose logs db

# Acceder al contenedor para debugging
docker-compose exec web bash
```

## 📝 Desarrollo

### Agregar Nuevos Módulos

1. Crear controlador en `public/index.php`
2. Crear vistas en `src/views/`
3. Agregar rutas en el router principal
4. Actualizar la base de datos si es necesario

### Estructura de Vistas

- Usar Bootstrap 5 para el diseño
- Implementar responsive design
- Seguir el patrón de diseño establecido
- Incluir validación JavaScript

## 📄 Licencia

Este proyecto es de uso interno para gestión administrativa.

## 👨‍💻 Soporte

Para soporte técnico o consultas sobre el sistema, contactar al equipo de desarrollo.

---

**Sistema SACRGAPI v1.0** - Desarrollado con ❤️ para la gestión administrativa
