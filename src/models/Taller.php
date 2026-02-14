<?php

/**
 * Modelo para gestión de talleres
 * Sistema SACRGAPI - Gestión Administrativa
 */
class Taller {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todos los talleres
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT t.*, p.descripcion as programa, i.descripcion as institucion, 
                           CONCAT(per.nombre, ' ', per.apellido) as instructor
                    FROM talleres t
                    LEFT JOIN programas p ON t.programas_id_programas = p.id_programas
                    LEFT JOIN instituciones i ON t.instituciones_id_instituciones = i.id_instituciones
                    LEFT JOIN personal per ON t.personal_id_personal = per.id_personal
                    WHERE t.activo = 1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $sql .= " AND (t.cod_taller LIKE ? OR t.grado LIKE ? OR t.seccion LIKE ?)";
                $search = "%{$filters['search']}%";
                $params = [$search, $search, $search];
            }
            
            if (!empty($filters['ano_escolar'])) {
                $sql .= " AND t.ano_escolar = ?";
                $params[] = $filters['ano_escolar'];
            }
            
            if (!empty($filters['lapso'])) {
                $sql .= " AND t.lapso = ?";
                $params[] = $filters['lapso'];
            }
            
            if (!empty($filters['programa'])) {
                $sql .= " AND t.programas_id_programas = ?";
                $params[] = $filters['programa'];
            }
            
            $sql .= " ORDER BY t.ano_escolar DESC, t.grado, t.seccion";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error obteniendo talleres: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener taller por ID
     */
    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT t.*, p.descripcion as programa, i.descripcion as institucion, 
                        CONCAT(per.nombre, ' ', per.apellido) as instructor
                 FROM talleres t
                 LEFT JOIN programas p ON t.programas_id_programas = p.id_programas
                 LEFT JOIN instituciones i ON t.instituciones_id_instituciones = i.id_instituciones
                 LEFT JOIN personal per ON t.personal_id_personal = per.id_personal
                 WHERE t.id_taller = ? AND t.activo = 1",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo taller: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo taller
     */
    public function create($data) {
        try {
            // Verificar que el código de taller no exista
            $existing = $this->db->fetchOne(
                "SELECT id_taller FROM talleres WHERE cod_taller = ?",
                [$data['cod_taller']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Ya existe un taller con este código'];
            }
            
            $id = $this->db->insert(
                "INSERT INTO talleres (programas_id_programas, instituciones_id_instituciones, personal_id_personal, 
                                       cod_taller, ano_escolar, grado, seccion, lapso) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['programas_id_programas'],
                    $data['instituciones_id_instituciones'],
                    $data['personal_id_personal'],
                    $data['cod_taller'],
                    $data['ano_escolar'],
                    $data['grado'] ?? null,
                    $data['seccion'] ?? null,
                    $data['lapso']
                ]
            );
            
            return ['success' => true, 'id' => $id];
            
        } catch (Exception $e) {
            error_log("Error creando taller: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar taller
     */
    public function update($id, $data) {
        try {
            // Verificar que el taller existe
            $existing = $this->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Taller no encontrado'];
            }
            
            // Verificar que el código no esté en uso por otro taller
            $duplicate = $this->db->fetchOne(
                "SELECT id_taller FROM talleres WHERE cod_taller = ? AND id_taller != ?",
                [$data['cod_taller'], $id]
            );
            
            if ($duplicate) {
                return ['success' => false, 'message' => 'Ya existe otro taller con este código'];
            }
            
            $result = $this->db->update(
                "UPDATE talleres SET programas_id_programas = ?, instituciones_id_instituciones = ?, 
                                      personal_id_personal = ?, cod_taller = ?, ano_escolar = ?, grado = ?, 
                                      seccion = ?, lapso = ? 
                 WHERE id_taller = ?",
                [
                    $data['programas_id_programas'],
                    $data['instituciones_id_instituciones'],
                    $data['personal_id_personal'],
                    $data['cod_taller'],
                    $data['ano_escolar'],
                    $data['grado'] ?? null,
                    $data['seccion'] ?? null,
                    $data['lapso'],
                    $id
                ]
            );
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error actualizando taller: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar taller (soft delete)
     */
    public function delete($id) {
        try {
            $result = $this->db->update(
                "UPDATE talleres SET activo = 0 WHERE id_taller = ?",
                [$id]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Taller no encontrado'];
            }
            
        } catch (Exception $e) {
            error_log("Error eliminando taller: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener opciones para formularios
     */
    public function getFormOptions() {
        try {
            $programas = $this->db->fetchAll("SELECT id_programas, descripcion FROM programas WHERE activo = 1 ORDER BY descripcion");
            $instituciones = $this->db->fetchAll("SELECT id_instituciones, descripcion FROM instituciones WHERE activo = 1 ORDER BY descripcion");
            $personal = $this->db->fetchAll("SELECT id_personal, cedula_personal, nombre, apellido FROM personal WHERE activo = 1 ORDER BY apellido, nombre");
            
            return [
                'programas' => $programas,
                'instituciones' => $instituciones,
                'personal' => $personal
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo opciones: " . $e->getMessage());
            return ['programas' => [], 'instituciones' => [], 'personal' => []];
        }
    }
    
    /**
     * Obtener estadísticas de talleres
     */
    public function getStats() {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM talleres WHERE activo = 1");
            $porLapso = $this->db->fetchAll("SELECT lapso, COUNT(*) as cantidad FROM talleres WHERE activo = 1 GROUP BY lapso");
            $porAño = $this->db->fetchAll("SELECT ano_escolar, COUNT(*) as cantidad FROM talleres WHERE activo = 1 GROUP BY ano_escolar ORDER BY ano_escolar DESC");
            
            return [
                'total' => $total['total'],
                'por_lapso' => $porLapso,
                'por_año' => $porAño
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return ['total' => 0, 'por_lapso' => [], 'por_año' => []];
        }
    }
    
    /**
     * Agregar estudiante a un taller
     */
    public function addStudent($tallerId, $estudianteId) {
        try {
            // Verificar que el taller existe
            $taller = $this->getById($tallerId);
            if (!$taller) {
                return ['success' => false, 'message' => 'Taller no encontrado'];
            }
            
            // Verificar que el estudiante existe
            $estudiante = $this->db->fetchOne(
                "SELECT id_estudiante, instituciones_id_instituciones FROM estudiante WHERE id_estudiante = ? AND activo = 1",
                [$estudianteId]
            );
            
            if (!$estudiante) {
                return ['success' => false, 'message' => 'Estudiante no encontrado'];
            }
            
            // Verificar que el estudiante pertenece a la misma institución del taller
            if ($estudiante['instituciones_id_instituciones'] != $taller['instituciones_id_instituciones']) {
                return ['success' => false, 'message' => 'El estudiante no pertenece a la institución del taller'];
            }
            
            // Verificar que no esté ya inscrito
            $existing = $this->db->fetchOne(
                "SELECT id_estudiante_taller FROM estudiante_taller WHERE estudiante_id_estudiante = ? AND talleres_id_taller = ?",
                [$estudianteId, $tallerId]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'El estudiante ya está inscrito en este taller'];
            }
            
            // Inscribir al estudiante
            $id = $this->db->insert(
                "INSERT INTO estudiante_taller (estudiante_id_estudiante, talleres_id_taller, instituciones_id_instituciones) VALUES (?, ?, ?)",
                [$estudianteId, $tallerId, $taller['instituciones_id_instituciones']]
            );
            
            return ['success' => true, 'id' => $id];
            
        } catch (Exception $e) {
            error_log("Error agregando estudiante al taller: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener estudiantes de un taller
     */
    public function getStudents($tallerId) {
        try {
            return $this->db->fetchAll(
                "SELECT et.*, e.cedula_estudiante, e.nombre, e.apellido, e.instituciones_id_instituciones,
                        c.momento_i_numeral, c.momento_i_literal, c.momento_ii_numeral, c.momento_ii_literal,
                        c.momento_iii_numeral, c.momento_iii_literal, c.promedio_final, c.literal_final
                 FROM estudiante_taller et
                 INNER JOIN estudiante e ON et.estudiante_id_estudiante = e.id_estudiante
                 LEFT JOIN calificaciones c ON e.id_estudiante = c.estudiante_id_estudiante AND c.talleres_id_taller = ?
                 WHERE et.talleres_id_taller = ? AND et.activo = 1
                 ORDER BY e.apellido, e.nombre",
                [$tallerId, $tallerId]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo estudiantes del taller: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Remover estudiante de un taller
     */
    public function removeStudent($tallerId, $estudianteId) {
        try {
            $result = $this->db->update(
                "UPDATE estudiante_taller SET activo = 0 WHERE talleres_id_taller = ? AND estudiante_id_estudiante = ?",
                [$tallerId, $estudianteId]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Estudiante no encontrado en el taller'];
            }
        } catch (Exception $e) {
            error_log("Error removiendo estudiante del taller: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener talleres con conteo de estudiantes
     */
    public function getAllWithStudentCount($filters = []) {
        try {
            $sql = "SELECT t.*, p.descripcion as programa, i.descripcion as institucion, 
                           CONCAT(per.nombre, ' ', per.apellido) as instructor,
                           COUNT(et.id_estudiante_taller) as estudiantes_count
                    FROM talleres t
                    LEFT JOIN programas p ON t.programas_id_programas = p.id_programas
                    LEFT JOIN instituciones i ON t.instituciones_id_instituciones = i.id_instituciones
                    LEFT JOIN personal per ON t.personal_id_personal = per.id_personal
                    LEFT JOIN estudiante_taller et ON t.id_taller = et.talleres_id_taller AND et.activo = 1
                    WHERE t.activo = 1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $sql .= " AND (t.cod_taller LIKE ? OR t.grado LIKE ? OR t.seccion LIKE ?)";
                $search = "%{$filters['search']}%";
                $params = [$search, $search, $search];
            }
            
            if (!empty($filters['ano_escolar'])) {
                $sql .= " AND t.ano_escolar = ?";
                $params[] = $filters['ano_escolar'];
            }
            
            if (!empty($filters['lapso'])) {
                $sql .= " AND t.lapso = ?";
                $params[] = $filters['lapso'];
            }
            
            if (!empty($filters['programa'])) {
                $sql .= " AND t.programas_id_programas = ?";
                $params[] = $filters['programa'];
            }
            
            $sql .= " GROUP BY t.id_taller ORDER BY t.ano_escolar DESC, t.grado, t.seccion";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error obteniendo talleres con conteo: " . $e->getMessage());
            return [];
        }
    }
}
