<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participantes - <?php echo Config::getAppName(); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #667eea; --secondary-color: #764ba2; }
        body { background-color: #f8f9fa; }
        .sidebar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1); }
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
            <?php $currentModule = 'participantes'; $currentSection = 'index'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-user-friends me-2"></i>Gestión de Participantes</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?php echo BASE_URL; ?>/participantes/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Participante
                        </a>
                    </div>
                </div>
                <div id="alertContainer"></div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="participantesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre Completo</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Fecha Creación</th>
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
    <?php include __DIR__ . '/../partials/uppercase-forms.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            let participantesTable;

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                Swal.fire({ icon: 'success', title: 'Éxito', text: 'Operación realizada correctamente.' });
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/participantes');
            }
            if (urlParams.get('error') === 'notfound') {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Participante no encontrado.' });
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/participantes');
            }

            function initTable() {
                participantesTable = $('#participantesTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: { url: '<?php echo BASE_URL; ?>/participantes/list', type: 'GET' },
                    columns: [
                        { data: 'cedula_participante' },
                        { 
                            data: null,
                            render: function(data, type, row) {
                                return (row.apellido || '') + ' ' + (row.nombre || '');
                            }
                        },
                        { data: 'telefono', defaultContent: '-' },
                        { data: 'correo', defaultContent: '-' },
                        { 
                            data: 'fecha_creacion',
                            render: function(data) {
                                if (!data) return '-';
                                const d = new Date(data);
                                return d.toLocaleDateString('es-VE');
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParticipante(${row.id_participante})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParticipante(${row.id_participante})">
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

            window.editParticipante = function(id) {
                window.location.href = `<?php echo BASE_URL; ?>/participantes/edit/${id}`;
            };

            window.deleteParticipante = function(id) {
                Swal.fire({
                    title: '¿Eliminar participante?',
                    text: '¿Está seguro de que desea eliminar este participante? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?php echo BASE_URL; ?>/participantes/delete/${id}`,
                            method: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({ icon: 'success', title: 'Éxito', text: 'Participante eliminado exitosamente' });
                                    participantesTable.ajax.reload();
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Error al eliminar participante' });
                                }
                            },
                            error: function() {
                                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al eliminar participante' });
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

