<?php

/**
 * Script de prueba para validar la configuración del sistema SACRGAPI
 * Sistema Automatizado para el Control y Registro de la Gestión Administrativa
 */

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Prueba de Configuración - Sistema SACRGAPI</h1>";
echo "<hr>";

// 1. Verificar PHP
echo "<h2>1. Verificación de PHP</h2>";
echo "<p>✅ Versión de PHP: " . PHP_VERSION . "</p>";
echo "<p>✅ Extensión PDO: " . (extension_loaded('pdo') ? 'Disponible' : '❌ No disponible') . "</p>";
echo "<p>✅ Extensión PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Disponible' : '❌ No disponible') . "</p>";
echo "<p>✅ Extensión MySQLi: " . (extension_loaded('mysqli') ? 'Disponible' : '❌ No disponible') . "</p>";
echo "<hr>";

// 2. Verificar archivos de configuración
echo "<h2>2. Verificación de Archivos</h2>";
$requiredFiles = [
    'src/includes/Config.php',
    'src/includes/Database.php',
    'src/includes/Auth.php',
    'public/index.php',
    'database/init.sql',
    'docker-compose.yml',
    'Dockerfile'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists($file);
    echo "<p>" . ($exists ? '✅' : '❌') . " $file</p>";
}
echo "<hr>";

// 3. Probar conexión a base de datos
echo "<h2>3. Prueba de Conexión a Base de Datos</h2>";
try {
    // Cargar configuración
    require_once 'src/includes/Config.php';
    Config::load();
    
    // Probar conexión
    require_once 'src/includes/Database.php';
    $db = Database::getInstance();
    
    if ($db->isConnected()) {
        echo "<p>✅ Conexión a base de datos exitosa</p>";
        
        // Probar consulta
        $result = $db->fetchOne("SELECT 'Conexión exitosa' as message, NOW() as timestamp");
        echo "<p>✅ Consulta de prueba exitosa: " . $result['message'] . "</p>";
        echo "<p>✅ Timestamp del servidor: " . $result['timestamp'] . "</p>";
        
        // Verificar tablas
        $tables = $db->fetchAll("SHOW TABLES");
        echo "<p>✅ Tablas encontradas: " . count($tables) . "</p>";
        
        if (count($tables) > 0) {
            echo "<ul>";
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                echo "<li>$tableName</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p>❌ No se pudo conectar a la base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
}
echo "<hr>";

// 4. Probar autenticación
echo "<h2>4. Prueba de Sistema de Autenticación</h2>";
try {
    require_once 'src/includes/Auth.php';
    $auth = new Auth();
    
    echo "<p>✅ Clase Auth cargada correctamente</p>";
    
    // Verificar usuario administrador por defecto
    $adminUser = $db->fetchOne("SELECT * FROM usuarios WHERE username = 'admin'");
    if ($adminUser) {
        echo "<p>✅ Usuario administrador encontrado</p>";
        echo "<p>📧 Email: " . $adminUser['email'] . "</p>";
        echo "<p>👤 Rol: " . $adminUser['rol'] . "</p>";
        echo "<p>📅 Creado: " . $adminUser['fecha_creacion'] . "</p>";
    } else {
        echo "<p>❌ Usuario administrador no encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en autenticación: " . $e->getMessage() . "</p>";
}
echo "<hr>";

// 5. Verificar configuración de la aplicación
echo "<h2>5. Configuración de la Aplicación</h2>";
echo "<p>✅ Nombre de la aplicación: " . Config::getAppName() . "</p>";
echo "<p>✅ URL base: " . Config::getBaseUrl() . "</p>";
echo "<p>✅ Entorno: " . Config::get('app.env') . "</p>";
echo "<p>✅ Zona horaria: " . Config::get('app.timezone') . "</p>";
echo "<hr>";

// 6. Instrucciones de uso
echo "<h2>6. Instrucciones de Uso</h2>";
echo "<div style='background-color: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #007bff;'>";
echo "<h3>🚀 Para iniciar el sistema:</h3>";
echo "<ol>";
echo "<li><strong>Construir y ejecutar con Docker:</strong><br>";
echo "<code>docker-compose up --build</code></li>";
echo "<li><strong>Acceder al sistema:</strong><br>";
echo "URL: <a href='http://localhost:8080/public' target='_blank'>http://localhost:8080/public</a></li>";
echo "<li><strong>Credenciales por defecto:</strong><br>";
echo "Usuario: <code>admin</code><br>";
echo "Contraseña: <code>password</code></li>";
echo "<li><strong>Acceder a phpMyAdmin:</strong><br>";
echo "URL: <a href='http://localhost:8081' target='_blank'>http://localhost:8081</a></li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>✅ Prueba completada - " . date('Y-m-d H:i:s') . "</strong></p>";
echo "<p><em>Sistema SACRGAPI - Gestión Administrativa</em></p>";
?>
