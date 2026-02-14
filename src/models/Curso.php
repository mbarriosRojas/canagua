<?php

/**
 * Modelo para gestión de cursos
 * Sistema SACRGAPI - Gestión Administrativa
 */
class Curso {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todos los cursos
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT c.*, CONCAT(p.nombre, ' ', p.apellido) as instructor, p.cargo
                    FROM cursos c
                    LEFT JOIN personal p ON c.personal_id_personal = p.id_personal
                    WHERE c.activo = 1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $sql .= " AND (c.cod_curso LIKE ? OR c.nombre_curso LIKE ?)";
                $search = "%{$filters['search']}%";
                $params = [$search, $search];
            }
            
            if (!empty($filters['ano'])) {
                $sql .= " AND c.ano = ?";
                $params[] = $filters['ano'];
            }
            
            if (!empty($filters['periodo'])) {
                $sql .= " AND c.periodo = ?";
                $params[] = $filters['periodo'];
            }
            
            if (!empty($filters['instructor'])) {
                $sql .= " AND c.personal_id_personal = ?";
                $params[] = $filters['instructor'];
            }
            
            $sql .= " ORDER BY c.ano DESC, c.nombre_curso";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error obteniendo cursos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener curso por ID
     */
    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT c.*, CONCAT(p.nombre, ' ', p.apellido) as instructor, p.cargo
                 FROM cursos c
                 LEFT JOIN personal p ON c.personal_id_personal = p.id_personal
                 WHERE c.id_cursos = ? AND c.activo = 1",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo curso: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo curso
     */
    public function create($data) {
        try {
            // Verificar que el código de curso no exista
            $existing = $this->db->fetchOne(
                "SELECT id_cursos FROM cursos WHERE cod_curso = ?",
                [$data['cod_curso']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Ya existe un curso con este código'];
            }
            
            $id = $this->db->insert(
                "INSERT INTO cursos (personal_id_personal, cod_curso, nombre_curso, cedula_persona, 
                                    duracion, ano, num_de_clases, periodo) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['personal_id_personal'],
                    $data['cod_curso'],
                    $data['nombre_curso'],
                    $data['cedula_persona'] ?? null,
                    $data['duracion'] ?? null,
                    $data['ano'],
                    $data['num_de_clases'] ?? null,
                    $data['periodo']
                ]
            );
            
            return ['success' => true, 'id' => $id];
            
        } catch (Exception $e) {
            error_log("Error creando curso: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar curso
     */
    public function update($id, $data) {
        try {
            // Verificar que el curso existe
            $existing = $this->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Curso no encontrado'];
            }
            
            // Verificar que el código no esté en uso por otro curso
            $duplicate = $this->db->fetchOne(
                "SELECT id_cursos FROM cursos WHERE cod_curso = ? AND id_cursos != ?",
                [$data['cod_curso'], $id]
            );
            
            if ($duplicate) {
                return ['success' => false, 'message' => 'Ya existe otro curso con este código'];
            }
            
            $result = $this->db->update(
                "UPDATE cursos SET personal_id_personal = ?, cod_curso = ?, nombre_curso = ?, 
                                    cedula_persona = ?, duracion = ?, ano = ?, num_de_clases = ?, periodo = ? 
                 WHERE id_cursos = ?",
                [
                    $data['personal_id_personal'],
                    $data['cod_curso'],
                    $data['nombre_curso'],
                    $data['cedula_persona'] ?? null,
                    $data['duracion'] ?? null,
                    $data['ano'],
                    $data['num_de_clases'] ?? null,
                    $data['periodo'],
                    $id
                ]
            );
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error actualizando curso: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar curso (soft delete)
     */
    public function delete($id) {
        try {
            $result = $this->db->update(
                "UPDATE cursos SET activo = 0 WHERE id_cursos = ?",
                [$id]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Curso no encontrado'];
            }
            
        } catch (Exception $e) {
            error_log("Error eliminando curso: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener opciones para formularios
     */
    public function getFormOptions() {
        try {
            $instructores = $this->db->fetchAll(
                "SELECT id_personal, cedula_personal, nombre, apellido, cargo 
                 FROM personal WHERE activo = 1 ORDER BY apellido, nombre"
            );
            
            return ['instructores' => $instructores];
            
        } catch (Exception $e) {
            error_log("Error obteniendo opciones: " . $e->getMessage());
            return ['instructores' => []];
        }
    }
    
    /**
     * Obtener estadísticas de cursos
     */
    public function getStats() {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM cursos WHERE activo = 1");
            $porPeriodo = $this->db->fetchAll("SELECT periodo, COUNT(*) as cantidad FROM cursos WHERE activo = 1 GROUP BY periodo");
            $porAño = $this->db->fetchAll("SELECT ano, COUNT(*) as cantidad FROM cursos WHERE activo = 1 GROUP BY ano ORDER BY ano DESC");
            
            return [
                'total' => $total['total'],
                'por_periodo' => $porPeriodo,
                'por_año' => $porAño
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return ['total' => 0, 'por_periodo' => [], 'por_año' => []];
        }
    }
}
