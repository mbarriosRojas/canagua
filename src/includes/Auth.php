<?php

/**
 * Clase para manejo de autenticación y autorización
 * Sistema SACRGAPI - Gestión Administrativa
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Iniciar sesión
     */
    public function login($username, $password) {
        try {
            $user = $this->db->fetchOne(
                "SELECT * FROM usuarios WHERE username = ? AND activo = 1",
                [$username]
            );
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Actualizar último acceso
                $this->db->update(
                    "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?",
                    [$user['id_usuario']]
                );
                
                // Configurar sesión (ya iniciada en index.php)
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellido'] = $user['apellido'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['email'] = $user['email'];
                
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id_usuario'],
                        'username' => $user['username'],
                        'nombre' => $user['nombre'],
                        'apellido' => $user['apellido'],
                        'rol' => $user['rol'],
                        'email' => $user['email']
                    ]
                ];
            }
            
            return ['success' => false, 'message' => 'Credenciales inválidas'];
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        return ['success' => true];
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Obtener usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'nombre' => $_SESSION['nombre'],
            'apellido' => $_SESSION['apellido'],
            'rol' => $_SESSION['rol'],
            'email' => $_SESSION['email']
        ];
    }
    
    /**
     * Verificar permisos por rol
     */
    public function hasRole($requiredRole) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        $roles = ['operador' => 1, 'supervisor' => 2, 'admin' => 3];
        $userLevel = $roles[$user['rol']] ?? 0;
        $requiredLevel = $roles[$requiredRole] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Verificar si es administrador
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Verificar si es supervisor o superior
     */
    public function isSupervisor() {
        return $this->hasRole('supervisor');
    }
    
    /**
     * Crear nuevo usuario
     */
    public function createUser($data) {
        try {
            // Verificar que el usuario actual tenga permisos
            if (!$this->isSupervisor()) {
                return ['success' => false, 'message' => 'No tiene permisos para crear usuarios'];
            }
            
            // Verificar que el username no exista
            $existingUser = $this->db->fetchOne(
                "SELECT id_usuario FROM usuarios WHERE username = ? OR email = ?",
                [$data['username'], $data['email']]
            );
            
            if ($existingUser) {
                return ['success' => false, 'message' => 'El usuario o email ya existe'];
            }
            
            // Crear usuario
            $userId = $this->db->insert(
                "INSERT INTO usuarios (username, email, password_hash, nombre, apellido, rol, creado_por) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['username'],
                    $data['email'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $data['nombre'],
                    $data['apellido'],
                    $data['rol'],
                    $this->getCurrentUser()['id']
                ]
            );
            
            return ['success' => true, 'user_id' => $userId];
            
        } catch (Exception $e) {
            error_log("Error creando usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar contraseña
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $currentUser = $this->getCurrentUser();
            
            // Verificar permisos (solo admin o el propio usuario)
            if (!$this->isAdmin() && $currentUser['id'] != $userId) {
                return ['success' => false, 'message' => 'No tiene permisos para cambiar esta contraseña'];
            }
            
            $result = $this->db->update(
                "UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?",
                [password_hash($newPassword, PASSWORD_DEFAULT), $userId]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }
            
        } catch (Exception $e) {
            error_log("Error actualizando contraseña: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Obtener lista de usuarios
     */
    public function getUsers() {
        try {
            if (!$this->isSupervisor()) {
                return ['success' => false, 'message' => 'No tiene permisos para ver usuarios'];
            }
            
            $users = $this->db->fetchAll(
                "SELECT id_usuario, username, email, nombre, apellido, rol, activo, 
                        fecha_creacion, ultimo_acceso 
                 FROM usuarios ORDER BY fecha_creacion DESC"
            );
            
            return ['success' => true, 'users' => $users];
            
        } catch (Exception $e) {
            error_log("Error obteniendo usuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Activar/Desactivar usuario
     */
    public function toggleUserStatus($userId) {
        try {
            if (!$this->isAdmin()) {
                return ['success' => false, 'message' => 'No tiene permisos para modificar usuarios'];
            }
            
            $result = $this->db->update(
                "UPDATE usuarios SET activo = NOT activo WHERE id_usuario = ?",
                [$userId]
            );
            
            if ($result > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }
            
        } catch (Exception $e) {
            error_log("Error cambiando estado de usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
}
