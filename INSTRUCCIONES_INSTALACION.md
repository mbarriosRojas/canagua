# 🚀 Instalación del Sistema SACRGAPI

## Instalación Automática (Recomendada)

### Paso 1: Verificar Requisitos
```bash
# Ejecutar el verificador de instalación
./verificar_instalacion.sh
```

### Paso 2: Instalar el Sistema
```bash
# Ejecutar el instalador automático
./install.sh
```

**¡Eso es todo!** El script `install.sh` hace todo automáticamente:
- ✅ Verifica que Docker esté instalado
- ✅ Inicia los contenedores de Docker
- ✅ Crea la base de datos
- ✅ Inserta datos de ejemplo
- ✅ Configura todo el sistema

## Instalación Manual (Si hay problemas)

Si el instalador automático falla, puede hacerlo paso a paso:

### Paso 1: Iniciar Docker
```bash
docker-compose up --build -d
```

### Paso 2: Verificar que esté funcionando
```bash
docker-compose ps
```

### Paso 3: Acceder al sistema
- URL: http://localhost:8080
- Usuario: admin@sacrgapi.com
- Contraseña: admin123

## Solución de Problemas

### Error: "Docker no está instalado"
- **Windows/Mac**: Descargar Docker Desktop desde https://www.docker.com/products/docker-desktop
- **Linux**: Ejecutar `curl -fsSL https://get.docker.com -o get-docker.sh && sudo sh get-docker.sh`

### Error: "Puerto 8080 en uso"
- Cambiar el puerto en `docker-compose.yml` (línea 15)
- O detener el servicio que usa el puerto 8080

### Error: "No se puede conectar a la base de datos"
- Esperar 1-2 minutos para que la base de datos se inicialice
- Verificar logs: `docker-compose logs db`

### Error: "Permisos denegados"
```bash
chmod +x install.sh
chmod +x verificar_instalacion.sh
```

## Verificación de la Instalación

### Verificar que todo funciona:
1. Abrir http://localhost:8080
2. Iniciar sesión con admin@sacrgapi.com / admin123
3. Navegar a "Talleres" para verificar el módulo

### Verificar contenedores:
```bash
docker-compose ps
```

### Ver logs si hay problemas:
```bash
docker-compose logs
```

## Estructura del Proyecto

```
SACRGAPI/
├── install.sh                 # Instalador automático
├── verificar_instalacion.sh   # Verificador de requisitos
├── docker-compose.yml         # Configuración de Docker
├── config.env                 # Variables de entorno
├── database/
│   └── init.sql              # Script de inicialización de BD
└── src/                      # Código fuente de la aplicación
```

## Módulos Disponibles

- 👥 **Usuarios**: Gestión de usuarios del sistema
- 📚 **Programas**: Gestión de programas educativos
- 🏢 **Instituciones**: Gestión de instituciones
- 🎓 **Estudiantes**: Gestión de estudiantes
- 👨‍🏫 **Personal**: Gestión del personal docente
- 🔧 **Talleres**: Gestión de talleres y calificaciones
- 📖 **Cursos**: Gestión de cursos
- 📦 **Inventario**: Gestión de equipos
- 📊 **Calificaciones**: Reportes de calificaciones

## Soporte

Si tiene problemas con la instalación:
1. Ejecutar `./verificar_instalacion.sh`
2. Revisar los logs con `docker-compose logs`
3. Verificar que Docker esté ejecutándose
4. Asegurarse de que el puerto 8080 esté libre
