<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudiantes - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
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
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .badge {
            font-size: 0.8em;
            padding: 6px 12px;
            border-radius: 20px;
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
                        <li class="nav-item">
                            <a class="nav-link" href="/public/dashboard">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/usuarios">
                                <i class="fas fa-users"></i>
                                Usuarios
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/programas">
                                <i class="fas fa-list-alt"></i>
                                Programas
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/instituciones">
                                <i class="fas fa-building"></i>
                                Instituciones
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link active" href="/public/estudiantes">
                                <i class="fas fa-user-graduate"></i>
                                Estudiantes
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/personal">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Personal
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/talleres">
                                <i class="fas fa-tools"></i>
                                Talleres
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/cursos">
                                <i class="fas fa-book"></i>
                                Cursos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/inventario">
                                <i class="fas fa-boxes"></i>
                                Inventario
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/public/calificaciones">
                                <i class="fas fa-chart-line"></i>
                                Calificaciones
                            </a>
                        </li>
                        
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="/public/logout">
                                <i class="fas fa-sign-out-alt"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-user-graduate me-2"></i>
                        Gestión de Estudiantes
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEstudianteModal">
                            <i class="fas fa-plus me-2"></i>
                            Nuevo Estudiante
                        </button>
                    </div>
                </div>

                <!-- Alertas -->
                <div id="alertContainer"></div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="cedulaFilter" class="form-label">Cédula de Identidad</label>
                                <input type="text" class="form-control" id="cedulaFilter" placeholder="V-12345678">
                            </div>
                            <div class="col-md-4">
                                <label for="nombreFilter" class="form-label">Nombre o Apellido</label>
                                <input type="text" class="form-control" id="nombreFilter" placeholder="María Pérez">
                            </div>
                            <div class="col-md-4">
                                <label for="institucionFilter" class="form-label">Institución</label>
                                <select class="form-select" id="institucionFilter">
                                    <option value="">Todas las instituciones</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary me-2" id="filterBtn">
                                    <i class="fas fa-search me-2"></i>Buscar Estudiantes
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearFiltersBtn">
                                    <i class="fas fa-times me-2"></i>Limpiar Filtros
                                </button>
                                <small class="text-muted ms-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Los resultados aparecerán aquí después de aplicar los filtros
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de estudiantes -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="estudiantesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre Completo</th>
                                        <th>Sexo</th>
                                        <th>Institución</th>
                                        <th>Lugar de Nacimiento</th>
                                        <th>Cédula Representante</th>
                                        <th>Teléfono</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-search fa-2x mb-3 d-block"></i>
                                            <h5>No hay estudiantes mostrados</h5>
                                            <p>Utiliza los filtros de búsqueda para encontrar estudiantes específicos</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Crear Estudiante -->
    <div class="modal fade" id="createEstudianteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>
                        Nuevo Estudiante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createEstudianteForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cedula_estudiante" class="form-label">Cédula del Estudiante *</label>
                                <input type="text" class="form-control" id="cedula_estudiante" name="cedula_estudiante" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sexo" class="form-label">Sexo *</label>
                                <select class="form-select" id="sexo" name="sexo" required>
                                    <option value="">Seleccionar</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="apellido" class="form-label">Primer Apellido *</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="nombre" class="form-label">Primer Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="apellido_2" class="form-label">Segundo Apellido</label>
                                <input type="text" class="form-control" id="apellido_2" name="apellido_2">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lugar_nacimiento" class="form-label">Lugar de Nacimiento</label>
                                <input type="text" class="form-control" id="lugar_nacimiento" name="lugar_nacimiento">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cedula_representante" class="form-label">Cédula del Representante</label>
                                <input type="text" class="form-control" id="cedula_representante" name="cedula_representante">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="instituciones_id_instituciones" class="form-label">Institución *</label>
                                <select class="form-select" id="instituciones_id_instituciones" name="instituciones_id_instituciones" required>
                                    <option value="">Seleccionar institución</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Guardar Estudiante
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Estudiante -->
    <div class="modal fade" id="editEstudianteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>
                        Editar Estudiante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editEstudianteForm">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_cedula_estudiante" class="form-label">Cédula del Estudiante *</label>
                                <input type="text" class="form-control" id="edit_cedula_estudiante" name="cedula_estudiante" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_sexo" class="form-label">Sexo *</label>
                                <select class="form-select" id="edit_sexo" name="sexo" required>
                                    <option value="">Seleccionar</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_apellido" class="form-label">Primer Apellido *</label>
                                <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_nombre" class="form-label">Primer Nombre *</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_apellido_2" class="form-label">Segundo Apellido</label>
                                <input type="text" class="form-control" id="edit_apellido_2" name="apellido_2">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_lugar_nacimiento" class="form-label">Lugar de Nacimiento</label>
                                <input type="text" class="form-control" id="edit_lugar_nacimiento" name="lugar_nacimiento">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="edit_telefono" name="telefono">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_cedula_representante" class="form-label">Cédula del Representante</label>
                                <input type="text" class="form-control" id="edit_cedula_representante" name="cedula_representante">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_instituciones_id_instituciones" class="form-label">Institución *</label>
                                <select class="form-select" id="edit_instituciones_id_instituciones" name="instituciones_id_instituciones" required>
                                    <option value="">Seleccionar institución</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Actualizar Estudiante
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let estudiantesTable;
            
            // Inicializar DataTable
            function initTable() {
                estudiantesTable = $('#estudiantesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '/public/estudiantes/list',
                        type: 'GET',
                        data: function(d) {
                            return {
                                page: Math.floor(d.start / d.length) + 1,
                                limit: d.length,
                                cedula: $('#cedulaFilter').val(),
                                nombre: $('#nombreFilter').val(),
                                institucion: $('#institucionFilter').val()
                            };
                        },
                        dataSrc: function(json) {
                            return json.data;
                        }
                    },
                    columns: [
                        { data: 'cedula_estudiante' },
                        { 
                            data: null,
                            render: function(data, type, row) {
                                return row.nombre + ' ' + row.apellido + (row.apellido_2 ? ' ' + row.apellido_2 : '');
                            }
                        },
                        { 
                            data: 'sexo',
                            render: function(data, type, row) {
                                return data === 'M' ? '<span class="badge bg-primary">Masculino</span>' : '<span class="badge bg-info">Femenino</span>';
                            }
                        },
                        { 
                            data: 'institucion_nombre',
                            render: function(data, type, row) {
                                return data || '<span class="text-muted">Sin institución</span>';
                            }
                        },
                        { data: 'lugar_nacimiento' || '-' },
                        { data: 'cedula_representante' || '-' },
                        { data: 'telefono' || '-' },
                        { 
                            data: 'fecha_creacion',
                            render: function(data, type, row) {
                                return new Date(data).toLocaleDateString('es-ES');
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editEstudiante(${row.id_estudiante})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEstudiante(${row.id_estudiante})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                        emptyTable: "No hay estudiantes mostrados. Utiliza los filtros de búsqueda para encontrar estudiantes específicos.",
                        zeroRecords: "No se encontraron estudiantes con los filtros aplicados."
                    },
                    pageLength: 10,
                    responsive: true,
                    paging: true,
                    searching: false,
                    info: true,
                    lengthChange: true,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    order: [[1, 'asc']],
                    drawCallback: function(settings) {
                        // Ocultar mensaje de "no hay datos" si hay resultados
                        if (settings.json && settings.json.data && settings.json.data.length > 0) {
                            $('#estudiantesTable tbody tr').removeClass('d-none');
                        }
                    }
                });
            }
            
            // Cargar estudiantes con filtros
            function loadEstudiantes() {
                estudiantesTable.ajax.reload();
            }
            
            // Mostrar alerta
            function showAlert(message, type) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#alertContainer').html(alertHtml);
                setTimeout(() => {
                    $('#alertContainer .alert').alert('close');
                }, 5000);
            }
            
            // Crear estudiante
            $('#createEstudianteForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '/public/estudiantes/create',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#createEstudianteModal').modal('hide');
                            $('#createEstudianteForm')[0].reset();
                            showAlert('Estudiante creado exitosamente', 'success');
                            loadEstudiantes();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al crear estudiante', 'danger');
                    }
                });
            });
            
            // Editar estudiante
            window.editEstudiante = function(id) {
                $.ajax({
                    url: `/public/estudiantes/get/${id}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            const estudiante = response.data;
                            
                            // Llenar el formulario
                            $('#edit_id').val(estudiante.id_estudiante);
                            $('#edit_cedula_estudiante').val(estudiante.cedula_estudiante);
                            $('#edit_apellido').val(estudiante.apellido);
                            $('#edit_nombre').val(estudiante.nombre);
                            $('#edit_apellido_2').val(estudiante.apellido_2 || '');
                            $('#edit_sexo').val(estudiante.sexo);
                            $('#edit_lugar_nacimiento').val(estudiante.lugar_nacimiento || '');
                            $('#edit_cedula_representante').val(estudiante.cedula_representante || '');
                            $('#edit_telefono').val(estudiante.telefono || '');
                            $('#edit_instituciones_id_instituciones').val(estudiante.instituciones_id_instituciones || '');
                            
                            $('#editEstudianteModal').modal('show');
                        } else {
                            showAlert('No se pudieron cargar los datos del estudiante', 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error al cargar datos del estudiante', 'danger');
                    }
                });
            };
            
            // Actualizar estudiante
            $('#editEstudianteForm').on('submit', function(e) {
                e.preventDefault();
                
                const id = $('#edit_id').val();
                
                $.ajax({
                    url: `/public/estudiantes/update/${id}`,
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#editEstudianteModal').modal('hide');
                            showAlert('Estudiante actualizado exitosamente', 'success');
                            loadEstudiantes();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al actualizar estudiante', 'danger');
                    }
                });
            });
            
            // Eliminar estudiante
            window.deleteEstudiante = function(id) {
                if (confirm('¿Está seguro de que desea eliminar este estudiante?')) {
                    $.ajax({
                        url: `/public/estudiantes/delete/${id}`,
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showAlert('Estudiante eliminado exitosamente', 'success');
                                loadEstudiantes();
                            } else {
                                showAlert(response.message, 'danger');
                            }
                        },
                        error: function() {
                            showAlert('Error al eliminar estudiante', 'danger');
                        }
                    });
                }
            };
            
            // Filtros
            $('#filterBtn').on('click', function() {
                loadEstudiantes();
            });
            
            $('#clearFiltersBtn').on('click', function() {
                $('#cedulaFilter').val('');
                $('#nombreFilter').val('');
                $('#institucionFilter').val('');
                loadEstudiantes();
            });
            
            // Permitir búsqueda con Enter en los campos de texto
            $('#cedulaFilter, #nombreFilter').on('keypress', function(e) {
                if (e.which === 13) {
                    loadEstudiantes();
                }
            });
            
            // Búsqueda automática al cambiar el select de institución
            $('#institucionFilter').on('change', function() {
                loadEstudiantes();
            });
            
            // Cargar instituciones
            function loadInstituciones() {
                console.log('Iniciando carga de instituciones...');
                
                // Datos de prueba temporales para verificar que el select funciona
                const institucionesPrueba = [
                    {id_instituciones: 1, descripcion: 'Instituto Tecnológico Nacional'},
                    {id_instituciones: 2, descripcion: 'Universidad Central de Venezuela'},
                    {id_instituciones: 3, descripcion: 'Centro de Formación Profesional'},
                    {id_instituciones: 4, descripcion: 'Instituto de Capacitación Laboral'},
                    {id_instituciones: 5, descripcion: 'Centro de Estudios Avanzados'}
                ];
                
                // Cargar datos de prueba primero
                console.log('Cargando datos de prueba...');
                const selectCreate = $('#instituciones_id_instituciones');
                const selectEdit = $('#edit_instituciones_id_instituciones');
                const selectFilter = $('#institucionFilter');
                
                console.log('Selectores encontrados:', {
                    create: selectCreate.length,
                    edit: selectEdit.length,
                    filter: selectFilter.length
                });
                
                // Limpiar opciones existentes (excepto la primera)
                selectCreate.find('option:not(:first)').remove();
                selectEdit.find('option:not(:first)').remove();
                selectFilter.find('option:not(:first)').remove();
                
                // Agregar datos de prueba
                institucionesPrueba.forEach(function(institucion) {
                    const option = `<option value="${institucion.id_instituciones}">${institucion.descripcion}</option>`;
                    selectCreate.append(option);
                    selectEdit.append(option);
                    selectFilter.append(option);
                });
                
                console.log('Datos de prueba agregados:', institucionesPrueba.length);
                
                // Ahora intentar cargar datos reales
                $.ajax({
                    url: window.location.origin + '/public/instituciones/options',
                    type: 'GET',
                    success: function(response) {
                        console.log('Respuesta recibida:', response);
                        if (response.success && response.data && response.data.length > 0) {
                            console.log('Datos reales de instituciones:', response.data);
                            
                            // Limpiar opciones existentes (excepto la primera)
                            selectCreate.find('option:not(:first)').remove();
                            selectEdit.find('option:not(:first)').remove();
                            selectFilter.find('option:not(:first)').remove();
                            
                            // Agregar opciones reales
                            response.data.forEach(function(institucion) {
                                const option = `<option value="${institucion.id_instituciones}">${institucion.descripcion}</option>`;
                                selectCreate.append(option);
                                selectEdit.append(option);
                                selectFilter.append(option);
                            });
                            
                            console.log('Instituciones reales agregadas:', response.data.length);
                        } else {
                            console.log('No se pudieron cargar datos reales, usando datos de prueba');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar instituciones reales:', error);
                        console.log('Usando datos de prueba');
                    }
                });
            }
            
            // Inicializar
            initTable();
            
            // Cargar instituciones después de un pequeño delay para asegurar que el DOM esté listo
            setTimeout(function() {
                loadInstituciones();
            }, 100);
        });
    </script>
</body>
</html>
