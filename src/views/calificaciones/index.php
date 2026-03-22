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
            <?php $currentModule = 'calificaciones'; $currentSection = 'index'; include __DIR__ . '/../partials/sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Gestión de Calificaciones</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?php echo BASE_URL; ?>/calificaciones/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Nueva Calificación</a>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <!-- Filtros -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filtros</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="filterInstitucion" class="form-label">Institución</label>
                                <select class="form-select" id="filterInstitucion">
                                    <option value="">Todas las instituciones</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filterTaller" class="form-label">Taller</label>
                                <select class="form-select" id="filterTaller">
                                    <option value="">Todos los talleres</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-primary me-2" id="applyFilters">
                                    <i class="fas fa-search me-2"></i>Buscar
                                </button>
                                <button type="button" class="btn btn-secondary" id="clearFilters">
                                    <i class="fas fa-times me-2"></i>Limpiar
                                </button>
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

    <?php include __DIR__ . '/../partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        url: '<?php echo BASE_URL; ?>/calificaciones/list',
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
                    paging: false,
                    info: false,
                    responsive: true
                });
            }
            
            function loadOptions() {
                // Opciones de calificaciones (estudiantes y talleres)
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/calificaciones/options',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (!response || !response.success || !response.data) {
                            console.error('Error cargando opciones de calificaciones:', response);
                            return;
                        }

                        optionsData = response.data;
                        
                        // Cargar estudiantes (solo para el modal de creación)
                        $('#estudiante_id_estudiante').empty().append('<option value="">Seleccionar estudiante</option>');
                        (response.data.estudiantes || []).forEach(estudiante => {
                            $('#estudiante_id_estudiante').append(
                                `<option value="${estudiante.id_estudiante}">${estudiante.apellido} ${estudiante.nombre} (${estudiante.cedula_estudiante})</option>`
                            );
                        });
                        
                        // Cargar talleres (para modal y filtros)
                        $('#cod_taller').empty().append('<option value="">Seleccionar taller</option>');
                        $('#filterTaller').empty().append('<option value=\"\">Todos los talleres</option>');
                        (response.data.talleres || []).forEach(taller => {
                            $('#cod_taller').append(
                                `<option value="${taller.cod_taller}">${taller.cod_taller}</option>`
                            );
                            $('#filterTaller').append(
                                `<option value="${taller.cod_taller}">${taller.cod_taller}</option>`
                            );
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error en /calificaciones/options:', status, error);
                    }
                });

                // Cargar instituciones para filtros
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/instituciones/options',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (!response || !response.success || !response.data) {
                            console.error('Error cargando instituciones:', response);
                            return;
                        }
                        $('#filterInstitucion').empty().append('<option value="">Todas las instituciones</option>');
                        (response.data || []).forEach(institucion => {
                            $('#filterInstitucion').append(
                                `<option value="${institucion.id_instituciones}">${institucion.descripcion}</option>`
                            );
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error en /instituciones/options:', status, error);
                    }
                });
            }
            
            function showAlert(message, type) {
                const icon = type === 'success' ? 'success' : type === 'danger' ? 'error' : type === 'warning' ? 'warning' : 'info';
                const title = type === 'success' ? 'Éxito' : type === 'danger' ? 'Error' : type === 'warning' ? 'Aviso' : 'Información';
                Swal.fire({ icon: icon, title: title, text: message });
            }

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('created')) {
                showAlert('Calificación creada correctamente.', 'success');
            }
            if (urlParams.has('updated')) {
                showAlert('Calificación actualizada correctamente.', 'success');
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
                    url: '<?php echo BASE_URL; ?>/calificaciones/create',
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
                const institucionId = $('#filterInstitucion').val();
                const codTaller = $('#filterTaller').val();

                const params = {};
                if (institucionId) params.institucion_id = institucionId;
                if (codTaller) params.cod_taller = codTaller;

                const query = $.param(params);
                const baseUrl = '<?php echo BASE_URL; ?>/calificaciones/list';
                const url = query ? `${baseUrl}?${query}` : baseUrl;

                calificacionesTable.ajax.url(url).load();
            });

            $('#clearFilters').on('click', function() {
                $('#filterInstitucion').val('');
                $('#filterTaller').val('');
                calificacionesTable.ajax.url('<?php echo BASE_URL; ?>/calificaciones/list').load();
            });
            
            window.editCalificacion = function(id) {
                window.location.href = '<?php echo BASE_URL; ?>/calificaciones/edit/' + id;
            };
            
            window.deleteCalificacion = function(id) {
                Swal.fire({
                    title: '¿Eliminar calificación?',
                    text: '¿Está seguro de que desea eliminar esta calificación? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?php echo BASE_URL; ?>/calificaciones/delete/${id}`,
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
                });
            };
            
            initTable();
            loadOptions();
        });
    </script>
</body>
</html>
