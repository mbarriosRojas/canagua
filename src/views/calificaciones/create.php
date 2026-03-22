<?php
$formOptions = $formOptions ?? ['estudiantes' => [], 'talleres' => []];
$estudiantesOptions = $formOptions['estudiantes'] ?? [];
$talleresOptions = $formOptions['talleres'] ?? [];
$data = $data ?? [];

// Prefill desde parámetros GET (por ejemplo, al venir desde Talleres > Detalle)
$prefillEstudianteId = isset($_GET['estudiante_id']) ? (int) $_GET['estudiante_id'] : null;
$prefillCodTaller = isset($_GET['cod_taller']) ? (string) $_GET['cod_taller'] : null;

// Resolver valores seleccionados para los selects (prioriza POST en reenvíos)
$selectedEstudianteId = isset($_POST['estudiante_id_estudiante'])
    ? (int) $_POST['estudiante_id_estudiante']
    : $prefillEstudianteId;
$selectedCodTaller = isset($_POST['cod_taller'])
    ? (string) $_POST['cod_taller']
    : $prefillCodTaller;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Calificación - <?php echo Config::getAppName(); ?></title>
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
        .form-control { border-radius: 8px; border: 2px solid #e9ecef; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $currentModule = 'calificaciones'; $currentSection = 'create'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-plus me-2"></i>Nueva Calificación</h1>
                    <a href="<?php echo BASE_URL; ?>/calificaciones" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo BASE_URL; ?>/calificaciones/create">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="estudiante_id_estudiante" class="form-label">Estudiante *</label>
                                    <select class="form-select" id="estudiante_id_estudiante" name="estudiante_id_estudiante" required>
                                        <option value="">Seleccionar estudiante</option>
                                        <?php foreach ($estudiantesOptions as $e): ?>
                                        <option
                                            value="<?php echo (int) $e['id_estudiante']; ?>"
                                            data-cedula="<?php echo htmlspecialchars($e['cedula_estudiante'] ?? ''); ?>"
                                            <?php echo $selectedEstudianteId !== null && (int)$selectedEstudianteId === (int)$e['id_estudiante'] ? 'selected' : ''; ?>
                                        >
                                            <?php echo htmlspecialchars(($e['apellido'] ?? '') . ' ' . ($e['nombre'] ?? '') . ' (' . ($e['cedula_estudiante'] ?? '') . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cedula_estudiante" class="form-label">Cédula del Estudiante *</label>
                                    <input type="text" class="form-control" id="cedula_estudiante" name="cedula_estudiante" value="<?php echo htmlspecialchars($data['cedula_estudiante'] ?? $_POST['cedula_estudiante'] ?? ''); ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cod_taller" class="form-label">Código del Taller *</label>
                                    <select class="form-select" id="cod_taller" name="cod_taller" required>
                                        <option value="">Seleccionar taller</option>
                                        <?php foreach ($talleresOptions as $t): ?>
                                        <option
                                            value="<?php echo htmlspecialchars($t['cod_taller'] ?? ''); ?>"
                                            <?php
                                                $codOption = $t['cod_taller'] ?? '';
                                                echo $selectedCodTaller !== null && (string)$selectedCodTaller === (string)$codOption ? 'selected' : '';
                                            ?>
                                        >
                                            <?php echo htmlspecialchars($codOption); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="literal" class="form-label">Literal</label>
                                    <select class="form-select" id="literal" name="literal">
                                        <option value="">Sin calificar</option>
                                        <option value="A" <?php echo ($data['literal'] ?? $_POST['literal'] ?? '') === 'A' ? 'selected' : ''; ?>>A - Excelente</option>
                                        <option value="B" <?php echo ($data['literal'] ?? $_POST['literal'] ?? '') === 'B' ? 'selected' : ''; ?>>B - Bueno</option>
                                        <option value="C" <?php echo ($data['literal'] ?? $_POST['literal'] ?? '') === 'C' ? 'selected' : ''; ?>>C - Regular</option>
                                        <option value="D" <?php echo ($data['literal'] ?? $_POST['literal'] ?? '') === 'D' ? 'selected' : ''; ?>>D - Deficiente</option>
                                        <option value="E" <?php echo ($data['literal'] ?? $_POST['literal'] ?? '') === 'E' ? 'selected' : ''; ?>>E - Reprobado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="momento_i" class="form-label">Momento I</label>
                                    <input type="number" class="form-control" id="momento_i" name="momento_i" min="0" max="20" step="0.1" value="<?php echo htmlspecialchars($data['momento_i'] ?? $_POST['momento_i'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="momento_ii" class="form-label">Momento II</label>
                                    <input type="number" class="form-control" id="momento_ii" name="momento_ii" min="0" max="20" step="0.1" value="<?php echo htmlspecialchars($data['momento_ii'] ?? $_POST['momento_ii'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="momento_iii" class="form-label">Momento III</label>
                                    <input type="number" class="form-control" id="momento_iii" name="momento_iii" min="0" max="20" step="0.1" value="<?php echo htmlspecialchars($data['momento_iii'] ?? $_POST['momento_iii'] ?? ''); ?>">
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Crear Calificación</button>
                                <a href="<?php echo BASE_URL; ?>/calificaciones" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <?php include __DIR__ . '/../partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('estudiante_id_estudiante').addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('cedula_estudiante').value = opt.getAttribute('data-cedula') || '';
        });
        var sel = document.getElementById('estudiante_id_estudiante');
        if (sel.value) { var o = sel.options[sel.selectedIndex]; document.getElementById('cedula_estudiante').value = o.getAttribute('data-cedula') || ''; }
    </script>
    <?php if (!empty($error)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'error', title: 'Error', text: <?php echo json_encode($error); ?> }); });</script>
    <?php endif; ?>
</body>
</html>
