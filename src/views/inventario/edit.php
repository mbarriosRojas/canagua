<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Inventario - <?php echo Config::getAppName(); ?></title>
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
            <?php $currentModule = 'inventario'; $currentSection = 'edit'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <?php $reparaciones = $reparaciones ?? []; ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-edit me-2"></i>Editar Item de Inventario</h1>
                    <a href="<?php echo BASE_URL; ?>/inventario" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo BASE_URL; ?>/inventario/update/<?php echo (int) ($inventarioRow['id_inventario'] ?? 0); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cod_equipo" class="form-label">Código del Equipo *</label>
                                    <input type="text" class="form-control" id="cod_equipo" name="cod_equipo" value="<?php echo htmlspecialchars($inventarioRow['cod_equipo'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Equipo</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($inventarioRow['nombre'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" list="ubicacionesList" value="<?php echo htmlspecialchars($inventarioRow['ubicacion'] ?? ''); ?>">
                                    <datalist id="ubicacionesList">
                                        <?php foreach ($ubicacionesOptions as $u): ?>
                                        <option value="<?php echo htmlspecialchars($u['ubicacion'] ?? ''); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" id="marca" name="marca" value="<?php echo htmlspecialchars($inventarioRow['marca'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo htmlspecialchars($inventarioRow['modelo'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="serial" class="form-label">Serial</label>
                                    <input type="text" class="form-control" id="serial" name="serial" value="<?php echo htmlspecialchars($inventarioRow['serial'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" value="<?php echo (int) ($inventarioRow['cantidad'] ?? 1); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="Bueno" <?php echo ($inventarioRow['estado'] ?? '') === 'Bueno' ? 'selected' : ''; ?>>Bueno</option>
                                        <option value="Regular" <?php echo ($inventarioRow['estado'] ?? '') === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                                        <option value="Malo" <?php echo ($inventarioRow['estado'] ?? '') === 'Malo' ? 'selected' : ''; ?>>Malo</option>
                                        <option value="Fuera de Servicio" <?php echo ($inventarioRow['estado'] ?? '') === 'Fuera de Servicio' ? 'selected' : ''; ?>>Fuera de Servicio</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="personal_id_personal" class="form-label">Responsable</label>
                                    <select class="form-select" id="personal_id_personal" name="personal_id_personal">
                                        <option value="">Seleccionar responsable</option>
                                        <?php foreach ($responsablesOptions as $r): ?>
                                        <option value="<?php echo (int) $r['id_personal']; ?>" <?php echo (isset($inventarioRow['personal_id_personal']) && (int) $inventarioRow['personal_id_personal'] === (int) $r['id_personal']) ? 'selected' : ''; ?>><?php echo htmlspecialchars(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? '')); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="text" class="form-control" id="color" name="color" value="<?php echo htmlspecialchars($inventarioRow['color'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="medidas" class="form-label">Medidas</label>
                                    <input type="text" class="form-control" id="medidas" name="medidas" value="<?php echo htmlspecialchars($inventarioRow['medidas'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="capacidad" class="form-label">Capacidad</label>
                                    <input type="text" class="form-control" id="capacidad" name="capacidad" value="<?php echo htmlspecialchars($inventarioRow['capacidad'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="otras_caracteristicas" class="form-label">Otras Características</label>
                                    <textarea class="form-control" id="otras_caracteristicas" name="otras_caracteristicas" rows="3"><?php echo htmlspecialchars($inventarioRow['otras_caracteristicas'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="observacion" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observacion" name="observacion" rows="3"><?php echo htmlspecialchars($inventarioRow['observacion'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Guardar cambios</button>
                                <a href="<?php echo BASE_URL; ?>/inventario" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-wrench me-2"></i>Historial de reparaciones</h5>
                        <a href="<?php echo BASE_URL; ?>/inventario/reparar/<?php echo (int) ($inventarioRow['id_inventario'] ?? 0); ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Registrar reparación
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($reparaciones)): ?>
                            <p class="text-muted mb-0">No hay reparaciones registradas para este equipo.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reparaciones as $rep): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($rep['fecha']))); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($rep['motivo'] ?? '')); ?></td>
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
    <?php if (!empty($error)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'error', title: 'Error', text: <?php echo json_encode($error); ?> }); });</script>
    <?php endif; ?>
</body>
</html>
