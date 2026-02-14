# 🚀 Instrucciones Rápidas de Instalación - SACRGAPI

## Para usuarios SIN conocimientos técnicos

### Paso 1: Instalar Docker
1. **Windows**: Descargue Docker Desktop desde https://www.docker.com/products/docker-desktop
2. **macOS**: Descargue Docker Desktop desde https://www.docker.com/products/docker-desktop  
3. **Linux**: Visite https://docs.docker.com/get-docker/

### Paso 2: Instalar el Sistema
1. Descargue o copie la carpeta del proyecto a su computadora
2. Abra una terminal/consola en la carpeta del proyecto
3. Ejecute uno de estos comandos:

**En Windows:**
```cmd
install.bat
```

**En Linux/macOS:**
```bash
./install.sh
```

### Paso 3: Usar el Sistema
1. Abra su navegador web
2. Vaya a: http://localhost:8080/public/
3. Use estas credenciales:
   - **Usuario**: admin
   - **Contraseña**: admin123

## ✅ ¡Listo!

El sistema ya está funcionando con datos de ejemplo incluidos.

### URLs importantes:
- **Sistema principal**: http://localhost:8080/public/
- **Administrar base de datos**: http://localhost:8081
- **Verificar instalación**: http://localhost:8080/test_connection.php

### Si algo no funciona:
1. Espere 2-3 minutos (la primera vez tarda más)
2. Verifique que Docker esté ejecutándose
3. Si la base de datos está vacía, ejecute nuevamente:
   - **Linux/macOS**: `./install.sh`
   - **Windows**: `install.bat`
4. Revise el archivo README.md para más detalles

---
**¿Necesita ayuda?** Consulte el archivo README.md completo para instrucciones detalladas.
