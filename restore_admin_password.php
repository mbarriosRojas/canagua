<?php

/**
 * Restaura el password del usuario admin@sacrgapi.com con el hash del respaldo.
 * Ejecutar una vez: php restore_admin_password.php
 * o desde el navegador: http://localhost:8000/../restore_admin_password.php (ajustar ruta)
 */

require_once __DIR__ . '/src/includes/Config.php';
require_once __DIR__ . '/src/includes/Database.php';

Config::load();

// Hash del respaldo backup_2026-03-03_101652.sql para usuario admin
$passwordHashFromBackup = '$2y$10$oZvMv/mqns.gnBi1w/QiF.uF/guZ/8gbC8PIjzu4f0k2bLqBBggD2';

try {
    $db = Database::getInstance();
    $result = $db->update(
        "UPDATE usuarios SET password_hash = ? WHERE email = ?",
        [$passwordHashFromBackup, 'admin@sacrgapi.com']
    );
    if ($result > 0) {
        echo "OK: Usuario admin@sacrgapi.com actualizado con la clave del respaldo.\n";
    } else {
        echo "AVISO: No se encontró ningún usuario con email admin@sacrgapi.com (0 filas actualizadas).\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
