<?php
$reportResults = $reportResults ?? [];
$config = $config ?? [];
$firmantes = $firmantes ?? [];
$reportTitle = 'Reporte de personal';
$currentModule = $currentModule ?? 'personal';
$currentSection = 'reportes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Personal - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #667eea; --secondary-color: #764ba2; }
        body { background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1); }
        .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); padding: 12px 20px; border-radius: 8px; margin: 5px 10px; transition: all 0.3s ease; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.2); transform: translateX(5px); }
        .main-content { padding: 2rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border: none; border-radius: 8px; }
        @media print { .sidebar, .no-print, .btn, nav, .border-bottom { display: none !important; } .main-content { max-width: 100% !important; } body { background: white; } }
        <?php include __DIR__ . '/../partials/report_print_styles.php'; ?>
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
                    <h1 class="h2"><i class="fas fa-file-alt me-2"></i>Reportes - Personal</h1>
                    <a href="<?php echo BASE_URL; ?>/personal" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
                </div>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'pdf'): ?>
                <div class="alert alert-warning no-print">
                    Para descargar el PDF debe instalar dependencias: ejecute <code>composer install</code> en la raíz del proyecto.
                </div>
                <?php endif; ?>
                <form method="POST" class="card mb-4 no-print">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Filtros</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Nombre o cédula</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($_POST['search'] ?? ''); ?>" placeholder="Buscar por nombre o cédula">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label">Fecha creación desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($_POST['fecha_desde'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_hasta" class="form-label">Fecha creación hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($_POST['fecha_hasta'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="buscar" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Buscar</button>
                            </div>
                        </div>
                    </div>
                </form>
                <?php if (isset($_POST['buscar']) || isset($_POST['search'])): ?>
                <?php include __DIR__ . '/../partials/report_membrete.php'; ?>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                            <h5 class="mb-0">Resultados (<?php echo count($reportResults); ?> registros)</h5>
                            <form method="get" action="<?php echo BASE_URL; ?>/personal/reportes-pdf" class="d-inline">
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_POST['search'] ?? ''); ?>">
                                <input type="hidden" name="fecha_desde" value="<?php echo htmlspecialchars($_POST['fecha_desde'] ?? ''); ?>">
                                <input type="hidden" name="fecha_hasta" value="<?php echo htmlspecialchars($_POST['fecha_hasta'] ?? ''); ?>">
                                <button type="submit" class="btn btn-success"><i class="fas fa-file-pdf me-1"></i> Imprimir / PDF</button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Apellido</th>
                                        <th>Nombre</th>
                                        <th>Cargo</th>
                                        <th>Fecha creación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reportResults)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">No se encontraron registros con los filtros indicados.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($reportResults as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['cedula_personal'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['apellido'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['cargo'] ?? '-'); ?></td>
                                        <td><?php echo !empty($row['fecha_creacion']) ? date('d/m/Y', strtotime($row['fecha_creacion'])) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php include __DIR__ . '/../partials/report_firmas.php'; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p class="mb-0">Use los filtros y pulse <strong>Buscar</strong> para generar el reporte.</p>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <?php include __DIR__ . '/../partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
