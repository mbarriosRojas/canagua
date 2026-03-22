<?php

/**
 * Modelo para gestión de directivos
 */
class Directivo {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    /**
     * Asegurar que la tabla directivos exista
     */
    private function ensureTable(): void {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS directivos (
                    id_directivo INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL,
                    apellido VARCHAR(100) NOT NULL,
                    cargo VARCHAR(100) NOT NULL,
                    telefono VARCHAR(30) NULL,
                    email VARCHAR(150) NULL,
                    firma TINYINT(1) NOT NULL DEFAULT 0,
                    activo TINYINT(1) NOT NULL DEFAULT 1,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (Exception $e) {
            error_log('Error asegurando tabla directivos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los directivos activos
     */
    public function getAll(): array {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM directivos WHERE activo = 1 ORDER BY apellido, nombre"
            );
        } catch (Exception $e) {
            error_log('Error obteniendo directivos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener directivos que firman (activos y con firma = 1)
     */
    public function getFirmantes(): array {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM directivos WHERE activo = 1 AND firma = 1 ORDER BY apellido, nombre"
            );
        } catch (Exception $e) {
            error_log('Error obteniendo firmantes: ' . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): ?array {
        try {
            $row = $this->db->fetchOne(
                "SELECT * FROM directivos WHERE id_directivo = ?",
                [$id]
            );
            return $row ?: null;
        } catch (Exception $e) {
            error_log('Error obteniendo directivo: ' . $e->getMessage());
            return null;
        }
    }

    public function create(array $data): array {
        try {
            $id = $this->db->insert(
                "INSERT INTO directivos (nombre, apellido, cargo, telefono, email, firma, activo)
                 VALUES (?, ?, ?, ?, ?, ?, 1)",
                [
                    $data['nombre'],
                    $data['apellido'],
                    $data['cargo'],
                    $data['telefono'] ?? null,
                    $data['email'] ?? null,
                    !empty($data['firma']) ? 1 : 0
                ]
            );
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            error_log('Error creando directivo: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al crear directivo'];
        }
    }

    public function update(int $id, array $data): array {
        try {
            $existing = $this->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Directivo no encontrado'];
            }

            $this->db->update(
                "UPDATE directivos
                 SET nombre = ?, apellido = ?, cargo = ?, telefono = ?, email = ?, firma = ?, activo = ?
                 WHERE id_directivo = ?",
                [
                    $data['nombre'],
                    $data['apellido'],
                    $data['cargo'],
                    $data['telefono'] ?? null,
                    $data['email'] ?? null,
                    !empty($data['firma']) ? 1 : 0,
                    isset($data['activo']) ? (int)$data['activo'] : ($existing['activo'] ?? 1),
                    $id
                ]
            );

            return ['success' => true];
        } catch (Exception $e) {
            error_log('Error actualizando directivo: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al actualizar directivo'];
        }
    }

    public function delete(int $id): array {
        try {
            $result = $this->db->update(
                "UPDATE directivos SET activo = 0 WHERE id_directivo = ?",
                [$id]
            );
            if ($result > 0) {
                return ['success' => true];
            }
            return ['success' => false, 'message' => 'Directivo no encontrado'];
        } catch (Exception $e) {
            error_log('Error eliminando directivo: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al eliminar directivo'];
        }
    }
}

