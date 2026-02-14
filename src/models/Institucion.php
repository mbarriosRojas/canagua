<?php

require_once __DIR__ . '/../includes/Database.php';

class Institucion {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todas las instituciones
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT * FROM instituciones WHERE 1=1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $sql .= " AND (descripcion LIKE ? OR cod_institucion LIKE ? OR telefono_enlace LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY descripcion ASC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Error obteniendo instituciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener institución por ID
     */
    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT * FROM instituciones WHERE id_instituciones = ?",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo institución: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nueva institución
     */
    public function create($data) {
        try {
            // Verificar que el código no exista
            $existing = $this->db->fetchOne(
                "SELECT id_instituciones FROM instituciones WHERE cod_institucion = ?",
                [$data['cod_institucion']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'El código de la institución ya existe'];
            }
            
            $id = $this->db->insert(
                "INSERT INTO instituciones (cod_institucion, descripcion, telefono_enlace, datos_docente_enlace, activo) VALUES (?, ?, ?, ?, ?)",
                [
                    $data['cod_institucion'],
                    $data['descripcion'],
                    $data['telefono_enlace'] ?? null,
                    $data['datos_docente_enlace'] ?? null,
                    $data['activo'] ?? 1
                ]
            );
            
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            error_log("Error creando institución: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar institución
     */
    public function update($id, $data) {
        try {
            // Verificar que el código no exista en otra institución
            $existing = $this->db->fetchOne(
                "SELECT id_instituciones FROM instituciones WHERE cod_institucion = ? AND id_instituciones != ?",
                [$data['cod_institucion'], $id]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'El código de la institución ya existe'];
            }
            
            $result = $this->db->update(
                "UPDATE instituciones SET cod_institucion = ?, descripcion = ?, telefono_enlace = ?, datos_docente_enlace = ?, activo = ? WHERE id_instituciones = ?",
                [
                    $data['cod_institucion'],
                    $data['descripcion'],
                    $data['telefono_enlace'] ?? null,
                    $data['datos_docente_enlace'] ?? null,
                    $data['activo'] ?? 1,
                    $id
                ]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Institución no encontrada'];
            }
        } catch (Exception $e) {
            error_log("Error actualizando institución: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar institución
     */
    public function delete($id) {
        try {
            // Verificar si hay talleres asociados
            $talleres = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM talleres WHERE instituciones_id_instituciones = ?",
                [$id]
            );
            
            if ($talleres['count'] > 0) {
                return ['success' => false, 'message' => 'No se puede eliminar la institución porque tiene talleres asociados'];
            }
            
            $result = $this->db->delete(
                "DELETE FROM instituciones WHERE id_instituciones = ?",
                [$id]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Institución no encontrada'];
            }
        } catch (Exception $e) {
            error_log("Error eliminando institución: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener estadísticas
     */
    public function getStats() {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM instituciones");
            $activos = $this->db->fetchOne("SELECT COUNT(*) as activos FROM instituciones WHERE activo = 1");
            $inactivos = $this->db->fetchOne("SELECT COUNT(*) as inactivos FROM instituciones WHERE activo = 0");
            
            return [
                'total' => $total['total'],
                'activos' => $activos['activos'],
                'inactivos' => $inactivos['inactivos']
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de instituciones: " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }
    }
    
    /**
     * Obtener opciones para formularios
     */
    public function getFormOptions() {
        try {
            return $this->db->fetchAll(
                "SELECT id_instituciones, cod_institucion, descripcion FROM instituciones WHERE activo = 1 ORDER BY descripcion ASC"
            );
        } catch (Exception $e) {
            error_log("Error obteniendo opciones de instituciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estudiantes por institución
     */
    public function getEstudiantesByInstitucion($institucionId) {
        try {
            return $this->db->fetchAll(
                "SELECT DISTINCT e.* FROM estudiante e 
                 INNER JOIN talleres t ON e.id_estudiante IN (
                     SELECT p.estudiante_id_estudiante FROM participantes p 
                     WHERE p.talleres_id_taller IN (
                         SELECT id_taller FROM talleres WHERE instituciones_id_instituciones = ?
                     )
                 )
                 ORDER BY e.apellido, e.nombre",
                [$institucionId]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo estudiantes por institución: " . $e->getMessage());
            return [];
        }
    }
}
