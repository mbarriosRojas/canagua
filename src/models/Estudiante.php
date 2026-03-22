<?php

/**
 * Modelo para gestión de estudiantes
 * Sistema SACRGAPI - Gestión Administrativa
 */
class Estudiante {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todos los estudiantes con paginación
     */
    public function getAll($filters = []) {
        try {
            $page = isset($filters['page']) ? (int)$filters['page'] : 1;
            $limit = isset($filters['limit']) ? (int)$filters['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Construir WHERE clause
            $whereConditions = ["e.activo = 1"];
            $params = [];
            
            // Filtro por institución
            if (!empty($filters['institucion'])) {
                $whereConditions[] = "e.instituciones_id_instituciones = ?";
                $params[] = $filters['institucion'];
            }
            
            // Filtro por nombre específico
            if (!empty($filters['nombre'])) {
                $whereConditions[] = "(e.nombre LIKE ? OR e.apellido LIKE ?)";
                $nombre = "%{$filters['nombre']}%";
                $params[] = $nombre;
                $params[] = $nombre;
            }
            
            // Filtro por cédula específica
            if (!empty($filters['cedula'])) {
                $whereConditions[] = "e.cedula_estudiante LIKE ?";
                $cedula = "%{$filters['cedula']}%";
                $params[] = $cedula;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Query para obtener el total de registros
            $countSql = "SELECT COUNT(*) as total 
                        FROM estudiante e 
                        LEFT JOIN instituciones i ON e.instituciones_id_instituciones = i.id_instituciones 
                        WHERE {$whereClause}";
            
            $totalResult = $this->db->fetchOne($countSql, $params);
            $total = $totalResult['total'];
            
            // Query para obtener los datos paginados
            $sql = "SELECT e.*, i.descripcion as institucion_nombre, i.cod_institucion 
                    FROM estudiante e 
                    LEFT JOIN instituciones i ON e.instituciones_id_instituciones = i.id_instituciones 
                    WHERE {$whereClause}
                    ORDER BY e.apellido, e.nombre
                    LIMIT {$limit} OFFSET {$offset}";
            
            $data = $this->db->fetchAll($sql, $params);
            
            return [
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estudiantes: " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'page' => 1,
                'limit' => 10,
                'total_pages' => 0
            ];
        }
    }
    
    /**
     * Obtener estudiante por ID (solo activos)
     */
    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT e.*, i.descripcion as institucion_nombre, i.cod_institucion 
                 FROM estudiante e 
                 LEFT JOIN instituciones i ON e.instituciones_id_instituciones = i.id_instituciones 
                 WHERE e.id_estudiante = ? AND e.activo = 1",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo estudiante: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener estudiante por ID para edición (cualquier estado)
     */
    public function getByIdForEdit($id) {
        try {
            return $this->db->fetchOne(
                "SELECT e.*, i.descripcion as institucion_nombre, i.cod_institucion 
                 FROM estudiante e 
                 LEFT JOIN instituciones i ON e.instituciones_id_instituciones = i.id_instituciones 
                 WHERE e.id_estudiante = ?",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo estudiante: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo estudiante
     */
    public function create($data) {
        try {
            // Verificar que la cédula no exista
            $existing = $this->db->fetchOne(
                "SELECT id_estudiante FROM estudiante WHERE cedula_estudiante = ?",
                [$data['cedula_estudiante']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Ya existe un estudiante con esta cédula'];
            }
            
            $id = $this->db->insert(
                "INSERT INTO estudiante (cedula_estudiante, apellido, nombre, apellido_2, sexo, lugar_nacimiento, cedula_representante, telefono, instituciones_id_instituciones) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['cedula_estudiante'],
                    $data['apellido'],
                    $data['nombre'],
                    $data['apellido_2'] ?? null,
                    $data['sexo'],
                    $data['lugar_nacimiento'] ?? null,
                    $data['cedula_representante'] ?? null,
                    $data['telefono'] ?? null,
                    $data['instituciones_id_instituciones'] ?? null
                ]
            );
            
            return ['success' => true, 'id' => $id];
            
        } catch (Exception $e) {
            error_log("Error creando estudiante: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar estudiante
     */
    public function update($id, $data) {
        try {
            // Verificar que el estudiante existe
            $existing = $this->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Estudiante no encontrado'];
            }
            
            // Verificar que la cédula no esté en uso por otro estudiante
            $duplicate = $this->db->fetchOne(
                "SELECT id_estudiante FROM estudiante WHERE cedula_estudiante = ? AND id_estudiante != ?",
                [$data['cedula_estudiante'], $id]
            );
            
            if ($duplicate) {
                return ['success' => false, 'message' => 'Ya existe otro estudiante con esta cédula'];
            }
            
            $result = $this->db->update(
                "UPDATE estudiante SET cedula_estudiante = ?, apellido = ?, nombre = ?, apellido_2 = ?, 
                 sexo = ?, lugar_nacimiento = ?, cedula_representante = ?, telefono = ?, instituciones_id_instituciones = ? 
                 WHERE id_estudiante = ?",
                [
                    $data['cedula_estudiante'],
                    $data['apellido'],
                    $data['nombre'],
                    $data['apellido_2'] ?? null,
                    $data['sexo'],
                    $data['lugar_nacimiento'] ?? null,
                    $data['cedula_representante'] ?? null,
                    $data['telefono'] ?? null,
                    $data['instituciones_id_instituciones'] ?? null,
                    $id
                ]
            );
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error actualizando estudiante: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar estudiante (soft delete)
     */
    public function delete($id) {
        try {
            $result = $this->db->update(
                "UPDATE estudiante SET activo = 0 WHERE id_estudiante = ?",
                [$id]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Estudiante no encontrado'];
            }
            
        } catch (Exception $e) {
            error_log("Error eliminando estudiante: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener estadísticas de estudiantes
     */
    public function getStats() {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM estudiante WHERE activo = 1");
            $porSexo = $this->db->fetchAll("SELECT sexo, COUNT(*) as cantidad FROM estudiante WHERE activo = 1 GROUP BY sexo");
            
            return [
                'total' => $total['total'],
                'por_sexo' => $porSexo
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return ['total' => 0, 'por_sexo' => []];
        }
    }
    
    /**
     * Obtener opciones para formularios
     */
    public function getFormOptions() {
        try {
            return $this->db->fetchAll(
                "SELECT id_estudiante, cedula_estudiante, nombre, apellido FROM estudiante WHERE activo = 1 ORDER BY apellido, nombre"
            );
        } catch (Exception $e) {
            error_log("Error obteniendo opciones de estudiantes: " . $e->getMessage());
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
                 INNER JOIN participantes p ON e.id_estudiante = p.estudiante_id_estudiante
                 INNER JOIN talleres t ON p.talleres_id_taller = t.id_taller
                 WHERE t.instituciones_id_instituciones = ? AND e.activo = 1
                 ORDER BY e.apellido, e.nombre",
                [$institucionId]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo estudiantes por institución: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener instituciones del estudiante
     */
    public function getInstitucionesByEstudiante($estudianteId) {
        try {
            return $this->db->fetchAll(
                "SELECT DISTINCT i.* FROM instituciones i 
                 INNER JOIN talleres t ON i.id_instituciones = t.instituciones_id_instituciones
                 INNER JOIN participantes p ON t.id_taller = p.talleres_id_taller
                 WHERE p.estudiante_id_estudiante = ? AND i.activo = 1
                 ORDER BY i.descripcion",
                [$estudianteId]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo instituciones del estudiante: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar estudiantes por cédula o nombre
     */
    public function search($filters = []) {
        try {
            $sql = "SELECT e.*, i.descripcion as institucion_nombre, i.cod_institucion 
                    FROM estudiante e 
                    LEFT JOIN instituciones i ON e.instituciones_id_instituciones = i.id_instituciones 
                    WHERE e.activo = 1";
            $params = [];
            
            if (!empty($filters['cedula'])) {
                $sql .= " AND e.cedula_estudiante LIKE ?";
                $params[] = "%{$filters['cedula']}%";
            }
            
            if (!empty($filters['nombre'])) {
                $sql .= " AND (e.nombre LIKE ? OR e.apellido LIKE ?)";
                $searchTerm = "%{$filters['nombre']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['institucion'])) {
                $sql .= " AND e.instituciones_id_instituciones = ?";
                $params[] = $filters['institucion'];
            }
            
            $sql .= " ORDER BY e.apellido, e.nombre LIMIT 20";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error buscando estudiantes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Datos para reportes (filtros: search, fecha_desde, fecha_hasta, institucion_id)
     */
    public function getForReport($filters = []) {
        try {
            $sql = "SELECT e.*, i.descripcion as institucion_nombre, i.cod_institucion 
                    FROM estudiante e 
                    LEFT JOIN instituciones i ON e.instituciones_id_instituciones = i.id_instituciones 
                    WHERE e.activo = 1";
            $params = [];
            if (!empty($filters['search'])) {
                $sql .= " AND (e.nombre LIKE ? OR e.apellido LIKE ? OR e.cedula_estudiante LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params[] = $term; $params[] = $term; $params[] = $term;
            }
            if (!empty($filters['fecha_desde'])) {
                $sql .= " AND DATE(e.fecha_creacion) >= ?";
                $params[] = $filters['fecha_desde'];
            }
            if (!empty($filters['fecha_hasta'])) {
                $sql .= " AND DATE(e.fecha_creacion) <= ?";
                $params[] = $filters['fecha_hasta'];
            }
            if (!empty($filters['institucion_id'])) {
                $sql .= " AND e.instituciones_id_instituciones = ?";
                $params[] = $filters['institucion_id'];
            }
            $sql .= " ORDER BY e.apellido, e.nombre";
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Error getForReport estudiantes: " . $e->getMessage());
            return [];
        }
    }
}
