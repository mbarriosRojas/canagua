<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$configRow = $configRow ?? [];
$directivos = $directivos ?? [];
$successMessage = $_SESSION['success'] ?? null;
$errorMessage = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #667eea; --secondary-color: #764ba2; }
        body { background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; }
        .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); padding: 12px 20px; border-radius: 8px; margin: 5px 10px; transition: all 0.3s ease; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.2); transform: translateX(5px); }
        .main-content { padding: 2rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border: none; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $currentModule = 'configuracion'; $currentSection = 'index'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-cog me-2"></i>Configuración del Sistema</h1>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-building me-1"></i> Datos de la Institución
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?php echo BASE_URL; ?>/configuracion/update">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required
                                               value="<?php echo htmlspecialchars($configRow['nombre'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="rif" class="form-label">RIF</label>
                                        <input type="text" class="form-control" id="rif" name="rif" required
                                               value="<?php echo htmlspecialchars($configRow['rif'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <textarea class="form-control" id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($configRow['direccion'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="telefono" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono"
                                                   value="<?php echo htmlspecialchars($configRow['telefono'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                   value="<?php echo htmlspecialchars($configRow['email'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Guardar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-users me-1"></i> Directivos</div>
                        <a href="<?php echo BASE_URL; ?>/configuracion/directivos-create" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Agregar Directivo
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($directivos)): ?>
                            <p class="text-muted mb-0">No hay directivos registrados.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Apellido</th>
                                            <th>Cargo</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                            <th>Firma</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($directivos as $d): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($d['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($d['apellido']); ?></td>
                                                <td><?php echo htmlspecialchars($d['cargo']); ?></td>
                                                <td><?php echo htmlspecialchars($d['telefono'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($d['email'] ?? ''); ?></td>
                                                <td>
                                                    <?php if (!empty($d['firma'])): ?>
                                                        <span class="badge bg-success">Firma</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">No firma</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?php echo BASE_URL; ?>/configuracion/directivos-edit/<?php echo (int)$d['id_directivo']; ?>" class="btn btn-outline-secondary">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </a>
                                                        <form method="POST" action="<?php echo BASE_URL; ?>/configuracion/directivos-delete/<?php echo (int)$d['id_directivo']; ?>" class="d-inline">
                                                            <button type="button" class="btn btn-outline-danger" onclick="confirmarEliminarDirectivo(this.form)">
                                                                <i class="fas fa-trash"></i> Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($successMessage): ?>
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '<?php echo addslashes($successMessage); ?>'
        });
        <?php endif; ?>
        <?php if ($errorMessage): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($errorMessage); ?>'
        });
        <?php endif; ?>

        function confirmarEliminarDirectivo(form) {
            Swal.fire({
                title: '¿Eliminar directivo?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>

