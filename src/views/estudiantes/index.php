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
            <?php $currentModule = 'estudiantes'; $currentSection = 'index'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-user-graduate me-2"></i>
                        Gestión de Estudiantes
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?php echo BASE_URL; ?>/estudiantes/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Nuevo Estudiante
                        </a>
                    </div>
                </div>

                <div id="alertContainer"></div>

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
                                            <i class="fas fa-user-graduate fa-2x mb-3 d-block"></i>
                                            <h5>No hay estudiantes registrados</h5>
                                            <p>Utilice "Nuevo Estudiante" para agregar estudiantes</p>
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

    <?php include __DIR__ . '/../partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let estudiantesTable;
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                showAlert('Estudiante guardado correctamente.', 'success');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/estudiantes');
            }
            if (urlParams.get('error') === 'notfound') {
                showAlert('Estudiante no encontrado.', 'danger');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/estudiantes');
            }
            function showAlert(message, type) {
                const icon = type === 'success' ? 'success' : type === 'danger' ? 'error' : type === 'warning' ? 'warning' : 'info';
                const title = type === 'success' ? 'Éxito' : type === 'danger' ? 'Error' : type === 'warning' ? 'Aviso' : 'Información';
                Swal.fire({ icon: icon, title: title, text: message });
            }
            // Inicializar DataTable
            function initTable() {
                estudiantesTable = $('#estudiantesTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: '<?php echo BASE_URL; ?>/estudiantes/list',
                        type: 'GET',
                        data: function(d) {
                            return { page: 1, limit: 99999 };
                        },
                        dataSrc: function(json) {
                            return (json && json.data) ? json.data : [];
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
                        emptyTable: "No hay estudiantes registrados.",
                        zeroRecords: "No se encontraron coincidencias."
                    },
                    paging: false,
                    responsive: true,
                    searching: false,
                    info: false,
                    lengthChange: false,
                    order: [[1, 'asc']],
                });
            }
            
            // Recargar lista de estudiantes
            function loadEstudiantes() {
                estudiantesTable.ajax.reload();
            }
            
            // Editar estudiante (redirige a página de edición)
            window.editEstudiante = function(id) {
                window.location.href = `<?php echo BASE_URL; ?>/estudiantes/edit/${id}`;
            };
            
            // Actualizar estudiante
            $('#editEstudianteForm').on('submit', function(e) {
                e.preventDefault();
                
                const id = $('#edit_id').val();
                
                $.ajax({
                    url: `<?php echo BASE_URL; ?>/estudiantes/update/${id}`,
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
                Swal.fire({
                    title: '¿Eliminar estudiante?',
                    text: '¿Está seguro de que desea eliminar este estudiante? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?php echo BASE_URL; ?>/estudiantes/delete/${id}`,
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
                });
            };
            
            // Cargar instituciones
            function loadInstituciones() {
                console.log('Iniciando carga de instituciones...');
                
                const institucionesPrueba = [
                    {id_instituciones: 1, descripcion: 'Instituto Tecnológico Nacional'},
                    {id_instituciones: 2, descripcion: 'Universidad Central de Venezuela'},
                    {id_instituciones: 3, descripcion: 'Centro de Formación Profesional'},
                    {id_instituciones: 4, descripcion: 'Instituto de Capacitación Laboral'},
                    {id_instituciones: 5, descripcion: 'Centro de Estudios Avanzados'}
                ];
                
                const selectCreate = $('#instituciones_id_instituciones');
                const selectEdit = $('#edit_instituciones_id_instituciones');
                
                selectCreate.find('option:not(:first)').remove();
                selectEdit.find('option:not(:first)').remove();
                
                institucionesPrueba.forEach(function(institucion) {
                    const option = `<option value="${institucion.id_instituciones}">${institucion.descripcion}</option>`;
                    selectCreate.append(option);
                    selectEdit.append(option);
                });
                
                $.ajax({
                    url: window.location.origin + '<?php echo BASE_URL; ?>/instituciones/options',
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data && response.data.length > 0) {
                            selectCreate.find('option:not(:first)').remove();
                            selectEdit.find('option:not(:first)').remove();
                            response.data.forEach(function(institucion) {
                                const option = `<option value="${institucion.id_instituciones}">${institucion.descripcion}</option>`;
                                selectCreate.append(option);
                                selectEdit.append(option);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar instituciones:', error);
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
