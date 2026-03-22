@echo off
chcp 65001 >nul
cd /d "%~dp0"
title Instalador SACRGAPI para XAMPP

REM Guardar log en archivo para diagnostico
set LOG=install_log.txt
echo Inicio: %DATE% %TIME% > %LOG%

echo.
echo ============================================
echo   INSTALADOR SACRGAPI - XAMPP WINDOWS
echo ============================================
echo.

REM -----------------------------------------------
REM PASO 1: Detectar XAMPP
REM -----------------------------------------------
echo [1/6] Buscando XAMPP...
echo [1/6] Buscando XAMPP >> %LOG%

set XAMPP_PATH=

if exist "C:\xampp\mysql\bin\mysql.exe"                          set XAMPP_PATH=C:\xampp
if exist "C:\XAMPP\mysql\bin\mysql.exe"                          set XAMPP_PATH=C:\XAMPP
if exist "D:\xampp\mysql\bin\mysql.exe"                          set XAMPP_PATH=D:\xampp
if exist "D:\XAMPP\mysql\bin\mysql.exe"                          set XAMPP_PATH=D:\XAMPP
if exist "C:\Program Files\xampp\mysql\bin\mysql.exe"            set XAMPP_PATH=C:\Program Files\xampp
if exist "C:\Program Files (x86)\xampp\mysql\bin\mysql.exe"      set XAMPP_PATH=C:\Program Files (x86)\xampp

if "%XAMPP_PATH%"=="" (
    echo.
    echo [ERROR] No se encontro XAMPP.
    echo Rutas revisadas: C:\xampp, D:\xampp y Program Files
    echo.
    echo Instale XAMPP desde: https://www.apachefriends.org
    echo Luego vuelva a ejecutar este script.
    echo.
    echo ERROR: XAMPP no encontrado >> %LOG%
    goto :fin_error
)

echo [OK] XAMPP en: %XAMPP_PATH%
echo [OK] XAMPP en: %XAMPP_PATH% >> %LOG%

set MYSQL="%XAMPP_PATH%\mysql\bin\mysql.exe"
set MYSQLADMIN="%XAMPP_PATH%\mysql\bin\mysqladmin.exe"
set HTDOCS=%XAMPP_PATH%\htdocs

REM -----------------------------------------------
REM PASO 2: Verificar MySQL activo
REM -----------------------------------------------
echo.
echo [2/6] Verificando que MySQL este activo...
echo [2/6] Verificando MySQL >> %LOG%

%MYSQLADMIN% -u root --connect-timeout=3 ping >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] MySQL no responde. Intentando iniciar servicio...
    net start mysql >nul 2>&1
    timeout /t 5 /nobreak >nul
    %MYSQLADMIN% -u root --connect-timeout=3 ping >nul 2>&1
    if %errorlevel% neq 0 (
        echo.
        echo [ERROR] MySQL no esta corriendo.
        echo.
        echo  SOLUCION:
        echo  1. Abra el Panel de Control de XAMPP
        echo  2. Haga clic en [Start] junto a MySQL
        echo  3. Espere a que aparezca "Running"
        echo  4. Vuelva a ejecutar este script
        echo.
        echo ERROR: MySQL no activo >> %LOG%
        goto :fin_error
    )
)

echo [OK] MySQL activo
echo [OK] MySQL activo >> %LOG%

REM -----------------------------------------------
REM PASO 3: Crear base de datos e importar
REM -----------------------------------------------
echo.
echo [3/6] Creando base de datos...
echo [3/6] Creando base de datos >> %LOG%

REM Crear base de datos (root sin contrasena = XAMPP por defecto)
%MYSQL% -u root -e "CREATE DATABASE IF NOT EXISTS sacrgapi_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>>%LOG%
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] No se pudo crear la base de datos.
    echo Revise el archivo %LOG% para detalles.
    echo.
    echo Posible causa: MySQL root tiene contrasena.
    echo Si es asi, abra phpMyAdmin (http://localhost/phpmyadmin)
    echo y ejecute manualmente: database\init.sql
    echo.
    goto :fin_error
)
echo [OK] Base de datos "sacrgapi_database" creada
echo [OK] Base de datos creada >> %LOG%

REM Crear usuario de la aplicacion
%MYSQL% -u root -e "CREATE USER IF NOT EXISTS 'sacrgapi_user'@'localhost' IDENTIFIED BY 'sacrgapi_password_2024';" 2>>%LOG%
%MYSQL% -u root -e "GRANT ALL PRIVILEGES ON sacrgapi_database.* TO 'sacrgapi_user'@'localhost';" 2>>%LOG%
%MYSQL% -u root -e "FLUSH PRIVILEGES;" 2>>%LOG%
echo [OK] Usuario sacrgapi_user configurado
echo [OK] Usuario creado >> %LOG%

REM Importar esquema
echo [INFO] Importando esquema (puede tardar 10-30 seg)...
echo [INFO] Importando esquema >> %LOG%

if not exist "database\init.sql" (
    echo [ERROR] No se encontro database\init.sql
    echo ERROR: init.sql no encontrado >> %LOG%
    goto :fin_error
)

%MYSQL% -u root sacrgapi_database < "database\init.sql" 2>>%LOG%
if %errorlevel% neq 0 (
    echo [ERROR] Fallo la importacion del esquema.
    echo Revise %LOG%
    goto :fin_error
)
echo [OK] Esquema importado correctamente
echo [OK] Esquema importado >> %LOG%

if exist "database\fix_encoding.sql" (
    %MYSQL% -u root sacrgapi_database < "database\fix_encoding.sql" 2>>%LOG%
    echo [OK] Codificacion corregida
)

REM Verificar que se crearon las tablas
echo [INFO] Verificando tablas...
%MYSQL% -u root sacrgapi_database -e "SHOW TABLES;" 2>>%LOG% | findstr "usuarios" >nul
if %errorlevel% neq 0 (
    echo [ADVERTENCIA] Las tablas podrian no haberse creado correctamente.
    echo Verifique en phpMyAdmin: http://localhost/phpmyadmin
) else (
    echo [OK] Tablas verificadas correctamente
    echo [OK] Tablas OK >> %LOG%
)

REM -----------------------------------------------
REM PASO 4: Configurar config.env
REM -----------------------------------------------
echo.
echo [4/6] Configurando config.env...
echo [4/6] Configurando config.env >> %LOG%

if not exist "config.env" (
    if exist "config.env.example" (
        copy "config.env.example" "config.env" >nul
        echo [OK] config.env creado desde config.env.example
    ) else (
        echo [ERROR] No se encontro config.env ni config.env.example
        goto :fin_error
    )
)

REM Obtener nombre de la carpeta actual del proyecto
for %%I in ("%~dp0.") do set FOLDER_NAME=%%~nxI
echo [INFO] Nombre de carpeta del proyecto: %FOLDER_NAME%

REM Actualizar DB_HOST en config.env (reemplazar linea completa)
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$c = Get-Content 'config.env'; $c = $c -replace '^DB_HOST=.*', 'DB_HOST=localhost'; $c | Set-Content 'config.env'" 2>>%LOG%

REM Actualizar APP_URL en config.env
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$c = Get-Content 'config.env'; $c = $c -replace '^APP_URL=.*', 'APP_URL=http://localhost/%FOLDER_NAME%/public'; $c | Set-Content 'config.env'" 2>>%LOG%

echo [OK] config.env actualizado
echo [OK] config.env actualizado >> %LOG%

REM -----------------------------------------------
REM PASO 5: Copiar a htdocs (si no esta ya ahi)
REM -----------------------------------------------
echo.
echo [5/6] Instalando en htdocs de XAMPP...
echo [5/6] Instalando en htdocs >> %LOG%

set DEST=%HTDOCS%\%FOLDER_NAME%

REM Revisar si ya estamos dentro de htdocs
echo %~dp0 | findstr /i "htdocs" >nul
if %errorlevel% equ 0 (
    echo [OK] El proyecto ya esta dentro de htdocs. No es necesario copiar.
    echo [OK] Ya en htdocs >> %LOG%
    goto :paso6
)

REM Copiar proyecto a htdocs
echo [INFO] Copiando a: %DEST%
if not exist "%HTDOCS%" (
    echo [ERROR] No se encontro la carpeta htdocs en: %HTDOCS%
    echo Verifique la instalacion de XAMPP.
    goto :fin_error
)

xcopy /E /I /Y /Q "%~dp0" "%DEST%\" >nul 2>>%LOG%
if %errorlevel% neq 0 (
    echo [ADVERTENCIA] No se pudo copiar automaticamente.
    echo.
    echo  Copie MANUALMENTE la carpeta del proyecto a:
    echo    %DEST%\
    echo.
) else (
    echo [OK] Proyecto copiado a %DEST%
    echo [OK] Copiado a htdocs >> %LOG%
)

:paso6
REM -----------------------------------------------
REM PASO 6: Verificar Apache activo
REM -----------------------------------------------
echo.
echo [6/6] Verificando Apache...
echo [6/6] Verificando Apache >> %LOG%

netstat -an 2>nul | findstr ":80 " >nul
if %errorlevel% neq 0 (
    echo [ADVERTENCIA] Apache podria no estar activo (puerto 80 no detectado).
    echo Asegurese de iniciar Apache en el Panel de XAMPP.
) else (
    echo [OK] Apache detectado en puerto 80
)

REM -----------------------------------------------
REM RESULTADO FINAL
REM -----------------------------------------------
echo.
echo ============================================
echo   INSTALACION COMPLETADA EXITOSAMENTE
echo ============================================
echo.
echo  ACCESO AL SISTEMA:
echo    http://localhost/%FOLDER_NAME%/public/
echo.
echo  USUARIO: admin
echo  CONTRASENA: admin123
echo.
echo  phpMyAdmin: http://localhost/phpmyadmin
echo.
echo  Log de instalacion guardado en: %LOG%
echo.
echo Fin exitoso: %DATE% %TIME% >> %LOG%

goto :fin_ok

:fin_error
echo.
echo ============================================
echo   LA INSTALACION ENCONTRO UN PROBLEMA
echo ============================================
echo.
echo  Revise el archivo %LOG% para ver los detalles del error.
echo  Ruta del log: %~dp0%LOG%
echo.
echo Fin con error: %DATE% %TIME% >> %LOG%

:fin_ok
echo Presione cualquier tecla para cerrar...
pause >nul
