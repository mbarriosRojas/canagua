<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificaciones - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body { background-color: #f8f9fa; }
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .main-content { padding: 2rem; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
        }
        .filter-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
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
                        <li class="nav-item"><a class="nav-link" href="/public/inventario"><i class="fas fa-boxes"></i> Inventario</a></li>
                        <li class="nav-item"><a class="nav-link active" href="/public/calificaciones"><i class="fas fa-chart-line"></i> Calificaciones</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="/public/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Gestión de Calificaciones</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCalificacionModal">
                            <i class="fas fa-plus me-2"></i>Nueva Calificación
                        </button>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <!-- Filtros -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filtros</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="filterInstitucion" class="form-label">Institución</label>
                                <select class="form-select" id="filterInstitucion">
                                    <option value="">Todas las instituciones</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterEstudiante" class="form-label">Estudiante</label>
                                <select class="form-select" id="filterEstudiante">
                                    <option value="">Todos los estudiantes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterTaller" class="form-label">Taller</label>
                                <select class="form-select" id="filterTaller">
                                    <option value="">Todos los talleres</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-primary" id="applyFilters">
                                        <i class="fas fa-search me-2"></i>Filtrar
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="clearFilters">
                                        <i class="fas fa-times me-2"></i>Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de calificaciones -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="calificacionesTable">
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

    <!-- Modal Crear Calificación -->
    <div class="modal fade" id="createCalificacionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nueva Calificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createCalificacionForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estudiante_id_estudiante" class="form-label">Estudiante *</label>
                                <select class="form-select" id="estudiante_id_estudiante" name="estudiante_id_estudiante" required>
                                    <option value="">Seleccionar estudiante</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cedula_estudiante" class="form-label">Cédula del Estudiante *</label>
                                <input type="text" class="form-control" id="cedula_estudiante" name="cedula_estudiante" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cod_taller" class="form-label">Código del Taller *</label>
                                <select class="form-select" id="cod_taller" name="cod_taller" required>
                                    <option value="">Seleccionar taller</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="literal" class="form-label">Literal</label>
                                <select class="form-select" id="literal" name="literal">
                                    <option value="">Sin calificar</option>
                                    <option value="A">A - Excelente</option>
                                    <option value="B">B - Bueno</option>
                                    <option value="C">C - Regular</option>
                                    <option value="D">D - Deficiente</option>
                                    <option value="E">E - Reprobado</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="momento_i" class="form-label">Momento I</label>
                                <input type="number" class="form-control" id="momento_i" name="momento_i" min="0" max="20" step="0.1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="momento_ii" class="form-label">Momento II</label>
                                <input type="number" class="form-control" id="momento_ii" name="momento_ii" min="0" max="20" step="0.1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="momento_iii" class="form-label">Momento III</label>
                                <input type="number" class="form-control" id="momento_iii" name="momento_iii" min="0" max="20" step="0.1">
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
            let calificacionesTable;
            let optionsData = {};
            
            function initTable() {
                calificacionesTable = $('#calificacionesTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: '/public/calificaciones/list',
                        type: 'GET'
                    },
                    columns: [
                        { 
                            data: null,
                            render: function(data, type, row) {
                                return row.nombre + ' ' + row.apellido;
                            }
                        },
                        { data: 'cedula_estudiante' },
                        { data: 'cod_taller' },
                        { data: 'programa_descripcion' },
                        { data: 'institucion_descripcion' },
                        { data: 'momento_i' },
                        { data: 'momento_ii' },
                        { data: 'momento_iii' },
                        { 
                            data: 'literal',
                            render: function(data, type, row) {
                                if (!data) return '<span class="badge bg-secondary">Sin calificar</span>';
                                const badges = {
                                    'A': 'bg-success',
                                    'B': 'bg-primary', 
                                    'C': 'bg-warning',
                                    'D': 'bg-danger',
                                    'E': 'bg-dark'
                                };
                                return `<span class="badge ${badges[data] || 'bg-secondary'}">${data}</span>`;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCalificacion(${row.id_calificaciones})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCalificacion(${row.id_calificaciones})">
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
                    url: '/public/calificaciones/options',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            optionsData = response.data;
                            
                            // Cargar estudiantes
                            $('#estudiante_id_estudiante, #filterEstudiante').empty().append('<option value="">Seleccionar estudiante</option>');
                            response.data.estudiantes.forEach(estudiante => {
                                $('#estudiante_id_estudiante, #filterEstudiante').append(
                                    `<option value="${estudiante.id_estudiante}">${estudiante.apellido} ${estudiante.nombre} (${estudiante.cedula_estudiante})</option>`
                                );
                            });
                            
                            // Cargar talleres
                            $('#cod_taller, #filterTaller').empty().append('<option value="">Seleccionar taller</option>');
                            response.data.talleres.forEach(taller => {
                                $('#cod_taller, #filterTaller').append(
                                    `<option value="${taller.cod_taller}">${taller.cod_taller}</option>`
                                );
                            });
                        }
                    }
                });
                
                // Cargar instituciones
                $.ajax({
                    url: '/public/instituciones/options',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#filterInstitucion').empty().append('<option value="">Todas las instituciones</option>');
                            response.data.forEach(institucion => {
                                $('#filterInstitucion').append(
                                    `<option value="${institucion.id_instituciones}">${institucion.descripcion}</option>`
                                );
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
            
            $('#estudiante_id_estudiante').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                if (selectedOption.val()) {
                    const cedula = selectedOption.text().match(/\(([^)]+)\)/);
                    if (cedula) {
                        $('#cedula_estudiante').val(cedula[1]);
                    }
                } else {
                    $('#cedula_estudiante').val('');
                }
            });
            
            $('#createCalificacionForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '/public/calificaciones/create',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#createCalificacionModal').modal('hide');
                            $('#createCalificacionForm')[0].reset();
                            showAlert('Calificación creada exitosamente', 'success');
                            calificacionesTable.ajax.reload();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al crear calificación', 'danger');
                    }
                });
            });
            
            $('#applyFilters').on('click', function() {
                const filters = {
                    institucion_id: $('#filterInstitucion').val(),
                    estudiante_id: $('#filterEstudiante').val(),
                    cod_taller: $('#filterTaller').val()
                };
                
                calificacionesTable.ajax.url('/public/calificaciones/list?' + $.param(filters)).load();
            });
            
            $('#clearFilters').on('click', function() {
                $('#filterInstitucion, #filterEstudiante, #filterTaller').val('');
                calificacionesTable.ajax.url('/public/calificaciones/list').load();
            });
            
            window.editCalificacion = function(id) {
                showAlert('Funcionalidad de edición en desarrollo', 'info');
            };
            
            window.deleteCalificacion = function(id) {
                if (confirm('¿Está seguro de que desea eliminar esta calificación?')) {
                    $.ajax({
                        url: `/public/calificaciones/delete/${id}`,
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showAlert('Calificación eliminada exitosamente', 'success');
                                calificacionesTable.ajax.reload();
                            } else {
                                showAlert(response.message, 'danger');
                            }
                        },
                        error: function() {
                            showAlert('Error al eliminar calificación', 'danger');
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
