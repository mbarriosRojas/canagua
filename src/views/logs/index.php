<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trazas / Logs de ingreso - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #667eea; --secondary-color: #764ba2; }
        body { background-color: #f8f9fa; }
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
        .main-content { padding: 2rem; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .table-responsive { border-radius: 10px; overflow: hidden; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-graduation-cap fa-2x text-white mb-2"></i>
                        <h5 class="text-white"><?php echo Config::getAppName(); ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/usuarios"><i class="fas fa-users"></i> Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/programas"><i class="fas fa-list-alt"></i> Programas</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/instituciones"><i class="fas fa-building"></i> Instituciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/estudiantes"><i class="fas fa-user-graduate"></i> Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/talleres"><i class="fas fa-tools"></i> Talleres</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/calificaciones"><i class="fas fa-chart-line"></i> Calificaciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/cursos"><i class="fas fa-book"></i> Cursos</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/personal"><i class="fas fa-chalkboard-teacher"></i> Personal</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/inventario"><i class="fas fa-boxes"></i> Inventario</a></li>

                        <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link active" href="<?php echo BASE_URL; ?>/logs"><i class="fas fa-history"></i> Trazas / Logs de ingreso</a></li>
                        <?php endif; ?>

                        <li class="nav-item mt-4"><a class="nav-link" href="<?php echo BASE_URL; ?>/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-history me-2"></i>Trazas / Logs de ingreso</h1>
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted small">Últimos 500 registros. Solo administradores.</p>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Id</th>
                                        <th>Fecha/Hora</th>
                                        <th>Usuario</th>
                                        <th>IP</th>
                                        <th>Detalle</th>
                                        <th>Página</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">No hay registros.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($logs as $row): ?>
                                    <tr>
                                        <td><?php echo (int)$row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['Time'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['usuario'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['IP'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['Details'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['Page'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <?php include __DIR__ . '/partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
