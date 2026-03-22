<?php
$calificacionRow = $calificacionRow ?? [];
$error = $error ?? '';
$id = (int) ($calificacionRow['id_calificaciones'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Calificación - <?php echo Config::getAppName(); ?></title>
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
            <?php $currentModule = 'calificaciones'; $currentSection = 'edit'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-edit me-2"></i>Editar Calificación</h1>
                    <a href="<?php echo BASE_URL; ?>/calificaciones" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo BASE_URL; ?>/calificaciones/update/<?php echo $id; ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Estudiante</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(($calificacionRow['apellido'] ?? '') . ' ' . ($calificacionRow['nombre'] ?? '') . ' (' . ($calificacionRow['cedula_estudiante'] ?? '') . ')'); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Taller</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($calificacionRow['cod_taller'] ?? ''); ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="literal" class="form-label">Literal</label>
                                    <select class="form-select" id="literal" name="literal">
                                        <option value="">Sin calificar</option>
                                        <option value="A" <?php echo ($calificacionRow['literal'] ?? '') === 'A' ? 'selected' : ''; ?>>A - Excelente</option>
                                        <option value="B" <?php echo ($calificacionRow['literal'] ?? '') === 'B' ? 'selected' : ''; ?>>B - Bueno</option>
                                        <option value="C" <?php echo ($calificacionRow['literal'] ?? '') === 'C' ? 'selected' : ''; ?>>C - Regular</option>
                                        <option value="D" <?php echo ($calificacionRow['literal'] ?? '') === 'D' ? 'selected' : ''; ?>>D - Deficiente</option>
                                        <option value="E" <?php echo ($calificacionRow['literal'] ?? '') === 'E' ? 'selected' : ''; ?>>E - Reprobado</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="numeral" class="form-label">Numeral</label>
                                    <input type="number" class="form-control" id="numeral" name="numeral" min="0" max="20" step="0.1" value="<?php echo htmlspecialchars($calificacionRow['numeral'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="momento_i" class="form-label">Momento I</label>
                                    <input type="number" class="form-control" id="momento_i" name="momento_i" min="0" max="20" step="0.1" value="<?php echo htmlspecialchars($calificacionRow['momento_i'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="momento_ii" class="form-label">Momento II</label>
                                    <input type="number" class="form-control" id="momento_ii" name="momento_ii" min="0" max="20" step="0.1" value="<?php echo htmlspecialchars($calificacionRow['momento_ii'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="momento_iii" class="form-label">Momento III</label>
                                    <input type="number" class="form-control" id="momento_iii" name="momento_iii" min="0" max="20" step="0.1" value="<?php echo htmlspecialchars($calificacionRow['momento_iii'] ?? ''); ?>">
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Guardar cambios</button>
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
    <?php if ($error): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'error', title: 'Error', text: <?php echo json_encode($error); ?> }); });</script>
    <?php endif; ?>
</body>
</html>
