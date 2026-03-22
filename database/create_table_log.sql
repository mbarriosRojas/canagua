-- Tabla de logs/trazas de ingreso (SAAA). Ejecutar si la BD ya existía sin esta tabla.
USE sacrgapi_database;

CREATE TABLE IF NOT EXISTS `log` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    IP VARCHAR(45),
    `Time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    Details TEXT,
    `Page` VARCHAR(255),
    clave VARCHAR(255),
    usuario VARCHAR(100),
    INDEX idx_time (`Time`),
    INDEX idx_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
