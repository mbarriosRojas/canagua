<?php

/**
 * Clase de configuración del sistema
 * Sistema SACRGAPI - Gestión Administrativa
 */
class Config {
    private static $config = [];
    
    /**
     * Cargar configuración desde variables de entorno
     */
    public static function load() {
        // Cargar variables de entorno si existe el archivo
        if (file_exists(__DIR__ . '/../../config.env')) {
            $lines = file(__DIR__ . '/../../config.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
        
        // Configuración por defecto
        self::$config = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'Sistema SACRGAPI',
                'url' => $_ENV['APP_URL'] ?? 'http://localhost/SACRGAPI/public',
                'env' => $_ENV['APP_ENV'] ?? 'development',
                'timezone' => 'America/Caracas'
            ],
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'name' => $_ENV['DB_NAME'] ?? 'sacrgapi_database',
                'user' => $_ENV['DB_USER'] ?? 'sacrgapi_user',
                'pass' => $_ENV['DB_PASS'] ?? 'sacrgapi_password_2024'
            ],
            'session' => [
                'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 3600,
                'name' => $_ENV['SESSION_NAME'] ?? 'SACRGAPI_SESSION'
            ],
            'security' => [
                'encryption_key' => $_ENV['ENCRYPTION_KEY'] ?? 'default_key_change_in_production',
                'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'default_jwt_secret_change_in_production'
            ],
            'mail' => [
                'from' => $_ENV['MAIL_FROM'] ?? 'noreply@' . (parse_url($_ENV['APP_URL'] ?? 'http://localhost', PHP_URL_HOST) ?: 'localhost'),
                'from_name' => $_ENV['MAIL_FROM_NAME'] ?? $_ENV['APP_NAME'] ?? 'SACRGAPI',
                'smtp_host' => $_ENV['MAIL_SMTP_HOST'] ?? '',
                'smtp_port' => (int) ($_ENV['MAIL_SMTP_PORT'] ?? 587),
                'smtp_user' => $_ENV['MAIL_SMTP_USER'] ?? '',
                'smtp_pass' => $_ENV['MAIL_SMTP_PASS'] ?? '',
                'smtp_secure' => $_ENV['MAIL_SMTP_SECURE'] ?? 'tls'
            ]
        ];
        
        // Configurar zona horaria
        date_default_timezone_set(self::$config['app']['timezone']);
        
        // Configurar sesiones
        ini_set('session.cookie_lifetime', self::$config['session']['lifetime']);
        ini_set('session.name', self::$config['session']['name']);
    }
    
    /**
     * Obtener valor de configuración
     */
    public static function get($key, $default = null) {
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
    
    /**
     * Establecer valor de configuración
     */
    public static function set($key, $value) {
        $keys = explode('.', $key);
        $config = &self::$config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }
    
    /**
     * Verificar si estamos en modo desarrollo
     */
    public static function isDevelopment() {
        return self::get('app.env') === 'development';
    }
    
    /**
     * Verificar si estamos en modo producción
     */
    public static function isProduction() {
        return self::get('app.env') === 'production';
    }
    
    /**
     * Obtener URL base de la aplicación
     */
    public static function getBaseUrl() {
        return self::get('app.url');
    }
    
    /**
     * Obtener nombre de la aplicación
     */
    public static function getAppName() {
        return self::get('app.name');
    }
}
