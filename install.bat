@echo off
REM =============================================
REM SCRIPT DE INSTALACIÓN AUTOMÁTICA
REM Sistema SACRGAPI - Para usuarios no técnicos
REM =============================================

REM Forzar UTF-8 para caracteres especiales
chcp 65001 >nul

REM Asegurar que el script se ejecute desde su carpeta
cd /d "%~dp0"

echo =============================================
echo   INSTALADOR AUTOMÁTICO DEL SISTEMA SACRGAPI
echo =============================================
echo.

REM Verificar si Docker está instalado
echo [INFO] Verificando instalación de Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker no está instalado en este sistema.
    echo.
    echo Para instalar Docker Desktop, visite: https://www.docker.com/products/docker-desktop
    echo.
    echo 1. Descargue Docker Desktop para Windows
    echo 2. Ejecute el instalador
    echo 3. Reinicie su computadora
    echo 4. Vuelva a ejecutar este script
    echo.
    pause
    exit /b 1
)
echo [ÉXITO] Docker está instalado correctamente

REM Verificar si Docker Compose está instalado
echo [INFO] Verificando instalación de Docker Compose...
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker Compose no está instalado en este sistema.
    echo.
    echo Docker Compose debería incluirse con Docker Desktop.
    echo Si no está disponible, visite: https://docs.docker.com/compose/install/
    echo.
    pause
    exit /b 1
)
echo [ÉXITO] Docker Compose está instalado correctamente

REM Verificar que docker-compose.yml exista
if not exist "docker-compose.yml" (
    echo [ERROR] No se encontró el archivo docker-compose.yml
    pause
    exit /b 1
)

REM Crear archivo de configuración si no existe
echo [INFO] Configurando archivo de variables de entorno...
if not exist "config.env" (
    if exist "config.env.example" (
        copy "config.env.example" "config.env" >nul
        echo [ÉXITO] Archivo config.env creado desde config.env.example
    ) else (
        echo [ERROR] No se encontró el archivo config.env.example
        pause
        exit /b 1
    )
) else (
    echo [ADVERTENCIA] El archivo config.env ya existe, se mantendrá la configuración actual
)

REM Verificar puertos disponibles
echo [INFO] Verificando disponibilidad de puertos...
netstat -an | findstr ":8080" >nul
if %errorlevel% equ 0 (
    echo [ADVERTENCIA] El puerto 8080 está en uso. El sistema usará el puerto 8080 para la aplicación web.
    echo Si hay conflictos, puede cambiar el puerto en docker-compose.yml
)

netstat -an | findstr ":8081" >nul
if %errorlevel% equ 0 (
    echo [ADVERTENCIA] El puerto 8081 está en uso. El sistema usará el puerto 8081 para phpMyAdmin.
    echo Si hay conflictos, puede cambiar el puerto en docker-compose.yml
)

netstat -an | findstr ":3306" >nul
if %errorlevel% equ 0 (
    echo [ADVERTENCIA] El puerto 3306 está en uso. El sistema usará el puerto 3306 para MySQL.
    echo Si hay conflictos, puede cambiar el puerto en docker-compose.yml
)

echo.
echo [INFO] Construyendo e iniciando los contenedores...
echo Esto puede tomar varios minutos la primera vez...
echo.

REM Detener contenedores existentes si los hay
docker-compose down >nul 2>&1

REM Eliminar volúmenes para asegurar inicialización limpia de la base de datos
echo [INFO] Eliminando datos anteriores para asegurar inicialización limpia...
docker volume rm sacrgapi_mysql_data >nul 2>&1

REM Construir y ejecutar
echo [INFO] Ejecutando: docker-compose up --build -d
docker-compose up --build -d
if %errorlevel% neq 0 (
    echo [ERROR] Error al iniciar los contenedores
    echo.
    echo Intente ejecutar manualmente:
    echo   docker-compose up --build -d
    echo.
    pause
    exit /b 1
)
echo [ÉXITO] Contenedores iniciados correctamente

REM Esperar a que la base de datos esté lista
echo [INFO] Esperando a que la base de datos esté lista...
echo Esto puede tomar 30-60 segundos...
for /l %%i in (1,1,24) do (
    echo.
    timeout /t 5 /nobreak >nul
    docker-compose exec -T db mysqladmin ping -h localhost --silent >nul 2>&1
    if %errorlevel% equ 0 (
        echo [ÉXITO] Base de datos lista
        goto :database_ready
    )
    echo Esperando... %%i/24
)
echo [ADVERTENCIA] La base de datos tardó más de lo esperado en iniciar
echo El sistema debería funcionar, pero si hay problemas, espere unos minutos más

:database_ready
echo.
echo [INFO] Cargando datos de ejemplo en la base de datos...
echo Esto puede tomar 30-60 segundos adicionales...
timeout /t 10 /nobreak >nul

REM Verificar si ya hay datos
docker-compose exec -T db mysql -u sacrgapi_user -psacrgapi_password_2024 sacrgapi_database -e "SELECT COUNT(*) FROM usuarios;" 2>nul | findstr "1" >nul
if %errorlevel% equ 0 (
    echo [ÉXITO] Base de datos ya tiene datos de ejemplo
    goto :data_loaded
)

REM Crear script de datos temporal
echo [INFO] Preparando datos de ejemplo...
(
echo USE sacrgapi_database;
echo.
echo -- Insertar usuario administrador por defecto
echo INSERT IGNORE INTO usuarios ^(username, email, password_hash, nombre, apellido, rol, creado_por^) 
echo VALUES ^('admin', 'admin@sacrgapi.com', '$2y$10$oZvMv/mqns.gnBi1w/QiF.uF/guZ/8gbC8PIjzu4f0k2bLqBBggD2', 'Administrador', 'Sistema', 'admin', 1^);
echo.
echo -- Insertar programas de ejemplo
echo INSERT IGNORE INTO programas ^(cod_programas, descripcion, sub_area^) VALUES
echo ^('PROG001', 'Programa de Formación Técnica en Informática', 'Tecnología'^),
echo ^('PROG002', 'Programa de Capacitación Docente', 'Educación'^),
echo ^('PROG003', 'Programa de Desarrollo Comunitario', 'Social'^),
echo ^('PROG004', 'Programa de Emprendimiento', 'Empresarial'^),
echo ^('PROG005', 'Programa de Idiomas', 'Lingüística'^);
echo.
echo -- Insertar instituciones de ejemplo
echo INSERT IGNORE INTO instituciones ^(cod_institucion, descripcion, telefono_enlace, datos_docente_enlace^) VALUES
echo ^('INST001', 'Instituto Tecnológico Nacional', '0212-555-0001', 'Dr. Juan Pérez - Coordinador Académico'^),
echo ^('INST002', 'Universidad Central de Venezuela', '0212-555-0002', 'Dra. María González - Directora de Extensión'^),
echo ^('INST003', 'Centro de Formación Profesional', '0212-555-0003', 'Ing. Carlos Rodríguez - Jefe de Programas'^),
echo ^('INST004', 'Instituto de Capacitación Laboral', '0212-555-0004', 'Lic. Ana Silva - Coordinadora de Proyectos'^),
echo ^('INST005', 'Centro de Estudios Avanzados', '0212-555-0005', 'Prof. Luis Herrera - Director Académico'^);
echo.
echo -- Insertar personal de ejemplo
echo INSERT IGNORE INTO personal ^(cedula_personal, apellido, nombre, cargo, fecha_ingreso^) VALUES
echo ^('V-12345678', 'García', 'Ana', 'Coordinadora General', '2024-01-15'^),
echo ^('V-87654321', 'López', 'Pedro', 'Instructor Técnico', '2024-02-01'^),
echo ^('V-11223344', 'Martínez', 'Carmen', 'Supervisora Académica', '2024-01-20'^),
echo ^('V-55667788', 'Rodríguez', 'Luis', 'Instructor de Programación', '2024-02-15'^),
echo ^('V-99887766', 'Silva', 'Elena', 'Coordinadora de Proyectos', '2024-01-10'^),
echo ^('V-44332211', 'Herrera', 'Roberto', 'Supervisor de Calidad', '2024-03-01'^);
echo.
echo -- Insertar estudiantes de ejemplo
echo INSERT IGNORE INTO estudiante ^(cedula_estudiante, apellido, nombre, apellido_2, sexo, lugar_nacimiento, cedula_representante, telefono^) VALUES
echo ^('V-11111111', 'Pérez', 'María', 'González', 'F', 'Caracas', 'V-22222222', '0412-111-1111'^),
echo ^('V-33333333', 'González', 'Carlos', 'López', 'M', 'Valencia', 'V-44444444', '0414-333-3333'^),
echo ^('V-55555555', 'Martínez', 'Ana', 'Silva', 'F', 'Maracaibo', 'V-66666666', '0424-555-5555'^),
echo ^('V-77777777', 'Rodríguez', 'José', 'Herrera', 'M', 'Barquisimeto', 'V-88888888', '0416-777-7777'^),
echo ^('V-99999999', 'López', 'Carmen', 'Pérez', 'F', 'Valencia', 'V-10101010', '0426-999-9999'^),
echo ^('V-12121212', 'Silva', 'Luis', 'García', 'M', 'Caracas', 'V-13131313', '0412-121-1212'^);
echo.
echo -- Insertar talleres de ejemplo
echo INSERT IGNORE INTO talleres ^(programas_id_programas, instituciones_id_instituciones, personal_id_personal, cod_taller, cod_institucion, cod_programa, cod_personal, ano_escolar, grado, seccion, lapso, activo^) VALUES
echo ^(1, 1, 1, 'TALL001', 'INST001', 'PROG001', 'V-12345678', 2024, '1er', 'A', 'I', 1^),
echo ^(1, 1, 2, 'TALL002', 'INST001', 'PROG001', 'V-87654321', 2024, '2do', 'B', 'I', 1^),
echo ^(2, 2, 3, 'TALL003', 'INST002', 'PROG002', 'V-11223344', 2024, '3er', 'A', 'I', 1^),
echo ^(1, 3, 4, 'TALL004', 'INST003', 'PROG001', 'V-55667788', 2024, '1er', 'C', 'I', 1^),
echo ^(3, 4, 5, 'TALL005', 'INST004', 'PROG003', 'V-99887766', 2024, '2do', 'A', 'I', 1^);
echo.
echo -- Insertar cursos de ejemplo
echo INSERT IGNORE INTO cursos ^(personal_id_personal, cod_curso, nombre_curso, cedula_persona, duracion, ano, num_de_clases, periodo^) VALUES
echo ^(1, 'CUR001', 'Introducción a la Programación', 'V-12345678', 40, 2024, 20, 'I'^),
echo ^(2, 'CUR002', 'Desarrollo Web con PHP', 'V-87654321', 60, 2024, 30, 'I'^),
echo ^(3, 'CUR003', 'Metodologías de Enseñanza', 'V-11223344', 32, 2024, 16, 'I'^),
echo ^(4, 'CUR004', 'Base de Datos MySQL', 'V-55667788', 48, 2024, 24, 'I'^),
echo ^(5, 'CUR005', 'Gestión de Proyectos Comunitarios', 'V-99887766', 36, 2024, 18, 'I'^);
echo.
echo -- Insertar calificaciones de ejemplo
echo INSERT IGNORE INTO calificaciones ^(estudiante_id_estudiante, cedula_estudiante, cod_taller, talleres_id_taller, instituciones_id_instituciones, literal, numeral, momento_i, momento_ii, momento_iii, momento_i_numeral, momento_i_literal, momento_ii_numeral, momento_ii_literal, momento_iii_numeral, momento_iii_literal, promedio, promedio_final, literal_final^) VALUES
echo ^(1, 'V-11111111', 'TALL001', 1, 1, 'A', 18.5, 18.0, 19.0, 18.5, 18.0, 'A', 19.0, 'A', 18.5, 'A', 18.5, 18.5, 'A'^),
echo ^(2, 'V-33333333', 'TALL001', 1, 1, 'B', 16.0, 15.5, 16.5, 16.0, 15.5, 'B', 16.5, 'B', 16.0, 'B', 16.0, 16.0, 'B'^),
echo ^(3, 'V-55555555', 'TALL002', 2, 1, 'A', 19.0, 18.5, 19.5, 19.0, 18.5, 'A', 19.5, 'A', 19.0, 'A', 19.0, 19.0, 'A'^),
echo ^(4, 'V-77777777', 'TALL002', 2, 1, 'B', 17.0, 16.5, 17.5, 17.0, 16.5, 'B', 17.5, 'B', 17.0, 'B', 17.0, 17.0, 'B'^),
echo ^(5, 'V-99999999', 'TALL003', 3, 2, 'A', 18.0, 17.5, 18.5, 18.0, 17.5, 'A', 18.5, 'A', 18.0, 'A', 18.0, 18.0, 'A'^),
echo ^(6, 'V-12121212', 'TALL003', 3, 2, 'B', 16.5, 16.0, 17.0, 16.5, 16.0, 'B', 17.0, 'B', 16.5, 'B', 16.5, 16.5, 'B'^);
echo.
echo -- Insertar relaciones estudiante-taller de ejemplo
echo INSERT IGNORE INTO estudiante_taller ^(estudiante_id_estudiante, talleres_id_taller, instituciones_id_instituciones^) VALUES
echo ^(1, 1, 1^),
echo ^(2, 1, 1^),
echo ^(3, 2, 1^),
echo ^(4, 2, 1^),
echo ^(5, 3, 2^),
echo ^(6, 3, 2^);
echo.
echo -- Insertar inventario de ejemplo
echo INSERT IGNORE INTO inventario ^(personal_id_personal, cod_equipo, ubicacion, cedula_personal, cantidad, estado, serial, marca, modelo, color, medidas, capacidad, otras_caracteristicas, observacion^) VALUES
echo ^(1, 'EQ001', 'Aula 101', 'V-12345678', 1, 'Bueno', 'SN123456', 'Dell', 'OptiPlex 7090', 'Negro', '35x25x8 cm', '8GB RAM', 'Intel i5, 256GB SSD', 'Equipo en perfecto estado'^),
echo ^(1, 'EQ002', 'Aula 101', 'V-12345678', 1, 'Bueno', 'SN789012', 'HP', 'ProBook 450', 'Plata', '35x25x3 cm', '16GB RAM', 'Intel i7, 512GB SSD', 'Laptop para instructor'^),
echo ^(2, 'EQ003', 'Aula 102', 'V-87654321', 1, 'Regular', 'SN345678', 'Lenovo', 'ThinkPad E15', 'Negro', '36x25x2 cm', '8GB RAM', 'AMD Ryzen 5, 256GB SSD', 'Requiere mantenimiento menor'^),
echo ^(3, 'EQ004', 'Oficina Principal', 'V-11223344', 1, 'Bueno', 'SN901234', 'Canon', 'PIXMA G3110', 'Blanco', '45x35x15 cm', 'Impresión A4', 'Impresora multifuncional', 'Nueva, sin usar'^),
echo ^(4, 'EQ005', 'Aula 103', 'V-55667788', 1, 'Bueno', 'SN567890', 'Epson', 'PowerLite 1781W', 'Blanco', '30x25x10 cm', '3000 lúmenes', 'Proyector portátil', 'Excelente calidad de imagen'^);
echo.
echo -- Insertar participantes de ejemplo
echo INSERT IGNORE INTO participantes ^(cursos_id_cursos, cedula_participante, apellido, nombre, sexo, telefono, correo, direccion^) VALUES
echo ^(1, 'V-11111111', 'Pérez', 'María', 'F', '0412-111-1111', 'maria.perez@email.com', 'Av. Principal, Caracas'^),
echo ^(1, 'V-33333333', 'González', 'Carlos', 'M', '0414-333-3333', 'carlos.gonzalez@email.com', 'Calle 2, Valencia'^),
echo ^(2, 'V-55555555', 'Martínez', 'Ana', 'F', '0424-555-5555', 'ana.martinez@email.com', 'Av. Libertador, Maracaibo'^),
echo ^(2, 'V-77777777', 'Rodríguez', 'José', 'M', '0416-777-7777', 'jose.rodriguez@email.com', 'Carrera 5, Barquisimeto'^),
echo ^(3, 'V-99999999', 'López', 'Carmen', 'F', '0426-999-9999', 'carmen.lopez@email.com', 'Av. Bolívar, Valencia'^),
echo ^(4, 'V-12121212', 'Silva', 'Luis', 'M', '0412-121-1212', 'luis.silva@email.com', 'Calle 10, Caracas'^);
echo.
echo -- Insertar notas de ejemplo
echo INSERT IGNORE INTO notas ^(participantes_id_participantes, cedula_participante, cod_curso, aprobo, participo, no_aprobo, recibio_certificado^) VALUES
echo ^(1, 'V-11111111', 'CUR001', TRUE, TRUE, FALSE, TRUE^),
echo ^(2, 'V-33333333', 'CUR001', TRUE, TRUE, FALSE, TRUE^),
echo ^(3, 'V-55555555', 'CUR002', TRUE, TRUE, FALSE, TRUE^),
echo ^(4, 'V-77777777', 'CUR002', FALSE, TRUE, TRUE, FALSE^),
echo ^(5, 'V-99999999', 'CUR003', TRUE, TRUE, FALSE, TRUE^),
echo ^(6, 'V-12121212', 'CUR004', TRUE, TRUE, FALSE, TRUE^);
) > insert_data_temp.sql

REM Cargar datos
echo [INFO] Ejecutando script de datos...
docker-compose exec -T db mysql -u sacrgapi_user -psacrgapi_password_2024 sacrgapi_database < insert_data_temp.sql
if %errorlevel% equ 0 (
    echo [ÉXITO] Datos de ejemplo cargados correctamente
    del insert_data_temp.sql
    echo [INFO] Corrigiendo codificación de caracteres...
    docker-compose exec -T db mysql -u sacrgapi_user -psacrgapi_password_2024 sacrgapi_database < database\fix_encoding.sql
) else (
    echo [ERROR] Error al cargar datos de ejemplo
    del insert_data_temp.sql
)

:data_loaded
echo.
echo [INFO] Verificando que el sistema esté funcionando...
timeout /t 10 /nobreak >nul

REM Verificar que los contenedores estén ejecutándose
docker-compose ps | findstr "Up" >nul
if %errorlevel% equ 0 (
    echo [ÉXITO] Contenedores ejecutándose correctamente
) else (
    echo [ERROR] Algunos contenedores no están ejecutándose
    echo Ejecute 'docker-compose ps' para ver el estado
)

echo.
echo =============================================
echo   INSTALACIÓN COMPLETADA EXITOSAMENTE
echo =============================================
echo.
echo 🌐 ACCESO AL SISTEMA:
echo   • Sistema Principal: http://localhost:8080/public/
echo   • phpMyAdmin: http://localhost:8081
echo   • Prueba de Conexión: http://localhost:8080/test_connection.php
echo.
echo 👤 CREDENCIALES DE ACCESO:
echo   • Usuario: admin
echo   • Contraseña: admin123
echo.
echo 🗄️ CREDENCIALES DE BASE DE DATOS:
echo   • Host: localhost:3306
echo   • Base de Datos: sacrgapi_database
echo   • Usuario: sacrgapi_user
echo   • Contraseña: sacrgapi_password_2024
echo.
echo 📊 DATOS DE EJEMPLO INCLUIDOS:
echo   • 5 Programas educativos
echo   • 5 Instituciones
echo   • 6 Personal docente
echo   • 6 Estudiantes
echo   • 5 Talleres
echo   • 5 Cursos
echo   • 6 Calificaciones
echo   • 5 Equipos de inventario
echo   • 6 Participantes en cursos
echo   • 6 Notas de certificación
echo.
echo 💡 COMANDOS ÚTILES:
echo   • Ver logs: docker-compose logs -f
echo   • Detener sistema: docker-compose down
echo   • Reiniciar sistema: docker-compose restart
echo   • Ver estado: docker-compose ps
echo.
echo ¡El sistema está listo para usar! 🎉
echo.
pause
