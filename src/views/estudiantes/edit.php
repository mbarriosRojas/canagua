<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estudiante - <?php echo Config::getAppName(); ?></title>
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
            <?php $currentModule = 'estudiantes'; $currentSection = 'edit'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-user-edit me-2"></i>Editar Estudiante</h1>
                    <a href="<?php echo BASE_URL; ?>/estudiantes" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo BASE_URL; ?>/estudiantes/update/<?php echo (int) ($estudianteRow['id_estudiante'] ?? 0); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cedula_estudiante" class="form-label">Cédula del Estudiante *</label>
                                    <input type="text" class="form-control" id="cedula_estudiante" name="cedula_estudiante" value="<?php echo htmlspecialchars($estudianteRow['cedula_estudiante'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sexo" class="form-label">Sexo *</label>
                                    <select class="form-select" id="sexo" name="sexo" required>
                                        <option value="">Seleccionar</option>
                                        <option value="M" <?php echo ($estudianteRow['sexo'] ?? '') === 'M' ? 'selected' : ''; ?>>Masculino</option>
                                        <option value="F" <?php echo ($estudianteRow['sexo'] ?? '') === 'F' ? 'selected' : ''; ?>>Femenino</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="apellido" class="form-label">Primer Apellido *</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($estudianteRow['apellido'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="nombre" class="form-label">Primer Nombre *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($estudianteRow['nombre'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="apellido_2" class="form-label">Segundo Apellido</label>
                                    <input type="text" class="form-control" id="apellido_2" name="apellido_2" value="<?php echo htmlspecialchars($estudianteRow['apellido_2'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="lugar_nacimiento" class="form-label">Lugar de Nacimiento</label>
                                    <input type="text" class="form-control" id="lugar_nacimiento" name="lugar_nacimiento" value="<?php echo htmlspecialchars($estudianteRow['lugar_nacimiento'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($estudianteRow['telefono'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cedula_representante" class="form-label">Cédula del Representante</label>
                                    <input type="text" class="form-control" id="cedula_representante" name="cedula_representante" value="<?php echo htmlspecialchars($estudianteRow['cedula_representante'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="instituciones_id_instituciones" class="form-label">Institución</label>
                                    <select class="form-select" id="instituciones_id_instituciones" name="instituciones_id_instituciones">
                                        <option value="">Seleccionar institución</option>
                                        <?php foreach ($institucionesOptions ?? [] as $inst): ?>
                                        <option value="<?php echo (int) $inst['id_instituciones']; ?>" <?php echo (isset($estudianteRow['instituciones_id_instituciones']) && (int)$estudianteRow['instituciones_id_instituciones'] === (int)$inst['id_instituciones']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($inst['descripcion'] ?? $inst['cod_institucion']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Guardar cambios</button>
                                <a href="<?php echo BASE_URL; ?>/estudiantes" class="btn btn-secondary">Cancelar</a>
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
