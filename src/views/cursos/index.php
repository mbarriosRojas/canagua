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

        @media print {
            body { background-color: #ffffff; }
            .sidebar,
            .btn,
            #alertContainer,
            #mainView,
            #addParticipanteModal {
                display: none !important;
            }
            #detailView {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $currentModule = 'cursos'; $currentSection = 'index'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-book me-2"></i>Gestión de Cursos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?php echo BASE_URL; ?>/cursos/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Curso
                        </a>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div id="mainView">
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
                                            <th>Participantes</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="detailView" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <div>
                            <h2 class="h4 mb-1">Detalle del Curso</h2>
                            <p class="mb-0 text-muted" id="cursoTitle"></p>
                        </div>
                        <div class="btn-toolbar">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="backToMainView()">
                                <i class="fas fa-arrow-left me-1"></i>Volver al listado
                            </button>
                            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addParticipanteModal">
                                <i class="fas fa-user-plus me-1"></i>Agregar Participante
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="printCourseParticipants()">
                                <i class="fas fa-print me-1"></i>Imprimir Lista
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    Información del Curso
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Código:</strong> <span id="detalleCodCurso"></span></p>
                                    <p class="mb-1"><strong>Nombre:</strong> <span id="detalleNombreCurso"></span></p>
                                    <p class="mb-1"><strong>Instructor:</strong> <span id="detalleInstructor"></span></p>
                                    <p class="mb-1"><strong>Año:</strong> <span id="detalleAno"></span></p>
                                    <p class="mb-1"><strong>Periodo:</strong> <span id="detallePeriodo"></span></p>
                                    <p class="mb-1"><strong>Duración (horas):</strong> <span id="detalleDuracion"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    Participantes del Curso
                                </div>
                                <div class="card-body">
                                    <div id="courseParticipantsList" class="list-group list-group-flush small"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal: Agregar participante al curso -->
    <div class="modal fade" id="addParticipanteModal" tabindex="-1" aria-labelledby="addParticipanteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addParticipanteModalLabel">Agregar Participante al Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="searchParticipanteForm" onsubmit="searchCursoParticipants(); return false;">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label for="searchCedulaParticipante" class="form-label">Cédula</label>
                                <input type="number" inputmode="numeric" id="searchCedulaParticipante" class="form-control" placeholder="12345678" min="0" step="1">
                            </div>
                            <div class="col-md-4">
                                <label for="searchNombreParticipante" class="form-label">Nombre o Apellido</label>
                                <input type="text" id="searchNombreParticipante" class="form-control" placeholder="Nombre o apellido">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Buscar
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearParticipanteSearch()">
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <div id="searchParticipantesResults"></div>
                </div>
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
            let cursosTable;
            let currentCursoId = null;
            let currentCursoData = null;

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                showAlert('Curso guardado correctamente.', 'success');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/cursos');
            }
            if (urlParams.get('error') === 'notfound') {
                showAlert('Curso no encontrado.', 'danger');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/cursos');
            }
            
            function showAlert(message, type) {
                const icon = type === 'success' ? 'success' : type === 'danger' ? 'error' : type === 'warning' ? 'warning' : 'info';
                const title = type === 'success' ? 'Éxito' : type === 'danger' ? 'Error' : type === 'warning' ? 'Aviso' : 'Información';
                Swal.fire({ icon: icon, title: title, text: message });
            }

            function normalizeCedula(value) {
                if (!value) return '';
                return value.replace(/^[VE]-/i, '').replace(/\D/g, '');
            }

            function initTable() {
                cursosTable = $('#cursosTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: { url: '<?php echo BASE_URL; ?>/cursos/list', type: 'GET' },
                    columns: [
                        { data: 'cod_curso' },
                        { data: 'nombre_curso' },
                        { data: 'instructor' },
                        { data: 'duracion' },
                        { data: 'ano' },
                        { data: 'periodo' },
                        { data: 'num_de_clases' },
                        { data: 'participantes_count', defaultContent: 0 },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="viewCursoDetail(${row.id_cursos})" title="Ver detalle y participantes">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCurso(${row.id_cursos})" title="Editar curso">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCurso(${row.id_cursos})" title="Eliminar curso">
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

            window.viewCursoDetail = function(id) {
                currentCursoId = id;

                $.ajax({
                    url: `<?php echo BASE_URL; ?>/cursos/get/${id}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            currentCursoData = response.data;
                            displayCursoDetail(response.data);
                            loadCursoParticipants();
                            $('#mainView').hide();
                            $('#detailView').show();
                        } else {
                            showAlert('Curso no encontrado.', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al cargar detalle del curso.', 'danger');
                    }
                });
            };

            function displayCursoDetail(curso) {
                $('#cursoTitle').text((curso.cod_curso || '') + ' - ' + (curso.nombre_curso || ''));
                $('#detalleCodCurso').text(curso.cod_curso || '');
                $('#detalleNombreCurso').text(curso.nombre_curso || '');
                $('#detalleInstructor').text(curso.instructor || '');
                $('#detalleAno').text(curso.ano || '');
                $('#detallePeriodo').text(curso.periodo || '');
                $('#detalleDuracion').text(curso.duracion || '');
            }

            window.backToMainView = function() {
                $('#detailView').hide();
                $('#mainView').show();
                currentCursoId = null;
                currentCursoData = null;
            };

            function loadCursoParticipants() {
                if (!currentCursoId) return;

                $.ajax({
                    url: `<?php echo BASE_URL; ?>/cursos/participantes/${currentCursoId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            displayCursoParticipants(response.data || []);
                        } else {
                            showAlert(response.message || 'No se pudieron cargar los participantes.', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al cargar participantes del curso.', 'danger');
                    }
                });
            }

            function displayCursoParticipants(participants) {
                const container = $('#courseParticipantsList');
                container.empty();

                if (!participants || participants.length === 0) {
                    container.append('<div class="text-muted">No hay participantes inscritos en este curso.</div>');
                    return;
                }

                participants.forEach(function (p) {
                    const item = $(`
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div><strong>${p.cedula_participante}</strong> - ${p.apellido} ${p.nombre}</div>
                                <div class="small text-muted">
                                    ${p.telefono || ''}${p.correo ? ' | ' + p.correo : ''}
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-id="${p.id_participante}">
                                <i class="fas fa-user-minus"></i>
                            </button>
                        </div>
                    `);

                    item.find('button').on('click', function () {
                        const participanteId = $(this).data('id');
                        removeParticipanteFromCurso(participanteId);
                    });

                    container.append(item);
                });
            }

            window.searchCursoParticipants = function() {
                const cedulaRaw = $('#searchCedulaParticipante').val().trim();
                const nombre = $('#searchNombreParticipante').val().trim();
                const cedula = normalizeCedula(cedulaRaw);

                if (!cedula && !nombre) {
                    showAlert('Ingrese cédula o nombre para buscar.', 'warning');
                    return;
                }

                $.ajax({
                    url: '<?php echo BASE_URL; ?>/participantes/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        cedula: cedula,
                        nombre: nombre
                    },
                    success: function(response) {
                        if (response.success) {
                            displaySearchParticipantesResults(response.data || []);
                        } else {
                            showAlert(response.message || 'No se pudieron buscar participantes.', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al buscar participantes.', 'danger');
                    }
                });
            };

            function displaySearchParticipantesResults(participants) {
                const container = $('#searchParticipantesResults');
                container.empty();

                if (!participants || participants.length === 0) {
                    container.append('<div class="alert alert-warning mb-0">No se encontraron participantes con los criterios indicados.</div>');
                    return;
                }

                participants.forEach(function (p) {
                    const card = $(`
                        <div class="card mb-2">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div><strong>${p.cedula_participante}</strong> - ${p.apellido} ${p.nombre}</div>
                                    <div class="small text-muted">
                                        ${p.telefono || ''}${p.correo ? ' | ' + p.correo : ''}
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-success" data-id="${p.id_participante}">
                                    <i class="fas fa-user-plus me-1"></i>Agregar
                                </button>
                            </div>
                        </div>
                    `);

                    card.find('button').on('click', function () {
                        const participanteId = $(this).data('id');
                        addParticipanteToCurso(participanteId);
                    });

                    container.append(card);
                });
            }

            window.clearParticipanteSearch = function() {
                $('#searchCedulaParticipante').val('');
                $('#searchNombreParticipante').val('');
                $('#searchParticipantesResults').empty();
            };

            window.addParticipanteToCurso = function(participanteId) {
                if (!currentCursoId) {
                    showAlert('Debe seleccionar un curso primero.', 'warning');
                    return;
                }

                $.ajax({
                    url: '<?php echo BASE_URL; ?>/cursos/add-participante',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        curso_id: currentCursoId,
                        participante_id: participanteId
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('Participante agregado correctamente al curso.', 'success');
                            loadCursoParticipants();
                        } else {
                            showAlert(response.message || 'No se pudo agregar el participante.', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al agregar participante al curso.', 'danger');
                    }
                });
            };

            function removeParticipanteFromCurso(participanteId) {
                if (!currentCursoId) {
                    showAlert('Debe seleccionar un curso primero.', 'warning');
                    return;
                }

                Swal.fire({
                    title: '¿Quitar participante del curso?',
                    text: 'Esta acción desactivará al participante en este curso.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, quitar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?php echo BASE_URL; ?>/cursos/remove-participante',
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                curso_id: currentCursoId,
                                participante_id: participanteId
                            },
                            success: function(response) {
                                if (response.success) {
                                    showAlert('Participante quitado del curso.', 'success');
                                    loadCursoParticipants();
                                } else {
                                    showAlert(response.message || 'No se pudo quitar el participante.', 'danger');
                                }
                            },
                            error: function() {
                                showAlert('Error al quitar participante del curso.', 'danger');
                            }
                        });
                    }
                });
            }

            window.printCourseParticipants = function() {
                if (!currentCursoId) {
                    showAlert('Debe seleccionar un curso primero.', 'warning');
                    return;
                }
                window.print();
            };

            window.editCurso = function(id) {
                window.location.href = `<?php echo BASE_URL; ?>/cursos/edit/${id}`;
            };

            window.deleteCurso = function(id) {
                Swal.fire({
                    title: '¿Eliminar curso?',
                    text: '¿Está seguro de que desea eliminar este curso? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?php echo BASE_URL; ?>/cursos/delete/${id}`,
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
                });
            };

            initTable();
        });
    </script>
</body>
</html>
