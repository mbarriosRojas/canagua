<?php
$data = $data ?? [];
$programasOptions = $programasOptions ?? [];
$institucionesOptions = $institucionesOptions ?? [];
$personalOptions = $personalOptions ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Taller - <?php echo Config::getAppName(); ?></title>
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
            <?php $currentModule = 'talleres'; $currentSection = 'create'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-plus me-2"></i>Nuevo Taller</h1>
                    <a href="<?php echo BASE_URL; ?>/talleres" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo BASE_URL; ?>/talleres/create">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cod_taller" class="form-label">Código del Taller *</label>
                                    <input type="text" class="form-control" id="cod_taller" name="cod_taller" value="<?php echo htmlspecialchars($data['cod_taller'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ano_escolar" class="form-label">Año Escolar *</label>
                                    <input type="number" class="form-control" id="ano_escolar" name="ano_escolar" min="2020" max="2030" value="<?php echo htmlspecialchars($data['ano_escolar'] ?? date('Y')); ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="programas_id_programas" class="form-label">Programa *</label>
                                    <select class="form-select" id="programas_id_programas" name="programas_id_programas" required>
                                        <option value="">Seleccionar programa</option>
                                        <?php foreach ($programasOptions as $p): ?>
                                        <option value="<?php echo (int) $p['id_programas']; ?>" <?php echo (isset($data['programas_id_programas']) && (int)$data['programas_id_programas'] === (int)$p['id_programas']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['descripcion'] ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="instituciones_id_instituciones" class="form-label">Institución *</label>
                                    <select class="form-select" id="instituciones_id_instituciones" name="instituciones_id_instituciones" required>
                                        <option value="">Seleccionar institución</option>
                                        <?php foreach ($institucionesOptions as $i): ?>
                                        <option value="<?php echo (int) $i['id_instituciones']; ?>" <?php echo (isset($data['instituciones_id_instituciones']) && (int)$data['instituciones_id_instituciones'] === (int)$i['id_instituciones']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($i['descripcion'] ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="personal_id_personal" class="form-label">Instructor *</label>
                                    <select class="form-select" id="personal_id_personal" name="personal_id_personal" required>
                                        <option value="">Seleccionar instructor</option>
                                        <?php foreach ($personalOptions as $per): ?>
                                        <option value="<?php echo (int) $per['id_personal']; ?>" <?php echo (isset($data['personal_id_personal']) && (int)$data['personal_id_personal'] === (int)$per['id_personal']) ? 'selected' : ''; ?>><?php echo htmlspecialchars(($per['apellido'] ?? '') . ' ' . ($per['nombre'] ?? '')); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lapso" class="form-label">Lapso *</label>
                                    <select class="form-select" id="lapso" name="lapso" required>
                                        <option value="">Seleccionar lapso</option>
                                        <option value="I" <?php echo ($data['lapso'] ?? '') === 'I' ? 'selected' : ''; ?>>I</option>
                                        <option value="II" <?php echo ($data['lapso'] ?? '') === 'II' ? 'selected' : ''; ?>>II</option>
                                        <option value="III" <?php echo ($data['lapso'] ?? '') === 'III' ? 'selected' : ''; ?>>III</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="grado" class="form-label">Grado</label>
                                    <input type="text" class="form-control" id="grado" name="grado" value="<?php echo htmlspecialchars($data['grado'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="seccion" class="form-label">Sección</label>
                                    <input type="text" class="form-control" id="seccion" name="seccion" value="<?php echo htmlspecialchars($data['seccion'] ?? ''); ?>">
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Crear Taller</button>
                                <a href="<?php echo BASE_URL; ?>/talleres" class="btn btn-secondary">Cancelar</a>
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
    <?php if (!empty($error)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'error', title: 'Error', text: <?php echo json_encode($error); ?> }); });</script>
    <?php endif; ?>
</body>
</html>
