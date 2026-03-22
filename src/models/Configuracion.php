<?php

/**
 * Modelo para configuración general del sistema
 */
class Configuracion {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    /**
     * Asegurar que la tabla configuracion exista
     */
    private function ensureTable(): void {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS configuracion (
                    id_configuracion INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(150) NOT NULL,
                    rif VARCHAR(20) NOT NULL,
                    direccion TEXT NULL,
                    telefono VARCHAR(30) NULL,
                    email VARCHAR(150) NULL,
                    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (Exception $e) {
            error_log('Error asegurando tabla configuracion: ' . $e->getMessage());
        }
    }

    /**
     * Obtener configuración (única fila). Si no existe, crearla con valores por defecto.
     */
    public function get(): array {
        try {
            $row = $this->db->fetchOne("SELECT * FROM configuracion ORDER BY id_configuracion ASC LIMIT 1");
            if ($row) {
                return $row;
            }

            $this->db->insert(
                "INSERT INTO configuracion (nombre, rif, direccion, telefono, email) VALUES (?, ?, ?, ?, ?)",
                ['Nombre de la Institución', 'J-00000000-0', null, null, null]
            );

            $row = $this->db->fetchOne("SELECT * FROM configuracion ORDER BY id_configuracion ASC LIMIT 1");
            return $row ?: [];
        } catch (Exception $e) {
            error_log('Error obteniendo configuracion: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar configuración
     */
    public function update(array $data): array {
        try {
            $current = $this->get();
            $id = $current['id_configuracion'] ?? null;
            if (!$id) {
                return ['success' => false, 'message' => 'No se encontró la configuración a actualizar'];
            }

            $this->db->update(
                "UPDATE configuracion SET nombre = ?, rif = ?, direccion = ?, telefono = ?, email = ? WHERE id_configuracion = ?",
                [
                    $data['nombre'],
                    $data['rif'],
                    $data['direccion'] ?? null,
                    $data['telefono'] ?? null,
                    $data['email'] ?? null,
                    $id
                ]
            );

            return ['success' => true];
        } catch (Exception $e) {
            error_log('Error actualizando configuracion: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al actualizar la configuración'];
        }
    }
}

