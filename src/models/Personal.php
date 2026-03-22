<?php

/**
 * Modelo para gestión de personal
 * Sistema SACRGAPI - Gestión Administrativa
 */
class Personal {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todo el personal
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT * FROM personal WHERE activo = 1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $sql .= " AND (cedula_personal LIKE ? OR nombre LIKE ? OR apellido LIKE ? OR cargo LIKE ?)";
                $search = "%{$filters['search']}%";
                $params = [$search, $search, $search, $search];
            }
            
            if (!empty($filters['cargo'])) {
                $sql .= " AND cargo LIKE ?";
                $params[] = "%{$filters['cargo']}%";
            }
            
            $sql .= " ORDER BY apellido, nombre";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error obteniendo personal: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener personal por ID
     */
    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT * FROM personal WHERE id_personal = ? AND activo = 1",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo personal: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo personal
     */
    public function create($data) {
        try {
            // Verificar que la cédula no exista
            $existing = $this->db->fetchOne(
                "SELECT id_personal FROM personal WHERE cedula_personal = ?",
                [$data['cedula_personal']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Ya existe personal con esta cédula'];
            }
            
            $id = $this->db->insert(
                "INSERT INTO personal (cedula_personal, apellido, nombre, cargo, fecha_ingreso) 
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $data['cedula_personal'],
                    $data['apellido'],
                    $data['nombre'],
                    $data['cargo'],
                    $data['fecha_ingreso']
                ]
            );
            
            return ['success' => true, 'id' => $id];
            
        } catch (Exception $e) {
            error_log("Error creando personal: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar personal
     */
    public function update($id, $data) {
        try {
            // Verificar que el personal existe
            $existing = $this->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Personal no encontrado'];
            }
            
            // Verificar que la cédula no esté en uso por otro personal
            $duplicate = $this->db->fetchOne(
                "SELECT id_personal FROM personal WHERE cedula_personal = ? AND id_personal != ?",
                [$data['cedula_personal'], $id]
            );
            
            if ($duplicate) {
                return ['success' => false, 'message' => 'Ya existe otro personal con esta cédula'];
            }
            
            $result = $this->db->update(
                "UPDATE personal SET cedula_personal = ?, apellido = ?, nombre = ?, cargo = ?, fecha_ingreso = ? 
                 WHERE id_personal = ?",
                [
                    $data['cedula_personal'],
                    $data['apellido'],
                    $data['nombre'],
                    $data['cargo'],
                    $data['fecha_ingreso'],
                    $id
                ]
            );
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error actualizando personal: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar personal (soft delete)
     */
    public function delete($id) {
        try {
            $result = $this->db->update(
                "UPDATE personal SET activo = 0 WHERE id_personal = ?",
                [$id]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Personal no encontrado'];
            }
            
        } catch (Exception $e) {
            error_log("Error eliminando personal: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener estadísticas de personal
     */
    public function getStats() {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM personal WHERE activo = 1");
            $porCargo = $this->db->fetchAll("SELECT cargo, COUNT(*) as cantidad FROM personal WHERE activo = 1 GROUP BY cargo ORDER BY cantidad DESC");
            
            return [
                'total' => $total['total'],
                'por_cargo' => $porCargo
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return ['total' => 0, 'por_cargo' => []];
        }
    }
    
    /**
     * Datos para reportes (filtros: search, fecha_desde, fecha_hasta)
     */
    public function getForReport($filters = []) {
        try {
            $sql = "SELECT * FROM personal WHERE 1=1";
            $params = [];
            if (!empty($filters['search'])) {
                $sql .= " AND (cedula_personal LIKE ? OR nombre LIKE ? OR apellido LIKE ? OR cargo LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
            }
            if (!empty($filters['fecha_desde'])) {
                $sql .= " AND DATE(fecha_creacion) >= ?";
                $params[] = $filters['fecha_desde'];
            }
            if (!empty($filters['fecha_hasta'])) {
                $sql .= " AND DATE(fecha_creacion) <= ?";
                $params[] = $filters['fecha_hasta'];
            }
            $sql .= " ORDER BY apellido, nombre";
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Error getForReport personal: " . $e->getMessage());
            return [];
        }
    }
}
