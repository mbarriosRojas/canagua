<?php

/**
 * Registro de trazas de ingreso (logs) en la tabla log.
 * Equivalente a SAAA/Procesos/logs.php.
 */

if (!function_exists('ensureLogTable')) {
    function ensureLogTable() {
        static $checked = false;
        if ($checked) {
            return;
        }
        try {
            $db = Database::getInstance();
            $db->query("CREATE TABLE IF NOT EXISTS `log` (
                id INT PRIMARY KEY AUTO_INCREMENT,
                IP VARCHAR(45),
                `Time` DATETIME DEFAULT CURRENT_TIMESTAMP,
                Details TEXT,
                `Page` VARCHAR(255),
                clave VARCHAR(255),
                usuario VARCHAR(100),
                INDEX idx_time (`Time`),
                INDEX idx_usuario (usuario)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            $checked = true;
        } catch (Exception $e) {
            error_log("Error asegurando tabla de logs: " . $e->getMessage());
        }
    }
}

if (!function_exists('registrarLogIngreso')) {
    function registrarLogIngreso($usuario, $clave_enmascarada, $details, $page = '') {
        try {
            ensureLogTable();

            $db = Database::getInstance();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            if ($ip === '::1') {
                $ip = '127.0.0.1';
            }
            if ($page === '') {
                $page = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? 'login';
            }
            $db->query(
                "INSERT INTO `log` (IP, `Time`, Details, `Page`, clave, usuario) VALUES (?, NOW(), ?, ?, ?, ?)",
                [$ip, $details, $page, $clave_enmascarada, $usuario]
            );
        } catch (Exception $e) {
            error_log("Error al registrar log de ingreso: " . $e->getMessage());
        }
    }
}
