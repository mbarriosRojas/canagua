<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-graduation-cap fa-2x text-white mb-2"></i>
                        <h5 class="text-white"><?php echo Config::getAppName(); ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="/public/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/usuarios"><i class="fas fa-users"></i> Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/programas"><i class="fas fa-list-alt"></i> Programas</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/instituciones"><i class="fas fa-building"></i> Instituciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/estudiantes"><i class="fas fa-user-graduate"></i> Estudiantes</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/personal"><i class="fas fa-chalkboard-teacher"></i> Personal</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/talleres"><i class="fas fa-tools"></i> Talleres</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/cursos"><i class="fas fa-book"></i> Cursos</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/public/inventario"><i class="fas fa-boxes"></i> Inventario</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/calificaciones"><i class="fas fa-chart-line"></i> Calificaciones</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="/public/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-boxes me-2"></i>Gestión de Inventario</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInventarioModal">
                            <i class="fas fa-plus me-2"></i>Nuevo Item
                        </button>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="inventarioTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Ubicación</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Serial</th>
                                        <th>Cantidad</th>
                                        <th>Estado</th>
                                        <th>Responsable</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Crear Item de Inventario -->
    <div class="modal fade" id="createInventarioModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-boxes me-2"></i>Nuevo Item de Inventario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createInventarioForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cod_equipo" class="form-label">Código del Equipo *</label>
                                <input type="text" class="form-control" id="cod_equipo" name="cod_equipo" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ubicacion" class="form-label">Ubicación</label>
                                <input type="text" class="form-control" id="ubicacion" name="ubicacion">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="marca" name="marca">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modelo" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="modelo" name="modelo">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="serial" class="form-label">Serial</label>
                                <input type="text" class="form-control" id="serial" name="serial">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" value="1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="Bueno">Bueno</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Malo">Malo</option>
                                    <option value="Fuera de Servicio">Fuera de Servicio</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="personal_id_personal" class="form-label">Responsable</label>
                                <select class="form-select" id="personal_id_personal" name="personal_id_personal">
                                    <option value="">Seleccionar responsable</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="medidas" class="form-label">Medidas</label>
                                <input type="text" class="form-control" id="medidas" name="medidas">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="capacidad" class="form-label">Capacidad</label>
                                <input type="text" class="form-control" id="capacidad" name="capacidad">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="otras_caracteristicas" class="form-label">Otras Características</label>
                                <textarea class="form-control" id="otras_caracteristicas" name="otras_caracteristicas" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="observacion" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let inventarioTable;
            
            function initTable() {
                inventarioTable = $('#inventarioTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: { url: '/public/inventario/list', type: 'GET' },
                    columns: [
                        { data: 'cod_equipo' },
                        { data: 'ubicacion' || '-' },
                        { data: 'marca' || '-' },
                        { data: 'modelo' || '-' },
                        { data: 'serial' || '-' },
                        { data: 'cantidad' },
                        { 
                            data: 'estado',
                            render: function(data, type, row) {
                                let badgeClass = 'secondary';
                                if (data === 'Bueno') badgeClass = 'success';
                                else if (data === 'Regular') badgeClass = 'warning';
                                else if (data === 'Malo') badgeClass = 'danger';
                                else if (data === 'Fuera de Servicio') badgeClass = 'dark';
                                
                                return `<span class="badge bg-${badgeClass}">${data}</span>`;
                            }
                        },
                        { data: 'responsable' || '-' },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editInventario(${row.id_inventario})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteInventario(${row.id_inventario})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                    pageLength: 10,
                    responsive: true
                });
            }
            
            function loadOptions() {
                $.ajax({
                    url: '/public/inventario/options',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const options = response.data;
                            $('#personal_id_personal').html('<option value="">Seleccionar responsable</option>');
                            options.responsables.forEach(responsable => {
                                $('#personal_id_personal').append(`<option value="${responsable.id_personal}">${responsable.nombre} ${responsable.apellido}</option>`);
                            });
                        }
                    }
                });
            }
            
            function showAlert(message, type) {
                const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                $('#alertContainer').html(alertHtml);
                setTimeout(() => { $('#alertContainer .alert').alert('close'); }, 5000);
            }
            
            $('#createInventarioForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '/public/inventario/create',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#createInventarioModal').modal('hide');
                            $('#createInventarioForm')[0].reset();
                            showAlert('Item de inventario creado exitosamente', 'success');
                            inventarioTable.ajax.reload();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al crear item de inventario', 'danger');
                    }
                });
            });
            
            window.editInventario = function(id) {
                showAlert('Funcionalidad de edición en desarrollo', 'info');
            };
            
            window.deleteInventario = function(id) {
                if (confirm('¿Está seguro de que desea eliminar este item del inventario?')) {
                    $.ajax({
                        url: `/public/inventario/delete/${id}`,
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showAlert('Item de inventario eliminado exitosamente', 'success');
                                inventarioTable.ajax.reload();
                            } else {
                                showAlert(response.message, 'danger');
                            }
                        },
                        error: function() {
                            showAlert('Error al eliminar item de inventario', 'danger');
                        }
                    });
                }
            };
            
            initTable();
            loadOptions();
        });
    </script>
</body>
</html>
