<?php

/**
 * Punto de entrada principal del sistema SACRGAPI
 * Sistema Automatizado para el Control y Registro de la Gestión Administrativa
 */

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos de configuración
require_once __DIR__ . '/../src/includes/Config.php';
require_once __DIR__ . '/../src/includes/Database.php';
require_once __DIR__ . '/../src/includes/Auth.php';

// Incluir modelos
require_once __DIR__ . '/../src/models/Estudiante.php';
require_once __DIR__ . '/../src/models/Personal.php';
require_once __DIR__ . '/../src/models/Taller.php';
require_once __DIR__ . '/../src/models/Curso.php';
require_once __DIR__ . '/../src/models/Inventario.php';
require_once __DIR__ . '/../src/models/Programa.php';
require_once __DIR__ . '/../src/models/Institucion.php';
require_once __DIR__ . '/../src/models/Calificacion.php';

// Cargar configuración
Config::load();

// Iniciar sesión
session_start();

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/public', '', $path);

// Remover la barra inicial
$path = ltrim($path, '/');

// Si no hay ruta, redirigir al login
if (empty($path)) {
    $path = 'login';
}

// Dividir la ruta en segmentos
$segments = explode('/', $path);
$controller = $segments[0] ?? 'login';
$action = $segments[1] ?? 'index';
$id = $segments[2] ?? null;

// Inicializar autenticación
$auth = new Auth();

// Rutas públicas (no requieren autenticación)
$publicRoutes = ['login', 'logout', 'test'];

// Rutas que requieren autenticación pero pueden ser accedidas por AJAX
$ajaxRoutes = ['list', 'get', 'stats', 'options'];

// Verificar autenticación para rutas protegidas
if (!in_array($controller, $publicRoutes) && !$auth->isAuthenticated()) {
    // Permitir acceso a rutas AJAX sin redirección completa
    if (!in_array($action, $ajaxRoutes)) {
        header('Location: /public/login');
        exit;
    }
}

// Procesar la solicitud
try {
    switch ($controller) {
        case 'login':
            handleLogin($auth);
            break;
            
        case 'logout':
            handleLogout($auth);
            break;
            
        case 'dashboard':
            handleDashboard($auth);
            break;
            
        case 'usuarios':
            handleUsuarios($auth, $action, $id);
            break;
            
        case 'estudiantes':
            handleEstudiantes($auth, $action, $id);
            break;
            
        case 'personal':
            handlePersonal($auth, $action, $id);
            break;
            
        case 'talleres':
            handleTalleres($auth, $action, $id);
            break;
            
        case 'cursos':
            handleCursos($auth, $action, $id);
            break;
            
        case 'inventario':
            handleInventario($auth, $action, $id);
            break;
            
        case 'programas':
            handleProgramas($auth, $action, $id);
            break;
            
        case 'instituciones':
            handleInstituciones($auth, $action, $id);
            break;
            
        case 'calificaciones':
            handleCalificaciones($auth, $action, $id);
            break;
            
        case 'test':
            handleTest();
            break;
            
        default:
            http_response_code(404);
            echo "Página no encontrada";
            break;
    }
} catch (Exception $e) {
    error_log("Error en la aplicación: " . $e->getMessage());
    http_response_code(500);
    echo "Error interno del servidor";
}

/**
 * Manejar login
 */
function handleLogin($auth) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            header('Location: /public/dashboard');
            exit;
        } else {
            $error = $result['message'];
        }
    }
    
    // Mostrar formulario de login
    include __DIR__ . '/../src/views/login.php';
}

/**
 * Manejar logout
 */
function handleLogout($auth) {
    $auth->logout();
    header('Location: /public/login');
    exit;
}

/**
 * Manejar dashboard
 */
function handleDashboard($auth) {
    $user = $auth->getCurrentUser();
    
    // Si es una petición AJAX para estadísticas
    if (isset($_GET['action']) && $_GET['action'] === 'stats') {
        getDashboardStats();
        return;
    }
    
    // Obtener estadísticas para el dashboard
    $dashboardStats = getDashboardStatsData();
    
    include __DIR__ . '/../src/views/dashboard.php';
}

/**
 * Obtener datos de estadísticas del dashboard (versión para PHP)
 */
function getDashboardStatsData() {
    try {
        $db = Database::getInstance();
        
        // Inicializar contadores
        $estudiantesActivos = 0;
        $personalActivo = 0;
        $talleresActivos = 0;
        $cursosEnProgreso = 0;
        $actividadReciente = [];
        
        // Contar estudiantes activos
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM estudiante WHERE activo = 1");
            $estudiantesActivos = (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error contando estudiantes: " . $e->getMessage());
        }
        
        // Contar personal activo
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM personal WHERE activo = 1");
            $personalActivo = (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error contando personal: " . $e->getMessage());
        }
        
        // Contar talleres activos
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM talleres WHERE activo = 1");
            $talleresActivos = (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error contando talleres: " . $e->getMessage());
        }
        
        // Contar cursos en progreso
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM cursos WHERE activo = 1");
            $cursosEnProgreso = (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error contando cursos: " . $e->getMessage());
        }
        
        // Obtener actividad reciente (simplificado)
        try {
            $actividadReciente = $db->fetchAll("
                SELECT 
                    'estudiante' as tipo,
                    CONCAT('Nuevo estudiante: ', nombre, ' ', apellido) as descripcion,
                    fecha_creacion as fecha
                FROM estudiante 
                WHERE activo = 1 
                ORDER BY fecha_creacion DESC 
                LIMIT 3
            ");
            
            // Agregar talleres recientes
            $talleresRecientes = $db->fetchAll("
                SELECT 
                    'taller' as tipo,
                    CONCAT('Nuevo taller: ', cod_taller) as descripcion,
                    fecha_creacion as fecha
                FROM talleres 
                WHERE activo = 1 
                ORDER BY fecha_creacion DESC 
                LIMIT 2
            ");
            
            $actividadReciente = array_merge($actividadReciente, $talleresRecientes);
            
            // Ordenar por fecha y limitar
            usort($actividadReciente, function($a, $b) {
                return strtotime($b['fecha']) - strtotime($a['fecha']);
            });
            $actividadReciente = array_slice($actividadReciente, 0, 5);
            
            // Formatear fechas para mostrar tiempo relativo
            foreach ($actividadReciente as &$actividad) {
                $fecha = new DateTime($actividad['fecha']);
                $ahora = new DateTime();
                $diferencia = $ahora->diff($fecha);
                
                if ($diferencia->days > 0) {
                    $actividad['tiempo_relativo'] = $diferencia->days . ' día' . ($diferencia->days > 1 ? 's' : '') . ' atrás';
                } elseif ($diferencia->h > 0) {
                    $actividad['tiempo_relativo'] = $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '') . ' atrás';
                } else {
                    $actividad['tiempo_relativo'] = 'Hace unos minutos';
                }
            }
        } catch (Exception $e) {
            error_log("Error obteniendo actividad reciente: " . $e->getMessage());
            $actividadReciente = [];
        }
        
        return [
            'estudiantes_activos' => $estudiantesActivos,
            'personal_activo' => $personalActivo,
            'talleres_activos' => $talleresActivos,
            'cursos_en_progreso' => $cursosEnProgreso,
            'actividad_reciente' => $actividadReciente
        ];
        
    } catch (Exception $e) {
        error_log("Error general obteniendo estadísticas del dashboard: " . $e->getMessage());
        
        return [
            'estudiantes_activos' => 0,
            'personal_activo' => 0,
            'talleres_activos' => 0,
            'cursos_en_progreso' => 0,
            'actividad_reciente' => []
        ];
    }
}

/**
 * Obtener estadísticas del dashboard
 */
function getDashboardStats() {
    // Configurar headers para JSON
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $db = Database::getInstance();
        
        // Inicializar contadores
        $estudiantesActivos = 0;
        $personalActivo = 0;
        $talleresActivos = 0;
        $cursosEnProgreso = 0;
        $actividadReciente = [];
        
        // Contar estudiantes activos
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM estudiante WHERE activo = 1");
            $estudiantesActivos = (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error contando estudiantes: " . $e->getMessage());
        }
        
        // Contar personal activo
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM personal WHERE activo = 1");
            $personalActivo = (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error contando personal: " . $e->getMessage());
        }
        
        // Contar talleres activos
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM talleres WHERE activo = 1");
            $talleresActivos = (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error contando talleres: " . $e->getMessage());
        }
        
        // Contar cursos en progreso
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM cursos WHERE activo = 1");
            $cursosEnProgreso = (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error contando cursos: " . $e->getMessage());
        }
        
        // Obtener actividad reciente (simplificado)
        try {
            $actividadReciente = $db->fetchAll("
                SELECT 
                    'estudiante' as tipo,
                    CONCAT('Nuevo estudiante: ', nombre, ' ', apellido) as descripcion,
                    fecha_creacion as fecha
                FROM estudiante 
                WHERE activo = 1 
                ORDER BY fecha_creacion DESC 
                LIMIT 3
            ");
            
            // Agregar talleres recientes
            $talleresRecientes = $db->fetchAll("
                SELECT 
                    'taller' as tipo,
                    CONCAT('Nuevo taller: ', cod_taller) as descripcion,
                    fecha_creacion as fecha
                FROM talleres 
                WHERE activo = 1 
                ORDER BY fecha_creacion DESC 
                LIMIT 2
            ");
            
            $actividadReciente = array_merge($actividadReciente, $talleresRecientes);
            
            // Ordenar por fecha y limitar
            usort($actividadReciente, function($a, $b) {
                return strtotime($b['fecha']) - strtotime($a['fecha']);
            });
            $actividadReciente = array_slice($actividadReciente, 0, 5);
            
            // Formatear fechas para mostrar tiempo relativo
            foreach ($actividadReciente as &$actividad) {
                $fecha = new DateTime($actividad['fecha']);
                $ahora = new DateTime();
                $diferencia = $ahora->diff($fecha);
                
                if ($diferencia->days > 0) {
                    $actividad['tiempo_relativo'] = $diferencia->days . ' día' . ($diferencia->days > 1 ? 's' : '') . ' atrás';
                } elseif ($diferencia->h > 0) {
                    $actividad['tiempo_relativo'] = $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '') . ' atrás';
                } else {
                    $actividad['tiempo_relativo'] = 'Hace unos minutos';
                }
            }
        } catch (Exception $e) {
            error_log("Error obteniendo actividad reciente: " . $e->getMessage());
            $actividadReciente = [];
        }
        
        $stats = [
            'success' => true,
            'data' => [
                'estudiantes_activos' => $estudiantesActivos,
                'personal_activo' => $personalActivo,
                'talleres_activos' => $talleresActivos,
                'cursos_en_progreso' => $cursosEnProgreso,
                'actividad_reciente' => $actividadReciente
            ]
        ];
        
        echo json_encode($stats, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("Error general obteniendo estadísticas del dashboard: " . $e->getMessage());
        
        $error = [
            'success' => false,
            'message' => 'Error obteniendo estadísticas: ' . $e->getMessage(),
            'data' => [
                'estudiantes_activos' => 0,
                'personal_activo' => 0,
                'talleres_activos' => 0,
                'cursos_en_progreso' => 0,
                'actividad_reciente' => []
            ]
        ];
        
        echo json_encode($error, JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Manejar gestión de usuarios
 */
function handleUsuarios($auth, $action, $id) {
    if (!$auth->isSupervisor()) {
        http_response_code(403);
        echo "Acceso denegado";
        return;
    }
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'nombre' => $_POST['nombre'],
                    'apellido' => $_POST['apellido'],
                    'rol' => $_POST['rol']
                ];
                
                $result = $auth->createUser($data);
                echo json_encode($result);
                return;
            }
            include __DIR__ . '/../src/views/usuarios/create.php';
            break;
            
        case 'list':
            $result = $auth->getUsers();
            if ($result['success']) {
                echo json_encode($result['users']);
            } else {
                echo json_encode(['error' => $result['message']]);
            }
            break;
            
        case 'change-password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userId = $_POST['userId'] ?? null;
                $newPassword = $_POST['newPassword'] ?? null;
                
                if (!$userId || !$newPassword) {
                    echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                    return;
                }
                
                $result = $auth->updatePassword($userId, $newPassword);
                echo json_encode($result);
                return;
            }
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
            
        case 'toggle-status':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userId = $_POST['userId'] ?? null;
                
                if (!$userId) {
                    echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
                    return;
                }
                
                $result = $auth->toggleUserStatus($userId);
                echo json_encode($result);
                return;
            }
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
            
        default:
            include __DIR__ . '/../src/views/usuarios/index.php';
            break;
    }
}

/**
 * Manejar gestión de estudiantes
 */
function handleEstudiantes($auth, $action, $id) {
    $estudiante = new Estudiante();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cedula_estudiante' => $_POST['cedula_estudiante'],
                    'apellido' => $_POST['apellido'],
                    'nombre' => $_POST['nombre'],
                    'apellido_2' => $_POST['apellido_2'] ?? null,
                    'sexo' => $_POST['sexo'],
                    'lugar_nacimiento' => $_POST['lugar_nacimiento'] ?? null,
                    'cedula_representante' => $_POST['cedula_representante'] ?? null,
                    'telefono' => $_POST['telefono'] ?? null,
                    'instituciones_id_instituciones' => $_POST['instituciones_id_instituciones'] ?? null
                ];
                
                $result = $estudiante->create($data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cedula_estudiante' => $_POST['cedula_estudiante'],
                    'apellido' => $_POST['apellido'],
                    'nombre' => $_POST['nombre'],
                    'apellido_2' => $_POST['apellido_2'] ?? null,
                    'sexo' => $_POST['sexo'],
                    'lugar_nacimiento' => $_POST['lugar_nacimiento'] ?? null,
                    'cedula_representante' => $_POST['cedula_representante'] ?? null,
                    'telefono' => $_POST['telefono'] ?? null,
                    'instituciones_id_instituciones' => $_POST['instituciones_id_instituciones'] ?? null
                ];
                
                $result = $estudiante->update($id, $data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $estudiante->delete($id);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'get':
            $result = $estudiante->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'list':
            $filters = $_GET;
            $result = $estudiante->getAll($filters);
            echo json_encode(['success' => true, 'data' => $result['data'], 'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit'],
                'total_pages' => $result['total_pages']
            ]]);
            return;
            
        case 'stats':
            $result = $estudiante->getStats();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'search':
            $filters = $_GET;
            $result = $estudiante->search($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        default:
            include __DIR__ . '/../src/views/estudiantes/index.php';
            break;
    }
}

/**
 * Manejar gestión de personal
 */
function handlePersonal($auth, $action, $id) {
    $personal = new Personal();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cedula_personal' => $_POST['cedula_personal'],
                    'apellido' => $_POST['apellido'],
                    'nombre' => $_POST['nombre'],
                    'cargo' => $_POST['cargo'],
                    'fecha_ingreso' => $_POST['fecha_ingreso']
                ];
                
                $result = $personal->create($data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cedula_personal' => $_POST['cedula_personal'],
                    'apellido' => $_POST['apellido'],
                    'nombre' => $_POST['nombre'],
                    'cargo' => $_POST['cargo'],
                    'fecha_ingreso' => $_POST['fecha_ingreso']
                ];
                
                $result = $personal->update($id, $data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $personal->delete($id);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'get':
            $result = $personal->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'list':
            $filters = $_GET;
            $result = $personal->getAll($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'stats':
            $result = $personal->getStats();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        default:
            include __DIR__ . '/../src/views/personal/index.php';
            break;
    }
}

/**
 * Manejar gestión de talleres
 */
function handleTalleres($auth, $action, $id) {
    $taller = new Taller();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'programas_id_programas' => $_POST['programas_id_programas'],
                    'instituciones_id_instituciones' => $_POST['instituciones_id_instituciones'],
                    'personal_id_personal' => $_POST['personal_id_personal'],
                    'cod_taller' => $_POST['cod_taller'],
                    'ano_escolar' => $_POST['ano_escolar'],
                    'grado' => $_POST['grado'] ?? null,
                    'seccion' => $_POST['seccion'] ?? null,
                    'lapso' => $_POST['lapso']
                ];
                
                $result = $taller->create($data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'programas_id_programas' => $_POST['programas_id_programas'],
                    'instituciones_id_instituciones' => $_POST['instituciones_id_instituciones'],
                    'personal_id_personal' => $_POST['personal_id_personal'],
                    'cod_taller' => $_POST['cod_taller'],
                    'ano_escolar' => $_POST['ano_escolar'],
                    'grado' => $_POST['grado'] ?? null,
                    'seccion' => $_POST['seccion'] ?? null,
                    'lapso' => $_POST['lapso']
                ];
                
                $result = $taller->update($id, $data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $taller->delete($id);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'get':
            $result = $taller->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'list':
            $filters = $_GET;
            $result = $taller->getAllWithStudentCount($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'stats':
            $result = $taller->getStats();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'options':
            $result = $taller->getFormOptions();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'add-student':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $taller->addStudent($_POST['taller_id'], $_POST['estudiante_id']);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'students':
            $result = $taller->getStudents($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'remove-student':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $taller->removeStudent($_POST['taller_id'], $_POST['estudiante_id']);
                echo json_encode($result);
                return;
            }
            break;
            
        default:
            include __DIR__ . '/../src/views/talleres/index.php';
            break;
    }
}

/**
 * Manejar gestión de cursos
 */
function handleCursos($auth, $action, $id) {
    $curso = new Curso();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'personal_id_personal' => $_POST['personal_id_personal'],
                    'cod_curso' => $_POST['cod_curso'],
                    'nombre_curso' => $_POST['nombre_curso'],
                    'cedula_persona' => $_POST['cedula_persona'] ?? null,
                    'duracion' => $_POST['duracion'] ?? null,
                    'ano' => $_POST['ano'],
                    'num_de_clases' => $_POST['num_de_clases'] ?? null,
                    'periodo' => $_POST['periodo']
                ];
                
                $result = $curso->create($data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'personal_id_personal' => $_POST['personal_id_personal'],
                    'cod_curso' => $_POST['cod_curso'],
                    'nombre_curso' => $_POST['nombre_curso'],
                    'cedula_persona' => $_POST['cedula_persona'] ?? null,
                    'duracion' => $_POST['duracion'] ?? null,
                    'ano' => $_POST['ano'],
                    'num_de_clases' => $_POST['num_de_clases'] ?? null,
                    'periodo' => $_POST['periodo']
                ];
                
                $result = $curso->update($id, $data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $curso->delete($id);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'get':
            $result = $curso->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'list':
            $filters = $_GET;
            $result = $curso->getAll($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'stats':
            $result = $curso->getStats();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'options':
            $result = $curso->getFormOptions();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        default:
            include __DIR__ . '/../src/views/cursos/index.php';
            break;
    }
}

/**
 * Manejar gestión de inventario
 */
function handleInventario($auth, $action, $id) {
    $inventario = new Inventario();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'personal_id_personal' => $_POST['personal_id_personal'] ?? null,
                    'cod_equipo' => $_POST['cod_equipo'],
                    'ubicacion' => $_POST['ubicacion'] ?? null,
                    'cedula_personal' => $_POST['cedula_personal'] ?? null,
                    'cantidad' => $_POST['cantidad'] ?? 1,
                    'estado' => $_POST['estado'] ?? 'Bueno',
                    'serial' => $_POST['serial'] ?? null,
                    'marca' => $_POST['marca'] ?? null,
                    'modelo' => $_POST['modelo'] ?? null,
                    'color' => $_POST['color'] ?? null,
                    'medidas' => $_POST['medidas'] ?? null,
                    'capacidad' => $_POST['capacidad'] ?? null,
                    'otras_caracteristicas' => $_POST['otras_caracteristicas'] ?? null,
                    'observacion' => $_POST['observacion'] ?? null
                ];
                
                $result = $inventario->create($data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'personal_id_personal' => $_POST['personal_id_personal'] ?? null,
                    'cod_equipo' => $_POST['cod_equipo'],
                    'ubicacion' => $_POST['ubicacion'] ?? null,
                    'cedula_personal' => $_POST['cedula_personal'] ?? null,
                    'cantidad' => $_POST['cantidad'] ?? 1,
                    'estado' => $_POST['estado'] ?? 'Bueno',
                    'serial' => $_POST['serial'] ?? null,
                    'marca' => $_POST['marca'] ?? null,
                    'modelo' => $_POST['modelo'] ?? null,
                    'color' => $_POST['color'] ?? null,
                    'medidas' => $_POST['medidas'] ?? null,
                    'capacidad' => $_POST['capacidad'] ?? null,
                    'otras_caracteristicas' => $_POST['otras_caracteristicas'] ?? null,
                    'observacion' => $_POST['observacion'] ?? null
                ];
                
                $result = $inventario->update($id, $data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $inventario->delete($id);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'get':
            $result = $inventario->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'list':
            $filters = $_GET;
            $result = $inventario->getAll($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'stats':
            $result = $inventario->getStats();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'options':
            $result = $inventario->getFormOptions();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        default:
            include __DIR__ . '/../src/views/inventario/index.php';
            break;
    }
}

/**
 * Manejar gestión de programas
 */
function handleProgramas($auth, $action, $id) {
    $programa = new Programa();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cod_programas' => $_POST['cod_programas'],
                    'descripcion' => $_POST['descripcion'],
                    'sub_area' => $_POST['sub_area'] ?? null,
                    'activo' => $_POST['activo'] ?? 1
                ];
                
                $result = $programa->create($data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cod_programas' => $_POST['cod_programas'],
                    'descripcion' => $_POST['descripcion'],
                    'sub_area' => $_POST['sub_area'] ?? null,
                    'activo' => $_POST['activo'] ?? 1
                ];
                
                $result = $programa->update($id, $data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $programa->delete($id);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'get':
            $result = $programa->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'list':
            $filters = $_GET;
            $result = $programa->getAll($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'stats':
            $result = $programa->getStats();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'options':
            $result = $programa->getFormOptions();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        default:
            include __DIR__ . '/../src/views/programas/index.php';
            break;
    }
}

/**
 * Manejar gestión de instituciones
 */
function handleInstituciones($auth, $action, $id) {
    $institucion = new Institucion();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cod_institucion' => $_POST['cod_institucion'],
                    'descripcion' => $_POST['descripcion'],
                    'telefono_enlace' => $_POST['telefono_enlace'] ?? null,
                    'datos_docente_enlace' => $_POST['datos_docente_enlace'] ?? null,
                    'activo' => $_POST['activo'] ?? 1
                ];
                
                $result = $institucion->create($data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cod_institucion' => $_POST['cod_institucion'],
                    'descripcion' => $_POST['descripcion'],
                    'telefono_enlace' => $_POST['telefono_enlace'] ?? null,
                    'datos_docente_enlace' => $_POST['datos_docente_enlace'] ?? null,
                    'activo' => $_POST['activo'] ?? 1
                ];
                
                $result = $institucion->update($id, $data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $institucion->delete($id);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'get':
            $result = $institucion->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'list':
            $filters = $_GET;
            $result = $institucion->getAll($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'stats':
            $result = $institucion->getStats();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'options':
            $result = $institucion->getFormOptions();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'estudiantes':
            $result = $institucion->getEstudiantesByInstitucion($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        default:
            include __DIR__ . '/../src/views/instituciones/index.php';
            break;
    }
}

/**
 * Manejar gestión de calificaciones
 */
function handleCalificaciones($auth, $action, $id) {
    $calificacion = new Calificacion();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'estudiante_id_estudiante' => $_POST['estudiante_id_estudiante'],
                    'cedula_estudiante' => $_POST['cedula_estudiante'],
                    'cod_taller' => $_POST['cod_taller'],
                    'literal' => $_POST['literal'] ?? null,
                    'numeral' => $_POST['numeral'] ?? null,
                    'momento_i' => $_POST['momento_i'] ?? null,
                    'momento_ii' => $_POST['momento_ii'] ?? null,
                    'momento_iii' => $_POST['momento_iii'] ?? null
                ];
                
                $result = $calificacion->create($data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'literal' => $_POST['literal'] ?? null,
                    'numeral' => $_POST['numeral'] ?? null,
                    'momento_i' => $_POST['momento_i'] ?? null,
                    'momento_ii' => $_POST['momento_ii'] ?? null,
                    'momento_iii' => $_POST['momento_iii'] ?? null
                ];
                
                $result = $calificacion->update($id, $data);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $calificacion->delete($id);
                echo json_encode($result);
                return;
            }
            break;
            
        case 'get':
            $result = $calificacion->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'list':
            $filters = $_GET;
            $result = $calificacion->getAll($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'stats':
            $result = $calificacion->getStats();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'options':
            $result = $calificacion->getFormOptions();
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'by-institucion':
            $result = $calificacion->getByInstitucion($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'by-estudiante':
            $result = $calificacion->getByEstudiante($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'by-student-taller':
            $estudianteId = $_GET['estudiante_id'];
            $tallerId = $_GET['taller_id'];
            $result = $calificacion->getByEstudianteTaller($estudianteId, $tallerId);
            echo json_encode(['success' => true, 'data' => $result]);
            return;
            
        case 'save-grades':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $calificacion->saveGrades($_POST);
                echo json_encode($result);
                return;
            }
            break;
            
        default:
            include __DIR__ . '/../src/views/calificaciones/index.php';
            break;
    }
}

/**
 * Manejar test de conexión
 */
function handleTest() {
    try {
        $db = Database::getInstance();
        $result = $db->fetchOne("SELECT 'Conexión exitosa' as message, NOW() as timestamp");
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Sistema SACRGAPI funcionando correctamente',
            'database' => $result,
            'config' => [
                'app_name' => Config::getAppName(),
                'base_url' => Config::getBaseUrl(),
                'environment' => Config::get('app.env')
            ]
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
