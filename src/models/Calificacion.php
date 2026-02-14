<?php

require_once __DIR__ . '/../includes/Database.php';

class Calificacion {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todas las calificaciones
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT c.*, e.cedula_estudiante, e.nombre, e.apellido, 
                           t.cod_taller, p.descripcion as programa_descripcion,
                           i.descripcion as institucion_descripcion
                    FROM calificaciones c
                    INNER JOIN estudiante e ON c.estudiante_id_estudiante = e.id_estudiante
                    INNER JOIN talleres t ON c.cod_taller = t.cod_taller
                    INNER JOIN programas p ON t.programas_id_programas = p.id_programas
                    INNER JOIN instituciones i ON t.instituciones_id_instituciones = i.id_instituciones
                    WHERE 1=1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $sql .= " AND (e.cedula_estudiante LIKE ? OR e.nombre LIKE ? OR e.apellido LIKE ? OR c.cod_taller LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['institucion_id'])) {
                $sql .= " AND i.id_instituciones = ?";
                $params[] = $filters['institucion_id'];
            }
            
            if (!empty($filters['estudiante_id'])) {
                $sql .= " AND e.id_estudiante = ?";
                $params[] = $filters['estudiante_id'];
            }
            
            if (!empty($filters['cod_taller'])) {
                $sql .= " AND c.cod_taller = ?";
                $params[] = $filters['cod_taller'];
            }
            
            $sql .= " ORDER BY e.apellido, e.nombre, c.cod_taller";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Error obteniendo calificaciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener calificación por ID
     */
    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT c.*, e.cedula_estudiante, e.nombre, e.apellido,
                        t.cod_taller, p.descripcion as programa_descripcion,
                        i.descripcion as institucion_descripcion
                 FROM calificaciones c
                 INNER JOIN estudiante e ON c.estudiante_id_estudiante = e.id_estudiante
                 INNER JOIN talleres t ON c.cod_taller = t.cod_taller
                 INNER JOIN programas p ON t.programas_id_programas = p.id_programas
                 INNER JOIN instituciones i ON t.instituciones_id_instituciones = i.id_instituciones
                 WHERE c.id_calificaciones = ?",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo calificación: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nueva calificación
     */
    public function create($data) {
        try {
            // Verificar que el estudiante existe
            $estudiante = $this->db->fetchOne(
                "SELECT id_estudiante FROM estudiante WHERE id_estudiante = ?",
                [$data['estudiante_id_estudiante']]
            );
            
            if (!$estudiante) {
                return ['success' => false, 'message' => 'Estudiante no encontrado'];
            }
            
            // Verificar que el taller existe
            $taller = $this->db->fetchOne(
                "SELECT id_taller FROM talleres WHERE cod_taller = ?",
                [$data['cod_taller']]
            );
            
            if (!$taller) {
                return ['success' => false, 'message' => 'Taller no encontrado'];
            }
            
            // Verificar que no existe ya una calificación para este estudiante y taller
            $existing = $this->db->fetchOne(
                "SELECT id_calificaciones FROM calificaciones WHERE estudiante_id_estudiante = ? AND cod_taller = ?",
                [$data['estudiante_id_estudiante'], $data['cod_taller']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Ya existe una calificación para este estudiante en este taller'];
            }
            
            $id = $this->db->insert(
                "INSERT INTO calificaciones (estudiante_id_estudiante, cedula_estudiante, cod_taller, literal, numeral, momento_i, momento_ii, momento_iii) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['estudiante_id_estudiante'],
                    $data['cedula_estudiante'],
                    $data['cod_taller'],
                    $data['literal'] ?? null,
                    $data['numeral'] ?? null,
                    $data['momento_i'] ?? null,
                    $data['momento_ii'] ?? null,
                    $data['momento_iii'] ?? null
                ]
            );
            
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            error_log("Error creando calificación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar calificación
     */
    public function update($id, $data) {
        try {
            $result = $this->db->update(
                "UPDATE calificaciones SET literal = ?, numeral = ?, momento_i = ?, momento_ii = ?, momento_iii = ? WHERE id_calificaciones = ?",
                [
                    $data['literal'] ?? null,
                    $data['numeral'] ?? null,
                    $data['momento_i'] ?? null,
                    $data['momento_ii'] ?? null,
                    $data['momento_iii'] ?? null,
                    $id
                ]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Calificación no encontrada'];
            }
        } catch (Exception $e) {
            error_log("Error actualizando calificación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar calificación
     */
    public function delete($id) {
        try {
            $result = $this->db->delete(
                "DELETE FROM calificaciones WHERE id_calificaciones = ?",
                [$id]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Calificación no encontrada'];
            }
        } catch (Exception $e) {
            error_log("Error eliminando calificación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener estadísticas
     */
    public function getStats() {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM calificaciones");
            $conNotas = $this->db->fetchOne("SELECT COUNT(*) as con_notas FROM calificaciones WHERE momento_i IS NOT NULL OR momento_ii IS NOT NULL OR momento_iii IS NOT NULL");
            $aprobados = $this->db->fetchOne("SELECT COUNT(*) as aprobados FROM calificaciones WHERE literal = 'A' OR literal = 'B'");
            $reprobados = $this->db->fetchOne("SELECT COUNT(*) as reprobados FROM calificaciones WHERE literal = 'E'");
            
            return [
                'total' => $total['total'],
                'con_notas' => $conNotas['con_notas'],
                'aprobados' => $aprobados['aprobados'],
                'reprobados' => $reprobados['reprobados']
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de calificaciones: " . $e->getMessage());
            return ['total' => 0, 'con_notas' => 0, 'aprobados' => 0, 'reprobados' => 0];
        }
    }
    
    /**
     * Obtener opciones para formularios
     */
    public function getFormOptions() {
        try {
            $estudiantes = $this->db->fetchAll(
                "SELECT id_estudiante, cedula_estudiante, nombre, apellido FROM estudiante WHERE activo = 1 ORDER BY apellido, nombre"
            );
            
            $talleres = $this->db->fetchAll(
                "SELECT DISTINCT cod_taller FROM talleres ORDER BY cod_taller"
            );
            
            return [
                'estudiantes' => $estudiantes,
                'talleres' => $talleres
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo opciones de calificaciones: " . $e->getMessage());
            return ['estudiantes' => [], 'talleres' => []];
        }
    }
    
    /**
     * Obtener calificaciones por institución
     */
    public function getByInstitucion($institucionId) {
        try {
            return $this->db->fetchAll(
                "SELECT c.*, e.cedula_estudiante, e.nombre, e.apellido,
                        t.cod_taller, p.descripcion as programa_descripcion
                 FROM calificaciones c
                 INNER JOIN estudiante e ON c.estudiante_id_estudiante = e.id_estudiante
                 INNER JOIN talleres t ON c.cod_taller = t.cod_taller
                 INNER JOIN programas p ON t.programas_id_programas = p.id_programas
                 WHERE t.instituciones_id_instituciones = ?
                 ORDER BY e.apellido, e.nombre",
                [$institucionId]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo calificaciones por institución: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener calificaciones por estudiante
     */
    public function getByEstudiante($estudianteId) {
        try {
            return $this->db->fetchAll(
                "SELECT c.*, t.cod_taller, p.descripcion as programa_descripcion,
                        i.descripcion as institucion_descripcion
                 FROM calificaciones c
                 INNER JOIN talleres t ON c.cod_taller = t.cod_taller
                 INNER JOIN programas p ON t.programas_id_programas = p.id_programas
                 INNER JOIN instituciones i ON t.instituciones_id_instituciones = i.id_instituciones
                 WHERE c.estudiante_id_estudiante = ?
                 ORDER BY t.cod_taller",
                [$estudianteId]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo calificaciones por estudiante: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener calificaciones por estudiante y taller
     */
    public function getByEstudianteTaller($estudianteId, $tallerId) {
        try {
            return $this->db->fetchOne(
                "SELECT c.*, e.cedula_estudiante, e.nombre, e.apellido,
                        t.cod_taller, p.descripcion as programa_descripcion,
                        i.descripcion as institucion_descripcion
                 FROM calificaciones c
                 INNER JOIN estudiante e ON c.estudiante_id_estudiante = e.id_estudiante
                 INNER JOIN talleres t ON c.talleres_id_taller = t.id_taller
                 INNER JOIN programas p ON t.programas_id_programas = p.id_programas
                 INNER JOIN instituciones i ON t.instituciones_id_instituciones = i.id_instituciones
                 WHERE c.estudiante_id_estudiante = ? AND c.talleres_id_taller = ?",
                [$estudianteId, $tallerId]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo calificaciones por estudiante y taller: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Guardar o actualizar calificaciones
     */
    public function saveGrades($data) {
        try {
            // Normalizar datos: convertir strings vacíos a null y castear números
            $normalize = function ($value) {
                if (!isset($value)) return null;
                if ($value === '') return null;
                return $value;
            };

            $estudianteId = (int)$data['estudiante_id'];
            $tallerId = (int)$data['taller_id'];

            $data['momento_i_numeral'] = $normalize($data['momento_i_numeral'] ?? null);
            $data['momento_i_literal'] = $normalize($data['momento_i_literal'] ?? null);
            $data['momento_ii_numeral'] = $normalize($data['momento_ii_numeral'] ?? null);
            $data['momento_ii_literal'] = $normalize($data['momento_ii_literal'] ?? null);
            $data['momento_iii_numeral'] = $normalize($data['momento_iii_numeral'] ?? null);
            $data['momento_iii_literal'] = $normalize($data['momento_iii_literal'] ?? null);
            $data['promedio_final'] = $normalize($data['promedio_final'] ?? null);
            $data['literal_final'] = $normalize($data['literal_final'] ?? null);
            
            // Verificar si ya existen calificaciones
            $existing = $this->getByEstudianteTaller($estudianteId, $tallerId);
            
            if ($existing) {
                // Actualizar calificaciones existentes
                $result = $this->db->update(
                    "UPDATE calificaciones SET 
                        momento_i_numeral = ?, momento_i_literal = ?,
                        momento_ii_numeral = ?, momento_ii_literal = ?,
                        momento_iii_numeral = ?, momento_iii_literal = ?,
                        promedio_final = ?, literal_final = ?,
                        fecha_modificacion = CURRENT_TIMESTAMP
                     WHERE estudiante_id_estudiante = ? AND talleres_id_taller = ?",
                    [
                        $data['momento_i_numeral'],
                        $data['momento_i_literal'],
                        $data['momento_ii_numeral'],
                        $data['momento_ii_literal'],
                        $data['momento_iii_numeral'],
                        $data['momento_iii_literal'],
                        $data['promedio_final'],
                        $data['literal_final'],
                        $estudianteId,
                        $tallerId
                    ]
                );
                
                if ($result > 0) {
                    return ['success' => true, 'message' => 'Calificaciones actualizadas exitosamente'];
                } else {
                    return ['success' => false, 'message' => 'No se pudieron actualizar las calificaciones'];
                }
            } else {
                // Crear nuevas calificaciones
                // Primero necesitamos obtener información del taller y estudiante
                $taller = $this->db->fetchOne(
                    "SELECT cod_taller, instituciones_id_instituciones FROM talleres WHERE id_taller = ?",
                    [$tallerId]
                );
                
                $estudiante = $this->db->fetchOne(
                    "SELECT cedula_estudiante FROM estudiante WHERE id_estudiante = ?",
                    [$estudianteId]
                );
                
                if (!$taller || !$estudiante) {
                    return ['success' => false, 'message' => 'Taller o estudiante no encontrado'];
                }
                
                $id = $this->db->insert(
                    "INSERT INTO calificaciones 
                     (estudiante_id_estudiante, cedula_estudiante, cod_taller, talleres_id_taller, 
                      instituciones_id_instituciones, momento_i_numeral, momento_i_literal,
                      momento_ii_numeral, momento_ii_literal, momento_iii_numeral, momento_iii_literal,
                      promedio_final, literal_final) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $estudianteId,
                        $estudiante['cedula_estudiante'],
                        $taller['cod_taller'],
                        $tallerId,
                        $taller['instituciones_id_instituciones'],
                        $data['momento_i_numeral'],
                        $data['momento_i_literal'],
                        $data['momento_ii_numeral'],
                        $data['momento_ii_literal'],
                        $data['momento_iii_numeral'],
                        $data['momento_iii_literal'],
                        $data['promedio_final'],
                        $data['literal_final']
                    ]
                );
                
                return ['success' => true, 'id' => $id, 'message' => 'Calificaciones creadas exitosamente'];
            }
            
        } catch (Exception $e) {
            error_log("Error guardando calificaciones: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
}
