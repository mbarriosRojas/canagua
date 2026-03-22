<?php
$config = $config ?? [];
$usuarios = $usuarios ?? [];
$firmantes = $firmantes ?? [];
$nombrePlataforma = $config['nombre'] ?? 'Sistema';
$currentModule = null;
$currentSection = 'reportes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de usuarios - <?php echo htmlspecialchars($nombrePlataforma); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #667eea; --secondary-color: #764ba2; }
        body { background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1); }
        .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); padding: 12px 20px; border-radius: 8px; margin: 5px 10px; transition: all 0.3s ease; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.2); transform: translateX(5px); }
        .main-content { padding: 2rem; }
        .report-header { text-align: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #dee2e6; }
        .report-title { font-size: 1.5rem; font-weight: 600; margin-top: 0.5rem; }
        .firmas-block { margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #dee2e6; }
        .firma-item { display: inline-block; text-align: center; margin: 1rem 2rem 1rem 0; min-width: 180px; }
        .firma-line { border-bottom: 1px solid #333; width: 100%; height: 2.5rem; margin-bottom: 0.25rem; }
        .firma-nombre { font-weight: 600; font-size: 0.9rem; }
        .firma-cargo { font-size: 0.8rem; color: #6c757d; }
        @media print {
            .sidebar, .no-print, .btn, nav, .border-bottom { display: none !important; }
            .main-content { max-width: 100% !important; padding: 0; }
            body { background: white; }
            .report-header, .report-title, .table, .firmas-block { break-inside: avoid; }
        }
        @page { margin: 0.5in; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
                    <h1 class="h2"><i class="fas fa-file-alt me-2"></i>Reporte de usuarios</h1>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/usuarios/reportes-pdf" class="btn btn-success me-2">
                            <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                        </a>
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="window.print();">
                            <i class="fas fa-print me-1"></i> Imprimir
                        </button>
                        <a href="<?php echo BASE_URL; ?>/usuarios" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'pdf'): ?>
                <div class="alert alert-warning no-print">
                    Para descargar el PDF debe instalar dependencias: ejecute <code>composer install</code> en la raíz del proyecto.
                </div>
                <?php endif; ?>

                <div class="report-header">
                    <h2 class="mb-0"><?php echo htmlspecialchars($nombrePlataforma); ?></h2>
                    <p class="report-title">Reporte de usuarios</p>
                    <p class="text-muted small mb-0">Fecha: <?php echo date('d/m/Y H:i'); ?></p>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre completo</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último acceso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?php echo (int)($u['id_usuario'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($u['username'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''))); ?></td>
                                <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($u['rol'] ?? ''); ?></td>
                                <td><?php echo !empty($u['activo']) ? 'Activo' : 'Inactivo'; ?></td>
                                <td><?php echo !empty($u['ultimo_acceso']) ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : 'Nunca'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($usuarios)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No hay usuarios registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($firmantes)): ?>
                <div class="firmas-block">
                    <p class="small text-muted mb-2">Firmas:</p>
                    <?php foreach ($firmantes as $f): ?>
                    <div class="firma-item">
                        <div class="firma-line"></div>
                        <div class="firma-nombre"><?php echo htmlspecialchars(trim(($f['nombre'] ?? '') . ' ' . ($f['apellido'] ?? ''))); ?></div>
                        <div class="firma-cargo"><?php echo htmlspecialchars($f['cargo'] ?? ''); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
