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
require_once __DIR__ . '/../src/includes/MailHelper.php';
require_once __DIR__ . '/../src/includes/Logs.php';
require_once __DIR__ . '/../src/includes/Backup.php';

// Incluir modelos
require_once __DIR__ . '/../src/models/Estudiante.php';
require_once __DIR__ . '/../src/models/Personal.php';
require_once __DIR__ . '/../src/models/Taller.php';
require_once __DIR__ . '/../src/models/Curso.php';
require_once __DIR__ . '/../src/models/Inventario.php';
require_once __DIR__ . '/../src/models/Participante.php';
require_once __DIR__ . '/../src/models/Reparacion.php';
require_once __DIR__ . '/../src/models/Programa.php';
require_once __DIR__ . '/../src/models/Institucion.php';
require_once __DIR__ . '/../src/models/Calificacion.php';
require_once __DIR__ . '/../src/models/Configuracion.php';
require_once __DIR__ . '/../src/models/Directivo.php';

// Cargar configuración
Config::load();

// Iniciar sesión
session_start();

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Obtener el base path desde APP_URL en config.env (ej: /SACRGAPI/public)
// Esto es más confiable que SCRIPT_NAME en XAMPP y otros entornos
$appUrl = Config::get('app.url'); // http://localhost/SACRGAPI/public
$basePath = rtrim(parse_url($appUrl, PHP_URL_PATH) ?? '', '/'); // /SACRGAPI/public

if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Constante global para modelo redirecciones — usa la URL completa de config.env
define('BASE_URL', $appUrl);

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
$publicRoutes = ['login', 'logout', 'recuperar-clave', 'test'];

// Rutas que requieren autenticación pero pueden ser accedidas por AJAX
$ajaxRoutes = ['list', 'get', 'stats', 'options'];

// Verificar autenticación para rutas protegidas
if (!in_array($controller, $publicRoutes) && !$auth->isAuthenticated()) {
    // Permitir acceso a rutas AJAX sin redirección completa
    if (!in_array($action, $ajaxRoutes)) {
        header('Location: ' . BASE_URL . '/login');
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
            
        case 'participantes':
            handleParticipantes($auth, $action, $id);
            break;
        
        case 'backup':
            handleBackup($auth, $action, $id);
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
        
        case 'configuracion':
            handleConfiguracion($auth, $action, $id);
            break;
            
        case 'logs':
            handleLogs($auth);
            break;
            
        case 'recuperar-clave':
            handleRecuperarClave();
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
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            registrarLogIngreso($username, '***', 'Inicio de sesión exitoso', 'login');
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        } else {
            registrarLogIngreso($username, '***', 'Intento de login fallido', 'login');
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
    header('Location: ' . BASE_URL . '/login');
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
 * Manejar módulo de respaldo de base de datos
 */
function handleBackup($auth, $action, $id) {
    // Solo administradores pueden acceder al módulo de respaldo
    if (!$auth->isAuthenticated() || !$auth->isAdmin()) {
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    // Normalizar acción vacía a index
    if ($action === '' || $action === null) {
        $action = 'index';
    }

    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = BackupService::createBackup();
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                if (!empty($result['success'])) {
                    $_SESSION['success'] = $result['message'] ?? 'Respaldo creado correctamente.';
                } else {
                    $_SESSION['error'] = $result['message'] ?? 'Error al crear el respaldo.';
                }
                header('Location: ' . BASE_URL . '/backup');
                exit;
            }
            header('Location: ' . BASE_URL . '/backup');
            exit;

        case 'restore':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $result = null;
                if (!empty($_POST['backup_name'])) {
                    $result = BackupService::restoreFromBackupName($_POST['backup_name']);
                } elseif (!empty($_FILES['backup_file']['name'] ?? '')) {
                    $result = BackupService::restoreFromUploadedFile($_FILES['backup_file']);
                } else {
                    $result = ['success' => false, 'message' => 'Debe seleccionar un archivo o un respaldo existente.'];
                }

                if (!empty($result['success'])) {
                    $_SESSION['success'] = $result['message'] ?? 'Base de datos restaurada correctamente.';
                } else {
                    $_SESSION['error'] = $result['message'] ?? 'Error al restaurar la base de datos.';
                }

                header('Location: ' . BASE_URL . '/backup');
                exit;
            }
            header('Location: ' . BASE_URL . '/backup');
            exit;

        case 'download':
            // Descargar archivo de respaldo por nombre indicado en la URL (/backup/download?file=...)
            $filename = $id ?? '';
            if ($filename === '' && isset($_GET['file'])) {
                $filename = $_GET['file'];
            }
            if ($filename === '') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['error'] = 'Archivo de respaldo no especificado.';
                header('Location: ' . BASE_URL . '/backup');
                exit;
            }
            $filepath = BackupService::getBackupFilePath($filename);
            if (!file_exists($filepath) || pathinfo($filepath, PATHINFO_EXTENSION) !== 'sql') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['error'] = 'Archivo de respaldo no encontrado o inválido.';
                header('Location: ' . BASE_URL . '/backup');
                exit;
            }
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;

        case 'delete':
            // Eliminar respaldo por nombre indicado en la URL (/backup/delete) usando POST o /backup/delete/{filename}
            $filename = $id ?? '';
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if ($filename === '' && isset($_POST['file'])) {
                $filename = $_POST['file'];
            }
            if ($filename === '') {
                $_SESSION['error'] = 'Archivo de respaldo no especificado.';
                header('Location: ' . BASE_URL . '/backup');
                exit;
            }
            $result = BackupService::deleteBackup($filename);
            if (!empty($result['success'])) {
                $_SESSION['success'] = $result['message'] ?? 'Respaldo eliminado correctamente.';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Error al eliminar el respaldo.';
            }
            header('Location: ' . BASE_URL . '/backup');
            exit;

        case 'index':
        default:
            // Listar respaldos y mostrar vista principal
            $backups = BackupService::listBackups();
            include __DIR__ . '/../src/views/backup/index.php';
            return;
    }
}

/**
 * Manejar módulo de Configuración general
 */
function handleConfiguracion($auth, $action, $id) {
    if (!$auth->isAuthenticated() || !$auth->isAdmin()) {
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    $configModel = new Configuracion();
    $directivoModel = new Directivo();

    if ($action === '' || $action === null) {
        $action = 'index';
    }

    switch ($action) {
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $data = [
                    'nombre' => trim($_POST['nombre'] ?? ''),
                    'rif' => trim($_POST['rif'] ?? ''),
                    'direccion' => $_POST['direccion'] ?? null,
                    'telefono' => $_POST['telefono'] ?? null,
                    'email' => $_POST['email'] ?? null,
                ];
                $result = $configModel->update($data);
                if (!empty($result['success'])) {
                    $_SESSION['success'] = 'Configuración actualizada correctamente.';
                } else {
                    $_SESSION['error'] = $result['message'] ?? 'Error al actualizar la configuración.';
                }
                header('Location: ' . BASE_URL . '/configuracion');
                exit;
            }
            header('Location: ' . BASE_URL . '/configuracion');
            exit;

        case 'directivos-create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $data = [
                    'nombre' => trim($_POST['nombre'] ?? ''),
                    'apellido' => trim($_POST['apellido'] ?? ''),
                    'cargo' => trim($_POST['cargo'] ?? ''),
                    'telefono' => $_POST['telefono'] ?? null,
                    'email' => $_POST['email'] ?? null,
                    'firma' => !empty($_POST['firma']) ? 1 : 0,
                ];
                $result = $directivoModel->create($data);
                if (!empty($result['success'])) {
                    $_SESSION['success'] = 'Directivo creado correctamente.';
                } else {
                    $_SESSION['error'] = $result['message'] ?? 'Error al crear directivo.';
                }
                header('Location: ' . BASE_URL . '/configuracion');
                exit;
            }
            $directivo = [
                'nombre' => '',
                'apellido' => '',
                'cargo' => '',
                'telefono' => '',
                'email' => '',
                'firma' => 0,
            ];
            include __DIR__ . '/../src/views/configuracion/directivos_create.php';
            return;

        case 'directivos-edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/configuracion');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $data = [
                    'nombre' => trim($_POST['nombre'] ?? ''),
                    'apellido' => trim($_POST['apellido'] ?? ''),
                    'cargo' => trim($_POST['cargo'] ?? ''),
                    'telefono' => $_POST['telefono'] ?? null,
                    'email' => $_POST['email'] ?? null,
                    'firma' => !empty($_POST['firma']) ? 1 : 0,
                    'activo' => !empty($_POST['activo']) ? 1 : 0,
                ];
                $result = $directivoModel->update((int)$id, $data);
                if (!empty($result['success'])) {
                    $_SESSION['success'] = 'Directivo actualizado correctamente.';
                } else {
                    $_SESSION['error'] = $result['message'] ?? 'Error al actualizar directivo.';
                }
                header('Location: ' . BASE_URL . '/configuracion');
                exit;
            }
            $directivo = $directivoModel->getById((int)$id);
            if (!$directivo) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['error'] = 'Directivo no encontrado.';
                header('Location: ' . BASE_URL . '/configuracion');
                exit;
            }
            include __DIR__ . '/../src/views/configuracion/directivos_edit.php';
            return;

        case 'directivos-delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $result = $directivoModel->delete((int)$id);
                if (!empty($result['success'])) {
                    $_SESSION['success'] = 'Directivo eliminado correctamente.';
                } else {
                    $_SESSION['error'] = $result['message'] ?? 'Error al eliminar directivo.';
                }
            }
            header('Location: ' . BASE_URL . '/configuracion');
            exit;

        case 'index':
        default:
            $configRow = $configModel->get();
            $directivos = $directivoModel->getAll();
            include __DIR__ . '/../src/views/configuracion/index.php';
            return;
    }
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

    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Usuarios', 'usuarios');
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
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/usuarios?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear usuario') : 'Error al crear usuario';
                include __DIR__ . '/../src/views/usuarios/create.php';
                return;
            }
            include __DIR__ . '/../src/views/usuarios/create.php';
            return;
            
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
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userId = $_POST['userId'] ?? null;
                
                if (!$userId) {
                    echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
                    return;
                }
                
                $result = $auth->deleteUser($userId);
                echo json_encode($result);
                return;
            }
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            $usuario = $auth->getUserById($id);
            if (!$usuario) {
                header('Location: ' . BASE_URL . '/usuarios?error=notfound');
                exit;
            }
            include __DIR__ . '/../src/views/usuarios/edit.php';
            break;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido' => trim($_POST['apellido'] ?? ''),
                'rol' => $_POST['rol'] ?? 'operador',
                'activo' => isset($_POST['activo']) ? (int) $_POST['activo'] : 1
            ];
            $result = $auth->updateUser($id, $data);
            if ($result['success']) {
                header('Location: ' . BASE_URL . '/usuarios?success=1');
                exit;
            }
            $usuario = array_merge($auth->getUserById($id) ?? [], $data);
            $error = $result['message'];
            include __DIR__ . '/../src/views/usuarios/edit.php';
            break;

        case 'reportes':
            $configModel = new Configuracion();
            $directivoModel = new Directivo();
            $config = $configModel->get();
            $usuariosResult = $auth->getUsers();
            $usuarios = ($usuariosResult['success'] && !empty($usuariosResult['users'])) ? $usuariosResult['users'] : [];
            $firmantes = $directivoModel->getFirmantes();
            include __DIR__ . '/../src/views/usuarios/reportes.php';
            break;

        case 'reportes-pdf':
            $configModel = new Configuracion();
            $directivoModel = new Directivo();
            $config = $configModel->get();
            $usuariosResult = $auth->getUsers();
            $usuarios = ($usuariosResult['success'] && !empty($usuariosResult['users'])) ? $usuariosResult['users'] : [];
            $firmantes = $directivoModel->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/usuarios/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/usuarios/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('reporte-usuarios.pdf', ['Attachment' => true]);
            exit;

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
    
    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Estudiantes', 'estudiantes');
    }

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
                    'instituciones_id_instituciones' => $_POST['instituciones_id_instituciones'] ?: null
                ];
                $result = $estudiante->create($data);
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/estudiantes?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear') : 'Error al crear';
                $institucionesOptions = (new Institucion())->getFormOptions();
                include __DIR__ . '/../src/views/estudiantes/create.php';
                return;
            }
            $institucionesOptions = (new Institucion())->getFormOptions();
            include __DIR__ . '/../src/views/estudiantes/create.php';
            return;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/estudiantes');
                exit;
            }
            $estudianteRow = $estudiante->getByIdForEdit($id);
            if (!$estudianteRow) {
                header('Location: ' . BASE_URL . '/estudiantes?error=notfound');
                exit;
            }
            $institucionesOptions = (new Institucion())->getFormOptions();
            include __DIR__ . '/../src/views/estudiantes/edit.php';
            return;
            
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
                
                $estudianteModel = new Estudiante();
                $result = $estudianteModel->update($id, $data);
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/estudiantes?success=1');
                        exit;
                    }
                    $estudianteRow = array_merge($estudianteModel->getByIdForEdit($id) ?? [], $data);
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    $institucionesOptions = (new Institucion())->getFormOptions();
                    include __DIR__ . '/../src/views/estudiantes/edit.php';
                    return;
                }
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
            
        case 'reportes':
            $reportResults = [];
            $institucionesOptions = (new Institucion())->getFormOptions();
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $reportResults = $estudiante->getForReport([
                    'search' => trim($_POST['search'] ?? ''),
                    'fecha_desde' => !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null,
                    'fecha_hasta' => !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null,
                    'institucion_id' => !empty($_POST['institucion_id']) ? $_POST['institucion_id'] : null
                ]);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'estudiantes';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/estudiantes/reportes.php';
            return;

        case 'reportes-pdf':
            $estudiante = new Estudiante();
            $reportResults = $estudiante->getForReport([
                'search' => trim($_GET['search'] ?? ''),
                'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
                'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null,
                'institucion_id' => !empty($_GET['institucion_id']) ? $_GET['institucion_id'] : null
            ]);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/estudiantes/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/estudiantes/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('reporte-estudiantes.pdf', ['Attachment' => true]);
            exit;
            
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
    
    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Personal', 'personal');
    }

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
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/personal?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear') : 'Error al crear';
                include __DIR__ . '/../src/views/personal/create.php';
                return;
            }
            include __DIR__ . '/../src/views/personal/create.php';
            return;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/personal');
                exit;
            }
            $personalRow = $personal->getById($id);
            if (!$personalRow) {
                header('Location: ' . BASE_URL . '/personal?error=notfound');
                exit;
            }
            include __DIR__ . '/../src/views/personal/edit.php';
            return;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cedula_personal' => $_POST['cedula_personal'],
                    'apellido' => $_POST['apellido'],
                    'nombre' => $_POST['nombre'],
                    'cargo' => $_POST['cargo'],
                    'fecha_ingreso' => $_POST['fecha_ingreso']
                ];
                
                $personalModel = new Personal();
                $result = $personalModel->update($id, $data);
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/personal?success=1');
                        exit;
                    }
                    $personalRow = array_merge($personalModel->getById($id) ?? [], $data);
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    include __DIR__ . '/../src/views/personal/edit.php';
                    return;
                }
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
            
        case 'reportes':
            $reportResults = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $reportResults = $personal->getForReport([
                    'search' => trim($_POST['search'] ?? ''),
                    'fecha_desde' => !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null,
                    'fecha_hasta' => !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null
                ]);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'personal';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/personal/reportes.php';
            return;

        case 'reportes-pdf':
            $personal = new Personal();
            $reportResults = $personal->getForReport([
                'search' => trim($_GET['search'] ?? ''),
                'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
                'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null
            ]);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/personal/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/personal/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('reporte-personal.pdf', ['Attachment' => true]);
            exit;
            
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
    
    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Talleres', 'talleres');
    }

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
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/talleres?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear') : 'Error al crear';
                $formOptions = $taller->getFormOptions();
                $programasOptions = $formOptions['programas'] ?? [];
                $institucionesOptions = $formOptions['instituciones'] ?? [];
                $personalOptions = $formOptions['personal'] ?? [];
                include __DIR__ . '/../src/views/talleres/create.php';
                return;
            }
            $formOptions = $taller->getFormOptions();
            $programasOptions = $formOptions['programas'] ?? [];
            $institucionesOptions = $formOptions['instituciones'] ?? [];
            $personalOptions = $formOptions['personal'] ?? [];
            include __DIR__ . '/../src/views/talleres/create.php';
            return;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/talleres');
                exit;
            }
            $tallerRow = $taller->getByIdForEdit($id);
            if (!$tallerRow) {
                header('Location: ' . BASE_URL . '/talleres?error=notfound');
                exit;
            }
            $formOptions = $taller->getFormOptions();
            $programasOptions = $formOptions['programas'] ?? [];
            $institucionesOptions = $formOptions['instituciones'] ?? [];
            $personalOptions = $formOptions['personal'] ?? [];
            include __DIR__ . '/../src/views/talleres/edit.php';
            return;
            
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
                
                $tallerModel = new Taller();
                $result = $tallerModel->update($id, $data);
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/talleres?success=1');
                        exit;
                    }
                    $tallerRow = array_merge($tallerModel->getByIdForEdit($id) ?? [], $data);
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    $formOptions = $tallerModel->getFormOptions();
                    $programasOptions = $formOptions['programas'] ?? [];
                    $institucionesOptions = $formOptions['instituciones'] ?? [];
                    $personalOptions = $formOptions['personal'] ?? [];
                    include __DIR__ . '/../src/views/talleres/edit.php';
                    return;
                }
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
            
        case 'reportes':
            $reportResults = [];
            $programasOptions = (new Programa())->getFormOptions();
            $institucionesOptions = (new Institucion())->getFormOptions();
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $reportResults = $taller->getForReport([
                    'search' => trim($_POST['search'] ?? ''),
                    'fecha_desde' => !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null,
                    'fecha_hasta' => !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null,
                    'cedula_instructor' => !empty($_POST['cedula_instructor']) ? $_POST['cedula_instructor'] : null,
                    'programa_id' => !empty($_POST['programa_id']) ? $_POST['programa_id'] : null,
                    'institucion_id' => !empty($_POST['institucion_id']) ? $_POST['institucion_id'] : null,
                ]);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'talleres';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/talleres/reportes.php';
            return;

        case 'reportes-pdf':
            $taller = new Taller();
            $reportResults = $taller->getForReport([
                'search' => trim($_GET['search'] ?? ''),
                'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
                'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null,
                'cedula_instructor' => !empty($_GET['cedula_instructor']) ? $_GET['cedula_instructor'] : null,
                'programa_id' => !empty($_GET['programa_id']) ? $_GET['programa_id'] : null,
                'institucion_id' => !empty($_GET['institucion_id']) ? $_GET['institucion_id'] : null,
            ]);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/talleres/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/talleres/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('reporte-talleres.pdf', ['Attachment' => true]);
            exit;
            
        default:
            include __DIR__ . '/../src/views/talleres/index.php';
            break;
    }
}

/**
 * Manejar gestión de participantes (módulo base)
 */
function handleParticipantes($auth, $action, $id) {
    if (!$auth->isAuthenticated()) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    $participante = new Participante();
    
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cedula_participante' => $_POST['cedula_participante'],
                    'apellido' => $_POST['apellido'],
                    'nombre' => $_POST['nombre'],
                    'telefono' => $_POST['telefono'] ?? null,
                    'correo' => $_POST['correo'] ?? null,
                ];
                
                $result = $participante->create($data);
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/participantes?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear participante') : 'Error al crear participante';
                include __DIR__ . '/../src/views/participantes/create.php';
                return;
            }
            include __DIR__ . '/../src/views/participantes/create.php';
            return;

        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/participantes');
                exit;
            }
            $participanteRow = $participante->getById($id);
            if (!$participanteRow) {
                header('Location: ' . BASE_URL . '/participantes?error=notfound');
                exit;
            }
            include __DIR__ . '/../src/views/participantes/edit.php';
            return;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cedula_participante' => $_POST['cedula_participante'],
                    'apellido' => $_POST['apellido'],
                    'nombre' => $_POST['nombre'],
                    'telefono' => $_POST['telefono'] ?? null,
                    'correo' => $_POST['correo'] ?? null,
                ];
                $result = $participante->update($id, $data);
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/participantes?success=1');
                        exit;
                    }
                    $participanteRow = array_merge($participante->getById($id) ?? [], $data);
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    include __DIR__ . '/../src/views/participantes/edit.php';
                    return;
                }
                echo json_encode($result);
                return;
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $participante->delete($id);
                echo json_encode($result);
                return;
            }
            break;

        case 'get':
            $result = $participante->getById($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;

        case 'list':
            $filters = $_GET;
            $result = $participante->getAll($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;

        case 'search':
            $filters = $_GET;
            $result = $participante->search($filters);
            echo json_encode(['success' => true, 'data' => $result]);
            return;

        case 'reportes':
            $reportResults = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $reportResults = $participante->getForReport([
                    'search' => trim($_POST['search'] ?? ''),
                    'fecha_desde' => !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null,
                    'fecha_hasta' => !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null,
                ]);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'participantes';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/participantes/reportes.php';
            return;

        case 'reportes-pdf':
            $participante = new Participante();
            $reportResults = $participante->getForReport([
                'search' => trim($_GET['search'] ?? ''),
                'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
                'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null,
            ]);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/participantes/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/participantes/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('reporte-participantes.pdf', ['Attachment' => true]);
            exit;

        default:
            include __DIR__ . '/../src/views/participantes/index.php';
            break;
    }
}

/**
 * Manejar gestión de cursos
 */
function handleCursos($auth, $action, $id) {
    $curso = new Curso();
    
    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Cursos', 'cursos');
    }

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
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/cursos?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear curso') : 'Error al crear curso';
                $formOptions = $curso->getFormOptions();
                $instructoresOptions = $formOptions['instructores'] ?? [];
                include __DIR__ . '/../src/views/cursos/create.php';
                return;
            }
            $formOptions = $curso->getFormOptions();
            $instructoresOptions = $formOptions['instructores'] ?? [];
            include __DIR__ . '/../src/views/cursos/create.php';
            return;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }
            $cursoRow = $curso->getByIdForEdit($id);
            if (!$cursoRow) {
                header('Location: ' . BASE_URL . '/cursos?error=notfound');
                exit;
            }
            $formOptions = $curso->getFormOptions();
            $instructoresOptions = $formOptions['instructores'] ?? [];
            include __DIR__ . '/../src/views/cursos/edit.php';
            return;
            
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
                
                $cursoModel = new Curso();
                $result = $cursoModel->update($id, $data);
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/cursos?success=1');
                        exit;
                    }
                    $cursoRow = array_merge($cursoModel->getByIdForEdit($id) ?? [], $data);
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    $formOptions = $cursoModel->getFormOptions();
                    $instructoresOptions = $formOptions['instructores'] ?? [];
                    include __DIR__ . '/../src/views/cursos/edit.php';
                    return;
                }
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
            
        case 'reportes':
            $reportResults = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $cedulaParticipante = null;
                if (!empty($_POST['cedula_participante'])) {
                    // Mantener solo dígitos para la búsqueda por cédula
                    $digits = preg_replace('/\D/', '', $_POST['cedula_participante']);
                    $cedulaParticipante = $digits !== '' ? $digits : null;
                }

                $reportResults = $curso->getForReport([
                    'search' => trim($_POST['search'] ?? ''),
                    'cedula_participante' => $cedulaParticipante,
                    'fecha_desde' => !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null,
                    'fecha_hasta' => !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null
                ]);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'cursos';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/cursos/reportes.php';
            return;

        case 'reportes-pdf':
            $curso = new Curso();
            $cedulaParticipante = null;
            if (!empty($_GET['cedula_participante'])) {
                $digits = preg_replace('/\D/', '', $_GET['cedula_participante']);
                $cedulaParticipante = $digits !== '' ? $digits : null;
            }
            $reportResults = $curso->getForReport([
                'search' => trim($_GET['search'] ?? ''),
                'cedula_participante' => $cedulaParticipante,
                'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
                'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null
            ]);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/cursos/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/cursos/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('reporte-cursos.pdf', ['Attachment' => true]);
            exit;

        case 'participantes':
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID de curso no proporcionado']);
                return;
            }
            $participanteModel = new Participante();
            $result = $participanteModel->getByCurso($id);
            echo json_encode(['success' => true, 'data' => $result]);
            return;

        case 'add-participante':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $cursoId = isset($_POST['curso_id']) ? (int)$_POST['curso_id'] : 0;
                $participanteId = isset($_POST['participante_id']) ? (int)$_POST['participante_id'] : 0;

                if ($cursoId <= 0 || $participanteId <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Datos incompletos para asociar participante']);
                    return;
                }

                $participanteModel = new Participante();
                $result = $participanteModel->addToCurso($cursoId, $participanteId);
                echo json_encode($result);
                return;
            }
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;

        case 'remove-participante':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $cursoId = isset($_POST['curso_id']) ? (int)$_POST['curso_id'] : 0;
                $participanteId = isset($_POST['participante_id']) ? (int)$_POST['participante_id'] : 0;

                if ($cursoId <= 0 || $participanteId <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Datos incompletos para quitar participante']);
                    return;
                }

                $participanteModel = new Participante();
                $result = $participanteModel->removeFromCurso($cursoId, $participanteId);
                echo json_encode($result);
                return;
            }
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
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
    
    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Inventario', 'inventario');
    }

    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'personal_id_personal' => $_POST['personal_id_personal'] ?? null,
                    'cod_equipo' => $_POST['cod_equipo'],
                    'nombre' => $_POST['nombre'] ?? null,
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
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/inventario?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear') : 'Error al crear';
                $opts = $inventario->getFormOptions();
                $responsablesOptions = $opts['responsables'] ?? [];
                $ubicacionesOptions = $opts['ubicaciones'] ?? [];
                include __DIR__ . '/../src/views/inventario/create.php';
                return;
            }
            $opts = $inventario->getFormOptions();
            $responsablesOptions = $opts['responsables'] ?? [];
            $ubicacionesOptions = $opts['ubicaciones'] ?? [];
            include __DIR__ . '/../src/views/inventario/create.php';
            return;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/inventario');
                exit;
            }
            $inventarioRow = $inventario->getById($id);
            if (!$inventarioRow) {
                header('Location: ' . BASE_URL . '/inventario?error=notfound');
                exit;
            }
            $opts = $inventario->getFormOptions();
            $responsablesOptions = $opts['responsables'] ?? [];
            $ubicacionesOptions = $opts['ubicaciones'] ?? [];
            $reparacionModel = new Reparacion();
            $reparaciones = $reparacionModel->getByInventarioId($id);
            include __DIR__ . '/../src/views/inventario/edit.php';
            return;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'personal_id_personal' => $_POST['personal_id_personal'] ?? null,
                    'cod_equipo' => $_POST['cod_equipo'],
                    'nombre' => $_POST['nombre'] ?? null,
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
                // Si responsable viene vacío, mantener el valor actual (la columna puede ser NOT NULL)
                $postResponsable = $_POST['personal_id_personal'] ?? '';
                if ($postResponsable === '' || $postResponsable === null) {
                    $current = $inventario->getById($id);
                    if ($current && array_key_exists('personal_id_personal', $current)) {
                        $data['personal_id_personal'] = $current['personal_id_personal'];
                    }
                }
                
                $result = $inventario->update($id, $data);
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/inventario?success=1');
                        exit;
                    }
                    $inventarioRow = array_merge($inventario->getById($id) ?? [], $data);
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    $opts = $inventario->getFormOptions();
                    $responsablesOptions = $opts['responsables'] ?? [];
                    $ubicacionesOptions = $opts['ubicaciones'] ?? [];
                    $reparacionModel = new Reparacion();
                    $reparaciones = $reparacionModel->getByInventarioId($id);
                    include __DIR__ . '/../src/views/inventario/edit.php';
                    return;
                }
                echo json_encode($result);
                return;
            }
            break;
            
        case 'reparar':
            if (!$id) {
                header('Location: ' . BASE_URL . '/inventario');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $fecha = trim($_POST['fecha'] ?? '');
                $motivo = trim($_POST['motivo'] ?? '');
                if ($fecha === '' || $motivo === '') {
                    $item = $inventario->getById($id);
                    $error = 'Fecha y motivo son obligatorios.';
                    include __DIR__ . '/../src/views/inventario/reparar.php';
                    return;
                }
                $reparacion = new Reparacion();
                $result = $reparacion->create([
                    'inventario_id_inventario' => (int) $id,
                    'fecha' => $fecha,
                    'motivo' => $motivo
                ]);
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/inventario?success=1');
                    exit;
                }
                $item = $inventario->getById($id);
                $error = is_array($result) ? ($result['message'] ?? 'Error al registrar la reparación') : 'Error al registrar la reparación';
                include __DIR__ . '/../src/views/inventario/reparar.php';
                return;
            }
            $item = $inventario->getById($id);
            if (!$item) {
                header('Location: ' . BASE_URL . '/inventario?error=notfound');
                exit;
            }
            include __DIR__ . '/../src/views/inventario/reparar.php';
            return;
            
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
            
        case 'reportes':
            $reportResults = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $reportResults = $inventario->getForReport([
                    'search' => trim($_POST['search'] ?? ''),
                    'fecha_desde' => !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null,
                    'fecha_hasta' => !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null
                ]);
                $reparacionModel = new Reparacion();
                foreach ($reportResults as &$row) {
                    $row['reparaciones'] = $reparacionModel->getByInventarioId($row['id_inventario'] ?? 0);
                }
                unset($row);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'inventario';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/inventario/reportes.php';
            return;

        case 'reportes-pdf':
            $inventario = new Inventario();
            $reportResults = $inventario->getForReport([
                'search' => trim($_GET['search'] ?? ''),
                'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
                'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null
            ]);
            $reparacionModel = new Reparacion();
            foreach ($reportResults as &$row) {
                $row['reparaciones'] = $reparacionModel->getByInventarioId($row['id_inventario'] ?? 0);
            }
            unset($row);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/inventario/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/inventario/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream('reporte-inventario.pdf', ['Attachment' => true]);
            exit;
            
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
    
    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Programas', 'programas');
    }

    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cod_programas' => $_POST['cod_programas'],
                    'descripcion' => $_POST['descripcion'],
                    'sub_area' => $_POST['sub_area'] ?? null,
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                $result = $programa->create($data);
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/programas?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear') : 'Error al crear';
                include __DIR__ . '/../src/views/programas/create.php';
                return;
            }
            include __DIR__ . '/../src/views/programas/create.php';
            return;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/programas');
                exit;
            }
            $programaData = $programa->getById($id);
            if (!$programaData) {
                header('Location: ' . BASE_URL . '/programas?error=notfound');
                exit;
            }
            $programaRow = $programaData;
            include __DIR__ . '/../src/views/programas/edit.php';
            return;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cod_programas' => $_POST['cod_programas'],
                    'descripcion' => $_POST['descripcion'],
                    'sub_area' => $_POST['sub_area'] ?? null,
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                
                $programaModel = new Programa();
                $result = $programaModel->update($id, $data);
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/programas?success=1');
                        exit;
                    }
                    $programaRow = array_merge($programaModel->getById($id) ?? [], $data);
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    include __DIR__ . '/../src/views/programas/edit.php';
                    return;
                }
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
            
        case 'reportes':
            $reportResults = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $reportResults = $programa->getForReport([
                    'search' => trim($_POST['search'] ?? ''),
                    'fecha_desde' => !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null,
                    'fecha_hasta' => !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null
                ]);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'programas';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/programas/reportes.php';
            return;

        case 'reportes-pdf':
            $programa = new Programa();
            $reportResults = $programa->getForReport([
                'search' => trim($_GET['search'] ?? ''),
                'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
                'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null
            ]);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/programas/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/programas/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('reporte-programas.pdf', ['Attachment' => true]);
            exit;
            
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
    
    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Instituciones', 'instituciones');
    }

    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cod_institucion' => $_POST['cod_institucion'],
                    'descripcion' => $_POST['descripcion'],
                    'telefono_enlace' => $_POST['telefono_enlace'] ?? null,
                    'datos_docente_enlace' => $_POST['datos_docente_enlace'] ?? null,
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                $result = $institucion->create($data);
                if (is_array($result) && !empty($result['success'])) {
                    header('Location: ' . BASE_URL . '/instituciones?success=1');
                    exit;
                }
                $error = is_array($result) ? ($result['message'] ?? 'Error al crear') : 'Error al crear';
                include __DIR__ . '/../src/views/instituciones/create.php';
                return;
            }
            include __DIR__ . '/../src/views/instituciones/create.php';
            return;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/instituciones');
                exit;
            }
            $institucionRow = $institucion->getById($id);
            if (!$institucionRow) {
                header('Location: ' . BASE_URL . '/instituciones?error=notfound');
                exit;
            }
            include __DIR__ . '/../src/views/instituciones/edit.php';
            return;
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'cod_institucion' => $_POST['cod_institucion'],
                    'descripcion' => $_POST['descripcion'],
                    'telefono_enlace' => $_POST['telefono_enlace'] ?? null,
                    'datos_docente_enlace' => $_POST['datos_docente_enlace'] ?? null,
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                
                $institucionModel = new Institucion();
                $result = $institucionModel->update($id, $data);
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/instituciones?success=1');
                        exit;
                    }
                    $institucionRow = array_merge($institucionModel->getById($id) ?? [], $data);
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    include __DIR__ . '/../src/views/instituciones/edit.php';
                    return;
                }
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
            
        case 'reportes':
            $reportResults = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $reportResults = $institucion->getForReport([
                    'search' => trim($_POST['search'] ?? ''),
                    'fecha_desde' => !empty($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null,
                    'fecha_hasta' => !empty($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null
                ]);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'instituciones';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/instituciones/reportes.php';
            return;

        case 'reportes-pdf':
            $institucion = new Institucion();
            $reportResults = $institucion->getForReport([
                'search' => trim($_GET['search'] ?? ''),
                'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
                'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null
            ]);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/instituciones/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/instituciones/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('reporte-instituciones.pdf', ['Attachment' => true]);
            exit;
            
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
    
    if ($action === '' || $action === null) {
        registrarLogIngreso($_SESSION['username'] ?? 'desconocido', '***', 'Ingreso al módulo Calificaciones', 'calificaciones');
    }

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
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/calificaciones?created=1');
                        exit;
                    }
                    $error = is_array($result) ? ($result['message'] ?? 'Error al crear calificación') : 'Error al crear calificación';
                    $data = $data ?? [];
                    $formOptions = $calificacion->getFormOptions();
                    $currentModule = 'calificaciones';
                    $currentSection = 'create';
                    include __DIR__ . '/../src/views/calificaciones/create.php';
                    return;
                }
                echo json_encode($result);
                return;
            }
            $formOptions = $calificacion->getFormOptions();
            $currentModule = 'calificaciones';
            $currentSection = 'create';
            include __DIR__ . '/../src/views/calificaciones/create.php';
            return;
            
        case 'edit':
            if (!$id) {
                header('Location: ' . BASE_URL . '/calificaciones');
                exit;
            }
            $calificacionRow = $calificacion->getById($id);
            if (!$calificacionRow) {
                header('Location: ' . BASE_URL . '/calificaciones?error=notfound');
                exit;
            }
            $currentModule = 'calificaciones';
            $currentSection = 'edit';
            include __DIR__ . '/../src/views/calificaciones/edit.php';
            return;
            
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
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    if (is_array($result) && !empty($result['success'])) {
                        header('Location: ' . BASE_URL . '/calificaciones?updated=1');
                        exit;
                    }
                    $calificacionRow = array_merge($calificacion->getById($id) ?? [], $_POST);
                    $currentModule = 'calificaciones';
                    $currentSection = 'edit';
                    $error = is_array($result) ? ($result['message'] ?? 'Error al actualizar') : 'Error al actualizar';
                    include __DIR__ . '/../src/views/calificaciones/edit.php';
                    return;
                }
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
            
        case 'reportes':
            $reportResults = [];
            $formOptions = $calificacion->getFormOptions();
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['search']))) {
                $reportResults = $calificacion->getAll([
                    'search' => trim($_POST['search'] ?? ''),
                    'institucion_id' => !empty($_POST['institucion_id']) ? $_POST['institucion_id'] : null,
                    'cedula_estudiante' => !empty($_POST['cedula_estudiante']) ? $_POST['cedula_estudiante'] : null,
                    'estudiante_id' => !empty($_POST['estudiante_id']) ? $_POST['estudiante_id'] : null,
                    'cod_taller' => !empty($_POST['cod_taller']) ? $_POST['cod_taller'] : null,
                    'ano' => !empty($_POST['ano']) ? $_POST['ano'] : null,
                    'lapso' => !empty($_POST['lapso']) ? $_POST['lapso'] : null,
                    'seccion' => !empty($_POST['seccion']) ? $_POST['seccion'] : null,
                    'grado' => !empty($_POST['grado']) ? $_POST['grado'] : null
                ]);
            }
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $currentModule = 'calificaciones';
            $currentSection = 'reportes';
            include __DIR__ . '/../src/views/calificaciones/reportes.php';
            return;

        case 'reportes-pdf':
            $calificacion = new Calificacion();
            $reportResults = $calificacion->getAll([
                'search' => trim($_GET['search'] ?? ''),
                'institucion_id' => !empty($_GET['institucion_id']) ? $_GET['institucion_id'] : null,
                'cedula_estudiante' => !empty($_GET['cedula_estudiante']) ? $_GET['cedula_estudiante'] : null,
                'estudiante_id' => !empty($_GET['estudiante_id']) ? $_GET['estudiante_id'] : null,
                'cod_taller' => !empty($_GET['cod_taller']) ? $_GET['cod_taller'] : null,
                'ano' => !empty($_GET['ano']) ? $_GET['ano'] : null,
                'lapso' => !empty($_GET['lapso']) ? $_GET['lapso'] : null,
                'seccion' => !empty($_GET['seccion']) ? $_GET['seccion'] : null,
                'grado' => !empty($_GET['grado']) ? $_GET['grado'] : null
            ]);
            $config = (new Configuracion())->get();
            $firmantes = (new Directivo())->getFirmantes();
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!is_file($autoload)) {
                header('Location: ' . BASE_URL . '/calificaciones/reportes?error=pdf');
                exit;
            }
            require_once $autoload;
            ob_start();
            include __DIR__ . '/../src/views/calificaciones/reportes_pdf_content.php';
            $html = ob_get_clean();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream('reporte-calificaciones.pdf', ['Attachment' => true]);
            exit;
            
        default:
            $currentModule = 'calificaciones';
            $currentSection = 'index';
            include __DIR__ . '/../src/views/calificaciones/index.php';
            break;
    }
}

/**
 * Trazas / Logs de ingreso (solo administrador)
 */
function handleLogs($auth) {
    if (!$auth->isAuthenticated()) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    if (!$auth->isAdmin()) {
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    ensureLogTable();

    $db = Database::getInstance();
    $logs = $db->fetchAll("SELECT id, IP, `Time`, Details, `Page`, clave, usuario FROM `log` ORDER BY `Time` DESC LIMIT 500");
    include __DIR__ . '/../src/views/logs/index.php';
}

/**
 * Recuperar clave: formulario solo con usuario; se genera nueva clave y se envía por correo.
 */
function handleRecuperarClave() {
    $error = null;
    $success = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        if ($username === '') {
            $error = 'Ingrese su nombre de usuario.';
        } else {
            $auth = new Auth();
            $user = $auth->getUsuarioByUsernameForRecovery($username);
            if (!$user) {
                $error = 'Usuario no encontrado o cuenta inactiva.';
            } elseif (empty(trim($user['email'] ?? ''))) {
                $error = 'Este usuario no tiene correo registrado. Contacte al administrador.';
            } else {
                $nuevaClave = bin2hex(random_bytes(6));
                if ($auth->setPasswordByUserIdForRecovery($user['id_usuario'], $nuevaClave)) {
                    $nombre = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? ''));
                    if (MailHelper::sendRecoveryPassword($user['email'], $nombre ?: $user['username'], $nuevaClave)) {
                        $success = 'Se ha generado una nueva contraseña y se ha enviado a su correo electrónico. Revise su bandeja de entrada (y spam).';
                    } else {
                        $error = 'La contraseña se actualizó pero no se pudo enviar el correo. Contacte al administrador para configurar el envío de correos.';
                    }
                } else {
                    $error = 'Error al actualizar la contraseña. Intente más tarde.';
                }
            }
        }
    }

    include __DIR__ . '/../src/views/recuperar_clave.php';
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
