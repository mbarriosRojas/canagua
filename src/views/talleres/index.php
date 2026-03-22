<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talleres - <?php echo Config::getAppName(); ?></title>
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
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; }
        .btn-info { background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); border: none; }
        .btn-warning { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none; }
        .table-hover tbody tr:hover { background-color: rgba(102, 126, 234, 0.1); }
        .badge { font-size: 0.8em; }
        .modal-lg { max-width: 90%; }
        .search-container { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .student-card { border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: white; }
        .student-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .grade-input { width: 80px; text-align: center; }
        .grade-section { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $currentModule = 'talleres'; $currentSection = 'index'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Vista Principal de Talleres -->
                <div id="mainView">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><i class="fas fa-tools me-2"></i>Gestión de Talleres</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="<?php echo BASE_URL; ?>/talleres/create" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Nuevo Taller
                            </a>
                        </div>
                    </div>

                    <div id="alertContainer"></div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="talleresTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Código</th>
                                            <th>Programa</th>
                                            <th>Institución</th>
                                            <th>Instructor</th>
                                            <th>Año Escolar</th>
                                            <th>Grado/Sección</th>
                                            <th>Lapso</th>
                                            <th>Estudiantes</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista de Detalle del Taller -->
                <div id="detailView" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><i class="fas fa-tools me-2"></i>Detalle del Taller</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-secondary me-2" onclick="backToMainView()">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                <i class="fas fa-user-plus me-2"></i>Agregar Estudiante
                            </button>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0" id="tallerInfoTitle">Información del Taller</h5>
                        </div>
                        <div class="card-body" id="tallerInfoContent">
                            <!-- Se llena dinámicamente -->
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Estudiantes Inscritos</h5>
                        </div>
                        <div class="card-body">
                            <div id="studentsList">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Agregar Estudiante -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Agregar Estudiante al Taller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="search-container">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="searchCedula" class="form-label">Cédula del Estudiante</label>
                                <input type="text" class="form-control" id="searchCedula" placeholder="V-12345678">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" onclick="searchStudents()">
                                    <i class="fas fa-search me-2"></i>Buscar
                                </button>
                                <button type="button" class="btn btn-secondary ms-2" onclick="clearSearch()">
                                    <i class="fas fa-times me-2"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="searchResults" class="mt-3">
                        <!-- Resultados de búsqueda -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Calificaciones -->
    <div class="modal fade" id="gradesModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i>Calificaciones del Estudiante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="gradesForm">
                    <div class="modal-body">
                        <input type="hidden" id="grade_estudiante_id" name="estudiante_id">
                        <input type="hidden" id="grade_taller_id" name="taller_id">
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 id="studentGradeName">Estudiante: </h6>
                                <p class="text-muted" id="studentGradeInfo">Información del estudiante</p>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Momento I -->
                            <div class="col-md-4">
                                <div class="grade-section">
                                    <h6 class="text-primary"><i class="fas fa-1 me-2"></i>Momento I</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="momento_i_numeral" class="form-label">Numeral</label>
                                            <input type="number" class="form-control grade-input" id="momento_i_numeral" name="momento_i_numeral" min="0" max="20" step="0.01">
                                        </div>
                                        <div class="col-6">
                                            <label for="momento_i_literal" class="form-label">Literal</label>
                                            <select class="form-select" id="momento_i_literal" name="momento_i_literal">
                                                <option value="">Seleccionar</option>
                                                <option value="A">A (18-20)</option>
                                                <option value="B">B (16-17)</option>
                                                <option value="C">C (14-15)</option>
                                                <option value="D">D (12-13)</option>
                                                <option value="E">E (0-11)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Momento II -->
                            <div class="col-md-4">
                                <div class="grade-section">
                                    <h6 class="text-success"><i class="fas fa-2 me-2"></i>Momento II</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="momento_ii_numeral" class="form-label">Numeral</label>
                                            <input type="number" class="form-control grade-input" id="momento_ii_numeral" name="momento_ii_numeral" min="0" max="20" step="0.01">
                                        </div>
                                        <div class="col-6">
                                            <label for="momento_ii_literal" class="form-label">Literal</label>
                                            <select class="form-select" id="momento_ii_literal" name="momento_ii_literal">
                                                <option value="">Seleccionar</option>
                                                <option value="A">A (18-20)</option>
                                                <option value="B">B (16-17)</option>
                                                <option value="C">C (14-15)</option>
                                                <option value="D">D (12-13)</option>
                                                <option value="E">E (0-11)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Momento III -->
                            <div class="col-md-4">
                                <div class="grade-section">
                                    <h6 class="text-warning"><i class="fas fa-3 me-2"></i>Momento III</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="momento_iii_numeral" class="form-label">Numeral</label>
                                            <input type="number" class="form-control grade-input" id="momento_iii_numeral" name="momento_iii_numeral" min="0" max="20" step="0.01">
                                        </div>
                                        <div class="col-6">
                                            <label for="momento_iii_literal" class="form-label">Literal</label>
                                            <select class="form-select" id="momento_iii_literal" name="momento_iii_literal">
                                                <option value="">Seleccionar</option>
                                                <option value="A">A (18-20)</option>
                                                <option value="B">B (16-17)</option>
                                                <option value="C">C (14-15)</option>
                                                <option value="D">D (12-13)</option>
                                                <option value="E">E (0-11)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="grade-section">
                                    <h6 class="text-info"><i class="fas fa-calculator me-2"></i>Promedio Final</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="promedio_final" class="form-label">Numeral</label>
                                            <input type="number" class="form-control grade-input" id="promedio_final" name="promedio_final" min="0" max="20" step="0.01" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label for="literal_final" class="form-label">Literal</label>
                                            <select class="form-select" id="literal_final" name="literal_final">
                                                <option value="">Seleccionar</option>
                                                <option value="A">A (18-20)</option>
                                                <option value="B">B (16-17)</option>
                                                <option value="C">C (14-15)</option>
                                                <option value="D">D (12-13)</option>
                                                <option value="E">E (0-11)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="saveGradesBtn" class="btn btn-primary"><i class="fas fa-save me-2"></i>Guardar Calificaciones</button>
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
            let talleresTable;
            let currentTallerId = null;
            let currentStudentId = null;
            let currentTallerInstitucionId = null;
            let currentTallerCode = null;
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                showAlert('Taller guardado correctamente.', 'success');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/talleres');
            }
            if (urlParams.get('error') === 'notfound') {
                showAlert('Taller no encontrado.', 'danger');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/talleres');
            }
            function showAlert(message, type) {
                const icon = type === 'success' ? 'success' : type === 'danger' ? 'error' : type === 'warning' ? 'warning' : 'info';
                const title = type === 'success' ? 'Éxito' : type === 'danger' ? 'Error' : type === 'warning' ? 'Aviso' : 'Información';
                Swal.fire({ icon: icon, title: title, text: message });
            }
            function initTable() {
                talleresTable = $('#talleresTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: { url: '<?php echo BASE_URL; ?>/talleres/list', type: 'GET' },
                    columns: [
                        { data: 'cod_taller' },
                        { data: 'programa' },
                        { data: 'institucion' },
                        { data: 'instructor' },
                        { data: 'ano_escolar' },
                        { 
                            data: null,
                            render: function(data, type, row) {
                                return (row.grado || '') + (row.seccion ? ' / ' + row.seccion : '');
                            }
                        },
                        { data: 'lapso' },
                        { 
                            data: null,
                            render: function(data, type, row) {
                                return `<span class="badge bg-info">${row.estudiantes_count || 0}</span>`;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewTallerDetail(${row.id_taller})" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editTaller(${row.id_taller})" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTaller(${row.id_taller})" title="Eliminar">
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
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/talleres/options',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const options = response.data;
                            
                            // Llenar programas
                            $('#programas_id_programas').html('<option value="">Seleccionar programa</option>');
                            if (options.programas && options.programas.length > 0) {
                                options.programas.forEach(programa => {
                                    $('#programas_id_programas').append(`<option value="${programa.id_programas}">${programa.descripcion}</option>`);
                                });
                            }
                            
                            // Llenar instituciones
                            $('#instituciones_id_instituciones').html('<option value="">Seleccionar institución</option>');
                            if (options.instituciones && options.instituciones.length > 0) {
                                options.instituciones.forEach(institucion => {
                                    $('#instituciones_id_instituciones').append(`<option value="${institucion.id_instituciones}">${institucion.descripcion}</option>`);
                                });
                            }
                            
                            // Llenar personal
                            $('#personal_id_personal').html('<option value="">Seleccionar instructor</option>');
                            if (options.personal && options.personal.length > 0) {
                                options.personal.forEach(persona => {
                                    $('#personal_id_personal').append(`<option value="${persona.id_personal}">${persona.nombre} ${persona.apellido}</option>`);
                                });
                            }
                        } else {
                            showAlert('Error al cargar opciones: ' + response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al cargar opciones del formulario', 'danger');
                    }
                });
            }
            
            // Buscar estudiantes solo por cédula (se limpia V- y caracteres no numéricos)
            window.searchStudents = function() {
                let cedula = $('#searchCedula').val().trim();
                
                if (!cedula) {
                    showAlert('Debe ingresar la cédula del estudiante para buscar', 'warning');
                    return;
                }

                // Eliminar prefijos tipo V- o E- y cualquier carácter no numérico
                cedula = cedula.replace(/^[VEve][-]/, '').replace(/[^0-9]/g, '');

                if (!cedula) {
                    showAlert('La cédula indicada no es válida', 'warning');
                    return;
                }
                
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/estudiantes/search',
                    method: 'GET',
                    data: { 
                        cedula: cedula
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            displaySearchResults(response.data);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al buscar estudiantes', 'danger');
                    }
                });
            };
            
            function displaySearchResults(students) {
                let html = '<h6>Resultados de la búsqueda:</h6>';
                
                if (students.length === 0) {
                    html += '<p class="text-muted">No se encontraron estudiantes</p>';
                } else {
                    students.forEach(student => {
                        html += `
                            <div class="student-card">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">${student.nombre} ${student.apellido}</h6>
                                        <p class="text-muted mb-0">Cédula: ${student.cedula_estudiante}</p>
                                        <small class="text-info">Institución: ${student.institucion_nombre || 'No asignada'}</small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button class="btn btn-success btn-sm" onclick="addStudentToTaller(${student.id_estudiante})">
                                            <i class="fas fa-plus me-1"></i>Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                $('#searchResults').html(html);
            }
            
            window.clearSearch = function() {
                $('#searchCedula').val('');
                $('#searchResults').html('');
            };
            
            window.addStudentToTaller = function(estudianteId) {
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/talleres/add-student',
                    method: 'POST',
                    data: {
                        taller_id: currentTallerId,
                        estudiante_id: estudianteId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert('Estudiante agregado exitosamente', 'success');
                            $('#addStudentModal').modal('hide');
                            loadTallerStudents();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al agregar estudiante', 'danger');
                    }
                });
            };
            
            window.viewTallerDetail = function(tallerId) {
                currentTallerId = tallerId;
                loadTallerDetail(tallerId);
                $('#mainView').hide();
                $('#detailView').show();
            };
            
            function loadTallerDetail(tallerId) {
                $.ajax({
                    url: `<?php echo BASE_URL; ?>/talleres/get/${tallerId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const taller = response.data;
                            // guardar datos del taller para búsquedas y enlaces
                            currentTallerInstitucionId = taller.instituciones_id_instituciones || taller.institucion_id || null;
                            currentTallerCode = taller.cod_taller || null;
                            displayTallerInfo(taller);
                            loadTallerStudents();
                        } else {
                            showAlert('Error al cargar información del taller', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al cargar información del taller', 'danger');
                    }
                });
            }
            
            function displayTallerInfo(taller) {
                $('#tallerInfoTitle').text(`Taller: ${taller.cod_taller}`);
                $('#tallerInfoContent').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Programa:</strong> ${taller.programa}</p>
                            <p><strong>Institución:</strong> ${taller.institucion}</p>
                            <p><strong>Instructor:</strong> ${taller.instructor}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Año Escolar:</strong> ${taller.ano_escolar}</p>
                            <p><strong>Grado/Sección:</strong> ${taller.grado || ''} ${taller.seccion ? '/' + taller.seccion : ''}</p>
                            <p><strong>Lapso:</strong> ${taller.lapso}</p>
                        </div>
                    </div>
                `);
            }
            
            function loadTallerStudents() {
                $.ajax({
                    url: `<?php echo BASE_URL; ?>/talleres/students/${currentTallerId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            displayTallerStudents(response.data);
                        } else {
                            showAlert('Error al cargar estudiantes del taller', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al cargar estudiantes del taller', 'danger');
                    }
                });
            }
            
            function displayTallerStudents(students) {
                let html = '';
                
                if (students.length === 0) {
                    html = '<p class="text-muted text-center">No hay estudiantes inscritos en este taller</p>';
                } else {
                    students.forEach(student => {
                        const hasGrades = student.momento_i_numeral || student.momento_ii_numeral || student.momento_iii_numeral;
                        html += `
                            <div class="student-card">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1">${student.nombre} ${student.apellido}</h6>
                                        <p class="text-muted mb-0">Cédula: ${student.cedula_estudiante}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            ${hasGrades ? 
                                                `<span class="badge bg-success">Con Calificaciones</span>` : 
                                                `<span class="badge bg-warning">Sin Calificaciones</span>`
                                            }
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button class="btn btn-primary btn-sm" onclick="goToCalificacionCreate(${student.estudiante_id_estudiante || student.id_estudiante})">
                                            <i class="fas fa-chart-line me-1"></i>Calificar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                $('#studentsList').html(html);
            }
            
            // Ir a la pantalla de creación de calificación con estudiante y taller preseleccionados
            window.goToCalificacionCreate = function(estudianteId) {
                if (!currentTallerCode) {
                    showAlert('No se pudo determinar el código del taller.', 'warning');
                    return;
                }
                const baseUrl = '<?php echo BASE_URL; ?>';
                const url = `${baseUrl}/calificaciones/create?estudiante_id=${encodeURIComponent(estudianteId)}&cod_taller=${encodeURIComponent(currentTallerCode)}`;
                window.location.href = url;
            };
            window.openGradesModal = function(estudianteId, nombre, cedula) {
                console.log('[grades] openGradesModal', { estudianteId, currentTallerId });
                currentStudentId = estudianteId;
                $('#grade_estudiante_id').val(estudianteId);
                $('#grade_taller_id').val(currentTallerId);
                // Persistir también en data-attributes del modal
                $('#gradesModal').data('estudiante-id', estudianteId);
                $('#gradesModal').data('taller-id', currentTallerId);
                $('#studentGradeName').text(`Estudiante: ${nombre}`);
                $('#studentGradeInfo').text(`Cédula: ${cedula}`);
                
                // Cargar calificaciones existentes
                loadStudentGrades(estudianteId);
                
                $('#gradesModal').modal('show');
            };
            
            function loadStudentGrades(estudianteId) {
                $.ajax({
                    url: `<?php echo BASE_URL; ?>/calificaciones/by-student-taller`,
                    method: 'GET',
                    data: {
                        estudiante_id: estudianteId,
                        taller_id: currentTallerId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            const grades = response.data;
                            $('#momento_i_numeral').val(grades.momento_i_numeral || '');
                            $('#momento_i_literal').val(grades.momento_i_literal || '');
                            $('#momento_ii_numeral').val(grades.momento_ii_numeral || '');
                            $('#momento_ii_literal').val(grades.momento_ii_literal || '');
                            $('#momento_iii_numeral').val(grades.momento_iii_numeral || '');
                            $('#momento_iii_literal').val(grades.momento_iii_literal || '');
                            $('#promedio_final').val(grades.promedio_final || '');
                            $('#literal_final').val(grades.literal_final || '');
                        } else {
                            // Limpiar formulario si no hay calificaciones
                            $('#gradesForm')[0].reset();
                            $('#grade_estudiante_id').val(estudianteId);
                            $('#grade_taller_id').val(currentTallerId);
                        }
                    },
                    error: function() {
                        showAlert('Error al cargar calificaciones', 'danger');
                    }
                });
            }
            
            // Guardar calificaciones
            $('#gradesForm').on('submit', function(e) {
                e.preventDefault();
                console.log('[grades] submit');
                
                // Asegurar IDs requeridos
                let estudianteId = $('#grade_estudiante_id').val();
                let tallerId = $('#grade_taller_id').val();
                if (!estudianteId && currentStudentId) {
                    $('#grade_estudiante_id').val(currentStudentId);
                    estudianteId = currentStudentId;
                }
                if (!tallerId && currentTallerId) {
                    $('#grade_taller_id').val(currentTallerId);
                    tallerId = currentTallerId;
                }
                // Último fallback: tomar desde data del modal
                if (!estudianteId) {
                    estudianteId = $('#gradesModal').data('estudiante-id');
                    if (estudianteId) $('#grade_estudiante_id').val(estudianteId);
                }
                if (!tallerId) {
                    tallerId = $('#gradesModal').data('taller-id');
                    if (tallerId) $('#grade_taller_id').val(tallerId);
                }
                
                if (!estudianteId || !tallerId) {
                    console.warn('[grades] faltan ids', { estudianteId, tallerId, currentTallerId });
                    showAlert('Faltan datos: estudiante o taller no definido', 'warning');
                    return;
                }
                
                const payload = {
                    estudiante_id: estudianteId,
                    taller_id: tallerId,
                    momento_i_numeral: $('#momento_i_numeral').val(),
                    momento_i_literal: $('#momento_i_literal').val(),
                    momento_ii_numeral: $('#momento_ii_numeral').val(),
                    momento_ii_literal: $('#momento_ii_literal').val(),
                    momento_iii_numeral: $('#momento_iii_numeral').val(),
                    momento_iii_literal: $('#momento_iii_literal').val(),
                    promedio_final: $('#promedio_final').val(),
                    literal_final: $('#literal_final').val()
                };
                console.log('[grades] payload', payload);
                
                const $submitBtn = $('#saveGradesBtn');
                $submitBtn.prop('disabled', true).append(' <span class="spinner-border spinner-border-sm"></span>');
                
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/calificaciones/save-grades',
                    method: 'POST',
                    data: payload,
                    dataType: 'json',
                    success: function(response) {
                        console.log('[grades] response', response);
                        if (response && response.success) {
                            showAlert('Calificaciones guardadas exitosamente', 'success');
                            $('#gradesModal').modal('hide');
                            loadTallerStudents(); // Recargar lista de estudiantes
                        } else {
                            const msg = (response && response.message) ? response.message : 'No se pudo guardar';
                            showAlert(msg, 'danger');
                        }
                    },
                    error: function(xhr) {
                        console.error('[grades] error', xhr.responseText);
                        showAlert('Error al guardar calificaciones', 'danger');
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).find('.spinner-border').remove();
                    }
                });
            });
            
            // Calcular promedio automáticamente
            $('.grade-input').on('input', function() {
                calculateAverage();
            });
            
            function calculateAverage() {
                const momento1 = parseFloat($('#momento_i_numeral').val()) || 0;
                const momento2 = parseFloat($('#momento_ii_numeral').val()) || 0;
                const momento3 = parseFloat($('#momento_iii_numeral').val()) || 0;
                
                if (momento1 > 0 || momento2 > 0 || momento3 > 0) {
                    const promedio = (momento1 + momento2 + momento3) / 3;
                    $('#promedio_final').val(promedio.toFixed(2));
                    
                    // Asignar literal automáticamente
                    let literal = '';
                    if (promedio >= 18) literal = 'A';
                    else if (promedio >= 16) literal = 'B';
                    else if (promedio >= 14) literal = 'C';
                    else if (promedio >= 12) literal = 'D';
                    else if (promedio > 0) literal = 'E';
                    
                    $('#literal_final').val(literal);
                }
            }
            
            window.backToMainView = function() {
                $('#detailView').hide();
                $('#mainView').show();
                currentTallerId = null;
            };
            
            window.editTaller = function(id) {
                window.location.href = `<?php echo BASE_URL; ?>/talleres/edit/${id}`;
            };
            
            window.deleteTaller = function(id) {
                Swal.fire({
                    title: '¿Eliminar taller?',
                    text: '¿Está seguro de que desea eliminar este taller? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?php echo BASE_URL; ?>/talleres/delete/${id}`,
                            method: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    showAlert('Taller eliminado exitosamente', 'success');
                                    talleresTable.ajax.reload();
                                } else {
                                    showAlert(response.message, 'danger');
                                }
                            },
                            error: function() {
                                showAlert('Error al eliminar taller', 'danger');
                            }
                        });
                    }
                });
            };
            
            function getCurrentTallerInstitucion() {
                return currentTallerInstitucionId;
            }

            // Buscar al presionar Enter dentro del modal
            $('#addStudentModal').on('shown.bs.modal', function () {
                $('#searchCedula, #searchNombre').off('keypress').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        searchStudents();
                    }
                });
            });

            // Reforzar seteo de IDs cuando se abre el modal de calificaciones
            $('#gradesModal').on('shown.bs.modal', function () {
                if (currentStudentId) {
                    $('#grade_estudiante_id').val(currentStudentId);
                }
                if (currentTallerId) {
                    $('#grade_taller_id').val(currentTallerId);
                }
                console.log('[grades] modal shown ids', {
                    estudianteId: $('#grade_estudiante_id').val(),
                    tallerId: $('#grade_taller_id').val(),
                    currentTallerId,
                    currentStudentId
                });
            });
            
            initTable();
            
            // Cargar opciones cuando se abra el modal
        });
    </script>
</body>
</html>