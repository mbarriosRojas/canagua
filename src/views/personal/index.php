<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal - <?php echo Config::getAppName(); ?></title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php $currentModule = 'personal'; $currentSection = 'index'; include __DIR__ . '/../partials/sidebar.php'; ?>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-chalkboard-teacher me-2"></i>Gestión de Personal</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?php echo BASE_URL; ?>/personal/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nuevo Personal
                        </a>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <!-- Tabla de personal -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="personalTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre Completo</th>
                                        <th>Cargo</th>
                                        <th>Fecha de Ingreso</th>
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
            let personalTable;
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                showAlert('Personal guardado correctamente.', 'success');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/personal');
            }
            if (urlParams.get('error') === 'notfound') {
                showAlert('Personal no encontrado.', 'danger');
                if (window.history.replaceState) window.history.replaceState({}, '', '<?php echo BASE_URL; ?>/personal');
            }
            function showAlert(message, type) {
                const icon = type === 'success' ? 'success' : type === 'danger' ? 'error' : type === 'warning' ? 'warning' : 'info';
                const title = type === 'success' ? 'Éxito' : type === 'danger' ? 'Error' : type === 'warning' ? 'Aviso' : 'Información';
                Swal.fire({ icon: icon, title: title, text: message });
            }
            function initTable() {
                personalTable = $('#personalTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: '<?php echo BASE_URL; ?>/personal/list',
                        type: 'GET'
                    },
                    columns: [
                        { data: 'cedula_personal' },
                        { 
                            data: null,
                            render: function(data, type, row) {
                                return row.nombre + ' ' + row.apellido;
                            }
                        },
                        { data: 'cargo' },
                        { 
                            data: 'fecha_ingreso',
                            render: function(data, type, row) {
                                return data ? new Date(data).toLocaleDateString('es-ES') : '-';
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPersonal(${row.id_personal})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePersonal(${row.id_personal})">
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
            
            window.editPersonal = function(id) {
                window.location.href = `<?php echo BASE_URL; ?>/personal/edit/${id}`;
            };
            
            window.deletePersonal = function(id) {
                Swal.fire({
                    title: '¿Eliminar personal?',
                    text: '¿Está seguro de que desea eliminar este personal? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `<?php echo BASE_URL; ?>/personal/delete/${id}`,
                            method: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    showAlert('Personal eliminado exitosamente', 'success');
                                    personalTable.ajax.reload();
                                } else {
                                    showAlert(response.message, 'danger');
                                }
                            },
                            error: function() {
                                showAlert('Error al eliminar personal', 'danger');
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
