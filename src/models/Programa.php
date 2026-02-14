<?php

require_once __DIR__ . '/../includes/Database.php';

class Programa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todos los programas
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT * FROM programas WHERE 1=1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $sql .= " AND (descripcion LIKE ? OR cod_programas LIKE ? OR sub_area LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY descripcion ASC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Error obteniendo programas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener programa por ID
     */
    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT * FROM programas WHERE id_programas = ?",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo programa: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo programa
     */
    public function create($data) {
        try {
            // Verificar que el código no exista
            $existing = $this->db->fetchOne(
                "SELECT id_programas FROM programas WHERE cod_programas = ?",
                [$data['cod_programas']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'El código del programa ya existe'];
            }
            
            $id = $this->db->insert(
                "INSERT INTO programas (cod_programas, descripcion, sub_area, activo) VALUES (?, ?, ?, ?)",
                [
                    $data['cod_programas'],
                    $data['descripcion'],
                    $data['sub_area'] ?? null,
                    $data['activo'] ?? 1
                ]
            );
            
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            error_log("Error creando programa: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar programa
     */
    public function update($id, $data) {
        try {
            // Verificar que el código no exista en otro programa
            $existing = $this->db->fetchOne(
                "SELECT id_programas FROM programas WHERE cod_programas = ? AND id_programas != ?",
                [$data['cod_programas'], $id]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'El código del programa ya existe'];
            }
            
            $result = $this->db->update(
                "UPDATE programas SET cod_programas = ?, descripcion = ?, sub_area = ?, activo = ? WHERE id_programas = ?",
                [
                    $data['cod_programas'],
                    $data['descripcion'],
                    $data['sub_area'] ?? null,
                    $data['activo'] ?? 1,
                    $id
                ]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Programa no encontrado'];
            }
        } catch (Exception $e) {
            error_log("Error actualizando programa: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar programa
     */
    public function delete($id) {
        try {
            // Verificar si hay talleres asociados
            $talleres = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM talleres WHERE programas_id_programas = ?",
                [$id]
            );
            
            if ($talleres['count'] > 0) {
                return ['success' => false, 'message' => 'No se puede eliminar el programa porque tiene talleres asociados'];
            }
            
            $result = $this->db->delete(
                "DELETE FROM programas WHERE id_programas = ?",
                [$id]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Programa no encontrado'];
            }
        } catch (Exception $e) {
            error_log("Error eliminando programa: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener estadísticas
     */
    public function getStats() {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM programas");
            $activos = $this->db->fetchOne("SELECT COUNT(*) as activos FROM programas WHERE activo = 1");
            $inactivos = $this->db->fetchOne("SELECT COUNT(*) as inactivos FROM programas WHERE activo = 0");
            
            return [
                'total' => $total['total'],
                'activos' => $activos['activos'],
                'inactivos' => $inactivos['inactivos']
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de programas: " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }
    }
    
    /**
     * Obtener opciones para formularios
     */
    public function getFormOptions() {
        try {
            return $this->db->fetchAll(
                "SELECT id_programas, cod_programas, descripcion FROM programas WHERE activo = 1 ORDER BY descripcion ASC"
            );
        } catch (Exception $e) {
            error_log("Error obteniendo opciones de programas: " . $e->getMessage());
            return [];
        }
    }
}
