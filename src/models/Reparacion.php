<?php

/**
 * Modelo para gestión de reparaciones de inventario
 * Sistema SACRGAPI - Gestión Administrativa
 */
class Reparacion {
    private $db;
    private static $tableExists = null;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Comprueba si la tabla reparaciones existe
     */
    private function tableExists() {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }
        try {
            $r = $this->db->fetchOne(
                "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reparaciones'"
            );
            self::$tableExists = (bool) $r;
            return self::$tableExists;
        } catch (Exception $e) {
            self::$tableExists = false;
            return false;
        }
    }
    
    /**
     * Crea la tabla reparaciones si no existe (para bases instaladas antes del cambio)
     */
    private function ensureTable() {
        if ($this->tableExists()) {
            return true;
        }
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS reparaciones (
                    id_reparacion INT AUTO_INCREMENT PRIMARY KEY,
                    inventario_id_inventario INT NOT NULL,
                    fecha DATE NOT NULL,
                    motivo TEXT,
                    FOREIGN KEY (inventario_id_inventario) REFERENCES inventario(id_inventario)
                )
            ");
            self::$tableExists = true;
            return true;
        } catch (Exception $e) {
            error_log("Error creando tabla reparaciones: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar una reparación
     */
    public function create($data) {
        try {
            $this->ensureTable();
            
            $this->db->insert(
                "INSERT INTO reparaciones (inventario_id_inventario, fecha, motivo) VALUES (?, ?, ?)",
                [
                    $data['inventario_id_inventario'],
                    $data['fecha'],
                    $data['motivo'] ?? null
                ]
            );
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error registrando reparación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar la reparación'];
        }
    }
    
    /**
     * Obtener reparaciones de un ítem de inventario
     */
    public function getByInventarioId($id) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM reparaciones WHERE inventario_id_inventario = ? ORDER BY fecha DESC",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo reparaciones: " . $e->getMessage());
            return [];
        }
    }
}
