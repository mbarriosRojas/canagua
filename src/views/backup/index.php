<?php
// Vista principal de Respaldo / Restauración de Base de Datos
// Variables esperadas: $backups (array con name, size, date)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$successMessage = $_SESSION['success'] ?? null;
$errorMessage = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo BD - <?php echo Config::getAppName(); ?></title>
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
            <?php $currentModule = null; $currentSection = 'backup'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-database me-2"></i>Respaldo y Restauración de Base de Datos</h1>
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver al dashboard
                    </a>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-download"></i> Crear Respaldo
                            </div>
                            <div class="card-body">
                                <p>Generar una copia de seguridad de toda la base de datos actual.</p>
                                <form method="POST" action="<?php echo BASE_URL; ?>/backup/create">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-database"></i> Generar Respaldo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <i class="fas fa-upload"></i> Restaurar Base de Datos
                            </div>
                            <div class="card-body">
                                <p class="text-danger"><strong>Advertencia:</strong> Esta acción reemplazará todos los datos actuales.</p>
                                <form id="formRestoreArchivo" method="POST" action="<?php echo BASE_URL; ?>/backup/restore" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="backup_file" class="form-label">Seleccionar archivo SQL</label>
                                        <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql">
                                    </div>
                                    <button type="button" class="btn btn-warning" onclick="confirmarRestaurar(document.getElementById('formRestoreArchivo'))">
                                        <i class="fas fa-upload"></i> Restaurar desde Archivo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Respaldos Disponibles (<?php echo isset($backups) ? count($backups) : 0; ?>)
                    </div>
                    <div class="card-body">
                        <?php if (empty($backups)): ?>
                            <p class="text-muted mb-0">No hay respaldos disponibles.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Nombre del Archivo</th>
                                            <th>Tamaño</th>
                                            <th>Fecha de Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backups as $backup): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                                <td><?php echo number_format(($backup['size'] ?? 0) / 1024, 2); ?> KB</td>
                                                <td>
                                                    <?php
                                                    $timestamp = $backup['date'] ?? 0;
                                                    echo $timestamp ? date('d/m/Y H:i:s', $timestamp) : '-';
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?php echo BASE_URL; ?>/backup/download?file=<?php echo rawurlencode($backup['name']); ?>" class="btn btn-success">
                                                            <i class="fas fa-download"></i> Descargar
                                                        </a>
                                                        <form method="POST" action="<?php echo BASE_URL; ?>/backup/restore" class="d-inline form-restore-backup">
                                                            <input type="hidden" name="backup_name" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                                            <button type="button" class="btn btn-warning" onclick="confirmarRestaurar(this.form)">
                                                                <i class="fas fa-upload"></i> Restaurar
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="<?php echo BASE_URL; ?>/backup/delete" class="d-inline form-delete-backup">
                                                            <input type="hidden" name="file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                                            <button type="button" class="btn btn-danger" onclick="confirmarEliminarRespaldo(this.form, '<?php echo htmlspecialchars($backup['name'], ENT_QUOTES); ?>')">
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

        function confirmarRestaurar(form) {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            Swal.fire({
                title: '¿Está seguro de restaurar la base de datos?',
                text: 'Esta acción no se puede deshacer. Se eliminarán todos los datos actuales.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f0ad4e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function confirmarEliminarRespaldo(form, nombre) {
            Swal.fire({
                title: '¿Eliminar respaldo?',
                text: 'Se eliminará el archivo de respaldo: ' + nombre,
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

