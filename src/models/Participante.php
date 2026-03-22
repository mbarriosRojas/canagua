<?php

/**
 * Modelo para gestión de participantes de cursos
 *
 * NOTA: crear las tablas en la base de datos (ejemplo):
 *
 * CREATE TABLE participantes (
 *   id_participante INT AUTO_INCREMENT PRIMARY KEY,
 *   cedula_participante VARCHAR(20) NOT NULL UNIQUE,
 *   apellido VARCHAR(100) NOT NULL,
 *   nombre VARCHAR(100) NOT NULL,
 *   telefono VARCHAR(30) NULL,
 *   correo VARCHAR(150) NULL,
 *   activo TINYINT(1) NOT NULL DEFAULT 1,
 *   fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * );
 *
 * CREATE TABLE participante_curso (
 *   id_participante_curso INT AUTO_INCREMENT PRIMARY KEY,
 *   participante_id INT NOT NULL,
 *   cursos_id_cursos INT NOT NULL,
 *   fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *   activo TINYINT(1) NOT NULL DEFAULT 1
 * );
 */

class Participante {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Listado básico de participantes
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT 
                        id_participantes AS id_participante,
                        cedula_participante,
                        apellido,
                        nombre,
                        telefono,
                        correo,
                        fecha_creacion,
                        activo
                    FROM participantes 
                    WHERE activo = 1";
            $params = [];

            if (!empty($filters['search'])) {
                $sql .= " AND (cedula_participante LIKE ? OR nombre LIKE ? OR apellido LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
            }

            $sql .= " ORDER BY apellido, nombre";
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Error obteniendo participantes: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            return $this->db->fetchOne(
                "SELECT 
                    id_participantes AS id_participante,
                    cedula_participante,
                    apellido,
                    nombre,
                    telefono,
                    correo,
                    fecha_creacion,
                    activo
                 FROM participantes 
                 WHERE id_participantes = ? AND activo = 1",
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo participante: " . $e->getMessage());
            return null;
        }
    }

    public function create($data) {
        try {
            $existing = $this->db->fetchOne(
                "SELECT id_participantes AS id_participante FROM participantes WHERE cedula_participante = ?",
                [$data['cedula_participante']]
            );

            if ($existing) {
                return ['success' => false, 'message' => 'Ya existe un participante con esta cédula'];
            }

            // Para que coincida con el esquema actual de la BD (init.sql),
            // rellenamos cursos_id_cursos y sexo con valores por defecto válidos.
            // Más adelante, estos se podrán ajustar desde la UI o al vincular con cursos.
            $defaultCursoId = 1; // asume que existe un curso con ID 1 (según datos de ejemplo)
            $defaultSexo = 'M';
            $direccion = $data['direccion'] ?? null;

            $id = $this->db->insert(
                "INSERT INTO participantes (cursos_id_cursos, cedula_participante, apellido, nombre, sexo, telefono, correo, direccion)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $defaultCursoId,
                    $data['cedula_participante'],
                    $data['apellido'],
                    $data['nombre'],
                    $defaultSexo,
                    $data['telefono'] ?? null,
                    $data['correo'] ?? null,
                    $direccion
                ]
            );

            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            error_log("Error creando participante: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    public function update($id, $data) {
        try {
            $existing = $this->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Participante no encontrado'];
            }

            $duplicate = $this->db->fetchOne(
                "SELECT id_participantes AS id_participante FROM participantes WHERE cedula_participante = ? AND id_participantes != ?",
                [$data['cedula_participante'], $id]
            );

            if ($duplicate) {
                return ['success' => false, 'message' => 'Ya existe otro participante con esta cédula'];
            }

            $this->db->update(
                "UPDATE participantes
                 SET cedula_participante = ?, apellido = ?, nombre = ?, telefono = ?, correo = ?
                 WHERE id_participantes = ?",
                [
                    $data['cedula_participante'],
                    $data['apellido'],
                    $data['nombre'],
                    $data['telefono'] ?? null,
                    $data['correo'] ?? null,
                    $id
                ]
            );

            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error actualizando participante: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    public function delete($id) {
        try {
            $result = $this->db->update(
                "UPDATE participantes SET activo = 0 WHERE id_participantes = ?",
                [$id]
            );

            if ($result > 0) {
                return ['success' => true];
            }

            return ['success' => false, 'message' => 'Participante no encontrado'];
        } catch (Exception $e) {
            error_log("Error eliminando participante: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    /**
     * Búsqueda rápida por cédula o nombre (para asociar a cursos)
     */
    public function search($filters = []) {
        try {
            $sql = "SELECT 
                        id_participantes AS id_participante,
                        cedula_participante,
                        apellido,
                        nombre,
                        telefono,
                        correo,
                        fecha_creacion
                    FROM participantes 
                    WHERE activo = 1";
            $params = [];

            if (!empty($filters['cedula'])) {
                $sql .= " AND cedula_participante LIKE ?";
                $params[] = '%' . $filters['cedula'] . '%';
            }

            if (!empty($filters['nombre'])) {
                $sql .= " AND (nombre LIKE ? OR apellido LIKE ?)";
                $term = '%' . $filters['nombre'] . '%';
                $params[] = $term;
                $params[] = $term;
            }

            $sql .= " ORDER BY apellido, nombre LIMIT 20";
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Error buscando participantes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Datos para reportes (filtros: search, fecha_desde, fecha_hasta)
     */
    public function getForReport($filters = []) {
        try {
            $sql = "SELECT 
                        id_participantes AS id_participante,
                        cedula_participante,
                        apellido,
                        nombre,
                        telefono,
                        correo,
                        fecha_creacion
                    FROM participantes 
                    WHERE activo = 1";
            $params = [];

            if (!empty($filters['search'])) {
                $sql .= " AND (cedula_participante LIKE ? OR nombre LIKE ? OR apellido LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
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
            error_log("Error getForReport participantes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener participantes asociados a un curso específico
     */
    public function getByCurso($cursoId) {
        try {
            return $this->db->fetchAll(
                "SELECT 
                    id_participantes AS id_participante,
                    cedula_participante,
                    apellido,
                    nombre,
                    telefono,
                    correo,
                    fecha_creacion,
                    activo,
                    cursos_id_cursos
                 FROM participantes
                 WHERE activo = 1 AND cursos_id_cursos = ?
                 ORDER BY apellido, nombre",
                [$cursoId]
            );
        } catch (Exception $e) {
            error_log("Error obteniendo participantes por curso: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Asociar un participante a un curso (usa campo cursos_id_cursos)
     */
    public function addToCurso($cursoId, $participanteId) {
        try {
            // Verificar que el curso existe
            $curso = $this->db->fetchOne(
                "SELECT id_cursos FROM cursos WHERE id_cursos = ? AND activo = 1",
                [$cursoId]
            );
            if (!$curso) {
                return ['success' => false, 'message' => 'Curso no encontrado'];
            }

            // Verificar que el participante existe
            $participante = $this->db->fetchOne(
                "SELECT id_participantes, cursos_id_cursos, activo 
                 FROM participantes 
                 WHERE id_participantes = ?",
                [$participanteId]
            );
            if (!$participante || !(int)$participante['activo']) {
                return ['success' => false, 'message' => 'Participante no encontrado'];
            }

            // Ya está inscrito en este curso
            if ((int)$participante['cursos_id_cursos'] === (int)$cursoId) {
                return ['success' => false, 'message' => 'El participante ya está inscrito en este curso'];
            }

            // Mover/inscribir participante a este curso
            $result = $this->db->update(
                "UPDATE participantes 
                 SET cursos_id_cursos = ? 
                 WHERE id_participantes = ?",
                [$cursoId, $participanteId]
            );

            if ($result > 0) {
                return ['success' => true];
            }

            return ['success' => false, 'message' => 'No se pudo asociar el participante al curso'];
        } catch (Exception $e) {
            error_log("Error asociando participante a curso: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    /**
     * Quitar participante de un curso.
     * Dado el modelo actual (campo cursos_id_cursos obligatorio), interpretamos
     * \"quitar\" como desactivar el participante (activo = 0).
     */
    public function removeFromCurso($cursoId, $participanteId) {
        try {
            $participante = $this->db->fetchOne(
                "SELECT id_participantes, cursos_id_cursos, activo 
                 FROM participantes 
                 WHERE id_participantes = ?",
                [$participanteId]
            );

            if (!$participante || !(int)$participante['activo']) {
                return ['success' => false, 'message' => 'Participante no encontrado'];
            }

            if ((int)$participante['cursos_id_cursos'] !== (int)$cursoId) {
                return ['success' => false, 'message' => 'El participante no está inscrito en este curso'];
            }

            $result = $this->db->update(
                "UPDATE participantes SET activo = 0 WHERE id_participantes = ?",
                [$participanteId]
            );

            if ($result > 0) {
                return ['success' => true];
            }

            return ['success' => false, 'message' => 'No se pudo quitar el participante del curso'];
        } catch (Exception $e) {
            error_log("Error quitando participante de curso: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
}

