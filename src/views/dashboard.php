<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-graduation-cap fa-2x text-white mb-2"></i>
                        <h5 class="text-white"><?php echo Config::getAppName(); ?></h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/public/dashboard">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/usuarios">
                                <i class="fas fa-users"></i>
                                Usuarios
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/programas">
                                <i class="fas fa-list-alt"></i>
                                Programas
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/instituciones">
                                <i class="fas fa-building"></i>
                                Instituciones
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/estudiantes">
                                <i class="fas fa-user-graduate"></i>
                                Estudiantes
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/personal">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Personal
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/talleres">
                                <i class="fas fa-tools"></i>
                                Talleres
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/cursos">
                                <i class="fas fa-book"></i>
                                Cursos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/inventario">
                                <i class="fas fa-boxes"></i>
                                Inventario
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/calificaciones">
                                <i class="fas fa-chart-line"></i>
                                Calificaciones
                            </a>
                        </li>
                        
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="/public/logout">
                                <i class="fas fa-sign-out-alt"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg">
                    <div class="container-fluid">
                        <h4 class="mb-0">Bienvenido al Sistema SACRGAPI</h4>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-3">Usuario</span>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/public/logout"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-number"><?php echo $dashboardStats['estudiantes_activos']; ?></div>
                                        <div class="text-white-50 small">Estudiantes Activos</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-graduate fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-number"><?php echo $dashboardStats['personal_activo']; ?></div>
                                        <div class="text-white-50 small">Personal Activo</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chalkboard-teacher fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-number"><?php echo $dashboardStats['talleres_activos']; ?></div>
                                        <div class="text-white-50 small">Talleres Activos</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tools fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-number"><?php echo $dashboardStats['cursos_en_progreso']; ?></div>
                                        <div class="text-white-50 small">Cursos en Progreso</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-book fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Actividad Reciente
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (empty($dashboardStats['actividad_reciente'])): ?>
                                        <div class="list-group-item d-flex justify-content-center align-items-center">
                                            <i class="fas fa-info-circle text-muted me-2"></i>
                                            No hay actividad reciente
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($dashboardStats['actividad_reciente'] as $actividad): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <?php
                                                    $icon = '';
                                                    $color = '';
                                                    switch ($actividad['tipo']) {
                                                        case 'estudiante':
                                                            $icon = 'fas fa-user-plus text-success';
                                                            break;
                                                        case 'taller':
                                                            $icon = 'fas fa-tools text-primary';
                                                            break;
                                                        case 'curso':
                                                            $icon = 'fas fa-book text-info';
                                                            break;
                                                        case 'personal':
                                                            $icon = 'fas fa-chalkboard-teacher text-warning';
                                                            break;
                                                        default:
                                                            $icon = 'fas fa-circle text-secondary';
                                                    }
                                                    ?>
                                                    <i class="<?php echo $icon; ?> me-2"></i>
                                                    <?php echo htmlspecialchars($actividad['descripcion'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <small class="text-muted"><?php echo $actividad['tiempo_relativo']; ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    Acciones Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="/public/estudiantes" class="btn btn-outline-primary">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Registrar Estudiante
                                    </a>
                                    <a href="/public/talleres" class="btn btn-outline-success">
                                        <i class="fas fa-tools me-2"></i>
                                        Crear Taller
                                    </a>
                                    <a href="/public/cursos" class="btn btn-outline-info">
                                        <i class="fas fa-book me-2"></i>
                                        Nuevo Curso
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dashboard con datos cargados desde PHP
        console.log('Dashboard cargado con datos reales desde PHP');
        
        // Función para recargar la página cada 5 minutos (opcional)
        // setInterval(() => {
        //     window.location.reload();
        // }, 300000);
    </script>
</body>
</html>
