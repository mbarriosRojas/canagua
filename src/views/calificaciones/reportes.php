<?php
$reportResults = $reportResults ?? [];
$formOptions = $formOptions ?? ['estudiantes' => [], 'talleres' => [], 'instituciones' => [], 'anos' => [], 'lapsos' => [], 'grados' => [], 'secciones' => []];
$estudiantesOptions = $formOptions['estudiantes'] ?? [];
$talleresOptions = $formOptions['talleres'] ?? [];
$institucionesOptions = $formOptions['instituciones'] ?? [];
$anosOptions = $formOptions['anos'] ?? [];
$lapsosOptions = $formOptions['lapsos'] ?? [];
$gradosOptions = $formOptions['grados'] ?? [];
$seccionesOptions = $formOptions['secciones'] ?? [];
$config = $config ?? [];
$firmantes = $firmantes ?? [];
$reportTitle = 'Reporte de calificaciones';
$currentModule = $currentModule ?? 'calificaciones';
$currentSection = 'reportes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Calificaciones - <?php echo Config::getAppName(); ?></title>
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
        @media print {
            .sidebar, .no-print, .btn, nav, .border-bottom { display: none !important; }
            .main-content { max-width: 100% !important; }
            body { background: white; }
        }
        @page {
            size: landscape;
        }
        <?php include __DIR__ . '/../partials/report_print_styles.php'; ?>
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
                    <h1 class="h2"><i class="fas fa-file-alt me-2"></i>Reportes - Calificaciones</h1>
                    <a href="<?php echo BASE_URL; ?>/calificaciones" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
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
                            <div class="col-md-2">
                                <label for="ano" class="form-label">Año</label>
                                <select class="form-select" id="ano" name="ano">
                                    <option value="">Todos</option>
                                    <?php foreach ($anosOptions as $a): ?>
                                    <option value="<?php echo htmlspecialchars($a['ano_escolar'] ?? ''); ?>" <?php echo (isset($_POST['ano']) && ($_POST['ano'] ?? '') === ($a['ano_escolar'] ?? '')) ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['ano_escolar'] ?? ''); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="institucion_id" class="form-label">Institución</label>
                                <select class="form-select" id="institucion_id" name="institucion_id">
                                    <option value="">Todas</option>
                                    <?php foreach ($institucionesOptions as $i): ?>
                                    <option value="<?php echo (int) $i['id_instituciones']; ?>" <?php echo (isset($_POST['institucion_id']) && (int)$_POST['institucion_id'] === (int)$i['id_instituciones']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($i['descripcion'] ?? ''); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="lapso" class="form-label">Lapso</label>
                                <select class="form-select" id="lapso" name="lapso">
                                    <option value="">Todos</option>
                                    <?php foreach ($lapsosOptions as $l): ?>
                                    <option value="<?php echo htmlspecialchars($l['lapso'] ?? ''); ?>" <?php echo (isset($_POST['lapso']) && ($_POST['lapso'] ?? '') === ($l['lapso'] ?? '')) ? 'selected' : ''; ?>><?php echo htmlspecialchars($l['lapso'] ?? ''); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="seccion" class="form-label">Sección</label>
                                <select class="form-select" id="seccion" name="seccion">
                                    <option value="">Todas</option>
                                    <?php foreach ($seccionesOptions as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s['seccion'] ?? ''); ?>" <?php echo (isset($_POST['seccion']) && ($_POST['seccion'] ?? '') === ($s['seccion'] ?? '')) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['seccion'] ?? ''); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="grado" class="form-label">Grado</label>
                                <select class="form-select" id="grado" name="grado">
                                    <option value="">Todos</option>
                                    <?php foreach ($gradosOptions as $g): ?>
                                    <option value="<?php echo htmlspecialchars($g['grado'] ?? ''); ?>" <?php echo (isset($_POST['grado']) && ($_POST['grado'] ?? '') === ($g['grado'] ?? '')) ? 'selected' : ''; ?>><?php echo htmlspecialchars($g['grado'] ?? ''); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="buscar" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Buscar</button>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label for="cedula_estudiante" class="form-label">Cédula del estudiante</label>
                                <input type="text" class="form-control" id="cedula_estudiante" name="cedula_estudiante" value="<?php echo htmlspecialchars($_POST['cedula_estudiante'] ?? ''); ?>" placeholder="Ej: 12345678">
                            </div>
                            <div class="col-md-4">
                                <label for="cod_taller" class="form-label">Taller</label>
                                <select class="form-select" id="cod_taller" name="cod_taller">
                                    <option value="">Todos</option>
                                    <?php foreach ($talleresOptions as $t): ?>
                                    <option value="<?php echo htmlspecialchars($t['cod_taller'] ?? ''); ?>" <?php echo (isset($_POST['cod_taller']) && ($_POST['cod_taller'] ?? '') === ($t['cod_taller'] ?? '')) ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['cod_taller'] ?? ''); ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                            <form method="get" action="<?php echo BASE_URL; ?>/calificaciones/reportes-pdf" class="d-inline">
                                <input type="hidden" name="ano" value="<?php echo htmlspecialchars($_POST['ano'] ?? ''); ?>">
                                <input type="hidden" name="institucion_id" value="<?php echo htmlspecialchars($_POST['institucion_id'] ?? ''); ?>">
                                <input type="hidden" name="lapso" value="<?php echo htmlspecialchars($_POST['lapso'] ?? ''); ?>">
                                <input type="hidden" name="seccion" value="<?php echo htmlspecialchars($_POST['seccion'] ?? ''); ?>">
                                <input type="hidden" name="grado" value="<?php echo htmlspecialchars($_POST['grado'] ?? ''); ?>">
                                <input type="hidden" name="cedula_estudiante" value="<?php echo htmlspecialchars($_POST['cedula_estudiante'] ?? ''); ?>">
                                <input type="hidden" name="cod_taller" value="<?php echo htmlspecialchars($_POST['cod_taller'] ?? ''); ?>">
                                <button type="submit" class="btn btn-success"><i class="fas fa-file-pdf me-1"></i> Imprimir / PDF</button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Cédula</th>
                                        <th>Taller</th>
                                        <th>Programa</th>
                                        <th>Institución</th>
                                        <th>Momento I</th>
                                        <th>Momento II</th>
                                        <th>Momento III</th>
                                        <th>Literal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reportResults)): ?>
                                    <tr><td colspan="9" class="text-center text-muted">No se encontraron registros con los filtros indicados.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($reportResults as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(($row['apellido'] ?? '') . ' ' . ($row['nombre'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($row['cedula_estudiante'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['cod_taller'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['programa_descripcion'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['institucion_descripcion'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['momento_i'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['momento_ii'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['momento_iii'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['literal'] ?? '-'); ?></td>
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
