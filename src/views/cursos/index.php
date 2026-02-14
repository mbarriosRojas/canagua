<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - <?php echo Config::getAppName(); ?></title>
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
                        <li class="nav-item"><a class="nav-link active" href="/public/cursos"><i class="fas fa-book"></i> Cursos</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/inventario"><i class="fas fa-boxes"></i> Inventario</a></li>
                        <li class="nav-item"><a class="nav-link" href="/public/calificaciones"><i class="fas fa-chart-line"></i> Calificaciones</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="/public/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-book me-2"></i>Gestión de Cursos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCursoModal">
                            <i class="fas fa-plus me-2"></i>Nuevo Curso
                        </button>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="cursosTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre del Curso</th>
                                        <th>Instructor</th>
                                        <th>Duración (Horas)</th>
                                        <th>Año</th>
                                        <th>Periodo</th>
                                        <th>N° Clases</th>
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

    <!-- Modal Crear Curso -->
    <div class="modal fade" id="createCursoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-book me-2"></i>Nuevo Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createCursoForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cod_curso" class="form-label">Código del Curso *</label>
                                <input type="text" class="form-control" id="cod_curso" name="cod_curso" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre_curso" class="form-label">Nombre del Curso *</label>
                                <input type="text" class="form-control" id="nombre_curso" name="nombre_curso" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="personal_id_personal" class="form-label">Instructor *</label>
                                <select class="form-select" id="personal_id_personal" name="personal_id_personal" required>
                                    <option value="">Seleccionar instructor</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                            <label for="ano" class="form-label">Año *</label>
                            <input type="number" class="form-control" id="ano" name="ano" min="2020" max="2030" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="periodo" class="form-label">Periodo *</label>
                                <select class="form-select" id="periodo" name="periodo" required>
                                    <option value="">Seleccionar periodo</option>
                                    <option value="I">I</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="duracion" class="form-label">Duración (Horas)</label>
                                <input type="number" class="form-control" id="duracion" name="duracion" min="1">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="num_de_clases" class="form-label">Número de Clases</label>
                                <input type="number" class="form-control" id="num_de_clases" name="num_de_clases" min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cedula_persona" class="form-label">Cédula Persona</label>
                                <input type="text" class="form-control" id="cedula_persona" name="cedula_persona">
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
            let cursosTable;
            
            function initTable() {
                cursosTable = $('#cursosTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: { url: '/public/cursos/list', type: 'GET' },
                    columns: [
                        { data: 'cod_curso' },
                        { data: 'nombre_curso' },
                        { data: 'instructor' },
                        { data: 'duracion' },
                        { data: 'ano' },
                        { data: 'periodo' },
                        { data: 'num_de_clases' },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCurso(${row.id_cursos})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCurso(${row.id_cursos})">
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
                console.log('Cargando opciones de cursos...');
                $.ajax({
                    url: '/public/cursos/options',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Respuesta recibida:', response);
                        if (response.success) {
                            const options = response.data;
                            console.log('Opciones:', options);
                            
                            // Llenar instructores
                            $('#personal_id_personal').html('<option value="">Seleccionar instructor</option>');
                            if (options.instructores && options.instructores.length > 0) {
                                options.instructores.forEach(instructor => {
                                    $('#personal_id_personal').append(`<option value="${instructor.id_personal}">${instructor.nombre} ${instructor.apellido}</option>`);
                                });
                            }
                        } else {
                            console.error('Error en respuesta:', response.message);
                            showAlert('Error al cargar opciones: ' + response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        showAlert('Error al cargar opciones del formulario', 'danger');
                    }
                });
            }
            
            function showAlert(message, type) {
                const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                $('#alertContainer').html(alertHtml);
                setTimeout(() => { $('#alertContainer .alert').alert('close'); }, 5000);
            }
            
            $('#createCursoForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '/public/cursos/create',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#createCursoModal').modal('hide');
                            $('#createCursoForm')[0].reset();
                            showAlert('Curso creado exitosamente', 'success');
                            cursosTable.ajax.reload();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al crear curso', 'danger');
                    }
                });
            });
            
            window.editCurso = function(id) {
                showAlert('Funcionalidad de edición en desarrollo', 'info');
            };
            
            window.deleteCurso = function(id) {
                if (confirm('¿Está seguro de que desea eliminar este curso?')) {
                    $.ajax({
                        url: `/public/cursos/delete/${id}`,
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showAlert('Curso eliminado exitosamente', 'success');
                                cursosTable.ajax.reload();
                            } else {
                                showAlert(response.message, 'danger');
                            }
                        },
                        error: function() {
                            showAlert('Error al eliminar curso', 'danger');
                        }
                    });
                }
            };
            
            initTable();
            
            // Cargar opciones cuando se abra el modal
            $('#createCursoModal').on('show.bs.modal', function() {
                loadOptions();
            });
        });
    </script>
</body>
</html>
