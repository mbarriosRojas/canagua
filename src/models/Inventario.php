<?php

/**
 * Modelo para gestión de inventario
 * Sistema SACRGAPI - Gestión Administrativa
 */
class Inventario {
    private $db;
    private static $hasNombreColumn = null;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Comprueba si la tabla inventario tiene la columna nombre (cache en memoria)
     */
    private function hasNombreColumn() {
        if (self::$hasNombreColumn !== null) {
            return self::$hasNombreColumn;
        }
        try {
            $r = $this->db->fetchOne(
                "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND COLUMN_NAME = 'nombre'"
            );
            self::$hasNombreColumn = (bool) $r;
            return self::$hasNombreColumn;
        } catch (Exception $e) {
            self::$hasNombreColumn = false;
            return false;
        }
    }
    
    /**
     * Añade la columna nombre si no existe (para bases instaladas antes del cambio)
     */
    private function ensureNombreColumn() {
        if ($this->hasNombreColumn()) {
            return true;
        }
        try {
            $this->db->query("ALTER TABLE inventario ADD COLUMN nombre VARCHAR(200) NULL AFTER cod_equipo");
            self::$hasNombreColumn = true;
            return true;
        } catch (Exception $e) {
            error_log("Error añadiendo columna nombre: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todo el inventario
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT i.*, CONCAT(p.nombre, ' ', p.apellido) as responsable, p.cargo
                    FROM inventario i
                    LEFT JOIN personal p ON i.personal_id_personal = p.id_personal
                    WHERE 1=1";
            $params = [];
            
            if (!empty($filters['search'])) {
                $search = "%{$filters['search']}%";
                if ($this->hasNombreColumn()) {
                    $sql .= " AND (i.cod_equipo LIKE ? OR i.nombre LIKE ? OR i.ubicacion LIKE ? OR i.marca LIKE ? OR i.modelo LIKE ?)";
                    $params = [$search, $search, $search, $search, $search];
                } else {
                    $sql .= " AND (i.cod_equipo LIKE ? OR i.ubicacion LIKE ? OR i.marca LIKE ? OR i.modelo LIKE ?)";
                    $params = [$search, $search, $search, $search];
                }
            }
            
            if (!empty($filters['estado'])) {
                $sql .= " AND i.estado = ?";
                $params[] = $filters['estado'];
            }
            
            if (!empty($filters['ubicacion'])) {
                $sql .= " AND i.ubicacion LIKE ?";
                $params[] = "%{$filters['ubicacion']}%";
            }
            
            if (!empty($filters['responsable'])) {
                $sql .= " AND i.personal_id_personal = ?";
                $params[] = $filters['responsable'];
            }
            
            $sql .= " ORDER BY i.cod_equipo";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error obteniendo inventario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener item de inventario por ID
     */
    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT i.*, CONCAT(p.nombre, ' ', p.apellido) as responsable, p.cargo
                 FROM inventario i
                 LEFT JOIN personal p ON i.personal_id_personal = p.id_personal
                 WHERE i.id_inventario = ?",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo inventario: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo item de inventario
     */
    public function create($data) {
        try {
            // Verificar que el código de equipo no exista
            $existing = $this->db->fetchOne(
                "SELECT id_inventario FROM inventario WHERE cod_equipo = ?",
                [$data['cod_equipo']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Ya existe un equipo con este código'];
            }
            
            $this->ensureNombreColumn();
            
            if ($this->hasNombreColumn()) {
                $id = $this->db->insert(
                    "INSERT INTO inventario (personal_id_personal, cod_equipo, nombre, ubicacion, cedula_personal, 
                                           cantidad, estado, serial, marca, modelo, color, medidas, 
                                           capacidad, otras_caracteristicas, observacion) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $data['personal_id_personal'] ?? null,
                        $data['cod_equipo'],
                        $data['nombre'] ?? null,
                        $data['ubicacion'] ?? null,
                        $data['cedula_personal'] ?? null,
                        $data['cantidad'] ?? 1,
                        $data['estado'] ?? 'Bueno',
                        $data['serial'] ?? null,
                        $data['marca'] ?? null,
                        $data['modelo'] ?? null,
                        $data['color'] ?? null,
                        $data['medidas'] ?? null,
                        $data['capacidad'] ?? null,
                        $data['otras_caracteristicas'] ?? null,
                        $data['observacion'] ?? null
                    ]
                );
            } else {
                $id = $this->db->insert(
                    "INSERT INTO inventario (personal_id_personal, cod_equipo, ubicacion, cedula_personal, 
                                           cantidad, estado, serial, marca, modelo, color, medidas, 
                                           capacidad, otras_caracteristicas, observacion) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $data['personal_id_personal'] ?? null,
                        $data['cod_equipo'],
                        $data['ubicacion'] ?? null,
                        $data['cedula_personal'] ?? null,
                        $data['cantidad'] ?? 1,
                        $data['estado'] ?? 'Bueno',
                        $data['serial'] ?? null,
                        $data['marca'] ?? null,
                        $data['modelo'] ?? null,
                        $data['color'] ?? null,
                        $data['medidas'] ?? null,
                        $data['capacidad'] ?? null,
                        $data['otras_caracteristicas'] ?? null,
                        $data['observacion'] ?? null
                    ]
                );
            }
            
            return ['success' => true, 'id' => $id];
            
        } catch (Exception $e) {
            error_log("Error creando inventario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar item de inventario
     */
    public function update($id, $data) {
        try {
            // Verificar que el item existe
            $existing = $this->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Item de inventario no encontrado'];
            }
            
            // Verificar que el código no esté en uso por otro item
            $duplicate = $this->db->fetchOne(
                "SELECT id_inventario FROM inventario WHERE cod_equipo = ? AND id_inventario != ?",
                [$data['cod_equipo'], $id]
            );
            
            if ($duplicate) {
                return ['success' => false, 'message' => 'Ya existe otro equipo con este código'];
            }
            
            $this->ensureNombreColumn();
            
            if ($this->hasNombreColumn()) {
                $result = $this->db->update(
                    "UPDATE inventario SET personal_id_personal = ?, cod_equipo = ?, nombre = ?, ubicacion = ?, 
                                           cedula_personal = ?, cantidad = ?, estado = ?, serial = ?, 
                                           marca = ?, modelo = ?, color = ?, medidas = ?, capacidad = ?, 
                                           otras_caracteristicas = ?, observacion = ? 
                     WHERE id_inventario = ?",
                    [
                        $data['personal_id_personal'] ?? null,
                        $data['cod_equipo'],
                        $data['nombre'] ?? null,
                        $data['ubicacion'] ?? null,
                        $data['cedula_personal'] ?? null,
                        $data['cantidad'] ?? 1,
                        $data['estado'] ?? 'Bueno',
                        $data['serial'] ?? null,
                        $data['marca'] ?? null,
                        $data['modelo'] ?? null,
                        $data['color'] ?? null,
                        $data['medidas'] ?? null,
                        $data['capacidad'] ?? null,
                        $data['otras_caracteristicas'] ?? null,
                        $data['observacion'] ?? null,
                        $id
                    ]
                );
            } else {
                $result = $this->db->update(
                    "UPDATE inventario SET personal_id_personal = ?, cod_equipo = ?, ubicacion = ?, 
                                           cedula_personal = ?, cantidad = ?, estado = ?, serial = ?, 
                                           marca = ?, modelo = ?, color = ?, medidas = ?, capacidad = ?, 
                                           otras_caracteristicas = ?, observacion = ? 
                     WHERE id_inventario = ?",
                    [
                        $data['personal_id_personal'] ?? null,
                        $data['cod_equipo'],
                        $data['ubicacion'] ?? null,
                        $data['cedula_personal'] ?? null,
                        $data['cantidad'] ?? 1,
                        $data['estado'] ?? 'Bueno',
                        $data['serial'] ?? null,
                        $data['marca'] ?? null,
                        $data['modelo'] ?? null,
                        $data['color'] ?? null,
                        $data['medidas'] ?? null,
                        $data['capacidad'] ?? null,
                        $data['otras_caracteristicas'] ?? null,
                        $data['observacion'] ?? null,
                        $id
                    ]
                );
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error actualizando inventario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar item de inventario
     */
    public function delete($id) {
        try {
            $result = $this->db->delete(
                "DELETE FROM inventario WHERE id_inventario = ?",
                [$id]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Item de inventario no encontrado'];
            }
            
        } catch (Exception $e) {
            error_log("Error eliminando inventario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener opciones para formularios
     */
    public function getFormOptions() {
        try {
            $responsables = $this->db->fetchAll(
                "SELECT id_personal, cedula_personal, nombre, apellido, cargo 
                 FROM personal WHERE activo = 1 ORDER BY apellido, nombre"
            );
            
            $ubicaciones = $this->db->fetchAll(
                "SELECT DISTINCT ubicacion FROM inventario WHERE ubicacion IS NOT NULL AND ubicacion != '' ORDER BY ubicacion"
            );
            
            return [
                'responsables' => $responsables,
                'ubicaciones' => $ubicaciones
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo opciones: " . $e->getMessage());
            return ['responsables' => [], 'ubicaciones' => []];
        }
    }
    
    /**
     * Datos para reportes (filtros: search, fecha_desde, fecha_hasta)
     */
    public function getForReport($filters = []) {
        try {
            $sql = "SELECT i.*, CONCAT(p.nombre, ' ', p.apellido) as responsable FROM inventario i LEFT JOIN personal p ON i.personal_id_personal = p.id_personal WHERE 1=1";
            $params = [];
            if (!empty($filters['search'])) {
                $sql .= " AND (i.cod_equipo LIKE ? OR i.nombre LIKE ? OR i.ubicacion LIKE ? OR i.marca LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
            }
            if (!empty($filters['fecha_desde'])) {
                $sql .= " AND DATE(i.fecha_creacion) >= ?";
                $params[] = $filters['fecha_desde'];
            }
            if (!empty($filters['fecha_hasta'])) {
                $sql .= " AND DATE(i.fecha_creacion) <= ?";
                $params[] = $filters['fecha_hasta'];
            }
            $sql .= " ORDER BY i.cod_equipo";
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Error getForReport inventario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de inventario
     */
    public function getStats() {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM inventario");
            $porEstado = $this->db->fetchAll("SELECT estado, COUNT(*) as cantidad FROM inventario GROUP BY estado");
            $porUbicacion = $this->db->fetchAll("SELECT ubicacion, COUNT(*) as cantidad FROM inventario WHERE ubicacion IS NOT NULL GROUP BY ubicacion ORDER BY cantidad DESC");
            $totalItems = $this->db->fetchOne("SELECT SUM(cantidad) as total_items FROM inventario");
            
            return [
                'total' => $total['total'],
                'total_items' => $totalItems['total_items'] ?? 0,
                'por_estado' => $porEstado,
                'por_ubicacion' => $porUbicacion
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return ['total' => 0, 'total_items' => 0, 'por_estado' => [], 'por_ubicacion' => []];
        }
    }
}
